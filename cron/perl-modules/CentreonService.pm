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