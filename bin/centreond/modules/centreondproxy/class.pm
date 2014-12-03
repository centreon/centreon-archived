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

package modules::centreondproxy::class;

use strict;
use warnings;
use centreon::centreond::common;
use centreon::centreond::clientzmq;
use ZMQ::LibZMQ3;
use ZMQ::Constants qw(:all);

my %handlers = (TERM => {}, HUP => {});
my ($connector, $socket);

sub new {
    my ($class, %options) = @_;
    $connector  = {};
    $connector->{logger} = $options{logger};
    $connector->{core_id} = $options{core_id};
    $connector->{pool_id} = $options{pool_id};
    $connector->{config} = $options{config};
    $connector->{config_core} = $options{config_core};
    $connector->{stop} = 0;
    $connector->{clients} = {};
    
    bless $connector, $class;
    $connector->set_signal_handlers;
    return $connector;
}

sub set_signal_handlers {
    my $self = shift;

    $SIG{TERM} = \&class_handle_TERM;
    $handlers{TERM}->{$self} = sub { $self->handle_TERM() };
    $SIG{HUP} = \&class_handle_HUP;
    $handlers{HUP}->{$self} = sub { $self->handle_HUP() };
}

sub handle_HUP {
    my $self = shift;
    $self->{reload} = 0;
}

sub handle_TERM {
    my $self = shift;
    $self->{logger}->writeLogInfo("centreond-proxy $$ Receiving order to stop...");
    $self->{stop} = 1;
}

sub class_handle_TERM {
    foreach (keys %{$handlers{TERM}}) {
        &{$handlers{TERM}->{$_}}();
    }
}

sub class_handle_HUP {
    foreach (keys %{$handlers{HUP}}) {
        &{$handlers{HUP}->{$_}}();
    }
}

sub get_client_information {
    my ($self, %options) = @_;
    
    # TODO DATABASE. Si database marche pas. On fait un PUTLOG.
    my $result = { type => 1, target_type => 'tcp', target_path => 'localhost:5556',
                   pubkey => 'keys/poller/pubkey.crt', cipher => 'Crypt::OpenSSL::AES',
                   keysize => '32', vector => '0123456789012345', class => undef, delete => 0 };
    return $result;
}

sub read_message {
    my (%options) = @_;
    
    return undef if (!defined($options{identity}) || $options{identity} !~ /^proxy-(.*?)-(.*?)$/);
    
    my ($client_identity) = ($2);
    if ($options{data} =~ /^\[PONG\]/) {
        if ($options{data} !~ /^\[(.+?)\]\s+\[(.*?)\]\s+\[(.*?)\]/m) {
            return undef;
        }
        my ($action, $token) = ($1, $2);
        
        centreon::centreond::common::zmq_send_message(socket => $socket,
                                                      action => 'PONG', token => $token, target => '',
                                                      data => { code => 0, data => { message => 'ping ok', action => 'ping', id => $client_identity } },
                                                      json_encode => 1);
    }
    elsif ($options{data} =~ /^\[ACK\]\s+\[(.*?)\]\s+(.*)/m) {
        my $data;
        eval {
            $data = JSON->new->utf8->decode($2);
        };
        if ($@) {
            return undef;
        }
        
        if (defined($data->{data}->{action}) && $data->{data}->{action} eq 'getlog') {
            centreon::centreond::common::zmq_send_message(socket => $socket,
                                                          action => 'SETLOGS', token => $1, target => '',
                                                          data => $2);
        }
        return undef;
    }
}

sub connect {
    my ($self, %options) = @_;

    if ($options{entry}->{type} == 1) {
        $options{entry}->{class} = centreon::centreond::clientzmq->new(identity => 'proxy-' . $self->{core_id} . '-' . $options{id}, 
                                                      cipher => $options{entry}->{cipher}, 
                                                      vector => $options{entry}->{vector},
                                                      pubkey => $options{entry}->{pubkey},
                                                      target_type => $options{entry}->{target_type},
                                                      target_path => $options{entry}->{target_path},
                                                      logger => $self->{logger}
                                                      );
        $options{entry}->{class}->init(callback => \&read_message);
    }
}

sub proxy {
    my (%options) = @_;
    
    if ($options{message} !~ /^\[(.+?)\]\s+\[(.*?)\]\s+\[(.*?)\]\s+(.*)$/m) {
        return undef;
    }
    my ($action, $token, $target, $data) = ($1, $2, $3, $4);
    
    my $entry;
    if (!defined($connector->{clients}->{$target})) {
        $entry = $connector->get_client_information(id => $target);
        return if (!defined($entry));
        
        $connector->connect(id => $target, entry => $entry);
    } else {
        $entry = $connector->{clients}->{$target};
    }

    if ($entry->{type} == 1) {
        my ($status, $msg) = $entry->{class}->send_message(action => $action, token => $token,
                                                           target => '', data => $data);
        if ($status == 0) {
            $connector->{clients}->{$target} = $entry;
        } else {
            # error we put log and we close (TODO the log)
            $connector->{logger}->writeLogError("centreondproxy: class: send message problem for '$target': $msg");
            $connector->{clients}->{$target}->{delete} = 1 if (defined($connector->{clients}->{$target}));
        }
    }
    
    $connector->{logger}->writeLogDebug("centreondproxy: class: [action = $action] [token = $token] [target = $target] [data = $data]");
}

sub event_internal {
    while (1) {
        my $message = centreon::centreond::common::zmq_dealer_read_message(socket => $socket);
                
        proxy(message => $message);        
        last unless (centreon::centreond::common::zmq_still_read(socket => $socket));
    }
}

sub run {
    my ($self, %options) = @_;

    # Connect internal
    $socket = centreon::centreond::common::connect_com(zmq_type => 'ZMQ_DEALER', name => 'centreondproxy-' . $self->{pool_id},
                                                       logger => $self->{logger},
                                                       type => $self->{config_core}{internal_com_type},
                                                       path => $self->{config_core}{internal_com_path});
    centreon::centreond::common::zmq_send_message(socket => $socket,
                                                  action => 'PROXYREADY', data => { pool_id => $self->{pool_id} },
                                                  json_encode => 1);
    my $poll = {
            socket  => $socket,
            events  => ZMQ_POLLIN,
            callback => \&event_internal,
    };
    while (1) {
        my $polls = [$poll];
        foreach (keys %{$self->{clients}}) {
            if ($self->{clients}->{$_}->{delete} == 1) {
                $self->{clients}->{$_}->{class}->close();
                delete $self->{clients}->{$_};
                next;
            }
            if ($self->{clients}->{$_}->{type} == 1) {
                push @{$polls}, $self->{clients}->{$_}->{class}->get_poll();
            }
        }
        
        # we try to do all we can
        my $rev = zmq_poll($polls, 5000);
        
        # Sometimes (with big message) we have a undef ??!!!
        next if (!defined($rev));
        
        if ($rev == 0 && $self->{stop} == 1) {
            $self->{logger}->writeLogInfo("centreond-proxy $$ has quit");
            zmq_close($socket);
            exit(0);
        }
    }
}

1;
