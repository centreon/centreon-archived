###################################################################
# Oreon is developped with GPL Licence 2.0 
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

sub getIntervalLenght(){
	my $sth = $con_oreon->prepare("SELECT interval_length FROM cfg_nagios WHERE nagios_activate = '1'");
	if (!$sth->execute) {writeLogFile("Error when getting interval_length : " . $sth->errstr . "\n");}
	my $interval = $sth->fetchrow_hashref();
	undef($sth);
	if (defined($interval)) {
		return $interval;
	} else {
		return 60;
	}
}

sub updateRrdDB($$$$$$$$){ # Path metric_id value timestamp interval type
	my $interval = 4000;
	my ($nb_value, $interval_length, $ERR);
	
	if (!-d $_[0]){
		writeLogFile("Directory ".$_[0]." does not exists. Trying to create it....\n");
		if (!mkdir($_[0], "775")) {
			writeLogFile("Can't create ".$_[0]." : permission denied\n");	
		} else {
			writeLogFile($_[0]." Created\n");
		}
	}
	
	# call function to check if DB exist and else create it
	if (-e $_[0]."/".$_[1].".rrd"){
		$valueRecorded++;
		$_[3] =~ s/\,/\./g;
		$_[6] =~ s/#S#/slash\_/g;
		if (!-w $_[0].$_[1].".rrd"){
			RRDs::update ($_[0].$_[1].".rrd" , "--template", substr($_[6], 0, 19), $_[2].":".sprintf("%e", $_[3]));
			$ERR = RRDs::error;
			#writeLogFile("Updating : $_[0]$_[1].rrd : ".substr($_[6], 0, 19).", ".$_[2].":".sprintf("%e", $_[3])."\n");
			writeLogFile("ERROR while updating $_[0]$_[1].rrd : $ERR\n") if ($ERR);
		} else {
			writeLogFile("ERROR when updating $_[0]$_[1].rrd : permission denied or file not found\n");
		}
	} else {
		if ($_[0] && $_[1] && $_[5]){
			$valueRecorded++;
			my $begin = $_[4] - 200000;
			$interval = getServiceCheckInterval($_[1]);
			$interval = 3 if (!defined($interval));
			CheckMySQLConnexion();
			$interval = getIntervalLenght() * $interval * 2;
			undef($data);
			undef($sth2);
			$nb_value =  $_[5] * 24 * 60 * 60 / $interval;
			$_[6] =~ s/#S#/slash\_/g;
			RRDs::create ($_[0].$_[1].".rrd", "-b ".$begin, "-s ".$interval, "DS:".substr($_[6], 0, 19).":GAUGE:".$interval.":U:U", "RRA:AVERAGE:0.5:1:".$nb_value, "RRA:MIN:0.5:12:".$nb_value, "RRA:MAX:0.5:12:".$nb_value);
			$ERR = RRDs::error;
			writeLogFile("Creating $_[0]$_[1].rrd -b $begin, -s $interval, DS:".substr($_[6], 0, 19).":GAUGE:$interval:U:U RRA:AVERAGE:0.5:1:$nb_value RRA:MIN:0.5:12:$nb_value RRA:MAX:0.5:12:$nb_value\n");
			writeLogFile("ERROR while creating $_[0]$_[1].rrd : $ERR\n") if ($ERR);
			$_[3] =~ s/\,/\./g;
			if (!-w $_[0].$_[1].".rrd"){
				RRDs::update ($_[0].$_[1].".rrd" , "--template", substr($_[6], 0, 19), $_[2].":".sprintf("%e", $_[3]));
				$ERR = RRDs::error;
				writeLogFile("ERROR while updating $_[0]/$_[1].rrd : $ERR\n") if ($ERR);
			} else {
				writeLogFile("ERROR when updating $_[0]$_[1].rrd : permission denied or file not found\n");
			}
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

	if (!-d $_[0]){
		writeLogFile("Directory ".$_[0]." does not exists. Trying to create it....\n");
		if (!mkdir($_[0], "775")) {
			writeLogFile("Can't create ".$_[0]." : permission denied\n");	
		} else {
			writeLogFile($_[0]." Created\n");
		}
	}

	# call function to check if DB exist and else create it
	if (-e $_[0]."/".$_[1].".rrd"){
		$valueRecorded++;
		$_[3] =~ s/\,/\./g;
		$_[6] =~ s/#S#/slash\_/g;
		if (!-w $_[0].$_[1].".rrd"){
			RRDs::update ($_[0].$_[1].".rrd" , "--template", substr($_[6], 0, 19), $_[2].":".sprintf("%e", $_[3]));
			$ERR = RRDs::error;
			if ($ERR){writeLogFile("ERROR while updating $_[0]$_[1].rrd : $ERR\n");}
		} else {
			writeLogFile("ERROR when updating $_[0]$_[1].rrd : permission denied or file not found\n");
		}
	} else {
		if ($_[0] && $_[1] && $_[5]){
			$valueRecorded++;
			my $begin = $_[4] - 200000;
			$interval = 1;
			CheckMySQLConnexion();
			$interval = getIntervalLenght() * $interval * 2;
			undef($data);
			undef($sth2);
			$nb_value =  $_[5] * 24 * 60 * 60 / $interval;
			writeLogFile("Creation of $_[0]$_[1].rrd\n");
			$_[6] =~ s/#S#/slash\_/g;
			RRDs::create ($_[0].$_[1].".rrd", "-b ".$begin, "-s ".$interval, "DS:".substr($_[6], 0, 19).":GAUGE:".$interval.":U:U", "RRA:AVERAGE:0.5:1:".$_[5], "RRA:MIN:0.5:12:".$_[5], "RRA:MAX:0.5:12:".$_[5]);
			$ERR = RRDs::error;
			if ($ERR){writeLogFile("ERROR while creating $_[0]$_[1].rrd : $ERR\n");}	
			$_[3] =~ s/\,/\./g;
			if (!-w $_[0].$_[1].".rrd"){
				RRDs::update ($_[0].$_[1].".rrd" , "--template", substr($_[6], 0, 19), $_[2].":".sprintf("%e", $_[3]));
				$ERR = RRDs::error;
				if ($ERR){writeLogFile("ERROR while updating $_[0]$_[1].rrd : $ERR\n");}	
			} else {
				writeLogFile("ERROR when updating $_[0]$_[1].rrd : permission denied or file not found\n");
			}
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