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

package centreon::reporting::CentreonProcessStateEvents;

# Constructor
# parameters:
# $logger: instance of class CentreonLogger
sub new {
    my $class = shift;
    my $self = {};
    $self->{"logger"} = shift;
    $self->{"host"} = shift;
    $self->{"service"} = shift;
    $self->{"nagiosLog"} = shift;
    $self->{"hostEvents"} = shift;
    $self->{"serviceEvents"} = shift;
    $self->{"centreonDownTime"} = shift;
    bless $self, $class;

    return $self;
}

# Parse services logs for given period
# Parameters:
# $start: period start
# $end: period end
sub parseServiceLog {
    my $self = shift;
    # parameters:
    my ($start ,$end) = (shift,shift);
    my $service = $self->{"service"};
    my $nagiosLog = $self->{"nagiosLog"};
    my $events = $self->{"serviceEvents"};
    my $centreonDownTime = $self->{"centreonDownTime"};

    my $serviceIds = $service->getAllServiceIds();
    my $currentEvents = $events->getLastStates($serviceIds);
    my $logs = $nagiosLog->getLogOfServices($start, $end);
    my $downtimes = $centreonDownTime->getDowntimes($serviceIds, $start, $end, 2);

    while (my $row = $logs->fetchrow_hashref()) {
        my $fullId  = $row->{host_id} . ";;" . $row->{service_id};
        if (defined($serviceIds->{$fullId})) {
            my $statusCode = $row->{status};

            # manage initial states (no entry in state events table)
            if (!defined($currentEvents->{$fullId})) {
                my @tab = ($row->{ctime}, $statusCode, 0, 0, 0);
                $currentEvents->{$fullId} = \@tab;
            }

            my $eventInfos =  $currentEvents->{$fullId};
            # $eventInfos is a reference to a table containing : incident start time | status | state_event_id | in_downtime. The last one is optional
            if ($statusCode ne "" && defined($eventInfos->[1]) && $eventInfos->[1] ne "" && $eventInfos->[1] != $statusCode) {
                my ($hostId, $serviceId) = split(";;", $fullId);
                my $result = {};
                if ($eventInfos->[2] != 0) {
                    # If eventId of log is defined, update the last day event
                    $result = $events->updateEventEndTime(
                        $hostId,
                        $serviceId,
                        $eventInfos->[0],
                        $row->{ctime},
                        $eventInfos->[1],
                        $eventInfos->[2],
                        $eventInfos->[3],
                        0,
                        $downtimes,
                        $eventInfos->[4]
                    );
                } else {
                    if ($row->{ctime} > $eventInfos->[0]) {
                        $result = $events->insertEvent(
                            $hostId,
                            $serviceId,
                            $eventInfos->[1],
                            $eventInfos->[0],
                            $row->{ctime},
                            0,
                            $downtimes,
                            $eventInfos->[4]
                        );
                    }
                }
                $eventInfos->[0] = $row->{ctime};
                $eventInfos->[1] = $statusCode;
                $eventInfos->[2] = 0;
                $eventInfos->[3] = 0;
                $eventInfos->[4] = defined($result->{in_ack}) ? $result->{in_ack} : 0;
                $currentEvents->{$fullId} = $eventInfos;
            }
        }
    }
    $self->insertLastServiceEvents($end, $currentEvents, $downtimes);
}

