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

# identifier la metric
# meed arguments 
# - perfdata
# - index_id
# - status
# - timestamp
# - storage_type

sub identify_metric($$$$$$){ # perfdata index status time type
    my (@data, $begin, $just_insert, $generalcounter);
	$generalcounter = $_[5];
	$just_insert = 0;   				
	# Get conf Data
	my $sth1 = $con_ods->prepare("SELECT * FROM config");
	if (!$sth1->execute) {writeLogFile("Error:" . $sth1->errstr . "\n");}
	my $configuration = $sth1->fetchrow_hashref();
	undef($sth1);
	
    foreach my $tab (split(' ', $_[0])){	
    	# Cut perfdata    	
		if ($tab =~ /([a-zA-Z0-9\_\-\/\:]+)\=([0-9\.\,\-]+)([a-zA-Z0-9\_\-\/\\\%]*)[\;]*([0-9\.\,\-]*)[\;]*([0-9\.\,\-]*)[\;]*([0-9\.\,\-]*)[\;]*([0-9\.\,\-]*)/){
		    if (!defined($3)){$3 = "";}			
		    if (!defined($4)){$4 = "";}			
		    if (!defined($5)){$5 = "";}			
		    @data = ($1, $2, $3, $4, $5); # metric, value, unit, warn, critical
		}
		if ($1 && defined($2)){			
			# Check if metric is known...
			$data[0] =~ s/\:/\ /g;
			$data[0] =~ s/\//slash/g;
			my $sth1 = $con_ods->prepare("SELECT * FROM `metrics` WHERE `index_id` = '".$_[1]."' AND `metric_name` = '".$data[0]."'");
			if (!$sth1->execute) {writeLogFile("Error:" . $sth1->errstr . "\n");}
			if ($sth1->rows() eq 0){
				$just_insert = 1;   				
				undef($sth1);
				# Si pas connue -> insert
			   	my $sth2 = $con_ods->prepare("INSERT INTO `metrics` (`index_id`, `metric_name`, `unit_name`) VALUES ('".$_[1]."', '".$data[0]."', '".$data[2]."')");
			    if (!$sth2->execute){writeLogFile("Error:" . $sth2->errstr . "\n");}
			    undef($sth2);
			    # Get ID
			   	$sth1 = $con_ods->prepare("SELECT * FROM `metrics` WHERE `index_id` = '".$_[1]."' AND `metric_name` = '".$data[0]."'");
				if (!$sth1->execute) {writeLogFile("Error:" . $sth1->errstr . "\n");}
			}
			my $metric = $sth1->fetchrow_hashref();
			undef($sth1);
			
		   	if ($just_insert || ($metric->{'unit_name'} ne $data[2])){
		   		my $sth1 = $con_ods->prepare("UPDATE `metrics` SET `unit_name` = '".$data[2]."' WHERE `metric_id` = '".$metric->{'metric_id'}."'");
		    	if (!$sth1->execute){writeLogFile("Error:" . $sth1->errstr . "\n");}
		    	undef($sth1);
		   	}
			
			# Check Storage Type
			# O -> BD Mysql & 1 -> RRDTool
			$begin = $_[3] - 200;
			if (defined($data[1])){
				if (defined($_[4]) && $_[4] eq 1){
					updateRrdDB($configuration->{'RRDdatabase_path'}, $metric->{'metric_id'}, $_[3], $data[1], $begin, $configuration->{'len_storage_rrd'}, $metric->{'metric_name'});
					$generalcounter++;
				} elsif (defined($_[4]) && $_[4] eq 0) {   # Insert Data In Mysql 
					updateMysqlDB($metric->{'metric_id'}, $_[3], $data[1], $status{$_[2]});
					$generalcounter++;
				} else {
					updateRrdDB($configuration->{'RRDdatabase_path'}, $metric->{'metric_id'}, $_[3], $data[1], $begin, $configuration->{'len_storage_rrd'}, $metric->{'metric_name'});					updateMysqlDB($metric->{'metric_id'}, $_[3], $data[1], $status{$_[2]});	
					updateMysqlDB($metric->{'metric_id'}, $_[3], $data[1], $status{$_[2]});
					$generalcounter++;
				}
			}
			$just_insert = 0;
		}
    }
    undef($tab);
    undef(@data);
    undef($begin);
    return $generalcounter;
}

