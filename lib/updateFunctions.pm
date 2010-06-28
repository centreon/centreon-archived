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
# SVN : $URL$
# SVN : $Id$
#
####################################################################################

sub getIntervalLenght($){
    my $con_oreon = $_[0];
    my $sth = $con_oreon->prepare("SELECT `interval_length` FROM `cfg_nagios` WHERE `nagios_activate` = '1' LIMIT 1");
    if (!$sth->execute) {
		writeLogFile("Error when getting interval_length : " . $sth->errstr . "\n");
    }
    my $data = $sth->fetchrow_hashref();
    undef($sth);
    if (defined($data)) {
	return $data->{'interval_length'};
    } else {
	return 60;
    }
}

sub checkDBDirectory($) {
    if (defined($_[0])) {
	if (!-d $_[0]){
	    writeLogFile("Directory ".$_[0]." does not exists. Trying to create it....\n");
	    if (!mkdir($_[0], 0775)) {
		writeLogFile("Can't create ".$_[0]." : permission denied\n");
		return 0;
	    } else {
		writeLogFile($_[0]." Created\n");
		return 1;
	    }
	}
	return 1;
    } else {
	writeLogFile("Directory name empty...");
	return 0;
    }
    
}

# Update RRDTool DB with data
# Parameters :
#   Path metric_id value timestamp interval type

sub updateRRDDB($$$$$$$$) {
    my $interval = 4000;
    my $nb_value;
    my $interval_length;
    my $begin;

    $_[3] =~s/,/./g;
    if (checkDBDirectory($_[0]) == 0) {
	writeLogFile("Data droped....\n");
	return 0;
    }

    # call function to check if DB exist and else create it
    if (-e $_[0]."/".$_[1].".rrd") {
	updateRRDDatabase($_[0], $_[1], $_[6], $_[2], $_[3]);
    } else {
	if ($_[0] && $_[1] && $_[5]) {

	    $begin = $_[4] - 200000;
	    $interval = getServiceCheckInterval($_[1], $con_ods) * getIntervalLenght($con_oreon);
	    $interval_hb = $interval * 10;

	    # Caclulate number of value 
	    $nb_value =  $_[5] * 24 * 60 * 60 / $interval;

	    createRRDDatabase($_[0], $_[1], $begin, $interval, $_[6], $nb_value);
	    tuneRRDDatabase($_[0], $_[1], $_[6], $interval_hb);
	    updateRRDDatabase($_[0], $_[1], $_[6], $_[2], $_[3]);

	    undef($begin);
	}
    }
    undef($interval);
    undef($ERR);
}

# Add new bin data in Mysql DataBase

sub updateMysqlDB($$$$) {
    if (length($dataBinInfo)) {
	$dataBinInfo .= ", ";
    }
    $dataBinInfo .= "('".$_[0]."', '".$_[1]."', '".$_[2]."', '".$_[3]."')";
}

#
#
# $metric_id $connexion
sub getServiceDescFromIndex($$) {
    my $index = $_[0];
    my $cnx = $_[1];

    my $sth1 = $cnx->prepare("SELECT service_description FROM `metrics` m, `index_data` i WHERE i.id = m.index_id AND m.metric_id = '".$index."'");
    if (!$sth1->execute){
	writeLogFile("Error with requeste to get service dscr (getServiceDescFromIndex) : ".$sth1->errstr);
    }
    my $data = $sth1->fetchrow_hashref();
    undef($sth1);
    return $data->{'service_description'}
}

# Update RRDTool DB for modules with data
# Parameters :
#   Path metric_id value timestamp interval type

sub updateRRDDBforHiddenSVC($$$$$$$$) { 
    my $interval = 4000;
    my $nb_value;
    my $interval_length;

    $_[3] =~s/,/./g;
    if (checkDBDirectory($_[0]) == 0) {
	writeLogFile("Data droped....\n");
	return 0;
    }

    # call function to check if DB exist and else create it
    if (-e $_[0]."/".$_[1].".rrd"){
	updateRRDDatabase($_[0], $_[1], $_[6], $_[2], $_[3]);
    } else {
	if ($_[0] && $_[1] && $_[5]){
	    my $begin = $_[4] - 200000;
	    CheckMySQLConnexion();

	    $interval = getModulesInterval(getServiceDescFromIndex($_[1], $con_ods), $con_oreon);
	    $interval_hb = $interval * 10;
	    
	    # Caclulate number of value 
	    $nb_value =  $_[5] * 24 * 60 * 60 / $interval;

	    createRRDDatabase($_[0], $_[1], $begin, $interval, $_[6], $nb_value);
	    tuneRRDDatabase($_[0], $_[1], $_[6], $interval_hb);
	    updateRRDDatabase($_[0], $_[1], $_[6], $_[2], $_[3]);

	    undef($begin);
	}
    }
    undef($interval);
}


# Add new bin data in Mysql DataBase
# Paremeters : 
#    

sub updateMysqlDBforHiddenSVC($$$$) {
    if (length($dataBinInfo)) {
	$dataBinInfo .= ", ";
    }
    $dataBinInfo .= "('".$_[0]."', '".$_[1]."', '".$_[2]."', '".$_[3]."')";
}

1;
