################################################################################
# Copyright 2005-2014 MERETHIS
# Centreon is developped by : Julien Mathis and Romain Le Merlus under
# GPL Licence 2.0.
# 
# This program is free software; you can redistribute it and/or modify it under 
# the terms of the GNU General Public License as published by the Free Software 
# Foundation ; either version 2 of the License.
# 
# This program is distributed in the hope that it will be useful, but WITHOUT ANY
# WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
# PARTICULAR PURPOSE. See the GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License along with 
# this program; if not, see <http://www.gnu.org/licenses>.
# 
# Linking this program statically or dynamically with other modules is making a 
# combined work based on this program. Thus, the terms and conditions of the GNU 
# General Public License cover the whole combination.
# 
# As a special exception, the copyright holders of this program give MERETHIS 
# permission to link this program with independent modules to produce an executable, 
# regardless of the license terms of these independent modules, and to copy and 
# distribute the resulting executable under terms of MERETHIS choice, provided that 
# MERETHIS also meet, for each linked independent module, the terms  and conditions 
# of the license of that module. An independent module is a module which is not 
# derived from this program. If you modify this program, you may extend this 
# exception to your version of the program, but you are not obliged to do so. If you
# do not wish to do so, delete this exception statement from your version.
# 
#
####################################################################################

package centreon::centreond::common;

use strict;
use warnings;
use ZMQ::LibZMQ3;
use ZMQ::Constants qw(:all);
use JSON;
use File::Basename;
use Config::IniFiles;
use Crypt::OpenSSL::RSA;
use Crypt::OpenSSL::Random;
use Crypt::CBC;
use Data::Dumper;

my %zmq_type = ('ZMQ_ROUTER' => ZMQ_ROUTER, 'ZMQ_DEALER' => ZMQ_DEALER);
my $privkey;

sub read_config {
    my (%options) = @_;
    my %config;
    
    tie %config, 'Config::IniFiles', (-file => $options{config_file});
    if (defined(@Config::IniFiles::errors)) {
        $options{logger}->writeLogError("Parsinig extra config file error:");
        $options{logger}->writeLogError(join("\n", @Config::IniFiles::errors));
        exit(1);
    }
    return \%config;
}

#######################
# Handshake functions
#######################

sub loadpubkey {
    my (%options) = @_;
    my $string_key = '';
    
    if (!open FILE, "<" . $options{pubkey}) {
        $options{logger}->writeLogError("Cannot read file '$options{pubkey}': $!");
        exit(1);
    }
    while (<FILE>) {
        $string_key .= $_;
    }
    close FILE;
    
    my $pubkey;
    eval {
        $pubkey = Crypt::OpenSSL::RSA->new_public_key($string_key);
        $pubkey->use_pkcs1_padding();
    };
    if ($@) {
        $options{logger}->writeLogError("Cannot load privkey '$options{pubkey}': $@");
        exit(1);
    }
    
    return $pubkey;
}

sub loadprivkey {
    my (%options) = @_;
    my $string_key = '';
    
    if (!open FILE, "<" . $options{privkey}) {
        $options{logger}->writeLogError("Cannot read file '$options{privkey}': $!");
        exit(1);
    }
    while (<FILE>) {
        $string_key .= $_;
    }
    close FILE;

    eval {
        $privkey = Crypt::OpenSSL::RSA->new_private_key($string_key);
        $privkey->use_pkcs1_padding();
    };
    if ($@) {
        $options{logger}->writeLogError("Cannot load privkey '$options{privkey}': $@");
        exit(1);
    }
}

sub zmq_core_key_response {
    my (%options) = @_;
    
    if (defined($options{identity})) {
        zmq_sendmsg($options{socket}, pack('H*', $options{identity}), ZMQ_NOBLOCK | ZMQ_SNDMORE);
    }
    my $crypttext;
    eval {
        $crypttext = $privkey->private_encrypt("[KEY] [$options{hostname}] [" . $options{symkey} . "]");
    };
    if ($@) {
        $options{logger}->writeLogError("Encoding issue: " .  $@);
        return -1;
    }
    zmq_sendmsg($options{socket}, $crypttext, ZMQ_NOBLOCK);
    return 0;
}

