
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
		if (defined($allIds->{$id})) {
			if (defined($currentEvents->{$id})) {
				my $eventInfos =  $currentEvents->{$id}; # $eventInfos is a reference to a table containing : incident start time | status | state_event_id. The last one is optionnal
				if ($eventInfos->[1] != $serviceStates{$row->{'status'}}) {
					if ($eventInfos->[2] != 0) {
						# If eventId of log is defined, update the last day event
						$events->updateEventEndTime($row->{'ctime'}, $eventInfos->[2], 0);
					}else {
						my ($hostId, $serviceId) = split (";;", $allIds->{$id});
						$events->insertEvent($hostId, $serviceId, $eventInfos->[1], $eventInfos->[0], $row->{'ctime'}, 0, 0);
					}
					$eventInfos->[0] = $row->{'ctime'};
					$eventInfos->[1] = $serviceStates{$row->{'status'}};
					$eventInfos->[2] = 0;
					$currentEvents->{$id} = $eventInfos;
				}
			}else {
				my @tab = ($row->{'ctime'}, $serviceStates{$row->{'status'}}, 0);
				$currentEvents->{$id} = \@tab;
			}
			
		}
	}
	$self->insertLastServiceEvents($end, $currentEvents, $allIds);
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
    print "getting last events\n";
	my $currentEvents = $events->getLastStates($allNames);
	print "getting logs\n";
	my $logs = $nagiosLog->getLogOfHosts($start, $end);
	
	print "processing logs\n";
    while(my $row = $logs->fetchrow_hashref()) {
		my $id  = $row->{'host_name'};
		if (defined($allIds->{$id})) {
			if ($allIds->{$id} == 270) {
				print "processing host\n";
			}
			if (defined($currentEvents->{$id})) {
				if ($allIds->{$id} == 270) {
					print "host exists\n";
				}
				my $eventInfos =  $currentEvents->{$id}; # $eventInfos is a reference to a table containing : incident start time | status | state_event_id. The last one is optionnal
				if ($eventInfos->[1] != $hostStates{$row->{'status'}}) {
					if ($allIds->{$id} == 270) {
						print "status changed\n";
					}
					if ($eventInfos->[2] != 0) {
						# If eventId of log is defined, update the last day event
						if ($allIds->{$id} == 270) {
							print "updating entry\n";
						}
						$events->updateEventEndTime($row->{'ctime'}, $eventInfos->[2], 0);
					}else {
						if ($allIds->{$id} == 270) {
							print "inserting entry\n";
						}
						$events->insertEvent($allIds->{$id}, $eventInfos->[1], $eventInfos->[0], $row->{'ctime'}, 0, 0);
					}
					$eventInfos->[0] = $row->{'ctime'};
					$eventInfos->[1] = $hostStates{$row->{'status'}};
					$eventInfos->[2] = 0;
					$currentEvents->{$id} = $eventInfos;
				}
			}else {
				if ($allIds->{$id} == 270) {
					print "host does not exists\n";
				}
				my @tab = ($row->{'ctime'}, $hostStates{$row->{'status'}}, 0);
				$currentEvents->{$id} = \@tab;
			}

		}
	}
	print "inserting last events\n";
	$self->insertLastHostEvents($end, $currentEvents, $allIds);
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
	my ($end,$currentEvents, $allIds)  = (shift, shift, shift);
	
	while(my ($id, $eventInfos) = each (%$currentEvents)) {
			if ($eventInfos->[2] != 0) {
				$events->updateEventEndTime($end, $eventInfos->[2], 1);
			}else {
				my ($hostId, $serviceId) = split (";;", $allIds->{$id});
				$events->insertEvent($hostId, $serviceId, $eventInfos->[1], $eventInfos->[0], $end, 1, 0);
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
	my ($end, $currentEvents, $allIds)  = (shift, shift, shift, shift);
	
	while(my ($id, $eventInfos) = each (%$currentEvents)) {
		if ($allIds->{$id} ==270) {
			print localtime($eventInfos->[0])." ".localtime($end)." ".$eventInfos->[1]." ".$eventInfos->[2]."\n";
		}
		if ($eventInfos->[2] != 0) {
			if ($allIds->{$id} == 270) {
				print "last update\n";
			}
			$events->updateEventEndTime($end, $eventInfos->[2], 1);
		}else {
			if ($allIds->{$id} == 270) {
				print "last insert\n";
			}
			$events->insertEvent($allIds->{$id}, $eventInfos->[1], $eventInfos->[0], $end, 1, 0);
		}
	}
}

1;