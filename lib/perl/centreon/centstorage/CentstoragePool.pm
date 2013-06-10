
package centreon::centstorage::CentstoragePool;

use strict;
use warnings;
use centreon::common::db;
use centreon::common::misc;
use centreon::centstorage::CentstorageLib;
use centreon::centstorage::CentstorageRebuild;

my %handlers = ('TERM' => {}, 'CHLD' => {}, 'HUP' => {});
my %rrd_trans = ("g" => 0, "c" => 1, "d" => 2, "a" => 3);

sub new {
    my $class = shift;
    my $self  = {};
    $self->{"logger"} = shift;
    $self->{"rrd"} = shift;
    $self->{"rebuild_progress"} = shift;
    $self->{"dbcentreon"} = undef;
    $self->{"dbcentstorage"} = undef;

    # skip if we don't find IDS in config
    $self->{"skip_if_no_ids"} = 1;
    
    $self->{"len_storage_rrd"} = undef;
    $self->{"rrd_metrics_path"} = undef;
    $self->{"rrd_status_path"} = undef;
    $self->{"main_perfdata_file"} = undef;
    $self->{"interval_time"} = undef;
    $self->{"do_rrd_status"} = 1;
    $self->{"TIMEOUT"} = 30;
    $self->{"num_pool"} = undef;
    # If rebuild in progress, we don't try to insert in DATA_BIN

    $self->{"storage_type"} = undef;
    # { 
    #    'metrics' => {'name1' => {}, 'name2' => {} }
    #    'service_id' => 
    #    'host_id' => 
    #    'storage_type' =>
    #    'check_interval' =>
    #    'index_id' =>
    #    'rebuild' =>
    #    'rrd_retention' => 
    # }
    $self->{"cache_service"} = {};
    
    # Perfdata parsing vars
    $self->{"perfdata_pos"} = undef;
    $self->{"perfdata_size"} = undef;
    $self->{"perfdata_chars"} = undef;

    $self->{"perfdata_parser_stop"} = 0;

    $self->{"service_perfdata"} = undef;
    $self->{"metric_name"} = undef;
    $self->{"metric_value"} = undef;
    $self->{"metric_unit"} = undef;
    $self->{"metric_warn"} = undef;
    $self->{"warn_low"} = undef;
    $self->{"warn_threshold_mode"} = undef;
    $self->{"metric_crit"} = undef;
    $self->{"crit_low"} = undef;
    $self->{"crit_threshold_mode"} = undef;
    $self->{"metric_min"} = undef;
    $self->{"metric_max"} = undef;

    # By service
    $self->{"cache_services_failed"} = {};
    $self->{"last_check_failed"} = time();
    $self->{"check_failed_every"} = 60 * 10; # 20 minutes

    # Rename
    $self->{"cache_services_rename"} = {};
    $self->{"rename_rebuild_wait"} = 0;
    $self->{"rename_old_new"} = {};

    $self->{"group_databin_stmt"} = "";
    $self->{"group_databin_total"} = 0;
    $self->{"group_databin_append"} = "";
    $self->{"group_databin_max"} = 500;

    $self->{"rebuild_index_id"} = undef;
    $self->{"rebuild_key"} = undef;
    $self->{"current_pid"} = undef;

    # reload flag
    $self->{reload} = 1;
    $self->{config_file} = undef;
    
    $self->{"save_read"} = [];
    $self->{"read_select"} = undef;
    $self->{"pipe_write"} = undef;
    bless $self, $class;
    $self->set_signal_handlers;
    return $self;
}

sub set_signal_handlers {
    my $self = shift;

    $SIG{TERM} = \&class_handle_TERM;
    $handlers{'TERM'}->{$self} = sub { $self->handle_TERM() };
    $SIG{CHLD} = \&class_handle_CHLD;
    $handlers{'CHLD'}->{$self} = sub { $self->handle_CHLD() };
    $SIG{HUP} = \&class_handle_HUP;
    $handlers{HUP}->{$self} = sub { $self->handle_HUP() };
}

sub handle_HUP {
    my $self = shift;
    $self->{reload} = 0;
}

sub handle_TERM {
    my $self = shift;
    $self->{'logger'}->writeLogInfo("$$ Receiving order to stop...");

    if (defined($self->{"current_pid"})) {
        $self->{'logger'}->writeLogInfo("Send -TERM signal to rebuild process..");
        kill('TERM', $self->{"current_pid"});
    }

    ###
    # Flush Data_bin
    ###
    eval {
        local $SIG{ALRM} = sub { die "alarm\n" };
        alarm 5;
        $self->flush_mysql(1);
        alarm 0;
    };
    if ($@) {
        $self->{'dbcentstorage'}->kill();
    }
    $self->{'dbcentreon'}->disconnect() if (defined($self->{'dbcentreon'}));
    $self->{'dbcentstorage'}->disconnect() if (defined($self->{'dbcentstorage'}));

    ###
    # Flush RRD
    ###
    $self->{'rrd'}->flush_all(1);

    ###
    # Write In File
    ###
    if (open(FILE, '>> ' . $self->{"main_perfdata_file"} . "_" . $self->{'num_pool'} . ".bckp")) {
        foreach my $id (keys %{$self->{"cache_services_failed"}}) {
            foreach (@{$self->{"cache_services_failed"}->{$id}}) {
                print FILE join("\t", @$_) . "\n";
            }
        }

        # Rename
        foreach my $id (keys %{$self->{"cache_services_rename"}}) {
            foreach (@{$self->{"cache_services_rename"}->{$id}}) {
                print FILE join("\t", @$_) . "\n";
            }
        }
        

        ### Try to read pipe
        my @rh_set = $self->{'read_select'}->can_read(1);
        if (scalar(@rh_set) > 0) {
            foreach my $rh (@rh_set) {
                my $read_done = 0;
                while ((my ($status_line, $readline) = centreon::common::misc::get_line_pipe($rh, \@{$self->{'save_read'}}, \$read_done))) {
                    last if ($status_line <= 0);
                    if ($readline =~ /^UPDATE/) {
                        $readline =~ s/^UPDATE\t//;
                        print FILE $readline . "\n";
                    }
                }
            }
        }
        close FILE;
    } else {
        $self->{"logger"}->writeLogError("Cannot open " . $self->{"main_perfdata_file"} . "_" . $self->{'num_pool'} . ".bckp file : $!");
    }

    ###
    # Check Child
    ###
    my $kill_or_not = 1;
    for (my $i = 0; $i < $self->{"TIMEOUT"}; $i++) {
        if (!defined($self->{"current_pid"})) {
            $kill_or_not = 0;
            last;
        }
        sleep(1);
    }

    if ($kill_or_not == 1) {
        $self->{'logger'}->writeLogInfo("Send -KILL signal to rebuild process..");
        kill('KILL', $self->{"current_pid"});
    }
}

