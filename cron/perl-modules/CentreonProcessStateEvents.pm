
use strict;
use warnings;

package CentreonProcessStateEvents;

require "/home/msugumaran/merethis/centreon-bi-server/centreon/cron/perl-modules/variables.pm";
use vars qw (%serviceStates %hostStates %servicStateIds %hostStateIds);

# Constructor
# parameters:
# $logger: instance of class CentreonLogger
sub new {
	my $class = shift;
	my $self  = {};
	$self->{"logger"}	= shift;
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
   	
    my ($allIds, $allNames) = $service->getAllServices();
	my $currentEvents = $events->getLastStates($allNames);
	my $logs = $nagiosLog->getLogOfServices($start, $end);
	my $downTime = $centreonDownTime->getDownTime($allIds, $start, $end, 2);
	
    while(my $row = $logs->fetchrow_hashref()) {
		my $id  = $row->{'host_name'}.";;".$row->{'service_description'};
		if (defined($allIds->{$id})) {
			if (defined($currentEvents->{$id})) {
				my $eventInfos =  $currentEvents->{$id}; # $eventInfos is a reference to a table containing : incident start time | status | state_event_id | in_downtime. The last one is optionnal
				if ($eventInfos->[1] != $serviceStates{$row->{'status'}}) {
					my ($hostId, $serviceId) = split (";;", $allIds->{$id});
					if ($eventInfos->[2] != 0) {
						if ($allIds->{$id} eq "17;;34") {
							print "====> updating ".localtime($eventInfos->[0])." ".localtime($row->{'ctime'})."\n";
						}
						# If eventId of log is defined, update the last day event
						$events->updateEventEndTime($hostId, $serviceId, $eventInfos->[0], $row->{'ctime'}, $eventInfos->[1], $eventInfos->[2], $eventInfos->[3], 0, $downTime);
					}else {
						if ($row->{'ctime'} > $eventInfos->[0]) {
							$events->insertEvent($hostId, $serviceId, $eventInfos->[1], $eventInfos->[0], $row->{'ctime'}, 0, $downTime);
						}
					}
					$eventInfos->[0] = $row->{'ctime'};
					$eventInfos->[1] = $serviceStates{$row->{'status'}};
					$eventInfos->[2] = 0;
					$eventInfos->[3] = 0;
					$currentEvents->{$id} = $eventInfos;
				}
			}else {
				my @tab = ($row->{'ctime'}, $serviceStates{$row->{'status'}}, 0, 0);
				$currentEvents->{$id} = \@tab;
			}
			
		}
	}
	
	$self->insertLastServiceEvents($end, $currentEvents, $allIds, $downTime);
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
    
    my ($allIds, $allNames) = $host->getAllHosts();
	my $currentEvents = $events->getLastStates($allNames);
	my $logs = $nagiosLog->getLogOfHosts($start, $end);
	my $downTime = $centreonDownTime->getDownTime($allIds, $start, $end, 1);
	
    while(my $row = $logs->fetchrow_hashref()) {
		my $id  = $row->{'host_name'};
		if (defined($allIds->{$id})) {
			if (defined($currentEvents->{$id})) {
				my $eventInfos =  $currentEvents->{$id}; # $eventInfos is a reference to a table containing : incident start time | status | state_event_id. The last one is optionnal
				if ($eventInfos->[1] != $hostStates{$row->{'status'}}) {
					if ($eventInfos->[2] != 0) {
						# If eventId of log is defined, update the last day event
						$events->updateEventEndTime($allIds->{$id}, $eventInfos->[0], $row->{'ctime'}, $eventInfos->[1], $eventInfos->[2],$eventInfos->[3], 0, $downTime);
					}else {
						if ($row->{'ctime'} > $eventInfos->[0]) {
							$events->insertEvent($allIds->{$id}, $eventInfos->[1], $eventInfos->[0], $row->{'ctime'}, 0, $downTime);
						}
					}
					$eventInfos->[0] = $row->{'ctime'};
					$eventInfos->[1] = $hostStates{$row->{'status'}};
					$eventInfos->[2] = 0;
					$eventInfos->[3] = 0;
					$currentEvents->{$id} = $eventInfos;
				}
			}else {
				my @tab = ($row->{'ctime'}, $hostStates{$row->{'status'}}, 0, 0);
				$currentEvents->{$id} = \@tab;
			}
		}
	}
	$self->insertLastHostEvents($end, $currentEvents, $allIds, $downTime);
}


# Insert in DB last service incident of day currently processed
# Parameters:
# $end: period end
# $currentEvents: reference to a hash table that contains last incident details
# $allIds: reference to a hash table that returns host/service ids for host/service names
sub insertLastServiceEvents {
	my $self = shift;
	my $events = $self->{"serviceEvents"};
	# parameters:
	my ($end,$currentEvents, $allIds, $downTime)  = (shift, shift, shift, shift);
	
	while(my ($id, $eventInfos) = each (%$currentEvents)) {
		my ($hostId, $serviceId) = split (";;", $allIds->{$id});
		if ($eventInfos->[2] != 0) {
			if ($allIds->{$id} eq "17;;34") {
				print "updating\n";
			}
			$events->updateEventEndTime($hostId, $serviceId, $eventInfos->[0], $end, $eventInfos->[1], $eventInfos->[2], $eventInfos->[3], 1, $downTime);
		}else {
			if ($allIds->{$id} eq "17;;34") {
				print "inserting\n";
			}
			$events->insertEvent($hostId, $serviceId, $eventInfos->[1], $eventInfos->[0], $end, 1, $downTime);
		}
	}
}

# Insert in DB last host incident of day currently processed
# Parameters:
# $end: period end
# $currentEvents: reference to a hash table that contains last incident details
# $allIds: reference to a hash table that returns host ids for host names
sub insertLastHostEvents {
	my $self = shift;
	my $events = $self->{"hostEvents"};
	# parameters:
	my ($end, $currentEvents, $allIds, $downTime)  = (shift, shift, shift, shift, shift);
	
	while(my ($id, $eventInfos) = each (%$currentEvents)) {
		if ($eventInfos->[2] != 0) {
			$events->updateEventEndTime($allIds->{$id}, $eventInfos->[0], $end, $eventInfos->[1], $eventInfos->[2], $eventInfos->[3], 1, $downTime);
		}else {
			$events->insertEvent($allIds->{$id}, $eventInfos->[1], $eventInfos->[0], $end, 1, $downTime);
		}
	}
}

1;