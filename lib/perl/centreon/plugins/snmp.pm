################################################################################
# Copyright 2005-2013 MERETHIS
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
# For more information : contact@centreon.com
# Authors : Quentin Garnier <qgarnier@merethis.com>
#
####################################################################################

package centreon::plugins::snmp;

use strict;
use warnings;
use SNMP;
use Socket;

sub new {
    my ($class, %options) = @_;
    my $self  = {};
    bless $self, $class;
    # $options{options} = options object
    # $options{output} = output object
    # $options{exit_value} = integer
    
    if (!defined($options{output})) {
        print "Class SNMP: Need to specify 'output' argument.\n";
        exit 3;
    }
    if (!defined($options{options})) {
        $options{output}->add_option_msg(short_msg => "Class SNMP: Need to specify 'options' argument.");
        $options{output}->option_exit();
    }

    $options{options}->add_options(arguments => 
                { "H|hostname|host:s"         => { name => 'host' },
                  "C|community:s"             => { name => 'snmp_community', default => 'public' },
                  "v|snmp|snmp-version:s"     => { name => 'snmp_version', default => 1 },
                  "P|snmpport|snmp-port:i"    => { name => 'snmp_port', default => 161 },
                  "snmp-timeout:s"            => { name => 'snmp_timeout', default => 1 },
                  "snmp-retries:s"            => { name => 'snmp_retries', default => 5 },
                  "maxrepetitions:s"          => { name => 'maxrepetitions', default => 50 },
                  "subsetleef:s"              => { name => 'subsetleef', default => 50 },
                  "u|username:s"              => { name => 'snmp_security_name' },
                  "authpassphrase:s"          => { name => 'snmp_auth_passphrase' },
                  "authprotocol:s"            => { name => 'snmp_auth_protocol' },
                  "privpassphrase:s"          => { name => 'snmp_priv_passphrase' },
                  "privprotocol:s"            => { name => 'snmp_priv_protocol' },
                  "contextname:s"             => { name => 'snmp_context_name' },
                  "contextengineid:s"         => { name => 'snmp_context_engine_id' },
                  "securityengineid:s"        => { name => 'snmp_security_engine_id' },
                  "snmp-errors-exit:s"        => { name => 'snmp_errors_exit', default => 'unknown' },
    });
    $options{options}->add_help(package => __PACKAGE__, sections => 'SNMP OPTIONS');

    #####
    $self->{session} = undef;
    $self->{output} = $options{output};
    $self->{maxrepetitions} = undef;
    $self->{snmp_params} = {};
    
    # Dont load MIB
    $SNMP::auto_init_mib = 0;
    # For snmpv v1 - get request retries when you have "NoSuchName"
    $self->{RetryNoSuch} = 1;
    # Dont try to translate OID (we keep value)
    $self->{UseNumeric} = 1;
    
    $self->{error_msg} = undef;
    $self->{error_status} = 0;
    
    return $self;
}

sub connect {
    my ($self, %options) = @_;
    # $options{exit_value} = integer
    
    my ($exit_value) = defined($options{exit_value}) ? $options{exit_value} : $self->{global_exit_value};

    $self->{snmp_params}->{RetryNoSuch} = $self->{RetryNoSuch};
    $self->{snmp_params}->{UseNumeric} = $self->{UseNumeric};

    if (!$self->{output}->is_litteral_status(status => $self->{snmp_errors_exit})) {
        $self->{output}->add_option_msg(short_msg => "Unknown value '" . $self->{snmp_errors_exit}  . "' for --snmp-errors-exit.");
        $self->{output}->option_exit(exit_litteral => 'unknown');
    }
    
    $self->{session} = new SNMP::Session(%{$self->{snmp_params}});
    if ($self->{session}->{ErrorNum}) {
        $self->{output}->add_option_msg(short_msg => 'UNKNOWN: SNMP Session : ' . $self->{session}->{ErrorStr});
        $self->{output}->option_exit(exit_litteral => $self->{option_results}->{snmp_errors_exit});
    }
}

