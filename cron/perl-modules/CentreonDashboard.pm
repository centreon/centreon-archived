use strict;
use warnings;

package CentreonDashboard;

# Constructor
# parameters:
# $logger: instance of class CentreonLogger
# $centreon: Instance of centreonDB class for connection to Centreon database
# $centstorage: (optionnal) Instance of centreonDB class for connection to Centstorage database
sub new {
	my $class = shift;
	my $self  = {};
	$self->{"logger"} = shift;
	$self->{"centstorage"} = shift;
	bless $self, $class;
	return $self;
}

# returns two references to two hash tables => hosts indexed by id and hosts indexed by name
sub insertHostStats {
	my $self = shift;
	my $centstorage = $self->{"centstorage"};
	my $names = shift;
	my $stateDurations = shift;
	my $start = shift;
	my $end = shift;
	my $dayDuration = $end - $start;
	my $query_start = "INSERT INTO `log_archive_host` (`host_id`,".
							" `UPTimeScheduled`,".
							" `DOWNTimeScheduled`,".
							" `UNREACHABLETimeScheduled`,".
							" `MaintenanceTime`,".
							" `UNDETERMINEDTimeScheduled`,".
							" `UPnbEvent`,".
							" `DOWNnbEvent`,".
							" `UNREACHABLEnbEvent`,".
							" `date_start`, `date_end`) VALUES (";
	while (my ($key, $value) = each %$names) {
		my $query_end = $key.",";
		if (defined($stateDurations->{$key})) {
			my $stats = $stateDurations->{$key};
			my @tab = @$stats;
			foreach(@tab) {
				 $query_end .= $_.",";
			}
			$query_end .= $start.",".$end.")";
		}else {
			$query_end .= "0,0,0,0,".$dayDuration.",0,0,0,".$start.",".$end.")";
		}
		my $sth = $centstorage->query($query_start.$query_end);
	}
}

# 
sub insertServiceStats {
	my $self = shift;
	my $centstorage = $self->{"centstorage"};
	my $names = shift;
	my $stateDurations = shift;
	my $start = shift;
	my $end = shift;
	my $dayDuration = $end - $start;
	my $query_start = "INSERT INTO `log_archive_service` (`host_id`, `service_id`,".
							" `OKTimeScheduled`,".
							" `WARNINGTimeScheduled`,".
							" `CRITICALTimeScheduled`,".
							" `UNKNOWNTimeScheduled`,".
							" `MaintenanceTime`,".
							" `UNDETERMINEDTimeScheduled`,".
							" `OKnbEvent`,".
							" `WARNINGnbEvent`,".
							" `CRITICALnbEvent`,".
							" `UNKNOWNnbEvent`,".
							" `date_start`, `date_end`) VALUES (";
	while (my ($key, $value) = each %$names) {
		my ($host_id, $service_id) = split(";;", $key);
		my $query_end = $host_id.",".$service_id.",";
		if (defined($stateDurations->{$key})) {
			my $stats = $stateDurations->{$key};
			my @tab = @$stats;
			foreach(@tab) {
				 $query_end .= $_.",";
			}
			$query_end .= $start.",".$end.")";
			
		}else {
			$query_end .= "0,0,0,0,0,".$dayDuration.",0,0,0,0,".$start.",".$end.")";
		}
		my $sth = $centstorage->query($query_start.$query_end);
	}
}

# Truncate service dashboard stats table
sub truncateServiceStats {
	my $self = shift;
	my $centstorage = $self->{"centstorage"};
	
	my $query = "TRUNCATE TABLE `log_archive_service`";
	$centstorage->query($query);
}

# Truncate host dashboard stats table
sub truncateHostStats {
	my $self = shift;
	my $centstorage = $self->{"centstorage"};
	
	my $query = "TRUNCATE TABLE `log_archive_host`";
	$centstorage->query($query);
}

1;