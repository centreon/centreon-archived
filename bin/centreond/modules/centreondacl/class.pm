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
use JSON;
use centreon::common::objects::organization;
use centreon::common::objects::host;
use centreon::common::objects::object;
use Data::Dumper;

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

sub acl_resource_add_filters {
    my ($self, %options) = @_;
    
    my %filters = ( hosts => [], services => [], extra_labels => [] );
    if (defined($options{resource_config}->{filter_host_tags}) and scalar(@{$options{resource_config}->{filter_host_tags}}) > 0) {
        push @{$filters{hosts}}, 'cfg_hosts.host_id = cfg_thf.resource_id AND cfg_thf.tag_id IN (' . join(', ',  @{$options{resource_config}->{filter_host_tags}}) . ')';
        push @{$filters{extra_tables}}, 'cfg_tags_hosts as cfg_thf';
    }
    if (defined($options{resource_config}->{filter_environnement}) and scalar(@{$options{resource_config}->{filter_environnement}}) > 0) {
        push @{$filters{hosts}}, "cfg_hosts.environment_id IN (" . join(', ',  @{$options{resource_config}->{filter_environnement}}) . ")";
        push @{$filters{services}}, "cfg_services.environment_id IN (" . join(', ',  @{$options{resource_config}->{filter_environnement}}) . ")";
    }
    if (defined($options{resource_config}->{filter_pollers}) and scalar(@{$options{resource_config}->{filter_pollers}}) > 0) {
        push @{$filters{hosts}}, "cfg_hosts.poller_id IN (" . join(', ',  @{$options{resource_config}->{filter_pollers}}) . ")";
    }
    if (defined($options{resource_config}->{filter_domains}) and scalar(@{$options{resource_config}->{filter_domains}}) > 0) {
        push @{$filters{services}}, "cfg_services.domain_id IN (" . join(', ',  @{$options{resource_config}->{filter_domains}}) . ")";
    }
    if (defined($options{resource_config}->{filter_service_tags}) and scalar(@{$options{resource_config}->{filter_service_tags}}) > 0) {
        push @{$filters{services}}, 'cfg_services.service_id = cfg_tsfilter.resource_id AND cfg_tsfilter.tag_id IN (' . join(', ',  @{$options{resource_config}->{filter_service_tags}}) . ')';
        push @{$filters{extra_tables}}, 'cfg_tags_services as cfg_tsfilter';
    }
    
    foreach (keys %filters) {
        $options{filters}->{$_} = join(' AND ', @{$filters{$_}}) if (scalar(@{$filters{$_}}) > 0);
    }
}

sub acl_resource_list_hs {
    my ($self, %options) = @_;
    
    my $filters = { hosts => '', services => '', extra_tables => '' };
    my $requests = [];
    $self->acl_resource_add_filters(filters => $filters, %options);
    # Manage hosts
    if (defined($options{resource_config}->{host_alls}) && $options{resource_config}->{host_alls} == 1) {
        push @{$requests}, "(SELECT host_id, service_id FROM cfg_hosts, cfg_hosts_services_relations, cfg_services" . $filters->{extra_tables} . ' WHERE ' .
                            'cfg_hosts.organization_id = ' . $self->{organization_id} . $filters->{hosts} . 
                            ' AND cfg_hosts.host_id = cfg_hosts_services_relations.host_host_id AND cfg_hosts_services_relations.service_service_id = cfg_services.service_id' . 
                            $filters->{services} . ')';
    } else {
        if (defined($options{resource_config}->{host_ids}) and scalar(@{$options{resource_config}->{host_ids}}) > 0) {
            push @{$requests}, "(SELECT host_id, service_id FROM cfg_hosts, cfg_hosts_services_relations, cfg_services" . $filters->{extra_tables} . ' WHERE ' .
                            'cfg_hosts.organization_id = ' . $self->{organization_id} . ' AND cfg_hosts.host_id IN (' . join(', ', @{$options{resource_config}->{host_ids}}) . ') ' . $filters->{hosts} . 
                            ' AND cfg_hosts.host_id = cfg_hosts_services_relations.host_host_id AND cfg_hosts_services_relations.service_service_id = cfg_services.service_id' . 
                            $filters->{services} . ')';
        }
        if (defined($options{resource_config}->{host_tags}) and scalar(@{$options{resource_config}->{host_tags}}) > 0) {
            push @{$requests}, "(SELECT host_id, service_id FROM cfg_hosts, cfg_hosts_services_relations, cfg_services, cfg_tags_hosts" . $filters->{extra_tables} . ' WHERE ' .
                            'cfg_hosts.organization_id = ' . $self->{organization_id} . ' AND cfg_hosts.host_id = cfg_tags_hosts.resource_id AND cfg_tags_hosts.tag_id IN (' . join(', ',  @{$options{resource_config}->{host_tags}}) . ')' . $filters->{hosts} . 
                            ' AND cfg_hosts.host_id = cfg_hosts_services_relations.host_host_id AND cfg_hosts_services_relations.service_service_id = cfg_services.service_id' . 
                            $filters->{services} . ')';
        }
    }

    # Manage services
    if (defined($options{resource_config}->{service_ids}) and scalar(@{$options{resource_config}->{service_ids}}) > 0) {
        push @{$requests}, "(SELECT host_id, service_id FROM cfg_hosts, cfg_hosts_services_relations, cfg_services" . $filters->{extra_tables} . ' WHERE ' .
                        'cfg_services.organization_id = ' . $self->{organization_id} . ' AND cfg_services.service_id IN (' . join(', ', @{$options{resource_config}->{service_ids}}) . ') ' . $filters->{services} . 
                        ' AND cfg_services.service_id = cfg_hosts_services_relations.service_service_id AND cfg_hosts_services_relations.host_id = cfg_hosts.host_id' . 
                        $filters->{hosts} . ')';
    }
    if (defined($options{resource_config}->{service_tags}) and scalar(@{$options{resource_config}->{service_tags}}) > 0) {
        push @{$requests}, "(SELECT host_id, service_id FROM cfg_hosts, cfg_hosts_services_relations, cfg_services, cfg_tags_services" . $filters->{extra_tables} . ' WHERE ' .
                        'cfg_services.organization_id = ' . $self->{organization_id} . ' AND cfg_services.service_id = cfg_tags_services.resource_id AND cfg_tags_services.tag_id IN (' . join(', ',  @{$options{resource_config}->{service_tags}}) . ')' . $filters->{services} . 
                        ' AND cfg_services.service_id = cfg_hosts_services_relations.service_service_id AND cfg_hosts_services_relations.host_id = cfg_hosts.host_id' . 
                        $filters->{hosts} . ')';
    }
    
    $self->{logger}->writeLogDebug("centreondacl: request: " . join(' UNION ALL ', @{$requests}));
    return 2 if (scalar(@{$requests}) == 0);
    return $self->{class_object}->custom_execute(request => join(' UNION ALL ', @{$requests}), mode => 0);
}

