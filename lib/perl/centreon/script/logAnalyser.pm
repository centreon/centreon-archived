package centreon::script::logAnalyser;

use strict;
use warnings;
use File::Path qw(mkpath);
use centreon::script;

use base qw(centreon::script);

sub new {
    my $class = shift;
    my $self = $class->SUPER::new("logAnalyser",
        centreon_db_conn => 1,
        centstorage_db_conn => 1
    );

    bless $self, $class;
    $self->add_options(
        "a" => \$self->{opt_a}, "archives" => \$self->{opt_a},
        "p=s" => \$self->{opt_p}, "poller" => \$self->{opt_p},
        "s=s" => \$self->{opt_s}, "startdate=s" => \$self->{opt_s}
    );
    $self->{launch_time} = time();
    $self->{msg_type5_disabled} = 0;
    $self->{queries_per_transaction} = 500;
    return $self;
}

sub read_config {
    my $self = shift;
    my ($status, $sth) = $self->{cdb}->query("SELECT `value` FROM `options` WHERE `key` = 'broker'");

    goto error if $status == -1;
    if ($sth->fetchrow_hashref()->{value} eq "broker") {
        die "This script is only suitable for NDO";
    }
    ($status, $sth) = $self->{csdb}->query(<<"EOQ");
SELECT archive_log, archive_retention FROM config
EOQ
    goto error if $status == -1;
    $self->{config} = $sth->fetchrow_hashref();
    die "No configuration found in database" if !defined $self->{config}->{archive_log};
    return;

  error:
    die "Failed to read configuration from database"
}

=head2 date_to_time($date)

Convert $date to a timestamp.

=cut
sub date_to_time($$) {
    my ($self, $date) = @_;

    $date =~ s|-|/|g;
    return int(`date -d $date +%s`);
}

=head2 time_to_date($timestamp)

Convert $timestamp to a human readable date.

=cut
sub time_to_date($$) {
    my ($self, $timestamp) = @_;
    chomp(my $result = `date -d \@$timestamp +%m-%d-%Y`);

    return $result;
}

sub reset_position_flag {
    my ($self, $instance) = @_;
    my $status = $self->{csdb}->do(<<"EOQ");
UPDATE instance SET log_flag = '0' WHERE instance_id = '$instance'
EOQ
    die "Failed to reset the position flag into database" if $status == -1;
}

sub commit_to_log {
    my ($self, $sth, $log_table_rows, $instance, $ctime, $counter) = @_;
    my @tuple_status;

    $sth->execute_for_fetch(sub { shift @$log_table_rows }, \@tuple_status);
    $self->{csdb}->do(<<"EOQ");
UPDATE instance SET log_flag='$counter', last_ctime='$ctime' WHERE instance_id = '$instance'
EOQ
    $self->{csdb}->commit;
    $self->{csdb}->transaction_mode(1);
}

=head2 parse_file($logFile, $instance)

Parse a nagios log file.

