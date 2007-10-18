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
#
# identifier la metric
# meed arguments 
# - perfdata
# - index_id
# - status
# - timestamp
# - storage_type

sub identify_metric($$$$$$$){ # perfdata index status time type counter rebuild
    my (@data, $begin, $just_insert, $generalcounter);
	$generalcounter = $_[5];
	$just_insert = 0;
	   				
	# Get conf Data
	my $sth1 = $con_ods->prepare("SELECT * FROM config");
	writeLogFile("Error:" . $sth1->errstr . "\n") if (!$sth1->execute);
	my $configuration = $sth1->fetchrow_hashref();
	undef($sth1);
	
	# Cut perfdata    	
	$_[0] =~ s/\n//g;
	my $metric = $_[0];
	while ($metric =~ m/\'?([a-zA-Z0-9\_\-\/\.\:\ ]+)\'?\=([0-9\.\,\-]+)([a-zA-Z0-9\_\-\/\\\%]*)[\;]*([0-9\.\,\-]*)[\;]*([0-9\.\,\-]*)[\;]*([0-9\.\,\-]*)[\;]*([0-9\.\,\-]*)\s?/g){
	    if (!defined($3)){$3 = "";}
	    if (!defined($4)){$4 = "";}
	    if (!defined($5)){$5 = "";}
	    if (!defined($6)){$6 = "";}
	    if (!defined($7)){$7 = "";}
	    
	    my $x = 0;
	    for (my $cpt = 1;defined($$cpt);$cpt++,$x++){
	    	$data[$x] = $$cpt;
	  	}
		if ($1 && defined($2)){			
			# Check if metric is known...
			$data[0] =~ s/\//#S#/g;
			$data[0] =~ s/\\/#BS#/g;
			$data[0] =~ s/\./\-/g;
			$data[0] =~ s/\,/\-/g;
			$data[0] =~ s/\:/\-/g;
			$data[0] =~ s/\ /\-/g;

			my $sth1 = $con_ods->prepare("SELECT * FROM `metrics` WHERE `index_id` = '".$_[1]."' AND `metric_name` = '".$data[0]."'");
			writeLogFile("Error:" . $sth1->errstr . "\n") if (!$sth1->execute);
			if ($sth1->rows() eq 0){
				$just_insert = 1;   				
				undef($sth1);
				# Si pas connue -> insert
			   	my $sth2 = $con_ods->prepare("INSERT INTO `metrics` (`index_id`, `metric_name`, `unit_name`, `warn`, `crit`, `min`, `max`) VALUES ('".$_[1]."', '".$data[0]."', '".$data[2]."', '".$data[3]."', '".$data[4]."', '".$data[5]."', '".$data[6]."')");
			    writeLogFile("Error:" . $sth2->errstr . "\n") if (!$sth2->execute);
			    undef($sth2);
			    # Get ID
			   	$sth1 = $con_ods->prepare("SELECT * FROM `metrics` WHERE `index_id` = '".$_[1]."' AND `metric_name` = '".$data[0]."'");
				writeLogFile("Error:" . $sth1->errstr . "\n") if (!$sth1->execute);
			}
			my $metric = $sth1->fetchrow_hashref();
			if (!defined($metric->{'locked'}) || (defined($metric->{'locked'}) && $metric->{'locked'} eq 0)){
				undef($sth1);
			   	if ($just_insert || ($metric->{'unit_name'} ne $data[2])){
			   		my $sth1 = $con_ods->prepare("UPDATE `metrics` SET `unit_name` = '".$data[2]."', `warn` = '".$data[3]."', `crit` = '".$data[4]."', `min` = '".$data[5]."', `max` = '".$data[6]."' WHERE `metric_id` = '".$metric->{'metric_id'}."'");
			    	writeLogFile("Error:" . $sth1->errstr . "\n") if (!$sth1->execute);
			    	undef($sth1);
			   	}
				if ($data[3] ne "" || $data[4] ne "" || $data[5] ne "" || $data[6] ne ""){
					my $str = "`warn` = ".$data[3]." " if ($data[3]);
				   	if ($data[4]){
				   		$str .= ", " if ($str ne "");
					   	$str .= "`crit` = ".$data[4]." ";
				   	}
				   	if ($data[5]){
				   		$str .= ", " if ($str ne "");
						$str .= "`min` = ".$data[5]." ";
				   	}
				   	if ($data[6]){
				   		$str .= ", " if ($str ne "");
						$str .= "`max` = ".$data[6]." ";
				   	}
				   	if (defined($metric->{'metric_id'}) && $metric->{'metric_id'}){
				   		if ($str){
						   	$sth1 = $con_ods->prepare("UPDATE metrics SET $str WHERE `metric_id` = '".$metric->{'metric_id'}."'");
							writeLogFile("Error:" . $sth1->errstr . "\n") if (!$sth1->execute);
						    undef($sth1);
						}
						undef($str);
					}
				}		   	
				# Check Storage Type
				# O -> BD Mysql & 1 -> RRDTool
				$begin = $_[3] - 200;
				if (defined($data[1])){
					updateMysqlDB($metric->{'metric_id'}, $_[3], $data[1], $status{$_[2]});
					updateRrdDB($configuration->{'RRDdatabase_path'}, $metric->{'metric_id'}, $_[3], $data[1], $begin, $configuration->{'len_storage_rrd'}, $metric->{'metric_name'}) if (defined($_[6]) && $_[6] ne 2);
					$generalcounter++;
				}
			}
		    $just_insert = 0;
	    }		
	    undef(@data);
		undef(%metric);
	}
    undef($begin);
    undef($configuration);
    return $generalcounter;
}

