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

use strict;
use warnings;

package centreon::centstorage::CentstorageRebuild;
my %handlers = (TERM => {});

sub new {
    my $class = shift;
    my $self  = {};
    $self->{logger} = shift;
    $self->{dbcentstorage} = undef;

    bless $self, $class;
    $self->set_signal_handlers;
    return $self;
}

sub set_signal_handlers {
    my $self = shift;

    $SIG{TERM} = \&class_handle_TERM;
    $handlers{TERM}->{$self} = sub { $self->handle_TERM() };
}

sub handle_TERM {
    my $self = shift;
    $self->{logger}->writeLogInfo("$$ Receiving order to stop...");

    eval {
        local $SIG{ALRM} = sub { die "alarm\n" };
        alarm 10;
        $self->{dbcentstorage}->kill();
        alarm 0;
    };
    if ($@) {
        $self->{logger}->writeLogError("Can't kill rebuild request");
    }
    $self->{dbcentstorage}->disconnect() if (defined($self->{dbcentstorage}));
}

sub class_handle_TERM {
    foreach (keys %{$handlers{TERM}}) {
        &{$handlers{TERM}->{$_}}();
    }
    exit(0);
}

sub main {
    my $self = shift;
    my ($dbcentstorage, $index_id, $interval, $rrd, $local_rrd) = @_;
    my $status;
    my $stmt;

    $self->{dbcentstorage} = $dbcentstorage;
    ### Update for UI
    ($status, $stmt) = $self->{dbcentstorage}->query("UPDATE index_data SET `must_be_rebuild` = '2' WHERE id = " . $index_id);
    if ($status == -1) {
        $self->{logger}->writeLogError("rebuild cannot update index_id $index_id");
        return 1;
    }

    ###
    # Get By Metric_id
    ###
    ($status, $stmt) = $self->{dbcentstorage}->query("SELECT metric_id, metric_name, data_source_type FROM metrics WHERE index_id = " . $index_id);
    if ($status == -1) {
        $self->{logger}->writeLogError("rebuild cannot get metrics list");
        return 1;
    }
    while ((my $data = $stmt->fetchrow_hashref())) {
        ($status, my $stmt2) = $self->{dbcentstorage}->query("SELECT ctime, value FROM data_bin WHERE id_metric = " . $data->{metric_id} . " ORDER BY ctime ASC");
        if ($status == -1) {
            $self->{logger}->writeLogError("rebuild cannot get metric_id datas " . $data->{metric_id});
            return 1;
        }

        ### Delete RRD
        $status = $rrd->delete_rrd_metric($data->{metric_id});

        my $rows = [];
        while (my $data2 = (shift(@$rows) ||
                            shift(@{$rows = $stmt2->fetchall_arrayref(undef,10_000)||[]}) ) ) {
            $rrd->add_metric($data->{metric_id}, $data->{metric_name}, $interval, $data->{data_source_type}, $$data2[0], $$data2[1], $local_rrd);
        }
        $rrd->flush_metric($data->{metric_id});
    }

    ### Update for UI
    ($status, $stmt) = $self->{dbcentstorage}->query("UPDATE index_data SET `must_be_rebuild` = '0' WHERE id = " . $index_id);
    if ($status == -1) {
        $self->{logger}->writeLogError("rebuild cannot update index_id $index_id");
        return 1;
    }

    return 0;
}

1;