sub load {
    my ($self, %options) = @_;
    # $options{oids} = ref to array of oids (example: ['.1.2', '.1.2'])
    # $options{instances} = ref to array of oids instances
    # $options{begin}, $args->{end} = integer instance end
    # 3 way to use: with instances, with end, none
    
    if (defined($options{end})) {
        for (my $i = $options{begin}; $i <= $options{end}; $i++) {
            foreach (@{$options{oids}}) {
                push @{$self->{oids_loaded}}, $_ . "." . $i;
            }
        }
        return ;
    }
    
    if (defined($options{instances})) {
        foreach my $instance (@{$options{instances}}) {
            $instance =~ /(\d+)$/;
            foreach (@{$options{oids}}) {
                push @{$self->{oids_loaded}}, $_ . "." . $1;
            }
        }
        return ;
    }
    
    push @{$self->{oids_loaded}}, @{$options{oids}};
}

sub get_leef {
    my ($self, %options) = @_;
    # $options{dont_quit} = integer
    # $options{nothing_quit} = integer
    # $options{oids} = ref to array of oids (example: ['.1.2', '.1.2'])
    
    # Returns array
    #    'undef' value for an OID means NoSuchValue
    
    my ($dont_quit) = (defined($options{dont_quit}) && $options{dont_quit} == 1) ? 1 : 0;
    my ($nothing_quit) = (defined($options{nothing_quit}) && $options{nothing_quit} == 1) ? 1 : 0;
    $self->set_error();
    
    if (!defined($options{oids})) {
        if ($#{$self->{oids_loaded}} < 0) {
            if ($dont_quit == 1) {
                $self->set_error(error_status => -1, error_msg => "Need to specify OIDs");
                return undef;
            }
            $self->{output}->add_option_msg(short_msg => 'Need to specify OIDs');
            $self->{output}->option_exit(exit_litteral => $self->{option_results}->{snmp_errors_exit});
        }
        push @{$options{oids}}, @{$self->{oids_loaded}};
        @{$self->{oids_loaded}} = ();
    }
    
    my $results = {};
    my @array_ref_ar = ();
    my $subset_current = 0;
    my $subset_construct = [];
    foreach my $oid (@{$options{oids}}) {
        # Get last value
        next if ($oid !~ /(.*)\.(\d+)([\.\s]*)$/);
        
        my ($oid, $instance) = ($1, $2);
        $results->{$oid . "." . $instance} = undef;
        push @$subset_construct, [$oid, $instance];
        $subset_current++;
        if ($subset_current == $self->{subsetleef}) {
            push @array_ref_ar, \@$subset_construct;
            $subset_construct = [];
            $subset_current = 0;
        }
    }
    if ($subset_current) {
        push @array_ref_ar, \@$subset_construct;
    }
    
    ############################
    # If wrong oid with SNMP v1, packet resent (2 packets more). Not the case with SNMP > 1.
    # Can have "NoSuchName", if nothing works...
    # = v1: wrong oid
    #   bless( [
    #       '.1.3.6.1.2.1.1.3',
    #       '0',
    #       '199720062',
    #       'TICKS'
    #       ], 'SNMP::Varbind' ),
    #   bless( [
    #       '.1.3.6.1.2.1.1.999',
    #       '0'
    #       ], 'SNMP::Varbind' ),
    #   bless( [
    #       '.1.3.6.1.2.1.1',
    #       '1000'
    #       ], 'SNMP::Varbind' )
    # > v1: wrong oid
    #   bless( [
    #        '.1.3.6.1.2.1.1.3',
    #        '0',
    #        '199728713',
    #        'TICKS'
    #       ], 'SNMP::Varbind' ),
    #   bless( [
    #         '.1.3.6.1.2.1.1',
    #         '3',
    #         'NOSUCHINSTANCE',
    #        'TICKS'
    #    ], 'SNMP::Varbind' )
    #   bless( [
    #        '.1.3.6.1.2.1.1.999',
    #        '0',
    #        'NOSUCHOBJECT',
    #        'NOSUCHOBJECT'
    #       ], 'SNMP::Varbind' ),
    #   bless( [
    #        '.1.3.6.1.2.1.1',
    #        '1000',
    #        'NOSUCHOBJECT',
    #        'NOSUCHOBJECT'
    #       ], 'SNMP::Varbind' )
    ############################
    
    my $total = 0;
    foreach (@array_ref_ar) {
        my $vb = new SNMP::VarList(@{$_});
        $self->{session}->get($vb);
        if ($self->{session}->{ErrorNum}) {
            # 0    noError       Pas d'erreurs.
            # 1    tooBig        Reponse de taille trop grande.
            # 2    noSuchName    Variable inexistante.
            if ($self->{session}->{ErrorNum} == 2) {
                # We are at the end with snmpv1. We next.
                next;
            }
        
            my $msg = 'SNMP GET Request : ' . $self->{session}->{ErrorStr};
            
            if ($dont_quit == 0) {
                $self->{output}->add_option_msg(short_msg => $msg);
                $self->{output}->option_exit(exit_litteral => $self->{option_results}->{snmp_errors_exit});
            }
            
            $self->set_error(error_status => -1, error_msg => $msg);
            return undef;
        }

        foreach my $entry (@$vb) {
            if ($#$entry < 3) {
                # Can be snmpv1 not find
                next;
            }
            if (${$entry}[2] eq 'NOSUCHOBJECT' || ${$entry}[2] eq 'NOSUCHINSTANCE') {
                # Error in snmp > 1
                next;
            }
            
            $total++;
            $results->{${$entry}[0] . "." . ${$entry}[1]} = ${$entry}[2];
        }
    }
    
    if ($nothing_quit == 1 && $total == 0) {
        $self->{output}->add_option_msg(short_msg => "SNMP GET Request : Cant get a single value.");
        $self->{output}->option_exit(exit_litteral => $self->{option_results}->{snmp_errors_exit});
    }
    
    return $results;
}

