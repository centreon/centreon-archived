use strict;
use warnings;

package CentreonServiceStateEvents;

# Constructor
# parameters:
# $logger: instance of class CentreonLogger
# $centreon: Instance of centreonDB class for connection to Centreon database
# $centstorage: (optionnal) Instance of centreonDB class for connection to Centstorage database
sub new {
	my $class = shift;
	my $self  = {};
	$self->{"logger"}	= shift;
	$self->{"centstorage"}  = shift;
	$self->{"centreonDownTime"}  = shift;
	bless $self, $class;
	return $self;
}

# Get events in given period
# Parameters:
# $start: period start
# $end: period end
sub getStateEventDurations {
	my $self = shift;
	my $centstorage = $self->{"centstorage"};
	my $start = shift;
	my $end = shift;

	my %services;
    my $query = "SELECT `host_id`, `service_id`, `state`,  `start_time`, `end_time`, `in_downtime`".
    			" FROM `servicestateevents`".
    			" WHERE `start_time` < ".$end.
    				" AND `end_time` > ".$start;
    my $sth = $centstorage->query($query);
    while (my $row = $sth->fetchrow_hashref()) {
    	if ($row->{"start_time"} < $start) {
    		$row->{"start_time"} = $start;
    	}
    	if ($row->{"end_time"} > $end) {
    		$row->{"end_time"} = $end;
    	}
		if (!defined($services{$row->{"host_id"}.";;".$row->{"service_id"}})) {
			my @tab = (0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
			# index 0: OK, index 1: WARNING, index 2: CRITICAL, index 3: UNKNOWN, index 4: DOWNTIME, index 5: UNDETERMINED
			# index 6: OK alerts, index 7: WARNING alerts, index 8: CRITICAL alerts, index 9: UNKNOWN alerts
			$services{$row->{"host_id"}.";;".$row->{"service_id"}} = \@tab;			
		}
		
		my $stats = $services{$row->{"host_id"}.";;".$row->{"service_id"}};
		if ($row->{"in_downtime"} == 0) {
			$stats->[$row->{"state"}] += $row->{"end_time"} - $row->{"start_time"};
			$stats->[$row->{"state"} + 6] += 1;
		}else {
			$stats->[4] += $row->{"end_time"} - $row->{"start_time"};
		}
		$services{$row->{"host_id"}.";;".$row->{"service_id"}} = $stats;
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
# $serviceNames: references a hash table containing a list of services
sub getLastStates {
	my $self = shift;
	my $centstorage = $self->{"centstorage"};
	
	my $serviceNames = shift;
	
	my $currentStates = {};
	
    my $query = "SELECT `host_id`, `service_id`, `state`, `servicestateevents_id`, `end_time`, `in_downtime`".
    			" FROM `servicestateevents`".
    			" WHERE `last_update` = 1";
    my $sth = $centstorage->query($query);
    while(my $row = $sth->fetchrow_hashref()) {
    	my $serviceId = $row->{'host_id'}.";;".$row->{'service_id'};
    	if (defined($serviceNames->{$serviceId})) {
		    my @tab = ($row->{'end_time'}, $row->{'state'}, $row->{'servicestateevents_id'}, $row->{'in_downtime'});
			$currentStates->{$serviceNames->{$serviceId}} = \@tab;
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
	my $centstorage = $self->{"centstorage"};
	my $centreonDownTime = $self->{"centreonDownTime"};
	#my ($startTime, $endTime, $state,$eventId, $downTime, $lastUpdate) = (shift, shift, shift, shift, shift, shift);
	my ($hostId, $serviceId, $start, $end, $state, $eventId, $downTimeFlag, $lastUpdate, $downTime) = (shift, shift, shift, shift, shift, shift, shift, shift, shift);
	
	my ($events, $updateTime);
	($updateTime, $events) = $centreonDownTime->splitUpdateEventDownTime($hostId.";;".$serviceId, $start, $end, $downTimeFlag, $downTime);
	
	my $totalEvents = 0;
	if (defined($events)) {
		$totalEvents = scalar(@$events);
	}
	if (!$totalEvents && $updateTime) {
		my $query = "UPDATE `servicestateevents` SET `end_time` = ".$updateTime.", `last_update`=".$lastUpdate.
					" WHERE `servicestateevents_id` = ".$eventId;
		$centstorage->query($query);
	}else {
		if ($updateTime) {
			my $query = "UPDATE `servicestateevents` SET `end_time` = ".$updateTime.", `last_update`= 0".
					" WHERE `servicestateevents_id` = ".$eventId;
			$centstorage->query($query);
		}
		if ($hostId == 330 && $serviceId == 4339) {
			print "UPDATING\n";
		}
		$self->insertEventTable($hostId, $serviceId, $state, $lastUpdate, $events);
	}
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
	my $centreonDownTime = $self->{"centreonDownTime"};
	my ($hostId, $serviceId, $state, $start, $end, $lastUpdate, $downTime) = (shift, shift, shift, shift, shift, shift, shift);
	if ($hostId == 330 && $serviceId == 4339) {
		print "INSERT EVENT: ".$state. " ". localtime($start)." ".localtime($end)."\n";
	}
	my $events = $centreonDownTime->splitInsertEventDownTime($hostId.";;".$serviceId, $start, $end, $downTime);
	$self->insertEventTable($hostId, $serviceId, $state, $lastUpdate, $events);
}

sub insertEventTable {
	my $self = shift;
	my $centstorage = $self->{"centstorage"};
	
	my ($hostId, $serviceId, $state, $lastUpdate, $events) =  (shift, shift, shift, shift, shift);
	
	my $query_start = "INSERT INTO `servicestateevents`".
			" (`host_id`, `service_id`, `state`, `start_time`, `end_time`, `last_update`, `in_downtime`)".
			" VALUES (";
			
	my $count = 0;
	my @tableau = @$events;
	
	for($count = 0; $count < scalar(@tableau) - 1; $count++) {
		my $tab = $tableau[$count];
		if ($hostId == 330 && $serviceId == 4339) {
			print "--inserting ".$serviceId." ".$state. " ". localtime($tab->[0])." ".localtime($tab->[1])."\n";
		}
		my $query_end = $hostId.", ".$serviceId.", ".$state.", ".$tab->[0].", ".$tab->[1].", 0, ".$tab->[2].")";
	}
	my $tab = $events->[$count];
	if ($hostId == 330 && $serviceId == 4339) {
		print "inserting ".$serviceId." ".$state. " ". localtime($tab->[0])." ".localtime($tab->[1])."\n";
	}
	my $query_end = $hostId.", ".$serviceId.", ".$state.", ".$tab->[0].", ".$tab->[1].", ".$lastUpdate.", ".$tab->[2].")";
	$centstorage->query($query_start.$query_end);
	if ($hostId == 330 && $serviceId == 4339) {
		print "finished inserting\n";
	}
}

# Truncate service incident table
sub truncateStateEvents {
	my $self = shift;
	my $centstorage = $self->{"centstorage"};
	
	my $query = "TRUNCATE TABLE `servicestateevents`";
	$centstorage->query($query);
}

# Get first and last events date
sub getFirstLastIncidentTimes {
	my $self = shift;
	my $centstorage = $self->{"centstorage"};
	
	my $query = "SELECT min(`start_time`) as minc, max(`end_time`) as maxc FROM `servicestateevents`";
	my $sth = $centstorage->query($query);
	my ($start, $end) = (0,0);
    if (my $row = $sth->fetchrow_hashref()) {
		($start, $end) = ($row->{"minc"}, $row->{"maxc"});
    }
    $sth->finish;
    return ($start, $end);
}

1;