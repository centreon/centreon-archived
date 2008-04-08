<?php
/**
Centreon is developped with GPL Licence 2.0 :
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


	#
	## init
	#
	$totalAlert = 0;
	$day = date("d",time());
	$year = date("Y",time());
	$month = date("m",time());
	$today_start = mktime(0, 0, 0, $month, $day, $year);
	$today_end = time();
	$tt = 0;
	$start_date_select = 0;
	$end_date_select = 0;

	require_once('simple-func.php');
	require_once('reporting-func.php');

	# LCA
	$lcaHostByName = getLcaHostByName($pearDB);
	$lcaHGByName = getLcaHostByName($pearDB);
	$lcaHostByID = getLcaHostByID($pearDB);
	$lcaHoststr = getLCAHostStr($lcaHostByID["LcaHost"]);
	$lcaHostGroupstr = getLCAHGStr($lcaHostByID["LcaHostGroup"]);

	#
	## Selectioned ?
	#		
	isset ($_GET["hostgroup"]) ? $mhostgroup = $_GET["hostgroup"] : $mhostgroup = NULL;
	isset ($_POST["hostgroup"]) ? $mhostgroup = $_POST["hostgroup"] : $mhostgroup = $mhostgroup;

	#
	## period selection
	#
	$period = (isset($_POST["period"])) ? $_POST["period"] : "today"; 
	$period = (isset($_GET["period"])) ? $_GET["period"] : $period;

	if($mhostgroup)	{
		$end_date_select = 0;
		$start_date_select= 0;
		if($period == "customized") {
			$end = (isset($_POST["end"])) ? $_POST["end"] : NULL;
			$end = (isset($_GET["end"])) ? $_GET["end"] : $end;
			$start = (isset($_POST["start"])) ? $_POST["start"] : NULL;
			$start = (isset($_GET["start"])) ? $_GET["start"] : $start;
			getDateSelect_customized($end_date_select, $start_date_select, $start,$end);
		}
		else {
			getDateSelect_predefined($end_date_select, $start_date_select, $period);
		}
		$hostgroup_id = getMyHostGroupID($mhostgroup);
		$sd = $start_date_select;
		$ed = $end_date_select;
		#
		## database log
		#
		$hbase = array();
		$Tup = NULL;
		$Tdown = NULL;
		$Tunreach = NULL;
		$Tnone = NULL;
		getLogInDbForHostGroup($hbase, $pearDB, $pearDBO, $hostgroup_id, $start_date_select, $end_date_select, $today_start, $today_end);
	}
	
	#
	## ressource selected
	#
	$today_up = 0;
	$today_down = 0;
	$today_unreachable = 0;
	$today_UPnbEvent = 0;
	$today_UNREACHABLEnbEvent = 0;
	$today_DOWNnbEvent = 0;
	
	if($mhostgroup){
		#
		## today log for xml timeline
		#
		$today_up = 0 + $hbase["average"]["today"]["Tup"];
		$today_down = 0 + $hbase["average"]["today"]["Tdown"];
		$today_unreachable = 0 + $hbase["average"]["today"]["Tunreachable"];
	
		$today_UPnbEvent = 0 + $hbase["average"]["today"]["TupNBAlert"];
		$today_UNREACHABLEnbEvent = 0 + $hbase["average"]["today"]["TunreachableNBAlert"];
		$today_DOWNnbEvent = 0 + $hbase["average"]["today"]["TdownNBAlert"];

		$tab_log = array();
		$day = date("d",time());
		$year = date("Y",time());
		$month = date("m",time());
		$startTimeOfThisDay = mktime(0, 0, 0, $month, $day, $year);
		$tab_host_list_average = array();
		$tab_host_list_average["PTUP"] = 0;
		$tab_host_list_average["PAUP"] = 0;
		$tab_host_list_average["PTD"] = 0;
		$tab_host_list_average["PAD"] = 0;
		$tab_host_list_average["PTUR"] = 0;
		$tab_host_list_average["PAUR"] = 0;
		$tab_host_list_average["PTU"] = 0;
		$tab_host_list_average["PKTup"] = 0;
		$tab_host_list_average["PKTd"] = 0;
		$tab_host_list_average["PKTu"] = 0;
		$tab_host_list_average["nb_host"] = 0;	
		$tab_hosts = array();	
		$day_current_start = 0;
		$day_current_end = time() + 1;
		$time = time();

		#
		## calculate resume
		#
		$tab_resume = array();
		$tab = array();
		$timeTOTAL = $end_date_select - $start_date_select;	
		$Tup = $hbase["average"]["Tup"];
		$Tdown = $hbase["average"]["Tdown"];
		$Tunreach = $hbase["average"]["Tunreachable"];
		$Tnone = $hbase["average"]["Tnone"];
		$Tnone = $timeTOTAL - ($Tup + $Tdown + $Tunreach);
		if($Tnone <= 1)
		$Tnone = 0;	
		$tab["state"] = _("Up");
		$tab["time"] = Duration::toString($Tup);
		$tab["timestamp"] = $Tup;
		$tab["pourcentTime"] = round($Tup/($timeTOTAL+1)*100,2) ;
		$tab["pourcentkTime"] = round($Tup/($timeTOTAL-$Tnone+1)*100,2). "%";
		$tab["nbAlert"] = $hbase["average"]["TupNBAlert"];
	//	$tab["style"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_up"]."'";
		$tab_resume[0] = $tab;
		$tab["state"] = _("Down");
		$tab["time"] = Duration::toString($Tdown);
		$tab["timestamp"] = $Tdown;
		$tab["pourcentTime"] = round($Tdown/$timeTOTAL*100,2);
		$tab["pourcentkTime"] = round($Tdown/($timeTOTAL-$Tnone+1)*100,2)."%";
		$tab["nbAlert"] = $hbase["average"]["TdownNBAlert"];
//		$tab["style"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_down"]."'";
		$tab_resume[1] = $tab;
		$tab["state"] = _("Unreachable");
		$tab["time"] = Duration::toString($Tunreach);
		$tab["timestamp"] = $Tunreach;
		$tab["pourcentTime"] = round($Tunreach/$timeTOTAL*100,2);
		$tab["pourcentkTime"] = round($Tunreach/($timeTOTAL-$Tnone+1)*100,2)."%";
		$tab["nbAlert"] = $hbase["average"]["TunreachableNBAlert"];
//		$tab["style"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_unreachable"]."'";
		$tab_resume[2] = $tab;
		$tab["state"] = _("Undetermined");
		$tab["time"] = Duration::toString($Tnone);
		$tab["timestamp"] = $Tnone;
		$tab["pourcentTime"] = round($Tnone/$timeTOTAL*100,2);
		$tab["pourcentkTime"] = null;
		$tab["nbAlert"] = "";
//		$tab["style"] = "class='ListColCenter' style='background:#cccccc'";
		$tab_resume[3] = $tab;


		#
		## calculate tablist
		#
		$i=0;
		foreach($hbase as $host_id => $tab)
		{
			if($host_id != "average"){
				$tab_tmp = array();
				$tab_tmp["hostName"] = getMyHostName($host_id);
				$tt = $end_date_select - $start_date_select;
				$tab_tmp["PtimeUP"] = round($tab["Tup"] / $tt *100,2);
				$tab_tmp["PtimeDOWN"] = round( $tab["Tdown"]/ $tt *100,2);
				$tab_tmp["PtimeUNREACHABLE"] = round( $tab["Tunreachable"]/ $tt *100,2);
				$tab_tmp["PtimeUNDETERMINATED"] = round( ( $tt - ($tab["Tup"] + $tab["Tdown"] + $tab["Tunreachable"])													 )  / $tt *100,2);
				$tmp_none = $tt - ($tab["Tup"] + $tab["Tdown"] + $tab["Tunreachable"]);
				$tab_tmp["UPnbEvent"] = isset($tab["TupNBAlert"]) ? $tab["TupNBAlert"] : 0;
				$tab_tmp["DOWNnbEvent"] = isset($tab["TdownNBAlert"]) ? $tab["TdownNBAlert"] : 0;
				$tab_tmp["UNREACHABLEnbEvent"] = isset($tab["TunreachableNBAlert"]) ? $tab["TunreachableNBAlert"] : 0;
				$tab_tmp["PktimeUP"] = $tab["Tup"] ? round($tab["Tup"] / ($tt - $tmp_none) *100,2): 0;
				$tab_tmp["PktimeDOWN"] = $tab["Tdown"] ? round( $tab["Tdown"]/ ($tt - $tmp_none) *100,2):0;
				$tab_tmp["PktimeUNREACHABLE"] =  $tab["Tunreachable"] ? round( $tab["Tunreachable"]/ ($tt - $tmp_none) *100,2):0;
				$tab_tmp["PtimeUP"] = number_format($tab_tmp["PtimeUP"], 1, '.', '');
				$tab_tmp["PtimeDOWN"] = number_format($tab_tmp["PtimeDOWN"], 1, '.', '');
				$tab_tmp["PtimeUNREACHABLE"] = number_format($tab_tmp["PtimeUNREACHABLE"], 1, '.', '');
				$tab_tmp["PtimeUNDETERMINATED"] = number_format($tab_tmp["PtimeUNDETERMINATED"], 1, '.', '');
				$tab_tmp["PtimeUNDETERMINATED"] = ($tab_tmp["PtimeUNDETERMINATED"] < 0.1) ? 0.0 : $tab_tmp["PtimeUNDETERMINATED"];
				$tab_tmp["PktimeUP"] = number_format($tab_tmp["PktimeUP"], 1, '.', '');
				$tab_tmp["PktimeDOWN"] = number_format($tab_tmp["PktimeDOWN"], 1, '.', '');
				$tab_tmp["PktimeUNREACHABLE"] = number_format($tab_tmp["PktimeUNREACHABLE"], 1, '.', '');
	
				#
				## fill average svc table
				#
				$tab_host_list_average["PTUP"] += $tab_tmp["PtimeUP"];
				$tab_host_list_average["PAUP"] += $tab_tmp["UPnbEvent"];
				$tab_host_list_average["PTD"] += $tab_tmp["PtimeDOWN"];
				$tab_host_list_average["PAD"] += $tab_tmp["DOWNnbEvent"];
				$tab_host_list_average["PTUR"] += $tab_tmp["PtimeUNREACHABLE"];
				$tab_host_list_average["PAUR"] += $tab_tmp["UNREACHABLEnbEvent"];
				$tab_host_list_average["PTU"] += $tab_tmp["PtimeUNDETERMINATED"];
				$tab_host_list_average["PKTup"] += $tab_tmp["PktimeUP"];
				$tab_host_list_average["PKTd"] += $tab_tmp["PktimeDOWN"];
				$tab_host_list_average["PKTu"] += $tab_tmp["PktimeUNREACHABLE"];
				$tab_host_list_average["nb_host"] += 1;
				$tab_host[$i++] = $tab_tmp;
			}
		}

		#
		## calculate svc average
		#
		# Alert
		if($tab_host_list_average["PAUP"] > 0)
		$tab_host_list_average["PAUP"] = number_format($tab_host_list_average["PAUP"] / $tab_host_list_average["nb_host"], 1, '.', '');
		if($tab_host_list_average["PAD"] > 0)
		$tab_host_list_average["PAD"] = number_format($tab_host_list_average["PAD"] / $tab_host_list_average["nb_host"], 1, '.', '');
		if($tab_host_list_average["PAUR"] > 0)
		$tab_host_list_average["PAUR"] = number_format($tab_host_list_average["PAUR"] / $tab_host_list_average["nb_host"], 1, '.', '');
		# Time
		if($tab_host_list_average["PTUP"] > 0)
		$tab_host_list_average["PTUP"] = number_format($tab_host_list_average["PTUP"] / $tab_host_list_average["nb_host"], 3, '.', '');
		if($tab_host_list_average["PTD"] > 0)
		$tab_host_list_average["PTD"] = number_format($tab_host_list_average["PTD"] / $tab_host_list_average["nb_host"], 3, '.', '');
		if($tab_host_list_average["PTUR"] > 0)
		$tab_host_list_average["PTUR"] = number_format($tab_host_list_average["PTUR"] / $tab_host_list_average["nb_host"], 3, '.', '');
		if($tab_host_list_average["PTU"] > 0)
		$tab_host_list_average["PTU"] = number_format($tab_host_list_average["PTU"] / $tab_host_list_average["nb_host"], 3, '.', '');
		# %
		if($tab_host_list_average["PKTup"] > 0)
		$tab_host_list_average["PKTup"] = number_format($tab_host_list_average["PKTup"] / $tab_host_list_average["nb_host"], 3, '.', '');
		if($tab_host_list_average["PKTd"] > 0)
		$tab_host_list_average["PKTd"] = number_format($tab_host_list_average["PKTd"] / $tab_host_list_average["nb_host"], 3, '.', '');
		if($tab_host_list_average["PKTu"] > 0)
		$tab_host_list_average["PKTu"] = number_format($tab_host_list_average["PKTu"] / $tab_host_list_average["nb_host"], 3, '.', '');

		$start_date_select = date("d/m/Y (G:i:s)", $start_date_select);
		$end_date_select_save_timestamp =  $end_date_select;
		$end_date_select =  date("d/m/Y (G:i:s)", $end_date_select);
		$status = "";
		$totalTime = 0;
		$totalpTime = 0;
		$totalpkTime = 0;
	
		foreach ($tab_resume  as $tb){
			if($tb["pourcentTime"] >= 0)
				$status .= "&value[".$tb["state"]."]=".$tb["pourcentTime"];
			$totalTime += $tb["timestamp"];
			$totalpTime += $tb["pourcentTime"];
			$totalpkTime += $tb["pourcentkTime"];
		}
		$totalAlert = $hbase["average"]["TunreachableNBAlert"] + $hbase["average"]["TdownNBAlert"] + $hbase["average"]["TupNBAlert"];
	}

	# For today in timeline
	$tt = 0 + ($today_end - $today_start);
	$today_pending = $tt - ($today_down + $today_up + $today_unreachable);

	$tab_report[date("d/m/Y", $today_start)]["duration"] = Duration::toString($tt);
	$tab_report[date("d/m/Y", $today_start)]["uptime"] = Duration::toString($today_up);
	$tab_report[date("d/m/Y", $today_start)]["downtime"] = Duration::toString($today_down);
	$tab_report[date("d/m/Y", $today_start)]["unreachalbetime"] = Duration::toString($today_unreachable);
	$tab_report[date("d/m/Y", $today_start)]["undeterminatetime"] = Duration::toString($today_pending);

	$today_pending = round(($today_pending/$tt *100),2);
	$today_up = ($today_up <= 0) ? 0 : round($today_up / $tt *100,2);
	$today_down = ($today_down <= 0) ? 0 : round($today_down / $tt *100,2);
	$today_unreachable = ($today_unreachable <= 0) ? 0 : round($today_unreachable / $tt *100,2);
	$today_pending = ($today_pending < 0.1) ? "0" : $today_pending;

	$tab_report[date("d/m/Y", $today_start)]["pup"] = $today_up;
	$tab_report[date("d/m/Y", $today_start)]["pdown"] = $today_down;
	$tab_report[date("d/m/Y", $today_start)]["punreach"] = $today_unreachable;
	$tab_report[date("d/m/Y", $today_start)]["pundet"] = $today_pending;
	$tab_report[date("d/m/Y", $today_start)]["UPnbEvent"] = $today_UPnbEvent;
	$tab_report[date("d/m/Y", $today_start)]["DOWNnbEvent"] = $today_DOWNnbEvent;
	$tab_report[date("d/m/Y", $today_start)]["UNREACHABLEnbEvent"] = $today_UNREACHABLEnbEvent;


	$today_pending = $tt - ($today_down + $today_up + $today_unreachable);
	$today_pending = round(($today_pending/$tt *100),2);
	$today_up = ($today_up <= 0) ? 0 : round($today_up / $tt *100,2);
	$today_down = ($today_down <= 0) ? 0 : round($today_down / $tt *100,2);
	$today_unreachable = ($today_unreachable <= 0) ? 0 : round($today_unreachable / $tt *100,2);
	$today_pending = ($today_pending < 0.1) ? "0" : $today_pending;


if($mhostgroup){
		$rq = "SELECT " .
				"date_start, date_end, " .
				"avg( `UPTimeScheduled` ) as 'UPTimeScheduled', " .
				"avg( `UPnbEvent` ) as 'UPnbEvent', " .
				"avg( `DOWNTimeScheduled` ) as 'DOWNTimeScheduled', " .
				"avg( `DOWNnbEvent` ) as 'DOWNnbEvent', " .
				"avg( `UNREACHABLETimeScheduled` ) as 'UNREACHABLETimeScheduled', " .
				"avg( `UNREACHABLEnbEvent` ) as 'UNREACHABLEnbEvent' " .
				"FROM `log_archive_host` WHERE `date_start` >= " . $sd . " AND `date_end` <= " . $ed .
				" AND `host_id` IN (" .
				"SELECT host_host_id FROM `hostgroup_relation` WHERE `hostgroup_hg_id` = '" . $hostgroup_id ."') group by date_end, date_start order by date_start desc";

		$res = & $pearDB->query($rq);
		//$tab_report = array();
		  while ($h =& $res->fetchRow()) {

			$uptime = $h["UPTimeScheduled"];
			$downtime = $h["DOWNTimeScheduled"];
			$unreachalbetime = $h["UNREACHABLETimeScheduled"];
			$tt = 0 + ($h["date_end"] - $h["date_start"]);
			if(($uptime + $downtime + $unreachalbetime) < $tt)
				$undeterminatetime = 0 + $tt - ($uptime + $downtime + $unreachalbetime);
			else
			$undeterminatetime = 0;
			if($unreachalbetime > 0)
			$punreach = 0 +round(($unreachalbetime / $tt * 100),2);
			else
			$punreach = "0.00";
			if($uptime > 0)
			$pup = 0 +round(($uptime / $tt * 100),2);
			else
			$pup = "0.00";
			if($downtime > 0)
			$pdown = 0 +round(($downtime / $tt * 100),2);
			else
			$pdown = "0.00";
			if($undeterminatetime > 0)
			$pundet = 0 +round(($undeterminatetime / $tt * 100),2);
			else
			$pundet = "0.00";
			$t = 0 + ($h["date_end"] - $h["date_start"]);
			$t = round(($t - ($t * 0.11574074074)),2);
			$start = $h["date_start"] + 5000;


			$tab_tmp = array();
			$tab_tmp ["duration"] = Duration::toString($tt) ? Duration::toString($tt) : 0;
			$tab_tmp ["uptime"] = Duration::toString($uptime) ? Duration::toString($uptime) : 0;
			$tab_tmp ["downtime"] = Duration::toString($downtime) ? Duration::toString($downtime) : 0;
			$tab_tmp ["unreachalbetime"] = Duration::toString($unreachalbetime) ? Duration::toString($unreachalbetime) : 0;
			$tab_tmp ["undeterminatetime"] = Duration::toString($undeterminatetime) ? Duration::toString($undeterminatetime) : 0 ;
			$tab_tmp ["pup"] = 0 + $pup;
			$tab_tmp ["pdown"] = 0 + $pdown;
			$tab_tmp ["punreach"] = 0 + $punreach;
			$tab_tmp ["pundet"] = 0 + $pundet;
	
			$tab_tmp ["UPnbEvent"] = $h["UPnbEvent"];
			$tab_tmp ["DOWNnbEvent"] = $h["DOWNnbEvent"];
			$tab_tmp ["UNREACHABLEnbEvent"] = $h["UNREACHABLEnbEvent"];
	
			$tab_report[date("d/m/Y", $start)] = $tab_tmp;

	  }
}

?>