sub get_table {
    my ($self, %options) = @_;
    # $options{dont_quit} = integer
    # $options{oid} = string (example: '.1.2')
    # $options{start} = string (example: '.1.2')
    # $options{end} = string (example: '.1.2')
    
    my ($dont_quit) = (defined($options{dont_quit}) && $options{dont_quit} == 1) ? 1 : 0;
    my ($nothing_quit) = (defined($options{nothing_quit}) && $options{nothing_quit} == 1) ? 1 : 0;
    $self->set_error();
    
    if (defined($options{start})) {
        $options{start} = $self->clean_oid($options{start});
    }
    my ($end_base, $end_instance);
    if (defined($options{end})) {
        $options{end} = $self->clean_oid($options{end});
    }
    
    # we use a medium (UDP have a PDU limit. SNMP protcol cant send multiples for one request)
    # So we need to manage
    # It's for "bulk". We ask 50 next values. If you set 1, it's like a getnext (snmp v1)
    my $repeat_count = 50;
    if (defined($self->{maxrepetitions}) && 
        $self->{maxrepetitions} =~ /^d+$/) {
        $repeat_count = $self->{maxrepetitions};
    }
    
    # Transform asking
    if ($options{oid} !~ /(.*)\.(\d+)([\.\s]*)$/) {
        if ($dont_quit == 1) {
            $self->set_error(error_status => -1, error_msg => "Method 'get_table': Wrong OID '" . $options{oid} . "'.");
            return undef;
        }
        $self->{output}->add_option_msg(short_msg => "Method 'get_table': Wrong OID '" . $options{oid} . "'.");
        $self->{output}->option_exit(exit_litteral => $self->{option_results}->{snmp_errors_exit});
    }
    
    my $main_indice = $1 . "." . $2;
    my $results = {};
    
    # Quit if base not the same or 'ENDOFMIBVIEW' value
    my $leave = 1;
    my $last_oid;

    if (defined($options{start})) {
        $last_oid = $options{start};
    } else {
        $last_oid = $options{oid};
    }
    while ($leave) {
        $last_oid =~ /(.*)\.(\d+)([\.\s]*)$/;
        my $vb = new SNMP::VarList([$1, $2]);
    
        if ($self->is_snmpv1()) {
            $self->{session}->getnext($vb);
        } else {
            $self->{session}->getbulk(0, $repeat_count, $vb);
        }
        
        # Error
        if ($self->{session}->{ErrorNum}) {
            # 0    noError       Pas d'erreurs.
            # 1    tooBig        Reponse de taille trop grande.
            # 2    noSuchName    Variable inexistante.
            if ($self->{session}->{ErrorNum} == 2) {
                # We are at the end with snmpv1. We quit.
                last;
            }
            my $msg = 'SNMP Table Request : ' . $self->{session}->{ErrorStr};
        
            if ($dont_quit == 0) {
                $self->{output}->add_option_msg(short_msg => $msg);
                $self->{output}->option_exit(exit_litteral => $self->{option_results}->{snmp_errors_exit});
            }
            
            $self->set_error(error_status => -1, error_msg => $msg);
            return undef;
        }
        
        # Manage
        foreach my $entry (@$vb) {
            if (${$entry}[2] eq 'ENDOFMIBVIEW') {
                # END mib
                $leave = 0;
                last;
            }
        
            # Not in same table
            my $complete_oid = ${$entry}[0] . "." . ${$entry}[1];
            if ($complete_oid !~ /^$main_indice\./ ||
                (defined($options{end}) && $self->check_oid_up($complete_oid, $options{end}) )) {
                $leave = 0;
                last;
            }
        
            $results->{$complete_oid} = ${$entry}[2];
            $last_oid = $complete_oid;
        }
    }
    
    if ($nothing_quit == 1 && scalar(keys %$results) == 0) {
        $self->{output}->add_option_msg(short_msg => "SNMP Table Request: Cant get a single value.");
        $self->{output}->option_exit(exit_litteral => $self->{option_results}->{snmp_errors_exit});
    }
    
    return $results;
}

