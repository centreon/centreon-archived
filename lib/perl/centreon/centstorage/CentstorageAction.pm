
use strict;
use warnings;

package centreon::centstorage::CentstorageAction;

use centreon::centstorage::CentstorageLib;
my %handlers = ('TERM' => {});

sub new {
    my $class = shift;
    my $self  = {};
    $self->{"logger"} = shift;
    $self->{"rebuild_progress"} = shift;
    $self->{"centreon_23_compatibility"} = shift;
    $self->{"dbcentreon"} = undef;
    $self->{"dbcentstorage"} = undef;
    $self->{"purge_delay"} = 3600;
    $self->{"last_purge_time"} = time() - $self->{"purge_delay"} - 5;

    $self->{"deleted_delay"} = 120;
    $self->{"last_deleted_time"} = time() - $self->{"deleted_delay"} - 5;

    $self->{"rrd_metrics_path"} = undef;
    $self->{"rrd_status_path"} = undef;

    $self->{"save_read"} = [];


    bless $self, $class;
    $self->set_signal_handlers;
    return $self;
}

sub set_signal_handlers {
    my $self = shift;

    $SIG{TERM} = \&class_handle_TERM;
    $handlers{'TERM'}->{$self} = sub { $self->handle_TERM() };
}

sub handle_TERM {
    my $self = shift;
    $self->{'logger'}->writeLogInfo("$$ Receiving order to stop...");

    $self->{'dbcentreon'}->disconnect() if (defined($self->{'dbcentreon'}));
    $self->{'dbcentstorage'}->disconnect() if (defined($self->{'dbcentstorage'}));
}

sub class_handle_TERM {
    foreach (keys %{$handlers{'TERM'}}) {
        &{$handlers{'TERM'}->{$_}}();
    }
    exit(0);
}

sub check_deleted {
    my $self = shift;
    my $pipe_write = $_[0];

    if (defined($self->{'centreon_23_compatibility'}) && $self->{'centreon_23_compatibility'} == 1) {
        return ;
    }

    if (time() < ($self->{"last_deleted_time"} + $self->{"deleted_delay"})) {
        return ;
    }

    my ($status, $stmt) = $self->{'dbcentstorage'}->query("SELECT `id`, `host_name`, `service_description`, `metrics`.metric_id FROM `index_data` LEFT JOIN `metrics` ON (index_data.id = metrics.index_id) WHERE index_data.to_delete = '1' ORDER BY id");
    return -1 if ($status == -1);
    my $current_index_id = -1;
    while ((my $data = $stmt->fetchrow_hashref())) {
        if ($current_index_id != $data->{'id'}) {
            if ($self->delete_rrd_file($self->{"rrd_status_path"}, $data->{'id'}) == 0) {
                $self->{'dbcentstorage'}->query("DELETE FROM index_data WHERE id = " . $data->{'id'});
            }
            $current_index_id = $data->{'id'};
            print $pipe_write "DELETECLEAN\t" . $data->{'host_name'} . "\t" . $data->{'service_description'} . "\n";
        }
        if (defined($data->{'metric_id'})) {
            if ($self->delete_rrd_file($self->{"rrd_metrics_path"}, $data->{'metric_id'}) == 0) {
                $self->{'dbcentstorage'}->query("DELETE FROM metrics WHERE metric_id = " . $data->{'metric_id'});
            }
        }
    }

    ###
    # Check metrics alone
    ###
    ($status, $stmt) = $self->{'dbcentstorage'}->query("SELECT `host_name`, `service_description`, `metrics`.metric_id, `metrics`.metric_name FROM `metrics` LEFT JOIN `index_data` ON (index_data.id = metrics.index_id) WHERE metrics.to_delete = '1'");
    return -1 if ($status == -1);
    while ((my $data = $stmt->fetchrow_hashref())) {
        if (defined($data->{'host_name'})) {
            print $pipe_write "DELETECLEAN\t" . $data->{'host_name'} . "\t" . $data->{'service_description'} . "\t" . $data->{'metric_name'} . "\n";
        }
        if ($self->delete_rrd_file($self->{"rrd_metrics_path"}, $data->{'metric_id'}) == 0) {
            $self->{'dbcentstorage'}->query("DELETE FROM metrics WHERE metric_id = " . $data->{'metric_id'});
        }
    }

    $self->{"last_deleted_time"} = time();
}

