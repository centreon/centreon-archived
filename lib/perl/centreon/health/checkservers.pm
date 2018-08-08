#
# Copyright 2017 Centreon (http://www.centreon.com/)
#
# Centreon is a full-fledged industry-strength solution that meets
# the needs in IT infrastructure and application monitoring for
# service performance.
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#     http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.
#

package centreon::health::checkservers;

use strict;
use warnings;
use integer;
use POSIX qw(strftime);

sub new {
    my $class = shift;
    my $self = {};
    $self->{output} = {};

    bless $self, $class;
    return $self;
}

sub query_misc {
    my ($self, %options) = @_;
    my ($sth, $status);

    ($status, $sth) = $options{cdb}->query($options{query});

    if ($status == -1) {
        return "Query error - information is not available\n";
    } else {
       return $sth->fetchrow()
    }

}

sub get_servers_informations {
    my ($self, %options) = @_;

    my $sth = $options{cdb}->query("SELECT id, name, localhost, ns_ip_address, ssh_port
                                   FROM nagios_server WHERE ns_activate='1'");
    while (my $row = $sth->fetchrow_hashref()) {
        $self->{output}->{poller}->{$row->{id}}{name} = $row->{name};
        $self->{output}->{poller}->{$row->{id}}{localhost} = ($row->{localhost} == 1) ? "YES" : "NO";
        $self->{output}->{poller}->{$row->{id}}{address} = $row->{ns_ip_address};
        $self->{output}->{poller}->{$row->{id}}{ssh_port} = (defined($row->{ssh_port})) ? $row->{ssh_port} : "-";
        $self->{output}->{poller}->{$row->{id}}{id} = $row->{id};
    }

    foreach my $id (keys %{$self->{output}->{poller}}) {
        $self->{output}->{global}->{count_poller}++;
        $sth = $options{csdb}->query("SELECT COUNT(DISTINCT hosts.host_id) as num_hosts, count(DISTINCT services.host_id, services.service_id) as num_services
                                     FROM hosts, services WHERE services.host_id=hosts.host_id
                                     AND hosts.enabled=1
                                     AND services.enabled=1
                                     AND hosts.instance_id=".$options{cdb}->quote($id)."
                                     AND hosts.name NOT LIKE '%Module%'");

        while (my $row = $sth->fetchrow_hashref()) {
            $self->{output}->{poller}{$id}{hosts} = $row->{num_hosts};
            $self->{output}->{poller}{$id}{services} = $row->{num_services};
            $self->{output}->{global}{count_hosts} += $row->{num_hosts};
            $self->{output}->{global}{count_services} += $row->{num_services};
        }

        $sth = $options{csdb}->query("SELECT COUNT(DISTINCT hosts.host_id) as num_hosts, count(DISTINCT services.host_id, services.service_id) as num_services
                                     FROM hosts, services WHERE services.host_id=hosts.host_id
                                     AND hosts.enabled=1
                                     AND services.enabled=1
                                     AND hosts.instance_id=".$options{cdb}->quote($id)."");

        $sth = $options{csdb}->query("SELECT *
                                     FROM instances
                                     WHERE instance_id = " . $options{cdb}->quote($id) . "");

        while (my $row = $sth->fetchrow_hashref()) {
            $self->{output}->{poller}{$row->{instance_id}}{uptime} = centreon::health::misc::change_seconds(value => $row->{last_alive} - $row->{start_time});
            $self->{output}->{poller}{$row->{instance_id}}{running} = ($row->{running} == 1) ? "YES" : "NO";
            $self->{output}->{poller}{$row->{instance_id}}{start_time} = strftime("%m/%d/%Y %H:%M:%S",localtime($row->{start_time}));
            $self->{output}->{poller}{$row->{instance_id}}{last_alive} = strftime("%m/%d/%Y %H:%M:%S",localtime($row->{last_alive}));
            $self->{output}->{poller}{$row->{instance_id}}{last_command_check} = strftime("%m/%d/%Y %H:%M:%S",localtime($row->{last_command_check}));
            $self->{output}->{poller}{$row->{instance_id}}{engine} = $row->{engine};
            $self->{output}->{poller}{$row->{instance_id}}{version} = $row->{version};
        }

        $sth = $options{csdb}->query("SELECT stat_key, stat_value, stat_label 
                                      FROM nagios_stats
                                      WHERE instance_id = " . $options{cdb}->quote($id) . "");

        while (my $row = $sth->fetchrow_hashref()) {
	        $self->{output}->{poller}->{$id}->{engine_stats}->{$row->{stat_label}}->{$row->{stat_key}} = $row->{stat_value}; 
        }

        $self->{output}->{global}->{hosts_by_poller_avg} = ($self->{output}->{global}->{count_poller} != 0) ? $self->{output}->{global}->{count_hosts} / $self->{output}->{global}->{count_poller} : '0';
        $self->{output}->{global}->{services_by_poller_avg} = ($self->{output}->{global}->{count_poller} != 0) ? $self->{output}->{global}->{count_services} / $self->{output}->{global}->{count_poller} : '0';
        $self->{output}->{global}->{services_by_host_avg} = ($self->{output}->{global}->{count_hosts} != 0) ? $self->{output}->{global}->{count_services} / $self->{output}->{global}->{count_hosts} : '0';
        $self->{output}->{global}->{metrics_by_service_avg} = ($self->{output}->{global}->{count_services} != 0) ? $self->{output}->{global}->{count_metrics} / $self->{output}->{global}->{count_services} : '0';
    }
}
     

sub run {
    my $self = shift;
    my ($centreon_db, $centstorage_db, $centreon_version) = @_;

    my $query_misc = {   count_pp => [$centreon_db, $centreon_version eq '2.7' ? "SELECT count(*) FROM mod_pluginpack" : "SELECT count(*) FROM mod_ppm_pluginpack"],
                            count_downtime => [$centreon_db, "SELECT count(*) FROM downtime"],
                            count_modules => [$centreon_db, "SELECT count(*) FROM modules_informations"],
                            centreon_version => [$centreon_db, "SELECT value FROM informations LIMIT 1"],
                            count_metrics => [$centstorage_db, "SELECT count(*) FROM metrics"] };

    foreach my $info (keys %$query_misc) {
        my $result = $self->query_misc(cdb => $query_misc->{$info}[0],
                                        query => $query_misc->{$info}[1] );
        $self->{output}->{global}->{$info} = $result;
    }

    $self->get_servers_informations(cdb => $centreon_db, csdb => $centstorage_db);

    return $self->{output}
}

1;