# identifier la metric
# meed arguments 
# - perfdata
# - index_id
# - status
# - timestamp
# - storage_type

sub identify_hidden_metric($$$$$$){ # perfdata index status time type
	my (@data, $begin, $just_insert, $generalcounter);
	$generalcounter = $_[5];
	$just_insert = 0;   				
	# Get conf Data
	my $sth1 = $con_ods->prepare("SELECT * FROM config");
	if (!$sth1->execute) {writeLogFile("Error:" . $sth1->errstr . "\n");}
	my $configuration = $sth1->fetchrow_hashref();
	undef($sth1);
	
    foreach my $tab (split(' ', $_[0])){	
    	# Cut perfdata    	
		if ($tab =~ /([a-zA-Z0-9\_\-]+)\=([0-9\.\,]+)([a-zA-Z0-9\_\-\/\\\%]*)[\;]*([0-9\.\,]*)[\;]*([0-9\.\,]*)[\;]*([0-9\.\,]*)[\;]*([0-9\.\,]*)/){
		    if (!defined($3)){$3 = "";}			
		    if (!defined($4)){$4 = "";}			
		    if (!defined($5)){$5 = "";}			
		    @data = ($1, $2, $3, $4, $5); # metric, value, unit, warn, critical
		}
		if ($1 && defined($2)){			
			# Check if metric is known...
			my $sth1 = $con_ods->prepare("SELECT * FROM `metrics` WHERE `index_id` = '".$_[1]."' AND `metric_name` = '".$data[0]."'");
			if (!$sth1->execute) {writeLogFile("Error:" . $sth1->errstr . "\n");}
			
			if ($sth1->rows() eq 0){
				$just_insert = 1;   				
				undef($sth1);
				# Si pas connue -> insert
			   	my $sth2 = $con_ods->prepare("INSERT INTO `metrics` (`index_id`, `metric_name`, `unit_name`) VALUES ('".$_[1]."', '".$data[0]."', '".$data[2]."')");
			    if (!$sth2->execute){writeLogFile("Error:" . $sth2->errstr . "\n");}
			    undef($sth2);
			    # Get ID
			   	$sth1 = $con_ods->prepare("SELECT * FROM `metrics` WHERE `index_id` = '".$_[1]."' AND `metric_name` = '".$data[0]."'");
				if (!$sth1->execute) {writeLogFile("Error:" . $sth1->errstr . "\n");}
			}
			my $metric = $sth1->fetchrow_hashref();
			undef($sth1);
			
		   	if ($just_insert || ($metric->{'unit_name'} ne $data[2])){
		   		my $sth1 = $con_ods->prepare("UPDATE `metrics` SET `unit_name` = '".$data[2]."' WHERE `metric_id` = '".$metric->{'metric_id'}."'");
		    	if (!$sth1->execute){writeLogFile("Error:" . $sth1->errstr . "\n");}
		    	undef($sth1);
		   	}
			
			# Check Storage Type
			# O -> BD Mysql & 1 -> RRDTool
			$begin = $_[3] - 200;
			if (defined($data[1])){
				if (defined($_[4]) && $_[4] eq 1){
					updateRrdDBforHiddenSVC($configuration->{'RRDdatabase_path'}, $metric->{'metric_id'}, $_[3], $data[1], $begin, $configuration->{'len_storage_rrd'}, $metric->{'metric_name'});$generalcounter++;
					$generalcounter++;
				} elsif (defined($_[4]) && $_[4] eq 0) {   # Insert Data In Mysql 
					updateMysqlDBforHiddenSVC($metric->{'metric_id'}, $_[3], $data[1], $status{$_[2]});
					$generalcounter++;
				} else {
					updateRrdDBforHiddenSVC($configuration->{'RRDdatabase_path'}, $metric->{'metric_id'}, $_[3], $data[1], $begin, $configuration->{'len_storage_rrd'}, $metric->{'metric_name'});					
					updateMysqlDBforHiddenSVC($metric->{'metric_id'}, $_[3], $data[1], $status{$_[2]});	
					$generalcounter++;
				}
			}
			$just_insert = 0;
		}
    }
    undef($tab);
    undef(@data);
    undef($begin);
    return $generalcounter;
}

1;