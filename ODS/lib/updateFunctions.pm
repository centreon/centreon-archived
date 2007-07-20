###################################################################
# Oreon is developped with GPL Licence 2.0 
#
# GPL License: http://www.gnu.org/licenses/gpl.txt
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

sub updateRrdDB($$$$$$$$){ # Path metric_id value timestamp interval type
	my $ERR;
	my $interval = 4000;
	my $nb_value;
	my $interval_length;
	
	# call function to check if DB exist and else create it
	if (-e $_[0]."/".$_[1].".rrd"){
		$valueRecorded++;
		$_[3] =~ s/\,/\./g;
		$_[6] =~ s/#S#/slash\_/g;
		RRDs::update ($_[0].$_[1].".rrd" , "--template", $_[6], $_[2].":".sprintf("%e", $_[3]));
		$ERR = RRDs::error;
		if ($ERR){
			writeLogFile("Updating : $_[0]$_[1].rrd : ".$_[6].", ".$_[2].":".sprintf("%e", $_[3])."\n");
			writeLogFile("ERROR while updating $_[0]$_[1].rrd : $ERR\n");	
		}
	} else {
		if ($_[0] && $_[1] && $_[5]){
			$valueRecorded++;
			my $begin = $_[4] - 200000;
			$interval = getServiceCheckInterval($_[1]);
			if (!defined($interval)){$interval = 3};
			CheckMySQLConnexion();
			my $sth2 = $con_oreon->prepare("SELECT interval_length FROM cfg_nagios WHERE nagios_activate");
			if (!$sth2->execute) {writeLogFile("Error when getting interval_length : " . $sth2->errstr . "\n");}
			$data = $sth2->fetchrow_hashref();
			$interval = $interval * $data->{'interval_length'} + 10;
			undef($data);
			undef($sth2);
			$nb_value =  $_[5] * 24 * 60 * 60 / $interval;
			$_[6] =~ s/#S#/slash\_/g;
			RRDs::create ($_[0].$_[1].".rrd", "-b ".$begin, "-s ".$interval, "DS:".$_[6].":GAUGE:".$interval.":U:U", "RRA:AVERAGE:0.5:1:".$nb_value, "RRA:MIN:0.5:12:".$nb_value, "RRA:MAX:0.5:12:".$nb_value);
			$ERR = RRDs::error;
			writeLogFile("Creating $_[0]$_[1].rrd -b $begin, -s $interval, DS:$_[6]:GAUGE:$interval:U:U RRA:AVERAGE:0.5:1:$nb_value RRA:MIN:0.5:12:$nb_value RRA:MAX:0.5:12:$nb_value\n");
			writeLogFile("ERROR while creating $_[0]$_[1].rrd : $ERR\n") if ($ERR);
			$_[3] =~ s/\,/\./g;
			RRDs::update ($_[0].$_[1].".rrd" , "--template", $_[6], $_[2].":".sprintf("%e", $_[3]));
			$ERR = RRDs::error;
			if ($ERR){writeLogFile("ERROR while updating $_[0]/$_[1].rrd : $ERR\n");}	
			undef($begin);
		}
	}
	undef($interval);
	undef($ERR);
}

# Add new bin data in Mysql DataBase

sub updateMysqlDB($$$$){ # connexion value timestamp
	my $sth1 = $con_ods->prepare("INSERT INTO `data_bin` (`id_metric`, `ctime`, `value`, `status`) VALUES ('".$_[0]."', '".$_[1]."', '".$_[2]."', '".$_[3]."')");
	if (!$sth1->execute) {writeLogFile("Error:" . $sth1->errstr . "\n");}
	undef($sth1);
}

sub updateRrdDBforHiddenSVC($$$$$$$$){ # Path metric_id value timestamp interval type
	my $ERR;
	my $interval = 4000;

	# call function to check if DB exist and else create it
	if (-e $_[0]."/".$_[1].".rrd"){
		$valueRecorded++;
		$_[3] =~ s/\,/\./g;
		$_[6] =~ s/#S#/slash\_/g;
		RRDs::update ($_[0].$_[1].".rrd" , "--template", $_[6], $_[2].":".sprintf("%e", $_[3]));
		$ERR = RRDs::error;
		if ($ERR){writeLogFile("ERROR while updating $_[0]$_[1].rrd : $ERR\n");}
	} else {
		if ($_[0] && $_[1] && $_[5]){
			$valueRecorded++;
			my $begin = $_[4] - 200000;
			$interval = 1;
			CheckMySQLConnexion();
			my $sth2 = $con_oreon->prepare("SELECT interval_length FROM cfg_nagios WHERE nagios_activate");
			if (!$sth2->execute) {writeLogFile("Error when getting interval_length : " . $sth2->errstr . "\n");}
			$data = $sth2->fetchrow_hashref();
			$interval = $interval * $data->{'interval_length'} + 10;
			undef($data);
			undef($sth2);
			$nb_value =  $_[5] * 24 * 60 * 60 / $interval;
			writeLogFile("Creation of $_[0]$_[1].rrd\n");
			$_[6] =~ s/#S#/slash\_/g;
			RRDs::create ($_[0].$_[1].".rrd", "-b ".$begin, "-s ".$interval, "DS:".$_[6].":GAUGE:".$interval.":U:U", "RRA:AVERAGE:0.5:1:".$_[5], "RRA:MIN:0.5:12:".$_[5], "RRA:MAX:0.5:12:".$_[5]);
			$ERR = RRDs::error;
			if ($ERR){writeLogFile("ERROR while creating $_[0]$_[1].rrd : $ERR\n");}	
			$_[3] =~ s/\,/\./g;
			RRDs::update ($_[0].$_[1].".rrd" , "--template", $_[6], $_[2].":".sprintf("%e", $_[3]));
			$ERR = RRDs::error;
			if ($ERR){writeLogFile("ERROR while updating $_[0]$_[1].rrd : $ERR\n");}	
			undef($begin);
		}
	}
	undef($interval);
	undef($ERR);
}

# Add new bin data in Mysql DataBase

sub updateMysqlDBforHiddenSVC($$$$){ # connexion value timestamp
	my $sth1 = $con_ods->prepare("INSERT INTO `data_bin` (`id_metric`, `ctime`, `value`, `status`) VALUES ('".$_[0]."', '".$_[1]."', '".$_[2]."', '".$_[3]."')");
	if (!$sth1->execute) {writeLogFile("Error:" . $sth1->errstr . "\n");}
	undef($sth1);
}

1;