################################################################################
# Copyright 2005-2011 MERETHIS
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

sub removeBackSpace($){
    $_[0] =~ s/\n//g;
    $_[0] =~ s/\ \'/\'/g;
    return $_[0];
}

sub putSpecialCharInMetric($){
    $_[0] =~ s/\-/\./g;
    $_[0] =~ s/\-/\,/g;
    $_[0] =~ s/\-/\:/g;
    $_[0] =~ s/\-/\ /g;
    return $_[0];
}

sub removeSpecialCharInMetric($){
    $_[0] =~ s/\./\-/g;
    $_[0] =~ s/\,/\-/g;
    $_[0] =~ s/\:/\-/g;
    $_[0] =~ s/\ /\-/g;
    return $_[0];
}

sub insertMetrics($$$$$$$){
    my ($index_id, $name, $unit, $warn, $crit, $min, $max) = @_;

    CreateConnexionForCentstorage();
    
    my $sth2 = $con_ods->prepare("INSERT INTO `metrics` (`index_id`, `metric_name`, `unit_name`, `warn`, `crit`, `min`, `max`) VALUES ('".$index_id."', '".$name."', '".$unit."', '".$warn."', '".$crit."', '".$min."', '".$max."')");
    if (!$sth2->execute()) { 
    	writeLogFile("Error:" . $sth2->errstr . "\n");
    }
    undef($sth2);
}

sub updateMetricInformation($$$$$){
    my ($id, $warn, $crit, $min, $max) = @_;

    if ($warn ne "" || $crit ne "" || $min ne "" || $max ne "") {
		my $str = "";
		$str .= "`warn` = '".$warn."'" if ($warn ne "");
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
		if (defined($id)) {
		    $sth1 = $con_ods->prepare("UPDATE `metrics` SET $str WHERE `metric_id` = '".$id."'");
		    if (!$sth1->execute()) {
				writeLogFile("Error:" . $sth1->errstr . "\n");
		    }
		    undef($sth1);
		} else {
		    writeLogFile("Metric id not found. Check database informations.\n");
		}
		undef($str);
    }		   	
}

