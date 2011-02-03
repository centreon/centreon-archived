
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
   
    my ($allIds, $allNames) = $service->getAllServices();
	my $currentEvents = $events->getLastStates($allNames);
	my $logs = $nagiosLog->getLogOfServices($start, $end);

    while(my $row = $logs->fetchrow_hashref()) {
		my $id  = $row->{'host_name'}.";;".$row->{'service_description'};
		my $eventInfos; # $eventInfos is a reference to a table containing : incident start time | status | state_event_id. The last one is optionnal
		if (defined($allIds->{$id})) {
			if (defined($currentEvents->{$id})) {
				$eventInfos =  $currentEvents->{$id};
				if ($eventInfos->[1] ne $row->{'status'}) {
					if (defined($eventInfos->[2])) {
						# If eventId of log is defined, update the last day event
						$events->updateEventEndTime($row->{'ctime'}, $eventInfos->[2]);
						$eventInfos->[2] = undef;
					}else {
						my ($hostId, $serviceId) = split (";;", $allIds->{$id});
						$events->insertEvent($hostId, $serviceId, $serviceStates{$eventInfos->[1]}, $eventInfos->[0], $row->{'ctime'}, 0, 0);
					}
				}
			}
			$eventInfos->[0] = $row->{'ctime'};
			$eventInfos->[1] = $row->{'status'};
			$currentEvents->{$id} = $eventInfos;
		}
	}
	$self->insertLastServiceEvents($end, $currentEvents, $allIds, $events);
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
    
    my ($allIds, $allNames) = $host->getAllHosts();
	my $currentEvents = $events->getLastStates($allNames);
	my $logs = $nagiosLog->getLogOfHosts($start, $end);

    while(my $row = $logs->fetchrow_hashref()) {
		my $id  = $row->{'host_name'};
		my $eventInfos; # $eventInfos is a reference to a table containing : incident start time | status | state_event_id. The last one is optionnal
		if (defined($allIds->{$id})) {
			if (defined($currentEvents->{$id})) {
				$eventInfos =  $currentEvents->{$id};
				if ($eventInfos->[1] ne $row->{'status'}) {
					if (defined($eventInfos->[2])) {
						# If eventId of log is defined, update the last day event
						$events->updateEventEndTime($row->{'ctime'}, $eventInfos->[2]);
						$eventInfos->[2] = undef;
					}else {
						$events->insertEvent($allIds->{$id}, $hostStates{$eventInfos->[1]}, $eventInfos->[0], $row->{'ctime'}, 0, 0);
					}
				}
			}
			$eventInfos->[0] = $row->{'ctime'};
			$eventInfos->[1] = $row->{'status'};
			$currentEvents->{$id} = $eventInfos;
		}
	}
	$self->insertLastHostEvents($end, $currentEvents, $allIds, $events);
}


# Insert in DB last service incident of day currently processed
# Parameters:
# $end: period end
# $currentEvents: reference to a hash table that contains last incident details
# $allIds: reference to a hash table that returns host/service ids for host/service names
sub insertLastServiceEvents {
	my $self = shift;
	# parameters:
	my ($end,$currentEvents, $allIds, $events)  = (shift, shift, shift, shift, shift);
	
	while(my ($id, $eventInfos) = each (%$currentEvents)) {
			if (defined($eventInfos->[2])) {
				$events->updateEventEndTime($end, $eventInfos->[2]);
				$eventInfos->[2] = undef;
			}else {
				my ($hostId, $serviceId) = split (";;", $allIds->{$id});
				$events->insertEvent($hostId, $serviceId, $serviceStates{$eventInfos->[1]}, $eventInfos->[0], $end, 1, 0);
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
	# parameters:
	my ($end, $currentEvents, $allIds, $events)  = (shift, shift, shift, shift, shift);
	
	while(my ($id, $eventInfos) = each (%$currentEvents)) {
			if (defined($eventInfos->[2])) {
				$events->updateEventEndTime($end, $eventInfos->[2]);
				$eventInfos->[2] = undef;
			}else {
				$events->insertEvent($allIds->{$id}, $hostStates{$eventInfos->[1]}, $eventInfos->[0], $end, 1, 0);
			}
	}
}

1;