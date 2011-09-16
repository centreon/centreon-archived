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

package CentreonAck;

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

# returns first ack time for a service or a host event
sub getServiceAckTime {
	my $self = shift;
	my $centreon = $self->{"centstatus"};
	my $start = shift;
	my $end = shift;
	my $hostName = shift;
	my $serviceDescription = shift;
	my $dbLayer = $self->{'dbLayer'};
	my $query;
	
	if ($dbLayer eq "ndo") {
		$query = "SELECT UNIX_TIMESTAMP(`entry_time`) as ack_time ".
			" FROM `nagios_acknowledgements` a, `nagios_objects` o".
			" WHERE o.`object_id` = a.`object_id`".
			" AND `acknowledgement_type` = '1'".
			" AND UNIX_TIMESTAMP(`entry_time`) >= ".$start.
			" AND UNIX_TIMESTAMP(`entry_time`) <= ".$end.
			" AND o.`name1` = '".$hostName. "'".
			" AND o.`name2` = '".$serviceDescription. "'".	
			" ORDER BY `entry_time` asc";
	} elsif ($dbLayer eq "broker") {
		$query = "SELECT `entry_time` as ack_time ".
			" FROM `acknowledgements` a, `services` s, `hosts` h ".
			" WHERE h.`host_id` = a.`host_id`".
			" AND a.`host_id` = s.`host_id`".
			" AND `type` = 1".
			" AND `entry_time` >= ".$start.
			" AND `entry_time` <= ".$end.
			" AND h.`name` = '".$hostName. "'".
			" AND s.`description` = '".$serviceDescription. "'".	
			" ORDER BY `entry_time` asc";
	}

	my $sth = $centreon->query($query);
	my $ackTime = "NULL";
	if (my $row = $sth->fetchrow_hashref()) {
		$ackTime = $row->{'ack_time'};
	}
	$sth->finish();
	return ($ackTime);
}

# returns first ack time for a service or a host event
sub getHostAckTime {
	my $self = shift;
	my $centreon = $self->{"centstatus"};
	my $start = shift;
	my $end = shift;
	my $hostName = shift;
	my $dbLayer = $self->{'dbLayer'};
	my $query;
	
	if ($dbLayer eq "ndo") {
		$query = "SELECT UNIX_TIMESTAMP(`entry_time`) as ack_time ".
			" FROM `nagios_acknowledgements` a, `nagios_objects` o".
			" WHERE o.`object_id` = a.`object_id`".
			" AND `acknowledgement_type` = '0'".
			" AND UNIX_TIMESTAMP(`entry_time`) >= ".$start.
			" AND UNIX_TIMESTAMP(`entry_time`) <= ".$end.
			" AND o.`name1` = '".$hostName. "'".
			" ORDER BY `entry_time` asc";
	} elsif ($dbLayer eq "broker") {
		$query = "SELECT entry_time as ack_time ".
			" FROM `acknowledgements` a, `hosts` h".
			" WHERE h.`host_id` = a.`host_id`".
			" AND `type` = 0".
			" AND `entry_time` >= ".$start.
			" AND `entry_time` <= ".$end.
			" AND h.`name` = '".$hostName. "'".
			" ORDER BY `entry_time` asc";
	}

	my $sth = $centreon->query($query);
	my $ackTime = "NULL";
	if (my $row = $sth->fetchrow_hashref()) {
		$ackTime = $row->{'ack_time'};
	}
	$sth->finish();
	return ($ackTime);
}

1;