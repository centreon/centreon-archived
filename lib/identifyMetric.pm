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

# identifier la metric
# meed arguments 
# - perfdata
# - index_id
# - status
# - timestamp
# - storage_type

sub removeBackSpace($){
	$_[0] =~ s/\n//g;
	return $_[0];
}

sub getGeneralConfig(){
	my $con_ods = CreateConnexionForCentstorage();
	# Get conf Data
	my $sth1 = $con_ods->prepare("SELECT * FROM `config`");
	writeLogFile("Error:" . $sth1->errstr . "\n") if (!$sth1->execute);
	my $configuration = $sth1->fetchrow_hashref();
	$sth1->finish();
	$con_ods->disconnect();
	return $configuration;
}

sub putSpecialCharInMetric($){
	$_[0] =~ s/#S#/\//g;
	$_[0] =~ s/#BS#/\\/g;
	$_[0] =~ s/\-/\./g;
	$_[0] =~ s/\-/\,/g;
	$_[0] =~ s/\-/\:/g;
	$_[0] =~ s/\-/\ /g;
	return $_[0];
}

sub removeSpecialCharInMetric($){
	$_[0] =~ s/\//#S#/g;
	$_[0] =~ s/\\/#BS#/g;
	$_[0] =~ s/\./\-/g;
	$_[0] =~ s/\,/\-/g;
	$_[0] =~ s/\:/\-/g;
	$_[0] =~ s/\ /\-/g;
	return $_[0];
}

sub insertMetrics($$$$$$$){
	my ($index_id, $name, $unit, $warn, $crit, $min, $max) = @_;

	my $con_ods = CreateConnexionForCentstorage();
	
	print ("INSERT INTO `metrics` (`index_id`, `metric_name`, `unit_name`, `warn`, `crit`, `min`, `max`) VALUES ('".$index_id."', '".$name."', '".$unit."', '".$warn."', '".$crit."', '".$min."', '".$max."')\n");
	
	my $sth2 = $con_ods->prepare("INSERT INTO `metrics` (`index_id`, `metric_name`, `unit_name`, `warn`, `crit`, `min`, `max`) VALUES ('".$index_id."', '".$name."', '".$unit."', '".$warn."', '".$crit."', '".$min."', '".$max."')");
    writeLogFile("Error:" . $sth2->errstr . "\n") if (!$sth2->execute);
    undef($sth2);
    $con_ods->disconnect();
}

sub updateMetricInformation($$$$$){
	my ($id, $warn, $crit, $min, $max) = @_;
	if ($warn ne "" || $crit ne "" || $min ne "" || $max ne ""){
		my $str = "";
		$str .= "`warn` = '".$warn."'" if ($warn);
	  	if ($crit ne ""){
	   		$str .= ", " if ($str ne "");
		   	$str .= "`crit` = '".$crit."' ";
	   	}
	   	if ($min ne ""){
	   		$str .= ", " if ($str ne "");
			$str .= "`min` = '".$min."' ";
	   	}
	   	if ($max ne ""){
	   		$str .= ", " if ($str ne "");
			$str .= "`max` = '".$max."' ";
	   	}
	   	$sth1 = $con_ods->prepare("UPDATE `metrics` SET $str WHERE `metric_id` = '".$id."'");
		writeLogFile("Error:" . $sth1->errstr . "\n") if (!$sth1->execute);
	    undef($sth1);
		undef($str);
	}		   	
}

