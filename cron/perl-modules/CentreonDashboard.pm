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

package CentreonDashboard;

use POSIX;
use Getopt::Long;
use Time::Local;

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
							" `date_start`, `date_end`) VALUES ";
	my $query_end = "";
	my $firstHost = 1;
	my $count = 0;
	my $sth;
	while (my ($key, $value) = each %$names) {
		if ($firstHost == 1) {
			$firstHost = 0;
		} else {
			$query_end .= ",";
		}
		$query_end .= "(".$key.",";
		if (defined($stateDurations->{$key})) {
			my $stats = $stateDurations->{$key};
			my @tab = @$stats;
			foreach(@tab) {
				 $query_end .= $_.",";
			}
			$query_end .= $start.",".$end.")";
		} else {
			$query_end .= "0,0,0,0,".$dayDuration.",0,0,0,".$start.",".$end.")";
		}
		$count++;
		if ($count == 5000) {
			$sth = $centstorage->query($query_start.$query_end);		
			$firstHost = 1;
			$query_end = "";
			$count = 0;
		}
	}
	if ($count) {
		$sth = $centstorage->query($query_start.$query_end);
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
							" `date_start`, `date_end`) VALUES ";
	my $query_end = "";
        my $firstService = 1;
	my $count = 0;
        my $sth;
	while (my ($key, $value) = each %$names) {
		if ($firstService == 1) {
                        $firstService = 0;
                } else {
                        $query_end .= ",";
                }
		my ($host_id, $service_id) = split(";;", $key);
		$query_end .= "(".$host_id.",".$service_id.",";
		if (defined($stateDurations->{$key})) {
			my $stats = $stateDurations->{$key};
			my @tab = @$stats;
			foreach(@tab) {
				 $query_end .= $_.",";
			}
			$query_end .= $start.",".$end.")";
			
		} else {
			$query_end .= "0,0,0,0,0,".$dayDuration.",0,0,0,0,".$start.",".$end.")";
		}
		$count++;
		if ($count == 5000) {
                        $sth = $centstorage->query($query_start.$query_end);
                        $firstService = 1;
                        $query_end = "";
                        $count = 0;
                }
	}
	if ($count) {
                $sth = $centstorage->query($query_start.$query_end);
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

# Delete service dashboard stats for a given period
sub deleteServiceStats {
	my $self = shift;
	my $centstorage = $self->{"centstorage"};
	
	my ($start, $end) = (shift, shift);
	my ($day, $month, $year) = (localtime($end))[3,4,5];
	$end = mktime(0, 0, 0, $day + 1, $month, $year);
	my $query = "DELETE FROM `log_archive_service` WHERE `date_start`>= ".$start." AND `date_end` <= ".$end;
	$centstorage->query($query);
}

# Delete host dashboard stats for a given period
sub deleteHostStats {
	my $self = shift;
	my $centstorage = $self->{"centstorage"};
	
	my ($start, $end) = (shift, shift);
	my ($day, $month, $year) = (localtime($end))[3,4,5];
	$end = mktime(0, 0, 0, $day + 1, $month, $year);
	my $query = "DELETE FROM `log_archive_host` WHERE `date_start`>= ".$start." AND `date_end` <= ".$end;
	$centstorage->query($query);
}
1;
