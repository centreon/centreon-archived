################################################################################
# Copyright 2005-2013 MERETHIS
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
# As a special exception, the copyright holders of this program give MERETHIS 
# permission to link this program with independent modules to produce an executable, 
# regardless of the license terms of these independent modules, and to copy and 
# distribute the resulting executable under terms of MERETHIS choice, provided that 
# MERETHIS also meet, for each linked independent module, the terms  and conditions 
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
    $self->{launch_time} = time();
    $self->{msg_type5_disabled} = 0;
    $self->{queries_per_transaction} = 500;

    %{$self->{cache_host}} = ();
    %{$self->{cache_host_service}} = ();
    $self->{current_time} = time();
    $self->{retention_time} = undef;
    return $self;
}

# Get poller id from poller name
sub getPollerId($) {
    my $self = shift;
    my $instanceName = $_[0];

    my ($status, $sth) = $self->{cdb}->query("SELECT id 
                                  FROM nagios_server 
                                  WHERE name = " . $self->{cdb}->quote($instanceName) . " 
                                  AND ns_activate = '1' LIMIT 1");
    die ("Can't get poller id") if ($status == -1);
    while ((my $row = $sth->fetchrow_hashref())) {
        return $row->{'id'};
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
    my $self = shift;
    my $instance_name = $_[1];
    my $logFile = $_[0];
    my $ctime;
    my ($nbqueries, $counter) = (0, 0, 0);
    my @log_table_rows;

    # Open Log File for parsing
    if (!open (FILE, $_[0])){
        $self->{logger}->writeLogError("Cannot open file : $_[0]");
        return;
    }

    $self->{csdb}->transaction_mode(1);
    eval {
        my $sth = $self->{csdb}->{instance}->prepare("INSERT INTO logs (ctime, host_name, service_description, status, output, notification_cmd, notification_contact, type, retry, msg_type, instance_name, host_id, service_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            while (<FILE>) {
                my $cur_ctime;

                if ($_ =~ m/^\[([0-9]*)\]\sSERVICE ALERT\:\s(.*)$/) {
                    my @tab = split(/;/, $2);
                    next if (!defined($self->{cache_host_service}{$tab[0] . ":" . $tab[1]}->{service_id}));
                    $cur_ctime = $1;
                    push @log_table_rows, 
                    [($cur_ctime, $tab[0], $tab[1], $svc_status_code{$tab[2]}, $tab[5], undef, undef, $type_code{$tab[3]}, $tab[4], '0', $instance_name, $self->{cache_host}{$tab[0]}, $self->{cache_host_service}{$tab[0] . ":" . $tab[1]}->{service_id})];
                } elsif ($_ =~ m/^\[([0-9]*)\]\sHOST ALERT\:\s(.*)$/) {
                    my @tab = split(/;/, $2);
                    next if (!defined($self->{cache_host}{$tab[0]}));
                    $cur_ctime = $1;
                    push @log_table_rows, 
                    [($cur_ctime, $tab[0], undef, $host_status_code{$tab[1]}, $tab[4], undef, undef, $type_code{$tab[2]}, $tab[3], '1', $instance_name, $self->{cache_host}{$tab[0]}, undef)];
                } elsif ($_ =~ m/^\[([0-9]*)\]\sSERVICE NOTIFICATION\:\s(.*)$/) {
                    my @tab = split(/;/, $2);
                    next if (!defined($self->{cache_host_service}{$tab[1] . ":" . $tab[2]}->{service_id}));
                    $cur_ctime = $1;
                    push @log_table_rows, 
                    [($cur_ctime, $tab[1], $tab[2], $svc_status_code{$tab[3]}, $tab[5], $tab[4], $tab[0], '', 0, '2', $instance_name, $self->{cache_host}{$tab[1]}, $self->{cache_host_service}{$tab[1] . ":" . $tab[2]}->{service_id})];
                } elsif ($_ =~ m/^\[([0-9]*)\]\sHOST NOTIFICATION\:\s(.*)$/) {
                    my @tab = split(/;/, $2);
                    next if (!defined($self->{cache_host}{$tab[1]}));
                    $cur_ctime = $1;
                    push @log_table_rows, 
                    [($cur_ctime, $tab[1], undef, $host_status_code{$tab[2]}, $tab[4], $tab[3], $tab[0], '', 0, '3', $instance_name, $self->{cache_host}{$tab[1]}, undef)];
                } elsif ($_ =~ m/^\[([0-9]*)\]\sCURRENT\sHOST\sSTATE\:\s(.*)$/) {
                    my @tab = split(/;/, $2);
                    next if (!defined($self->{cache_host}{$tab[0]}));
                    $cur_ctime = $1;
                    push @log_table_rows, 
                    [($cur_ctime, $tab[0], undef, $host_status_code{$tab[1]}, undef, undef, undef, $type_code{$tab[2]}, 0, '7', $instance_name, $self->{cache_host}{$tab[0]}, undef)];
                } elsif ($_ =~ m/^\[([0-9]*)\]\sCURRENT\sSERVICE\sSTATE\:\s(.*)$/) {
                    my @tab = split(/;/, $2);
                    next if (!defined($self->{cache_host_service}{$tab[0] . ":" . $tab[1]}->{service_id}));
                    $cur_ctime = $1;
                    push @log_table_rows,
                    [($cur_ctime, $tab[0], $tab[1], $svc_status_code{$tab[2]}, undef, undef, undef,  $type_code{$tab[3]}, 0, '6', $instance_name, $self->{cache_host}{$tab[0]}, $self->{cache_host_service}{$tab[0] . ":" . $tab[1]}->{service_id})];
                } elsif ($_ =~ m/^\[([0-9]*)\]\sINITIAL\sHOST\sSTATE\:\s(.*)$/) {
                    my @tab = split(/;/, $2);
                    next if (!defined($self->{cache_host}{$tab[0]}));
                    $cur_ctime = $1;
                    push @log_table_rows, 
                    [($cur_ctime, $tab[0], undef, $host_status_code{$tab[1]}, undef, undef, undef, $type_code{$tab[2]}, 0, '9', $instance_name, $self->{cache_host}{$tab[0]}, undef)];
                } elsif ($_ =~ m/^\[([0-9]*)\]\sINITIAL\sSERVICE\sSTATE\:\s(.*)$/) {
                    my @tab = split(/;/, $2);
                    next if (!defined($self->{cache_host_service}{$tab[0] . ":" . $tab[1]}->{service_id}));
                    $cur_ctime = $1;
                    push @log_table_rows, 
                    [($cur_ctime, $tab[0], $tab[1], $svc_status_code{$tab[2]}, undef, undef, undef, $type_code{$tab[3]}, 0, '8', $instance_name, $self->{cache_host}{$tab[0]}, $self->{cache_host_service}{$tab[0] . ":" . $tab[1]}->{service_id})];
                } elsif ($_ =~ m/^\[([0-9]*)\]\sEXTERNAL\sCOMMAND\:\sACKNOWLEDGE\_SVC\_PROBLEM\;(.*)$/) {
                    $cur_ctime = $1;
                    my @tab = split(/;/, $2);
                    next if (!defined($self->{cache_host_service}{$tab[0] . ":" . $tab[1]}->{service_id}));
                    push @log_table_rows, 
                    [($cur_ctime, $tab[0], $tab[1], undef, $tab[6], undef, $tab[5], undef, 0, '10', $instance_name, $self->{cache_host}{$tab[0]}, $self->{cache_host_service}{$tab[0] . ":" . $tab[1]}->{service_id})];
                } elsif ($_ =~ m/^\[([0-9]*)\]\sEXTERNAL\sCOMMAND\:\sACKNOWLEDGE\_HOST\_PROBLEM\;(.*)$/) {
                    $cur_ctime = $1;
                    my @tab = split(/;/, $2);
                    next if (!defined($self->{cache_host}{$tab[0]}));
                    push @log_table_rows, 
                    [($cur_ctime, $tab[0], undef, undef, $tab[5], undef, $tab[4], undef, 0, '11', $instance_name, $self->{cache_host}{$tab[0]}, undef)];
                } elsif ($_ =~ m/^\[([0-9]*)\]\sWarning\:\s(.*)$/) {
                    my $tab = $2;
                    $cur_ctime = $1;
                    push @log_table_rows, 
                    [($cur_ctime, undef, undef, undef, $tab, undef, undef, undef, 0, '4', $instance_name, undef, undef)];
                } elsif ($_ =~ m/^\[([0-9]*)\]\s(.*)$/ && (!$self->{msg_type5_disabled})) {
                    $cur_ctime = $1;
                    my $tab = $2;
                    push @log_table_rows, 
                    [($cur_ctime, undef, undef, undef, $tab, undef, undef, undef, 0, '5', $instance_name, undef, undef)];
                }
                $counter++;
                $nbqueries++;
                if ($nbqueries == $self->{queries_per_transaction}) {
                    $self->commit_to_log($sth, \@log_table_rows);
                    $nbqueries = 0;
                    @log_table_rows = ();
                }
        }
        $self->commit_to_log($sth, \@log_table_rows);
    };
    close FILE;
    if ($@) {
        $self->{csdb}->rollback;
        die "Database error: $@";
    }
    $self->{csdb}->transaction_mode(0);
}

=head2 date_to_time($date)

Convert $date to a timestamp.

=cut
sub date_to_time($) {
    my $date = shift;

    $date =~ s|-|/|g;
    return int(`date -d $date +%s`);
}

sub parseArchive {
    my $self = shift;
    my ($instance) = @_;
    my $archives;

    # Get instance name
    my ($status, $sth) = $self->{cdb}->query("SELECT localhost, id, name FROM `nagios_server` WHERE id = " . $instance);
    if ($status == -1) {
        die "Can't get information on poller";
    }
    my $tmp_server = $sth->fetchrow_hashref();

    if ($tmp_server->{'localhost'}) {
        ($status, $sth) = $self->{cdb}->query("SELECT `log_archive_path`
                                            FROM `cfg_nagios`, `nagios_server` 
                                            WHERE `nagios_server_id` = '$instance' 
                                             AND `nagios_server`.`id` = `cfg_nagios`.`nagios_server_id` 
                                             AND `nagios_server`.`ns_activate` = '1' 
                                             AND `cfg_nagios`.`nagios_activate` = '1' LIMIT 1");
        if ($status == -1) {
            die "Can't get information on poller";
        }
        my $data = $sth->fetchrow_hashref();
        if (!$data->{'log_archive_path'}) {
            die "Could not find local var log directory";
        }
        $archives = $data->{log_archive_path};
    } else {
        $archives = $self->{centreon_config}->{VarLib} . "/log/$instance/archives/";
    }

    my @log_files = split /\s/,`ls $archives`;
    foreach (@log_files) {
        $self->parseFile($archives.$_, $tmp_server->{'name'});
    }
}

sub run {
    my $self = shift;

    $self->SUPER::run();

    if (defined($self->{opt_s})) {
        if ($self->{opt_s} !~ m/\d{2}-\d{2}-\d{4}/) {
            $self->{logger}->writeLogError("Invalid start date provided");
            exit 1;
        }
    }

    # Get conf Data
    my ($status, $sth_config) = $self->{csdb}->query("SELECT `archive_log`, `archive_retention`, `nagios_log_file`  FROM `config` LIMIT 1");
    die("Cannot get archive log path") if ($status == -1);
    my $data = $sth_config->fetchrow_hashref();
    die("Cannot get archive log path") if (!$data->{'archive_log'});

    my $retention = $data->{'archive_retention'};

    my ($day,$month,$year) = (localtime(time()))[3,4,5];
    $self->{retention_time} =  mktime(0,0,0,$day-$retention,$month,$year,0,0,-1);

    # Get cache
    my $filter_instance = "";
    my $instanceId;
    if (defined($self->{opt_p})) {
        $instanceId = $self->getPollerId($self->{opt_p});
        if ($instanceId == 0) {
            $self->{logger}->writeLogError("Unknown poller $self->{opt_p}");
            die("Unknown poller $self->{opt_p}");
        }
        $filter_instance = "ns_host_relation.nagios_server_id = $instanceId AND ";
    }

    ($status, my $sth_cache) = $self->{cdb}->query("SELECT host.host_id, host.host_name FROM host, ns_host_relation WHERE ${filter_instance}ns_host_relation.host_host_id = host.host_id AND host.host_activate = '1'");
    die("Cannot get host cache") if ($status == -1);
    while ((my $tmp_cache = $sth_cache->fetchrow_hashref())) {
        $self->{cache_host}{$tmp_cache->{'host_name'}} = $tmp_cache->{'host_id'};
    }

    ($status, $sth_cache) = $self->{cdb}->query("SELECT host.host_name, host.host_id, service.service_id, service.service_description FROM host, host_service_relation, service, ns_host_relation WHERE ${filter_instance}ns_host_relation.host_host_id = host.host_id AND host.host_id = host_service_relation.host_host_id AND host_service_relation.service_service_id = service.service_id AND service.service_activate = '1'");   
    die("Cannot get service cache") if ($status == -1);
    while ((my $tmp_cache = $sth_cache->fetchrow_hashref())) {
        $self->{cache_host_service}{$tmp_cache->{'host_name'} . ':' . $tmp_cache->{'service_description'}} = {'host_id' =>  $tmp_cache->{'host_id'}, 'service_id' =>  $tmp_cache->{'service_id'}};
    }

    ($status, $sth_cache) = $self->{cdb}->query("SELECT host.host_name, host.host_id, service.service_id, service.service_description FROM host, host_service_relation, hostgroup_relation, service, ns_host_relation WHERE ${filter_instance}ns_host_relation.host_host_id = host.host_id AND host.host_id = hostgroup_relation.host_host_id AND hostgroup_relation.hostgroup_hg_id = host_service_relation.hostgroup_hg_id AND host_service_relation.service_service_id = service.service_id AND service.service_activate = '1'");
    die("Cannot get service by hostgroup cache") if ($status == -1);
    while ((my $tmp_cache = $sth_cache->fetchrow_hashref())) {
        $self->{cache_host_service}{$tmp_cache->{'host_name'} . ':' . $tmp_cache->{'service_description'}} = {'host_id' =>  $tmp_cache->{'host_id'}, 'service_id' =>  $tmp_cache->{'service_id'}};
    }

    if (defined($self->{opt_p}) && $instanceId) {
        if (!defined($self->{opt_s})) {
            $self->{csdb}->query("DELETE FROM `logs` WHERE instance_name = " . $self->{csdb}->quote($self->{opt_p}) . " AND `ctime` < $self->{current_time}");
        } else {
            my $limit = date_to_time($self->{opt_s});
            if ($limit >  $self->{retention_time}) {
                $self->{retention_time} = $limit;
            }
            $self->{csdb}->query("DELETE FROM `logs` WHERE `ctime` >= $limit AND `ctime` < $self->{current_time} AND instance_name = " . $self->{csdb}->quote($self->{opt_p}));
        }
        $self->parseArchive($instanceId);
    } else {
        my ($status, $sth) = $self->{cdb}->query("SELECT `id`, `name`, `localhost` FROM `nagios_server` WHERE `ns_activate` = 1");
        if ($status == -1) {
            die("Can't get poller list");
        }
        while (my $ns_server = $sth->fetchrow_hashref()) {
            if (!defined($self->{opt_s})) {
                $self->{csdb}->query("DELETE FROM `logs` WHERE `ctime` < $self->{current_time} AND instance_name = " . $self->{csdb}->quote($ns_server->{'name'}));
            } else {
                my $limit = date_to_time($self->{opt_s});
                if ($limit > $self->{retention_time}) {
                    $self->{retention_time} = $limit;
                }
                $self->{csdb}->query("DELETE FROM `logs` WHERE `ctime` >= $limit AND `ctime` < $self->{current_time} AND instance_name = " . $self->{csdb}->quote($ns_server->{'name'}));
            }
            $self->parseArchive($ns_server->{'id'});
        }
    }

    $self->{logger}->writeLogInfo("Done");
}

1;
