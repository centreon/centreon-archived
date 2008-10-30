###################################################################
# Centreon is developped with GPL Licence 2.0 
#
# GPL License: http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
#
# Developped by : Julien Mathis - jmathis@merethis.com
#
###################################################################
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
#    For information : contact@merethis.com
####################################################################


# Get host id in oreon Data base.
# need in paramter : host_name

sub getHostID($){
	
	my $con = CreateConnexionForOreon();
	my $sth2 = $con->prepare("SELECT `host_id` FROM `host` WHERE `host_name` = '".$_[0]."' AND `host_register` = '1'");
    writeLogFile("Error:" . $sth2->errstr . "\n") if (!$sth2->execute);
    my $data_host = $sth2->fetchrow_hashref();
    my $host_id = $data_host->{'host_id'};
    $sth2->finish();
    $con->disconnect();
    undef($data_host);
    return $host_id;
}

# Get host name in oreon Data base.
# need in paramter : host_id

sub getHostName($){
	return 0 if (!$_[0]);
	
	my $con = CreateConnexionForOreon();
	my $sth2 = $con->prepare("SELECT `host_name` FROM `host` WHERE `host_id` = '".$_[0]."' AND `host_register` = '1'");
    writeLogFile("Error:" . $sth2->errstr . "\n") if (!$sth2->execute);
    my $data_host = $sth2->fetchrow_hashref();
    my $host_name = $data_host->{'host_name'};
    undef($data_host);
    $sth2->finish();
    $con->disconnect();
    return $host_name;
}

1;