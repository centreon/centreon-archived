
package centreon::script::nagiosPerfTrace;

use strict;
use warnings;
use RRDs;
use File::Path qw(mkpath);
use centreon::script;

use base qw(centreon::script);

sub new {
    my $class = shift;
    my $self = $class->SUPER::new("nagiosPerfTrace",
        centreon_db_conn => 1,
        centstorage_db_conn => 1,
        noroot => 1
    );

    bless $self, $class;
    $self->{sshOptions} = "-o ConnectTimeout=5 -o StrictHostKeyChecking=yes -o PreferredAuthentications=publickey -o ServerAliveInterval=10 -o ServerAliveCountMax=3 -o Compression=yes ";
    $self->{interval} = 300;
    $self->{heartbeatFactor} = 10;
    $self->{heartbeat} = $self->{interval} * $self->{heartbeatFactor};
    $self->{number} = undef;
    
    $self->{global_cmd_buffer} = undef;
    $self->{global_active_service_latency} = undef;
    $self->{global_active_service_execution} = undef;
    $self->{global_active_service_last} = undef;
    $self->{global_services_states} = undef;
    $self->{global_active_host_latency} = undef;
    $self->{global_active_host_execution} = undef;
    $self->{global_active_host_last} = undef;
    $self->{global_hosts_states} = undef;
    return $self;
}

sub trim($) {
    my $string = shift;
    $string =~ s/^\s+//;
    $string =~ s/\s+$//;
    return $string;
}

