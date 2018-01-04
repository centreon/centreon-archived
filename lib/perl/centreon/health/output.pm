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

package centreon::health::output;

use strict;
use warnings;
use JSON;

sub new {
    my $class = shift;
    my $self = {};

    bless $self, $class;
    return $self;
}

sub output_text {
    my ($self, %options) = @_;

    my $output; 

    $output = "\t ===CENTREON_HEALTH TEXT OUTPUT=== \n\n";
    $output .=  "\t\t CENTREON OVERVIEW\n\n";
    $output .=  "Centreon Version: " . $options{data}->{server}->{global}->{centreon_version} . "\n";
    $output .=  "Number of pollers: " . $options{data}->{server}->{global}->{count_poller} . "\n";
    $output .=  "Number of hosts: " . $options{data}->{server}->{global}->{count_hosts} . "\n";
    $output .=  "Number of services: " . $options{data}->{server}->{global}->{count_services} . "\n";
    $output .=  "Number of metrics: " . $options{data}->{server}->{global}->{count_metrics} . "\n";
    $output .=  "Number of modules: " . $options{data}->{server}->{global}->{count_modules} . "\n";
    $output .=  defined($options{data}->{server}->{global}->{count_pp}) ? "Number of plugin-packs: " . $options{data}->{server}->{global}->{count_pp} . "\n" : " Number of plugin-packs: N/A\n";
    $output .=  "Number of recurrent downtimes: " . $options{data}->{server}->{global}->{count_downtime} . "\n\n";

    $output .=  "\t\t AVERAGE METRICS\n\n";
    $output .=  "Host / poller (avg): " . $options{data}->{server}->{global}->{hosts_by_poller_avg} . "\n";
    $output .=  "Service / poller (avg): " . $options{data}->{server}->{global}->{services_by_poller_avg} . "\n";
    $output .=  "Service / host (avg): " . $options{data}->{server}->{global}->{services_by_host_avg} . "\n";
    $output .=  "Metrics / service (avg): " . $options{data}->{server}->{global}->{metrics_by_service_avg} . "\n\n";

    if ($options{flag_rrd} != 1 || $options{flag_db} eq "") {
        $output .=  "\t\t RRD INFORMATIONS\n\n";
        $output .= "RRD not updated since more than 180 days: " .  $options{data}->{rrd}->{rrd_not_updated_since_180d} . "\n";
        $output .= "RRD written during last 5 five minutes: " .  $options{data}->{rrd}->{rrd_written_last_5m} . "\n";
        foreach my $key (sort keys %{$options{data}->{rrd}}) {
	    next if ($key =~ m/^rrd_/);
            $output .= "RRD files Count/Size in " . $key . " directory: " . $options{data}->{rrd}->{$key}->{count} . "/" . $options{data}->{rrd}->{$key}->{size} . "\n";
        }
	$output .= "\n";
    }

    if ($options{flag_db} != 1 || $options{flag_db} eq "") {
        $output .=  "\t\t DATABASES INFORMATIONS\n\n";
        $output .= "\tDatabases size\n\n";
        foreach my $database (keys %{$options{data}->{database}->{db_size}}) { 
	    $output .= "Size of " . $database . " database: " . $options{data}->{database}->{db_size}->{$database} . "\n";
        }
        $output .= "\n";
        $output .= "\tTables size (centreon_storage db)\n\n";
        foreach my $database (keys %{$options{data}->{database}->{table_size}}) {
            $output .= "Size of " . $database . " table: " . $options{data}->{database}->{table_size}->{$database} . "\n";
        }
        $output .= "\n";
        $output .= "\tPartitioning check\n\n";
        foreach my $table (keys %{$options{data}->{database}->{partitioning_last_part}}) {
            $output .= "Last partition date for " . $table . " table: " . $options{data}->{database}->{partitioning_last_part}->{$table} . "\n";
        }
        $output .= "\n";
    }
   
    $output .= "\t\t MODULE INFORMATIONS\n\n";
    foreach my $module_key (keys %{$options{data}->{module}}) {
	$output .= "Module " . $options{data}->{module}->{$module_key}->{full_name} . " is installed. (Author: " . $options{data}->{module}->{$module_key}->{author} . " # Codename: " . $module_key . " # Version: " . $options{data}->{module}->{$module_key}->{version} . ")\n";
    }
    $output .= "\n";

    $output .= "\t\t CENTREON NODES INFORMATIONS\n\n";
    
    foreach my $poller_id (keys %{$options{data}->{server}->{poller}}) {
        $output .= "\t" . $options{data}->{server}->{poller}->{$poller_id}->{name} . "\n\n";
	$output .= "Identity: \n";
	if (defined($options{data}->{server}->{poller}->{$poller_id}->{engine}) && defined($options{data}->{server}->{poller}->{$poller_id}->{version})) { 
  	    $output .= "    Engine (version): " . $options{data}->{server}->{poller}->{$poller_id}->{engine} . " (" . $options{data}->{server}->{poller}->{$poller_id}->{version} . ")\n";
            $output .= "    IP Address (SSH port): " . $options{data}->{server}->{poller}->{$poller_id}->{address} . " (" . $options{data}->{server}->{poller}->{$poller_id}->{ssh_port} . ")\n";
	    $output .= "    Localhost: " . $options{data}->{server}->{poller}->{$poller_id}->{localhost} . "\n"; 
            $output .= "    Running: " . $options{data}->{server}->{poller}->{$poller_id}->{running} . "\n";
            $output .= "    Start time: " . $options{data}->{server}->{poller}->{$poller_id}->{start_time} . "\n";
            $output .= "    Last alive: " . $options{data}->{server}->{poller}->{$poller_id}->{last_alive} . "\n";
            $output .= "    Uptime: " . $options{data}->{server}->{poller}->{$poller_id}->{uptime} . "\n";
            $output .= "    Count Hosts/Services - (Last command check): " . $options{data}->{server}->{poller}->{$poller_id}->{hosts} . "/" . $options{data}->{server}->{poller}->{$poller_id}->{services} . " - (" . $options{data}->{server}->{poller}->{$poller_id}->{last_command_check} . ")\n\n";
        } else {
	    $output .= "    SKIP Identity for this poller, enabled but does not seems to work correctly ! \n\n";
        }

        $output .= "Engine stats: \n";
	foreach my $stat_key (sort keys %{$options{data}->{server}->{poller}->{$poller_id}->{engine_stats}}) {
	    foreach my $stat_value (sort keys %{$options{data}->{server}->{poller}->{$poller_id}->{engine_stats}->{$stat_key}}) {
            $output .= "    " . $stat_key . "(" . $stat_value . "): " . $options{data}->{server}->{poller}->{$poller_id}->{engine_stats}->{$stat_key}->{$stat_value} . "\n";
	    }
	}
	$output .= "\n";

        $output .= "Broker stats: \n";
        foreach my $broker_stat_file (sort keys %{$options{data}->{broker}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}}) {
   	    $output .= "    \tFile: " . $broker_stat_file . "\n";
            $output .= "    Version: " . $options{data}->{broker}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}->{$broker_stat_file}->{version} . "\n";
	    $output .= "    State: " . $options{data}->{broker}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}->{$broker_stat_file}->{state} . "\n";
	    $output .= "    Event proecessing speed " . $options{data}->{broker}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}->{$broker_stat_file}->{event_processing_speed} . "\n";
            $output .= "    Queued events: " . $options{data}->{broker}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}->{$broker_stat_file}->{queued_events} . "\n";
            $output .= "    Last connection attempts: " . $options{data}->{broker}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}->{$broker_stat_file}->{last_connection_attempt} . "\n";
            $output .= "    Last connection success: " . $options{data}->{broker}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}->{$broker_stat_file}->{last_connection_success} . "\n\n";
	}

	$output .= "System stats: \n"; 
        $output .= defined($options{data}->{systems}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}->{cpu_usage}) ? 
			"    CPU => " . $options{data}->{systems}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}->{cpu_usage} . "\n" :
                        "    CPU => Could not gather data \n";
        $output .= defined($options{data}->{systems}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}->{load}) ? 
			"    LOAD => " . $options{data}->{systems}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}->{load} . "\n" :
			"    LOAD => Could not gather data \n";
        $output .= defined($options{data}->{systems}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}->{mem_usage}) ?
			"    MEMORY => " . $options{data}->{systems}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}->{mem_usage} . "\n" :
                        "    MEMORY => Could not gather data \n";
        $output .= defined($options{data}->{systems}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}->{swap_usage}) ? 
			"    SWAP => " . $options{data}->{systems}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}->{swap_usage} . "\n" :
			"    SWAP => Could not gather data \n";
        $output .= defined($options{data}->{systems}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}->{storage_usage}) ? 
			"    STORAGE => " . $options{data}->{systems}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}->{storage_usage} . "\n\n" :
                        "    STORAGE => Could not gather data \n\n";

	if ($options{flag_logs} != 1 || $options{flag_logs} eq "") {
            $output .= "\t\t LOGS LAST LINES: \n\n";
            foreach my $log_topic (sort keys %{$options{data}->{logs}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}}) {
	        foreach my $log_file (keys %{$options{data}->{logs}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}->{$log_topic}}) {
	            $output .= "    " . $log_file . " (" . $log_topic . ")\n\n";
		    $output .= $options{data}->{logs}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}->{$log_topic}->{$log_file} . "\n\n"; 
	        }
	        $output .= "\n";
	    }
        }
    }
    return $output;
}