sub handle_CHLD {
    my $self = shift;
    my $child_pid;
    my $exit_code;

    $self->{'logger'}->writeLogInfo("Received SIGCHLD...");
    $self->{"current_pid"} = undef;
    if ($self->{"rename_rebuild_wait"} == 1) {
        my ($new_host_name, $new_service_description) = split(';', $self->{"rename_old_new"}->{$self->{"rebuild_key"}});
        $self->force_flush_rrd($self->{"rebuild_key"});
        delete $self->{"cache_service"}->{$self->{"rebuild_key"}};
        delete $self->{"cache_services_failed"}->{$self->{"rebuild_key"}};
        delete $self->{"rename_old_new"}->{$self->{"rebuild_key"}};
        $self->send_rename_finish($new_host_name, $new_service_description);
    }
    $self->rebuild_finish();
    while (($child_pid = waitpid(-1, &POSIX::WNOHANG)) > 0) {
        $exit_code = $? >> 8;
    }
    $SIG{CHLD} = \&class_handle_CHLD;
}

sub class_handle_TERM {
    foreach (keys %{$handlers{'TERM'}}) {
        &{$handlers{'TERM'}->{$_}}();
    }
    exit(0);
}

sub class_handle_CHLD {
    foreach (keys %{$handlers{'CHLD'}}) {
        &{$handlers{'CHLD'}->{$_}}();
    }
}

sub class_handle_HUP {
    foreach (keys %{$handlers{HUP}}) {
        &{$handlers{HUP}->{$_}}();
    }
}

sub reload {
    my $self = shift;
    
    $self->{logger}->writeLogInfo("Reload in progress for pool process " . $self->{num_pool} . "...");
    # reopen file
    if ($self->{logger}->is_file_mode()) {
        $self->{logger}->file_mode($self->{logger}->{file_name});
    }
    $self->{logger}->redirect_output();
    
    my ($status, $status_cdb, $status_csdb) = centreon::common::misc::reload_db_config($self->{logger}, $self->{config_file},
                                                                                       $self->{dbcentreon}, $self->{dbcentstorage});
    if ($status_cdb == 1) {
        $self->{dbcentreon}->disconnect();
        $self->{dbcentreon}->connect();
    }
    if ($status_csdb == 1) {
        $self->{dbcentstorage}->disconnect();
        $self->{dbcentstorage}->connect();
    }
    centreon::common::misc::check_debug($self->{logger}, "debug_centstorage", $self->{dbcentreon}, "centstorage pool process " . $self->{num_pool});

    $self->{reload} = 1;
}

sub add_data_mysql {
    my $self = shift;
    my ($metric_id, $ctime, $value) = @_;
    
    $self->{"group_databin_stmt"} .= $self->{"group_databin_append"} . "('$metric_id', '$ctime', '$value')";
    $self->{"group_databin_append"} = ", ";
    $self->{"group_databin_total"}++;
}

sub force_flush_rrd {
    my $self = shift;
    my ($key) = @_;

    if (defined($self->{"cache_service"}->{$key})) {
        foreach (keys %{$self->{"cache_service"}->{$key}->{'metrics'}}) {
            $self->{'rrd'}->flush_metric($self->{"cache_service"}->{$key}->{'metrics'}->{$_}->{'metric_id'});
        }

        $self->{'rrd'}->flush_status($self->{"cache_service"}->{$key}->{'index_id'});
    }
}

sub flush_mysql {
    my $self = shift;
    my ($force) = @_;
    
    return 0 if ($self->{"rebuild_progress"} == 1 && (!defined($force) || $force == 0));
    if ((defined($force) && $force == 1 && $self->{"group_databin_total"} > 0) || $self->{"group_databin_total"} > $self->{"group_databin_max"}) {
        my $rq = "INSERT INTO `data_bin` (`id_metric`, `ctime`, `value`) VALUES " . $self->{"group_databin_stmt"};
        my ($status, $stmt) = $self->{'dbcentstorage'}->query($rq);
        $self->{"group_databin_total"} = 0;
        $self->{"group_databin_append"} = "";
        $self->{"group_databin_stmt"} = "";
    }
}

sub flush_failed {
    my $self = shift;

    if (time() > ($self->{"last_check_failed"} + $self->{"check_failed_every"})) {
        # Need to reconnect (maybe a gone away. So we try)
        $self->{'dbcentreon'}->disconnect();
        $self->{'dbcentreon'}->connect();

        $self->{'logger'}->writeLogInfo("Begin Cache Services Failed");
        foreach my $id (keys %{$self->{"cache_services_failed"}}) {
            next if ($self->{"rebuild_progress"} == 1 && $id == $self->{"rebuild_key"});
            my @tmp_ar = ();
            my $lerror = 0;            
            foreach (@{$self->{"cache_services_failed"}->{$id}}) {
                if ($lerror == 0 && $self->update(1, @$_) != 0) {
                    push @tmp_ar, \@$_;
                    $lerror = 1;
                } else {
                    push @tmp_ar, \@$_;
                }
            }
            if (scalar(@tmp_ar) != 0) {
                @{$self->{"cache_services_failed"}->{$id}} = @tmp_ar;
            } else {
                delete $self->{"cache_services_failed"}->{$id};
            }
        }
        $self->{"last_check_failed"} = time();
    }
}

sub remove_special_char_metric {
    my $self = shift;
    my $remove_special_char = $_[0];

    $remove_special_char =~ s/\./\-/g;
    $remove_special_char =~ s/\,/\-/g;
    $remove_special_char =~ s/\:/\-/g;
    $remove_special_char =~ s/\ /\-/g;
    return $remove_special_char;
}