sub is_snmpv1 {
    my $self = shift;
    
    if ($self->{snmp_params}->{Version} eq '1') {
        return 1;
    }
    return 0;
}

sub clean_oid {
    my $self = shift;
    
    $_[0] =~ s/\.$//;
    $_[0] =~ s/^(\d)/\.$1/;
    return $_[0];
}

sub check_oid_up {
    my $self = shift;
    my ($current_oid, $end_oid) = @_;
    
    my @current_oid_splitted = split /\./, $current_oid;
    my @end_oid_splitted = split /\./, $end_oid;
    # Skip first value (before first '.' empty)
    for (my $i = 1; $i <= $#current_oid_splitted && $i <= $#end_oid_splitted; $i++) {
        if (int($current_oid_splitted[$i]) > int($end_oid_splitted[$i])) {
            return 1;
        }
    }
    
    return 0;
}

sub check_options {
    my ($self, %options) = @_;
    # $options{option_results} = ref to options result
    
    if (!defined($options{option_results}->{host})) {
        $self->{output}->add_option_msg(short_msg => "Missing parameter -H (--host).");
        $self->{output}->option_exit();
    }

    $options{option_results}->{snmp_version} =~ s/^v//;
    if ($options{option_results}->{snmp_version} !~ /1|2c|2|3/) {
        $self->{output}->add_option_msg(short_msg => "Unknown snmp version.");
        $self->{output}->option_exit();
    }

    $self->{maxrepetitions} = $options{option_results}->{maxrepetitions};
    $self->{subsetleef} = (defined($options{option_results}->{subsetleef}) && $options{option_results}->{subsetleef} =~ /^[0-9]+$/) ? $options{option_results}->{subsetleef} : 50;
    $self->{snmp_errors_exit} = $options{option_results}->{snmp_errors_exit};

    %{$self->{snmp_params}} = (DestHost => $options{option_results}->{host},
                               Community => $options{option_results}->{snmp_community},
                               Version => $options{option_results}->{snmp_version},
                               RemotePort => $options{option_results}->{snmp_port});
    
    if (defined($options{option_results}->{snmp_timeout}) && $options{option_results}->{snmp_timeout} =~ /^[0-9]+$/) {
        $self->{snmp_params}->{Timeout} = $options{option_results}->{snmp_timeout} * (10**6);
    }
    if (defined($options{option_results}->{snmp_retries}) && $options{option_results}->{snmp_retries} =~ /^[0-9]+$/) {
        $self->{snmp_params}->{Retries} = $options{option_results}->{snmp_retries};
    }

    if ($options{option_results}->{snmp_version} eq "3") {

        $self->{snmp_params}->{Context} = $options{option_results}->{snmp_context_name} if (defined($options{option_results}->{snmp_context_name}));
        $self->{snmp_params}->{ContextEngineId} = $options{option_results}->{snmp_context_engine_id} if (defined($options{option_results}->{snmp_context_engine_id}));
        $self->{snmp_params}->{SecEngineId} = $options{option_results}->{snmp_security_engine_id} if (defined($options{option_results}->{snmp_security_engine_id}));
        $self->{snmp_params}->{SecName} = $options{option_results}->{snmp_security_name} if (defined($options{option_results}->{snmp_security_name}));
        
        # Certificate SNMPv3. Need net-snmp > 5.6
        if ($options{option_results}->{host} =~ /^(dtls|tls|ssh).*:/) {
            $self->{snmp_params}->{OurIdentity} = $options{option_results}->{snmp_our_identity} if (defined($options{option_results}->{snmp_our_identity}));
            $self->{snmp_params}->{TheirIdentity} = $options{option_results}->{snmp_their_identity} if (defined($options{option_results}->{snmp_their_identity}));
            $self->{snmp_params}->{TheirHostname} = $options{option_results}->{snmp_their_hostname} if (defined($options{option_results}->{snmp_their_hostname}));
            $self->{snmp_params}->{TrustCert} = $options{option_results}->{snmp_trust_cert} if (defined($options{option_results}->{snmp_trust_cert}));
            $self->{snmp_params}->{SecLevel} = 'authPriv';
            return ;
        }
        

        if (!defined($options{option_results}->{snmp_security_name})) {
            $self->{output}->add_option_msg(short_msg => "Missing paramater Security Name.");
            $self->{output}->option_exit();
        }
        
        # unauthenticated and unencrypted
        if (!defined($options{option_results}->{snmp_auth_passphrase}) && !defined($options{option_results}->{snmp_priv_passphrase})) {
            $self->{snmp_params}->{SecLevel} = 'noAuthNoPriv';
            return ;
        }

        if (defined($options{option_results}->{snmp_auth_passphrase}) && !defined($options{option_results}->{snmp_auth_protocol})) {
            $self->{output}->add_option_msg(short_msg => "Missing parameter authenticate protocol.");
            $self->{output}->option_exit();
        }
        $options{option_results}->{snmp_auth_protocol} = lc($options{option_results}->{snmp_auth_protocol});
        if ($options{option_results}->{snmp_auth_protocol} ne "md5" && $options{option_results}->{snmp_auth_protocol} ne "sha") {
            $self->{output}->add_option_msg(short_msg => "Wrong authentication protocol. Must be MD5 or SHA.");
            $self->{output}->option_exit();
        }

        $self->{snmp_params}->{SecLevel} = 'authNoPriv';
        $self->{snmp_params}->{AuthProto} = $options{option_results}->{snmp_auth_protocol};
        $self->{snmp_params}->{AuthPass} = $options{option_results}->{snmp_auth_passphrase};

        if (defined($options{option_results}->{snmp_priv_passphrase}) && !defined($options{option_results}->{snmp_priv_protocol})) {
            $self->{output}->add_option_msg(short_msg => "Missing parameter privacy protocol.");
            $self->{output}->option_exit();
        }
        
        if (defined($options{option_results}->{snmp_priv_protocol})) {
            $options{option_results}->{snmp_priv_protocol} = lc($options{option_results}->{snmp_priv_protocol});
            if ($options{option_results}->{snmp_priv_protocol} ne 'des' && $options{option_results}->{snmp_priv_protocol} ne 'aes') {
                $self->{output}->add_option_msg(short_msg => "Wrong privacy protocol. Must be DES or AES.");
                $self->{output}->option_exit();
            }
            
            $self->{snmp_params}->{SecLevel} = 'authPriv';
            $self->{snmp_params}->{PrivPass} = $options{option_results}->{snmp_priv_passphrase};
            $self->{snmp_params}->{PrivProto} = $options{option_results}->{snmp_priv_protocol};
        }
    }
}

