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

sub GetPerfData(){
	my ($line_tab, $sth2, $data, $flag_drop, $sleeptime);
	
	CheckMySQLConnexion();
	$sth2 = $con_oreon->prepare("SELECT oreon_path FROM general_opt LIMIT 1");
	if (!$sth2->execute) {writeLogFile("Error when getting oreon Path : " . $sth2->errstr . "\n");}
	$data = $sth2->fetchrow_hashref();
	my $STOPFILE = $data->{'oreon_path'} . "ODS/stopods.flag";
	undef($sth2);
	undef($data);
	
	$sth2 = $con_ods->prepare("SELECT perfdata_file FROM config");
	if (!$sth2->execute) {writeLogFile("Error when getting perfdata file : " . $sth2->errstr . "\n");}
	$data = $sth2->fetchrow_hashref();
	my $PFDT = $data->{'perfdata_file'};
	undef($sth2);
	undef($data);

	while (1) {
		if (-r $PFDT){
			if (copy($PFDT, $PFDT."_read")){
				if (!unlink($PFDT)){writeLogFile("Error When removing service-perfdata file : $!");}
			} else {
				writeLogFile("Error When moving data in tmp read file : $!");
			}
			if (open(PFDT, "< $PFDT"."_read")){
				CheckMySQLConnexion();
				$sth2 = $con_ods->prepare("SELECT auto_drop,drop_file,perfdata_file FROM config");
				if (!$sth2->execute) {writeLogFile("Error when getting drop and perfdata properties : ".$sth2->errstr."\n");}
				$data = $sth2->fetchrow_hashref();	
				$PFDT = $data->{'perfdata_file'};
				
				$flag_drop = 1;
				if ($data->{'auto_drop'} == 1 && defined($data->{'drop_file'})){
					if (!open(DROP, ">> ".$data->{'drop_file'})){
						$flag_drop = 0;
						writeLogFile("can't write in ".$data->{'drop_file'}." : $!");
					}
				} else {
					$flag_drop = 0;
				}
				undef($data);
				
				while (<PFDT>){
					if ($debug){writeLogFile($_);}
					if ($flag_drop == 1){print DROP $_ ;}
			    	print $_;
			    	@line_tab = split('\t');
			    	if (defined($line_tab[5]) && ($line_tab[5] ne '' && $line_tab[5] ne "\n")){
						CheckMySQLConnexion();
						checkAndUpdate(@line_tab);
					}
					$line_tab[5] = '';
				}
				close(PFDT);
				if (!unlink($PFDT."_read")){
					writeLogFile("Error When removing service-perfdata file : $!");
				}
				if ($flag_drop == 1){close(DROP);}
				undef($line_tab);
				undef($flag_drop);
			} else {
				writeLogFile("Error When reading data in tmp read file : $!");
			}
		}
		$sleeptime = getSleepTime();
		for (my $i = 0; $i <= $sleeptime ; $i++){
			print ".";
			# Check if ods must leave
			return () if (-r $STOPFILE);
			# Sleep Time between To check
			sleep(1);	
		}
		undef($sleeptime);
		undef($i);
	}
} 

1;