sub check_rebuild {
    my $self = shift;
    my $pipe_write = $_[0];

    return if ($self->{"rebuild_progress"} == 1);
    my ($status, $stmt) = $self->{'dbcentstorage'}->query("SELECT `host_name`, `service_description` FROM `index_data` WHERE `must_be_rebuild` IN ('1', '2') LIMIT 1");
        return -1 if ($status == -1);
        my $data = $stmt->fetchrow_hashref();
    if (defined($data)) {
        $self->{"rebuild_progress"} = 1;
        $self->{"logger"}->writeLogInfo("Rebuild detected: " . $data->{'host_name'} . "/" . $data->{'service_description'});
        print $pipe_write "REBUILDBEGIN\t" . $data->{'host_name'} . "\t" . $data->{'service_description'} . "\n";
    }
}

sub delete_rrd_file {
    my $self = shift;
    my ($path, $id) = @_;

    
    if (-e $path . "/" . $id . ".rrd") {
        if (unlink($path . "/" . $id . ".rrd")) {
            $self->{'logger'}->writeLogInfo("Delete RRD file " . $path . "/" . $id . ".rrd");
        } else {
            $self->{'logger'}->writeLogError("Cannot delete RRD file " . $path . "/" . $id . ".rrd: " . $!);
            return 1;
        }
    }
    return 0;
}

sub purge_mysql_and_rrd {
    my $self = shift;
    my ($status, $stmt, $rows, $data);
    my %cache_index_data = ();
    my %cache_services = ();

    # Get By direct
    ($status, $stmt) = $self->{'dbcentreon'}->query("SELECT host_host_id, service_service_id FROM host_service_relation WHERE hostgroup_hg_id IS NULL");
    return -1 if ($status == -1);
    $rows = [];
    while ($data = (shift(@$rows) ||
           shift(@{$rows = $stmt->fetchall_arrayref(undef,10_000)||[]}) ) ) {
        $cache_services{$$data[0] . ";" . $$data[1]} = 1;
    }

    # Get By Hostgroup
    ($status, $stmt) = $self->{'dbcentreon'}->query("SELECT host.host_id, host_service_relation.service_service_id FROM host, host_service_relation, hostgroup_relation WHERE host.host_id = hostgroup_relation.host_host_id AND hostgroup_relation.hostgroup_hg_id = host_service_relation.hostgroup_hg_id");
    return -1 if ($status == -1);
    $rows = [];
    while ($data = (shift(@$rows) ||
           shift(@{$rows = $stmt->fetchall_arrayref(undef,10_000)||[]}) ) ) {
        $cache_services{$$data[0] . ";" . $$data[1]} = 1;
    }

    ####
    # Cache Dir
    ####
    my @files = ();
    if (opendir(DIR, $self->{"rrd_status_path"})) {
        @files = grep { $_ ne '.' and $_ ne '..' } readdir DIR;
    } else {
        $self->{'logger'}->writeLogError("Can't opendir " . $self->{"rrd_status_path"} . ": $!");
    }
    
    ($status, $stmt) = $self->{'dbcentstorage'}->query("SELECT host_id, service_id, id FROM index_data");
    return -1 if ($status);
    $rows = [];
    while ($data = (shift(@$rows) ||
           shift(@{$rows = $stmt->fetchall_arrayref(undef,10_000)||[]}) ) ) {
        $cache_index_data{$$data[2]} = 1;
        if (defined($$data[0]) && defined($$data[1]) && !defined($cache_services{$$data[0] . ";" . $$data[1]})) {
            ($status, my $stmt2) = $self->{'dbcentstorage'}->query("SELECT metric_id FROM metrics WHERE index_id = '" . $$data[2] . "'");
            next if ($status == -1);
            while ((my $data2 = $stmt2->fetchrow_hashref())) {
                $self->{'dbcentstorage'}->query("DELETE FROM metrics WHERE metric_id = " . $data2->{'metric_id'});
                $self->delete_rrd_file($self->{"rrd_metrics_path"}, $data2->{'metric_id'});
            }
            $self->{'dbcentstorage'}->query("DELETE FROM index_data WHERE id = '" . $$data[2] . "'");
            $self->{'logger'}->writeLogInfo("Delete MySQL metrics " . $$data[0] . "/" . $$data[1]);
            $self->delete_rrd_file($self->{"rrd_status_path"}, $$data[2]);
        }
    }

    ###
    # Purge RRD Status
    ###
    foreach (@files) {
        if ($_ =~ /(.*)\.rrd$/ && !defined($cache_index_data{$1})) {
            $self->delete_rrd_file($self->{"rrd_status_path"}, $1);
        }
    }

    ###
    # Purge RRD Metrics
    ###
    @files = ();
    if (opendir(DIR, $self->{"rrd_metrics_path"})) {
        @files = grep { $_ ne '.' and $_ ne '..' } readdir DIR;
    } else {
        $self->{'logger'}->writeLogError("Can't opendir " . $self->{"rrd_metrics_path"} . ": $!");
    }

    my %cache_metrics_data = ();
    ($status, $stmt) = $self->{'dbcentstorage'}->query("SELECT metric_id FROM metrics");
    return -1 if ($status == -1);
    $rows = [];
    while ($data = (shift(@$rows) ||
            shift(@{$rows = $stmt->fetchall_arrayref(undef,10_000)||[]}) ) ) {
        $cache_metrics_data{$$data[0]} = 1;
    }

    foreach (@files) {
        if ($_ =~ /(.*)\.rrd$/ && !defined($cache_metrics_data{$1})) {
            $self->delete_rrd_file($self->{"rrd_metrics_path"}, $1);
        }
    }
}