sub set_default_resource_config {
    my ($self, %options) = @_;

    $options{config}->{$options{acl_resource_id}} = {
        host_ids => [], host_alls => 0, host_tags => [],
        service_ids => [], service_tags => [],
        filter_domains => [], filter_pollers => [],
        filter_environnement => [],
        filter_host_tags => [], filter_service_tags => []
    };
}

sub acl_get_resources_config {
    my ($self, %options) = @_;
    
    # filter
    my $filter = 'cfg_acl_resources.organization_id = ' . $self->{organization_id};
    if (defined($options{acl_resource_id})) {
        $filter .= ' AND cfg_acl_resources.acl_resource_id = ' . $options{acl_resource_id};
    }
    
    my $resource_configs = {};

    # Get all_hosts
    my $request = 'SELECT cfg_acl_resources.acl_resource_id, all_hosts FROM cfg_acl_resources, cfg_acl_resources_hosts_params WHERE ' . $filter . ' AND cfg_acl_resources.acl_resource_id = cfg_acl_resources_hosts_params.acl_resource_id';
    my ($status, $datas) = $self->{class_object}->custom_execute(request => $request, mode => 2);
    return 1 if ($status == -1);
    foreach (@{$datas}) {
        $self->set_default_resource_config(config => $resource_configs, acl_resource_id => $$_[0]) if (!defined($resource_configs->{$$_[0]}));
        $resource_configs->{$$_[0]}->{host_alls} = $$_[1];
    }
    
    # Get hosts
    $request = 'SELECT cfg_acl_resources.acl_resource_id, host_id, type FROM cfg_acl_resources, cfg_acl_resources_hosts_relations WHERE ' . $filter . ' AND cfg_acl_resources.acl_resource_id = cfg_acl_resources_hosts_relations.acl_resource_id';
    ($status, $datas) = $self->{class_object}->custom_execute(request => $request, mode => 2);
    return 1 if ($status == -1);
    foreach (@{$datas}) {
        $self->set_default_resource_config(config => $resource_configs, acl_resource_id => $$_[0]) if (!defined($resource_configs->{$$_[0]}));
        # 0 = inclus, 2 => exclude (pas de filtre pour les hôtes)
        push @{$resource_configs->{$$_[0]}->{host_ids}}, $$_[1] if ($$_[2] == 0);
    }
    
    # Get host tags
    $request = 'SELECT cfg_acl_resources.acl_resource_id, tag_id, type FROM cfg_acl_resources, cfg_acl_resources_tags_hosts_relations WHERE ' . $filter . ' AND cfg_acl_resources.acl_resource_id = cfg_acl_resources_tags_hosts_relations.acl_resource_id';
    ($status, $datas) = $self->{class_object}->custom_execute(request => $request, mode => 2);
    return 1 if ($status == -1);
    foreach (@{$datas}) {
        $self->set_default_resource_config(config => $resource_configs, acl_resource_id => $$_[0]) if (!defined($resource_configs->{$$_[0]}));
        # 0 = inclus, 1 => filter, 2 => exclude
        push @{$resource_configs->{$$_[0]}->{host_tags}}, $$_[1] if ($$_[2] == 0);
        push @{$resource_configs->{$$_[0]}->{filter_host_tags}}, $$_[1] if ($$_[2] == 1);
    }
    
    # Get services
    $request = 'SELECT cfg_acl_resources.acl_resource_id, service_id, type FROM cfg_acl_resources, cfg_acl_resources_services_relations WHERE ' . $filter . ' AND cfg_acl_resources.acl_resource_id = cfg_acl_resources_services_relations.acl_resource_id';
    ($status, $datas) = $self->{class_object}->custom_execute(request => $request, mode => 2);
    return 1 if ($status == -1);
    foreach (@{$datas}) {
        $self->set_default_resource_config(config => $resource_configs, acl_resource_id => $$_[0]) if (!defined($resource_configs->{$$_[0]}));
        # 0 = inclus, 2 => exclude (pas de filtre pour les services)
        push @{$resource_configs->{$$_[0]}->{service_ids}}, $$_[1] if ($$_[2] == 0);
    }
    
    return (0, $resource_configs);
}

