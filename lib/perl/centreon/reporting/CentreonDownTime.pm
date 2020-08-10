################################################################################
# Copyright 2005-2020 Centreon
# Centreon is developed by : Julien Mathis and Romain Le Merlus under
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

use strict;
use warnings;

package centreon::reporting::CentreonDownTime;

# Constructor
# parameters:
# $logger: instance of class CentreonLogger
# $centreon: Instance of centreonDB class for connection to Centreon database
# $centstorage: (optionnal) Instance of centreonDB class for connection to Centstorage database
sub new {
    my $class = shift;
    my $self  = {};
    $self->{"logger"}    = shift;
    $self->{"centstatus"} = shift;
    if (@_) {
        $self->{"centstorage"}  = shift;
    }
    bless $self, $class;
    return $self;
}

# returns two references to two hash tables => hosts indexed by id and hosts indexed by name
sub getDowntimes {
    my $self = shift;
    my $centreon = $self->{"centstatus"};
    my $allIds = shift;
    my $start = shift;
    my $end = shift;
    my $type = shift; # if 1 => host, if 2 => service
    my $query;

    $query = "SELECT DISTINCT h.host_id, s.service_id, " .
             "d.actual_start_time, d.actual_end_time " .
             "FROM `hosts` h, `downtimes` d " .
             "LEFT JOIN services s ON s.service_id = d.service_id " .
             "WHERE started = 1 " .
             "AND d.host_id = h.host_id ";
    if ($type == 1) {
        $query .= "AND d.type = 2 "; # That can be confusing, but downtime_type 2 is for host
    } elsif ($type == 2) {
        $query .= "AND d.type = 1 "; # That can be confusing, but downtime_type 1 is for service
    }
    $query .= "AND (actual_start_time < " . $end . " AND actual_start_time IS NOT NULL) " .
             "AND (actual_end_time > " . $start . " OR actual_end_time IS NULL) " .
             "ORDER BY h.host_id ASC, actual_start_time ASC, actual_end_time ASC";

    my ($status, $sth) = $centreon->query($query);

    my @periods = ();
    while (my $row = $sth->fetchrow_hashref()) {
        my $id = $row->{"host_id"};
        if ($type == 2) {
            $id .= ";;" . $row->{"service_id"}
        }
        if (defined($allIds->{$id})) {
            if ($row->{"actual_start_time"} < $start) {
                $row->{"actual_start_time"} = $start;
            }
            if (!defined $row->{"actual_end_time"} || $row->{"actual_end_time"} > $end) {
                $row->{"actual_end_time"} = $end;
            }

            my $insert = 1;
            for (my $i = 0; $i < scalar(@periods) && $insert; $i++) {
                my $checkTab = $periods[$i];
                if ($checkTab->[0] eq $id){
                    if ($row->{"actual_start_time"} <= $checkTab->[2] && $row->{"actual_end_time"} <= $checkTab->[2]) {
                        $insert = 0;
                    } elsif ($row->{"actual_start_time"} <= $checkTab->[2] && $row->{"actual_end_time"} > $checkTab->[2]) {
                        $checkTab->[2] = $row->{"actual_end_time"};
                        $periods[$i] = $checkTab;
                        $insert = 0;
                    }
                }
            }
            if ($insert) {
                my @tab = ($id, $row->{"actual_start_time"}, $row->{"actual_end_time"});
                $periods[scalar(@periods)] = \@tab;
            }
        }
    }
    $sth->finish();
    return (\@periods);
}

sub splitInsertEventDownTime {
    my $self = shift;

    my $objectId = shift;
    my $start = shift;
    my $end = shift;
    my $downtimes = shift;
    my $state = shift;

    my @events = ();
    my $total = 0;
    if ($state ne "" && defined($downtimes) && defined($state) && $state != 0) {
        $total = scalar(@$downtimes);
    }
    for (my $i = 0; $i < $total && $start < $end; $i++) {
         my $tab = $downtimes->[$i];
         my $id = $tab->[0];
         my $downTimeStart = $tab->[1];
         my $downTimeEnd = $tab->[2];

         if ($id eq $objectId) {
             if ($downTimeStart < $start) {
                 $downTimeStart = $start;
             }
             if ($downTimeEnd > $end) {
                 $downTimeEnd = $end;
             }
             if ($downTimeStart < $end && $downTimeEnd > $start) {
                 if ($downTimeStart > $start) {
                     my @tab = ($start, $downTimeStart, 0);
                     $events[scalar(@events)] = \@tab;
                 }
                 my @tab = ($downTimeStart, $downTimeEnd, 1);
                 $events[scalar(@events)] = \@tab;
                 $start = $downTimeEnd;
             }
         }
    }
    if ($start < $end) {
        my @tab = ($start, $end, 0);
        $events[scalar(@events)] = \@tab;
    }
    return (\@events);
}

sub splitUpdateEventDownTime {
    my $self = shift;

    my $objectId = shift;
    my $start = shift;
    my $end = shift;
    my $downTimeFlag = shift;
    my $downTimes = shift;
    my $state = shift;

    my $updated = 0;
    my @events = ();
    my $updateTime = 0;
    my $total = 0;
    if (defined($downTimes) && $state != 0) {
        $total = scalar(@$downTimes);
    }
    for (my $i = 0; $i <  $total && $start < $end; $i++) {
        my $tab = $downTimes->[$i];
         my $id = $tab->[0];
         my $downTimeStart = $tab->[1];
         my $downTimeEnd = $tab->[2];

         if ($id eq $objectId) {
             if ($downTimeStart < $start) {
                 $downTimeStart = $start;
             }
             if ($downTimeEnd > $end) {
                 $downTimeEnd = $end;
             }
             if ($downTimeStart < $end && $downTimeEnd > $start) {
                 if ($updated == 0) {
                    $updated = 1;
                    if ($downTimeStart > $start) {
                        if ($downTimeFlag == 1) {
                            my @tab = ($start, $downTimeStart, 0);
                            $events[scalar(@events)] = \@tab;
                        }else {
                            $updateTime = $downTimeStart;
                        }
                        my @tab = ($downTimeStart, $downTimeEnd, 1);
                        $events[scalar(@events)] = \@tab;
                    }else {
                        if ($downTimeFlag == 1) {
                            $updateTime = $downTimeEnd;
                        }else {
                            my @tab = ($downTimeStart, $downTimeEnd, 1);
                            $events[scalar(@events)] = \@tab;
                        }
                    }
                }else {
                    if ($downTimeStart > $start) {
                        my @tab = ($start, $downTimeStart, 0);
                        $events[scalar(@events)] = \@tab;
                    }
                    my @tab = ($downTimeStart, $downTimeEnd, 1);
                    $events[scalar(@events)] = \@tab;
                }
                $start = $downTimeEnd;
            }
         }
    }
    if ($start < $end && scalar(@events)) {
        my @tab = ($start, $end, 0);
        $events[scalar(@events)] = \@tab;
    } else {
        $updateTime = $end;
        if (scalar(@events) && $end > $events[0][0]) {
            $updateTime = $events[0][0];
        }
    }
    return ($updateTime, \@events);
}

1;
