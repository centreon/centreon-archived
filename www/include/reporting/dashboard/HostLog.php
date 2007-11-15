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
	$lcaHostByID = getLcaHostByID($pearDB);
	$lcaHoststr = getLCAHostStr($lcaHostByID["LcaHost"]);
	$lcaHostGroupstr = getLCAHGStr($lcaHostByID["LcaHostGroup"]);
	
	#
	## period selection
	#
	$period = (isset($_POST["period"])) ? $_POST["period"] : "today"; 
	$period = (isset($_GET["period"])) ? $_GET["period"] : $period;
		
	if($mhost)	{
		$end_date_select = 0;
		$start_date_select= 0;
		if($period == "customized") {
			$end = (isset($_POST["end"])) ? $_POST["end"] : NULL;
			$end = (isset($_GET["end"])) ? $_GET["end"] : $end;
			$start = (isset($_POST["start"])) ? $_POST["start"] : NULL;
			$start = (isset($_GET["start"])) ? $_GET["start"] : $start;
			$start_var = $start;
			$end_var = $end;
			getDateSelect_customized($end_date_select, $start_date_select, $start,$end);
		}
		else {
			getDateSelect_predefined($end_date_select, $start_date_select, $period);
		}
		$host_id = getMyHostID($mhost);
		$sd = $start_date_select;
		$ed = $end_date_select;

		#
		## recupere les log host en base
		#
		$hbase = array();
		$Tup = NULL;
		$Tdown = NULL;
		$Tunreach = NULL;
		$Tnone = NULL;
		getLogInDbForHost($hbase, $pearDB, $host_id, $start_date_select, $end_date_select,$pearDBO, $today_start, $today_end);
		$Tup = $hbase["Tup"];
		$Tdown = $hbase["Tdown"];
		$Tunreach = $hbase["Tunreach"];
		$Tnone = $hbase["Tnone"];
		
		$tab_svc_bdd = array();
		getLogInDbForSVC($tab_svc_bdd, $pearDB, $host_id, $start_date_select, $end_date_select,$pearDBO, $today_start, $today_end);
		$serviceList = getMyHostServices($host_id);
	}

	$today_up = 0;
	$today_down = 0;
	$today_unreachable = 0;
	$today_pending = 0;
	$today_UPnbEvent = 0;
	$today_UNREACHABLEnbEvent = 0;
	$today_DOWNnbEvent = 0;
	$tab_today = array();
	getTodayLogForHost($mhost, $tab_today, $pearDBO, $today_start, $today_end);
	$today_up += $tab_today["Tup"];
	$today_down += $tab_today["Tdown"];
	$today_unreachable += $tab_today["Tunreachable"];
	$today_pending += $tab_today["Tnone"];
	$today_UPnbEvent += $tab_today["TupNBAlert"];
	$today_UNREACHABLEnbEvent += $tab_today["TunreachableNBAlert"];
	$today_DOWNnbEvent += $tab_today["TdownNBAlert"];


	if($mhost){

	$day = date("d",time());
	$year = date("Y",time());
	$month = date("m",time());
	$startTimeOfThisDay = mktime(0, 0, 0, $month, $day, $year);

	$tab_svc_average = array();
	$tab_svc_average["PTOK"] = 0;
	$tab_svc_average["PAOK"] = 0;
	$tab_svc_average["PTW"] = 0;
	$tab_svc_average["PAW"] = 0;
	$tab_svc_average["PTU"] = 0;
	$tab_svc_average["PAU"] = 0;
	$tab_svc_average["PTC"] = 0;
	$tab_svc_average["PAC"] = 0;
	$tab_svc_average["PTN"] = 0;
	$tab_svc_average["PKTOK"] = 0;
	$tab_svc_average["PKTW"] = 0;
	$tab_svc_average["PKTU"] = 0;
	$tab_svc_average["PKTC"] = 0;
	$tab_svc_average["nb_svc"] = 0;

	$tab_hosts = array();
	$day_current_start = 0;
	$day_current_end = time() + 1;
	$time = time();
	
	$i=0;
	foreach($serviceList as $svc_id => $svc_name)
	{
		$tab_tmp = array();
		$tab_tmp["svcName"] = $svc_name;
		$tab_tmp["service_id"] = $svc_id;
		$tt = $end_date_select - $start_date_select;

		$tab_tmp["PtimeOK"] = 0;
		$tab_tmp["PtimeWARNING"] =  0;
		$tab_tmp["PtimeUNKNOWN"] = 0;
		$tab_tmp["PtimeCRITICAL"] = 0;
		$tab_tmp["PtimeNONE"] = $tt;

		if(isset($tab_svc_bdd[$svc_id]) && $tab_svc_bdd[$svc_id]){
			$tab_tmp["PtimeOK"] = round($tab_svc_bdd[$svc_id]["Tok"] / $tt *100,2);
			$tab_tmp["PtimeWARNING"] = round( $tab_svc_bdd[$svc_id]["Twarn"]/ $tt *100,2);
			$tab_tmp["PtimeUNKNOWN"] = round( $tab_svc_bdd[$svc_id]["Tunknown"]/ $tt *100,2);
			$tab_tmp["PtimeCRITICAL"] = round( $tab_svc_bdd[$svc_id]["Tcri"]/ $tt *100,2);
			$tab_tmp["PtimeNONE"] = round( ( $tt - ($tab_svc_bdd[$svc_id]["Tok"] + $tab_svc_bdd[$svc_id]["Twarn"] + $tab_svc_bdd[$svc_id]["Tunknown"] + $tab_svc_bdd[$svc_id]["Tcri"])
												 )  / $tt *100,2);
			$tmp_none = $tt - ($tab_svc_bdd[$svc_id]["Tok"] + $tab_svc_bdd[$svc_id]["Twarn"] + $tab_svc_bdd[$svc_id]["Tunknown"] + $tab_svc_bdd[$svc_id]["Tcri"]);
	
			$tab_tmp["OKnbEvent"] = isset($tab_svc_bdd[$svc_id]["OKnbEvent"]) ? $tab_svc_bdd[$svc_id]["OKnbEvent"] : 0;
			$tab_tmp["WARNINGnbEvent"] = isset($tab_svc_bdd[$svc_id]["WARNINGnbEvent"]) ? $tab_svc_bdd[$svc_id]["WARNINGnbEvent"] : 0;
			$tab_tmp["UNKNOWNnbEvent"] = isset($tab_svc_bdd[$svc_id]["UNKNOWNnbEvent"]) ? $tab_svc_bdd[$svc_id]["UNKNOWNnbEvent"] : 0;
			$tab_tmp["CRITICALnbEvent"] = isset($tab_svc_bdd[$svc_id]["CRITICALnbEvent"]) ? $tab_svc_bdd[$svc_id]["CRITICALnbEvent"] : 0;


			if( ($tt - $tmp_none) > 0){
				$tab_tmp["PktimeOK"] = round($tab_svc_bdd[$svc_id]["Tok"] / ($tt - $tmp_none) *100,2);
				$tab_tmp["PktimeWARNING"] = round( $tab_svc_bdd[$svc_id]["Twarn"]/ ($tt - $tmp_none) *100,2);
				$tab_tmp["PktimeUNKNOWN"] = round( $tab_svc_bdd[$svc_id]["Tunknown"]/ ($tt - $tmp_none) *100,2);
				$tab_tmp["PktimeCRITICAL"] = round( $tab_svc_bdd[$svc_id]["Tcri"]/ ($tt - $tmp_none) *100,2);
			}else{
				$tab_tmp["PktimeOK"] = 0;
				$tab_tmp["PktimeWARNING"] = 0;
				$tab_tmp["PktimeUNKNOWN"] = 0;
				$tab_tmp["PktimeCRITICAL"] = 0;
				
			}
	
			// les lignes suivante ne servent qu'a corriger un bug mineur correspondant a un decalage d'une seconde... 
			$tab_tmp["PtimeOK"] = number_format($tab_tmp["PtimeOK"], 1, '.', '');
			$tab_tmp["PtimeWARNING"] = number_format($tab_tmp["PtimeWARNING"], 1, '.', '');
			$tab_tmp["PtimeUNKNOWN"] = number_format($tab_tmp["PtimeUNKNOWN"], 1, '.', '');
			$tab_tmp["PtimeCRITICAL"] = number_format($tab_tmp["PtimeCRITICAL"], 1, '.', '');
			$tab_tmp["PtimeNONE"] = number_format($tab_tmp["PtimeNONE"], 1, '.', '');
			$tab_tmp["PtimeNONE"] = ($tab_tmp["PtimeNONE"] < 0.1) ? 0.0 : $tab_tmp["PtimeNONE"];
	
			$tab_tmp["PktimeOK"] = number_format($tab_tmp["PktimeOK"], 1, '.', '');
			$tab_tmp["PktimeWARNING"] = number_format($tab_tmp["PktimeWARNING"], 1, '.', '');
			$tab_tmp["PktimeUNKNOWN"] = number_format($tab_tmp["PktimeUNKNOWN"], 1, '.', '');
			$tab_tmp["PktimeCRITICAL"] = number_format($tab_tmp["PktimeCRITICAL"], 1, '.', '');
	
			//end
	
			#
			## fill average svc table
			#
			$tab_svc_average["PTOK"] += $tab_tmp["PtimeOK"];
			$tab_svc_average["PAOK"] += $tab_tmp["OKnbEvent"];
			$tab_svc_average["PTW"] += $tab_tmp["PtimeWARNING"];
			$tab_svc_average["PAW"] += $tab_tmp["WARNINGnbEvent"];
			$tab_svc_average["PTU"] += $tab_tmp["PtimeUNKNOWN"];
			$tab_svc_average["PAU"] += $tab_tmp["UNKNOWNnbEvent"];
			$tab_svc_average["PTC"] += $tab_tmp["PtimeCRITICAL"];
			$tab_svc_average["PAC"] += $tab_tmp["CRITICALnbEvent"];			
			$tab_svc_average["PTN"] += $tab_tmp["PtimeNONE"];
			$tab_svc_average["PKTOK"] += $tab_tmp["PktimeOK"];
			$tab_svc_average["PKTW"] += $tab_tmp["PktimeWARNING"];
			$tab_svc_average["PKTU"] += $tab_tmp["PktimeUNKNOWN"];
			$tab_svc_average["PKTC"] += $tab_tmp["PktimeCRITICAL"];
			$tab_svc_average["nb_svc"] += 1;
	
		}
		$tab_svc[$i++] = $tab_tmp;
	}

	#
	## calculate svc average
	#
	if($tab_svc_average["PAOK"] > 0)
	$tab_svc_average["PAOK"] = number_format($tab_svc_average["PAOK"] / $tab_svc_average["nb_svc"], 1, '.', '');
	if($tab_svc_average["PAW"] > 0)
	$tab_svc_average["PAW"] = number_format($tab_svc_average["PAW"] / $tab_svc_average["nb_svc"], 1, '.', '');
	if($tab_svc_average["PAU"] > 0)
	$tab_svc_average["PAU"] = number_format($tab_svc_average["PAU"] / $tab_svc_average["nb_svc"], 1, '.', '');
	if($tab_svc_average["PAC"] > 0)
	$tab_svc_average["PAC"] = number_format($tab_svc_average["PAC"] / $tab_svc_average["nb_svc"], 1, '.', '');


	if($tab_svc_average["PTOK"] > 0)
	$tab_svc_average["PTOK"] = number_format($tab_svc_average["PTOK"] / $tab_svc_average["nb_svc"], 3, '.', '');
	if($tab_svc_average["PTW"] > 0)
	$tab_svc_average["PTW"] = number_format($tab_svc_average["PTW"] / $tab_svc_average["nb_svc"], 3, '.', '');
	if($tab_svc_average["PTU"] > 0)
	$tab_svc_average["PTU"] = number_format($tab_svc_average["PTU"] / $tab_svc_average["nb_svc"], 3, '.', '');
	if($tab_svc_average["PTC"] > 0)
	$tab_svc_average["PTC"] = number_format($tab_svc_average["PTC"] / $tab_svc_average["nb_svc"], 3, '.', '');
	if($tab_svc_average["PTN"] > 0)
	$tab_svc_average["PTN"] = number_format($tab_svc_average["PTN"] / $tab_svc_average["nb_svc"], 3, '.', '');
	if($tab_svc_average["PKTOK"] > 0)
	$tab_svc_average["PKTOK"] = number_format($tab_svc_average["PKTOK"] / $tab_svc_average["nb_svc"], 3, '.', '');
	if($tab_svc_average["PKTW"] > 0)
	$tab_svc_average["PKTW"] = number_format($tab_svc_average["PKTW"] / $tab_svc_average["nb_svc"], 3, '.', '');
	if($tab_svc_average["PKTU"] > 0)
	$tab_svc_average["PKTU"] = number_format($tab_svc_average["PKTU"] / $tab_svc_average["nb_svc"], 3, '.', '');
	if($tab_svc_average["PKTC"] > 0)
	$tab_svc_average["PKTC"] = number_format($tab_svc_average["PKTC"] / $tab_svc_average["nb_svc"], 3, '.', '');


	#
	## calculate host %
	#
	$tab_resume = array();
	$tab = array();
	$timeTOTAL = $end_date_select - $start_date_select;

	$Tnone = $timeTOTAL - ($Tup + $Tdown + $Tunreach);
	if($Tnone <= 1)
	$Tnone = 0;

	$tab["state"] = $lang["m_UpTitle"];
	$tab["time"] = Duration::toString($Tup);
	$tab["timestamp"] = $Tup;
	$tab["pourcentTime"] = round($Tup/($timeTOTAL+1)*100,2) ;
	$tab["pourcentkTime"] = round($Tup/($timeTOTAL-$Tnone+1)*100,2). "%";
	$tab["nbAlert"] = $hbase["TupNBAlert"];
	$tab_resume[0] = $tab;

	$tab["state"] = $lang["m_DownTitle"];
	$tab["time"] = Duration::toString($Tdown);
	$tab["timestamp"] = $Tdown;
	$tab["pourcentTime"] = round($Tdown/$timeTOTAL*100,2);
	$tab["pourcentkTime"] = round($Tdown/($timeTOTAL-$Tnone+1)*100,2)."%";
	$tab["nbAlert"] = $hbase["TdownNBAlert"];
	
	$tab_resume[1] = $tab;

	$tab["state"] = $lang["m_UnreachableTitle"];
	$tab["time"] = Duration::toString($Tunreach);
	$tab["timestamp"] = $Tunreach;
	$tab["pourcentTime"] = round($Tunreach/$timeTOTAL*100,2);
	$tab["pourcentkTime"] = round($Tunreach/($timeTOTAL-$Tnone+1)*100,2)."%";
	$tab["nbAlert"] = $hbase["TunreachableNBAlert"];
	$tab_resume[2] = $tab;


	$tab["state"] = $lang["m_PendingTitle"];
	$tab["time"] = Duration::toString($Tnone);
	$tab["timestamp"] = $Tnone;
	$tab["pourcentTime"] = round($Tnone/$timeTOTAL*100,2);
	$tab["pourcentkTime"] = null;
	$tab["nbAlert"] = "";	
	$tab_resume[3] = $tab;

//	$tpl->assign('infosTitle', $lang["m_duration"] . Duration::toString($end_date_select - $start_date_select));

	$start_date_select = date("d/m/Y (G:i:s)", $start_date_select);
	$end_date_select_save_timestamp =  $end_date_select;
	$end_date_select =  date("d/m/Y (G:i:s)", $end_date_select);

//	$tpl->assign('host_name', $mhost);
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

	$totalAlert = $hbase["TunreachableNBAlert"] + $hbase["TdownNBAlert"] + $hbase["TupNBAlert"];

		}## end of period requirement



	if(isset($host_id)) {
	/* historical daily report*/
	$tab_report = array();

	$tab_report[date("d/m/Y", $today_start)]["duration"] = $tt;

	# For today in timeline
	$tt = 0 + ($today_end - $today_start);
	$tab_report[date("d/m/Y", $today_start)]["duration"] = Duration::toString($tt);
	$tab_report[date("d/m/Y", $today_start)]["uptime"] = Duration::toString($today_up);
	$tab_report[date("d/m/Y", $today_start)]["downtime"] = Duration::toString($today_down);
	$tab_report[date("d/m/Y", $today_start)]["unreachalbetime"] = Duration::toString($today_unreachable);
	$tab_report[date("d/m/Y", $today_start)]["undeterminatetime"] = Duration::toString($today_pending);

	$today_pending = $tt - ($today_down + $today_up + $today_unreachable);
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

	$rq = 'SELECT ' .
	' * FROM `log_archive_host` WHERE host_id = ' . $host_id .
	' AND date_start >= ' . $sd . ' AND date_end <= ' . $ed .
	' order by date_start desc';
		
	$res = & $pearDB->query($rq);
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
		$tab_tmp ["pup"] = 0+$pup;
		$tab_tmp ["pdown"] = 0+$pdown;
		$tab_tmp ["punreach"] = 0+$punreach;
		$tab_tmp ["pundet"] = 0+$pundet;

		$tab_tmp ["UPnbEvent"] = $h["UPnbEvent"];
		$tab_tmp ["DOWNnbEvent"] = $h["DOWNnbEvent"];
		$tab_tmp ["UNREACHABLEnbEvent"] = $h["UNREACHABLEnbEvent"];


		$tab_report[date("d/m/Y", $start)] = $tab_tmp;
	  }
}
?>