sub rrd_process {
    my $self = shift;
    my($str, $is_localhost, $must_update_ds, $ns_id) = @_;
    my @tab;
    my $match;
    my $j;
    my $error;
    my $query_str;

    $str =~ s/\n/:/g;
    @tab = split(/:/, $str);
    #chomp(@tab);
    $j = 0;

    $query_str = "";
    foreach $match (@tab) {     
        if ($match =~ /Used\/High\/Total Command Buffers/) {
            $tab[$j+1] = trim($tab[$j+1]);
            $tab[$j+1] =~ /([0-9\.]*)\ \/\ ([0-9\.]*)\ \/\ ([0-9\.]*)/;

            if (!-e $self->{global_cmd_buffer}) {
                RRDs::create($self->{global_cmd_buffer}, "-s $self->{interval}", "DS:In_Use:GAUGE:$self->{interval}:0:U", "DS:Max_Used:GAUGE:$self->{interval}:0:U", "DS:Total_Available:GAUGE:$self->{interval}:0:U", "RRA:AVERAGE:0.5:1:".$self->{number}, "RRA:AVERAGE:0.5:12:".$self->{number});
                RRDs::tune($self->{global_cmd_buffer}, "-h", "In_Use:$self->{heartbeat}");
                RRDs::tune($self->{global_cmd_buffer}, "-h", "Max_Used:$self->{heartbeat}");
                RRDs::tune($self->{global_cmd_buffer}, "-h", "Total_Available:$self->{heartbeat}");
            }
            RRDs::update ($self->{global_cmd_buffer}, "--template", "In_Use:Max_Used:Total_Available", "N:".$1.":".$2.":".$3);
            if (RRDs::error()) {
                $error = RRDs::error();
                $self->{logger}->writeLogError($error);
            }

            if ($query_str ne "") {
                $query_str .= ", ";
            }
            $query_str .= "('$ns_id', 'Buffer Usage', 'In_Use', '$1'), ";
            $query_str .= "('$ns_id', 'Buffer Usage', 'Max_Used', '$2'), ";
            $query_str .= "('$ns_id', 'Buffer Usage', 'Total_Available', '$3') ";
        } elsif ($match =~ /Active Service Latency/) {
            $tab[$j+1] = trim($tab[$j+1]);
            $tab[$j+1] =~ /([0-9\.]*)\ \/\ ([0-9\.]*)\ \/\ ([0-9\.]*)\ [sec|%]/;
            if (!-e $self->{global_active_service_latency}) {
                RRDs::create ($self->{global_active_service_latency}, "-s $self->{interval}", "DS:Min:GAUGE:$self->{interval}:0:U", "DS:Max:GAUGE:$self->{interval}:0:U", "DS:Average:GAUGE:$self->{interval}:0:U", "RRA:AVERAGE:0.5:1:".$self->{number}, "RRA:AVERAGE:0.5:12:".$self->{number});
                RRDs::tune($self->{global_active_service_latency}, "-h", "Min:$self->{heartbeat}");
                RRDs::tune($self->{global_active_service_latency}, "-h", "Max:$self->{heartbeat}");
                RRDs::tune($self->{global_active_service_latency}, "-h", "Average:$self->{heartbeat}");
            }
            RRDs::update ($self->{global_active_service_latency}, "--template", "Min:Max:Average", "N:".$1.":".$2.":".$3);
            if (RRDs::error()) {
                $error = RRDs::error();
                $self->{logger}->writeLogError($error);
            }

            if ($query_str ne "") {
                $query_str .= ", ";
            }
            $query_str .= "('$ns_id', 'Service Check Latency', 'Min', '$1'), ";
            $query_str .= "('$ns_id', 'Service Check Latency', 'Max', '$2'), ";
            $query_str .= "('$ns_id', 'Service Check Latency', 'Average', '$3') ";
        } elsif ($match =~ /Active Service Execution Time/){
            $tab[$j+1] = trim($tab[$j+1]);
            $tab[$j+1] =~ /([0-9\.]*)\ \/\ ([0-9\.]*)\ \/\ ([0-9\.]*)\ sec/;
            if (! -e $self->{global_active_service_execution}) {
                RRDs::create ($self->{global_active_service_execution}, "-s $self->{interval}", "DS:Min:GAUGE:$self->{interval}:0:U", "DS:Max:GAUGE:$self->{interval}:0:U", "DS:Average:GAUGE:$self->{interval}:0:U", "RRA:AVERAGE:0.5:1:".$self->{number}, "RRA:AVERAGE:0.5:12:".$self->{number});
                RRDs::tune($self->{global_active_service_execution}, "-h", "Min:$self->{heartbeat}");
                RRDs::tune($self->{global_active_service_execution}, "-h", "Max:$self->{heartbeat}");
                RRDs::tune($self->{global_active_service_execution}, "-h", "Average:$self->{heartbeat}");
            }
            RRDs::update ($self->{global_active_service_execution}, "--template", "Min:Max:Average", "N:".$1.":".$2.":".$3);
            if (RRDs::error()) {
                $error = RRDs::error();
                $self->{logger}->writeLogError($error);
            }

            if ($query_str ne "") {
                $query_str .= ", ";
            }
            $query_str .= "('$ns_id', 'Service Check Execution Time', 'Min', '$1'), ";
            $query_str .= "('$ns_id', 'Service Check Execution Time', 'Max', '$2'), ";
            $query_str .= "('$ns_id', 'Service Check Execution Time', 'Average', '$3') ";

        } elsif ($match =~ /Active Services Last 1\/5\/15\/60 min/){
            $tab[$j+1] = trim($tab[$j+1]);
            $tab[$j+1] =~ /([0-9\.]*)\ \/\ ([0-9\.]*)\ \/\ ([0-9\.]*)\ \/\ ([0-9\.]*)/;
            if (!-e $self->{global_active_service_last}) {
                RRDs::create ($self->{global_active_service_last}, "-s $self->{interval}", "DS:Last_Min:GAUGE:$self->{interval}:0:U", "DS:Last_5_Min:GAUGE:$self->{interval}:0:U", "DS:Last_15_Min:GAUGE:$self->{interval}:0:U", "DS:Last_Hour:GAUGE:$self->{interval}:0:U", "RRA:AVERAGE:0.5:1:".$self->{number}, "RRA:AVERAGE:0.5:12:".$self->{number});
                RRDs::tune($self->{global_active_service_last}, "-h", "Last_Min:$self->{heartbeat}");
                RRDs::tune($self->{global_active_service_last}, "-h", "Last_5_Min:$self->{heartbeat}");
                RRDs::tune($self->{global_active_service_last}, "-h", "Last_15_Min:$self->{heartbeat}");
                RRDs::tune($self->{global_active_service_last}, "-h", "Last_Hour:$self->{heartbeat}");
            }
            RRDs::update ($self->{global_active_service_last}, "--template", "Last_Min:Last_5_Min:Last_15_Min:Last_Hour", "N:".$1.":".$2.":".$3.":".$4);
            if (RRDs::error()) {
                $error = RRDs::error();
                $self->{logger}->writeLogError($error);
            }

            if ($query_str ne "") {
                $query_str .= ", ";
            }
            $query_str .= "('$ns_id', 'Service Actively Checked', 'Last_minute', '$1'), ";
            $query_str .= "('$ns_id', 'Service Actively Checked', 'Last_5_minutes', '$2'), ";
            $query_str .= "('$ns_id', 'Service Actively Checked', 'Last_15_minutes', '$3'), ";
            $query_str .= "('$ns_id', 'Service Actively Checked', 'Last_hour', '$4')";

        } elsif ($match =~ /Services Ok\/Warn\/Unk\/Crit/) {     
            $tab[$j+1] = trim($tab[$j+1]);
            $tab[$j+1] =~ /([0-9\.]*)\ \/\ ([0-9\.]*)\ \/\ ([0-9\.]*)\ \/\ ([0-9\.]*)/;
            if (! -e $self->{global_services_states}) {
                RRDs::create ($self->{global_services_states}, "-s $self->{interval}", "DS:Ok:GAUGE:$self->{interval}:0:U", "DS:Warn:GAUGE:$self->{interval}:0:U", "DS:Unk:GAUGE:$self->{interval}:0:U", "DS:Crit:GAUGE:$self->{interval}:0:U", "RRA:AVERAGE:0.5:1:".$self->{number}, "RRA:AVERAGE:0.5:12:".$self->{number});
                RRDs::tune($self->{global_services_states}, "-h", "Ok:$self->{heartbeat}");
                RRDs::tune($self->{global_services_states}, "-h", "Warn:$self->{heartbeat}");
                RRDs::tune($self->{global_services_states}, "-h", "Unk:$self->{heartbeat}");
                RRDs::tune($self->{global_services_states}, "-h", "Crit:$self->{heartbeat}");
            }
            RRDs::update ($self->{global_services_states}, "--template", "Ok:Warn:Unk:Crit", "N:".$1.":".$2.":".$3.":".$4);
            if (RRDs::error()) {
                $error = RRDs::error();
                $self->{logger}->writeLogError($error);
            }

            if ($query_str ne "") {
                $query_str .= ", ";
            }
            $query_str .= "('$ns_id', 'Services Status', 'OK', '$1'), ";
            $query_str .= "('$ns_id', 'Services Status', 'Warning', '$2'), ";
            $query_str .= "('$ns_id', 'Services Status', 'Unknown', '$3'), ";
            $query_str .= "('$ns_id', 'Services Status', 'Critical', '$4')";

        } elsif($match =~ /Active Host Latency/){
            $tab[$j+1] = trim($tab[$j+1]);
            $tab[$j+1] =~ /([0-9\.]*)\ \/\ ([0-9\.]*)\ \/\ ([0-9\.]*)\ [sec|%]/;
            if (! -e $self->{global_active_host_latency}) {
                RRDs::create ($self->{global_active_host_latency}, "-s $self->{interval}", "DS:Min:GAUGE:$self->{interval}:0:U", "DS:Max:GAUGE:$self->{interval}:0:U", "DS:Average:GAUGE:$self->{interval}:0:U", "RRA:AVERAGE:0.5:1:".$self->{number}, "RRA:AVERAGE:0.5:12:".$self->{number});
                RRDs::tune($self->{global_active_host_latency}, "-h", "Min:$self->{heartbeat}");
                RRDs::tune($self->{global_active_host_latency}, "-h", "Max:$self->{heartbeat}");
                RRDs::tune($self->{global_active_host_latency}, "-h", "Average:$self->{heartbeat}");
            }
            RRDs::update ($self->{global_active_host_latency}, "--template", "Min:Max:Average", "N:".$1.":".$2.":".$3);
            if (RRDs::error()) {
                $error = RRDs::error();
                $self->{logger}->writeLogError($error);
            }

            if ($query_str ne "") {
                $query_str .= ", ";
            }
            $query_str .= "('$ns_id', 'Host Check Latency', 'Min', '$1'), ";
            $query_str .= "('$ns_id', 'Host Check Latency', 'Max', '$2'), ";
            $query_str .= "('$ns_id', 'Host Check Latency', 'Average', '$3')";

        } elsif ($match =~ /Active Host Execution Time/){
            $tab[$j+1] = trim($tab[$j+1]);
            $tab[$j+1] =~ /([0-9\.]*)\ \/\ ([0-9\.]*)\ \/\ ([0-9\.]*)\ sec/;
            if (! -e $self->{global_active_host_execution}) {
                RRDs::create ($self->{global_active_host_execution}, "-s $self->{interval}", "DS:Min:GAUGE:$self->{interval}:0:U", "DS:Max:GAUGE:$self->{interval}:0:U", "DS:Average:GAUGE:$self->{interval}:0:U", "RRA:AVERAGE:0.5:1:".$self->{number}, "RRA:AVERAGE:0.5:12:".$self->{number});
                RRDs::tune($self->{global_active_host_execution}, "-h", "Min:$self->{heartbeat}");
                RRDs::tune($self->{global_active_host_execution}, "-h", "Max:$self->{heartbeat}");
                RRDs::tune($self->{global_active_host_execution}, "-h", "Average:$self->{heartbeat}");
            }
            RRDs::update ($self->{global_active_host_execution}, "--template", "Min:Max:Average", "N:".$1.":".$2.":".$3);
            if (RRDs::error()) {
                $error = RRDs::error();
                $self->{logger}->writeLogError($error);
            }

            if ($query_str ne "") {
                $query_str .= ", ";
            }
            $query_str .= "('$ns_id', 'Host Check Execution Time', 'Min', '$1'), ";
            $query_str .= "('$ns_id', 'Host Check Execution Time', 'Max', '$2'), ";
            $query_str .= "('$ns_id', 'Host Check Execution Time', 'Average', '$3')";

        } elsif($match =~ /Active Hosts Last 1\/5\/15\/60 min/){
            $tab[$j+1] = trim($tab[$j+1]);
            $tab[$j+1] =~ /([0-9\.]*)\ \/\ ([0-9\.]*)\ \/\ ([0-9\.]*)\ \/\ ([0-9\.]*)/;
            if (!-e $self->{global_active_host_last}) {
                RRDs::create ($self->{global_active_host_last}, "-s $self->{interval}", "DS:Last_Min:GAUGE:$self->{interval}:0:U", "DS:Last_5_Min:GAUGE:$self->{interval}:0:U", "DS:Last_15_Min:GAUGE:$self->{interval}:0:U", "DS:Last_Hour:GAUGE:$self->{interval}:0:U", "RRA:AVERAGE:0.5:1:".$self->{number}, "RRA:AVERAGE:0.5:12:".$self->{number});
                RRDs::tune($self->{global_active_host_last}, "-h", "Last_Min:$self->{heartbeat}");
                RRDs::tune($self->{global_active_host_last}, "-h", "Last_5_Min:$self->{heartbeat}");
                RRDs::tune($self->{global_active_host_last}, "-h", "Last_15_Min:$self->{heartbeat}");
                RRDs::tune($self->{global_active_host_last}, "-h", "Last_Hour:$self->{heartbeat}");
            }
            RRDs::update ($self->{global_active_host_last}, "--template", "Last_Min:Last_5_Min:Last_15_Min:Last_Hour", "N:".$1.":".$2.":".$3.":".$4);
            if (RRDs::error()) {
                $error = RRDs::error();
                $self->{logger}->writeLogError($error);
            }

            if ($query_str ne "") {
                $query_str .= ", ";
            }
            $query_str .= "('$ns_id', 'Host Actively Checked', 'Last_minute', '$1'), ";
            $query_str .= "('$ns_id', 'Host Actively Checked', 'Last_5_minutes', '$2'), ";
            $query_str .= "('$ns_id', 'Host Actively Checked', 'Last_15_minutes', '$3'), ";
            $query_str .= "('$ns_id', 'Host Actively Checked', 'Last_hour', '$4')";
        } elsif ($match =~ /Hosts Up\/Down\/Unreach/){
            $tab[$j+1] = trim($tab[$j+1]);
            $tab[$j+1] =~ /([0-9\.]*)\ \/\ ([0-9\.]*)\ \/\ ([0-9\.]*)/;
            if (!-e $self->{global_hosts_states}) {
                RRDs::create($self->{global_hosts_states}, "-s $self->{interval}", "DS:Up:GAUGE:$self->{interval}:0:U", "DS:Down:GAUGE:$self->{interval}:0:U", "DS:Unreach:GAUGE:$self->{interval}:0:U", "RRA:AVERAGE:0.5:1:".$self->{number}, "RRA:AVERAGE:0.5:12:".$self->{number});
                RRDs::tune($self->{global_hosts_states}, "-h", "Up:$self->{heartbeat}");
                RRDs::tune($self->{global_hosts_states}, "-h", "Down:$self->{heartbeat}");
                RRDs::tune($self->{global_hosts_states}, "-h", "Unreach:$self->{heartbeat}");
            }
            RRDs::update ($self->{global_hosts_states}, "--template", "Up:Down:Unreach", "N:".$1.":".$2.":".$3);
            if (RRDs::error()) {
                $error = RRDs::error();
                $self->{logger}->writeLogError($error);
            }

            if ($query_str ne "") {
                $query_str .= ", ";
            }
            $query_str .= "('$ns_id', 'Hosts Status', 'Up', '$1'), ";
            $query_str .= "('$ns_id', 'Hosts Status', 'Down', '$2'), ";
            $query_str .= "('$ns_id', 'Hosts Status', 'Unreachable', '$3')";
        }
        $j++;
    }
    if (!$error && ($query_str ne "")) {
        my ($status, $sth) = $self->{csdb}->query("DELETE FROM `nagios_stats` WHERE instance_id = '" . $ns_id . "'");
        ($status, $sth) = $self->{csdb}->query("INSERT INTO `nagios_stats` (instance_id, stat_label, stat_key, stat_value) VALUES " . $query_str);
    }
}