sub identify_metric($$$$$$$){ # perfdata index status time type counter rebuild
	my (@data, $begin, $just_insert, $generalcounter);
	$generalcounter = $_[5];
	$just_insert = 0;
	
	my $con_ods = CreateConnexionForCentstorage();
	my $con_oreon = CreateConnexionForOreon();
	
	# Get All Configuration values   				
	$configuration = getGeneralConfig();
	
	# Cut perfdata    	
	my $metric = removeBackSpace($_[0]);
	while ($metric =~ m/\'?([a-zA-Z0-9\_\-\/\.\:\ ]+)\'?\=([0-9\.\,\-]+)([a-zA-Z0-9\_\-\/\\\%]*)[\;]*([0-9\.\,\-]*)[\;]*([0-9\.\,\-]*)[\;]*([0-9\.\,\-]*)[\;]*([0-9\.\,\-]*)\s?/g){
	    if (!defined($3)){$3 = "";}
	    if (!defined($4)){$4 = "";}
	    if (!defined($5)){$5 = "";}
	    if (!defined($6)){$6 = "";}
	    if (!defined($7)){$7 = "";}
	    my $cpt = 1;
	    my $x = 0;
	    while (defined($$cpt)){
	    	$data[$x] = $$cpt;
	    	$cpt++;
	    	$x++;
	    }
	    @data = ($1, $2, $3, $4, $5, $6, $7); # metric, value, unit, warn, critical, min, max
		if ($1 && defined($2)){			
			# Check if metric is known...
			$data[0] = removeSpecialCharInMetric($data[0]);
			
			my $sth1 = $con_ods->prepare("SELECT * FROM `metrics` WHERE `index_id` = '".$_[1]."' AND `metric_name` = '".$data[0]."'");
			writeLogFile("Error:" . $sth1->errstr . "\n") if (!$sth1->execute);
			
			if ($sth1->rows() eq 0){
				$just_insert = 1;  
				insertMetrics($_[1], $data[0], $data[2], $data[3], $data[4], $data[5], $data[6]);
				
			    # Get ID
			   	$sth1 = $con_ods->prepare("SELECT * FROM `metrics` WHERE `index_id` = '".$_[1]."' AND `metric_name` = '".$data[0]."'");
				if (!$sth1->execute) {writeLogFile("Error:" . $sth1->errstr . "\n");}
			}
			my $metric = $sth1->fetchrow_hashref();
			$sth1->finish();
		   	# Update metric attributs
		   	if ($just_insert || ($metric->{'unit_name'} ne $data[2])){
		   		my $sth1 = $con_ods->prepare("UPDATE `metrics` SET `unit_name` = '".$data[2]."', `warn` = '".$data[3]."', `crit` = '".$data[4]."', `min` = '".$data[5]."', `max` = '".$data[6]."' WHERE `metric_id` = '".$metric->{'metric_id'}."'");
		    	writeLogFile("Error:" . $sth1->errstr . "\n") if (!$sth1->execute);
		    	undef($sth1);
		   	}
		   	updateMetricInformation($metric->{'metric_id'}, $data[3], $data[4], $data[5], $data[6]);
			# Check Storage Type
			# O -> BD Mysql & 1 -> RRDTool
			$begin = $_[3] - 200;
			if (defined($data[1])){
				if (defined($_[4]) && $_[4] eq 1 && $_[6] eq 0){
					updateRRDDB($configuration->{'RRDdatabase_path'}, $metric->{'metric_id'}, $_[3], $data[1], $begin, $configuration->{'len_storage_rrd'}, $metric->{'metric_name'});
				} elsif (defined($_[4]) && $_[4] eq 2) { 
					updateRRDDB($configuration->{'RRDdatabase_path'}, $metric->{'metric_id'}, $_[3], $data[1], $begin, $configuration->{'len_storage_rrd'}, $metric->{'metric_name'});
					updateMysqlDB($metric->{'metric_id'}, $_[3], $data[1], $status{$_[2]});
				}
			}
			$just_insert = 0;
	    }
	    undef(@data);
	}
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

sub identify_hidden_metric($$$$$$$){ # perfdata index status time type counter rebuild
	my (@data, $begin, $just_insert, $generalcounter);
	
	CheckMySQLConnexion();
	
	$generalcounter = $_[5];
	return $generalcounter if ($_[1] eq 0);
    $just_insert = 0;   				
	# Get conf Data
	my $sth1 = $con_ods->prepare("SELECT * FROM config");
	if (!$sth1->execute) {writeLogFile("Error:" . $sth1->errstr . "\n");}
	my $configuration = $sth1->fetchrow_hashref();
	undef($sth1);
	
    foreach my $tab (split(' ', $_[0])){	
    	# Cut perfdata    	
		if ($tab =~ /([a-zA-Z0-9\_\-\/\\]+)\=([0-9\.\,]+)([a-zA-Z0-9\_\-\/\\\%]*)[\;]*([0-9\.\,]*)[\;]*([0-9\.\,]*)[\;]*([0-9\.\,]*)[\;]*([0-9\.\,]*)/){
		    if (!defined($3)){$3 = "";}			
		    if (!defined($4)){$4 = "";}			
		    if (!defined($5)){$5 = "";}	
		    @data = ($1, $2, $3, $4, $5); # metric, value, unit, warn, critical
		}
		if ($1 && defined($2)){			
			# Check if metric is known...
			$data[0] =~ s/\//#S#/g;
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
					updateRRDDBforHiddenSVC($configuration->{'RRDdatabase_path'}, $metric->{'metric_id'}, $_[3], $data[1], $begin, $configuration->{'len_storage_rrd'}, $metric->{'metric_name'});$generalcounter++;
					$generalcounter++;
				} elsif (defined($_[4]) && $_[4] eq 0) {   # Insert Data In Mysql 
					updateMysqlDBforHiddenSVC($metric->{'metric_id'}, $_[3], $data[1], $status{$_[2]});
					$generalcounter++;
				} else {
					updateRRDDBforHiddenSVC($configuration->{'RRDdatabase_path'}, $metric->{'metric_id'}, $_[3], $data[1], $begin, $configuration->{'len_storage_rrd'}, $metric->{'metric_name'});					
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