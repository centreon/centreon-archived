################################################################################
# Copyright 2005-2011 MERETHIS
# Centreon is developped by : Julien Mathis and Romain Le Merlus under
# GPL Licence 2.0.
# 
# This program is free software; you can redistribute it and/or modify it under 
# the terms of the GNU General Public License as published by the Free Software 
# Foundation ; either version 2 of the License.
# 
# This program is distributed in the hope that it will be useful, but WITHOUT ANY
# WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
# PARTICULAR PURPOSE. See the GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License along with 
# this program; if not, see <http://www.gnu.org/licenses>.
# 
# Linking this program statically or dynamically with other modules is making a 
# combined work based on this program. Thus, the terms and conditions of the GNU 
# General Public License cover the whole combination.
# 
# As a special exception, the copyright holders of this program give MERETHIS 
# permission to link this program with independent modules to produce an executable, 
# regardless of the license terms of these independent modules, and to copy and 
# distribute the resulting executable under terms of MERETHIS choice, provided that 
# MERETHIS also meet, for each linked independent module, the terms  and conditions 
# of the license of that module. An independent module is a module which is not 
# derived from this program. If you modify this program, you may extend this 
# exception to your version of the program, but you are not obliged to do so. If you
# do not wish to do so, delete this exception statement from your version.
# 
# For more information : contact@centreon.com
# 
# SVN : $URL
# SVN : $Id
#
####################################################################################

use strict;
use warnings;

package CentreonDownTime;

# Constructor
# parameters:
# $logger: instance of class CentreonLogger
# $centreon: Instance of centreonDB class for connection to Centreon database
# $dbLayer : Database Layer : ndo | broker
# $centstorage: (optionnal) Instance of centreonDB class for connection to Centstorage database
sub new {
	my $class = shift;
	my $self  = {};
	$self->{"logger"}	= shift;
	$self->{"centstatus"} = shift;
	$self->{'dbLayer'} = shift;
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
	my $dbLayer = $self->{'dbLayer'};
	my $query;
	
	if ($dbLayer eq "ndo") {
		$query = "SELECT `name1`, `name2`,".
				" UNIX_TIMESTAMP(`actual_start_time`) as start_time,".
				" UNIX_TIMESTAMP(`actual_end_time`) as end_time".
			" FROM `nagios_downtimehistory` d, `nagios_objects` o".
			" WHERE o.`object_id` = d.`object_id` AND o.`objecttype_id` = '".$type."'".
			" AND was_started = 1".
			" AND UNIX_TIMESTAMP(`actual_start_time`) < ".$end.
			" AND (UNIX_TIMESTAMP(`actual_end_time`) > ".$start." || UNIX_TIMESTAMP(`actual_end_time`) = 0)".
			" ORDER BY `name1` ASC, `actual_start_time` ASC, `actual_end_time` ASC";
	} elsif ($dbLayer eq "broker") {
		$query = "SELECT h.name as name1, s.description as name2, " .
				 "d.start_time, d.end_time " .
				 "FROM `hosts` h, `downtimes` d " .
				 "LEFT JOIN services s ON s.service_id = d.service_id " .
				 "WHERE started = 1 " .
				 "AND d.host_id = h.host_id ";
		if ($type == 1) {
			$query .= "AND d.type = 2 "; # That can be confusing, but downtime_type 2 is for host
		} elsif ($type == 2) {
			$query .= "AND d.type = 1 "; # That can be confusing, but downtime_type 1 is for service
		}
		$query .= "AND start_time < " . $end . " " .
				 "AND (end_time > " . $start . " || end_time = 0) " .
				 "ORDER BY name1 ASC, start_time ASC, end_time ASC";		
	}

	my $sth = $centreon->query($query);
	
	my @periods = ();
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
				if ($checkTab->[0] eq $allIds->{$id}){
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
	my $state = shift;
	
	my @events = ();
	my $total = 0;
	if ($state ne "" && defined($downTimes) && defined($state) && $state != 0) {
		$total = scalar(@$downTimes);
		
	}
	for (my $i = 0; $i < $total && $start < $end; $i++) {
 		my $tab = $downTimes->[$i];
 		my $id = $tab->[0];
 		my $downTimeStart = $tab->[1];
 		my $downTimeEnd = $tab->[2];
 		
 		if ($id eq $objectId) {
 			
 			if ($downTimeStart < $start) {
 				$downTimeStart = $start;
 			}
 			if ($downTimeEnd > $end) {
 				$downTimeEnd = $end;
 			}
 			if ($downTimeStart < $end && $downTimeEnd > $start) {
 				if ($downTimeStart > $start) {
 					my @tab = ($start, $downTimeStart, 0);
 					$events[scalar(@events)] = \@tab;
 				}
 				my @tab = ($downTimeStart, $downTimeEnd, 1);
 				$events[scalar(@events)] = \@tab;
 				$start = $downTimeEnd;
 			}
 		}
	}
	if ($start < $end) {
		my @tab = ($start, $end, 0);
		$events[scalar(@events)] = \@tab;
	}
	return (\@events);
}

sub splitUpdateEventDownTime {
	my $self = shift;
	
	my $objectId = shift;
	my $start = shift;
	my $end = shift;
	my $downTimeFlag = shift;
	my $downTimes = shift;
	my $state = shift;
	
	my $updated = 0;
	my @events = ();
	my $updateTime = 0;
	my $total = 0;
	if (defined($downTimes) && $state != 0) {
		$total = scalar(@$downTimes);
	}
	for (my $i = 0; $i <  $total && $start < $end; $i++) {
		my $tab = $downTimes->[$i];
 		my $id = $tab->[0];
 		my $downTimeStart = $tab->[1];
 		my $downTimeEnd = $tab->[2];
 
 		if ($id eq $objectId) {
 			if ($downTimeStart < $start) {
 				$downTimeStart = $start;
 			}
 			if ($downTimeEnd > $end) {
 				$downTimeEnd = $end;
 			}
 			if ($downTimeStart < $end && $downTimeEnd > $start) {
 				if ($updated == 0) {
					$updated = 1;
					if ($downTimeStart > $start) {
						if ($downTimeFlag == 1) {
							my @tab = ($start, $downTimeStart, 0);
							$events[scalar(@events)] = \@tab;
						}else {
							$updateTime = $downTimeStart;
						}
						my @tab = ($downTimeStart, $downTimeEnd, 1);
						$events[scalar(@events)] = \@tab;
					}else {
						if ($downTimeFlag == 1) {
							$updateTime = $downTimeEnd;
						}else {
							my @tab = ($downTimeStart, $downTimeEnd, 1);
							$events[scalar(@events)] = \@tab;
						}			
					}
				}else {
					if ($downTimeStart > $start) {
						my @tab = ($start, $downTimeStart, 0);
						$events[scalar(@events)] = \@tab;
					}
					my @tab = ($downTimeStart, $downTimeEnd, 1);
					$events[scalar(@events)] = \@tab;
				}
				$start = $downTimeEnd;
			}
 		}
	}
	if ($start < $end && scalar(@events)) {
		my @tab = ($start, $end, 0);
		$events[scalar(@events)] = \@tab;
	}else {
		$updateTime = $end;
	}
	return ($updateTime, \@events);
}

1;