sub zmq_core_response {
    my (%options) = @_;
    my $msg;
    my $response_type = defined($options{response_type}) ? $options{response_type} : 'ACK';
    
    if (defined($options{identity})) {
        zmq_sendmsg($options{socket}, pack('H*', $options{identity}), ZMQ_NOBLOCK | ZMQ_SNDMORE);
    }

    my $data = json_encode(data => { code => $options{code}, data => $options{data} });
    # We add 'target' for 'PONG', 'CONSTATUS'. Like that 'centreond-proxy can get it
    $msg = '[' . $response_type . '] [' . (defined($options{token}) ? $options{token} : '') . '] ' . ($response_type eq 'PONG' ? '[] ' : '') . $data;
    
    if (defined($options{cipher})) {
        my $cipher = Crypt::CBC->new(-key    => $options{symkey},
                                     -keysize => length($options{symkey}),
                                     -cipher => $options{cipher},
                                     -iv => $options{vector},
                                     -header => 'none',
                                     -literal_key => 1
                                     );
        $msg = $cipher->encrypt($msg);
    }
    zmq_sendmsg($options{socket}, $msg, ZMQ_NOBLOCK);
}

sub uncrypt_message {
    my (%options) = @_;
    my $plaintext;
    
    my $cipher = Crypt::CBC->new(-key    => $options{symkey},
                                 -keysize => length($options{symkey}),
                                 -cipher => $options{cipher},
                                 -iv => $options{vector},
                                 -header => 'none',
                                 -literal_key => 1
                                );
    eval {
        $plaintext = $cipher->decrypt($options{message});
    };
    if ($@) {
        if (defined($options{logger})) {
            $options{logger}->writeLogError("Sym encrypt issue: " .  $@);
        }
        return (-1, $@);
    }
    return (0, $plaintext);
}

sub generate_token {
    my (%options) = @_;
    
    my $token = Crypt::OpenSSL::Random::random_bytes(256);
    return unpack('H*', $token);
}

sub generate_symkey {
    my (%options) = @_;
    
    my $random_key = Crypt::OpenSSL::Random::random_bytes($options{keysize});
    return (0, $random_key);
}

sub client_get_secret {
    my (%options) = @_;
    my $plaintext;
    
    eval {
        $plaintext = $options{pubkey}->public_decrypt($options{message});
    };
    if ($@) {
        return (-1, "Decoding issue: $@");
    }

    $plaintext = unpack('H*', $plaintext);
    
    if ($plaintext !~ /^5b(.*?)5d(.*?)5b(.*?)5d(.*?)5b(.*)5d$/i) {
        return (-1, 'Wrong protocol');
    }

    my $hostname = pack('H*', $3);
    my $symkey = pack('H*', $5);
    return (0, $symkey, $hostname);
}

sub client_helo_encrypt {
    my (%options) = @_;
    my $ciphertext;
    
    eval {
        $ciphertext = $options{pubkey}->encrypt($options{message});
    };
    if ($@) {
        return (-1, "Decoding issue: $@");
    }

    return (0, $ciphertext);
}

sub is_client_can_connect {
    my (%options) = @_;
    my $plaintext;
    
    eval {
        $plaintext = $privkey->decrypt($options{message});
    };
    if ($@) {
        $options{logger}->writeLogError("Decoding issue: " .  $@);
        return -1;
    }

    if ($plaintext !~ /\[HELO\]\s+\[(.+)\]/) {
        $options{logger}->writeLogError("Decoding issue. Protocol not good");
        return -1;
    }

    $options{logger}->writeLogError("Connection from $1");
    return 0;
}

sub is_handshake_done {
    my (%options) = @_;
    
    my ($status, $sth) = $options{dbh}->query("SELECT `key` FROM centreond_identity WHERE identity = " . $options{dbh}->quote($options{identity}) . " ORDER BY id DESC");
    return if ($status == -1);
    if (my $row = $sth->fetchrow_hashref()) {
        return (1, pack('H*', $row->{key}));
    }
    return 0;
}

#######################
# internal functions
#######################

sub constatus {
    my (%options) = @_;
    
    if (defined($options{centreond}->{modules_register}->{ $options{centreond}->{modules_id}->{$options{centreond_config}->{centreondcore}{proxy_name}} })) {
        my $name = $options{centreond_config}->{$options{centreond_config}->{centreondcore}{proxy_name}}{module};
        my $method;
        if (defined($name) && ($method = $name->can('get_constatus_result'))) {
            return (0, { action => 'constatus', mesage => 'ok', data => $method->() }, 'CONSTATUS');
        }
    }
    
    return (1, { action => 'constatus', mesage => 'cannot get value' }, 'CONSTATUS');
}

