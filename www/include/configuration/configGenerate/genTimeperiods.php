<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 */
	
	if (!isset($oreon))
		exit();

	if (!is_dir($nagiosCFGPath.$tab['id']."/"))
		mkdir($nagiosCFGPath.$tab['id']."/");
	
	$handle = create_file($nagiosCFGPath.$tab['id']."/timeperiods.cfg", $oreon->user->get_name());
	
	/*
	 * Generate Standart Timeperiod
	 */
	$DBRESULT =& $pearDB->query("SELECT * FROM `timeperiod` ORDER BY `tp_name`");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	$i = 1;
	$str = NULL;
	
	while ($timePeriod =& $DBRESULT->fetchRow())	{
		$ret["comment"] ? ($str .= "# '" . $timePeriod["tp_name"] . "' timeperiod definition " . $i . "\n") : NULL;
		$str .= "define timeperiod{\n";
		if ($timePeriod["tp_name"]) 
			$str .= print_line("timeperiod_name", $timePeriod["tp_name"]);
		if ($timePeriod["tp_alias"]) 
			$str .= print_line("alias", $timePeriod["tp_alias"]);
		if ($timePeriod["tp_sunday"]) 
			$str .= print_line("sunday", $timePeriod["tp_sunday"]);
		if ($timePeriod["tp_monday"]) 
			$str .= print_line("monday", $timePeriod["tp_monday"]);
		if ($timePeriod["tp_tuesday"]) 
			$str .= print_line("tuesday", $timePeriod["tp_tuesday"]);
		if ($timePeriod["tp_wednesday"]) 
			$str .= print_line("wednesday", $timePeriod["tp_wednesday"]);
		if ($timePeriod["tp_thursday"]) 
			$str .= print_line("thursday", $timePeriod["tp_thursday"]);
		if ($timePeriod["tp_friday"]) 
			$str .= print_line("friday", $timePeriod["tp_friday"]);
		if ($timePeriod["tp_saturday"]) 
			$str .= print_line("saturday", $timePeriod["tp_saturday"]);
		$str .= "}\n\n";
		$i++;
		unset($timePeriod);
	}	
	
	if ($oreon->CentreonGMT->used() == 1) {
		$GMTList = $oreon->CentreonGMT->listGTM;
		foreach ($GMTList as $gmt => $value) {
			$DBRESULT =& $pearDB->query("SELECT * FROM `timeperiod` ORDER BY `tp_name`");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			while ($timePeriod =& $DBRESULT->fetchRow())	{
				$PeriodBefore 	= array("monday" => "", "tuesday" => "", "wednesday" => "", "thursday" => "", "friday" => "", "saturday" => "", "sunday" => "");
				$Period 		= array("monday" => "", "tuesday" => "", "wednesday" => "", "thursday" => "", "friday" => "", "saturday" => "", "sunday" => "");
				$PeriodAfter 	= array("monday" => "", "tuesday" => "", "wednesday" => "", "thursday" => "", "friday" => "", "saturday" => "", "sunday" => "");
				
				$ret["comment"] ? ($str .= "# '" . $timePeriod["tp_name"]."_GMT".$gmt . "' timeperiod definition " . $i . "\n") : NULL;
				$str .= "define timeperiod{\n";
				if ($timePeriod["tp_name"]) 
					$str .= print_line("timeperiod_name", $timePeriod["tp_name"]."_GMT".$gmt);
	
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
				
				$i++;
				unset($timePeriod);
				foreach ($Period as $day => $value){
					if (strlen($PeriodAfter[$day].$Period[$day].$PeriodBefore[$day]))
					  $str .= print_line($day, $PeriodAfter[$day].($Period[$day] && $PeriodAfter[$day] ? "," : "").$Period[$day].($PeriodBefore[$day] && ($PeriodAfter[$day] || $Period[$day]) ? "," : "").$PeriodBefore[$day]);
				}
				$str .= "}\n\n";
			}
			
		}
	}
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES), $nagiosCFGPath.$tab['id']."/timeperiods.cfg");
	fclose($handle);
	$DBRESULT->free();
	unset($str);
	unset($i);
?>