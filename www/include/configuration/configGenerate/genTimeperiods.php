<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */
	
	if (!isset($oreon))
		exit();

	if (!is_dir($nagiosCFGPath.$tab['id']."/"))
		mkdir($nagiosCFGPath.$tab['id']."/");
	
	$handle = create_file($nagiosCFGPath.$tab['id']."/timeperiods.cfg", $oreon->user->get_name());
	
	/*
	 * Generate Standart Timeperiod
	 */
	$cacheTemplate = array();
	$cacheQuery = "SELECT tp_id, tp_name FROM timeperiod ORDER BY tp_name";
	$res = $pearDB->query($cacheQuery);
	while ($row = $res->fetchRow()) {
	    $cacheTemplate[$row['tp_id']] = $row['tp_name']; 
	}
	
	$timeperiods = array();
	$i = 1;
	$str = NULL;
	$DBRESULT = $pearDB->query("SELECT * FROM `timeperiod` ORDER BY `tp_name`");
	while ($timePeriod = $DBRESULT->fetchRow()) {
		$ret["comment"] ? ($str .= "# '" . $timePeriod["tp_name"] . "' timeperiod definition " . $i . "\n") : NULL;
		$str .= "define timeperiod{\n";
		if ($timePeriod["tp_name"]) {
			$str .= print_line("name", $timePeriod["tp_name"]);
		    $str .= print_line("timeperiod_name", $timePeriod["tp_name"]);
		}
		if ($timePeriod["tp_alias"]) { 
			$str .= print_line("alias", $timePeriod["tp_alias"]);
		}
		if ($timePeriod["tp_sunday"]) { 
			$str .= print_line("sunday", $timePeriod["tp_sunday"]);
		}
		if ($timePeriod["tp_monday"]) { 
			$str .= print_line("monday", $timePeriod["tp_monday"]);
		}
		if ($timePeriod["tp_tuesday"]) { 
			$str .= print_line("tuesday", $timePeriod["tp_tuesday"]);
		}
		if ($timePeriod["tp_wednesday"]) { 
			$str .= print_line("wednesday", $timePeriod["tp_wednesday"]);
		}
		if ($timePeriod["tp_thursday"]) { 
			$str .= print_line("thursday", $timePeriod["tp_thursday"]);
		}
		if ($timePeriod["tp_friday"]) { 
			$str .= print_line("friday", $timePeriod["tp_friday"]);
		}
		if ($timePeriod["tp_saturday"]) { 
			$str .= print_line("saturday", $timePeriod["tp_saturday"]);
		}
		
		/*
		 *  For Exceptions
		 *  Exceptions don't work if GMT is used
		 */
		if ($oreon->CentreonGMT->used() == 0) {
    		$query = "SELECT days, timerange ". 
    				"FROM timeperiod_exceptions ex, timeperiod tp ". 
    				"WHERE ex.timeperiod_id = tp.tp_id ". 
    				"AND tp.tp_name = '".$timePeriod['tp_name']."'";
    		$res = $pearDB->query($query);
    		while ($row = $res->fetchRow()) {
    		    $str .= print_line($row['days'], $row['timerange']);
    		}
		}
		
		/*
		 *  For Inclusions
		 */
	    $query = "SELECT timeperiod_include_id ". 
				"FROM timeperiod_include_relations tir, timeperiod tp ". 
				"WHERE tir.timeperiod_id = tp.tp_id ". 
				"AND tp.tp_name = '".$timePeriod['tp_name']."'";
		$res = $pearDB->query($query);		
		$tmpStr = "";
		while ($row = $res->fetchRow()) {
		    if (isset($cacheTemplate[$row['timeperiod_include_id']])) {
		        if ($tmpStr != "") {
		            $tmpStr .= ",";
		        }
		        $tmpStr .= $cacheTemplate[$row['timeperiod_include_id']];
		    }
		}
		if ($tmpStr != "") {
		    $str .= print_line('use', $tmpStr);
		}
		
		/*
		 *  For Exclusions
		 */
	    $query = "SELECT timeperiod_exclude_id ". 
				"FROM timeperiod_exclude_relations ter, timeperiod tp ". 
				"WHERE ter.timeperiod_id = tp.tp_id ". 
				"AND tp.tp_name = '".$timePeriod['tp_name']."'";
		$res = $pearDB->query($query);
		$tmpStr = "";
		while ($row = $res->fetchRow()) {
		    if (isset($cacheTemplate[$row['timeperiod_exclude_id']])) {
		        if ($tmpStr != "") {
		            $tmpStr .= ",";
		        }
		        $tmpStr .= $cacheTemplate[$row['timeperiod_exclude_id']];		        
		    }
		}
		if ($tmpStr != "") {
		    $str .= print_line('exclude', $tmpStr);
		}
		
		$str .= "}\n\n";
		$i++;
		$timeperiods[$timePeriod["tp_id"]] = $timePeriod["tp_name"];
		unset($timePeriod);
	}
	
	if ($oreon->CentreonGMT->used() == 1) {
		$GMTList = $oreon->CentreonGMT->listGTM;
		foreach ($GMTList as $gmt => $value) {
			$DBRESULT = $pearDB->query("SELECT * FROM `timeperiod` ORDER BY `tp_name`");
			while ($timePeriod = $DBRESULT->fetchRow())	{
				$PeriodBefore 	= array("monday" => "", "tuesday" => "", "wednesday" => "", "thursday" => "", "friday" => "", "saturday" => "", "sunday" => "");
				$Period 		= array("monday" => "", "tuesday" => "", "wednesday" => "", "thursday" => "", "friday" => "", "saturday" => "", "sunday" => "");
				$PeriodAfter 	= array("monday" => "", "tuesday" => "", "wednesday" => "", "thursday" => "", "friday" => "", "saturday" => "", "sunday" => "");
				
				$ret["comment"] ? ($str .= "# '" . $timePeriod["tp_name"]."_GMT".$gmt . "' timeperiod definition " . $i . "\n") : NULL;
				$str .= "define timeperiod{\n";
				if ($timePeriod["tp_name"]) {
					$str .= print_line("timeperiod_name", $timePeriod["tp_name"]."_GMT".$gmt);
					$str .= print_line("name", $timePeriod["tp_name"]."_GMT".$gmt);
				}
	
				if ($timePeriod["tp_alias"]) 
					$str .= print_line("alias", $timePeriod["tp_alias"]);
	
				if ($timePeriod["tp_sunday"])
					ComputeGMTTime("sunday", "saturday", "monday", $gmt, $timePeriod["tp_sunday"]);
				
				if ($timePeriod["tp_monday"]) 
					ComputeGMTTime("monday", "sunday", "tuesday", $gmt, $timePeriod["tp_monday"]);
				
				if ($timePeriod["tp_tuesday"]) 
					ComputeGMTTime("tuesday", "monday", "wednesday", $gmt, $timePeriod["tp_tuesday"]);
				
				if ($timePeriod["tp_wednesday"])
					ComputeGMTTime("wednesday", "tuesday", "thursday", $gmt, $timePeriod["tp_wednesday"]);
				
				if ($timePeriod["tp_thursday"]) 
					ComputeGMTTime("thursday", "wednesday", "friday", $gmt, $timePeriod["tp_thursday"]);
				
				if ($timePeriod["tp_friday"]) 
					ComputeGMTTime("friday", "thursday", "saturday", $gmt, $timePeriod["tp_friday"]);
				
				if ($timePeriod["tp_saturday"]) 
					ComputeGMTTime("saturday", "friday", "sunday", $gmt, $timePeriod["tp_saturday"]);
									
    			/*
        		 *  For Inclusions
        		 */
        	    $query = "SELECT timeperiod_include_id ". 
        				"FROM timeperiod_include_relations tir, timeperiod tp ". 
        				"WHERE tir.timeperiod_id = tp.tp_id ". 
        				"AND tp.tp_name = '".$timePeriod['tp_name']."'";
        		$res = $pearDB->query($query);
        		$tmpStr = "";
        		while ($row = $res->fetchRow()) {
        		    if (isset($cacheTemplate[$row['timeperiod_include_id']])) {
        		        if ($tmpStr != "") {
        		            $tmpStr .= ",";
        		        }
        		        $tmpStr .= $cacheTemplate[$row['timeperiod_include_id']]."_GMT".$gmt;		        
        		    }
        		}
        		if ($tmpStr != "") {
        		    $str .= print_line('use', $tmpStr);
        		}
        		
        		/*
        		 *  For Exclusions
        		 */
        	    $query = "SELECT timeperiod_exclude_id ". 
        				"FROM timeperiod_exclude_relations ter, timeperiod tp ". 
        				"WHERE ter.timeperiod_id = tp.tp_id ". 
        				"AND tp.tp_name = '".$timePeriod['tp_name']."'";
        		$res = $pearDB->query($query);
        		$tmpStr = "";
        		while ($row = $res->fetchRow()) {
        		    if (isset($cacheTemplate[$row['timeperiod_exclude_id']])) {
        		        if ($tmpStr != "") {
        		            $tmpStr .= ",";
        		        }
        		        $tmpStr .= $cacheTemplate[$row['timeperiod_exclude_id']]."_GMT".$gmt;		        
        		    }
        		}
        		if ($tmpStr != "") {
        		    $str .= print_line('exclude', $tmpStr);
        		}
					
				$i++;
				$timeperiods[$timePeriod["tp_id"]] = $timePeriod["tp_name"];
				unset($timePeriod);
				foreach ($Period as $day => $value){
					if (strlen($PeriodAfter[$day].$Period[$day].$PeriodBefore[$day]))
					  $str .= print_line($day, $PeriodAfter[$day].($Period[$day] != "" && $PeriodAfter[$day] != "" ? "," : "").$Period[$day].($PeriodBefore[$day] != "" && ($PeriodAfter[$day] != "" || $Period[$day] != "") ? "," : "").$PeriodBefore[$day]);
				}
				$str .= "}\n\n";
			}
			
		}
	}
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES, "UTF-8"), $nagiosCFGPath.$tab['id']."/timeperiods.cfg");
	fclose($handle);
	
	setFileMod($nagiosCFGPath.$tab['id']."/timeperiods.cfg");
	
	$DBRESULT->free();
	unset($str);
	unset($i);
?>