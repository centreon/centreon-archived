################################################################################
# Copyright 2005-2010 MERETHIS
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

package CentreonService;

# Constructor
# parameters:
# $logger: instance of class CentreonLogger
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

# returns two references to two hash tables => services indexed by id and services indexed by name
sub getAllServices {
	my $self = shift;
	my $centreon = $self->{"centreon"};
	my $activated = 1;
	if (@_) {
		$activated  = 0;
	}
	
    my (%service_ids, %service_names);
	# getting services linked to hosts
	my $query = "SELECT service_description, service_id, host_id, host_name".
				" FROM host, service, host_service_relation".
				" WHERE host_id = host_host_id and service_service_id = service_id".
						" AND service_register = '1'";
						if ($activated == 1) {
							$query .= " AND `service_activate`='1'";
						}
	my $sth = $centreon->query($query);
    while(my $row = $sth->fetchrow_hashref()) {
		$service_ids{$row->{'host_name'}.";;".$row->{'service_description'}} = $row->{'host_id'}.";;".$row->{'service_id'};
		$service_names{$row->{'host_id'}.";;".$row->{'service_id'}} = $row->{'host_name'}.";;".$row->{'service_description'};
	}
	#getting services linked to hostgroup
	$query = "SELECT service_description, service_id, host_id, host_name".
			" FROM host, service, host_service_relation hr, hostgroup_relation hgr, hostgroup hg".
			" WHERE  hr.hostgroup_hg_id is not null".
			" AND hr.service_service_id = service_id".
			" AND hr.hostgroup_hg_id = hgr.hostgroup_hg_id".
			" AND hgr.host_host_id = host_id".
			" AND service_register = '1'";
			if ($activated == 1) {
				$query .= " AND service_activate='1'".
				" AND host_activate = '1'".
				" AND hg.hg_activate = '1'";
			}			
	$query .= " AND hg.hg_id = hgr.hostgroup_hg_id";

	$sth = $centreon->query($query);
    while(my $row = $sth->fetchrow_hashref()) {
		$service_ids{$row->{'host_name'}.";;".$row->{'service_description'}} = $row->{'host_id'}.";;".$row->{'service_id'};
		$service_names{$row->{'host_id'}.";;".$row->{'service_id'}} = $row->{'host_name'}.";;".$row->{'service_description'};
	}
	$sth->finish();
		
	return (\%service_ids, \%service_names);
}

# Get all services, keys are IDs
sub getAllServicesByID {
	my $self = shift;
	my ($service_ids, $service_names) = $self->getAllServices();	
	return ($service_ids);
}

# Get all services, keys are names
sub getAllservicesByName {
	my $self = shift;
	my ($service_ids, $service_names) = $self->getAllServices();	
	return ($service_names);
}

1;