sub create_metric {
    my $self = shift;
    my ($index_id, $cache_metrics, $metric_name) = @_;

    # Check if exists already
    my ($status, $stmt) = $self->{dbcentstorage}->query("SELECT * FROM `metrics` WHERE `index_id` = '" . $index_id . "' AND `metric_name` = " . $self->{dbcentstorage}->quote($metric_name) . " LIMIT 1");
    return -1 if ($status == -1);
    my $data = $stmt->fetchrow_hashref();
    # move part for compat with old centstorage name
    if (!defined($data)) {
        ($status, $stmt) = $self->{dbcentstorage}->query("SELECT * FROM `metrics` WHERE `index_id` = '" . $index_id . "' AND `metric_name` = " . $self->{dbcentstorage}->quote($self->remove_special_char_metric($metric_name)) . " LIMIT 1");
        return -1 if ($status == -1);
        $data = $stmt->fetchrow_hashref();
        if (defined($data)) {
            ($status) = $self->{dbcentstorage}->query("UPDATE `metrics` SET `metric_name` = " . $self->{dbcentstorage}->quote($metric_name) . " WHERE `index_id` = '" . $index_id . "' AND `metric_name` = " . $self->{dbcentstorage}->quote($self->remove_special_char_metric($metric_name)) . " LIMIT 1");
            return -1 if ($status == -1);
        } else {
            # Insert
            ($status, $stmt) = $self->{dbcentstorage}->query("INSERT INTO `metrics` (`index_id`, `metric_name`, `unit_name`, `warn`, `warn_low`, `warn_threshold_mode`, `crit`, `crit_low`, `crit_threshold_mode`, `min`, `max`, `data_source_type`) VALUES ('" . $index_id . "', " . $self->{dbcentstorage}->quote($metric_name) . ", '" . $self->{metric_unit} . "', " . 
                                    $self->{dbcentstorage}->quote($self->{metric_warn}) . ", " . $self->{dbcentstorage}->quote($self->{warn_low}) . ", " . $self->{dbcentstorage}->quote($self->{warn_threshold_mode}) . ", " . 
                                    $self->{dbcentstorage}->quote($self->{metric_crit}) . ", " . $self->{dbcentstorage}->quote($self->{crit_low}) . ", " . $self->{dbcentstorage}->quote($self->{crit_threshold_mode}) . ", '" .
                                    $self->{metric_min} . "', '" . $self->{metric_max} . "', '" . $self->{metric_type} . "')");    
            return -1 if ($status);
            my $last_insert_id = $self->{dbcentstorage}->last_insert_id();
            $$cache_metrics->{$metric_name} = {metric_id => $last_insert_id, metric_unit => $self->{metric_unit}, 
                                               metric_warn => $self->{metric_warn}, warn_low => $self->{warn_low}, warn_threshold_mode => $self->{warn_threshold_mode}, 
                                               metric_crit => $self->{metric_crit}, crit_low => $self->{crit_low}, crit_threshold_mode => $self->{crit_threshold_mode},
                                               metric_min => $self->{metric_min}, metric_max => $self->{metric_max}, data_source_type => $self->{metric_type}};
            
            return 0;
        }
    }
    # We get
    $$cache_metrics->{$metric_name} = {metric_id => $data->{metric_id}, metric_unit => defined($data->{unit_name}) ? $data->{unit_name} : "", 
                                       metric_warn => defined($data->{warn}) ? $data->{warn} : "", 
                                       warn_low => defined($data->{warn_low}) ? $data->{warn_low} : "", 
                                       warn_threshold_mode => defined($data->{warn_threshold_mode}) ? $data->{warn_threshold_mode} : "",
                                       metric_crit => defined($data->{crit}) ? $data->{crit} : "", 
                                       crit_low => defined($data->{crit_low}) ? $data->{crit_low} : "", 
                                       crit_threshold_mode => defined($data->{crit_threshold_mode}) ? $data->{crit_threshold_mode} : "",
                                       metric_min => defined($data->{min}) ? $data->{min} : "", 
                                       metric_max => defined($data->{max}) ? $data->{max} : "",
                                       data_source_type => $data->{data_source_type}};
    return 0;
}

sub check_update_extra_metric {
    my $self = shift;
    my $cache_metric = $_[0];

    if ($$cache_metric->{metric_unit} ne $self->{metric_unit} ||
        $$cache_metric->{metric_warn} ne $self->{metric_warn} ||
        $$cache_metric->{warn_low} ne $self->{warn_low} ||
        $$cache_metric->{warn_threshold_mode} ne $self->{warn_threshold_mode} ||
        $$cache_metric->{metric_crit} ne $self->{metric_crit} ||
        $$cache_metric->{crit_low} ne $self->{crit_low} ||
        $$cache_metric->{crit_threshold_mode} ne $self->{crit_threshold_mode} ||
        $$cache_metric->{metric_min} ne $self->{metric_min} ||
        $$cache_metric->{metric_max} ne $self->{metric_max}) {
        $self->{dbcentstorage}->query("UPDATE `metrics` SET `unit_name` = " . $self->{dbcentstorage}->quote($self->{metric_unit}) . ", " . 
                                            "`warn` = " . $self->{dbcentstorage}->quote($self->{metric_warn}) . ", `warn_low` = " . $self->{dbcentstorage}->quote($self->{warn_low}) . ", `warn_threshold_mode` = " . $self->{dbcentstorage}->quote($self->{warn_threshold_mode}) . ", " .
                                            "`crit` = " . $self->{dbcentstorage}->quote($self->{metric_crit}) . ", `crit_low` = " . $self->{dbcentstorage}->quote($self->{crit_low}) . ", `crit_threshold_mode` = " . $self->{dbcentstorage}->quote($self->{crit_threshold_mode}) . ", " .
                                            "`min` = '" . $self->{metric_min} . "', `max` = '" . $self->{metric_max} . "' WHERE `metric_id` = " . $$cache_metric->{metric_id});
        $$cache_metric->{metric_unit} = $self->{metric_unit};
        $$cache_metric->{metric_warn} = $self->{metric_warn};
        $$cache_metric->{warn_low} = $self->{warn_low};
        $$cache_metric->{warn_threshold_mode} = $self->{warn_threshold_mode};
        $$cache_metric->{metric_crit} = $self->{metric_crit};
        $$cache_metric->{crit_low} = $self->{crit_low};
        $$cache_metric->{crit_threshold_mode} = $self->{crit_threshold_mode};
        $$cache_metric->{metric_min} = $self->{metric_min};
        $$cache_metric->{metric_max} = $self->{metric_max};
    }
}

sub send_rename_command {
    my $self = shift;
    my ($old_host_name, $old_service_description, $new_host_name, $new_service_description) = @_;
    
    $self->{logger}->writeLogInfo("Hostname/Servicename changed had been detected " . $old_host_name . "/" . $old_service_description);
    my $fh = $self->{'pipe_write'};
        print $fh "RENAMECLEAN\t" . $old_host_name . "\t" . $old_service_description . "\t" . $new_host_name . "\t" . $new_service_description . "\n";
}