sub output_markdown {
    my ($self, %options) = @_;

    my $output;

    $output = "# CENTREON_HEALTH TEXT OUTPUT\n";
    $output .=  "## CENTREON OVERVIEW\n\n";
    $output .=  "  + Centreon Version: " . $options{data}->{server}->{global}->{centreon_version} . "\n";
    $output .=  "  + Number of pollers: " . $options{data}->{server}->{global}->{count_poller} . "\n";
    $output .=  "  + Number of hosts: " . $options{data}->{server}->{global}->{count_hosts} . "\n";
    $output .=  "  + Number of services: " . $options{data}->{server}->{global}->{count_services} . "\n";
    $output .=  "  + Number of metrics: " . $options{data}->{server}->{global}->{count_metrics} . "\n";
    $output .=  "  + Number of modules: " . $options{data}->{server}->{global}->{count_modules} . "\n";
    $output .=  defined($options{data}->{server}->{global}->{count_pp}) ? "  + Number of plugin-packs: " . $options{data}->{server}->{global}->{count_pp} . "\n" : "  + Number of plugin-packs: N/A\n";
    $output .=  "  + Number of recurrent downtimes: " . $options{data}->{server}->{global}->{count_downtime} . "\n\n";
    $output .=  "## AVERAGE METRICS\n\n";
    $output .=  "  + Host / poller (avg): " . $options{data}->{server}->{global}->{hosts_by_poller_avg} . "\n";
    $output .=  "  + Service / poller (avg): " . $options{data}->{server}->{global}->{services_by_poller_avg} . "\n";
    $output .=  "  + Service / host (avg): " . $options{data}->{server}->{global}->{services_by_host_avg} . "\n";
    $output .=  "  + Metrics / service (avg): " . $options{data}->{server}->{global}->{metrics_by_service_avg} . "\n\n";

    if ($options{flag_rrd} != 1 || $options{flag_db} eq "") {
        $output .=  "## RRD INFORMATIONS\n\n";
        $output .= "  + RRD not updated since more than 180 days: " .  $options{data}->{rrd}->{rrd_not_updated_since_180d} . "\n";
        $output .= "  + RRD written during last 5 five minutes: " .  $options{data}->{rrd}->{rrd_written_last_5m} . "\n";
        foreach my $key (sort keys %{$options{data}->{rrd}}) {
            next if ($key =~ m/^rrd_/);
            $output .= "  + RRD files Count/Size in " . $key . " directory: " . $options{data}->{rrd}->{$key}->{count} . "/" . $options{data}->{rrd}->{$key}->{size} . "\n";
        }
        $output .= "\n";
    }

    if ($options{flag_db} != 1 || $options{flag_db} eq "") {
        $output .=  "## DATABASES INFORMATIONS\n\n";
        $output .= "### Databases size\n\n";
        foreach my $database (keys %{$options{data}->{database}->{db_size}}) {
            $output .= "  + Size of " . $database . " database: " . $options{data}->{database}->{db_size}->{$database} . "\n";
        }
        $output .= "\n";
        $output .= "### Tables size (centreon_storage db)\n\n";
        foreach my $database (keys %{$options{data}->{database}->{table_size}}) {
            $output .= "  + Size of " . $database . " table: " . $options{data}->{database}->{table_size}->{$database} . "\n";
        }
        $output .= "\n";
        $output .= "### Partitioning check\n\n";
        foreach my $table (keys %{$options{data}->{database}->{partitioning_last_part}}) {
            $output .= "  + Last partition date for " . $table . " table: " . $options{data}->{database}->{partitioning_last_part}->{$table} . "\n";
        }
        $output .= "\n";
    }

    $output .= "## MODULE INFORMATIONS\n\n";
    foreach my $module_key (keys %{$options{data}->{module}}) {
        $output .= "  + Module " . $options{data}->{module}->{$module_key}->{full_name} . " is installed. (Author: " . $options{data}->{module}->{$module_key}->{author} . " # Codename: " . $module_key . " # Version: " . $options{data}->{module}->{$module_key}->{version} . ")\n";
    }
    $output .= "\n";

    $output .= "## CENTREON NODES INFORMATIONS\n\n";

    foreach my $poller_id (keys %{$options{data}->{server}->{poller}}) {
        $output .= "### " . $options{data}->{server}->{poller}->{$poller_id}->{name} . "\n\n";
        $output .= "#### Identity: \n";
        if (defined($options{data}->{server}->{poller}->{$poller_id}->{engine}) && defined($options{data}->{server}->{poller}->{$poller_id}->{version})) {
            $output .= "  + Engine (version): " . $options{data}->{server}->{poller}->{$poller_id}->{engine} . " (" . $options{data}->{server}->{poller}->{$poller_id}->{version} . ")\n";
            $output .= "  + IP Address (SSH port): " . $options{data}->{server}->{poller}->{$poller_id}->{address} . " (" . $options{data}->{server}->{poller}->{$poller_id}->{ssh_port} . ")\n";
            $output .= "  + Localhost: " . $options{data}->{server}->{poller}->{$poller_id}->{localhost} . "\n";
            $output .= "  + Running: " . $options{data}->{server}->{poller}->{$poller_id}->{running} . "\n";
            $output .= "  + Start time: " . $options{data}->{server}->{poller}->{$poller_id}->{start_time} . "\n";
            $output .= "  + Last alive: " . $options{data}->{server}->{poller}->{$poller_id}->{last_alive} . "\n";
            $output .= "  + Uptime: " . $options{data}->{server}->{poller}->{$poller_id}->{uptime} . "\n";
            $output .= "  + Count Hosts/Services - (Last command check): " . $options{data}->{server}->{poller}->{$poller_id}->{hosts} . "/" . $options{data}->{server}->{poller}->{$poller_id}->{services} . " - (" . $options{data}->{server}->{poller}->{$poller_id}->{last_command_check} . ")\n\n";

        } else {
            $output .= "  + SKIP Identity for this poller, enabled but does not seems to work correctly ! \n\n";
        }

        $output .= "#### Engine stats: \n";
        foreach my $stat_key (sort keys %{$options{data}->{server}->{poller}->{$poller_id}->{engine_stats}}) {
            foreach my $stat_value (sort keys %{$options{data}->{server}->{poller}->{$poller_id}->{engine_stats}->{$stat_key}}) {
            $output .= "  + " . $stat_key . "(" . $stat_value . "): " . $options{data}->{server}->{poller}->{$poller_id}->{engine_stats}->{$stat_key}->{$stat_value} . "\n";
            }
        }
        $output .= "\n";

        $output .= "#### Broker stats: \n";
        foreach my $broker_stat_file (sort keys %{$options{data}->{broker}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}}) {
            $output .= "##### File: " . $broker_stat_file . "\n";
            $output .= "  + Version: " . $options{data}->{broker}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}->{$broker_stat_file}->{version} . "\n";
            $output .= "  + State: " . $options{data}->{broker}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}->{$broker_stat_file}->{state} . "\n";
            $output .= "  + Event proecessing speed " . $options{data}->{broker}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}->{$broker_stat_file}->{event_processing_speed} . "\n";
            $output .= "  + Queued events: " . $options{data}->{broker}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}->{$broker_stat_file}->{queued_events} . "\n";
            $output .= "  + Last connection attempts: " . $options{data}->{broker}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}->{$broker_stat_file}->{last_connection_attempt} . "\n";
            $output .= "  + Last connection success: " . $options{data}->{broker}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}->{$broker_stat_file}->{last_connection_success} . "\n\n";
        }

        $output .= "#### System stats: \n";
        $output .= defined($options{data}->{systems}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}->{cpu_usage}) ?
                        "  + CPU => " . $options{data}->{systems}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}->{cpu_usage} . "\n" :
                        "  + CPU => Could not gather data \n";
        $output .= defined($options{data}->{systems}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}->{load}) ?
                        "  + LOAD => " . $options{data}->{systems}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}->{load} . "\n" :
                        "  + LOAD => Could not gather data \n";
        $output .= defined($options{data}->{systems}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}->{mem_usage}) ?
                        "  + MEMORY => " . $options{data}->{systems}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}->{mem_usage} . "\n" :
                        "  + MEMORY => Could not gather data \n";
        $output .= defined($options{data}->{systems}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}->{swap_usage}) ?
                        "  + SWAP => " . $options{data}->{systems}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}->{swap_usage} . "\n" :
                        "  + SWAP => Could not gather data \n";
        $output .= defined($options{data}->{systems}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}->{storage_usage}) ?
                        "  + STORAGE => " . $options{data}->{systems}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}->{storage_usage} . "\n\n" :
                        "  + STORAGE => Could not gather data \n\n";

        if ($options{flag_logs} != 1 || $options{flag_logs} eq "") {
            $output .= "## LOGS LAST LINES: \n\n";
            foreach my $log_topic (sort keys %{$options{data}->{logs}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}}) {
                foreach my $log_file (keys %{$options{data}->{logs}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}->{$log_topic}}) {
                    $output .= "  + " . $log_file . " (" . $log_topic . ")\n\n";
                    $output .= $options{data}->{logs}->{$options{data}->{server}->{poller}->{$poller_id}->{name}}->{$log_topic}->{$log_file} . "\n\n";
                }
                $output .= "\n";
            }
        }
    }
    return $output;
}


sub run {
    my $self = shift;
    my ($data, $format, $flag_rrd, $flag_db, $flash_logs) = @_;

    if ($format eq "JSON") {
	my $output = JSON->new->encode($data);
	print $output;
    } elsif ($format eq "TEXT") {
	my $output = $self->output_text(data => $data, flag_rrd => $flag_rrd, flag_db => $flag_db, flag_logs => $flash_logs);
	print $output;
    } elsif ($format eq "MARKDOWN") {
        my $output = $self->output_markdown(data => $data, flag_rrd => $flag_rrd, flag_db => $flag_db, flag_logs => $flash_logs);
        print $output;
    } elsif ($format eq "DUMPER") {
	use Data::Dumper;
	print Dumper($data);
    }
}
        
1;
