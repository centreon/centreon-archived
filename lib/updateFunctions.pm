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

sub getIntervalLenght($){
    my $con_oreon = $_[0];
    my $sth = $con_oreon->prepare("SELECT `interval_length` FROM `cfg_nagios` WHERE `nagios_activate` = '1' LIMIT 1");
    if (!$sth->execute) {writeLogFile("Error when getting interval_length : " . $sth->errstr . "\n");}
    my @data = $sth->fetchrow_hashref();
    undef($sth);
    if (defined($interval)) {
	return $data->{'interval_length'};
    } else {
	return 60;
    }
}

sub checkDBDirectory($) {
    if (defined($_[0])) {
	if (!-d $_[0]){
	    writeLogFile("Directory ".$_[0]." does not exists. Trying to create it....\n");
	    if (!mkdir($_[0], "775")) {
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
	    $interval_hb = $interval * 2;
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

# Update RRDTool DB for modules with data
# Parameters :
#   Path metric_id value timestamp interval type

sub updateRRDDBforHiddenSVC($$$$$$$$) { 
    my $interval = 4000;
    my $nb_value;
    my $interval_length;

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
	    $interval = 1;
	    $interval = getIntervalLenght($con_oreon) * $interval;
	    $interval_hb = $interval * 2;
	    undef($data);
	    undef($sth2);
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
