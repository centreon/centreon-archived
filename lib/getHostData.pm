################################################################################
# Copyright 2005-2009 MERETHIS
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
# SVN : $URL$
# SVN : $Id$
#
####################################################################################


# Get host id in oreon Data base.
# need in paramter : host_name, DBcnx

sub getHostID($$){

    my $con = $_[1];

    # Request
    my $sth2 = $con->prepare("SELECT `host_id` FROM `host` WHERE `host_name` = '".$_[0]."' AND `host_register` = '1'");
    writeLogFile("Error:" . $sth2->errstr . "\n") if (!$sth2->execute);

    my $data_host = $sth2->fetchrow_hashref();
    my $host_id = $data_host->{'host_id'};
    $sth2->finish();

    # free data
    undef($data_host);
    undef($con);
    
    # return host_id
    return $host_id;
}

# Get host name in oreon Data base.
# need in paramter : host_id

sub getHostName($){
    return 0 if (!$_[0]);
    
    my $con = CreateConnexionForOreon();
   
    my $sth2 = $con->prepare("SELECT `host_name` FROM `host` WHERE `host_id` = '".$_[0]."' AND `host_register` = '1'");
 	if (!$sth2->execute) {
	    writeLogFile("Error:" . $sth2->errstr . "\n");
	}

    my $data_host = $sth2->fetchrow_hashref();
    my $host_name = $data_host->{'host_name'};
    undef($data_host);
    $sth2->finish();
    $con->disconnect();
    
    return $host_name;
}

1;