sub create_service {
    my $self = shift;
    my ($host_id, $service_id, $interval, $host_name, $service_description) = @_;
    my ($status, $stmt);

    if ($host_name =~ /_Module_([a-zA-Z0-9]*)/) {
        ($status, $stmt) = $self->{'dbcentstorage'}->query("SELECT `id`, `storage_type`, `host_name`, `service_description`, `rrd_retention` FROM `index_data` WHERE `host_name` = " . $self->{'dbcentstorage'}->quote($host_name) . " AND `service_description` = " . $self->{'dbcentstorage'}->quote($service_description)  . " LIMIT 1");
    } else {
        ($status, $stmt) = $self->{'dbcentstorage'}->query("SELECT `id`, `storage_type`, `host_name`, `service_description`, `rrd_retention` FROM `index_data` WHERE `host_id` = " . $host_id . " AND `service_id` = " . $service_id . " LIMIT 1");
    }    
    return -1 if ($status == -1);
    my $data = $stmt->fetchrow_hashref();
    if (defined($data) && ($data->{'host_name'} ne $host_name || $data->{'service_description'} ne $service_description)) {
        ($status, $stmt) = $self->{'dbcentstorage'}->query("UPDATE `index_data` SET `host_name` = " . $self->{'dbcentstorage'}->quote($host_name) . ", `service_description` = " . $self->{'dbcentreon'}->quote($service_description) . " WHERE id = " . $data->{'id'});
        if ($status != -1) {
            $self->{"cache_service"}->{$host_name . ";" . $service_description} = {};
            $self->{"cache_service"}->{$host_name . ";" . $service_description}->{'metrics'} = {};
            $self->{"cache_service"}->{$host_name . ";" . $service_description}->{'service_id'} = $service_id;
            $self->{"cache_service"}->{$host_name . ";" . $service_description}->{'host_id'} = $host_id;
            $self->{"cache_service"}->{$host_name . ";" . $service_description}->{'storage_type'} = int($data->{'storage_type'});
            $self->{"cache_service"}->{$host_name . ";" . $service_description}->{'index_id'} = $data->{'id'};
            $self->{"cache_service"}->{$host_name . ";" . $service_description}->{'check_interval'} = $interval;
            $self->{"cache_service"}->{$host_name . ";" . $service_description}->{'rebuild'} = 0;
            $self->{"cache_service"}->{$host_name . ";" . $service_description}->{'rrd_retention'} = (defined($data->{'rrd_retention'})) ? $data->{'rrd_retention'} : -1;
            # Send command to clean cache
            $self->send_rename_command($data->{'host_name'}, $data->{'service_description'}, $host_name, $service_description);
            return -2;
        }
    } elsif (defined($data) && ($data->{'host_name'} eq $host_name || $data->{'service_description'} eq $service_description)) {
        # same name but exist already
        $self->{"cache_service"}->{$host_name . ";" . $service_description} = {};
        $self->{"cache_service"}->{$host_name . ";" . $service_description}->{'metrics'} = {};
        $self->{"cache_service"}->{$host_name . ";" . $service_description}->{'service_id'} = $service_id;
        $self->{"cache_service"}->{$host_name . ";" . $service_description}->{'host_id'} = $host_id;
        $self->{"cache_service"}->{$host_name . ";" . $service_description}->{'storage_type'} = int($data->{'storage_type'});
        $self->{"cache_service"}->{$host_name . ";" . $service_description}->{'index_id'} = $data->{'id'};
        $self->{"cache_service"}->{$host_name . ";" . $service_description}->{'check_interval'} = $interval;
        $self->{"cache_service"}->{$host_name . ";" . $service_description}->{'rebuild'} = 0;
        $self->{"cache_service"}->{$host_name . ";" . $service_description}->{'rrd_retention'} = (defined($data->{'rrd_retention'})) ? $data->{'rrd_retention'} : -1;
    } else {
        # create
        if ($host_name =~ /_Module_([a-zA-Z0-9]*)/) {
            ($status, $stmt) = $self->{'dbcentstorage'}->query("INSERT INTO `index_data` (`host_name`, `service_description`, `host_id`, `service_id`, `special`, `storage_type`) VALUES (" . $self->{'dbcentstorage'}->quote($host_name) . ", " . $self->{'dbcentstorage'}->quote($service_description) . ", " . $host_id . ", " . $service_id . ", '1', '" . $self->{'storage_type'} . "')");
        } else {
            ($status, $stmt) = $self->{'dbcentstorage'}->query("INSERT INTO `index_data` (`host_name`, `service_description`, `host_id`, `service_id`, `storage_type`) VALUES (" . $self->{'dbcentstorage'}->quote($host_name) . ", " . $self->{'dbcentstorage'}->quote($service_description) . ", " . $host_id . ", " . $service_id . ", '" . $self->{'storage_type'} . "')");
        }
        if ($status != -1) {
            $self->{"cache_service"}->{$host_name . ";" . $service_description} = {};
            $self->{"cache_service"}->{$host_name . ";" . $service_description}->{'metrics'} = {};
            $self->{"cache_service"}->{$host_name . ";" . $service_description}->{'service_id'} = $service_id;
            $self->{"cache_service"}->{$host_name . ";" . $service_description}->{'host_id'} = $host_id;
            $self->{"cache_service"}->{$host_name . ";" . $service_description}->{'storage_type'} = $self->{'storage_type'};
            $self->{"cache_service"}->{$host_name . ";" . $service_description}->{'index_id'} = $self->{'dbcentstorage'}->last_insert_id();
            $self->{"cache_service"}->{$host_name . ";" . $service_description}->{'check_interval'} = $interval;
            $self->{"cache_service"}->{$host_name . ";" . $service_description}->{'rebuild'} = 0;
            $self->{"cache_service"}->{$host_name . ";" . $service_description}->{'rrd_retention'} = -1;
        }
    }

    return $status;
}

sub get_centstorage_information {
    my $self = shift;
    my ($len_storage_rrd, $rrd_metrics_path, $rrd_status_path, $storage_type);

    my ($status, $stmt) = $self->{'dbcentstorage'}->query("SELECT len_storage_rrd, RRDdatabase_path, RRDdatabase_status_path, storage_type FROM config");
    my $data = $stmt->fetchrow_hashref();
    if (defined($data)) {
        $len_storage_rrd = int($data->{'len_storage_rrd'});
        $rrd_metrics_path = $data->{'RRDdatabase_path'};
        $rrd_status_path = $data->{'RRDdatabase_status_path'};
        $storage_type = int($data->{'storage_type'});
    }
    return ($status, $len_storage_rrd, $rrd_metrics_path, $rrd_status_path, $storage_type);
}

sub get_centreon_intervaltime {
    my $self = shift;
    my $interval = 60;

    my ($status, $stmt) = $self->{'dbcentreon'}->query("SELECT `value` AS interval_length FROM options WHERE `key` = 'interval_length'");
    my $data = $stmt->fetchrow_hashref();
    if (defined($data)) {
        $interval = $data->{'interval_length'};
    }
    return (0, $interval);
}

#############################
### Perfdata Handle
#############################

sub trim {
    my $self = shift;
    $_[0] =~ s/^[ \t]*?//;
    $_[0] =~ s/[ \t]*?$//;
    return $_[0];
}

sub skip_chars {
    my $self = shift;
    $self->{"perfdata_pos"}++ while ($self->{"perfdata_pos"} < $self->{"perfdata_size"} && (${$self->{"perfdata_chars"}}[$self->{"perfdata_pos"}] =~ /$_[0]/));
}

sub continue_to {
    my $self = shift;
    my ($forbidden, $stop1, $not_stop_after) = @_;
    my $value = "";

    while ($self->{"perfdata_pos"} < $self->{"perfdata_size"}) {
        if (defined($forbidden) && ${$self->{"perfdata_chars"}}[$self->{"perfdata_pos"}] =~ /$forbidden/) {
                return undef;
        }
        if (${$self->{"perfdata_chars"}}[$self->{"perfdata_pos"}] =~ /$stop1/) {
            if (!defined($not_stop_after)) {
                return $value;
            }
            if (!($self->{"perfdata_pos"} + 1 < $self->{"perfdata_size"} && ${$self->{"perfdata_chars"}}[$self->{"perfdata_pos"} + 1] =~ /$not_stop_after/)) {
                $self->{"perfdata_pos"}++;
                return $value;
            }
            $self->{"perfdata_pos"}++;
        }

        $value .= ${$self->{"perfdata_chars"}}[$self->{"perfdata_pos"}];
        $self->{"perfdata_pos"}++;
    }

    return $value;
}

sub parse_label {
    my $self = shift;
    my $label;

    if (defined(${$self->{"perfdata_chars"}}[$self->{"perfdata_pos"}]) && ${$self->{"perfdata_chars"}}[$self->{"perfdata_pos"}] eq "'") {
        $self->{"perfdata_pos"}++;
        $label = $self->continue_to(undef, "'", "'");
    } else {
        $label = $self->continue_to("[ \t]", "=");
    }
    $self->{"perfdata_pos"}++;

    return $label;
}

