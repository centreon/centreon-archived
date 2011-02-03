use strict;
use warnings;

package CentreonLog;

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
					" AND `ctime` < ".$end.
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
					" AND `ctime` < ".$end.
					" AND (`type` = 'HARD' OR (`status` = 'UP' AND `type` = 'SOFT'))".
					" AND `msg_type` IN ('0', '1', '6', '7', '8', '9')".
					" AND `service_description` IS NULL".
				" ORDER BY `ctime`";
	my $result = $centstorage->query($query);
	return $result;
}

# Get First log date and last log date
sub getFirstLastLogTime {
	my $self = shift;
	my $centstorage = $self->{"centstorage"};
	
	my $query = "SELECT min(`ctime`) as minc, max(`ctime`) as maxc FROM `log`";
	my $sth = $centstorage->query($query);
	my ($start, $end) = (0,0);
    if (my $row = $sth->fetchrow_hashref()) {
		($start, $end) = ($row->{"minc"}, $row->{"maxc"});
    }
    $sth->finish;
    return ($start, $end);
}

1;