sub ping {
    my (%options) = @_;

    #my $status = add_history(dbh => $options{centreond}->{db_centreond}, 
    #                         token => $options{token}, logger => $options{logger}, code => 0);
    return (0, { action => 'ping', mesage => 'ping ok', id => $options{id} }, 'PONG');
}
    
sub putlog {
    my (%options) = @_;

    my $data;
    eval {
        $data = JSON->new->utf8->decode($options{data});
    };
    if ($@) {
        return (1, { mesage => 'request not well formatted' });
    }
    
    my $status = add_history(dbh => $options{centreond}->{db_centreond}, 
                             etime => $data->{etime}, token => $data->{token}, data => json_encode(data => $data->{data}, logger => $options{logger}), code => $data->{code});
    if ($status == -1) {
        return (1, { mesage => 'database issue' });
    }
    return (0, { mesage => 'message inserted' });
}

sub getlog {
    my (%options) = @_;

    my $data;
    eval {
        $data = JSON->new->utf8->decode($options{data});
    };
    if ($@) {
        return (1, { mesage => 'request not well formatted' });
    }
    
    my %filters = ();
    my ($filter, $filter_append) = ('', '');
    
    foreach ((['id', '>'], ['token', '='], ['ctime', '>='], ['etime', '>'], ['code', '='])) {
        if (defined($data->{${$_}[0]}) && $data->{${$_}[0]} ne '') {
            $filter .= $filter_append . ${$_}[0] . ' ' . ${$_}[1] . ' ' . $options{centreond}->{db_centreond}->quote($data->{${$_}[0]});
            $filter_append = ' AND ';
        }
    }
    
    if ($filter eq '') {
        return (1, { mesage => 'need at least one filter' });
    }
    
    my ($status, $sth) = $options{centreond}->{db_centreond}->query("SELECT * FROM centreond_history WHERE " . $filter);
    if ($status == -1) {
        return (1, { mesage => 'database issue' });
    }
    
    return (0, { action => 'getlog', result => $sth->fetchall_hashref('id'), id => $options{centreond}->{id} });
}

sub kill {
    my (%options) = @_;

}

#######################
# Database functions
#######################

sub update_identity {
    my (%options) = @_;

    my ($status, $sth) = $options{dbh}->query("UPDATE centreond_identity SET `ctime` = " . $options{dbh}->quote(time()) . " WHERE `identity` = " . $options{dbh}->quote($options{identity}));
    return $status;
}

sub add_identity {
    my (%options) = @_;

    my ($status, $sth) = $options{dbh}->query("INSERT INTO centreond_identity (`ctime`, `identity`, `key`) VALUES (" . 
                  $options{dbh}->quote(time()) . ", " . $options{dbh}->quote($options{identity}) . ", " . $options{dbh}->quote(unpack('H*', $options{symkey})) . ")");
    return $status;
}

sub add_history {
    my (%options) = @_;

    if (defined($options{data}) && defined($options{json_encode})) {
        return -1 if (!($options{data} = json_encode(data => $options{data}, logger => $options{logger})));
    }
    if (!defined($options{ctime})) {
        $options{ctime} = time();
    }
    if (!defined($options{etime})) {
        $options{etime} = time();
    }
    
    my @names = ();
    my @values = ();
    foreach (('data', 'token', 'ctime', 'etime', 'code')) {
        if (defined($options{$_})) {
            push @names, $_;
            push @values, $options{dbh}->quote($options{$_});
        }
    }
    my ($status, $sth) = $options{dbh}->query("INSERT INTO centreond_history (" . join(',', @names) . ") VALUES (" . 
                 join(',', @values) . ")");
    return $status;
}

#######################
# Misc functions
#######################

sub json_encode {
    my (%options) = @_;
    
    my $data;
    eval {
        $data = JSON->new->utf8->encode($options{data});
    };
    if ($@) {
        if (defined($options{logger})) {
            $options{logger}->writeLogError("Cannot encode json data: $@");
        }
        return undef;
    }

    return $data;
}

