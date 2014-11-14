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
use ZMQ::LibZMQ3;
use ZMQ::Constants qw(:all);

my %handlers = (TERM => {}, HUP => {});
my ($connector, $socket);

sub new {
    my ($class, %options) = @_;
    my $connector  = {};
    $connector->{logger} = $options{logger};
    $connector->{pool_id} = $options{pool_id};
    $connector->{config} = $options{config};
    $connector->{config_core} = $options{config_core};
    $connector->{stop} = 0;
    
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

sub event {
    while (1) {
        my $message = centreon::centreond::common::zmq_dealer_read_message(socket => $socket);
        
        print "===== PROXY class = $message ==== yeah!!!!\n";
        
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
    $self->{poll} = [
            {
            socket  => $socket,
            events  => ZMQ_POLLIN,
            callback => \&event,
            }
    ];
    while (1) {
        # we try to do all we can
        my $rev = zmq_poll($self->{poll}, 5000);
        if ($rev == 0 && $self->{stop} == 1) {
            $self->{logger}->writeLogInfo("centreond-proxy $$ has quit");
            zmq_close($socket);
            exit(0);
        }
    }
}

1;
