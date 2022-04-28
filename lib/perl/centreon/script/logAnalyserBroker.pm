################################################################################
# Copyright 2005-2019 Centreon
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

package centreon::script::logAnalyserBroker;

use strict;
use warnings;
use POSIX;
use centreon::script;

use base qw(centreon::script);

my %svc_status_code = (
    "OK"       => 0,
    "WARNING"  => 1,
    "CRITICAL" => 2,
    "UNKNOWN"  => 3
);
my %host_status_code = (
    "UP"            => 0,
    "DOWN"          => 1,
    "UNREACHABLE"   => 2,
    "PENDING"       => 4
);
my %type_code = (
    "SOFT" => 0,
    "HARD" => 1
);

sub new {
    my $class = shift;
    my $self = $class->SUPER::new("logAnalyserBroker",
        centreon_db_conn => 1,
        centstorage_db_conn => 1,
    );

    bless $self, $class;
    $self->add_options(
        "p=s" => \$self->{opt_p}, "poller" => \$self->{opt_p},
        "s=s" => \$self->{opt_s}, "startdate=s" => \$self->{opt_s}
    );
    
    $self->{msg_type5_disabled} = 0;
    $self->{queries_per_transaction} = 500;

    %{$self->{cache_host}} = ();
    %{$self->{cache_host_service}} = ();
    my ($day,$month,$year) = (localtime(time()))[3,4,5];
    $self->{current_time} = mktime(0,0,0,$day,$month,$year,0,0,-1);
    return $self;
}