# identifier la metric
# meed arguments 
# - perfdata
# - index_id
# - status
# - timestamp
# - storage_type

sub identify_hidden_metric($$$$$$$){ # perfdata index status time type counter rebuild
	my (@data, $begin, $just_insert, $generalcounter);
	$generalcounter = $_[5];
	$just_insert = 0;   				
	
	# Get conf Data
	my $sth1 = $con_ods->prepare("SELECT * FROM config");
	if (!$sth1->execute) {writeLogFile("Error:" . $sth1->errstr . "\n");}
	my $configuration = $sth1->fetchrow_hashref();
	undef($sth1);
	
    # Cut perfdata    	
	$_[0] =~ s/\n//g;
	my $metric = $_[0];
	while ($metric =~ m/\'?([a-zA-Z0-9\_\-\/\.\:\ ]+)\'?\=([0-9\.\,\-]+)([a-zA-Z0-9\_\-\/\\\%]*)[\;]*([0-9\.\,\-]*)[\;]*([0-9\.\,\-]*)[\;]*([0-9\.\,\-]*)[\;]*([0-9\.\,\-]*)\s?/g){
	    if (!defined($3)){$3 = "";}
	    if (!defined($4)){$4 = "";}
	    if (!defined($5)){$5 = "";}
	    if (!defined($6)){$6 = "";}
	    if (!defined($7)){$7 = "";}
	    
	    my $x = 0;
	    for (my $cpt = 1;defined($$cpt);$cpt++,$x++){
	    	$data[$x] = $$cpt;
	  	}
		if ($1 && defined($2)){			
			# Check if metric is known...
			$data[0] =~ s/\//#S#/g;
			$data[0] =~ s/\\/#BS#/g;
			$data[0] =~ s/\./\-/g;
			$data[0] =~ s/\,/\-/g;
			$data[0] =~ s/\:/\-/g;
			$data[0] =~ s/\ /\-/g;

			my $sth1 = $con_ods->prepare("SELECT * FROM `metrics` WHERE `index_id` = '".$_[1]."' AND `metric_name` = '".$data[0]."'");
			writeLogFile("Error:" . $sth1->errstr . "\n") if (!$sth1->execute);
			if ($sth1->rows() eq 0){
				$just_insert = 1;   				
				undef($sth1);
				# Si pas connue -> insert
			   	my $sth2 = $con_ods->prepare("INSERT INTO `metrics` (`index_id`, `metric_name`, `unit_name`, `warn`, `crit`, `min`, `max`) VALUES ('".$_[1]."', '".$data[0]."', '".$data[2]."', '".$data[3]."', '".$data[4]."', '".$data[5]."', '".$data[6]."')");
			    writeLogFile("Error:" . $sth2->errstr . "\n") if (!$sth2->execute);
			    undef($sth2);
			    # Get ID
			   	$sth1 = $con_ods->prepare("SELECT * FROM `metrics` WHERE `index_id` = '".$_[1]."' AND `metric_name` = '".$data[0]."'");
				writeLogFile("Error:" . $sth1->errstr . "\n") if (!$sth1->execute);
			}
			my $metric = $sth1->fetchrow_hashref();
			if (!defined($metric->{'locked'}) || (defined($metric->{'locked'}) && $metric->{'locked'} eq 0)){
				undef($sth1);
			   	if ($just_insert || ($metric->{'unit_name'} ne $data[2])){
			   		my $sth1 = $con_ods->prepare("UPDATE `metrics` SET `unit_name` = '".$data[2]."', `warn` = '".$data[3]."', `crit` = '".$data[4]."', `min` = '".$data[5]."', `max` = '".$data[6]."' WHERE `metric_id` = '".$metric->{'metric_id'}."'");
			    	writeLogFile("Error:" . $sth1->errstr . "\n") if (!$sth1->execute);
			    	undef($sth1);
			   	}
				if ($data[3] ne "" || $data[4] ne "" || $data[5] ne "" || $data[6] ne ""){
					my $str = "`warn` = ".$data[3]." " if ($data[3]);
				   	if ($data[4]){
				   		$str .= ", " if ($str ne "");
					   	$str .= "`crit` = ".$data[4]." ";
				   	}
				   	if ($data[5]){
				   		$str .= ", " if ($str ne "");
						$str .= "`min` = ".$data[5]." ";
				   	}
				   	if ($data[6]){
				   		$str .= ", " if ($str ne "");
						$str .= "`max` = ".$data[6]." ";
				   	}
				   	if (defined($metric->{'metric_id'}) && $metric->{'metric_id'}){
				   		if ($str){
						   	$sth1 = $con_ods->prepare("UPDATE metrics SET $str WHERE `metric_id` = '".$metric->{'metric_id'}."'");
							writeLogFile("Error:" . $sth1->errstr . "\n") if (!$sth1->execute);
						    undef($sth1);
						}
						undef($str);
					}
				}		   	
				# Check Storage Type
				# O -> BD Mysql & 1 -> RRDTool
				$begin = $_[3] - 200;
				if (defined($data[1])){
					updateMysqlDBforHiddenSVC($metric->{'metric_id'}, $_[3], $data[1], $status{$_[2]});
					updateRrdDBforHiddenSVC($configuration->{'RRDdatabase_path'}, $metric->{'metric_id'}, $_[3], $data[1], $begin, $configuration->{'len_storage_rrd'}, $metric->{'metric_name'}) if (defined($_[6]) && $_[6] ne 2);
					$generalcounter++;
				}
			}
		    $just_insert = 0;
	    }		
	    undef(@data);
		undef(%metric);
	}
    undef($begin);
    undef($configuration);
    return $generalcounter;
}

1;