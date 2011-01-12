use strict;
use warnings;
use DBI;
require "perl-modules/CentreonDB.pm";

package CentreonService;

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

# Get all service logs between two dates
# Parameters:
# $start: period start date in timestamp
# $end: period start date in timestamp
sub getLogOfServices {
	my $self = shift;
	my $centstorage = $self->{"centstorage"};
	my ($start, $end);
	if (@_) {
		$start = shift;
		$end = shift;
	}
	my $query = "SELECT `status`, `ctime`, `host_name`, `service_description`".
				" FROM `log`".
				" WHERE `ctime` >= ".$start.
					" AND `ctime` <= ".$end.
					" AND (`type` = 'HARD' OR (`status` = 'OK' AND `type` = 'SOFT'))".
					" AND `service_description` IS NOT null".
					" AND `msg_type` IN ('0', '1', '6', '7', '8', '9')".
				" ORDER BY `ctime`";
	my $result = $centstorage->query($query);
	return $result;
}

# Get all hosts logs between two dates
# Parameters:
# $start: period start date in timestamp
# $end: period start date in timestamp
sub getLogOfHosts {
	my $self = shift;
	my $centstorage = $self->{"centstorage"};
	my ($start, $end);
	if (@_) {
		$start = shift;
		$end = shift;
	}
	my $query = "SELECT `status`, `ctime`, `host_name`".
				" FROM `log`".
				" WHERE `ctime` >= ".$start.
					" AND `ctime` <= ".$end.
					" AND (`type` = 'HARD' OR (`status` = 'UP' AND `type` = 'SOFT'))".
					" AND `service_description` IS NULL".
					" AND `msg_type` IN ('0', '1', '6', '7', '8', '9')".
				" ORDER BY `ctime`";
	my $result = $centstorage->query($query);
	return $result;
}

1;