# Get poller id from poller name
sub getPollerId {
    my ($self, $instance_name) = @_;

    my ($status, $sth) = $self->{cdb}->query("SELECT id 
        FROM nagios_server 
        WHERE name = " . $self->{cdb}->quote($instance_name) . " 
        AND ns_activate = '1' LIMIT 1");
    if ($status == -1) {
        $self->{logger}->writeLogError("Can't get poller id");
        return 0;
    }
    while ((my $row = $sth->fetchrow_hashref())) {
        return $row->{id};
    }
    return 0;
}

sub commit_to_log {
    my ($self, $sth, $log_table_rows) = @_;
    my @tuple_status;

    $sth->execute_for_fetch(sub { shift @$log_table_rows }, \@tuple_status);
    $self->{csdb}->commit;
    $self->{csdb}->transaction_mode(1);
}

# Parsing .log
sub parseFile($$) {
    my ($self, $logFile, $instance_name) = @_;
    my $ctime;
    my ($nbqueries, $counter) = (0, 0, 0);
    my @log_table_rows;

    # Open Log File for parsing
    if (!open(FILE, $logFile)) {
        $self->{logger}->writeLogError("Cannot open file : $logFile");
        return;
    }

    $self->{csdb}->transaction_mode(1);
    eval {
        my $sth = $self->{csdb}->{instance}->prepare("INSERT INTO logs (ctime, host_name, service_description, status, output, notification_cmd, notification_contact, type, retry, msg_type, instance_name, host_id, service_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        while (<FILE>) {
            my $cur_ctime;

            if ($_ =~ m/^\[([0-9]*)\](?:\s\[[0-9]*\])*\sSERVICE ALERT\:\s(.*)$/) {
                $cur_ctime = $1;
                next if ($cur_ctime < $self->{retention_time});
                my @tab = split(/;/, $2);
                next if (!defined($self->{cache_host_service}{$tab[0] . ":" . $tab[1]}->{service_id}));
                push @log_table_rows, [($cur_ctime, $tab[0], $tab[1], $svc_status_code{$tab[2]}, $tab[5], undef, undef, $type_code{$tab[3]}, $tab[4], '0', $instance_name, $self->{cache_host}{$tab[0]}, $self->{cache_host_service}{$tab[0] . ":" . $tab[1]}->{service_id})];
            } elsif ($_ =~ m/^\[([0-9]*)\](?:\s\[[0-9]*\])*\sHOST ALERT\:\s(.*)$/) {
                $cur_ctime = $1;
                next if ($cur_ctime < $self->{retention_time});
                my @tab = split(/;/, $2);
                next if (!defined($self->{cache_host}{$tab[0]}));
                push @log_table_rows, [($cur_ctime, $tab[0], undef, $host_status_code{$tab[1]}, $tab[4], undef, undef, $type_code{$tab[2]}, $tab[3], '1', $instance_name, $self->{cache_host}{$tab[0]}, undef)];
            } elsif ($_ =~ m/^\[([0-9]*)\](?:\s\[[0-9]*\])*\sSERVICE NOTIFICATION\:\s(.*)$/) {
                $cur_ctime = $1;
                next if ($cur_ctime < $self->{retention_time});
                my @tab = split(/;/, $2);
                next if (!defined($self->{cache_host_service}{$tab[1] . ":" . $tab[2]}->{service_id}));
                push @log_table_rows, [($cur_ctime, $tab[1], $tab[2], $svc_status_code{$tab[3]}, $tab[5], $tab[4], $tab[0], '', 0, '2', $instance_name, $self->{cache_host}{$tab[1]}, $self->{cache_host_service}{$tab[1] . ":" . $tab[2]}->{service_id})];
            } elsif ($_ =~ m/^\[([0-9]*)\](?:\s\[[0-9]*\])*\sHOST NOTIFICATION\:\s(.*)$/) {
                $cur_ctime = $1;
                next if ($cur_ctime < $self->{retention_time});
                my @tab = split(/;/, $2);
                next if (!defined($self->{cache_host}{$tab[1]}));
                push @log_table_rows, [($cur_ctime, $tab[1], undef, $host_status_code{$tab[2]}, $tab[4], $tab[3], $tab[0], '', 0, '3', $instance_name, $self->{cache_host}{$tab[1]}, undef)];
            } elsif ($_ =~ m/^\[([0-9]*)\](?:\s\[[0-9]*\])*\sCURRENT\sHOST\sSTATE\:\s(.*)$/) {
                $cur_ctime = $1;
                next if ($cur_ctime < $self->{retention_time});
                my @tab = split(/;/, $2);
                next if (!defined($self->{cache_host}{$tab[0]}));
                push @log_table_rows, [($cur_ctime, $tab[0], undef, $host_status_code{$tab[1]}, undef, undef, undef, $type_code{$tab[2]}, 0, '7', $instance_name, $self->{cache_host}{$tab[0]}, undef)];
            } elsif ($_ =~ m/^\[([0-9]*)\](?:\s\[[0-9]*\])*\sCURRENT\sSERVICE\sSTATE\:\s(.*)$/) {
                $cur_ctime = $1;
                next if ($cur_ctime < $self->{retention_time});
                my @tab = split(/;/, $2);
                next if (!defined($self->{cache_host_service}{$tab[0] . ":" . $tab[1]}->{service_id}));
                push @log_table_rows, [($cur_ctime, $tab[0], $tab[1], $svc_status_code{$tab[2]}, undef, undef, undef,  $type_code{$tab[3]}, 0, '6', $instance_name, $self->{cache_host}{$tab[0]}, $self->{cache_host_service}{$tab[0] . ":" . $tab[1]}->{service_id})];
            } elsif ($_ =~ m/^\[([0-9]*)\](?:\s\[[0-9]*\])*\sINITIAL\sHOST\sSTATE\:\s(.*)$/) {
                $cur_ctime = $1;
                next if ($cur_ctime < $self->{retention_time});
                my @tab = split(/;/, $2);
                next if (!defined($self->{cache_host}{$tab[0]}));
                push @log_table_rows, [($cur_ctime, $tab[0], undef, $host_status_code{$tab[1]}, undef, undef, undef, $type_code{$tab[2]}, 0, '9', $instance_name, $self->{cache_host}{$tab[0]}, undef)];
            } elsif ($_ =~ m/^\[([0-9]*)\](?:\s\[[0-9]*\])*\sINITIAL\sSERVICE\sSTATE\:\s(.*)$/) {
                $cur_ctime = $1;
                next if ($cur_ctime < $self->{retention_time});
                my @tab = split(/;/, $2);
                next if (!defined($self->{cache_host_service}{$tab[0] . ":" . $tab[1]}->{service_id}));
                push @log_table_rows, [($cur_ctime, $tab[0], $tab[1], $svc_status_code{$tab[2]}, undef, undef, undef, $type_code{$tab[3]}, 0, '8', $instance_name, $self->{cache_host}{$tab[0]}, $self->{cache_host_service}{$tab[0] . ":" . $tab[1]}->{service_id})];
            } elsif ($_ =~ m/^\[([0-9]*)\](?:\s\[[0-9]*\])*\sEXTERNAL\sCOMMAND\:\sACKNOWLEDGE\_SVC\_PROBLEM\;(.*)$/) {
                $cur_ctime = $1;
                next if ($cur_ctime < $self->{retention_time});
                my @tab = split(/;/, $2);
                next if (!defined($self->{cache_host_service}{$tab[0] . ":" . $tab[1]}->{service_id}));
                push @log_table_rows, [($cur_ctime, $tab[0], $tab[1], undef, $tab[6], undef, $tab[5], undef, 0, '10', $instance_name, $self->{cache_host}{$tab[0]}, $self->{cache_host_service}{$tab[0] . ":" . $tab[1]}->{service_id})];
            } elsif ($_ =~ m/^\[([0-9]*)\](?:\s\[[0-9]*\])*\sEXTERNAL\sCOMMAND\:\sACKNOWLEDGE\_HOST\_PROBLEM\;(.*)$/) {
                $cur_ctime = $1;
                next if ($cur_ctime < $self->{retention_time});
                my @tab = split(/;/, $2);
                next if (!defined($self->{cache_host}{$tab[0]}));
                push @log_table_rows, [($cur_ctime, $tab[0], undef, undef, $tab[5], undef, $tab[4], undef, 0, '11', $instance_name, $self->{cache_host}{$tab[0]}, undef)];
            } elsif ($_ =~ m/^\[([0-9]*)\](?:\s\[[0-9]*\])*\sWarning\:\s(.*)$/) {
                $cur_ctime = $1;
                next if ($cur_ctime < $self->{retention_time});
                my $tab = $2;
                push @log_table_rows, [($cur_ctime, undef, undef, undef, $tab, undef, undef, undef, 0, '4', $instance_name, undef, undef)];
            } elsif ($_ =~ m/^\[([0-9]*)\](?:\s\[[0-9]*\])*\s(.*)$/ && (!$self->{msg_type5_disabled})) {
                $cur_ctime = $1;
                next if ($cur_ctime < $self->{retention_time});
                my $tab = $2;
                push @log_table_rows, [($cur_ctime, undef, undef, undef, $tab, undef, undef, undef, 0, '5', $instance_name, undef, undef)];
            }

            if (scalar(@log_table_rows) > $self->{queries_per_transaction}) {
                $self->{counter} += scalar(@log_table_rows);
                $self->commit_to_log($sth, \@log_table_rows);
                @log_table_rows = ();
            }
        }
        $self->{counter} += scalar(@log_table_rows);
        $self->commit_to_log($sth, \@log_table_rows);
    };
    close FILE;
    if ($@) {
        $self->{csdb}->rollback;
        die "Database error: $@";
    }
    $self->{csdb}->transaction_mode(0);
}

sub parseArchive {
    my ($self, $instance) = @_;
    my $archives;

    # Get instance name
    my ($status, $sth) = $self->{cdb}->query("SELECT localhost, id, name FROM `nagios_server` WHERE id = " . $instance);
    if ($status == -1) {
        $self->{logger}->writeLogInfo("Can't get information on poller");
        return ;
    }
    my $poller = $sth->fetchrow_hashref();

    if ($poller->{localhost}) {
        ($status, $sth) = $self->{cdb}->query("SELECT `log_file`
            FROM `cfg_nagios`, `nagios_server` 
            WHERE `nagios_server_id` = '$instance' 
                AND `nagios_server`.`id` = `cfg_nagios`.`nagios_server_id` 
                AND `nagios_server`.`ns_activate` = '1' 
                AND `cfg_nagios`.`nagios_activate` = '1' LIMIT 1");
        if ($status == -1) {
            $self->{logger}->writeLogError("Can't get information on poller");
            return ;
        }
        my $data = $sth->fetchrow_hashref();
        $data->{log_archive_path} = $data->{log_file};
        $data->{log_archive_path} =~ s/(.*)\/.*/$1\/archives\//;
        if (!$data->{log_archive_path}) {
            $self->{logger}->writeLogError("Can't find local varlib directory \"$data->{log_archive_path}\"");
            return ;
        }
        $archives = $data->{log_archive_path};
    } else {
        $archives = $self->{centreon_config}->{VarLib} . "/log/$instance/archives/";
    }

    my @files;
    if (!opendir(DIR, $archives)) {
        $self->{logger}->writeLogError("Can't find $archives directory");
        return ;
    }
    while (my $file = readdir(DIR)) {
        push(@files, $file) if ($file !~ m/^\.|\.gz$/msi);
    }
    closedir DIR;

    @files = sort { ($a =~ m/.*-(.*?)$/msi)[0] <=> ($b =~ m/.*-(.*?)$/msi)[0] } @files;

    while (my $file = shift @files) {
        $self->parseFile($archives . $file, $poller->{name});
    }
}

sub run {
    my $self = shift;

    $self->SUPER::run();
    $self->{logger}->writeLogInfo("Starting program...");

    if (defined($self->{opt_s})) {
        if ($self->{opt_s} !~ m/\d{2}-\d{2}-\d{4}/) {
            $self->{logger}->writeLogError("Invalid start date provided");
            return ;
        }
    }

    # Get conf Data
    if (defined($self->{opt_s})) {
        my ($day,$month,$year) = (split(/-/, $self->{opt_s}))[1,0,2];
        $self->{retention_time} = mktime(0,0,0,$day,$month-1,$year-1900,0,0,-1);
    } else {
        my ($status, $sth_config) = $self->{csdb}->query("SELECT `archive_retention` FROM `config` LIMIT 1");
        if ($status == -1) {
            $self->{logger}->writeLogError("Can't get logs retention duration");
            return;
        }
        my $data = $sth_config->fetchrow_hashref();

        my ($day,$month,$year) = (localtime(time()))[3,4,5];
        $self->{retention_time} =  mktime(0,0,0,$day-$data->{'archive_retention'},$month,$year,0,0,-1);
    }

    # Get cache
    my $filter_instance = "";
    my $instance_id;
    if (defined($self->{opt_p})) {
        $instance_id = $self->getPollerId($self->{opt_p});
        if ($instance_id == 0) {
            $self->{logger}->writeLogError("Unknown poller $self->{opt_p}");
            return ;
        }
        $filter_instance = "ns_host_relation.nagios_server_id = '$instance_id' AND";
    }

    my ($status, $sth_cache) = $self->{cdb}->query("SELECT host.host_id, host.host_name 
        FROM host, ns_host_relation 
        WHERE $filter_instance ns_host_relation.host_host_id = host.host_id 
        AND host.host_activate = '1'");
    if ($status == -1) {
        $self->{logger}->writeLogError("Can't get host cache");
        return ;
    }
    while (my $tmp_cache = $sth_cache->fetchrow_hashref()) {
        $self->{cache_host}{$tmp_cache->{'host_name'}} = $tmp_cache->{'host_id'};
    }

    ($status, $sth_cache) = $self->{cdb}->query("SELECT host.host_name, host.host_id, service.service_id, service.service_description 
        FROM host, host_service_relation, service, ns_host_relation 
        WHERE $filter_instance ns_host_relation.host_host_id = host.host_id 
        AND host.host_id = host_service_relation.host_host_id 
        AND host_service_relation.service_service_id = service.service_id 
        AND service.service_activate = '1'");
    if ($status == -1) {
        $self->{logger}->writeLogError("Can't get service cache");
        return;
    }
    while ((my $tmp_cache = $sth_cache->fetchrow_hashref())) {
        $self->{cache_host_service}{$tmp_cache->{'host_name'} . ':' . $tmp_cache->{'service_description'}} = {'host_id' =>  $tmp_cache->{'host_id'}, 'service_id' =>  $tmp_cache->{'service_id'}};
    }

    ($status, $sth_cache) = $self->{cdb}->query("SELECT host.host_name, host.host_id, service.service_id, service.service_description 
        FROM host, host_service_relation, hostgroup_relation, service, ns_host_relation 
        WHERE $filter_instance ns_host_relation.host_host_id = host.host_id 
        AND host.host_id = hostgroup_relation.host_host_id 
        AND hostgroup_relation.hostgroup_hg_id = host_service_relation.hostgroup_hg_id 
        AND host_service_relation.service_service_id = service.service_id 
        AND service.service_activate = '1'");
    if ($status == -1) {
        $self->{logger}->writeLogError("Can't get service by hostgroup cache");
        return;
    }
    while ((my $tmp_cache = $sth_cache->fetchrow_hashref())) {
        $self->{cache_host_service}{$tmp_cache->{host_name} . ':' . $tmp_cache->{'service_description'}} = { host_id =>  $tmp_cache->{host_id}, service_id => $tmp_cache->{service_id}};
    }

    $self->{logger}->writeLogInfo("Starting logs import from " . localtime($self->{retention_time}) . " to " . localtime($self->{current_time}));

    if (defined($self->{opt_p}) && $instance_id) {
	    $self->{logger}->writeLogInfo("Processing poller $self->{opt_p}");

        $self->{logger}->writeLogInfo("Purging data");
        $self->{csdb}->query("DELETE FROM `logs` 
            WHERE `ctime` >= $self->{retention_time} 
            AND `ctime` < $self->{current_time} 
            AND instance_name = " . $self->{csdb}->quote($self->{opt_p}));
        $self->{logger}->writeLogInfo("Purge completed");

        $self->{counter} = 0;
        $self->{logger}->writeLogInfo("Importing data");
        $self->parseArchive($instance_id);
        $self->{logger}->writeLogInfo("Import completed ($self->{counter} entries)");
    } else {
        my ($status, $sth) = $self->{cdb}->query("SELECT `id`, `name`, `localhost` FROM `nagios_server` WHERE `ns_activate` = 1");
        if ($status == -1) {
            $self->{logger}->writeLogError("Can't get poller list");
        } else {
            while (my $ns_server = $sth->fetchrow_hashref()) {
                $self->{logger}->writeLogInfo("Processing poller $ns_server->{'name'}");

                $self->{logger}->writeLogInfo("Purging data");
                $self->{csdb}->query("DELETE FROM `logs` 
                    WHERE `ctime` >= $self->{retention_time} 
                    AND `ctime` < $self->{current_time} 
                    AND instance_name = " . $self->{csdb}->quote($ns_server->{'name'}));
                $self->{logger}->writeLogInfo("Purging completed");
                
                $self->{counter} = 0;
                $self->{logger}->writeLogInfo("Importing data");
                $self->parseArchive($ns_server->{'id'});
                $self->{logger}->writeLogInfo("Import completed ($self->{counter} entries)");
            }
        }
    }

    $self->{logger}->writeLogInfo("Exiting program...");
}

1;