#######################
# Global ZMQ functions
#######################

sub connect_com {
    my (%options) = @_;
    
    my $context = zmq_init();
    my $socket = zmq_socket($context, $zmq_type{$options{zmq_type}});
    if (!defined($socket)) {
        $options{logger}->writeLogError("Can't setup server: $!");
        exit(1);
    }

    zmq_setsockopt($socket, ZMQ_IDENTITY, $options{name});
    zmq_setsockopt($socket, ZMQ_LINGER, defined($options{linger}) ? $options{linger} : 0); # 0 we discard
    zmq_connect($socket, $options{type} . '://' . $options{path});
    return $socket;
}

sub create_com {
    my (%options) = @_;
    
    my $context = zmq_init();
    my $socket = zmq_socket($context, $zmq_type{$options{zmq_type}});
    if (!defined($socket)) {
        $options{logger}->writeLogError("Can't setup server: $!");
        exit(1);
    }

    zmq_setsockopt($socket, ZMQ_IDENTITY, $options{name});
    zmq_setsockopt($socket, ZMQ_LINGER, 0); # we discard    
    if ($options{type} eq 'tcp') {
        zmq_bind($socket, 'tcp://' . $options{path});
    } elsif ($options{type} eq 'ipc') {
        if (zmq_bind($socket, 'ipc://' . $options{path}) == -1) {
            $options{logger}->writeLogError("Cannot bind ipc '$options{path}': $!");
            # try create dir
            $options{logger}->writeLogError("Maybe directory not exist. We try to create it!!!");
            if (!mkdir(dirname($options{path}))) {
                zmq_close($socket);
                exit(1);
            }
            if (zmq_bind($socket, 'ipc://' . $options{path}) == -1) {
                $options{logger}->writeLogError("Cannot bind ipc '$options{path}': $!");
                zmq_close($socket);
                exit(1);
            }
        }
    } else {
        $options{logger}->writeLogError("zmq type '$options{type}' not managed");
        zmq_close($socket);
        exit(1);
    }
    
    return $socket;
}

sub build_protocol {
    my (%options) = @_;
    my $data = $options{data};
    my $token = defined($options{token}) ? $options{token} : '';
    my $action = defined($options{action}) ? $options{action} : '';
    my $target = defined($options{target}) ? $options{target} : '';

    if (defined($data)) {
        if (defined($options{json_encode})) {
            $data = json_encode(data => $data, logger => $options{logger});
        }
    } else {
        $data = json_encode(data => {}, logger => $options{logger});
    }
    
    return '[' . $action . '] [' . $token . '] [' . $target . '] ' . $data;
}

sub zmq_send_message {
    my (%options) = @_;
    my $message = $options{message};
    
    if (!defined($message)) {
        $message = build_protocol(%options);
    }
    if (defined($options{identity})) {
        zmq_sendmsg($options{socket}, $options{identity}, ZMQ_NOBLOCK | ZMQ_SNDMORE);
    }    
    if (defined($options{cipher})) {
        my $cipher = Crypt::CBC->new(-key    => $options{symkey},
                                     -keysize => length($options{symkey}),
                                     -cipher => $options{cipher},
                                     -iv => $options{vector},
                                     -header => 'none',
                                     -literal_key => 1
                                     );
        $message = $cipher->encrypt($message);
    }
    zmq_sendmsg($options{socket}, $message, ZMQ_NOBLOCK);
}

sub zmq_dealer_read_message {
    my (%options) = @_;
    
    # Process all parts of the message
    my $message = zmq_recvmsg($options{socket});
    my $data = zmq_msg_data($message);
 
    return $data;
}

sub zmq_read_message {
    my (%options) = @_;
    
    # Process all parts of the message
    my $message = zmq_recvmsg($options{socket});
    my $identity = zmq_msg_data($message);
    $message = zmq_recvmsg($options{socket});
    my $data = zmq_msg_data($message);
 
    return (unpack('H*', $identity), $data);
}

sub zmq_still_read {
    my (%options) = @_;
    
    return zmq_getsockopt($options{socket}, ZMQ_RCVMORE);        
}

sub add_zmq_pollin {
    my (%options) = @_;

    push @{$options{poll}}, {
            socket  => $options{socket},
            events  => ZMQ_POLLIN,
            callback => $options{callback},
    };
}
        
1;