sub get_centstorage_information {
    my $self = shift;
    my ($rrd_metrics_path, $rrd_status_path);

    my ($status, $stmt) = $self->{'dbcentstorage'}->query("SELECT RRDdatabase_path, RRDdatabase_status_path FROM config");
    my $data = $stmt->fetchrow_hashref();
    if (defined($data)) {
        $rrd_metrics_path = $data->{'RRDdatabase_path'};
        $rrd_status_path = $data->{'RRDdatabase_status_path'};
    }
    return ($status, $rrd_metrics_path, $rrd_status_path);
}

sub check_purge {
    my $self = shift;

    if (time() < ($self->{"last_purge_time"} + $self->{"purge_delay"})) {
        return ;
    }

    $self->purge_mysql_and_rrd();
    $self->{"last_purge_time"} = time();
}

sub main {
    my $self = shift;
    my ($dbcentreon, $dbcentstorage, $pipe_read, $pipe_write) = @_;
    my $status;

    $self->{'dbcentreon'} = $dbcentreon;
    $self->{'dbcentstorage'} = $dbcentstorage;
    
    ($status, $self->{"rrd_metrics_path"}, $self->{"rrd_status_path"}) = $self->get_centstorage_information();

    # We have to manage if you don't need infos
    $self->{'dbcentreon'}->force(0);
    $self->{'dbcentstorage'}->force(0);
    
    my $read_select = new IO::Select();
    $read_select->add($pipe_read);
    while (1) {
        my @rh_set = $read_select->can_read(5);
        if (scalar(@rh_set) > 0) {
            foreach my $rh (@rh_set) {
                my $read_done = 0;
                while ((my ($status_line, $readline) = centreon::centstorage::CentstorageLib::get_line_pipe($rh, \@{$self->{'save_read'}}, \$read_done))) {
                    class_handle_TERM() if ($status_line == -1);
                    last if ($status_line == 0);
                    my ($method, @fields) = split(/\t/, $readline);

                    # Check Type
                    if (defined($method) && $method eq "REBUILDFINISH") {
                        $self->{"rebuild_progress"} = 0;
                    }
                }
            }
        } else {
            $self->check_rebuild($pipe_write);
            $self->check_deleted($pipe_write);
            $self->check_purge();
        }
    }
}

1;
