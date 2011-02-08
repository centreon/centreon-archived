use strict;
use warnings;

package CentreonDownTime;

# Constructor
# parameters:
# $logger: instance of class CentreonLogger
# $centreon: Instance of centreonDB class for connection to Centreon database
# $centstorage: (optionnal) Instance of centreonDB class for connection to Centstorage database
sub new {
	my $class = shift;
	my $self  = {};
	$self->{"logger"}	= shift;
	$self->{"centstatus"} = shift;
	if (@_) {
		$self->{"centstorage"}  = shift;
	}
	bless $self, $class;
	return $self;
}

# returns two references to two hash tables => hosts indexed by id and hosts indexed by name
sub getDownTime {
	my $self = shift;
	my $centreon = $self->{"centstatus"};
	my $allIds = shift;
	my $start = shift;
	my $end = shift;
	my $type = shift; # if 1 => host, if 2 => service
	my $query = "SELECT `name1`, `name2`".
						" UNIX_TIMESTAMP(`actual_start_time`) as start_time,".
						" UNIX_TIMESTAMP(`actual_end_time`) as end_time".
				" FROM `nagios_downtimehistory` d, `nagios_objects` o".
				" WHERE o.`object_id` = d.`object_id` AND o.`objecttype` = '".$type."'".
						" AND UNIX_TIMESTAMP(`actual_start_time`) < ".$end.
						" AND (UNIX_TIMESTAMP(`actual_end_time`) > ".$start." || UNIX_TIMESTAMP(`actual_end_time`) = 0)".
				" ORDER BY `name1` ASC, `actual_start_time` ASC, `actual_end_time` ASC";
	my $sth = $centreon->query($query);
	
	my @periods;
	while (my $row = $sth->fetchrow_hashref()) {
		my $id = $row->{"name1"};
		if ($type == 2) {
			$id .= ";;".$row->{"name2"}
		}
		if (defined($allIds->{$id})) {
			if ($row->{"start_time"} < $start) {
				$row->{"start_time"} = $start;
			}
			if ($row->{"end_time"} > $end || $row->{"end_time"} == 0) {
				$row->{"end_time"} = $end;
			}
			my $insert = 1;
			for (my $i = 0; $i < scalar(@periods) && $insert; $i++) {
				my $checkTab = $periods[$i];
				if ($checkTab->[0] == $allIds->{$id}){
					if ($row->{"start_time"} <= $checkTab->[2] && $row->{"end_time"} <= $checkTab->[2]) {
						$insert = 0;
					}elsif ($row->{"start_time"} <= $checkTab->[2] && $row->{"end_time"} > $checkTab->[2]) {
						$checkTab->[2] = $row->{"end_time"};
						$periods[$i] = $checkTab;
						$insert = 0;
					}
				}
			}
			if ($insert) {
				my @tab = ($allIds->{$id}, $row->{"start_time"}, $row->{"end_time"});
				$periods[scalar(@periods)] = \@tab;
			}
		}
	}
	$sth->finish();
	return (\@periods);
}

sub splitInsertEventDownTime {
	my $self = shift;
	
	my $objectId = shift;
	my $start = shift;
	my $end = shift;
	my $downTimes = shift;
	
	my @events;
	for (my $i = 0; $i < scalar($downTimes) && $start < $end; $i++) {
 		my $tab = $_;
 		my $id = $tab->[0];
 		my $downTimeStart = $tab->[1];
 		my $downTimeEnd = $tab->[2];
 		if ($id == $objectId) {
 			if ($downTimeStart < $start) {
 				$downTimeStart = $start;
 			}
 			if ($downTimeEnd > $end) {
 				$downTimeStart = $end;
 			}
 			if ($downTimeStart < $end && $downTimeEnd > $start) {
 				if ($downTimeStart > $start) {
 					$events[scalar(@events)] = \($start, $downTimeStart, 0);
 				}
 				$events[scalar(@events)] = \($start, $downTimeStart, 1);
 				$start = $downTimeEnd;
 			}
 		}
	}
	return ($start, \@events);
}

sub splitUpdateEventDownTime {
	my $self = shift;
	
	my $objectId = shift;
	my $start = shift;
	my $end = shift;
	my $downTimeFlag = shift;
	my $downTimes = shift;
	
	my $updated = 0;
	my @events;
	my $updateTime = 0;
	for (my $i = 0; $i < scalar(@$downTimes) && $start < $end; $i++) {
 		my $tab = $_;
 		my $id = $tab->[0];
 		my $downTimeStart = $tab->[1];
 		my $downTimeEnd = $tab->[2];
 		if ($id == $objectId) {
 			if ($downTimeStart < $start) {
 				$downTimeStart = $start;
 			}
 			if ($downTimeEnd > $end) {
 				$downTimeStart = $end;
 			}
			if ($downTimeStart < $end && $downTimeEnd > $start) {
				if ($updated == 0) {
					$updated = 1;
					if ($downTimeStart > $start) {
						if ($downTimeFlag == 1) {
							$events[scalar(@events)] = \($start, $downTimeStart, 0);
						}else {
							$updateTime = $downTimeStart;
						}
						$events[scalar(@events)] = \($downTimeStart, $downTimeEnd, 1);
					}else {
						if ($downTimeFlag == 1) {
							$updateTime = $downTimeEnd;
						}else {
							$events[scalar(@events)] = \($downTimeStart, $downTimeEnd, 1);
						}			
					}
				}else {
					if ($downTimeStart > $start) {
						$events[scalar(@events)] = \($start, $downTimeStart, 0);
					}
					$events[scalar(@events)] = \($downTimeStart, $downTimeEnd, 1);
				}
				$start = $downTimeEnd;
			}
 		}
	}
	return ($start, $updateTime, \@events);
}

1;