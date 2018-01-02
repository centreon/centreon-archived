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

package centreon::health::checklogs;

use strict;
use warnings;
use POSIX qw(strftime);
use centreon::common::misc;
use centreon::health::ssh;

sub new {
    my $class = shift;
    my $self = {};
    $self->{logs_path_broker} = {};
    $self->{logs_path_engine} = {};
    $self->{output} = {};

    bless $self, $class;
    return $self;
}

sub run {
    my $self = shift;
    my ($centreon_db, $server_list, $centreon_version, $logger) = @_;

    my $sth;
    my ($lerror, $stdout);

    foreach my $server (keys %$server_list) {
        $sth = $centreon_db->query("SELECT log_file
                                     FROM cfg_nagios
                                     WHERE nagios_id=" . $centreon_db->quote($server));

        while (my $row = $sth->fetchrow_hashref) {
            push @{$self->{logs_path_engine}->{engine}->{$server_list->{$server}->{name}}}, $row->{log_file};
        }

        foreach my $log_file (@{$self->{logs_path_engine}->{engine}->{$server_list->{$server}->{name}}}) {
            if ($server_list->{$server}->{localhost} eq "YES") {
                ($lerror, $self->{output}->{$server_list->{$server}->{name}}->{engine}->{$log_file}) = centreon::common::misc::backtick(command => "tail -n20 " . $log_file);
            } else {
                $self->{output}->{$server_list->{$server}->{name}}->{engine}->{$log_file} = centreon::health::ssh->new->main(host => $server_list->{$server}->{address},
 	 	                                                                                                             port => $server_list->{$server}->{ssh_port},
           	   	                                                                                                     userdata => $log_file,
                             		                                                                                     command => "tail -n20 " . $log_file);
            }
        }

        $sth = $centreon_db->query("SELECT DISTINCT(config_value) FROM cfg_centreonbroker, cfg_centreonbroker_info
                                            WHERE config_group='logger' AND config_key='name'
                                            AND cfg_centreonbroker.config_id=cfg_centreonbroker_info.config_id
                                            AND cfg_centreonbroker.ns_nagios_server=" . $centreon_db->quote($server));

        while (my $row = $sth->fetchrow_hashref) {
            push @{$self->{logs_path_broker}->{broker}->{$server_list->{$server}->{name}}}, $row->{config_value};
        }

        foreach my $log_file (@{$self->{logs_path_broker}->{broker}->{$server_list->{$server}->{name}}}) {
            if ($server_list->{$server}->{localhost} eq "YES") {
                ($lerror, $self->{output}->{$server_list->{$server}->{name}}->{broker}->{$log_file}) = centreon::common::misc::backtick(command => "tail -n20 " . $log_file);
            } else {
                $self->{output}->{$server_list->{$server}->{name}}->{broker}->{$log_file} = centreon::health::ssh->new->main(host => $server_list->{$server}->{address},
                                                                              			                             port => $server_list->{$server}->{ssh_port},
                                                                                                		             userdata => $log_file,
                                                                                                         		     command => "tail -n20 " . $log_file);
            }
        }

	if ($server_list->{$server}->{localhost} eq "YES") {
             $sth = $centreon_db->query("SELECT `value` FROM options WHERE `key`='debug_path'");

	     my $centreon_log_path = $sth->fetchrow();
	     ($lerror, $stdout) = centreon::common::misc::backtick(command => "find " . $centreon_log_path . " -type f -name *.log");

	     foreach my $log_file (split '\n', $stdout) {
	         ($lerror, $self->{output}->{$server_list->{$server}->{name}}->{centreon}->{$log_file}) = centreon::common::misc::backtick(command => "tail -n10 " . $log_file);
	     }
	}
    }
 
    return $self->{output}
}

1;