# Parse host logs for given period
# Parameters:
# $start: period start
# $end: period end
sub parseHostLog {
    my $self = shift;

    # parameters:
    my ($start ,$end) = (shift,shift);

    my $host = $self->{"host"};
    my $nagiosLog = $self->{"nagiosLog"};
    my $events = $self->{"hostEvents"};
    my $centreonDownTime = $self->{"centreonDownTime"};

    my $hostIds = $host->getAllHostIds();
    my $currentEvents = $events->getLastStates($hostIds);
    my $logs = $nagiosLog->getLogOfHosts($start, $end);
    my $downtimes = $centreonDownTime->getDowntimes($hostIds, $start, $end, 1);

    while (my $row = $logs->fetchrow_hashref()) {
        my $hostId  = $row->{host_id};

        if (defined($hostIds->{$hostId})) {
            my $statusCode = $row->{status};

            # manage initial states (no entry in state events table)
            if (!defined($currentEvents->{$hostId})) {
                my @tab = ($row->{'ctime'}, $statusCode, 0, 0, 0);
                $currentEvents->{$hostId} = \@tab;
            }

            my $eventInfos =  $currentEvents->{$hostId}; # $eventInfos is a reference to a table containing : incident start time | status | state_event_id. The last one is optionnal
            if ($statusCode ne "" && defined($eventInfos->[1]) && $eventInfos->[1] ne "" && $eventInfos->[1] != $statusCode) {
                my $result = {};
                if ($eventInfos->[2] != 0) {
                    # If eventId of log is defined, update the last day event
                    $result = $events->updateEventEndTime(
                        $hostId,
                        $eventInfos->[0],
                        $row->{'ctime'},
                        $eventInfos->[1],
                        $eventInfos->[2],
                        $eventInfos->[3],
                        0,
                        $downtimes,
                        $eventInfos->[4]
                    );
                } else {
                    if ($row->{ctime} > $eventInfos->[0]) {
                        $result = $events->insertEvent(
                            $hostId,
                            $eventInfos->[1],
                            $eventInfos->[0],
                            $row->{'ctime'},
                            0,
                            $downtimes,
                            $eventInfos->[4]
                        );
                    }
                }
                $eventInfos->[0] = $row->{'ctime'};
                $eventInfos->[1] = $statusCode;
                $eventInfos->[2] = 0;
                $eventInfos->[3] = 0;
                $eventInfos->[4] = defined($result->{in_ack}) ? $result->{in_ack} : 0;
                $currentEvents->{$hostId} = $eventInfos;
            }
        }
    }
    $self->insertLastHostEvents($end, $currentEvents, $downtimes);
}


# Insert in DB last service incident of day currently processed
# Parameters:
# $end: period end
# $currentEvents: reference to a hash table that contains last incident details
# $serviceIds: reference to a hash table that returns host/service ids for host/service ids
sub insertLastServiceEvents {
    my $self = shift;
    my $events = $self->{"serviceEvents"};

    # parameters:
    my ($end, $currentEvents, $downtimes)  = (shift, shift, shift, shift);

    while (my ($id, $eventInfos) = each (%$currentEvents)) {
        my ($hostId, $serviceId) = split(";;", $id);
        if ($eventInfos->[2] != 0) {
            $events->updateEventEndTime(
                $hostId,
                $serviceId,
                $eventInfos->[0],
                $end,
                $eventInfos->[1],
                $eventInfos->[2],
                $eventInfos->[3],
                1,
                $downtimes,
                $eventInfos->[4]
            );
        } else {
            $events->insertEvent(
                $hostId,
                $serviceId,
                $eventInfos->[1],
                $eventInfos->[0],
                $end,
                1,
                $downtimes,
                $eventInfos->[4]
            );
        }
    }
}

# Insert in DB last host incident of day currently processed
# Parameters:
# $end: period end
# $currentEvents: reference to a hash table that contains last incident details
sub insertLastHostEvents {
    my $self = shift;
    my $events = $self->{"hostEvents"};

    # parameters:
    my ($end, $currentEvents, $downtimes)  = (shift, shift, shift);

    while (my ($hostId, $eventInfos) = each (%$currentEvents)) {
        if ($eventInfos->[2] != 0) {
            $events->updateEventEndTime(
                $hostId,
                $eventInfos->[0],
                $end,
                $eventInfos->[1],
                $eventInfos->[2],
                $eventInfos->[3],
                1,
                $downtimes,
                $eventInfos->[4]
            );
        } else {
            $events->insertEvent($hostId, $eventInfos->[1], $eventInfos->[0], $end, 1, $downtimes, $eventInfos->[4]);
        }
    }
}

1;
