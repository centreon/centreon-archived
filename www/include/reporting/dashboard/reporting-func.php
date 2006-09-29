<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Cedrick Facon

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/
	
	function getDateSelect(&$end_date_select, &$start_date_select, $period, $start, $end){

		$day = date("d",time());
		$year = date("Y",time());
		$month = date("m",time());
		$hour = date("G",time());
		$minute = date("i",time());
		$second = date("s",time());
		$start_date_day = mktime(0, 0, 0, $month, $day, $year);
	
		if(!is_null($period)){
			if($period == "today"){
				$start_date_select = mktime(0, 0, 0, $month, $day, $year);
				$end_date_select = time();
			} else if($period == "last24hours"){
				$start_date_select = time() - (24*60*60);
				$end_date_select = time();
			} else if($period == "yesterday"){
				$start_date_select = mktime(0, 0, 0, $month, $day-1, $year);
				$end_date_select = mktime(0, 0, 0, $month, $day, $year);
			} else if($period == "thisweek"){//a fair
				$dd = (date("D",mktime(0, 0, 0, $month, $day, $year)));
				for($ct = 1; $dd != "Mon" ;$ct++)
					$dd = (date("D",mktime(0, 0, 0, $month, ($day-$ct), $year)));
				$start_date_select = mktime(0, 0, 0, $month, $day-$ct, $year);
				$end_date_select = time();
			} else if($period == "last7days"){
				$start_date_select = mktime($hour, $minute, $second, $month, $day-7, $year);
				$end_date_select = mktime($hour, $minute, $second, $month, $day, $year);
			} else if($period == "last30days"){// attention au 31 
				$start_date_select = mktime($hour, $minute, $second, $month, $day-30, $year);
				$end_date_select = mktime($hour, $minute, $second, $month, $day, $year);
			} else if($period == "lastyear"){// attention au 31 
				$start_date_select = mktime(23, 59, 60, 12, 31, $year-1);
				$end_date_select = mktime(23, 59, 60, 12, 31, $year);
			} else if($period == "thismonth") {
				$start_date_select = mktime(23, 59, 60, $month, 0, $year);
				$end_date_select = mktime($hour, $minute, $second, $month, $day, $year);
			} else if($period == "thisyear"){
				$start_date_select = mktime(0, 0, 0, 0, 0, $year);
				$end_date_select = mktime($hour, $minute, $second, $month, $day, $year);
			} else { // lastmonth
				$start_date_select = mktime(23, 59, 60, $month-1, 0, $year);
				$end_date_select = mktime(23, 59, 60, $month, 0, $year);
			}		
		} else {
			$start_date_select = mktime(0, 0, 0, $month, $day, $year);
			$end_date_select = time();
		}

		if(!is_null($end) && !is_null($start)){
			if(!$end){
				$end = time();
				$endday = date("d",$end);
				$endyear = date("Y",$end);
				$endmonth = date("m",$end);
				$endhour = date("G",$end);
				$endminute = date("i",$end);
				$endsecond = date("s",$end);
				$end_date_select = mktime($endhour, $endminute, $endsecond, $endmonth, $endday, $endyear);
			}
			else{
				$end = my_getTimeTamps($end);
				$endday = date("d",$end);
				$endyear = date("Y",$end);
				$endmonth = date("m",$end);
				$endhour = date("G",$end);
				$endminute = date("i",$end);
				$endsecond = date("s",$end);
				$end_date_select = mktime(23, 59, 59, $endmonth, $endday, $endyear);
			}
			if(!$start)
				$start = mktime(0, 0, 0, $endmonth, $endday, $endyear);
			else
				$start = my_getTimeTamps($start);
			$start_date_select = $start;
		}
	}

	function getLogInDbForHost(&$Tup, &$Tdown, &$Tunreach, &$Tnone, $pearDB, $host_id, $start_date_select, $end_date_select){
		$rq = 'SELECT ' .
			'sum(UPTimeScheduled)' .
			' as Tup,' .				
			'sum(DOWNTimeScheduled)' .
			' as Tdown,' .
			'sum(UNREACHABLETimeScheduled)' .
			' as Tunreach, ' .				
			'sum(UNDETERMINATETimeScheduled)' .
			' as Tnone, ' .
			'min(date_start) as log_date_start,' .
			'max(date_end) as log_date_end,' .
			'sum(UNREACHABLETimeScheduled) as unreach FROM `log_archive_host` WHERE host_id = ' . $host_id  .
			' AND date_start >=  ' . ($start_date_select-1) .
			' AND date_end <= ' . $end_date_select;

		$res = & $pearDB->query($rq);
		if (PEAR::isError($res)){
		  die($res->getMessage());
		} else {
		  while ($h =& $res->fetchRow()){
			$Tup = 0 + $h["Tup"];
			$Tdown = 0 + $h["Tdown"];
			$Tunreach = 0 + $h["Tunreach"];
			$Tnone = 0 + $h["Tnone"];
		  }
		}		
	}

	function getLogInDbForSVC(&$tab_svc_bdd, $pearDB, $host_id, $start_date_select, $end_date_select){	
		$rq = 'SELECT ' .
			'service_id, ' .
			'sum(OKTimeScheduled)' .
			' as Tok,' .				
			'sum(WARNINGTimeScheduled)' .
			' as Twarn,' .
			'sum(UNKNOWNTimeScheduled)' .
			' as Tunknown, ' .				
			'sum(UNDETERMINATETimeScheduled)' .
			' as Tnone, ' .
			'sum(CRITICALTimeScheduled)' .
			' as Tcri, ' .
			'min(date_start) as log_date_start,' .
			'max(date_end) as log_date_end' .
			' FROM `log_archive_service` WHERE host_id = ' . $host_id  .
			' AND date_start >=  ' . ($start_date_select-1) .
			' AND date_end <= ' . $end_date_select .
			' GROUP BY service_id';
			$res = & $pearDB->query($rq);
			$tab_svc_bdd = array();
			if (PEAR::isError($res)){
			  die($res->getMessage());
			} else { 
			  while ($s =& $res->fetchRow()){
			  	
				$tab_svc_bdd[$s["service_id"]]["Tok"] = 0 + $s["Tok"];
				$tab_svc_bdd[$s["service_id"]]["Twarn"] = 0 + $s["Twarn"];
				$tab_svc_bdd[$s["service_id"]]["Tunknown"] = 0 + $s["Tunknown"];
				$tab_svc_bdd[$s["service_id"]]["Tnone"] = 0 + $s["Tnone"];
				$tab_svc_bdd[$s["service_id"]]["Tcri"] = 0 + $s["Tcri"];
			  }
			}
	}
	function getLogInDbForOneSVC(&$tab_svc_bdd, $pearDB, $host_id, $svc_id, $start_date_select, $end_date_select){	
		$rq = 'SELECT ' .
			'service_id, ' .
			'sum(OKTimeScheduled)' .
			' as Tok,' .				
			'sum(WARNINGTimeScheduled)' .
			' as Twarn,' .
			'sum(UNKNOWNTimeScheduled)' .
			' as Tunknown, ' .				
			'sum(UNDETERMINATETimeScheduled)' .
			' as Tnone, ' .
			'sum(CRITICALTimeScheduled)' .
			' as Tcri, ' .
			'min(date_start) as log_date_start,' .
			'max(date_end) as log_date_end' .
			' FROM `log_archive_service` WHERE host_id = ' . $host_id  .			
			' AND service_id =  ' . $svc_id .
			' AND date_start >=  ' . ($start_date_select-1) .
			' AND date_end <= ' . $end_date_select .
			' GROUP BY service_id';
			$res = & $pearDB->query($rq);
			$tab_svc_bdd = array();
			if (PEAR::isError($res)){
			  die($res->getMessage());
			} else { 
			  while ($s =& $res->fetchRow()){
			  	
				$tab_svc_bdd[$s["service_id"]]["Tok"] = 0 + $s["Tok"];
				$tab_svc_bdd[$s["service_id"]]["Twarn"] = 0 + $s["Twarn"];
				$tab_svc_bdd[$s["service_id"]]["Tunknown"] = 0 + $s["Tunknown"];
				$tab_svc_bdd[$s["service_id"]]["Tnone"] = 0 + $s["Tnone"];
				$tab_svc_bdd[$s["service_id"]]["Tcri"] = 0 + $s["Tcri"];
			  }
			}
	}

	// parser que pour l'host demandé
	function parseFile($file,$end_time, $startTimeOfThisDay, $mhost, $mservice){
		$start_time = 0;
		$log = NULL;
		$matches = "";
	
		if (file_exists($file) && !($log = fopen($file, "r")))
			echo "pel_cant_open" . $file . "<br>";
		$tab_log = array();	
		$tab_svc_log = array();
		$tablist = array();
		$res1 = array();	

	
		if ($log)
			for ($a=0, $b= 0, $i = 0; $str = fgets($log); $i++){
				if (preg_match("/^\[([0-9]*)\] (.+)/", $str, $matches)){				
					$time_event = $matches[1];
					$res = preg_split("/:/", $matches[2], 2);
				if (isset($res[1]))
					$res1 = preg_split("/;/", $res[1]);
				$type = $res[0];
				
				
				//array_walk($res1, 'trim_value');
				$type = trim($type);
			$name0 = isset($res1[0]) ? trim($res1[0]) : NULL;
			$name1 = isset($res1[1]) ? trim($res1[1]) : NULL;
			$name2 = isset($res1[2]) ? trim($res1[2]) : NULL;
			$name3 = isset($res1[3]) ? trim($res1[3]) : NULL;
			$name4 = isset($res1[4]) ? trim($res1[4]) : NULL;


				#
				## find the log's start time
				#
				if ($i == 0)// take start time
				$start_time = $time_event;
				if (!strncmp($type, "LOG ROTATION", 12))
				{
						$start_time = $time_event;
				}
				else if ($name0 == $mhost && (!strncmp($type, "CURRENT HOST STATE", 18) || !strncmp($type, "INITIAL HOST STATE", 18))){
					$tablist[$name0] = array();
					$tablist[$name0]["current_time"] = $start_time;
					$tablist[$name0]["current_state"] = $name1;
					$tablist[$name0]["timeUP"] = 0;
					$tablist[$name0]["timeDOWN"] = 0;
					$tablist[$name0]["timeUNREACHABLE"] = 0;
					$tablist[$name0]["timeNONE"] = 0;
					$tablist[$name0]["start_time"] = $start_time;
					$tablist[$name0]["tab_svc_log"] = array();
	
					if($name0 == $mhost)
						$tab_log[$a++] = getLogData($time_event, $name0, "", $name1, $res1[4], $type);
					
				}
				else if ($name0 == $mhost && (!strncmp($type, "CURRENT SERVICE STATE", 21) || !strncmp($type, "INITIAL SERVICE STATE", 21)))
				{
					$tablist[$name0]["tab_svc_log"][$name1] = array();
					$tab_tmp = array();
					$tab_tmp["current_state"] = $name2;
					$tab_tmp["current_time"] = $start_time;
					$tab_tmp["timeOK"] = 0;
					$tab_tmp["timeWARNING"] = 0;
					$tab_tmp["timeUNKNOWN"] = 0;
					$tab_tmp["timeCRITICAL"] = 0;
					$tab_tmp["timeNONE"] = 0;
					$tab_tmp["service_id"] = getMyServiceID($name1,getMyHostID($name0));				
					$tablist[$name0]["tab_svc_log"][$name1] = $tab_tmp;
				}
				#
				## host
				#
				else if ($name0 == $mhost && !strncmp($type, "HOST ALERT", 10) )
				{
					if(!isset($tablist[$name0]))
					{
						$tablist[$name0] = array();
						$tablist[$name0]["current_time"] = $start_time;
						$tablist[$name0]["current_state"] = "NONE";
						$tablist[$name0]["timeUP"] = 0;
						$tablist[$name0]["timeDOWN"] = 0;
						$tablist[$name0]["timeUNREACHABLE"] = 0;
						$tablist[$name0]["timeNONE"] = 0;
						$tablist[$name0]["start_time"] = $start_time;
						$tablist[$name0]["tab_svc_log"] = array();
					}

					//if($startTimeOfThisDay < $time_event){ ## pour essayer d'eviter les problemes quand la rotation na pas pu etre faite..
						if(!strncmp($tablist[$name0]["current_state"], "UP", 2))
						$tablist[$name0]["timeUP"] += ($time_event-$tablist[$name0]["current_time"]);
						elseif(!strncmp($tablist[$name0]["current_state"], "DOWN", 4))
						$tablist[$name0]["timeDOWN"] += ($time_event-$tablist[$name0]["current_time"]);
						elseif(!strncmp($tablist[$name0]["current_state"], "UNREACHABLE", 11))
						$tablist[$name0]["timeUNREACHABLE"] += ($time_event-$tablist[$name0]["current_time"]);
						else				
						$tablist[$name0]["timeNONE"] += ($time_event-$tablist[$name0]["current_time"]);
						if($name0 == $mhost)
							$tab_log[$a++] = getLogData($time_event, $name0, "", $name1, $res1[4], $type);
						$tablist[$name0]["current_time"] = $time_event; //save time
					//}
					$tablist[$name0]["current_state"] = $name1;
				}
				#
				## services associed
				#
				else if ($name0 == $mhost && !strncmp($type, "SERVICE ALERT", 13))
				{
					if(isset($tablist[$name0]["tab_svc_log"][$name1]) && (is_null($mservice) || $name0 == $mservice))
					{
						if($startTimeOfThisDay < $time_event){ ## pour essayer d'eviter les problemes quand la rotation na pas pu etre faite..
							$tab_tmp = array();
							$tab_tmp = $tablist[$name0]["tab_svc_log"][$name1];
							if(!strncmp($tab_tmp["current_state"], "OK", 2))
								$tab_tmp["timeOK"] += ($time_event-$tab_tmp["current_time"]);
							elseif(!strncmp($tab_tmp["current_state"], "WARNING", 7))
								$tab_tmp["timeWARNING"] += ($time_event-$tab_tmp["current_time"]);
							elseif(!strncmp($tab_tmp["current_state"], "UNKNOWN", 7))
								$tab_tmp["timeUNKNOWN"] += ($time_event-$tab_tmp["current_time"]);
							elseif(!strncmp($tab_tmp["current_state"], "CRITICAL", 8))
								$tab_tmp["timeCRITICAL"] += ($time_event-$tab_tmp["current_time"]);
							else
								$tab_tmp["timeNONE"] += ($time_event-$tab_tmp["current_time"]);
							$tab_tmp["current_time"] = $time_event; //save time
							$tablist[$name0]["tab_svc_log"][$name1] = $tab_tmp;
						}
						$tab_tmp["current_state"] = $name2; //save time
					}
				}
			}
		}
		$tablist["time_start"] = $start_time;
		$tablist["tab_log"] = $tab_log;

		return($tablist);
	}

?>