sub insert_execute {
    my ($self, %options) = @_;
    
    if (!(my $rv = $options{sth}->execute(@{$options{bind}}))) {
        $self->{logger}->writeLogError('SQL error: ' . $options{sth}->errstr);
        $self->{db_centreon}->rollback();
        return 1;
    }
    
    return 0;
}

sub insert_result {
    my ($self, %options) = @_;
    my ($status, $sth);
    
    $self->{db_centreon}->transaction_mode(1);
    if (defined($options{first_request})) {
       ($status) = $self->{db_centreon}->query($options{first_request});
       if ($status == -1) {
            $self->{db_centreon}->rollback();
            return 1;
       }
    }
    
    # In Oracle 11: IGNORE_ROW_ON_DUPKEY_INDEX 
    # MS SQL: IGNORE_DUP_KEY
    # Postgres: ???
    ($status, $sth) = $self->{db_centreon}->query('INSERT IGNORE INTO cfg_acl_resources_cache VALUES (' . $self->{organization_id} . ', ' . $options{acl_resource_id} . ', ?, ?)', prepare_only => 1);
    my $rows = [];
    my %host_insert = ();
    my $i = 0;
    while (my $row = ( shift(@$rows) || # get row from cache, or reload cache:
                       shift(@{$rows = $options{hs_sth}->fetchall_arrayref(undef, $self->{config}{sql_fetch})||[]})) ) {
        $i++;
        if (!defined($host_insert{$$row[0]})) {
            $host_insert{$$row[0]} = 1;
            return 1 if ($self->insert_execute(sth => $sth, bind => [1, $$row[0]]));
        }
        return 1 if ($self->insert_execute(sth => $sth, bind => [2, $$row[1]]));
    }
    
    $self->{db_centreon}->commit();
    return 0;
}

sub action_aclresync {
    my ($self, %options) = @_;
    my ($status, $sth, $resource_configs);
    
    $self->{logger}->writeLogDebug("centreondacl: organization $self->{organization_id} : begin resync");
    ($status, $resource_configs) = $self->acl_get_resources_config();
    if ($status) {
        return 1;
    }
    
    foreach my $acl_resource_id (sort keys %{$resource_configs}) {
        $self->{logger}->writeLogDebug("centreondacl: organization $self->{organization_id} acl resource $acl_resource_id : begin resync");
        ($status, $sth) = $self->acl_resource_list_hs(resource_config => $resource_configs->{$acl_resource_id});
        if ($status == -1) {
            return 1;
        }

        if ($status == 2) {
            $self->{logger}->writeLogDebug("centreondacl: organization $self->{organization_id} acl resource $acl_resource_id : finished resync (emtpy resource)");
            next;
        }

        $status = $self->insert_result(acl_resource_id => $acl_resource_id, hs_sth => $sth, first_request => "DELETE FROM cfg_acl_resources_cache WHERE organization_id = '" . $self->{organization_id} . "' AND acl_resource_id = " . $acl_resource_id);
        $self->{logger}->writeLogDebug("centreondacl: organization $self->{organization_id} acl resource $acl_resource_id : finished resync (status: $status)");
        if ($status == 1) {
            return 1;
        }
    }
    
    return 0;
}

sub event {
    while (1) {
        my $message = centreon::centreond::common::zmq_dealer_read_message(socket => $socket);
        
        $connector->{logger}->writeLogDebug("centreondacl: class: $message");
        if ($message =~ /^\[(.*?)\]/) {
            if ((my $method = $connector->can('action_' . lc($1)))) {
                $message =~ /^\[(.*?)\]\s+\[(.*?)\]\s+\[.*?\]\s+(.*)$/m;
                my ($action, $token) = ($1, $2);
                my $data = JSON->new->utf8->decode($3);
                while ($method->($connector, token => $token, data => $data)) {
                    # We block until it's fixed!!
                    sleep(5);
                }
            }
        }

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
                                                     force => 2,
                                                     logger => $self->{logger});
    ##### Load objects #####
    #$self->{class_organization} = centreon::common::objects::organization->new(logger => $self->{logger}, db_centreon => $self->{db_centreon});
    #$self->{class_host} = centreon::common::objects::host->new(logger => $self->{logger}, db_centreon => $self->{db_centreon});
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