sub parse_value {
    my $self = shift;
    my $with_unit = shift;
    my $unit = '';
    my $value = "";
    my $neg = 1;

    if (defined(${$self->{"perfdata_chars"}}[$self->{"perfdata_pos"}]) && ${$self->{"perfdata_chars"}}[$self->{"perfdata_pos"}] eq "-") {
        $neg = -1;
        $self->{"perfdata_pos"}++;
    }

    $value = $self->continue_to(undef, "[^0-9\.,]");
    if (defined($value) && $value ne "") {
        $value =~ s/,/./g;
        $value = $value * $neg;
    }
    if (defined($with_unit) && $with_unit == 1) {
        $unit = $self->parse_unit();
    } else {
        $self->skip_chars(";");
    }
    return ($value, $unit);
}

sub parse_unit {
    my $self = shift;
    my $value = "";

    $value = $self->continue_to(undef, "[ \t;]");
    $self->skip_chars(";");
    return $value;
}

sub parse_threshold {
    my $self = shift;
    my $neg = 1;
    my $value_tmp = "";

    my $arobase = 0;
    my $infinite_neg = 0;
    my $infinite_pos = 0;
    my $value_start = "";
    my $value_end = "";
    my $global_status = 1;
    
    if (defined(${$self->{"perfdata_chars"}}[$self->{"perfdata_pos"}]) && ${$self->{"perfdata_chars"}}[$self->{"perfdata_pos"}] eq "@") {
        $arobase = 1;
        $self->{"perfdata_pos"}++;
    }

    if (defined(${$self->{"perfdata_chars"}}[$self->{"perfdata_pos"}]) && ${$self->{"perfdata_chars"}}[$self->{"perfdata_pos"}] eq "~") {
        $infinite_neg = 1;
        $self->{"perfdata_pos"}++;
    } else {
        if (defined(${$self->{"perfdata_chars"}}[$self->{"perfdata_pos"}]) && ${$self->{"perfdata_chars"}}[$self->{"perfdata_pos"}] eq "-") {
            $neg = -1;
            $self->{"perfdata_pos"}++;
        }
        $value_tmp = $self->continue_to(undef, "[^0-9\.,]");
        if (defined($value_tmp) && $value_tmp ne "") {
            $value_tmp =~ s/,/./g;
            $value_tmp = $value_tmp * $neg;
        }
        $neg = 1;
    }

    if (defined(${$self->{"perfdata_chars"}}[$self->{"perfdata_pos"}]) && ${$self->{"perfdata_chars"}}[$self->{"perfdata_pos"}] eq ":") {
        if ($value_tmp ne "") {
                $value_start = $value_tmp;
        } else {
                $value_start = 0;
        }
        $self->{"perfdata_pos"}++;

        if (defined(${$self->{"perfdata_chars"}}[$self->{"perfdata_pos"}]) && ${$self->{"perfdata_chars"}}[$self->{"perfdata_pos"}] eq "-") {
            $neg = -1;
            $self->{"perfdata_pos"}++;
        }
        $value_end = $self->continue_to(undef, "[^0-9\.,]");
        if (defined($value_tmp) && $value_end ne "") {
            $value_end =~ s/,/./g;
            $value_end = $value_end * $neg;
        } else {
            $infinite_pos = 1;
        }
    } else {
        $value_start = 0;
        $value_end = $value_tmp;
    }
    
    my $value = $self->continue_to(undef, "[ \t;]");
    $self->skip_chars(";");
    if ($value ne '') {
        $self->{logger}->writeLogInfo("Wrong threshold...");
        $global_status = 0;
    }

    if ($infinite_neg == 1) {
        $value_start = '-1e500';
    }
    if ($infinite_pos == 1) {
        $value_end = '1e500';
    }

    return ($global_status, $value_start, $value_end, $arobase);
}

sub get_perfdata {
    my $self = shift;
    my ($counter_type, $perf_label, $perf_value, $perf_unit, $perf_warn, $perf_crit, $perf_min, $perf_max);

    if (!defined($self->{'service_perfdata'}) || $self->{"perfdata_pos"} >= $self->{"perfdata_size"}) {
        return 0;
    }

    $self->skip_chars("[ \t]");
    $perf_label = $self->parse_label();
    if (!defined($perf_label) || $perf_label eq '') {
        $self->{"logger"}->writeLogError("Wrong perfdata format: " . $self->{'service_perfdata'});
        return -1 if ($self->{'perfdata_parser_stop'} == 1);
        return 1;
    }

    ($perf_value, $perf_unit) = $self->parse_value(1);
    if (!defined($perf_value) || $perf_value eq '') {
        $self->{"logger"}->writeLogError("Wrong perfdata format: " . $self->{'service_perfdata'});
        return -1 if ($self->{'perfdata_parser_stop'} == 1);
        return 1;
    }

    my ($status_th_warn, $th_warn_start, $th_warn_end, $th_warn_inclusive) = $self->parse_threshold();
    my ($status_th_crit, $th_crit_start, $th_crit_end, $th_crit_inclusive) = $self->parse_threshold();
    $perf_min = $self->parse_value();
    $perf_max = $self->parse_value();

    $perf_label = $self->trim($perf_label);
    $counter_type = 'g';
    if ($perf_label =~ /^([adc])\[(.*?)\]$/) {
        $counter_type = $1;
        $perf_label = $2;
        if (!defined($perf_label) || $perf_label eq '') {
            $self->{"logger"}->writeLogError("Wrong perfdata format: " . $self->{'service_perfdata'});
            return -1 if ($self->{'perfdata_parser_stop'} == 1);
            return 1;
        }
    }

    $self->{metric_name} = $perf_label;
    $self->{metric_value} = $perf_value;
    $self->{metric_unit} = $perf_unit;

    $self->{metric_warn} = 0;
    $self->{warn_low} = 0;
    $self->{warn_threshold_mode} = 0;
    if ($status_th_warn == 1) {
        $self->{metric_warn} = $th_warn_end;
        $self->{warn_low} = $th_warn_start;
        $self->{warn_threshold_mode} = $th_warn_inclusive;
    }
    
    $self->{metric_crit} = 0;
    $self->{crit_low} = 0;
    $self->{crit_threshold_mode} = 0;
    if ($status_th_crit == 1) {
        $self->{metric_crit} = $th_crit_end;
        $self->{crit_low} = $th_crit_start;
        $self->{crit_threshold_mode} = $th_crit_inclusive;
    }

    $self->{metric_min} = $perf_min;
    $self->{metric_max} = $perf_max;
    $self->{metric_type} = $rrd_trans{$counter_type};
    
    return 1;
}

sub init_perfdata {
    my $self = shift;
    
    if (!defined($self->{'service_perfdata'})) {
        return ;
    }
    @{$self->{"perfdata_chars"}} = split //, $self->trim($self->{'service_perfdata'});
    $self->{"perfdata_pos"} = 0;
    $self->{"perfdata_size"} = scalar(@{$self->{"perfdata_chars"}});
}

######################################################

