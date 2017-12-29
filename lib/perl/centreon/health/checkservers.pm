################################################################################
# Copyright 2005-2013 Centreon
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
# As a special exception, the copyright holders of this program give Centreon 
# permission to link this program with independent modules to produce an executable, 
# regardless of the license terms of these independent modules, and to copy and 
# distribute the resulting executable under terms of Centreon choice, provided that 
# Centreon also meet, for each linked independent module, the terms  and conditions 
# of the license of that module. An independent module is a module which is not 
# derived from this program. If you modify this program, you may extend this 
# exception to your version of the program, but you are not obliged to do so. If you
# do not wish to do so, delete this exception statement from your version.
# 
#
####################################################################################

package centreon::health::checkservers;

use strict;
use warnings;
use integer;

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

    $sth = $options{cdb}->query($options{query});

    return $sth->fetchrow()
}

sub get_servers_informations {
    my ($self, %options) = @_;

    my $sth = $options{cdb}->query("SELECT id, name, localhost, ns_ip_address, ssh_port
                                   FROM nagios_server");
    while (my $row = $sth->fetchrow_hashref()) {
        $self->{output}->{poller}->{$row->{id}}{name} = $row->{name};
        $self->{output}->{poller}->{$row->{id}}{localhost} = $row->{localhost};
        $self->{output}->{poller}->{$row->{id}}{address} = $row->{ns_ip_address};
        $self->{output}->{poller}->{$row->{id}}{ssh_port} = $row->{ssh_port};
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
            $self->{output}->{poller}{$row->{instance_id}}{running} = $row->{running};
            $self->{output}->{poller}{$row->{instance_id}}{start_time} = $row->{start_time};
            $self->{output}->{poller}{$row->{instance_id}}{last_alive} = $row->{last_alive};
            $self->{output}->{poller}{$row->{instance_id}}{last_command_check} = $row->{last_command_check};
            $self->{output}->{poller}{$row->{instance_id}}{engine} = $row->{engine};
            $self->{output}->{poller}{$row->{instance_id}}{version} = $row->{version};
        }

	$self->{output}->{global}->{hosts_by_poller_avg} = $self->{output}->{global}->{count_hosts}/$self->{output}->{global}->{count_poller};
        $self->{output}->{global}->{services_by_poller_avg} = $self->{output}->{global}->{count_services}/$self->{output}->{global}->{count_poller};
        $self->{output}->{global}->{services_by_host_avg} = $self->{output}->{global}->{count_services}/$self->{output}->{global}->{count_hosts};
        $self->{output}->{global}->{metrics_by_service_avg} = $self->{output}->{global}->{count_metrics}/$self->{output}->{global}->{count_services};      
	
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

    foreach my $info (keys $query_misc) {
        my $result = $self->query_misc(cdb => $query_misc->{$info}[0],
                                       query => $query_misc->{$info}[1] );
        $self->{output}->{global}->{$info} = $result;
    }

    $self->get_servers_informations(cdb => $centreon_db, csdb => $centstorage_db);
   
    return $self->{output}
}

1;
