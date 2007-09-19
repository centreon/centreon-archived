<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
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
	function getTodayLogForHost($host_name, &$hbase, $pearDBO, $today_start, $today_end){
		$tab_tmp = array();
		$tab_tmp["state"] = "UP";
		$tab_tmp["time"] = $today_start;
		$tab_tmp["Tup"] = 0;
		$tab_tmp["TupNBAlert"] = 0;
		$tab_tmp["Tdown"] = 0;
		$tab_tmp["TdownNBAlert"] = 0;
		$tab_tmp["Tunreachable"] = 0;
		$tab_tmp["TunreachableNBAlert"] = 0;
		$tab_tmp["Tnone"] = 0;

		$rq = "select * from log where host_name like '%".$host_name."%' and ctime <= ". 
			$today_end . " AND service_description is null and ctime >= " . $today_start . " AND ( msg_type = '7' OR msg_type = '9' OR msg_type = '1')";


		$DBres =& $pearDBO->query($rq);
		if (PEAR::isError($DBres))
			print "DB Error : ".$DBres->getDebugInfo()."<br>";
		$log = array();
		while ($DBres->fetchInto($log)){
			if($log["status"] == "UP"){
				$tab_tmp["Tup"] += $log["ctime"] - $tab_tmp["time"];
				$tab_tmp["TupNBAlert"] += 1;
			}
			if($log["status"] == "DOWN"){
				$tab_tmp["Tdown"] += $log["ctime"] - $tab_tmp["time"];
				$tab_tmp["TdownNBAlert"] += 1;
			}
			if($log["status"] == "UNREACHABLE"){
				$tab_tmp["Tunreachable"] += $log["ctime"] - $tab_tmp["time"];
				$tab_tmp["TunreachableNBAlert"] += 1;
			}
			else
				$tab_tmp["Tnone"] += $log["ctime"] - $tab_tmp["time"];
			$tab_tmp["state"] = $log["status"];
			$tab_tmp["time"] = $log["ctime"];
		}

		if($tab_tmp["state"] == "UP"){
			$tab_tmp["Tup"] += $today_end - $tab_tmp["time"];
		}
		if($tab_tmp["state"] == "DOWN"){
			$tab_tmp["Tdown"] += $today_end - $tab_tmp["time"];
		}
		if($tab_tmp["state"] == "UNREACHABLE"){
			$tab_tmp["Tunreachable"] += $today_end - $tab_tmp["time"];
		}
		else
			$tab_tmp["Tnone"] += $today_end - $tab_tmp["time"];

		if(isset($hbase["Tup"]))
			$hbase["Tup"] += $tab_tmp["Tup"];
		else
			$hbase["Tup"] = $tab_tmp["Tup"];
		if(isset($hbase["TupNBAlert"]))
			$hbase["TupNBAlert"] += $tab_tmp["TupNBAlert"];
		else
			$hbase["TupNBAlert"] = $tab_tmp["TupNBAlert"];
		if(isset($hbase["Tdown"]))
			$hbase["Tdown"] += $tab_tmp["Tdown"];
		else
			$hbase["Tdown"] = $tab_tmp["Tdown"];
		if(isset($hbase["TdownNBAlert"]))
			$hbase["TdownNBAlert"] += $tab_tmp["TdownNBAlert"];
		else
			$hbase["TdownNBAlert"] = $tab_tmp["TdownNBAlert"];
		if(isset($hbase["Tunreachable"]))
			$hbase["Tunreachable"] += $tab_tmp["Tunreachable"];
		else
			$hbase["Tunreachable"] = $tab_tmp["Tunreachable"];
		if(isset($hbase["TunreachableNBAlert"]))
			$hbase["TunreachableNBAlert"] += $tab_tmp["TunreachableNBAlert"];
		else
			$hbase["TunreachableNBAlert"] = $tab_tmp["TunreachableNBAlert"];
		if(isset($hbase["Tnone"]))
			$hbase["Tnone"] += $tab_tmp["Tnone"];
		else
			$hbase["Tnone"] = $tab_tmp["Tnone"];
	}

	function getLogInDbForHost(&$hbase, $pearDB, $host_id, $start_date_select, $end_date_select,$pearDBO, $today_start, $today_end){
		$hbase["Tup"] = 0;
		$hbase["TupNBAlert"] = 0;
		$hbase["Tdown"] = 0;
		$hbase["TdownNBAlert"] = 0;
		$hbase["Tunreachable"] = 0;
		$hbase["TunreachableNBAlert"] = 0;
		$hbase["Tnone"] = 0;

		# ODS Database retrieve information
		$DBRESULT =& $pearDBO->query("SELECT * FROM config LIMIT 1");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		$result_config = $DBRESULT->fetchRow();
		if (isset($result_config) && $result_config)
			$gopt = array_map("myDecode", $result_config);

		$rq = 'SELECT ' .
			'sum(UPnbEvent) as TupNBAlert, ' .
			'sum(UPTimeScheduled)' .
			' as Tup,' .				
			'sum(DOWNnbEvent) as TdownNBAlert, ' .
			'sum(DOWNTimeScheduled)' .
			' as Tdown,' .
			'sum(UNREACHABLEnbEvent) as TunreachableNBAlert, ' .
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
			$hbase = $h;
			$hbase["Tnone"] = 0 + ($end_date_select - $start_date_select) - ($h["Tup"]+$h["Tdown"]+ $h["Tunreach"]);
		  }
		}
				
		if($end_date_select > $today_start)
			getTodayLogForHost(getMyHostName($host_id), $hbase, $pearDBO, $today_start, $today_end);		
	}

	function getLogInDbForHostGroup(&$hbase, $pearDB, $pearDBO, $hostgroup_id, $start_date_select, $end_date_select, $today_start, $today_end){
		# ODS Database retrieve information
		$DBRESULT =& $pearDBO->query("SELECT * FROM config LIMIT 1");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		$result_config = $DBRESULT->fetchRow();
		if (isset($result_config) && $result_config)
			$gopt = array_map("myDecode", $result_config);
		$hbase["average"]["Tup"] = 0;
		$hbase["average"]["TupNBAlert"] = 0;
		$hbase["average"]["Tdown"] = 0;
		$hbase["average"]["TdownNBAlert"] = 0;
		$hbase["average"]["Tunreachable"] = 0;
		$hbase["average"]["TunreachableNBAlert"] = 0;
		$hbase["average"]["Tnone"] = 0;
		$hbase["average"]["today"]["Tup"] = 0;
		$hbase["average"]["today"]["TupNBAlert"] = 0;
		$hbase["average"]["today"]["Tdown"] = 0;
		$hbase["average"]["today"]["TdownNBAlert"] = 0;
		$hbase["average"]["today"]["Tunreachable"] = 0;
		$hbase["average"]["today"]["TunreachableNBAlert"] = 0 ;
		$hbase["average"]["today"]["Tnone"] = 0;
		$i = 0;
		$hosts_id = getMyHostGroupHosts($hostgroup_id);
		foreach($hosts_id as $h) {
			$htmp = array();
			getLogInDbForHost($htmp, $pearDB, $h, $start_date_select, $end_date_select,$pearDBO, $today_start, $today_end);
			$hbase[$h]["Tup"] = $htmp["Tup"];
			$hbase[$h]["TupNBAlert"] = $htmp["TupNBAlert"];
			$hbase[$h]["Tdown"] = $htmp["Tdown"];
			$hbase[$h]["TdownNBAlert"] = $htmp["TdownNBAlert"];
			$hbase[$h]["Tunreachable"] = $htmp["Tunreach"];
			$hbase[$h]["TunreachableNBAlert"] = $htmp["TunreachableNBAlert"];
			$hbase[$h]["Tnone"] = $htmp["Tnone"];

			$hbase["average"]["Tup"] += $htmp["Tup"];
			$hbase["average"]["TupNBAlert"] += $htmp["TupNBAlert"];
			$hbase["average"]["Tdown"] += $htmp["Tdown"];
			$hbase["average"]["TdownNBAlert"] += $htmp["TdownNBAlert"];
			$hbase["average"]["Tunreachable"] += $htmp["Tunreach"];
			$hbase["average"]["TunreachableNBAlert"] += $htmp["TunreachableNBAlert"];
			$hbase["average"]["Tnone"] += $htmp["Tnone"];

			#
			## ods data for TODAY log
			#
			$tab_tmp = array();
			if($end_date_select > $today_start){
				getTodayLogForHost(getMyHostName($h), $tab_tmp, $pearDBO, $today_start, $today_end);
				$hbase["average"]["today"]["Tup"] +=  $tab_tmp["Tup"];
				$hbase["average"]["today"]["TupNBAlert"] += $htmp["TupNBAlert"];
				$hbase["average"]["today"]["Tdown"] +=   $tab_tmp["Tdown"];
				$hbase["average"]["today"]["TdownNBAlert"] +=  $tab_tmp["TdownNBAlert"];
				$hbase["average"]["today"]["Tunreachable"] += $tab_tmp["Tunreachable"];
				$hbase["average"]["today"]["TunreachableNBAlert"] +=  $tab_tmp["TunreachableNBAlert"];
				$hbase["average"]["today"]["Tnone"] +=  $tab_tmp["Tnone"];	
				$hbase[$h]["today"] = $tab_tmp;
			}
			$i++;			
		}
		$hbase["average"]["Tup"] > 0 ? $hbase["average"]["Tup"] /= $i: 0;
		$hbase["average"]["TupNBAlert"] > 0 ? $hbase["average"]["TupNBAlert"] /= $i: 0;
		$hbase["average"]["Tdown"] > 0 ? $hbase["average"]["Tdown"] /= $i: 0;
		$hbase["average"]["TdownNBAlert"] > 0 ? $hbase["average"]["TdownNBAlert"] /= $i: 0;
		$hbase["average"]["Tunreachable"] > 0 ? $hbase["average"]["Tunreachable"] /= $i: 0;
		$hbase["average"]["TunreachableNBAlert"] > 0 ? $hbase["average"]["TunreachableNBAlert"] /= $i: 0;
		$hbase["average"]["Tnone"] > 0 ? $hbase["average"]["Tnone"] /= $i: 0;
		if($end_date_select > $today_start){
			$hbase["average"]["today"]["Tup"] > 0 ? $hbase["average"]["today"]["Tup"] /= $i: 0;
			$hbase["average"]["today"]["TupNBAlert"] > 0 ? $hbase["average"]["today"]["TupNBAlert"] /= $i: 0;
			$hbase["average"]["today"]["Tdown"] > 0 ? $hbase["average"]["today"]["Tdown"] /= $i: 0;
			$hbase["average"]["today"]["TdownNBAlert"] > 0 ? $hbase["average"]["today"]["TdownNBAlert"] /= $i: 0;
			$hbase["average"]["today"]["Tunreachable"] > 0 ? $hbase["average"]["today"]["Tunreachable"] /= $i: 0;
			$hbase["average"]["today"]["TunreachableNBAlert"] > 0 ? $hbase["average"]["today"]["TunreachableNBAlert"] /= $i: 0;
			$hbase["average"]["today"]["Tnone"] > 0 ? $hbase["average"]["today"]["Tnone"] /= $i: 0;
		}
	}

	function getLogInDbForServicesGroup(&$sbase, $pearDB, $pearDBO, $servicegroup_id, $start_date_select, $end_date_select, $today_start, $today_end){

		$sbase["average"]["Tok"] = 0;
		$sbase["average"]["Twarning"] = 0;
		$sbase["average"]["Tunknown"] = 0;
		$sbase["average"]["Tcritical"] = 0;
		$sbase["average"]["OKnbEvent"] = 0;
		$sbase["average"]["WARNINGnbEvent"] = 0;
		$sbase["average"]["UNKNOWNnbEvent"] = 0;
		$sbase["average"]["CRITICALnbEvent"] = 0;

		$sbase["average"]["today"]["Tok"] = 0;
		$sbase["average"]["today"]["Twarning"] = 0;
		$sbase["average"]["today"]["Tunknown"] = 0;
		$sbase["average"]["today"]["Tcritical"] = 0;
		$sbase["average"]["today"]["OKnbEvent"] = 0;
		$sbase["average"]["today"]["WARNINGnbEvent"] = 0;
		$sbase["average"]["today"]["UNKNOWNnbEvent"] = 0;
		$sbase["average"]["today"]["CRITICALnbEvent"] = 0;

		$i = 0;
		$svc_tab = getMyServiceGroupServices($servicegroup_id);

		foreach($svc_tab as $key => $s){
			$stmp = array();
			$res = preg_split("/_/", $key);
			$sbase[$s]["Tok"] = 0;
			$sbase[$s]["Twarning"] = 0;
			$sbase[$s]["Tunknown"] = 0;
			$sbase[$s]["Tcritical"] = 0;
			$sbase[$s]["OKnbEvent"]= 0;
			$sbase[$s]["WARNINGnbEvent"]= 0;
			$sbase[$s]["UNKNOWNnbEvent"]= 0;
			$sbase[$s]["CRITICALnbEvent"]= 0;
			$sbase[$s]["svc_id"]= $res[1];
			$sbase[$s]["host_id"]= $res[0];

			getLogInDbForOneSVC($stmp, $pearDB, $res[0], $res[1], $start_date_select, $end_date_select, $pearDBO, $today_start, $today_end);

			if(isset($stmp)){
				$sbase[$s]["Tok"] = $stmp["resume"]["Tok"];
				$sbase[$s]["Twarning"] = $stmp["resume"]["Twarn"];
				$sbase[$s]["Tunknown"] = $stmp["resume"]["Tunknown"];
				$sbase[$s]["Tcritical"] = $stmp["resume"]["Tcri"];
				$sbase[$s]["OKnbEvent"]= $stmp["resume"]["OKnbEvent"];
				$sbase[$s]["WARNINGnbEvent"]= $stmp["resume"]["WARNINGnbEvent"];
				$sbase[$s]["UNKNOWNnbEvent"]= $stmp["resume"]["UNKNOWNnbEvent"];
				$sbase[$s]["CRITICALnbEvent"]= $stmp["resume"]["CRITICALnbEvent"];
	
				$sbase["average"]["Tok"] += $stmp["resume"]["Tok"];
				$sbase["average"]["Twarning"] += $stmp["resume"]["Twarn"];
				$sbase["average"]["Tunknown"] += $stmp["resume"]["Tunknown"];
				$sbase["average"]["Tcritical"] += $stmp["resume"]["Tcri"];
				$sbase["average"]["OKnbEvent"] += $stmp["resume"]["OKnbEvent"];
				$sbase["average"]["WARNINGnbEvent"] += $stmp["resume"]["WARNINGnbEvent"];
				$sbase["average"]["UNKNOWNnbEvent"] += $stmp["resume"]["UNKNOWNnbEvent"];
				$sbase["average"]["CRITICALnbEvent"] += $stmp["resume"]["CRITICALnbEvent"];

				$sbase["average"]["today"]["Tok"] += $stmp["today"]["Tok"];
				$sbase["average"]["today"]["Twarning"] += $stmp["today"]["Twarn"];
				$sbase["average"]["today"]["Tunknown"] += $stmp["today"]["Tunknown"];
				$sbase["average"]["today"]["Tcritical"] += $stmp["today"]["Tcri"];
				$sbase["average"]["today"]["OKnbEvent"] += $stmp["today"]["OKnbEvent"];
				$sbase["average"]["today"]["WARNINGnbEvent"] += $stmp["today"]["WARNINGnbEvent"];
				$sbase["average"]["today"]["UNKNOWNnbEvent"] += $stmp["today"]["UNKNOWNnbEvent"];
				$sbase["average"]["today"]["CRITICALnbEvent"] += $stmp["today"]["CRITICALnbEvent"];
			}
			$i++;
		}
		$sbase["average"]["today"]["Tok"] > 0 ?  $sbase["average"]["today"]["Tok"] /= $i : 0;
		$sbase["average"]["today"]["Twarning"] > 0 ? $sbase["average"]["today"]["Twarning"] /= $i : 0;
		$sbase["average"]["today"]["Tunknown"] > 0 ? $sbase["average"]["today"]["Tunknown"] /= $i : 0;
		$sbase["average"]["today"]["Tcritical"] > 0 ? $sbase["average"]["today"]["Tcritical"] /= $i : 0;
		$sbase["average"]["today"]["OKnbEvent"] > 0 ? $sbase["average"]["today"]["OKnbEvent"] /= $i : 0;
		$sbase["average"]["today"]["WARNINGnbEvent"] > 0 ? $sbase["average"]["today"]["WARNINGnbEvent"] /= $i : 0;
		$sbase["average"]["today"]["UNKNOWNnbEvent"] > 0 ? $sbase["average"]["today"]["UNKNOWNnbEvent"] /= $i : 0;
		$sbase["average"]["today"]["CRITICALnbEvent"] > 0 ? $sbase["average"]["today"]["CRITICALnbEvent"] /= $i : 0;

		$sbase["average"]["Tok"] > 0 ?  $sbase["average"]["Tok"] /= $i : 0;
		$sbase["average"]["Twarning"] > 0 ? $sbase["average"]["Twarning"] /= $i : 0;
		$sbase["average"]["Tunknown"] > 0 ? $sbase["average"]["Tunknown"] /= $i : 0;
		$sbase["average"]["Tcritical"] > 0 ? $sbase["average"]["Tcritical"] /= $i : 0;
		$sbase["average"]["OKnbEvent"] > 0 ? $sbase["average"]["OKnbEvent"] /= $i : 0;
		$sbase["average"]["WARNINGnbEvent"] > 0 ? $sbase["average"]["WARNINGnbEvent"] /= $i : 0;
		$sbase["average"]["UNKNOWNnbEvent"] > 0 ? $sbase["average"]["UNKNOWNnbEvent"] /= $i : 0;
		$sbase["average"]["CRITICALnbEvent"] > 0 ? $sbase["average"]["CRITICALnbEvent"] /= $i : 0;

		/*
					echo "<pre>";
					print_r($sbase);
					echo "</pre><hr>";
		*/
	}


	function getLogInDbForSVC(&$tab_svc_bdd, $pearDB, $host_id, $start_date_select, $end_date_select, $pearDBO, $today_start, $today_end){
		$tab_svc_bdd = array();
		$tab_svc = getMyHostServices($host_id);
		$tab_log = array();
		$s = array();
		$tmp = array();
		
		$rq = 'SELECT ' .
			'service_id, ' .
			'sum(OKTimeScheduled)' .
			' as Tok,' .
			'sum(OKnbEvent) as OKnbEvent,' .
			'sum(WARNINGTimeScheduled)' .
			' as Twarn,' .
			'sum(WARNINGnbEvent) as WARNINGnbEvent,' .
			'sum(UNKNOWNTimeScheduled)' .
			' as Tunknown, ' .
			'sum(UNKNOWNnbEvent) as UNKNOWNnbEvent,' .				
			'sum(CRITICALTimeScheduled)' .
			' as Tcri, ' .
			'sum(CRITICALnbEvent) as CRITICALnbEvent, ' .
			'min(date_start) as log_date_start,' .
			'max(date_end) as log_date_end' .
			' FROM `log_archive_service` WHERE host_id = ' . $host_id  .
			' AND date_start >=  ' . ($start_date_select-1) .
			' AND date_end <= ' . ($end_date_select + 1) .
			' GROUP BY service_id';
		$DBres =& $pearDB->query($rq);
		if (PEAR::isError($DBres))
			print "DB Error : ".$DBres->getDebugInfo()."<br>";
		while ($DBres->fetchInto($s)){
			$tab_log[$s["service_id"]] = $s;
		}

		foreach ($tab_svc as $svc_id => $svc) {			
			$tab_svc_bdd[$svc_id]["OKnbEvent"] = 0;
			$tab_svc_bdd[$svc_id]["WARNINGnbEvent"] = 0;
			$tab_svc_bdd[$svc_id]["UNKNOWNnbEvent"] = 0;
			$tab_svc_bdd[$svc_id]["CRITICALnbEvent"] = 0;
			$tab_svc_bdd[$svc_id]["Tok"] = 0;
			$tab_svc_bdd[$svc_id]["Twarn"] = 0 ;
			$tab_svc_bdd[$svc_id]["Tunknown"] = 0;
			$tab_svc_bdd[$svc_id]["Tcri"] = 0;				

			if(isset($tab_log[$svc_id]))
			$tab_svc_bdd[$svc_id] = $tab_log[$svc_id];

			if($end_date_select > $today_start){
				getTodayLogForSVC(getMyHostName($host_id), getMyServiceName($svc_id), $tmp, $pearDBO, $today_start, $today_end);
				$tab_svc_bdd[$svc_id]["OKnbEvent"] += $tmp["OKnbEvent"];
				$tab_svc_bdd[$svc_id]["WARNINGnbEvent"] += $tmp["WARNINGnbEvent"];
				$tab_svc_bdd[$svc_id]["UNKNOWNnbEvent"] += $tmp["UNKNOWNnbEvent"];
				$tab_svc_bdd[$svc_id]["CRITICALnbEvent"] += $tmp["CRITICALnbEvent"];
				$tab_svc_bdd[$svc_id]["Tok"] += $tmp["Tok"];
				$tab_svc_bdd[$svc_id]["Twarn"] += $tmp["Twarn"];
				$tab_svc_bdd[$svc_id]["Tunknown"] += $tmp["Tunknown"];
				$tab_svc_bdd[$svc_id]["Tcri"] += $tmp["Tcri"];
			}
		}
	}

	function getTodayLogForSVC($host_name, $service_description, &$hbase, $pearDBO, $today_start, $today_end){
		$tab_tmp = array();
		$tab_tmp["state"] = "OK";
		$tab_tmp["time"] = $today_start;
		$tab_tmp["Tok"] = 0;
		$tab_tmp["OKnbEvent"] = 0;
		$tab_tmp["Twarn"] = 0;
		$tab_tmp["WARNINGnbEvent"] = 0;
		$tab_tmp["Tunknown"] = 0;
		$tab_tmp["UNKNOWNnbEvent"] = 0;
		$tab_tmp["Tcri"] = 0;
		$tab_tmp["CRITICALnbEvent"] = 0;
		$tab_tmp["Tnone"] = 0;

		$rq = "select * from log where host_name like '%".$host_name."%' and ctime <= ". 
			$today_end . " AND service_description like '%".$service_description."%' and ctime >= " . $today_start . " AND ( msg_type = '6' OR msg_type = '8' OR msg_type = '2')";

		$DBres =& $pearDBO->query($rq);
		if (PEAR::isError($DBres))
			print "DB Error : ".$DBres->getDebugInfo()."<br>";
		$log = array();
		while ($DBres->fetchInto($log)){
			if($log["status"] == "OK"){
				$tab_tmp["Tok"] += $log["ctime"] - $tab_tmp["time"];
				$tab_tmp["OKnbEvent"] += 1;
			}
			if($log["status"] == "CRITICAL"){
				$tab_tmp["Tcri"] += $log["ctime"] - $tab_tmp["time"];
				$tab_tmp["CRITICALnbEvent"] += 1;
			}
			if($log["status"] == "WARNING"){
				$tab_tmp["Twarn"] += $log["ctime"] - $tab_tmp["time"];
				$tab_tmp["WARNINGnbEvent"] += 1;
			}
			if($log["status"] == "UNKNOWN"){
				$tab_tmp["Tunknown"] += $log["ctime"] - $tab_tmp["time"];
				$tab_tmp["UNKNOWNnbEvent"] += 1;
			}
			else
				$tab_tmp["Tnone"] += $log["ctime"] - $tab_tmp["time"];
			$tab_tmp["state"] = $log["status"];
			$tab_tmp["time"] = $log["ctime"];
		}
		if($tab_tmp["state"] == "OK"){
			$tab_tmp["Tok"] += $today_end - $tab_tmp["time"];
		}
		if($tab_tmp["state"] == "CRITICAL"){
			$tab_tmp["Tcri"] += $today_end - $tab_tmp["time"];
		}
		if($tab_tmp["state"] == "WARNING"){
			$tab_tmp["Twarn"] += $today_end - $tab_tmp["time"];
		}
		if($tab_tmp["state"] == "UNKNOWN"){
			$tab_tmp["Tunknown"] += $today_end - $tab_tmp["time"];
		}
		else
			$tab_tmp["Tnone"] += $today_end - $tab_tmp["time"];

		$hbase["Tok"] = $tab_tmp["Tok"];
		$hbase["OKnbEvent"] = $tab_tmp["OKnbEvent"];
		$hbase["Twarn"] = $tab_tmp["Twarn"];
		$hbase["WARNINGnbEvent"] = $tab_tmp["WARNINGnbEvent"];
		$hbase["Tunknown"] = $tab_tmp["Tunknown"];
		$hbase["UNKNOWNnbEvent"] = $tab_tmp["UNKNOWNnbEvent"];
		$hbase["Tcri"] = $tab_tmp["Tcri"];
		$hbase["CRITICALnbEvent"] = $tab_tmp["CRITICALnbEvent"];
		$hbase["Tnone"] = $tab_tmp["Tnone"];		
	}



	function getLogInDbForOneSVC(&$tab_svc_bdd, $pearDB, $host_id, $svc_id, $start_date_select, $end_date_select, $pearDBO, $today_start, $today_end){
		$tab_svc_bdd = array();
		$tab_svc_bdd["today"] = array();
		$tab_svc_bdd["resume"] = array();
		$tab_svc_bdd["resume"]["OKnbEvent"] = 0;
		$tab_svc_bdd["resume"]["WARNINGnbEvent"] = 0;
		$tab_svc_bdd["resume"]["UNKNOWNnbEvent"] = 0;
		$tab_svc_bdd["resume"]["CRITICALnbEvent"] = 0;			  	
		$tab_svc_bdd["resume"]["Tok"] = 0;
		$tab_svc_bdd["resume"]["Twarn"] = 0;
		$tab_svc_bdd["resume"]["Tunknown"] = 0;
		$tab_svc_bdd["resume"]["Tcri"] = 0;

		#
		## get log before today
		#
		$rq = 'SELECT ' .
			'service_id, ' .
			'sum(OKTimeScheduled)' .
			' as Tok,' .
			'sum(OKnbEvent) as OKnbEvent,' .
			'sum(WARNINGTimeScheduled)' .
			' as Twarn,' .
			'sum(WARNINGnbEvent) as WARNINGnbEvent,' .
			'sum(UNKNOWNTimeScheduled)' .
			' as Tunknown, ' .
			'sum(UNKNOWNnbEvent) as UNKNOWNnbEvent,' .				
			'sum(CRITICALTimeScheduled)' .
			' as Tcri, ' .
			'sum(CRITICALnbEvent) as CRITICALnbEvent, ' .
			'min(date_start) as log_date_start,' .
			'max(date_end) as log_date_end' .
			' FROM `log_archive_service` WHERE host_id = ' . $host_id  .			
			' AND service_id =  ' . $svc_id .
			' AND date_start >=  ' . ($start_date_select-1) .
			' AND date_end <= ' . ($end_date_select + 1) .
			' GROUP BY service_id';

		$res = & $pearDB->query($rq);
		if (PEAR::isError($res)){
		  die($res->getMessage());
		} else { 
		  while ($s =& $res->fetchRow()){
			$tab_svc_bdd["resume"]["OKnbEvent"] = 0 + $s["OKnbEvent"];
			$tab_svc_bdd["resume"]["WARNINGnbEvent"] = 0 + $s["WARNINGnbEvent"];
			$tab_svc_bdd["resume"]["UNKNOWNnbEvent"] = 0 + $s["UNKNOWNnbEvent"];
			$tab_svc_bdd["resume"]["CRITICALnbEvent"] = 0 + $s["CRITICALnbEvent"];			  	
			$tab_svc_bdd["resume"]["Tok"] = 0 + $s["Tok"];
			$tab_svc_bdd["resume"]["Twarn"] = 0 + $s["Twarn"];
			$tab_svc_bdd["resume"]["Tunknown"] = 0 + $s["Tunknown"];
			$tab_svc_bdd["resume"]["Tcri"] = 0 + $s["Tcri"];
			}
		  }

		#
		## get log for today (timeline)
		#
		$tab_tmp = array();
		getTodayLogForSVC(getMyHostName($host_id), getMyServiceName($svc_id), $hbase, $pearDBO, $today_start, $today_end);
		$tab_svc_bdd["today"]["OKnbEvent"] = $hbase["OKnbEvent"];
		$tab_svc_bdd["today"]["WARNINGnbEvent"] = $hbase["OKnbEvent"];
		$tab_svc_bdd["today"]["UNKNOWNnbEvent"] = $hbase["OKnbEvent"];
		$tab_svc_bdd["today"]["CRITICALnbEvent"] = $hbase["OKnbEvent"];			  	
		$tab_svc_bdd["today"]["Tok"] = $hbase["Tok"];
		$tab_svc_bdd["today"]["Twarn"] = $hbase["Twarn"];
		$tab_svc_bdd["today"]["Tunknown"] = $hbase["Tunknown"];
		$tab_svc_bdd["today"]["Tcri"] = $hbase["Tcri"];


		#
		## if today in the period
		#
		if($end_date_select > $today_start){
			$tab_svc_bdd["resume"]["OKnbEvent"] += $hbase["OKnbEvent"];
			$tab_svc_bdd["resume"]["WARNINGnbEvent"] += $hbase["WARNINGnbEvent"];
			$tab_svc_bdd["resume"]["UNKNOWNnbEvent"] += $hbase["UNKNOWNnbEvent"];
			$tab_svc_bdd["resume"]["CRITICALnbEvent"] += $hbase["CRITICALnbEvent"];			  	
			$tab_svc_bdd["resume"]["Tok"] += $hbase["Tok"];
			$tab_svc_bdd["resume"]["Twarn"] += $hbase["Twarn"];
			$tab_svc_bdd["resume"]["Tunknown"] += $hbase["Tunknown"];
			$tab_svc_bdd["resume"]["Tcri"] += $hbase["Tcri"];
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