sub cache_update_service_index_data {
    my $self = shift;
    my ($key) = @_;

    my ($status, $stmt) = $self->{'dbcentstorage'}->query("SELECT rrd_retention FROM index_data WHERE id = " . $self->{"cache_service"}->{$key}->{'index_id'});
        if ($status == -1) {
                $self->{'logger'}->writeLogError("Cannot get index_data");
                return -1;
        }
    my $data = $stmt->fetchrow_hashref();
    if (!defined($data)) {
        $self->{'logger'}->writeLogError("Can't find index_data");
        return -1;
    }
    $self->{"cache_service"}->{$key}->{'rrd_retention'} = (defined($data->{'rrd_retention'})) ? $data->{'rrd_retention'} : -1;
    return 0;
}


# Module meta/bam or normal are specified in tables
sub get_host_service_ids {
    my $self = shift;
    my ($host_name, $service_description) = @_;
    my ($host_id, $service_id);
    my ($status, $stmt, $data);
    my $host_register = 1;
    my $service_register = "'1', '3'";

    # For Modules
    if ($host_name =~ /_Module_([a-zA-Z0-9]*)/) {
        $host_register = 2;
        $service_register = "'2'";
    }
    # Get Host_Id
    ($status, $stmt) = $self->{'dbcentreon'}->query("SELECT `host_id` FROM `host` WHERE `host_name` = " . $self->{'dbcentreon'}->quote($host_name) . " AND `host_register` = '$host_register' LIMIT 1");
    return -1 if ($status);
    $data = $stmt->fetchrow_hashref();
    if (!defined($data)) {
        $self->{'logger'}->writeLogError("Can't find 'host_id' $host_name");
        return -2;
    }

    $host_id = $data->{'host_id'};
    
    ($status, $stmt) = $self->{'dbcentreon'}->query("SELECT service_id FROM service, host_service_relation hsr WHERE hsr.host_host_id = '" . $host_id . "' AND hsr.service_service_id = service_id AND service_description = " . $self->{'dbcentreon'}->quote($service_description) . " AND `service_register` IN (" . $service_register . ") LIMIT 1");
    return -1 if ($status == -1);
    $data = $stmt->fetchrow_hashref();
    if (!defined($data)) {
        # Search in service By hostgroup
        ($status, $stmt) = $self->{'dbcentreon'}->query("SELECT service_id FROM hostgroup_relation hgr, service, host_service_relation hsr WHERE hgr.host_host_id = '" . $host_id . "' AND hsr.hostgroup_hg_id = hgr.hostgroup_hg_id AND service_id = hsr.service_service_id AND service_description = " . $self->{'dbcentreon'}->quote($service_description) . " AND `service_register` IN (" . $service_register . ") LIMIT 1");
        return -1 if ($status == -1);
        $data = $stmt->fetchrow_hashref();
    }

    if (!defined($data)) {
        $self->{'logger'}->writeLogError("Can't find 'service_id' for $host_name/$service_description");
        return -2;
    }

    $service_id = $data->{'service_id'};
    return (0, $host_id, $service_id);
}

sub get_check_interval_normal {
    my $self = shift;
    my $service_id = shift;
    my $rotation_check = shift;

    if (!defined($service_id)) {
        return (0, 5);
    }
    $rotation_check = {} if (!defined($rotation_check));
    return (0, 5) if (defined($rotation_check->{$service_id}));

    my ($status, $stmt) = $self->{'dbcentreon'}->query("SELECT service_normal_check_interval, service_template_model_stm_id FROM service WHERE service_id = " . $service_id);
    return -1 if ($status == -1);
    my $data = $stmt->fetchrow_hashref();
    return (0, 5) if (!defined($data));
    
    if (!defined($data->{'service_normal_check_interval'}) || $data->{'service_normal_check_interval'} eq '') {
        $rotation_check->{$service_id} = 1;
        $self->get_check_interval_normal($data->{'service_template_model_stm_id'}, $rotation_check);
    } else {
        return (0, $data->{'service_normal_check_interval'});
    }
}

sub get_check_interval_module {
    my $self = shift;
    my ($host_name, $service_description) = @_;

    my $interval = 1;
    $service_description =~ /([a-zA-Z0-9]*)_([0-9]*)/;
    if ($1 eq "meta"){
        my ($status, $stmt) = $self->{'dbcentreon'}->query("SELECT normal_check_interval FROM meta_service WHERE meta_id = '" . $2 . "' LIMIT 1");
        return -1 if ($status == -1);
        my $data = $stmt->fetchrow_hashref();
        if (defined($data->{'normal_check_interval'})){
            $interval = $data->{'normal_check_interval'};
        }
    } elsif ($1 eq "ba") {
        my ($status, $stmt) = $self->{'dbcentreon'}->query("SELECT normal_check_interval FROM mod_bam WHERE ba_id = '" . $2 . "' LIMIT 1");
        return -1 if ($status == -1);
        my $data = $stmt->fetchrow_hashref();
        if (defined($data->{'normal_check_interval'})) {
             $interval = $data->{'normal_check_interval'};
        }
    }
    return (0, $interval);
}

sub get_check_interval {
    my $self = shift;
    my ($host_name, $service_description, $service_id) = @_;

    if ($host_name =~ /_Module_([a-zA-Z0-9]*)/) {
        return $self->get_check_interval_module($host_name, $service_description);
    } else {
        return $self->get_check_interval_normal($service_id);
    }
}

###########################

sub get_information_service {
    my $self = shift;
    my ($key_service, $timestamp, $host_name, $service_description, $last_service_state, $service_state, $no_cache) = @_;

    # Need to identify it
    my ($status, $host_id, $service_id) = $self->get_host_service_ids($host_name, $service_description);
    if ($status != 0) {
        if ($status == -2 && $self->{"skip_if_no_ids"} == 1) {
            return 1;
        }
        if (!defined($no_cache) || $no_cache == 0) {
            push @{$self->{"cache_services_failed"}->{$key_service}}, [$timestamp, $host_name, $service_description, $last_service_state, $service_state, $self->{'service_perfdata'}];
        }
        return 1;
    }
        
    # Get Interval
    ($status, my $interval) = $self->get_check_interval($host_name, $service_description, $service_id);
    if ($status != 0) {
        if (!defined($no_cache) || $no_cache == 0) {
            push @{$self->{"cache_services_failed"}->{$key_service}}, [$timestamp, $host_name, $service_description, $last_service_state, $service_state, $self->{'service_perfdata'}];
        }
        return 1;
    }

    # Create It
    $status = $self->create_service($host_id, $service_id, $interval * $self->{"interval_time"}, $host_name, $service_description);
    if ($status != 0) {
        if ($status == -1 && (!defined($no_cache) || $no_cache == 0)) {
            push @{$self->{"cache_services_failed"}->{$key_service}}, [$timestamp, $host_name, $service_description, $last_service_state, $service_state, $self->{'service_perfdata'}];
        }
        if ($status == -2 && (!defined($no_cache) || $no_cache == 0)) {
            push @{$self->{"cache_services_rename"}->{$key_service}}, [$timestamp, $host_name, $service_description, $last_service_state, $service_state, $self->{'service_perfdata'}];
        }
        return 1;
    }
    
    return 0;
}

