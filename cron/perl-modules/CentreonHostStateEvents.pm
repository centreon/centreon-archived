use strict;
use warnings;

package CentreonHostStateEvents;

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

# Get last events for each hhost
# Parameters:
# $start: max date possible for each event
# $serviceNames: references a hash table containing a list of host
sub getLastStates {
	my $self = shift;
	my $centstorage = $self->{"centstorage"};
	my $hostNames = shift;
	
	my %currentStates;
	
    my $query = "SELECT `host_id`, `state`, `hoststateevents_id`, `start_time`".
    			" FROM `hoststateevents`".
    			" WHERE `last_update` = 1";
    my $sth = $centstorage->query($query);
    while(my $row = $sth->fetchrow_hashref()) {
    	if (defined($hostNames->{$row->{'host_id'}})) {
		    my @tab = ($row->{'start_time'}, $row->{'state'}, $row->{'hoststateevents_id'});
			$currentStates{$hostNames->{$row->{'host_id'}}} = \@tab;
    	}
	}
    $sth->finish();
    
    return (\%currentStates);
}

# update a specific host incident end time
# Parameters
# $endTime: incident end time
# $eventId: ID of event to update
sub updateEventEndTime {
	my $self = shift;
	my $centstorage = $self->{"centstorage"};
	my $endTime = shift;
	my $eventId = shift;
	my $last_update = shift;
	my $query = "UPDATE `hoststateevents`".
			" SET `end_time` = ".$endTime.
				", `last_update`=".$last_update.
			" WHERE `hoststateevents_id` = ".$eventId;
	$centstorage->query($query);
}

# insert a new incident for host
# Parameters
# $hostId : host ID
# $serviceId: service ID
# $state: incident state
# $start: incident start time
# $end: incident end time
sub insertEvent {
	my $self = shift;
	my $centstorage = $self->{"centstorage"};
	my ($hostId, $state, $start, $end, $lastUpdate, $downTime) = (shift, shift, shift, shift, shift, shift);
	my $query = "INSERT INTO `hoststateevents`".
			" (`host_id`, `state`, `start_time`, `end_time`, `last_update`, `in_downtime`)".
			" VALUES (".
			$hostId.", ".
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
	
	my $query = "TRUNCATE TABLE `hoststateevents`";
	$centstorage->query($query);
}

1;