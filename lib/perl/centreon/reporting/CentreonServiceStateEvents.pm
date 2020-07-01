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

package centreon::reporting::CentreonServiceStateEvents;

# Constructor
# parameters:
# $logger: instance of class CentreonLogger
# $centreon: Instance of centreonDB class for connection to Centreon database
# $centstorage: (optional) Instance of centreonDB class for connection to Centstorage database
sub new {
    my $class = shift;
    my $self  = {};
    $self->{logger} = shift;
    $self->{centstorage} = shift;
    $self->{centreonAck} = shift;
    $self->{centreonDownTime}  = shift;
    bless $self, $class;
    return $self;
}

# Get events in given period
# Parameters:
# $start: period start
# $end: period end
sub getStateEventDurations {
    my $self = shift;
    my $centstorage = $self->{centstorage};
    my $start = shift;
    my $end = shift;

    my %services;
    my $query = "SELECT `host_id`, `service_id`, `state`,  `start_time`, `end_time`, `in_downtime`".
                " FROM `servicestateevents`".
                " WHERE `start_time` < ".$end.
                    " AND `end_time` > ".$start.
                    " AND `state` < 4"; # NOT HANDLING PENDING STATE
    my ($status, $sth) = $centstorage->query($query);
    while (my $row = $sth->fetchrow_hashref()) {
        if ($row->{start_time} < $start) {
            $row->{start_time} = $start;
        }
        if ($row->{end_time} > $end) {
            $row->{end_time} = $end;
        }
        if (!defined($services{$row->{host_id}.";;".$row->{service_id}})) {
            my @tab = (0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
            # index 0: OK, index 1: WARNING, index 2: CRITICAL, index 3: UNKNOWN, index 4: DOWNTIME, index 5: UNDETERMINED
            # index 6: OK alerts, index 7: WARNING alerts, index 8: CRITICAL alerts, index 9: UNKNOWN alerts
            $services{$row->{host_id}.";;".$row->{service_id}} = \@tab;
        }

        my $stats = $services{$row->{host_id}.";;".$row->{service_id}};
        if ($row->{in_downtime} == 0) {
            $stats->[$row->{state}] += $row->{end_time} - $row->{start_time};
            if ($row->{state} != 0 || ($row->{state} == 0 && $row->{start_time} > $start)) {
                $stats->[$row->{state} + 6] += 1;
            }
        } else {
            $stats->[4] += $row->{end_time} - $row->{start_time};
        }
        $services{$row->{host_id}.";;".$row->{service_id}} = $stats;
    }
    my %results;
    while (my ($key, $value) = each %services) {
        $value->[5] = ($end - $start) - ($value->[0] + $value->[1] + $value->[2] + $value->[3] + $value->[4]);
        $results{$key} = $value;
    }
    return (\%results);
}


# Get last events for each service
# Parameters:
# $start: max date possible for each event
# $serviceIds: references a hash table containing a list of services
sub getLastStates {
    my $self = shift;
    my $centstorage = $self->{centstorage};

    my $serviceIds = shift;

    my $currentStates = {};

    my $query = "SELECT `host_id`, `service_id`, `state`, `servicestateevent_id`, `end_time`, `in_downtime`, `in_ack`".
                " FROM `servicestateevents`".
                " WHERE `last_update` = 1";
    my ($status, $sth) = $centstorage->query($query);
    while (my $row = $sth->fetchrow_hashref()) {
        my $serviceId = $row->{host_id} . ";;" . $row->{service_id};
        if (defined($serviceIds->{$serviceId})) {
            my @tab = ($row->{end_time}, $row->{state}, $row->{servicestateevent_id}, $row->{in_downtime}, $row->{in_ack});
            $currentStates->{$serviceId} = \@tab;
        }
    }
    $sth->finish();

    return ($currentStates);
}

# update a specific service incident end time
# Parameters
# $endTime: incident end time
# $eventId: ID of event to update
sub updateEventEndTime {
    my $self = shift;
    my $centstorage = $self->{centstorage};
    my $centstatus = $self->{centstatus};
    my $centreonAck = $self->{centreonAck};
    my $centreonDownTime = $self->{centreonDownTime};
    my ($hostId, $serviceId, $start, $end, $state, $eventId, $downTimeFlag, $lastUpdate, $downtimes, $inAck) = @_;
    my $return = {};

    my ($events, $updateTime);
    ($updateTime, $events) = $centreonDownTime->splitUpdateEventDownTime($hostId . ";;" . $serviceId, $start, $end, $downTimeFlag, $downtimes, $state);
    my $totalEvents = 0;
    if (defined($events)) {
        $totalEvents = scalar(@$events);
    }
    my ($ack, $sticky) = $centreonAck->getServiceAckTime($start, $updateTime, $hostId, $serviceId);
    if (defined($ack) && $sticky == 1) {
        $inAck = 1;
    }
    if (!$totalEvents && $updateTime) {
        my $query = "UPDATE `servicestateevents` SET `end_time` = ".$updateTime.", `ack_time`= IFNULL(ack_time,$ack), `in_ack` = '$inAck', `last_update`=".$lastUpdate.
                    " WHERE `servicestateevent_id` = ".$eventId;
        $centstorage->query($query);
    } else {
        if ($updateTime) {
            my $query = "UPDATE `servicestateevents` SET `end_time` = ".$updateTime.", `ack_time`= IFNULL(ack_time,$ack), `in_ack` = '$inAck', `last_update`= 0".
                    " WHERE `servicestateevent_id` = ".$eventId;
            $centstorage->query($query);
        }
        return $self->insertEventTable($hostId, $serviceId, $state, $lastUpdate, $events, $inAck);
    }

    $return->{in_ack} = $inAck;
    return $return;
}

# insert a new incident for service
# Parameters
# $hostId : host ID
# $serviceId: service ID
# $state: incident state
# $start: incident start time
# $end: incident end time
sub insertEvent {
    my $self = shift;
    my $centreonDownTime = $self->{centreonDownTime};
    my ($hostId, $serviceId, $state, $start, $end, $lastUpdate, $downtimes, $inAck) = @_;
    my $events = $centreonDownTime->splitInsertEventDownTime($hostId . ";;" . $serviceId, $start, $end, $downtimes, $state);
    my $return = { in_ack => $inAck };

    if ($state ne "") {
        return $self->insertEventTable($hostId, $serviceId, $state, $lastUpdate, $events, $inAck);
    }

    return $return;
}

sub insertEventTable {
    my $self = shift;
    my $centstorage = $self->{centstorage};
    my $centreonAck = $self->{centreonAck};
    my ($hostId, $serviceId, $state, $lastUpdate, $events, $inAck) =  @_;
    my $return = {};

    my $query_start = "INSERT INTO `servicestateevents`".
                      " (`host_id`, `service_id`, `state`, `start_time`, `end_time`, `last_update`, `in_downtime`, `ack_time`, `in_ack`)".
                      " VALUES (";

    # Stick ack is removed
    if ($state == 0) {
        $inAck = 0;
    }
    my $count = 0;
    for ($count = 0; $count < scalar(@$events) - 1; $count++) {
        my $tab = $events->[$count];

        my ($ack, $sticky) = $centreonAck->getServiceAckTime($tab->[0], $tab->[1], $hostId, $serviceId);
        if ($inAck == 1) {
            $sticky = 1;
            $ack = $tab->[0];
        }
        if (defined($ack) && $sticky == 1) {
            $inAck = 1;
        }
        my $query_end = $hostId.", ".$serviceId.", ".$state.", ".$tab->[0].", ".$tab->[1].", 0, ".$tab->[2].", ".$ack.", '$sticky')";
        $centstorage->query($query_start.$query_end);
    }
    if (scalar(@$events)) {
        my $tab = $events->[$count];
        if (defined($hostId) && defined($serviceId) && defined($state)) {
            my ($ack, $sticky) = $centreonAck->getServiceAckTime($tab->[0], $tab->[1], $hostId, $serviceId);
            if ($inAck == 1) {
                $sticky = 1;
                $ack = $tab->[0];
            }
            if (defined($ack) && $sticky == 1) {
                $inAck = 1;
            }
            my $query_end = $hostId.", ".$serviceId.", ".$state.", ".$tab->[0].", ".$tab->[1].", ".$lastUpdate.", ".$tab->[2].", ".$ack.", '$sticky')";
            $centstorage->query($query_start.$query_end);
        }
    }

    $return->{in_ack} = $inAck;
    return $return;
}

# Truncate service incident table
sub truncateStateEvents {
    my ($self, %options) = @_;
    my $centstorage = $self->{centstorage};

    if (defined($options{start})) {
        my $query = "DELETE FROM servicestateevents WHERE start_time > $options{start}";
        $centstorage->query($query);
        $query = "UPDATE servicestateevents SET end_time = $options{midnight} WHERE end_time > $options{midnight}";
        $centstorage->query($query);
    } else {
        my $query = "TRUNCATE TABLE servicestateevents";
        $centstorage->query($query);
    }
}

# Get first and last events date
sub getFirstLastIncidentTimes {
    my $self = shift;
    my $centstorage = $self->{centstorage};

    my $query = "SELECT min(`start_time`) as minc, max(`end_time`) as maxc FROM `servicestateevents`";
    my ($status, $sth) = $centstorage->query($query);
    my ($start, $end) = (0,0);
    if (my $row = $sth->fetchrow_hashref()) {
        ($start, $end) = ($row->{minc}, $row->{maxc});
    }
    $sth->finish;
    return ($start, $end);
}

1;
