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

package centreon::health::checkbroker;

use strict;
use warnings;
use JSON;
use POSIX qw(strftime);
use centreon::common::misc;
use centreon::health::ssh;

sub new {
    my $class = shift;
    my $self = {};
    $self->{output} = {};

    bless $self, $class;
    return $self;
}

sub json_parsing {
    my ($self, %options) = @_;
    
    my $json_content = JSON->new->decode($options{json_content});
    foreach my $key (keys %$json_content) {
	if ($key =~ m/^endpoint/) {
	foreach my $broker_metric (keys %{$json_content->{$key}}) {
		next if ($broker_metric !~ m/version|event_processing|last_connection|queued|state/);
		$self->{output}->{$options{poller_name}}->{$options{file_name}}->{$broker_metric} = ($broker_metric =~ m/^last_connection/ && $json_content->{$key}->{$broker_metric} != -1) 
										? strftime("%m/%d/%Y %H:%M:%S",localtime($json_content->{$key}->{$broker_metric}))
										: $json_content->{$key}->{$broker_metric} ;
	    }
	} elsif ($key =~ m/version/) {
	    $self->{output}->{$options{poller_name}}->{$options{file_name}}->{$key} = $json_content->{$key};
	}

    }

    return $self->{output} 

}

sub run {
    my $self = shift;
    my ($centreon_db, $server_list, $centreon_version) = @_;

    my $sth;

    if ($centreon_version ne "2.8") {
	$self->{output}->{not_compliant} = "Incompatible file format, work only with JSON format";
	return $self->{output}
    }

    return if ($centreon_version ne "2.8");
    foreach my $server (keys %$server_list) {
	$sth = $centreon_db->query("SELECT config_name, cache_directory 
					FROM cfg_centreonbroker 
					WHERE stats_activate='1' 
					AND ns_nagios_server=".$centreon_db->quote($server)."");

	if ($server_list->{$server}->{localhost} eq "YES") {
            while (my $row = $sth->fetchrow_hashref()) {
	        my ($lerror, $stdout) = centreon::common::misc::backtick(command => "cat " . $row->{cache_directory} . "/" . $row->{config_name} . "-stats.json");
		$self->{output} = $self->json_parsing(json_content => $stdout,
						      poller_name => $server_list->{$server}->{name},
						      file_name => $row->{config_name}. "-stats.json");
	    }
	} else {
            while (my $row = $sth->fetchrow_hashref()) {
		my $stdout = centreon::health::ssh->new->main(host => $server_list->{$server}->{address},
                                                              port => $server_list->{$server}->{ssh_port},
                                                              userdata => $row->{cache_directory} . "/" . $row->{config_name} . "-stats.json",
                                                              command => "cat " . $row->{cache_directory} . "/" . $row->{config_name} . "-stats.json");
                $self->{output} = $self->json_parsing(json_content => $stdout,
                                                      poller_name => $server_list->{$server}->{name},
                                                      file_name => $row->{config_name}. "-stats.json");

            }
        }
       
    }
    return $self->{output}
}

1;