=cut
sub parse_file($$$) {
    my ($self, $logfile, $instance) = @_;
    my $ctime = 0;
    my $logdir = "$self->{centreon_config}->{VarLib}/log/$instance";
    my ($last_position, $nbqueries, $counter) = (0, 0, 0);
    my @log_table_rows;

    if (!-d $logdir) {
        mkpath($logdir);
    }
    my ($status, $sth) = $self->{csdb}->query(<<"EOQ");
SELECT `log_flag`,`last_ctime` FROM `instance` WHERE `instance_id`='$instance'
EOQ
    die "Cannot read previous run information from database" if $status == -1;
    my $prev_run_info = $sth->fetchrow_hashref();

    # Get History Flag
    if (open LOG, $logfile) {
        my $fline = <LOG>;
        close LOG;
        if ($fline =~ m/\[([0-9]*)\]\ /) {
            chomp($ctime = $1);
        } else {
            $self->{logger}->writeLogError("Cannot find ctime in first line for poller $instance");
        }
    }

    # Decide if we have to read the nagios.log from the begining
    if ($ctime && $prev_run_info->{ctime} && $ctime == $prev_run_info->{ctime}) {
        $last_position = $prev_run_info->{log_flag};
    }

    # Open Log File for parsing
    if (!open FILE, $logfile) {
        $self->{logger}->writeLogError("Cannot open file: $logfile");
        return;
    }

    # Skip old lines (already read)
    if (!$self->{opt_a} && $last_position) {
        while ($counter < $last_position && <FILE>) {
            $counter++;
        }
    }

    $self->{csdb}->transaction_mode(1);
    eval {
        my $sth = $self->{csdb}->query(<<"EOQ");
INSERT INTO log (ctime, host_name, service_description, status, output, notification_cmd, notification_contact, type, retry, msg_type, instance)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
EOQ

        while (<FILE>) {
            my $cur_ctime;

            if ($_ =~ m/^\[([0-9]*)\]\sSERVICE ALERT\:\s(.*)$/) {
                my @tab = split(/;/, $2);
                $cur_ctime = $1;
                push @log_table_rows, 
                  map { defined $_ ? $self->{cdbs}->quote($_) : "" } 
                    ($cur_ctime, $tab[0], $tab[1], $tab[2], $tab[5], '', '', $tab[3], $tab[4], '0', $instance);
            } elsif ($_ =~ m/^\[([0-9]*)\]\sHOST ALERT\:\s(.*)$/) {
                my @tab = split(/;/, $2);
                $cur_ctime = $1;
                push @log_table_rows, 
                  map { defined $_ ? $self->{csdb}->quote($_) : "" } 
                    ($cur_ctime, $tab[0], '', $tab[1], $tab[4], '', '', $tab[2], $tab[3], '1', $instance);
            } elsif ($_ =~ m/^\[([0-9]*)\]\sSERVICE NOTIFICATION\:\s(.*)$/) {
                my @tab = split(/;/, $2);
                push @log_table_rows, 
                  map { defined $_ ? $self->{csdb}->quote($_) : "" }
                    ($cur_ctime, $tab[1], $tab[2], $tab[3], $tab[5], $tab[4], $tab[0], '', '', '2', $instance);
            } elsif ($_ =~ m/^\[([0-9]*)\]\sHOST NOTIFICATION\:\s(.*)$/) {
                my @tab = split(/;/, $2);
                $cur_ctime = $1;
                push @log_table_rows, 
                  map { defined $_ ? $self->{csdb}->quote($_) : "" } 
                    ($cur_ctime, $tab[1], '', $tab[2], $tab[4], $tab[3], $tab[0], '', '', '3', $instance);
            } elsif ($_ =~ m/^\[([0-9]*)\]\sCURRENT\sHOST\sSTATE\:\s(.*)$/) {
                my @tab = split(/;/, $2);
                $cur_ctime = $1;
                push @log_table_rows, 
                  map { defined $_ ? $self->{csdb}->quote($_) : "" } 
                    ($cur_ctime, $tab[0], '', $tab[1], '', '', '', $tab[2], '', '7', $instance);
            } elsif ($_ =~ m/^\[([0-9]*)\]\sCURRENT\sSERVICE\sSTATE\:\s(.*)$/) {
                my @tab = split(/;/, $2);
                $cur_ctime = $1;
                push @log_table_rows,
                  map { defined $_ ? $self->{csdb}->quote($_) : "" } 
                    ($cur_ctime, $tab[0], $tab[1], $tab[2], '', '', '', $tab[3], '', '6', $instance);
            } elsif ($_ =~ m/^\[([0-9]*)\]\sINITIAL\sHOST\sSTATE\:\s(.*)$/) {
                my @tab = split(/;/, $2);
                $cur_ctime = $1;
                push @log_table_rows, 
                  map { defined $_ ? $self->{csdb}->quote($_) : "" } 
                    ($cur_ctime, $tab[0], '', $tab[1], '', '', '', $tab[2], '', '9', $instance);
            } elsif ($_ =~ m/^\[([0-9]*)\]\sINITIAL\sSERVICE\sSTATE\:\s(.*)$/) {
                my @tab = split(/;/, $2);
                $cur_ctime = $1;
                push @log_table_rows, 
                  map { defined $_ ? $self->{csdb}->quote($_) : "" } 
                    ($cur_ctime, $tab[0], $tab[1], $tab[2], '', '', '', $tab[3], '', '8', $instance);
            } elsif ($_ =~ m/^\[([0-9]*)\]\sEXTERNAL\sCOMMAND\:\sACKNOWLEDGE\_SVC\_PROBLEM\;(.*)$/) {
                $cur_ctime = $1;
                my @tab = split(/;/, $2);
                push @log_table_rows, 
                  map { defined $_ ? $self->{csdb}->quote($_) : "" } 
                    ($cur_ctime, $tab[0], $tab[1], '', $tab[6], '', $tab[5], '', '', '10', $instance);
            } elsif ($_ =~ m/^\[([0-9]*)\]\sEXTERNAL\sCOMMAND\:\sACKNOWLEDGE\_HOST\_PROBLEM\;(.*)$/) {
                $cur_ctime = $1;
                my @tab = split(/;/, $2);
                push @log_table_rows, 
                  map { defined $_ ? $self->{csdb}->quote($_) : "" } 
                    ($cur_ctime, $tab[0], '', '', $tab[5], '', $tab[4], '', '', '11', $instance);
            } elsif ($_ =~ m/^\[([0-9]*)\]\sWarning\:\s(.*)$/) {
                my $tab = $2;
                $cur_ctime = $1;
                push @log_table_rows, 
                  map { defined $_ ? $self->{csdb}->quote($_) : "" } 
                    ($cur_ctime, '', '', '', $tab, '', '', '', '', '4', $instance);
            } elsif ($_ =~ m/^\[([0-9]*)\]\s(.*)$/ && (!$self->{msg_type5_disabled})) {
                $cur_ctime = $1;
                my $tab = $2;
                push @log_table_rows, 
                  map { defined $_ ? $self->{csdb}->quote($_) : "" } 
                    ($cur_ctime, '', '', '', $tab, '', '', '', '', '5', $instance);
            }
            $counter++;
            $nbqueries++;
            if ($nbqueries == $self->{queries_per_transaction}) {
                $self->commit_to_log($sth, \@log_table_rows, $instance, $ctime, $counter);
                $nbqueries = 0;
                @log_table_rows = ();
            }
        }
        $self->commit_to_log($sth, \@log_table_rows, $instance, $ctime, $counter);
    };
    close FILE;
    if ($@) {
        $self->{csdb}->rollback;
        die "Database error: $@";
    }
    $self->{csdb}->transaction_mode(0);
}

