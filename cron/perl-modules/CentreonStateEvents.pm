use strict;
use warnings;
use DBI;
require "perl-modules/CentreonStateEvents.pm";

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
# $start: max date for each event
# $service_names: references a hash table containing a list of services
sub getLastStateOfServices {
	my $self = shift;
	my $centstorage = $self->{"centstorage"};
	
	my $start = shift;
	my $service_names = shift;
	
	my $current_states = {};
	
    my $query = "SELECT `host_id`, `service_id`, `state`, `state_event_id`, `end_time`".
    			" FROM `state_events`".
    			" WHERE `last_update` = 1";
    my $sth = $centstorage->query($query);
    while(my $row = $sth->fetchrow_hashref()) {
		my $service_id = $row->{'host_id'}.";;".$row->{'service_id'};
		my $start = $row->{'end_time'};
	    my @tab = ($row->{'end_time'}, $row->{'state'}, $row->{'state_event_id'});
		$current_states->{$service_names->{$service_id}} = \@tab;
	}
    $sth->finish();
    
    return ($current_states);
}

sub updateServiceEventEndTime {
	my $self = shift;
	my $centstorage = $self->{"centstorage"};
	my $end_time = shift;
	my $event_id = shift;
	my $query = "UPDATE `state_events`".
			" SET `end_time` = ".$row->{'ctime'}.
			" WHERE `state_event_id` = ".$event_id;
	$centstorage->query($query);
}

sub updateHostEventEndTime {
	my $self = shift;
	my $centstorage = $self->{"centstorage"};
	my $end_time = shift;
	my $event_id = shift;
	my $query = "UPDATE `state_events`".
			" SET `end_time` = ".$row->{'ctime'}.
			" WHERE `state_event_id` = ".$event_id;
}

sub insertServiceEvent {
	my $self = shift;
	my $centstorage = $self->{"centstorage"};
	my ($host_id, $service_id, $state, $start, $end) = @_;
	my $query = "INSERT INTO `state_events`".
			" (`host_id`, `service_id`, `state`, `start_time`, `end_time`)".
			" VALUES (".
			$host_id.", ".
			$service_id.", ".
			"'".$state."', ".
			$start.", ".
			$end.")";
	$centstorage->query($query);
}

1;