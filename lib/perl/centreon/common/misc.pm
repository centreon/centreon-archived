
package centreon::common::misc;

use strict;
use warnings;
use vars qw($centreon_config);
use POSIX ":sys_wait_h";

my $read_size = 1*1024*1024*10; # 10Mo

sub reload_db_config {
    my ($logger, $config_file, $cdb, $csdb) = @_;
    my ($cdb_mod, $csdb_mod) = (0, 0);
    
    unless (my $return = do $config_file) {
        $logger->writeLogError("couldn't parse $config_file: $@") if $@;
        $logger->writeLogError("couldn't do $config_file: $!") unless defined $return;
        $logger->writeLogError("couldn't run $config_file") unless $return;
        return -1;
    }
    
    if (defined($cdb)) {
        if ($centreon_config->{centreon_db} ne $cdb->db() ||
            $centreon_config->{db_host} ne $cdb->host() ||
            $centreon_config->{db_user} ne $cdb->user() ||
            $centreon_config->{db_passwd} ne $cdb->password() ||
            $centreon_config->{db_port} ne $cdb->port()) {
            $logger->writeLogInfo("Database centreon config had been modified");
            $cdb->db($centreon_config->{centreon_db});
            $cdb->host($centreon_config->{db_host});
            $cdb->user($centreon_config->{db_user});
            $cdb->password($centreon_config->{db_passwd});
            $cdb->port($centreon_config->{db_port});
            $cdb_mod = 1;
        }
    }
    
    if (defined($csdb)) {
        if ($centreon_config->{centstorage_db} ne $csdb->db() ||
            $centreon_config->{db_host} ne $csdb->host() ||
            $centreon_config->{db_user} ne $csdb->user() ||
            $centreon_config->{db_passwd} ne $csdb->password() ||
            $centreon_config->{db_port} ne $csdb->port()) {
            $logger->writeLogInfo("Database centstorage config had been modified");
            $csdb->db($centreon_config->{centstorage_db});
            $csdb->host($centreon_config->{db_host});
            $csdb->user($centreon_config->{db_user});
            $csdb->password($centreon_config->{db_passwd});
            $csdb->port($centreon_config->{db_port});
            $csdb_mod = 1;
        }
    }
   
    return (0, $cdb_mod, $csdb_mod);
}

sub get_all_options_config {
    my ($extra_config, $centreon_db_centreon, $prefix) = @_;

    my $save_force = $centreon_db_centreon->force();
    $centreon_db_centreon->force(0);
    
    my ($status, $stmt) = $centreon_db_centreon->query("SELECT `key`, `value` FROM options WHERE `key` LIKE " . $centreon_db_centreon->quote($prefix . "_%") . " LIMIT 1");
    if ($status == -1) {
        $centreon_db_centreon->force($save_force);
        return ;
    }
    while ((my $data = $stmt->fetchrow_hashref())) {
        if (defined($data->{value}) && length($data->{value}) > 0) {
            $data->{key} =~ s/^${prefix}_//;
            $extra_config->{$data->{key}} = $data->{value};
        }
    }
    
    $centreon_db_centreon->force($save_force);
}

sub get_option_config {
    my ($extra_config, $centreon_db_centreon, $prefix, $key) = @_;
    my $data;
 
    my $save_force = $centreon_db_centreon->force();
    $centreon_db_centreon->force(0);
    
    my ($status, $stmt) = $centreon_db_centreon->query("SELECT value FROM options WHERE `key` = " . $centreon_db_centreon->quote($prefix . "_" . $key) . " LIMIT 1");
    if ($status == -1) {
        $centreon_db_centreon->force($save_force);
        return ;
    }
    if (($data = $stmt->fetchrow_hashref()) && defined($data->{value})) {
        $extra_config->{$key} = $data->{value};
    }
    
    $centreon_db_centreon->force($save_force);
}

sub check_debug {
    my ($logger, $key, $cdb, $name) = @_;
    
    my $request = "SELECT value FROM options WHERE `key` = " . $cdb->quote($key);
    my ($status, $sth) =  $cdb->query($request);
    return -1 if ($status == -1);
    my $data = $sth->fetchrow_hashref();
    if (defined($data->{'value'}) && $data->{'value'} == 1) {
        if (!$logger->is_debug()) {
            $logger->severity("debug");
            $logger->writeLogInfo("Enable Debug in $name");
        }
    } else {
        if ($logger->is_debug()) {
            $logger->set_default_severity();
            $logger->writeLogInfo("Disable Debug in $name");
        }
    }
    return 0;
}

sub get_line_file {
    my ($fh, $datas, $readed) = @_;
    my $line;
    my $size = scalar(@$datas);

    return (1, shift(@$datas)) if ($size > 1);
    while ((my $eof = sysread($fh, $line, $read_size))) {
        my @result = split("\n", $line);
        if ($line =~ /\n$/) {
            push @result, "";
        }
        if ($size == 1) {
            $$datas[0] .= shift(@result);
        }
        push @$datas, @result;
        $$readed += $eof;
        $size = scalar(@$datas);
        if ($size > 1) {
            return (1, shift(@$datas));
        }
    }
    return (1, shift(@$datas)) if ($size > 1);
    return -1;
}

sub get_line_pipe {
    my ($fh, $datas, $read_done) = @_;
    my $line;
    my $size = scalar(@$datas);

    if ($size > 1) {
        return (1, shift(@$datas));
    } elsif ($size == 1 && $$read_done == 1) {
        return 0;
    }
    while ((my $eof = sysread($fh, $line, 10000))) {
        $$read_done = 1;
        my @result = split("\n", $line);
        if ($line =~ /\n$/) {
            push @result, "";
        }
        if ($size == 1) {
            $$datas[0] .= shift(@result);
        }
        push @$datas, @result;
        $size = scalar(@$datas);
        if ($size > 1) {
            return (1, shift(@$datas));
        } else {
            return 0;
        }
    }
    return -1;
}

sub backtick {
    my %arg = (
        command => undef,
        logger => undef,
        timeout => 30,
        wait_exit => 0,
        @_,
    );
    my @output;
    my $pid;
    my $return_code;
    
    my $sig_do;
    if ($arg{wait_exit} == 0) {
        $sig_do = 'IGNORE';
        $return_code = undef;
    } else {
        $sig_do = 'DEFAULT';
    }
    local $SIG{CHLD} = $sig_do;
    if (!defined($pid = open( KID, "-|" ))) {
        $arg{logger}->writeLogError("Cant fork: $!");
        return -1;
    }
    
    if ($pid) {
        
       
        eval {
           local $SIG{ALRM} = sub { die "Timeout by signal ALARM\n"; };
           alarm( $arg{timeout} );
           while (<KID>) {
               chomp;
               push @output, $_;
           }

           alarm(0);
        };
        if ($@) {
            $arg{logger}->writeLogInfo($@);

            $arg{logger}->writeLogInfo("Killing child process [$pid] ...");
            if ($pid != -1) {
                kill -9, $pid;
            }
            $arg{logger}->writeLogInfo("Killed");

            alarm(0);
            close KID;
            return (-1, join("\n", @output), -1);
        } else {
            if ($arg{wait_exit} == 1) {
                # We're waiting the exit code                
                waitpid($pid, 0);
                $return_code = $?;
            }
            close KID;
        }
    } else {
        # child
        # set the child process to be a group leader, so that
        # kill -9 will kill it and all its descendents
        setpgrp( 0, 0 );

        exec($arg{command});
        exit(0);
    }

    return (0, join("\n", @output), $return_code);
}
        
1;
