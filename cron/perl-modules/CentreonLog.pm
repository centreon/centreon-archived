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
	$self->{"dbLayer"} = shift;
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
	my $query;
	if ($self->{'dbLayer'} eq "ndo") {
		$query = "SELECT `status`, `ctime`, `host_name`, `service_description`".
					" FROM `log`".
					" WHERE `ctime` >= ".$start.
						" AND `ctime` < ".$end.
						" AND (`type` = 'HARD' OR (`status` = 'OK' AND `type` = 'SOFT'))".
						" AND `service_description` IS NOT null".
						" AND `msg_type` IN ('0', '1', '6', '7', '8', '9')".
					" ORDER BY `ctime`";
	} elsif($self->{'dbLayer'} eq "broker") {
		$query = "SELECT `status`, `ctime`, `host_name`, `service_description`".
					" FROM `logs`".
					" WHERE `ctime` >= ".$start.
						" AND `ctime` < ".$end.
						" AND (`type` = 1 OR (`status` = 0 AND `type` = 0))".
						" AND `service_description` IS NOT null".
						" AND `msg_type` IN ('0', '1', '6', '7', '8', '9')".
					" ORDER BY `ctime`";
	}
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
	my $query;
	if ($self->{'dbLayer'} eq "ndo") {
		$query = "SELECT `status`, `ctime`, `host_name`".
				" FROM `log`".
				" WHERE `ctime` >= ".$start.
					" AND `ctime` < ".$end.
					" AND (`type` = 'HARD' OR (`status` = 'UP' AND `type` = 'SOFT'))".
					" AND `msg_type` IN ('0', '1', '6', '7', '8', '9')".
					" AND `service_description` IS NULL".
				" ORDER BY `ctime`";
	} elsif ($self->{'dbLayer'} eq "broker") {
		$query = "SELECT `status`, `ctime`, `host_name`".
				" FROM `logs`".
				" WHERE `ctime` >= ".$start.
					" AND `ctime` < ".$end.
					" AND (`type` = 1 OR (`status` = 0 AND `type` = 0))".
					" AND `msg_type` IN ('0', '1', '6', '7', '8', '9')".
					" AND `service_description` IS NULL".
				" ORDER BY `ctime`";
	}
	my $result = $centstorage->query($query);
	return $result;
}

# Get First log date and last log date
sub getFirstLastLogTime {
	my $self = shift;
	my $centstorage = $self->{"centstorage"};
	
	my $query;
	if ($self->{'dbLayer'} eq "ndo") {
		$query = "SELECT min(`ctime`) as minc, max(`ctime`) as maxc FROM `log`";
	} elsif ($self->{'dbLayer'} eq "broker") {
		$query = "SELECT min(`ctime`) as minc, max(`ctime`) as maxc FROM `logs`";
	}
	my $sth = $centstorage->query($query);
	my ($start, $end) = (0,0);
    if (my $row = $sth->fetchrow_hashref()) {
		($start, $end) = ($row->{"minc"}, $row->{"maxc"});
    }
    $sth->finish;
    return ($start, $end);
}

1;