=head2 parse_archives($instance, $localhost, $startdate)

Parse log file archices for a given poller (B<$instance>). An
optionnal B<$startdate> can be provided.

=cut
sub parse_archives {
    my ($self, $instance, $localhost, $startdate) = @_;
    my $archives;

    if ($localhost) {
        my ($status, $sth) = $self->{cdb}->query(<<"EOQ");
SELECT `log_archive_path` FROM `cfg_nagios`, `nagios_server` 
WHERE `nagios_server_id` = '$instance' 
AND `nagios_server`.`id` = `cfg_nagios`.`nagios_server_id` 
AND `nagios_server`.`ns_activate` = '1' 
AND `cfg_nagios`.`nagios_activate` = '1'
EOQ
        die "Failed to read instance configuration" if $status == -1;
        $archives = $sth->fetchrow_hashref()->{log_archive_path};
    } else {
        $archives = "$self->{centreon_config}->{VarLib}/log/$instance/archives/";
    }

    $archives .= "/" if (!($archives =~ /\/$/));
    if (!-d $archives) {
        $self->{logger}->writeLogError("No archives for poller $instance");
        return;
    }

    my @log_files = split /\s/, `ls $archives`;
    my $last_log = undef;

    if (!defined $startdate) {
        $last_log = time() - ($self->{config}->{archive_retention} * 24 * 60 * 60);
    } else {
        $last_log = $self->date_to_time($startdate);
    }
    foreach (@log_files) {
        $_ =~ /nagios\-([0-9\-]+).log/;
        my @time = split /\-/, $1;
        my $temp = "$time[0]/$time[1]/$time[2]";
        $temp = `date -d $temp +%s`;
        if ($temp > $last_log) {
            my $curarchive = "$archives$_";

            $self->{logger}->writeLogInfo("Parsing log file: $curarchive");
            if (!-r $curarchive) {
                $self->{logger}->writeLogError("Cannot read file $curarchive");
                next;
            }
            $self->parse_file($curarchive, $instance);
        }
    }
}

=head2 parse_logfile($instance, $localhost, $previous_launch_time)

Parse the current nagios log file for a given poller.

