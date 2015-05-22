################################################################################
# Copyright 2005-2015 MERETHIS
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

package modules::centreondacl::class;

use strict;
use warnings;
use centreon::centreond::common;
use ZMQ::LibZMQ3;
use ZMQ::Constants qw(:all);
use centreon::common::objects::organization;
use centreon::common::objects::host;
use centreon::common::objects::object;

my %handlers = (TERM => {}, HUP => {});
my ($connector, $socket);

sub new {
    my ($class, %options) = @_;
    $connector  = {};
    $connector->{logger} = $options{logger};
    $connector->{organization_id} = $options{organization_id};
    $connector->{config} = $options{config};
    $connector->{config_core} = $options{config_core};
    $connector->{config_db_centreon} = $options{config_db_centreon};
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
    $self->{logger}->writeLogInfo("centreond-acl $$ Receiving order to stop...");
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

sub get_list_hosts_services {
    my ($self, %options) = @_;
    
    # Params:
    #   host_ids = [], host_alls = 1|0, host_tags = []
    #   service_ids = [], service_tags = []
    #   filter_domains = [], filter_pollers = []
    #   filter_environnement = []
    #   filter_host_tags = [], filter_service_tags = []
    
    my ($requests, $filter_hosts, $filter_services, $extra_tables) = ([], [], [], []);
    if (defined($options{filter_environnement}) and scalar(@{$options{filter_environnement}}) > 0) {
        push @{$filter_hosts}, "cfg_hosts.environment_id IN (" . join(', ',  @{$options{filter_environnement}}) . ")";
        push @{$filter_services}, "cfg_services.environment_id IN (" . join(', ',  @{$options{filter_environnement}}) . ")";
    }
    if (defined($options{filter_pollers}) and scalar(@{$options{filter_pollers}}) > 0) {
        push @{$filter_hosts}, "cfg_hosts.poller_id IN (" . join(', ',  @{$options{filter_pollers}}) . ")";
    }
    if (defined($options{filter_domains}) and scalar(@{$options{filter_domains}}) > 0) {
        push @{$filter_services}, "cfg_services.domain_id IN (" . join(', ',  @{$options{filter_domains}}) . ")";
    }
    if (defined($options{filter_host_tags}) and scalar(@{$options{filter_host_tags}}) > 0) {
        push @{$filter_hosts}, 'cfg_hosts.host_id = cfg_thf.resource_id AND cfg_thf.tag_id IN (' . join(', ',  @{$options{filter_host_tags}}) . ')';
        push @{$extra_tables}, 'cfg_tags_hosts as cfg_thf';
    }
    if (defined($options{filter_service_tags}) and scalar(@{$options{filter_service_tags}}) > 0) {
        push @{$filter_services}, 'cfg_services.service_id = cfg_tsfilter.resource_id AND cfg_tsfilter.tag_id IN (' . join(', ',  @{$options{filter_service_tags}}) . ')';
        push @{$extra_tables}, 'cfg_tags_services as cfg_tsfilter';
    }
    
    # Manage hosts
    if (defined($options{host_alls}) && $options{host_alls} == 1) {
        push @{$requests}, "(SELECT host_id, service_id FROM cfg_hosts, cfg_hosts_services_relations, cfg_services" . join(', ', @{$extra_tables}) . ' WHERE ' .
                            'cfg_hosts.organization_id = ' . $connector->{organization_id} . (scalar(@{$filter_hosts}) > 0 ? join(' AND ', @{$filter_hosts}) : '') . 
                            ' AND cfg_hosts.host_id = cfg_hosts_services_relations.host_host_id AND cfg_hosts_services_relations.service_service_id = cfg_services.service_id' . 
                            (scalar(@{$filter_services}) > 0 ? ' AND ' . join(' AND ', @{$filter_services}) : '') . ')';
    } else {
        if (defined($options{host_ids}) and scalar(@{$options{host_ids}}) > 0) {
            push @{$requests}, "(SELECT host_id, service_id FROM cfg_hosts, cfg_hosts_services_relations, cfg_services" . join(', ', @{$extra_tables}) . ' WHERE ' .
                            'cfg_hosts.organization_id = ' . $connector->{organization_id} . ' AND cfg_hosts.host_id IN (' . join(', ', @{$options{host_ids}}) . ') ' . (scalar(@{$filter_hosts}) > 0 ? join(' AND ', @{$filter_hosts}) : '') . 
                            ' AND cfg_hosts.host_id = cfg_hosts_services_relations.host_host_id AND cfg_hosts_services_relations.service_service_id = cfg_services.service_id' . 
                            (scalar(@{$filter_services}) > 0 ? ' AND ' . join(' AND ', @{$filter_services}) : '') . ')';
        } elsif (defined($options{host_tags}) and scalar(@{$options{host_tags}}) > 0) {
            push @{$requests}, "(SELECT host_id, service_id FROM cfg_hosts, cfg_hosts_services_relations, cfg_services, cfg_tags_hosts" . join(', ', @{$extra_tables}) . ' WHERE ' .
                            'cfg_hosts.organization_id = ' . $connector->{organization_id} . ' AND cfg_hosts.host_id = cfg_tags_hosts.resource_id AND cfg_tags_hosts.tag_id IN (' . join(', ',  @{$options{host_tags}}) . ')' . (scalar(@{$filter_hosts}) > 0 ? join(' AND ', @{$filter_hosts}) : '') . 
                            ' AND cfg_hosts.host_id = cfg_hosts_services_relations.host_host_id AND cfg_hosts_services_relations.service_service_id = cfg_services.service_id' . 
                            (scalar(@{$filter_services}) > 0 ? ' AND ' . join(' AND ', @{$filter_services}) : '') . ')';
        }
    }

    # Manage services
    if (defined($options{service_ids}) and scalar(@{$options{service_ids}}) > 0) {
        push @{$requests}, "(SELECT host_id, service_id FROM cfg_hosts, cfg_hosts_services_relations, cfg_services" . join(', ', @{$extra_tables}) . ' WHERE ' .
                        'cfg_services.organization_id = ' . $connector->{organization_id} . ' AND cfg_services.service_id IN (' . join(', ', @{$options{service_ids}}) . ') ' . (scalar(@{$filter_services}) > 0 ? join(' AND ', @{$filter_services}) : '') . 
                        ' AND cfg_services.service_id = cfg_hosts_services_relations.service_service_id AND cfg_hosts_services_relations.host_id = cfg_hosts.host_id' . 
                        (scalar(@{$filter_hosts}) > 0 ? ' AND ' . join(' AND ', @{$filter_hosts}) : '') . ')';
    } elsif (defined($options{service_tags}) and scalar(@{$options{service_tags}}) > 0) {
        push @{$requests}, "(SELECT host_id, service_id FROM cfg_hosts, cfg_hosts_services_relations, cfg_services, cfg_tags_services" . join(', ', @{$extra_tables}) . ' WHERE ' .
                        'cfg_services.organization_id = ' . $connector->{organization_id} . ' AND cfg_services.service_id = cfg_tags_services.resource_id AND cfg_tags_services.tag_id IN (' . join(', ',  @{$options{service_tags}}) . ')' . (scalar(@{$filter_services}) > 0 ? join(' AND ', @{$filter_services}) : '') . 
                        ' AND cfg_services.service_id = cfg_hosts_services_relations.service_service_id AND cfg_hosts_services_relations.host_id = cfg_hosts.host_id' . 
                        (scalar(@{$filter_hosts}) > 0 ? ' AND ' . join(' AND ', @{$filter_hosts}) : '') . ')';
    }
    
    print join(' UNION ', @{$requests}) . "==\n";
    print Data::Dumper::Dumper($connector->{class_object}->custom_execute(request => join(' UNION ', @{$requests}), mode => 2));
}

sub event {
    while (1) {
        my $message = centreon::centreond::common::zmq_dealer_read_message(socket => $socket);
        
        $connector->{logger}->writeLogDebug("centreondacl: class: $message");
   
        use Data::Dumper;
        # Function examples
        #print Data::Dumper::Dumper($connector->{class_organization}->get_organizations(mode => 1, keys => 'organization_id'));
        # Get all hosts
        #print Data::Dumper::Dumper($connector->{class_host}->get_hosts_by_organization(organization_id => $connector->{organization_id}, mode => 2, fields => ['host_id', 'host_name']));
        # Get all hosts with services
        #print Data::Dumper::Dumper($connector->{class_host}->get_hosts_by_organization(organization_id => $connector->{organization_id}, with_services => 1,
        #                                                                               mode => 1, keys => ['host_id', 'service_id'], fields => ['host_id', 'host_name', 'service_id']));
        
        # we try an open one:
        # print Data::Dumper::Dumper($connector->{class_host}->get_hosts_by_organization(organization_id => $connector->{organization_id}, with_services => 1,
        #                                                                                mode => 1, keys => ['host_id', 'service_id'], fields => ['host_id', 'host_name', 'service_id']));
        $connector->get_list_hosts_services(host_alls => 1);
        
        last unless (centreon::centreond::common::zmq_still_read(socket => $socket));
    }
}

sub run {
    my ($self, %options) = @_;
    my $on_demand = (defined($options{on_demand}) && $options{on_demand} == 1) ? 1 : 0;
    my $on_demand_time = time();

    # Database creation. We stay in the loop still there is an error
    $self->{db_centreon} = centreon::common::db->new(dsn => $self->{config_db_centreon}{dsn},
                                                     user => $self->{config_db_centreon}{username},
                                                     password => $self->{config_db_centreon}{password},
                                                     force => 1,
                                                     logger => $self->{logger});
    ##### Load objects #####
    $self->{class_organization} = centreon::common::objects::organization->new(logger => $self->{logger}, db_centreon => $self->{db_centreon});
    $self->{class_host} = centreon::common::objects::host->new(logger => $self->{logger}, db_centreon => $self->{db_centreon});
    $self->{class_object} = centreon::common::objects::object->new(logger => $self->{logger}, db_centreon => $self->{db_centreon});
    
    # Connect internal
    $socket = centreon::centreond::common::connect_com(zmq_type => 'ZMQ_DEALER', name => 'centreondacl-' . $self->{organization_id},
                                                       logger => $self->{logger},
                                                       type => $self->{config_core}{internal_com_type},
                                                       path => $self->{config_core}{internal_com_path});
    centreon::centreond::common::zmq_send_message(socket => $socket,
                                                  action => 'ACLREADY', data => { organization_id => $self->{organization_id} },
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
            $self->{logger}->writeLogInfo("centreond-acl $$ has quit");
            zmq_close($socket);
            exit(0);
        }

        # Check if we need to quit
        if ($on_demand == 1) {
            if ($rev == 0) {
                if (time() - $on_demand_time > $self->{config}{on_demand_time}) {
                    $self->{logger}->writeLogInfo("centreond-acl $$ has quit");
                    zmq_close($socket);
                    exit(0);
                }
            } else {
                $on_demand_time = time();
            }
        }
    }
}

1;