sub check_dir {
    my $self = shift;
    my ($nagios_id) = @_;

    if (! -d $self->{global_prefix} . "perfmon-" . $nagios_id) {
        if (mkpath($self->{global_prefix} . "perfmon-" . $nagios_id) == 0) {
            $self->{logger}->writeLogError("Can't create directory '" . $self->{global_prefix} . "perfmon-" . $nagios_id . "': $!");
            return 0;
        }
    }

    my $tmp_prefix = $self->{global_prefix} . "perfmon-" . $nagios_id;
    $self->{global_cmd_buffer} = $tmp_prefix . "/nagios_cmd_buffer.rrd";
    $self->{global_active_service_latency} = $tmp_prefix . "/nagios_active_service_latency.rrd";
    $self->{global_active_service_execution} = $tmp_prefix . "/nagios_active_service_execution.rrd";
    $self->{global_active_service_last} = $tmp_prefix . "/nagios_active_service_last.rrd";
    $self->{global_services_states} = $tmp_prefix . "/nagios_services_states.rrd";
    $self->{global_active_host_latency} = $tmp_prefix . "/nagios_active_host_latency.rrd";
    $self->{global_active_host_execution} = $tmp_prefix . "/nagios_active_host_execution.rrd";
    $self->{global_active_host_last} = $tmp_prefix . "/nagios_active_host_last.rrd";
    $self->{global_hosts_states} = $tmp_prefix . "/nagios_hosts_states.rrd";
    return 1;
}

