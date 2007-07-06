<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus - Cedrick Facon

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

	function getDateSelect_predefined(&$end_date_select, &$start_date_select, $period){

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
			} else if($period == "thisweek"){
				$dd = (date("D",mktime(0, 0, 0, $month, $day, $year)));
				for($ct = 1; $dd != "Mon" ;$ct++)
					$dd = (date("D",mktime(0, 0, 0, $month, ($day-$ct), $year)));
				$start_date_select = mktime(0, 0, 0, $month, $day-$ct, $year);
				$end_date_select = time();
			} else if($period == "last7days"){
				$start_date_select = mktime(0, 0, 0, $month, $day-7, $year);
				$end_date_select = mktime($hour, $minute, $second, $month, $day, $year);
			} else if($period == "last30days"){// attention au 31 
				$start_date_select = mktime(0, 0, 0, $month, $day-30, $year);
				$end_date_select = mktime($hour, $minute, $second, $month, $day, $year);
			} else if($period == "lastyear"){// attention au 31 
				$start_date_select = mktime(23, 59, 60, 12, 31, $year-2);
				$end_date_select = mktime(23, 59, 60, 12, 31, $year-1);
			} else if($period == "thismonth") {
				$start_date_select = mktime(23, 59, 60, $month, 0, $year);
				$end_date_select = mktime($hour, $minute, $second, $month, $day, $year);
			} else if($period == "thisyear"){				
				$start_date_select = mktime(0, 0, 0, 1, 1, $year);
				$end_date_select = mktime($hour, $minute, $second, $month, $day, $year);
			} else { // lastmonth
				$start_date_select = mktime(23, 59, 60, $month-1, 0, $year);
				$end_date_select = mktime(23, 59, 60, $month, 0, $year);
			}		
		} else {
			$start_date_select = mktime(0, 0, 0, $month, $day, $year);
			$end_date_select = time();
		}
	}
	
	function getDateSelect_customized(&$end_date_select, &$start_date_select, $start, $end){
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
			'min(date_start) as log_date_start,' .
			'max(date_end) as log_date_end,' .
			'sum(UNREACHABLETimeScheduled) as unreach FROM `log_archive_host` WHERE host_id = ' . $host_id  .
			' AND date_start >=  ' . ($start_date_select - 1) .
			' AND date_end <= ' . ($end_date_select + 1);


		$ttmp = $end_date_select - $start_date_select;

		$res = & $pearDB->query($rq);
		if (PEAR::isError($res)){
		  die($res->getMessage());
		} else {
		  while ($h =& $res->fetchRow()){
			$Tup = 0 + $h["Tup"];
			$Tdown = 0 + $h["Tdown"];
			$Tunreach = 0 + $h["Tunreach"];
			$Tnone = 0 + ($end_date_select - $start_date_select) - ($h["Tup"]+$h["Tdown"]+ $h["Tunreach"]);
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
			'sum(CRITICALTimeScheduled)' .
			' as Tcri, ' .
			'min(date_start) as log_date_start,' .
			'max(date_end) as log_date_end' .
			' FROM `log_archive_service` WHERE host_id = ' . $host_id  .
			' AND date_start >=  ' . ($start_date_select-1) .
			' AND date_end <= ' . ($end_date_select + 1) .
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
				$tab_svc_bdd[$s["service_id"]]["Tnone"] = 0 + ($end_date_select - $start_date_select) - ($s["Tok"]+$s["Twarn"]+$s["Tunknown"]);
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
			'sum(CRITICALTimeScheduled)' .
			' as Tcri, ' .
			'min(date_start) as log_date_start,' .
			'max(date_end) as log_date_end' .
			' FROM `log_archive_service` WHERE host_id = ' . $host_id  .			
			' AND service_id =  ' . $svc_id .
			' AND date_start >=  ' . ($start_date_select-1) .
			' AND date_end <= ' . ($end_date_select + 1) .
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
				$tab_svc_bdd[$s["service_id"]]["Tcri"] = 0 + $s["Tcri"];
			  }
			}
	}

	#
	## Parsing file help function
	#
	function is_new_day($time_event, $day_current_start, $day_current_end)
	{
		if($time_event > $day_current_end || $time_event < $day_current_start)
		{
			return true;
		}
		return false;
	}
	function is_in_today($time_event)
	{
		$time = time();
		$day = date("d",$time);
		$year = date("Y",$time);
		$month = date("m",$time);
				
		if($time_event >= mktime(0, 0, 0, $month, $day, $year) && $time_event < mktime(0, 0, 0, $month, $day+1, $year))
		return true;
		else
		return false;
	}


	function parseFile($file, $time, &$tab_hosts, &$tab_services, &$day_current_start, &$day_current_end, $today_mode){	
	#
	## if $today_mode = 0 => archivelog else todaylog only  
	#

		$file_start_time = 0;
		$day_in_second = 86400;
	
		if (file_exists($file) && !($log = fopen($file, "r")))
			echo "pel_cant_open" . $file . "<br>";
		if ($log)
			for ($a=0, $b= 0, $i = 0; $str = fgets($log); $i++){
				if (preg_match("/^\[([0-9]*)\] (.+)/", $str, $matches)){
					$time_event = $matches[1];

					if($day_current_end == 0)
					{
						$day_current_start = my_getStartDay($time_event);
						$day_current_end = my_getEndDay($time_event);
					}

					#
					## use for archivelogindb
					#										
					if(!$today_mode && is_new_day($time_event, $day_current_start, $day_current_end)){
						insert_in_db($file, $tab_hosts, $tab_services, $day_current_start, $day_current_end);
						$tab_hosts = array();
						$tab_services = array();
						$day_current_start = my_getStartDay($time_event);
						$day_current_end = my_getEndDay($time_event);
					}

					#
					## verify if $time_event is in today time for the today usage with viewX.php
					#
					if(($today_mode && is_in_today($time_event)) || !$today_mode) {
					$res = preg_split("/:/", $matches[2], 2);
					if (isset($res[1]))
						$res1 = preg_split("/;/", $res[1]);
					$type = $res[0];

					if(isset($res1) && is_array($res1))
						array_walk($res1, 'trim_value');


					#
					## Hosts
					#	
					if( (!strncmp($type, "CURRENT HOST STATE", 18) ||
					 	 !strncmp($type, "INITIAL HOST STATE", 18) ||
					  	 !strncmp($type, "HOST ALERT", 10))
						&& !strncmp($res1[2], "HARD", 4)
					  	 )
					{
						if(isset($tab_hosts[$res1[0]])){
							if(!strncmp($tab_hosts[$res1[0]]["current_state"], "UP", 2)){
								if(strncmp($type, "CURRENT HOST STATE", 18) && strncmp($type, "INITIAL HOST STATE", 18))
									$tab_hosts[$res1[0]]["UPnbEvent"] += 1;
								$tab_hosts[$res1[0]]["timeUP"] += ($time_event-$tab_hosts[$res1[0]]["current_time"]);
							}
							elseif(!strncmp($tab_hosts[$res1[0]]["current_state"], "DOWN", 4)){
								if(strncmp($type, "CURRENT HOST STATE", 18) && strncmp($type, "INITIAL HOST STATE", 18))
								$tab_hosts[$res1[0]]["DOWNnbEvent"] += 1;
								$tab_hosts[$res1[0]]["timeDOWN"] += ($time_event-$tab_hosts[$res1[0]]["current_time"]);
							}
							elseif(!strncmp($tab_hosts[$res1[0]]["current_state"], "UNREACHABLE", 11)){
								if(strncmp($type, "CURRENT HOST STATE", 18) && strncmp($type, "INITIAL HOST STATE", 18))
								$tab_hosts[$res1[0]]["UNREACHABLEnbEvent"] += 1;
								$tab_hosts[$res1[0]]["timeUNREACHABLE"] += ($time_event-$tab_hosts[$res1[0]]["current_time"]);
							}
							else
								$tab_hosts[$res1[0]]["timeNONE"] += ($time_event-$tab_hosts[$res1[0]]["current_time"]);
							$tab_hosts[$res1[0]]["current_state"] = $res1[1];
							$tab_hosts[$res1[0]]["current_time"] = $time_event; //save time
/*
							$tmp_log = array();
							$tmp_log["time"] = date("d/m/Y H:i:s", $time_event);
							$tmp_log["status"] = $res1[1];
							$tmp_log["host"] = $res1[0];
							$tmp_log["type"] = $type;
							$tmp_log["state"] = $res1[2];
							$tmp_log["output"] = $res1[4];
							$tab_hosts[$res1[0]]["log"][$time_event] = $tmp_log; //log
*/
						}
						else {
							$tab_hosts[$res1[0]] = array();
							$tab_hosts[$res1[0]]["current_time"] = $time_event;
							$tab_hosts[$res1[0]]["current_state"] = $res1[1];
							$tab_hosts[$res1[0]]["timeUP"] = 0;
							$tab_hosts[$res1[0]]["timeDOWN"] = 0;
							$tab_hosts[$res1[0]]["timeUNREACHABLE"] = 0;
							$tab_hosts[$res1[0]]["timeNONE"] = 0;
							$tab_hosts[$res1[0]]["UPnbEvent"] = 0;
							$tab_hosts[$res1[0]]["DOWNnbEvent"] = 0;
							$tab_hosts[$res1[0]]["UNREACHABLEnbEvent"] = 0;
							$tab_hosts[$res1[0]]["start_time"] = $day_current_start;
							$tab_hosts[$res1[0]]["tab_svc_log"] = array();
/*
							$tmp_log = array();
							$tmp_log["time"] = date("d/m/Y H:i:s", $time_event);
							$tmp_log["status"] = $res1[1];
							$tmp_log["host"] = $res1[0];
							$tmp_log["type"] = $type;
							$tmp_log["state"] = $res1[2];
							$tmp_log["output"] = $res1[4];
							$tab_hosts[$res1[0]]["log"][$time_event] = $tmp_log; //log
*/
							}
					}
					
					#
					## Services
					#
					else if ( (!strncmp($type, "CURRENT SERVICE STATE", 21) || !strncmp($type, "INITIAL SERVICE STATE", 21) || !strncmp($type, "SERVICE ALERT", 13))
							&& !strncmp($res1[3], "HARD", 4)
							)
					{
						if(isset($tab_services[$res1[1]][$res1[0]]))
						{
							$tab_tmp = array();
							$tab_tmp = $tab_services[$res1[1]][$res1[0]];
							if(!strncmp($tab_tmp["current_state"], "OK", 2)){
								if(strncmp($type, "CURRENT SERVICE STATE", 21) && strncmp($type, "INITIAL SERVICE STATE", 21))
									$tab_tmp["OKnbEvent"] += 1;
								$tab_tmp["timeOK"] += ($time_event-$tab_tmp["current_time"]);
							}
							elseif(!strncmp($tab_tmp["current_state"], "WARNING", 7)){
								if(strncmp($type, "CURRENT SERVICE STATE", 21) && strncmp($type, "INITIAL SERVICE STATE", 21))
									$tab_tmp["WARNINGnbEvent"] += 1;
								$tab_tmp["timeWARNING"] += ($time_event-$tab_tmp["current_time"]);
							}
							elseif(!strncmp($tab_tmp["current_state"], "UNKNOWN", 7)){
								if(strncmp($type, "CURRENT SERVICE STATE", 21) && strncmp($type, "INITIAL SERVICE STATE", 21))
									$tab_tmp["UNKNOWNnbEvent"] += 1;
								$tab_tmp["timeUNKNOWN"] += ($time_event-$tab_tmp["current_time"]);
							}
							elseif(!strncmp($tab_tmp["current_state"], "CRITICAL", 8)){
								if(strncmp($type, "CURRENT SERVICE STATE", 21) && strncmp($type, "INITIAL SERVICE STATE", 21))
									$tab_tmp["CRITICALnbEvent"] += 1;
								$tab_tmp["timeCRITICAL"] += ($time_event-$tab_tmp["current_time"]);
							}
							else
								$tab_tmp["timeNONE"] += ($time_event-$tab_tmp["current_time"]);
							$tab_tmp["current_time"] = $time_event; //save time
							$tab_tmp["current_state"] = $res1[2]; //save time
							$tab_services[$res1[1]][$res1[0]] = $tab_tmp;							
						}
						else{
							$tab_services[$res1[1]][$res1[0]] = array();
							$tab_tmp = array();
							$tab_tmp["current_state"] = $res1[2];
							$tab_tmp["current_time"] = $time_event;
							$tab_tmp["timeOK"] = 0;
							$tab_tmp["OKnbEvent"] = 0;
							$tab_tmp["timeWARNING"] = 0;
							$tab_tmp["WARNINGnbEvent"] = 0;
							$tab_tmp["timeUNKNOWN"] = 0;
							$tab_tmp["UNKNOWNnbEvent"] = 0;
							$tab_tmp["timeCRITICAL"] = 0;
							$tab_tmp["CRITICALnbEvent"] = 0;
							$tab_tmp["timeNONE"] = 0;
							$tab_tmp["start_time"] = $day_current_start;
							$tab_tmp["service_id"] = getMyServiceID($res1[1],getMyHostID($res1[0]));
							$tab_services[$res1[1]][$res1[0]] = $tab_tmp;
						}
					}
				}//end if todaylog
				}
			}
	}


?>