#
# perfdata index status time type counter rebuild
#
sub identify_metric($$$$$$$$){ 
    my (@data, $begin, $just_insert, $generalcounter);
    my $configuration = $_[7];
    $generalcounter = $_[5];
    $just_insert = 0;

    CheckMySQLConnexion();
    
    # Cut perfdata    	
    my $metric = removeBackSpace($_[0]);

    //while ($metric =~ m/\'?([a-zA-Z0-9\_\-\/\.\:\ \\\%]+)\'?\=([0-9\.\,\-]+)([a-zA-Z0-9\_\-\/\\\%]*)[\;]*([0-9\.\,\-]*)[\;]*([0-9\.\,\-]*)[\;]*([0-9\.\,\-]*)[\;]*([0-9\.\,\-]*)\s?/g) {
	while ($metric =~ m/\'?([a-zA-Z0-9\_\-\/\.\:\ ]+)\s*\'?\=([0-9\.\,\-]+)([a-zA-Z0-9\_\-\/\\\%]*)[\;]*([0-9\.\,\-]*)[\;]*([0-9\.\,\-]*)[\;]*([0-9\.\,\-]*)[\;]*([0-9\.\,\-]*)\s?/g) {
	
		my $metric_name = $1;
		$metric_name =~ s/^\s+//;
		$metric_name =~ s/\s+$//;
		my $unit = "";
		my $value = $2;
		my $warn = "";
		my $critical = "";
		my $min = "";
		my $max = "";
	
		if (defined($3)){$unit = $3;} else {$unit = "";}
		if (defined($4)){$warn = $4;} else {$warn = "";}
		if (defined($5)){$critical = $5;} else {$critical = "";}
		if (defined($6)){$min = $6;} else {$min = "";}
		if (defined($7)){$max = $7;} else {$max = "";}
	
		my $cpt = 1;
		my $x = 0;
		while (defined($$cpt)) {
			writeLogFile "$x: $$cpt" if ($debug);
		    $data[$x] = $$cpt;
		    $cpt++;
		    $x++;
		}
	
		# metric, value, unit, warn, critical, min, max
		@data = ($metric_name, $value, $unit, $warn, $critical, $min, $max); 
	
		if (defined($metric_name) && $metric_name && defined($value)) {
		    # Check if metric is known...
		    $data[0] = removeSpecialCharInMetric($data[0]);
	
		    my $sth1 = $con_ods->prepare("SELECT * FROM `metrics` WHERE `index_id` = '".$_[1]."' AND `metric_name` = '".$data[0]."'");
		    if (!$sth1->execute()) {
				writeLogFile("Error:" . $sth1->errstr . "\n");
		    } else {
				if ($sth1->rows() eq 0) {
				    $just_insert = 1;  
				    insertMetrics($_[1], $data[0], $data[2], $data[3], $data[4], $data[5], $data[6]);
		
				    # Get ID
				    $sth1 = $con_ods->prepare("SELECT * FROM `metrics` WHERE `index_id` = '".$_[1]."' AND `metric_name` = '".$data[0]."'");
				    if (!$sth1->execute()) {
						writeLogFile("Error:" . $sth1->errstr . "\n");
				    }
				}
				my $metric = $sth1->fetchrow_hashref();
				$sth1->finish();
	
				# Update metric attributs
				if ($just_insert || (defined($data[2]) && defined($metric->{'unit_name'}) && $metric->{'unit_name'} ne $data[2])) {
				    my $sth1 = $con_ods->prepare("UPDATE `metrics` SET `unit_name` = '".$data[2]."', `warn` = '".$data[3]."', `crit` = '".$data[4]."', `min` = '".$data[5]."', `max` = '".$data[6]."' WHERE `metric_id` = '".$metric->{'metric_id'}."'");
				    if (!$sth1->execute()) {
						writeLogFile("Error:" . $sth1->errstr . "\n");
				    }
				    undef($sth1);
				}
		
				updateMetricInformation($metric->{'metric_id'}, $data[3], $data[4], $data[5], $data[6]);
				
				# Check Storage Type
				# O -> BD Mysql & 1 -> RRDTool
				if (defined($data[1])) {
				    # manage 'data_source_type' default value : NULL = '0'
		            $metric->{'data_source_type'} = defined($metric->{'data_source_type'}) ? $metric->{'data_source_type'} : 0;
				    if (defined($_[4]) && $_[4] eq 0 && $_[6] eq 0){
						updateRRDDB($configuration->{'RRDdatabase_path'}, $metric->{'metric_id'}, $_[3], $data[1], ($_[3] - 200), $configuration->{'len_storage_rrd'}, $metric->{'metric_name'}, $metric->{'data_source_type'});
				    } elsif (defined($_[4]) && $_[4] eq 2) { 
						updateRRDDB($configuration->{'RRDdatabase_path'}, $metric->{'metric_id'}, $_[3], $data[1], ($_[3] - 200), $configuration->{'len_storage_rrd'}, $metric->{'metric_name'}, $metric->{'data_source_type'});
						updateMysqlDB($metric->{'metric_id'}, $_[3], $data[1], $status{$_[2]});
				    }
				}	    
		    }
		    $just_insert = 0;
		}
		undef($sth1);
		undef(@data);
    }
    undef($metric_name);
    undef($value);
    undef($unit);
    undef($min);
    undef($max);
    undef($warn);
    undef($critical);
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

sub identify_hidden_metric($$$$$$$$){ # perfdata index status time type counter rebuild
    my (@data, $begin, $just_insert, $generalcounter);
    my $configuration = $_[7];

    CheckMySQLConnexion();

    $generalcounter = $_[5];
    return $generalcounter if ($_[1] eq 0);
    $just_insert = 0;   				

    foreach my $tab (split(' ', $_[0])){	
    	# Cut perfdata    	
	if ($tab =~ /([a-zA-Z0-9\_\-\/\\]+)\=([0-9\.\,]+)([a-zA-Z0-9\_\-\/\\\%]*)[\;]*([0-9\.\,]*)[\;]*([0-9\.\,]*)[\;]*([0-9\.\,]*)[\;]*([0-9\.\,]*)/){
	    if (!defined($3)){$3 = "";}			
	    if (!defined($4)){$4 = "";}			
	    if (!defined($5)){$5 = "";}	
	    @data = ($1, $2, $3, $4, $5); # metric, value, unit, warn, critical
	}
	if ($1 && defined($2)) {			
	    # Check if metric is known...
	    #$data[0] =~ s/\//#S#/g;
	    #$data[0] =~ s/\\/#BS#/g;
	    #$data[0] =~ s/\%/#P#/g;
	    my $sth1 = $con_ods->prepare("SELECT * FROM `metrics` WHERE `index_id` = '".$_[1]."' AND `metric_name` = '".$data[0]."'");
	    if (!$sth1->execute) {writeLogFile("Error:" . $sth1->errstr . "\n");}

	    if ($sth1->rows() eq 0) {
		$just_insert = 1;   				
		undef($sth1);

		# Si pas connue -> insert
		my $sth2 = $con_ods->prepare("INSERT INTO `metrics` (`index_id`, `metric_name`, `unit_name`) VALUES ('".$_[1]."', '".$data[0]."', '".$data[2]."')");
		if (!$sth2->execute){
		    writeLogFile("Error:" . $sth2->errstr . "\n");
		}
		undef($sth2);
		# Get ID
		$sth1 = $con_ods->prepare("SELECT * FROM `metrics` WHERE `index_id` = '".$_[1]."' AND `metric_name` = '".$data[0]."'");
		if (!$sth1->execute) {
		    writeLogFile("Error:" . $sth1->errstr . "\n");
		}
	    }
	    my $metric = $sth1->fetchrow_hashref();
	    undef($sth1);

	    if ($just_insert || (defined($data[2]) && defined($metric->{'unit_name'}) && $metric->{'unit_name'} ne $data[2])) {
		my $sth1 = $con_ods->prepare("UPDATE `metrics` SET `unit_name` = '".$data[2]."' WHERE `metric_id` = '".$metric->{'metric_id'}."'");
		if (!$sth1->execute){
		    writeLogFile("Error:" . $sth1->errstr . "\n");
		}
		undef($sth1);
	    }

	    $begin = $_[3] - 200;

	    if (defined($data[1]) && defined($_[4])){
		if ($_[6] eq 0){
		    # no rebuild running
                    # manage 'data_source_type' default value : NULL = '0'
                    $metric->{'data_source_type'} = defined($metric->{'data_source_type'}) ? $metric->{'data_source_type'} : 0;
		    updateRRDDBforHiddenSVC($configuration->{'RRDdatabase_path'}, $metric->{'metric_id'}, $_[3], $data[1], $begin, $configuration->{'len_storage_rrd'}, $metric->{'metric_name'}, $metric->{'data_source_type'});
		}
		# Storage Type
		# O -> RRDTool only,  2 -> RRDTool + MySQL
		if ($_[4] eq 2) { 
		    updateMysqlDBforHiddenSVC($metric->{'metric_id'}, $_[3], $data[1], $status{$_[2]});	
		}
		$generalcounter++;
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