sub get_poller {
    my $self = shift;
    my $id;
    my $ip;
    my $ssh_port;
    my $is_localhost;
    my $cfg_item;
    my $cfg_result;
    my $cfg_dir;
    my $nagiostats_bin;
    my $must_update_ds;
    my $dataDir = $self->{centreon_config}->{VarLib} . "/log/";

    my ($status, $sth) = $self->{cdb}->query("SELECT id, ssh_port, ns_ip_address, localhost, nagiostats_bin FROM nagios_server WHERE ns_activate = 1");
    die("Quit") if ($status == -1);
    while (($id, $ssh_port, $ip, $is_localhost, $nagiostats_bin) = $sth->fetchrow_array()) {
        $must_update_ds = 0;
        my ($status2, $sth2) = $self->{cdb}->query("SELECT cfg_dir, cfg_file FROM cfg_nagios WHERE nagios_server_id = " . $id . " AND nagios_activate = '1' LIMIT 1");
        die("Quit") if ($status2 == -1);
        $cfg_result = $sth2->fetchrow_hashref();
        if (!defined($cfg_result->{cfg_dir})) {
            $self->{logger}->writeLogError("Missing monitoring engine configuration file, skipping poller.");
            next;
        }
        $cfg_dir = $cfg_result->{'cfg_dir'};
        $cfg_dir =~ s!/\$!!g;
        if ($cfg_dir eq '') {
            $self->{logger}->writeLogError("The Monitoring engine configuration dir is empty.");
            next;
        } elsif ($cfg_result->{'cfg_file'} eq '') {
            $self->{logger}->writeLogError("The Monitoring engine configuration filename is empty.");
            next;
        }
        if (!defined($nagiostats_bin) || $nagiostats_bin eq '') {
            $self->{logger}->writeLogError("The monitoring engine stat binary is empty");
            next;
        }

        my $nagiostats = '';

        if ($is_localhost){
            $nagiostats = `$nagiostats_bin -c $cfg_dir/$cfg_result->{'cfg_file'}`;
        } else {
            if (-e $dataDir . "$id/nagiostats.trace.txt") {
                if (!open(FILE, $dataDir . "$id/nagiostats.trace.txt")) {
                    $self->{logger}->writeLogError("Can't read '" . $dataDir . "$id/nagiostats.trace.txt' file: $!");
                    next;
                }
                while (<FILE>) {
                    $nagiostats .= $_."\n";
                }
                close(FILE);
                unlink($dataDir . "$id/nagiostats.trace.txt");
            } else {
                if (!defined($ssh_port) || !$ssh_port) {
                    $ssh_port = "22";
                }
                $nagiostats = `ssh $self->{sshOptions} -p $ssh_port $ip $nagiostats_bin -c $cfg_dir/$cfg_result->{'cfg_file'}`;
            }
        }
        if ($nagiostats eq '' || $nagiostats =~ m/Error reading status file/ ) {
            next;
        }
        if ($self->check_dir($id) == 1) {
            # Update Database 
            $self->rrd_process($nagiostats, $is_localhost, $must_update_ds, $id);
        }
    }
}

sub get_interval {
    my $self = shift;
    
    my ($status, $sth) = $self->{csdb}->query("SELECT len_storage_rrd FROM config LIMIT 1");
    die("Quit") if ($status == -1);
    my $data = $sth->fetchrow_hashref();
    if (defined($data->{'len_storage_rrd'})) {
        $self->{number} = $data->{'len_storage_rrd'} * 24 * 60 * 60 / $self->{interval};
    } else {
        $self->{number} = 365 * 24 * 60 * 60 / $self->{interval};
    }
}

sub run {
    my $self = shift;

    $self->SUPER::run();
    $self->{global_prefix} = $self->{centreon_config}->{VarLib} . "/nagios-perf/";
    $self->get_interval();
    
    if (! -d $self->{global_prefix}){
        die("Can't create directory '$self->{global_prefix}': $!") if (mkpath($self->{global_prefix}) == 0);
    }

    $self->get_poller();
}

1;

__END__

=head1 NAME

    sample - Using GetOpt::Long and Pod::Usage

=cut