=cut
sub parse_logfile($$$) {
    my ($self, $instance, $localhost, $previous_launch_time) = @_;
    my ($logfile, $archivepath);

    if ($localhost) {
        my ($status, $sth) = $self->{cdb}->query(<<"EOQ");
SELECT `log_file`, `log_archive_path` 
FROM `cfg_nagios`, `nagios_server` 
WHERE `nagios_server_id` = '$instance' 
AND `nagios_server`.`id` = `cfg_nagios`.`nagios_server_id` 
AND `nagios_server`.`ns_activate` = '1' 
AND `cfg_nagios`.`nagios_activate` = '1'
EOQ
        die "Cannot read logfile from database" if $status == -1;
        my $data = $sth->fetchrow_hashref();
        $logfile = $data->{log_file};
        $archivepath = $data->{log_archive_path};
        $archivepath .= "/" if ($archivepath !~ /\/$/);
        die "Failed to open $logfile" if !-r $logfile;

        my @now = localtime();
        my $archname = "$archivepath/nagios-" . $self->time_to_date($self->{launch_time}) . "-$now[2].log";
        if (-f $archname) {
            my $st = stat($archname);
            if ($st->mtime > $previous_launch_time) {
                $self->{logger}->writeLogInfo("Parsing rotated file for instance $instance");
                $self->parse_file($archname, $instance);
            }
        }
    } else {
        $logfile = "$self->{centreon_config}->{VarLib}/log/$instance/nagios.log";
        my $rotate_file = "$logfile.rotate";

        if (-e $rotate_file) {
            $self->{logger}->writeLogInfo("Parsing rotated file for instance $instance");
            $self->parse_file($rotate_file, $instance);
            unlink $rotate_file;
        }
    }

    $self->parse_file($logfile, $instance);
}

=head2 run()

Main method.

Several working modes:

* Parse the current log file of each poller (default)
* Parse the archives of each poller (-a)
* Parse the archives of a given poller (-p)

When parsing the archives, a start date can be specified.

=cut
sub run {
    my $self = shift;

    $self->SUPER::run();
    $self->read_config();

    if (defined $self->{opt_s}) {
        if ($self->{opt_s} !~ m/\d{2}-\d{2}-\d{4}/) {
            $self->{logger}->writeLogError("Invalid start date provided");
            exit 1;
        }
    }

    if (defined $self->{opt_p}) {
        $self->reset_position_flag($self->{opt_p});
        $self->{csdb}->do("DELETE FROM `log` WHERE instance='$self->{opt_p}'");
        $self->parse_archives($self->{opt_p}, 0, $self->{opt_s});
        return;
    }

    my $flag = 0;
    my ($status, $list_sth) = $self->{cdb}->query(<<"EOQ");
SELECT `id`, `name`, `localhost` FROM `nagios_server` WHERE `ns_activate`=1
EOQ
    die "Cannot read pollers list from database" if $status == -1;

    while (my $ns_server = $list_sth->fetchrow_hashref()) {
        my $sth;
        ($status, $sth) = $self->{csdb}->query(<<"EOQ");
SELECT `instance_name` FROM `instance` WHERE `instance_id` = '$ns_server->{id}' LIMIT 1
EOQ
        die "Cannot read instance name from database" if $status == -1;
        if (!$sth->rows()) {
            $status = $self->{csdb}->do(<<"EOQ");
INSERT INTO `instance` 
(`instance_id`, `instance_name`, `log_flag`)
VALUES ('$ns_server->{id}', '$ns_server->{name}', '0')
EOQ
            die "Cannot save instance to database" if $status == -1;
        } else {
            $status = $self->{csdb}->do(<<"EOQ");
UPDATE `instance` SET `instance_name` = '$ns_server->{name}' 
WHERE `instance_id` = '$ns_server->{id}' LIMIT 1
EOQ
            die "Cannot update instance from database" if $status == -1;
        }
        $self->{logger}->writeLogInfo("Poller: $ns_server->{name}");
        if ($self->{opt_a}) {
            if (!$flag) {
                if (!defined $self->{opt_s}) {
                    $status = $self->{csdb}->do("TRUNCATE TABLE `log`");
                    $self->{logger}->writeLogError("Failed to truncate 'log' table") if $status == -1;
                } else {
                    my $limit = $self->date_to_time($self->{opt_s});
                    $status = $self->{csdb}->do("DELETE FROM `log` WHERE `ctime` >= $limit");
                    $self->{logger}->writeLogError("Failed to purge 'log' table") if $status == -1;
                }
                $flag = 1;
            }
            $self->reset_position_flag($ns_server->{id});
            $self->parse_archives($ns_server->{id}, $ns_server->{localhost}, $self->{opt_s});
        } else {
            $self->parse_logfile($ns_server->{id}, $ns_server->{localhost}, 
                                 $self->{lock}->{previous_launch_time});
        }
    }
    $self->{logger}->writeLogInfo("Done");
}

1;

__END__

=head1 NAME

    sample - Using GetOpt::Long and Pod::Usage

=cut
