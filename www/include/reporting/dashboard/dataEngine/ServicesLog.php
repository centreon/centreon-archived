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
 * For information : contact@oreon-project.org
 */

	$day = date("d",time());
	$year = date("Y",time());
	$month = date("m",time());
	$today_start = mktime(0, 0, 0, $month, $day, $year);
	$today_end = time();

	$today_ok = 0;
	$today_warning = 0;
	$today_unknown = 0;
	$today_critical = 0;

	$start_date_select = 0;
	$end_date_select = 0;

	$tab_svc = array();

	require_once($centreon_path."www/include/reporting/dashboard/common-Func.php");
	require_once($centreon_path."www/include/reporting/dashboard/DB-Func.php");

	# LCA
	if (isset($is_admin) && !$is_admin){
		$lcaHostByName = getLcaHostByName($pearDB);
		$lcaHostByID = getLcaHostByID($pearDB);
		$lcaHoststr = getLCAHostStr($lcaHostByID["LcaHost"]);
		$lcaHostGroupstr = getLCAHGStr($lcaHostByID["LcaHostGroup"]);
	}

	isset ($_GET["host"]) ? $mhost = $_GET["host"] : $mhost = NULL;
	isset ($_POST["host"]) ? $mhost = $_POST["host"] : $mhost = $mhost;

	isset ($_GET["service"]) ? $mservice = $_GET["service"] : $mservice = NULL;
	isset ($_POST["service"]) ? $mservice = $_POST["service"] : $mservice = $mservice;

	$service_name = getMyServiceName($mservice);

	#
	## period selection
	#
	$period = (isset($_POST["period"])) ? $_POST["period"] : "today"; 
	$period = (isset($_GET["period"])) ? $_GET["period"] : $period;

	if ($mhost)	{
		$end_date_select = 0;
		$start_date_select= 0;
		
		if ($period == "customized") {
			$end = (isset($_POST["end"])) ? $_POST["end"] : NULL;
			$end = (isset($_GET["end"])) ? $_GET["end"] : $end;
			$start = (isset($_POST["start"])) ? $_POST["start"] : NULL;
			$start = (isset($_GET["start"])) ? $_GET["start"] : $start;			
			getDateSelect_customized($end_date_select, $start_date_select, $start,$end);
		} else {
			getDateSelect_predefined($end_date_select, $start_date_select, $period);
		}
		$host_id = getMyHostID($mhost);
		$sd = $start_date_select;
		$ed = $end_date_select;

		/*
		 * database log
		 */
		 
		$Tup = NULL;
		$Tdown = NULL;
		$Tunreach = NULL;
		$Tnone = NULL;
		getLogInDbForHost($hbase, $pearDB, $host_id, $start_date_select, $end_date_select,$pearDBO, $today_start, $today_end);

		$Tup = $hbase["Tup"];
		$Tdown = $hbase["Tnone"];
		$Tunreach = $hbase["Tunreach"];
		$Tnone = $hbase["Tnone"];
		
		$svc_id = $mservice;
		$tab_svc_bdd = array();
		getLogInDbForOneSVC($tab_svc_bdd, $pearDB, $host_id, $svc_id, $start_date_select, $end_date_select, $pearDBO, $today_start, $today_end);			
		$tab_svc["svcName"] = getMyServiceName($mservice);
	}

	/*
	 * fourchette de temps
	 */

	$periodList = array();
	$periodList[""] = "";
	$periodList["today"] = _("Today");
	$periodList["yesterday"] = _("Yesterday");
	$periodList["thisweek"] = _("This Week");
	$periodList["last7days"] = _("Last 7 Days");
	$periodList["thismonth"] = _("This Month");
	$periodList["last30days"] = _("Last 30 Days");
	$periodList["lastmonth"] = _("Last Month");
	$periodList["thisyear"] = _("This Year");
	$periodList["lastyear"] = _("Last Year");
	$periodList["customized"] = _("Customized");

	
	if ($mhost){
		$tab_log = array();
		$tab_svc = array();
		$day = date("d",time());
		$year = date("Y",time());
		$month = date("m",time());
		$startTimeOfThisDay = mktime(0, 0, 0, $month, $day, $year);

		$tab_hosts = array();	
		$day_current_start = 0;
		$day_current_end = time() + 1;
		$tab_svc = array();
		$today_ok = $tab_svc_bdd["today"]["Tok"];
		$today_warning = $tab_svc_bdd["today"]["Twarn"];
		$today_unknown = $tab_svc_bdd["today"]["Tunknown"];
		$today_uncritical = $tab_svc_bdd["today"]["Tcri"];
		#
		$tt = $end_date_select - $start_date_select;
		$tab_svc["timeOK"] = (isset($tab_svc_bdd["resume"]["Tok"])) ? $tab_svc_bdd["resume"]["Tok"] : 0;
		$tab_svc["timeWARNING"] = (isset($tab_svc_bdd["resume"]["Twarn"])) ? $tab_svc_bdd["resume"]["Twarn"] : 0;
		$tab_svc["timeUNKNOWN"] = (isset($tab_svc_bdd["resume"]["Tunknown"])) ? $tab_svc_bdd["resume"]["Tunknown"] : 0;
		$tab_svc["timeCRITICAL"] = (isset($tab_svc_bdd["resume"]["Tcri"])) ? $tab_svc_bdd["resume"]["Tcri"] : 0;
		$tab_svc["timeNONE"] = $tt - ($tab_svc["timeOK"] + $tab_svc["timeWARNING"] + $tab_svc["timeUNKNOWN"] + $tab_svc["timeCRITICAL"]);
		#
		$tab_svc["OKnbEvent"] = isset($tab_svc_bdd["resume"]["OKnbEvent"]) ? $tab_svc_bdd["resume"]["OKnbEvent"] : 0;
		$tab_svc["WARNINGnbEvent"] = isset($tab_svc_bdd["resume"]["WARNINGnbEvent"]) ? $tab_svc_bdd["resume"]["WARNINGnbEvent"] : 0;
		$tab_svc["UNKNOWNnbEvent"] = isset($tab_svc_bdd["resume"]["UNKNOWNnbEvent"]) ? $tab_svc_bdd["resume"]["UNKNOWNnbEvent"] : 0;
		$tab_svc["CRITICALnbEvent"] = isset($tab_svc_bdd["resume"]["CRITICALnbEvent"]) ? $tab_svc_bdd["resume"]["CRITICALnbEvent"] : 0;
		#
		$tab_svc["PtimeOK"] = round($tab_svc["timeOK"] / $tt *100,3);
		$tab_svc["PtimeWARNING"] = round( $tab_svc["timeWARNING"]/ $tt *100,3);
		$tab_svc["PtimeUNKNOWN"] = round( $tab_svc["timeUNKNOWN"]/ $tt *100,3);
		$tab_svc["PtimeCRITICAL"] = round( $tab_svc["timeCRITICAL"]/ $tt *100,3);
		$tab_svc["PtimeNONE"] = round(($tab_svc["timeNONE"])  / $tt *100,3);
		#
		if ($tt != $tab_svc["timeNONE"]){
			$tab_svc["PktimeOK"] = round($tab_svc["timeOK"] / ($tt-$tab_svc["timeNONE"]) *100,3);
			$tab_svc["PktimeWARNING"] = round( $tab_svc["timeWARNING"]/ ($tt-$tab_svc["timeNONE"]) *100,3);
			$tab_svc["PktimeUNKNOWN"] = round( $tab_svc["timeUNKNOWN"]/ ($tt-$tab_svc["timeNONE"]) *100,3);
			$tab_svc["PktimeCRITICAL"] = round( $tab_svc["timeCRITICAL"]/ ($tt-$tab_svc["timeNONE"]) *100,3);
		} else {
			$tab_svc["PktimeOK"] = round(0,3);
			$tab_svc["PktimeWARNING"] = round(0,3);
			$tab_svc["PktimeUNKNOWN"] = round(0,3);
			$tab_svc["PktimeCRITICAL"] = round(0,3);
		}			
		# les lignes suivante ne servent qu'a corriger un bug mineur correspondant a un decalage d'une seconde...
		$tab_svc["PtimeOK"] = number_format($tab_svc["PtimeOK"], 1, '.', '');
		$tab_svc["PtimeWARNING"] = number_format($tab_svc["PtimeWARNING"], 1, '.', '');
		$tab_svc["PtimeUNKNOWN"] = number_format($tab_svc["PtimeUNKNOWN"], 1, '.', '');
		$tab_svc["PtimeCRITICAL"] = number_format($tab_svc["PtimeCRITICAL"], 1, '.', '');
		$tab_svc["PtimeNONE"] = number_format($tab_svc["PtimeNONE"], 1, '.', '');	
		$tab_svc["PtimeNONE"] = ($tab_svc["PtimeNONE"] < 0.1) ? "0.0" : $tab_svc["PtimeNONE"];
		#
		$tab_svc["PktimeOK"] = number_format($tab_svc["PktimeOK"], 1, '.', '');
		$tab_svc["PktimeWARNING"] = number_format($tab_svc["PktimeWARNING"], 1, '.', '');
		$tab_svc["PktimeUNKNOWN"] = number_format($tab_svc["PktimeUNKNOWN"], 1, '.', '');
		$tab_svc["PktimeCRITICAL"] = number_format($tab_svc["PktimeCRITICAL"], 1, '.', '');
		#end
	}	

	## calculate service  resume
	$tab_resume = array();
	$tab = array();	
	if ($mservice && $mhost){
		$tab["state"] = _("OK");
		$tab["timestamp"] = $tab_svc["timeOK"];
		$tab["time"] = Duration::toString($tab_svc["timeOK"]);
		$tab["pourcentTime"] = $tab_svc["PtimeOK"];
		$tab["pourcentkTime"] = $tab_svc["PktimeOK"]." %";
		$tab["nbAlert"] = $tab_svc["OKnbEvent"];
		$today_OKnbEvent = $tab_svc["OKnbEvent"];
		$tab_resume[0] = $tab;

		$tab["state"] = _("Critical");
		$tab["time"] = Duration::toString($tab_svc["timeCRITICAL"]);
		$tab["timestamp"] = $tab_svc["timeCRITICAL"];
		$tab["pourcentTime"] = $tab_svc["PtimeCRITICAL"];
		$tab["pourcentkTime"] = $tab_svc["PktimeCRITICAL"]." %";
		$tab["nbAlert"] = $tab_svc["CRITICALnbEvent"];
		$today_CRITICALnbEvent = $tab_svc["CRITICALnbEvent"];
		$tab_resume[1] = $tab;
		
		$tab["state"] = _("Warning");
		$tab["time"] = Duration::toString($tab_svc["timeWARNING"]);
		$tab["timestamp"] = $tab_svc["timeWARNING"];
		$tab["pourcentTime"] = $tab_svc["PtimeWARNING"];
		$tab["pourcentkTime"] = $tab_svc["PktimeWARNING"]." %";
		$tab["nbAlert"] = $tab_svc["WARNINGnbEvent"];
		$today_WARNINGnbEvent = $tab_svc["WARNINGnbEvent"];
		$tab_resume[2] = $tab;
		
		$tab["state"] = _("Unknown");
		$tab["time"] = Duration::toString($tab_svc["timeUNKNOWN"]);
		$tab["timestamp"] = $tab_svc["timeUNKNOWN"];
		$tab["pourcentTime"] = $tab_svc["PtimeUNKNOWN"];
		$tab["pourcentkTime"] = $tab_svc["PktimeUNKNOWN"]." %";
		$tab["nbAlert"] = $tab_svc["UNKNOWNnbEvent"];
		$today_UNKNOWNnbEvent = $tab_svc["UNKNOWNnbEvent"];
		$tab_resume[3] = $tab;
		
		$tab["state"] = _("Undetermined");
		$tab_svc["timeNONE"] = $tab_svc["timeNONE"] < 0 ? 0 : $tab_svc["timeNONE"];
		$tab["time"] = Duration::toString($tab_svc["timeNONE"]);
		$tab["timestamp"] = $tab_svc["timeNONE"];
		$tab["pourcentTime"] = $tab_svc["PtimeNONE"];
		$tab["pourcentkTime"] = null;
		$tab["nbAlert"] = "";
		$tab_resume[4] = $tab;
	}

	$start_date_select = date("d/m/Y (G:i:s)", $start_date_select);
	$end_date_select =  date("d/m/Y (G:i:s)", $end_date_select);

	$status = "";
	$totalTime = 0;
	$totalpTime = 0;
	$totalpkTime = 0;
	foreach ($tab_resume  as $tb){
		if ($tb["pourcentTime"] >= 0)
			$status .= "&value[".$tb["state"]."]=".$tb["pourcentTime"];
		$totalTime += $tb["timestamp"];
		$totalpTime += $tb["pourcentTime"];
		$totalpkTime += $tb["pourcentkTime"];
	}

	$totalAlert = $tab_svc["OKnbEvent"] + $tab_svc["CRITICALnbEvent"] + $tab_svc["WARNINGnbEvent"] + $tab_svc["UNKNOWNnbEvent"]  ;

	$status = "";
	foreach ($tab_resume  as $tb)
		if ($tb["pourcentTime"] >= 0)
			$status .= "&value[".$tb["state"]."]=".$tb["pourcentTime"];  
	# For today in timeline
	$tt = 0 + ($today_end - $today_start);

	$tab_report = array();
	$today_none = $tt - ($today_warning + $today_ok + $today_unknown + $today_critical);

	$tab_report[date("d/m/Y", $today_start)]["duration"] = Duration::toString($tt);
	$tab_report[date("d/m/Y", $today_start)]["oktime"] = Duration::toString($today_ok);
	$tab_report[date("d/m/Y", $today_start)]["warningtime"] = Duration::toString($today_warning);
	$tab_report[date("d/m/Y", $today_start)]["unknowntime"] = Duration::toString($today_unknown);
	$tab_report[date("d/m/Y", $today_start)]["criticaltime"] = Duration::toString($today_critical);
	$tab_report[date("d/m/Y", $today_start)]["pendingtime"] = Duration::toString($today_none);

	$today_none = round(($today_none/$tt *100),2);
	$today_ok = ($today_ok <= 0) ? 0 : round($today_ok / $tt *100,2);
	$today_warning = ($today_warning <= 0) ? 0 : round($today_warning / $tt *100,2);
	$today_unknown = ($today_unknown <= 0) ? 0 : round($today_unknown / $tt *100,2);
	$today_critical = ($today_critical <= 0) ? 0 : round($today_critical / $tt *100,2);
	$today_none = ($today_none < 0.1) ? "0" : $today_none;
	$tab_report[date("d/m/Y", $today_start)]["pok"] = $today_ok;
	$tab_report[date("d/m/Y", $today_start)]["pwarning"] = $today_warning;
	$tab_report[date("d/m/Y", $today_start)]["punknown"] = $today_unknown;
	$tab_report[date("d/m/Y", $today_start)]["pcritical"] = $today_critical;
	$tab_report[date("d/m/Y", $today_start)]["ppending"] = $today_none;
	$tab_report[date("d/m/Y", $today_start)]["OKnbEvent"] = $today_OKnbEvent;
	$tab_report[date("d/m/Y", $today_start)]["WARNINGnbEvent"] = $today_WARNINGnbEvent;
	$tab_report[date("d/m/Y", $today_start)]["CRITICALnbEvent"] = $today_CRITICALnbEvent;


	$rq = 	'SELECT ' .
			' * FROM `log_archive_service` WHERE host_id = ' . $host_id . ' AND service_id = ' . $svc_id .
			' AND date_start >= ' . $sd . ' AND date_end <= ' . $ed .
			' order by date_start desc';
	$res = & $pearDBO->query($rq);
	while ($h =& $res->fetchRow()) {
		$oktime = $h["OKTimeScheduled"];
		$criticaltime = $h["CRITICALTimeScheduled"];
		$warningtime = $h["WARNINGTimeScheduled"];
		$unknowntime = $h["UNKNOWNTimeScheduled"];
		$tt = 0 + ($h["date_end"] - $h["date_start"]);
		if(($oktime + $criticaltime + $warningtime + $unknowntime) < $tt)
			$pendingtime = 	$tt - ($oktime + $criticaltime + $warningtime + $unknowntime);
		else
			$pendingtime = 0;
		if($oktime > 0)
			$pok = 0 +round(($oktime / $tt * 100),2);
		else
			$pok = "0.00";					
		if($criticaltime > 0)
			$pcritical = 0 +round(($criticaltime / $tt * 100),2);
		else
			$pcritical = "0.00";
		if($warningtime > 0)
			$pwarning = 0 +round(($warningtime / $tt * 100),2);
		else
			$pwarning = "0.00";
		if($unknowntime > 0)
			$punknown = 0 +round(($unknowntime / $tt * 100),2);
		else
			$punknown = "0.00";
		if($pendingtime > 0)
			$ppending = 0 +round(($pendingtime / $tt * 100),2);
		else
			$ppending = "0.00";


		$t = 0 + ($h["date_end"] - $h["date_start"]);
		
		$t = round(($t - ($t * 0.11574074074)),2);
		$startd = $h["date_start"] + 5000;
		$tab_tmp = array();
		$tab_tmp ["duration"] = Duration::toString($tt) ? Duration::toString($tt) : 0;
		$tab_tmp ["oktime"] = Duration::toString($oktime) ? Duration::toString($oktime) : 0;
		$tab_tmp ["pok"] = Duration::toString($pok) ? Duration::toString($pok) : 0;
		$tab_tmp ["OKnbEvent"] = Duration::toString($h["OKnbEvent"]) ? Duration::toString($h["OKnbEvent"]) : 0;
		$tab_tmp ["criticaltime"] = Duration::toString($criticaltime) ? Duration::toString($criticaltime) : 0;
		$tab_tmp ["pcritical"] = Duration::toString($pcritical) ? Duration::toString($pcritical) : 0;
		$tab_tmp ["CRITICALnbEvent"] = Duration::toString($h["CRITICALnbEvent"]) ? Duration::toString($h["CRITICALnbEvent"]) : 0;
		$tab_tmp ["warningtime"] = Duration::toString($warningtime) ? Duration::toString($warningtime) : 0;
		$tab_tmp ["pwarning"] = Duration::toString($pwarning) ? Duration::toString($pwarning) : 0;
		$tab_tmp ["WARNINGnbEvent"] = Duration::toString($h["WARNINGnbEvent"]) ? Duration::toString($h["WARNINGnbEvent"]) : 0;
		$tab_tmp ["pendingtime"] = Duration::toString($pendingtime) ? Duration::toString($pendingtime) : 0;
		$tab_tmp ["ppending"] = Duration::toString($ppending) ? Duration::toString($ppending) : 0;
		$tab_tmp ["unknowntime"] = Duration::toString($unknowntime) ? Duration::toString($unknowntime) : 0;
		$tab_tmp ["punknown"] = Duration::toString($punknown) ? Duration::toString($punknown) : 0;

		$tab_report[date("d/m/Y", $startd)] = $tab_tmp;
	  }

?>