sub set_error {
    my ($self, %options) = @_;
    # $options{error_msg} = string error
    # $options{error_status} = integer status
    
    $self->{error_status} = defined($options{error_status}) ? $options{error_status} : 0;
    $self->{error_msg} = defined($options{error_msg}) ? $options{error_msg} : undef;
}

sub error_status {
     my ($self) = @_;
    
    return $self->{error_status};
}

sub error {
    my ($self) = @_;
    
    return $self->{error_msg};
}

sub get_hostname {
    my ($self) = @_;

    my $host = $self->{snmp_params}->{DestHost};
    $host =~ s/.*://;
    return $host;
}

sub oid_lex_sort {
    my $self = shift;

    if (@_ <= 1) {
        return @_;
    }

    return map { $_->[0] }
            sort { $a->[1] cmp $b->[1] }
                map
                {
                   my $oid = $_;
                   $oid =~ s/^\.//;
                   $oid =~ s/ /\.0/g;
                   [$_, pack 'N*', split m/\./, $oid]
                } @_;
}

1;

__END__

=head1 NAME

SNMP global

=head1 SYNOPSIS

snmp class

=head1 SNMP OPTIONS

=over 8

=item B<--hostname>

Hostname to query (required).

=item B<--community>

Read community (defaults to public).

=item B<--snmp-version>

Version: 1 for SNMP v1 (default), 2 for SNMP v2c, 3 for SNMP v3.

=item B<--snmp-port>

Port (default: 161).

=item B<--snmp-timeout>

Timeout in secondes (default: 1) before retries.

=item B<--snmp-retries>

Set the number of retries (default: 5) before failure.

=item B<--maxrepetitions>

Max repititions value (default: 50) (only for SNMP v2 and v3).

=item B<--subsetleef>

How many oid values per SNMP request (default: 50) (for get_leef method. Be cautious whe you set it. Prefer to let the default value).

=item B<--username>

Security name (only for SNMP v3).

=item B<--authpassphrase>

Authentication protocol pass phrase.

=item B<--authprotocol>

Authentication protocol (MD5|SHA)

=item B<--privpassphrase>

Privacy protocol pass phrase

=item B<--privprotocol>

Privacy protocol (DES|AES)

=item B<--contextname>

Context name

=item B<--contextengineid>

Context engine ID

=item B<--securityengineid>

Security engine ID

=item B<--snmp-errors-exit>

Exit code for SNMP Errors (default: unknown)

=back

=head1 DESCRIPTION

B<snmp>.

=cut
