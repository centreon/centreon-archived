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
	if (@_) {
		$self->{"centreon"} = shift;
	}
	bless $self, $class;
	return $self;
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
	
    my $query = "SELECT `host_id`, `service_id`, `state`, `servicestateevents_id`, `start_time`".
    			" FROM `servicestateevents`".
    			" WHERE `last_update` = 1";
    my $sth = $centstorage->query($query);
    while(my $row = $sth->fetchrow_hashref()) {
    	my $serviceId = $row->{'host_id'}.";;".$row->{'service_id'};
    	if (defined($serviceNames->{$serviceId})) {
		    my @tab = ($row->{'start_time'}, $row->{'state'}, $row->{'servicestateevents_id'});
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
	my $endTime = shift;
	my $eventId = shift;
	my $last_update = shift;
	my $query = "UPDATE `servicestateevents`".
			" SET `end_time` = ".$endTime.
				", `last_update`=".$last_update.
			" WHERE `servicestateevents_id` = ".$eventId;
	$centstorage->query($query);
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
	my $centstorage = $self->{"centstorage"};
	my ($hostId, $serviceId, $state, $start, $end, $lastUpdate, $downTime) = (shift, shift, shift, shift, shift, shift, shift);
	my $query = "INSERT INTO `servicestateevents`".
			" (`host_id`, `service_id`, `state`, `start_time`, `end_time`, `last_update`, `in_downtime`)".
			" VALUES (".
			$hostId.", ".
			$serviceId.", ".
			$state.", ".
			$start.", ".
			$end.", ".
			$lastUpdate.", ".
			$downTime.")";
	$centstorage->query($query);
}

# Truncate service incident table
sub truncateStateEvents {
	my $self = shift;
	my $centstorage = $self->{"centstorage"};
	
	my $query = "TRUNCATE TABLE `servicestateevents`";
	$centstorage->query($query);
}

1;