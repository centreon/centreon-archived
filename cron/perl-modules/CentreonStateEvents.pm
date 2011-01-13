use strict;
use warnings;

package CentreonStateEvents;

# Constructor
# parameters:
# $centreon: Instance of centreonDB class for connection to Centreon database
# $centstorage: (optionnal) Instance of centreonDB class for connection to Centstorage database
sub new {
	my $class = shift;
	my $self  = {};
	$self->{"logger"}	= shift;
	$self->{"centreon"} = shift;
	if (@_) {
		$self->{"centstorage"}  = shift;
	}
	bless $self, $class;
	return $self;
}

# Get last events for each service
# Parameters:
# $start: max date possible for each event
# $serviceNames: references a hash table containing a list of services
sub getLastStateOfServices {
	my $self = shift;
	my $centstorage = $self->{"centstorage"};
	
	my $start = shift;
	my $serviceNames = shift;
	
	my $currentStates = {};
	
    my $query = "SELECT `host_id`, `service_id`, `state`, `state_event_id`, `end_time`".
    			" FROM `servicestateevents`".
    			" WHERE `last_update` = 1";
    my $sth = $centstorage->query($query);
    while(my $row = $sth->fetchrow_hashref()) {
		my $serviceId = $row->{'host_id'}.";;".$row->{'service_id'};
		my $start = $row->{'end_time'};
	    my @tab = ($row->{'end_time'}, $row->{'state'}, $row->{'state_event_id'});
		$currentStates->{$serviceNames->{$serviceId}} = \@tab;
	}
    $sth->finish();
    
    return ($currentStates);
}

# update a specific service incident end time
# Parameters
# $endTime: incident end time
# $eventId: ID of event to update
sub updateServiceEventEndTime {
	my $self = shift;
	my $centstorage = $self->{"centstorage"};
	my $endTime = shift;
	my $eventId = shift;
	my $query = "UPDATE `servicestateevents`".
			" SET `end_time` = ".$endTime.
			" WHERE `servicestateevents_id` = ".$eventId;
	$centstorage->query($query);
}

# update a specific host incident end time
# Parameters
# $endTime: incident end time
# $eventId: ID of event to update
sub updateHostEventEndTime {
	my $self = shift;
	my $centstorage = $self->{"centstorage"};
	my $endTime = shift;
	my $eventId = shift;
	my $query = "UPDATE `hoststateevents`".
			" SET `end_time` = ".$endTime.
			" WHERE `hoststateevents_id` = ".$eventId;
}

# insert a new incident for service
# Parameters
# $hostId : host ID
# $serviceId: service ID
# $state: incident state
# $start: incident start time
# $end: incident end time
sub insertServiceEvent {
	my $self = shift;
	my $centstorage = $self->{"centstorage"};
	my ($hostId, $serviceId, $state, $start, $end) = @_;
	my $query = "INSERT INTO `servicestateevents`".
			" (`host_id`, `service_id`, `state`, `start_time`, `end_time`)".
			" VALUES (".
			$hostId.", ".
			$serviceId.", ".
			"'".$state."', ".
			$start.", ".
			$end.")";
	$centstorage->query($query);
}

# Truncate service incident table
sub truncateServiceStateEvents {
	my $self = shift;
	my $centstorage = $self->{"centstorage"};
	
	my $query = "TRUNCATE TABLE `servicestateevents`";
	$centstorage->query($query);
}

# Truncate service incident table
sub truncateHostStateEvents {
	my $self = shift;
	my $centstorage = $self->{"centstorage"};
	
	my $query = "TRUNCATE TABLE `hoststateevents`";
	$centstorage->query($query);
}

1;