sub update {
    my $self = shift;
    my ($play_failed, $timestamp, $host_name, $service_description, $last_service_state, $service_state);
    ($play_failed, $timestamp, $host_name, $service_description, $last_service_state, $service_state, $self->{'service_perfdata'}) = @_;

    if ($timestamp !~ /^[0-9]+$/ || $timestamp > (time() + 86400)) {
        $self->{'logger'}->writeLogError("Unknown timestamp format or in future: $timestamp");
        return 0;
    }
    # Not good number field
    if (!defined($service_state)) {
        $self->{'logger'}->writeLogError("Line not well formed");
        return 0;
    }

    my $key_service = $host_name . ";" . $service_description;
    # We quit because we have failed to retest before || rebuild
    if ((defined($self->{"cache_services_failed"}->{$key_service}) && $play_failed == 0) ||
        defined($self->{"cache_service"}->{$key_service}) && $self->{"cache_service"}->{$key_service}->{'rebuild'} == 1) {
        push @{$self->{"cache_services_failed"}->{$key_service}}, [$timestamp, $host_name, $service_description, $last_service_state, $service_state, $self->{'service_perfdata'}];
        return 1;
    }
    # We quit because we wait rename finish
    if (defined($self->{"cache_services_rename"}->{$key_service}) && $play_failed == 0) {
        push @{$self->{"cache_services_rename"}->{$key_service}}, [$timestamp, $host_name, $service_description, $last_service_state, $service_state, $self->{'service_perfdata'}];
        return 1;
    }
    

    if (!defined($self->{"cache_service"}->{$key_service})) {
        my $status = $self->get_information_service($key_service, $timestamp, $host_name, $service_description, $last_service_state, $service_state, $play_failed);
        return 1 if ($status == 1);
    }

     $self->init_perfdata();
    while (($self->get_perfdata()) > 0) {
        if (!defined($self->{"cache_service"}->{$key_service}->{'metrics'}->{$self->{"metric_name"}})) {
            # Need to identify metrics    
            # if failed, we go 'next'
            my $status = $self->create_metric($self->{"cache_service"}->{$key_service}->{'index_id'}, \$self->{"cache_service"}->{$key_service}->{'metrics'}, $self->{"metric_name"});
            next if ($status == -1);
        }

        $self->check_update_extra_metric(\$self->{"cache_service"}->{$key_service}->{'metrics'}->{$self->{"metric_name"}});

        ###
        # Check data source type: DB
        ###
        if ($self->{"cache_service"}->{$key_service}->{'storage_type'} == 2) {
            # Do DataBin Add
            $self->add_data_mysql($self->{"cache_service"}->{$key_service}->{'metrics'}->{$self->{"metric_name"}}->{'metric_id'},
                          $timestamp,
                          $self->{"metric_value"});
        }

        ###
        # Do RRDs: metric
        ###
        $self->{"rrd"}->add_metric($self->{"cache_service"}->{$key_service}->{'metrics'}->{$self->{"metric_name"}}->{'metric_id'},
                       $self->{"metric_name"},
                       $self->{"cache_service"}->{$key_service}->{'check_interval'},
                       $self->{"cache_service"}->{$key_service}->{'metrics'}->{$self->{"metric_name"}}->{'data_source_type'},
                       $timestamp,
                       $self->{"metric_value"},
                       $self->{"cache_service"}->{$key_service}->{'rrd_retention'});
    }

    ###
    # Do RRD Status
    ###
    if ($self->{"do_rrd_status"} == 1) {
        $self->{"rrd"}->add_status($self->{"cache_service"}->{$key_service}->{'index_id'},
                       $self->{"cache_service"}->{$key_service}->{'check_interval'},
                       $timestamp,
                       $service_state,
                       $self->{"cache_service"}->{$key_service}->{'rrd_retention'});
    }

    return 0;
}

sub rebuild_finish {
    my $self = shift;

    $self->{"rebuild_progress"} = 0;
    if (defined($self->{"cache_service"}->{$self->{"rebuild_key"}})) {
        $self->{"cache_service"}->{$self->{"rebuild_key"}}->{'rebuild'} = 0;
    }
    my $fh = $self->{'pipe_write'};
    print $fh "REBUILDFINISH\n";
}

sub rebuild {
    my $self = shift;
    my ($host_name, $service_description) = @_;
    my $status;
    my $current_interval;

    $self->{"rebuild_progress"} = 1;
    if (!defined($host_name)) {
        # A rebuild is in progress
        return 0;
    }

    my $key_service = $host_name . ";" . $service_description;
    $self->{"rebuild_key"} = $key_service;

    ######
    # To do the rebuild
    # Force flush data_bin
    $self->flush_mysql(1);
    
    ######
    # Maybe we have to create cache service and metrics
    # We'll get information for rebuild fork
    #
    if (!defined($self->{"cache_service"}->{$key_service})) {
        $status = $self->get_information_service($key_service, undef, $host_name, $service_description, undef, undef, 1); 
        if ($status == 1) {
            $self->{'logger'}->writeLogError("rebuild cannot get information service");
            $self->rebuild_finish();
            return ;
        }
    } else {
        ######
        # Update Interval
        #
        ($status, $current_interval) = $self->get_check_interval($host_name, $service_description, $self->{"cache_service"}->{$key_service}->{'service_id'});
        if ($status == -1) {
            $self->{'logger'}->writeLogError("rebuild cannot get interval service");
            $self->rebuild_finish();
            return ;
        }
        $self->{"cache_service"}->{$key_service}->{'check_interval'} = $current_interval * $self->{"interval_time"};

        #####
        # Update cache to get 'rrd_retention'
        ($status) = $self->cache_update_service_index_data($key_service);
        if ($status == -1) {
            $self->rebuild_finish();
            return ;
        }
    }
    $self->{"cache_service"}->{$key_service}->{'rebuild'} = 1;

    ######
    # Get List Metrics and Flush if needed
    #
    ($status, my $stmt) = $self->{'dbcentstorage'}->query("SELECT metric_id, data_source_type FROM metrics WHERE index_id = " . $self->{"cache_service"}->{$key_service}->{'index_id'});
    if ($status == -1) {
        $self->{'logger'}->writeLogError("rebuild cannot get metrics list");
        $self->rebuild_finish();
        return ;
    }
    while ((my $data = $stmt->fetchrow_hashref())) {
        $self->{"rrd"}->delete_cache_metric($data->{'metric_id'});
        # Update cache
        $self->{"cache_service"}->{$key_service}->{'metrics'}->{'data_source_type'} = $data->{'data_source_type'};
    }

    ######
    # Fork and launch rebuild (we'll rebuild each metric)
    #
    $self->{"rebuild_index_id"} = $self->{"cache_service"}->{$key_service}->{'index_id'};
    my $rebuild_index_id = $self->{"rebuild_index_id"};

    $self->{"current_pid"} = fork();
        if (!defined($self->{"current_pid"})) {
        $self->{'logger'}->writeLogError("rebuild cannot fork: $!");
        $self->rebuild_finish();
    } elsif (!$self->{"current_pid"}) {
        $self->{'dbcentstorage'}->set_inactive_destroy();
        $self->{'dbcentreon'}->set_inactive_destroy();

        my $centreon_db_centstorage = centreon::common::db->new(logger => $self->{'logger'},
                                                           db => $self->{'dbcentstorage'}->db(),
                                                           host => $self->{'dbcentstorage'}->host(),
                                                           user => $self->{'dbcentstorage'}->user(),
                                                           password => $self->{'dbcentstorage'}->password(),
                                                           port => $self->{'dbcentstorage'}->port(),
                                                           force => 0);
        $status = $centreon_db_centstorage->connect();
        exit 1 if ($status == -1);
        my $centstorage_rebuild = centreon::centstorage::CentstorageRebuild->new($self->{'logger'});
        $status = $centstorage_rebuild->main($centreon_db_centstorage, $rebuild_index_id, $self->{"cache_service"}->{$key_service}->{'check_interval'}, $self->{'rrd'}, $self->{"cache_service"}->{$key_service}->{'rrd_retention'});
        $centreon_db_centstorage->disconnect();
        exit $status;
    }
    if ($self->{"current_pid"} == -1) {
        $self->{"current_pid"} = undef;
    }
}

sub rename_finish {
    my $self = shift;
    my ($host_name, $service_description) = @_;

    if (defined($self->{"cache_services_rename"}->{$host_name . ";" . $service_description})) {
        $self->{'logger'}->writeLogInfo("rename finish received $host_name/$service_description");
        my @tmp_ar = ();
        my $lerror = 0;  
        foreach (@{$self->{"cache_services_rename"}->{$host_name . ";" . $service_description}}) {
            if ($lerror == 0 && $self->update(1, @$_) != 0) {
                push @tmp_ar, \@$_;
                $lerror = 1;
            } else {
                push @tmp_ar, \@$_;
            }
        }
        if (scalar(@tmp_ar) != 0) {
            @{$self->{"cache_services_failed"}->{$host_name . ";" . $service_description}} = @tmp_ar;
        }
        $self->{'logger'}->writeLogInfo("rename finish $host_name/$service_description ok");
        delete $self->{"cache_services_rename"}->{$host_name . ";" . $service_description};
    }
}

sub send_rename_finish {
    my $self = shift;
    my ($host_name, $service_description) = @_;
    
    $self->{"rename_rebuild_wait"} = 0;
    my $fh = $self->{'pipe_write'};
    print $fh "RENAMEFINISH\t$host_name\t$service_description\n";
}

sub rename_clean {
    my $self = shift;
    my ($host_name, $service_description, $new_host_name, $new_service_description) = @_;
    my $key = $host_name . ";" . $service_description;    

    $self->{'logger'}->writeLogInfo("rename clean received $host_name/$service_description");
    $self->{"rename_old_new"}->{$key} = $new_host_name . ";" . $new_service_description;
    if ($self->{"rebuild_progress"} == 1 && $self->{"rebuild_key"} eq $key) {
        $self->{"rename_rebuild_wait"} = 1;
        $self->{'logger'}->writeLogInfo("Wait rebuild finish...");
        return ;
    }

    # Do RRD flush
    $self->force_flush_rrd($key);
    delete $self->{"cache_service"}->{$key};
    delete $self->{"cache_services_failed"}->{$key};
    delete $self->{"rename_old_new"}->{$key};
    $self->send_rename_finish($new_host_name, $new_service_description);
}

sub delete_clean {
    my $self = shift;
    my ($host_name, $service_description, $metric_name) = @_;
    my $key = $host_name . ";" . $service_description;    
    
    if (defined($metric_name)) {
        $self->{'rrd'}->delete_cache_metric($self->{"cache_service"}->{$key}->{'metrics'}->{$metric_name}->{'metric_id'});
        delete $self->{"cache_service"}->{$key}->{'metrics'}->{$metric_name};
    } else {
        foreach (keys %{$self->{"cache_service"}->{$key}->{'metrics'}}) {
            $self->{'rrd'}->delete_cache_metric($self->{"cache_service"}->{$key}->{'metrics'}->{$_}->{'metric_id'});
        }
        $self->{'rrd'}->delete_cache_status($self->{"cache_service"}->{$key}->{'index_id'});
        delete $self->{"cache_service"}->{$key};
        delete $self->{"cache_services_failed"}->{$key};
    }
}

sub main {
    my $self = shift;
    my ($dbcentreon, $dbcentstorage, $pipe_read, $pipe_write, $num_pool, $rrd_cache_mode, $rrd_flush_time, $perfdata_parser_stop, $config_file) = @_;
    my $status;

    $self->{dbcentreon} = $dbcentreon;
    $self->{dbcentstorage} = $dbcentstorage;
    $self->{num_pool} = $num_pool;
    $self->{config_file} = $config_file;
    $self->{perfdata_parser_stop} = $perfdata_parser_stop if (defined($perfdata_parser_stop));

    ($status, $self->{"main_perfdata_file"}) = centreon::centstorage::CentstorageLib::get_main_perfdata_file($self->{'dbcentreon'});
    ($status, $self->{"len_storage_rrd"}, $self->{"rrd_metrics_path"}, $self->{"rrd_status_path"}, $self->{"storage_type"}) = $self->get_centstorage_information();
    ($status, $self->{"interval_time"}) = $self->get_centreon_intervaltime();
    $self->{"rrd"}->metric_path($self->{"rrd_metrics_path"});
    $self->{"rrd"}->status_path($self->{"rrd_status_path"});
    $self->{"rrd"}->len_rrd($self->{"len_storage_rrd"});
    $self->{"rrd"}->cache_mode($rrd_cache_mode);
    $self->{"rrd"}->flush($rrd_flush_time);

    # We have to manage if you don't need infos
    $self->{'dbcentreon'}->force(0);
    $self->{'dbcentstorage'}->force(0);
    
    $self->{'pipe_write'} = $pipe_write;
    $self->{'read_select'} = new IO::Select();
    $self->{'read_select'}->add($pipe_read);
    while (1) {
        my @rh_set = $self->{'read_select'}->can_read(10);
        if (scalar(@rh_set) == 0) {
            $self->flush_mysql();
            $self->{"rrd"}->flush_all();
            $self->flush_failed();
        }
        foreach my $rh (@rh_set) {
            my $read_done = 0;
            while ((my ($status_line, $readline) = centreon::common::misc::get_line_pipe($rh, \@{$self->{'save_read'}}, \$read_done))) {
                class_handle_TERM() if ($status_line == -1);
                last if ($status_line == 0);
                my ($method, @fields) = split(/\t/, $readline);

                # Check Type
                if (defined($method) && $method eq "UPDATE") {
                    $self->update(0, @fields);
                } elsif (defined($method) && $method eq "REBUILDBEGIN") {
                    $self->rebuild(@fields);
                } elsif (defined($method) && $method eq "REBUILDFINISH") {
                    $self->{"rebuild_progress"} = 0;
                } elsif (defined($method) && $method eq "RENAMEFINISH") {
                    $self->rename_finish(@fields);
                } elsif (defined($method) && $method eq "RENAMECLEAN") {
                    $self->rename_clean(@fields);
                } elsif (defined($method) && $method eq "DELETECLEAN") {
                    $self->delete_clean(@fields);
                }

                $self->flush_mysql();
                $self->{"rrd"}->flush_all();
            }
        }
        $self->flush_failed();
        
        if ($self->{reload} == 0) {
            $self->reload();
        }
    }
}

1;
