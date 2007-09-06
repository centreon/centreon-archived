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
/*
	if (!isset($oreon))
		exit;
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

	#
	## Selectioned ?
	#		
	isset ($_GET["servicegroup"]) ? $mservicegroup = $_GET["servicegroup"] : $mservicegroup = NULL;
	isset ($_POST["servicegroup"]) ? $mservicegroup = $_POST["servicegroup"] : $mservicegroup = $mservicegroup;

	#
	## period selection
	#
	$period = (isset($_POST["period"])) ? $_POST["period"] : "today"; 
	$period = (isset($_GET["period"])) ? $_GET["period"] : $period;

	if($mservicegroup)	{
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
		$servicegroup_id = getMyservicegroupID($mservicegroup);
		$sd = $start_date_select;
		$ed = $end_date_select;

		#
		## database log
		#
		$sbase = array();
		$Tup = NULL;
		$Tdown = NULL;
		$Tunreach = NULL;
		$Tnone = NULL;
		getLogInDbForServicesGroup($sbase, $pearDB, $pearDBO, $servicegroup_id, $start_date_select, $end_date_select, $today_start, $today_end);
	}

	#
	## ressource selected
	#
	$today_ok = 0;
	$today_warning = 0;
	$today_unknown = 0;
	$today_critical = 0;
	$today_OKnbEvent = 0;
	$today_UNKNOWNnbEvent = 0;
	$today_WARNINGnbEvent = 0;
	$today_CRITICALnbEvent = 0;
	
	if($mservicegroup){
		#
		## today log for xml timeline
		#
		$today_ok = 0 + $sbase["average"]["today"]["Tok"];
		$today_warning = 0 + $sbase["average"]["today"]["Twarning"];
		$today_unknown = 0 + $sbase["average"]["today"]["Tunknown"];
	
		$today_OKnbEvent = 0 + $sbase["average"]["today"]["Tok"];
		$today_UNKNOWNnbEvent = 0 + $sbase["average"]["today"]["Tunknown"];
		$today_WARNINGnbEvent = 0 + $sbase["average"]["today"]["Twarning"];
		$today_CRITICALnbEvent = 0 + $sbase["average"]["today"]["Tcritical"];


		$tab_log = array();
		$day = date("d",time());
		$year = date("Y",time());
		$month = date("m",time());
		$startTimeOfThisDay = mktime(0, 0, 0, $month, $day, $year);
		$tab_svc_list_average = array();
		$tab_svc_list_average = array();
		$tab_svc_list_average["PTOK"] = 0;
		$tab_svc_list_average["PAOK"] = 0;
		$tab_svc_list_average["PTW"] = 0;
		$tab_svc_list_average["PAW"] = 0;
		$tab_svc_list_average["PTU"] = 0;
		$tab_svc_list_average["PAU"] = 0;
		$tab_svc_list_average["PTC"] = 0;
		$tab_svc_list_average["PAC"] = 0;
		$tab_svc_list_average["PTN"] = 0;
		$tab_svc_list_average["PKTOK"] = 0;
		$tab_svc_list_average["PKTW"] = 0;
		$tab_svc_list_average["PKTU"] = 0;
		$tab_svc_list_average["PKTC"] = 0;
		$tab_svc_list_average["nb_svc"] = 0;

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
		$Tok = $sbase["average"]["Tok"];
		$Twarning = $sbase["average"]["Twarning"];
		$Tunreach = $sbase["average"]["Tunknown"];
		$Tcritical = $sbase["average"]["Tcritical"];
		$Tnone = $timeTOTAL - ($Tok + $Twarning + $Tunreach + $Tcritical);
		if($Tnone <= 1)
		$Tnone = 0;	
		$tab["state"] = $lang["m_UpTitle"];
		$tab["time"] = Duration::toString($Tok);
		$tab["timestamp"] = $Tok;
		$tab["pourcentTime"] = round($Tok/($timeTOTAL+1)*100,2) ;
		$tab["pourcentkTime"] = round($Tok/($timeTOTAL-$Tnone+1)*100,2). "%";
		$tab["nbAlert"] = $sbase["average"]["OKnbEvent"];
//		$tab["style"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_ok"]."'";
		$tab_resume[0] = $tab;
		$tab["state"] = $lang["m_CriticalTitle"];
		$tab["time"] = Duration::toString($Tcritical);
		$tab["timestamp"] = $Tcritical;
		$tab["pourcentTime"] = round($Tcritical/$timeTOTAL*100,2);
		$tab["pourcentkTime"] = null;
		$tab["nbAlert"] = $sbase["average"]["CRITICALnbEvent"];
//		$tab["style"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_critical"]."'";
		$tab_resume[1] = $tab;
		$tab["state"] = $lang["m_DownTitle"];
		$tab["time"] = Duration::toString($Twarning);
		$tab["timestamp"] = $Twarning;
		$tab["pourcentTime"] = round($Twarning/$timeTOTAL*100,2);
		$tab["pourcentkTime"] = round($Twarning/($timeTOTAL-$Tnone+1)*100,2)."%";
		$tab["nbAlert"] = $sbase["average"]["WARNINGnbEvent"];
//		$tab["style"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_warning"]."'";
		$tab_resume[2] = $tab;
		$tab["state"] = $lang["m_UnreachableTitle"];
		$tab["time"] = Duration::toString($Tunreach);
		$tab["timestamp"] = $Tunreach;
		$tab["pourcentTime"] = round($Tunreach/$timeTOTAL*100,2);
		$tab["pourcentkTime"] = round($Tunreach/($timeTOTAL-$Tnone+1)*100,2)."%";
		$tab["nbAlert"] = $sbase["average"]["UNKNOWNnbEvent"];
//		$tab["style"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_unknown"]."'";
		$tab_resume[3] = $tab;
		$tab["state"] = $lang["m_PendingTitle"];
		$tab["time"] = Duration::toString($Tnone);
		$tab["timestamp"] = $Tnone;
		$tab["pourcentTime"] = round($Tnone/$timeTOTAL*100,2);
		$tab["pourcentkTime"] = null;
		$tab["nbAlert"] = "";
//		$tab["style"] = "class='ListColCenter' style='background:#cccccc'";
		$tab_resume[4] = $tab;

		#
		## calculate tablist
		#
		$i=0;
		foreach($sbase as $svc_name => $tab)
		{
			if($svc_name != "average"){
				$tab_tmp = array();
				$tab_tmp["hostName"] = getMyHostName($tab["host_id"]);
				$tab_tmp["serviceName"] = getMyServiceName($tab["svc_id"]);
				$tab_tmp["svc_id"] = $tab["svc_id"];
				$tt = $end_date_select - $start_date_select;

				$tab_tmp["PtimeOK"] = round($tab["Tok"] / $tt *100,2);
				$tab_tmp["PtimeWARNING"] = round( $tab["Twarning"]/ $tt *100,2);
				$tab_tmp["PtimeUNKNOWN"] = round( $tab["Tunknown"]/ $tt *100,2);
				$tab_tmp["PtimeCRITICAL"] = round( $tab["Tcritical"]/ $tt *100,2);
				$tab_tmp["PtimeUNDETERMINATED"] = round( ( $tt - ($tab["Tok"] + $tab["Twarning"] + $tab["Tunknown"] + $tab["Tcritical"] ))  / $tt *100,2);

				$tmp_none = $tt - ($tab["Tok"] + $tab["Twarning"] + $tab["Tunknown"]);
				$tab_tmp["OKnbEvent"] = isset($tab["TokNBAlert"]) ? $tab["TokNBAlert"] : 0;
				$tab_tmp["WARNINGnbEvent"] = isset($tab["WARNINGnbEvent"]) ? $tab["WARNINGnbEvent"] : 0;
				$tab_tmp["UNKNOWNnbEvent"] = isset($tab["UNKNOWNnbEvent"]) ? $tab["UNKNOWNnbEvent"] : 0;
				$tab_tmp["CRITICALnbEvent"] = isset($tab["TcriticalNBAlert"]) ? $tab["TcriticalNBAlert"] : 0;

				$kt = $tt - $tmp_none;
				if($kt > 0){
					$tab_tmp["PktimeOK"] = $tab["Tok"] ? round($tab["Tok"] / ($kt) *100,2): 0;
					$tab_tmp["PktimeWARNING"] = $tab["Twarning"] ? round( $tab["Twarning"]/ ($kt) *100,2):0;
					$tab_tmp["PktimeUNKNOWN"] =  $tab["Tunknown"] ? round( $tab["Tunknown"]/ ($kt) *100,2):0;
					$tab_tmp["PktimeCRITICAL"] =  $tab["Tcritical"] ? round( $tab["Tcritical"]/ ($kt) *100,2):0;
				}
				else{
					$tab_tmp["PktimeOK"] = 0;
					$tab_tmp["PktimeWARNING"] = 0;
					$tab_tmp["PktimeUNKNOWN"] = 0;
					$tab_tmp["PktimeCRITICAL"] = 0;					
				}
				
				$tab_tmp["PtimeOK"] = number_format($tab_tmp["PtimeOK"], 1, '.', '');
				$tab_tmp["PtimeWARNING"] = number_format($tab_tmp["PtimeWARNING"], 1, '.', '');
				$tab_tmp["PtimeUNKNOWN"] = number_format($tab_tmp["PtimeUNKNOWN"], 1, '.', '');
				$tab_tmp["PtimeCRITICAL"] = number_format($tab_tmp["PtimeCRITICAL"], 1, '.', '');

				$tab_tmp["PtimeUNDETERMINATED"] = number_format($tab_tmp["PtimeUNDETERMINATED"], 1, '.', '');
				$tab_tmp["PtimeUNDETERMINATED"] = ($tab_tmp["PtimeUNDETERMINATED"] < 0.1) ? 0.0 : $tab_tmp["PtimeUNDETERMINATED"];

				$tab_tmp["PktimeOK"] = number_format($tab_tmp["PktimeOK"], 1, '.', '');
				$tab_tmp["PktimeWARNING"] = number_format($tab_tmp["PktimeWARNING"], 1, '.', '');
				$tab_tmp["PktimeUNKNOWN"] = number_format($tab_tmp["PktimeUNKNOWN"], 1, '.', '');
				$tab_tmp["PktimeCRITICAL"] = number_format($tab_tmp["PktimeCRITICAL"], 1, '.', '');
	
				#
				## fill average svc table
				#
				$tab_svc_list_average["PTOK"] += $tab_tmp["PtimeOK"];
				$tab_svc_list_average["PAOK"]  += $tab_tmp["OKnbEvent"];
				$tab_svc_list_average["PTW"] += $tab_tmp["PtimeWARNING"];
				$tab_svc_list_average["PAW"] += $tab_tmp["WARNINGnbEvent"];
				$tab_svc_list_average["PTU"] += $tab_tmp["PtimeUNKNOWN"];
				$tab_svc_list_average["PAU"] += $tab_tmp["UNKNOWNnbEvent"];
				$tab_svc_list_average["PTC"] += $tab_tmp["PtimeCRITICAL"];
				$tab_svc_list_average["PAC"] += $tab_tmp["CRITICALnbEvent"];
				$tab_svc_list_average["PTN"] += $tab_tmp["PtimeUNDETERMINATED"];
				$tab_svc_list_average["PKTOK"] += $tab_tmp["PktimeOK"];
				$tab_svc_list_average["PKTW"]+= $tab_tmp["PktimeWARNING"];
				$tab_svc_list_average["PKTU"]+= $tab_tmp["PktimeUNKNOWN"];
				$tab_svc_list_average["PKTC"] += $tab_tmp["PktimeCRITICAL"];
				$tab_svc_list_average["nb_svc"]+= 1;


				$tab_svc[$i++] = $tab_tmp;
			}
		}

		#
		## calculate svc average
		#
		# Alert
		if($tab_svc_list_average["PAOK"] > 0)
		$tab_svc_list_average["PAOK"] = number_format($tab_svc_list_average["PAOK"] / $tab_svc_list_average["nb_svc"], 1, '.', '');
		if($tab_svc_list_average["PAW"] > 0)
		$tab_svc_list_average["PAW"] = number_format($tab_svc_list_average["PAW"] / $tab_svc_list_average["nb_svc"], 1, '.', '');
		if($tab_svc_list_average["PAU"] > 0)
		$tab_svc_list_average["PAU"] = number_format($tab_svc_list_average["PAU"] / $tab_svc_list_average["nb_svc"], 1, '.', '');
		if($tab_svc_list_average["PAC"] > 0)
		$tab_svc_list_average["PAC"] = number_format($tab_svc_list_average["PAC"] / $tab_svc_list_average["nb_svc"], 1, '.', '');
		# Time
		if($tab_svc_list_average["PTOK"] > 0)
		$tab_svc_list_average["PTOK"] = number_format($tab_svc_list_average["PTOK"] / $tab_svc_list_average["nb_svc"], 3, '.', '');
		if($tab_svc_list_average["PTW"] > 0)
		$tab_svc_list_average["PTW"] = number_format($tab_svc_list_average["PTW"] / $tab_svc_list_average["nb_svc"], 3, '.', '');
		if($tab_svc_list_average["PTC"] > 0)
		$tab_svc_list_average["PTC"] = number_format($tab_svc_list_average["PTC"] / $tab_svc_list_average["nb_svc"], 3, '.', '');
		if($tab_svc_list_average["PTU"] > 0)
		$tab_svc_list_average["PTU"] = number_format($tab_svc_list_average["PTU"] / $tab_svc_list_average["nb_svc"], 3, '.', '');
		if($tab_svc_list_average["PTN"] > 0)
		$tab_svc_list_average["PTN"] = number_format($tab_svc_list_average["PTN"] / $tab_svc_list_average["nb_svc"], 3, '.', '');

		# %
		if($tab_svc_list_average["PKTOK"] > 0)
		$tab_svc_list_average["PKTOK"] = number_format($tab_svc_list_average["PKTOK"] / $tab_svc_list_average["nb_svc"], 3, '.', '');
		if($tab_svc_list_average["PKTW"] > 0)
		$tab_svc_list_average["PKTW"] = number_format($tab_svc_list_average["PKTW"] / $tab_svc_list_average["nb_svc"], 3, '.', '');
		if($tab_svc_list_average["PKTC"] > 0)
		$tab_svc_list_average["PKTC"] = number_format($tab_svc_list_average["PKTC"] / $tab_svc_list_average["nb_svc"], 3, '.', '');
		if($tab_svc_list_average["PKTU"] > 0)
		$tab_svc_list_average["PKTU"] = number_format($tab_svc_list_average["PKTU"] / $tab_svc_list_average["nb_svc"], 3, '.', '');

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
		$totalAlert = $sbase["average"]["UNKNOWNnbEvent"] + $sbase["average"]["WARNINGnbEvent"] + $sbase["average"]["OKnbEvent"] + $sbase["average"]["CRITICALnbEvent"];
	}

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
	$tab_report[date("d/m/Y", $today_start)]["pok"] = Duration::toString($today_ok);
	$tab_report[date("d/m/Y", $today_start)]["pwarning"] = Duration::toString($today_warning);
	$tab_report[date("d/m/Y", $today_start)]["punknown"] = Duration::toString($today_unknown);
	$tab_report[date("d/m/Y", $today_start)]["pcritical"] = Duration::toString($today_critical);
	$tab_report[date("d/m/Y", $today_start)]["ppending"] = Duration::toString($today_none);
	$tab_report[date("d/m/Y", $today_start)]["OKnbEvent"] = Duration::toString($today_OKnbEvent);
	$tab_report[date("d/m/Y", $today_start)]["WARNINGnbEvent"] = Duration::toString($today_WARNINGnbEvent);
	$tab_report[date("d/m/Y", $today_start)]["CRITICALnbEvent"] = Duration::toString($today_CRITICALnbEvent);


	/* historical daily report*/

	$rq = "SELECT " .
			"date_start, date_end, " .
			"avg( `OKTimeScheduled` ) as 'OKTimeScheduled', " .
			"avg( `OKnbEvent` ) as 'OKnbEvent', " .
			"avg( `WARNINGTimeScheduled` ) as 'WARNINGTimeScheduled', " .
			"avg( `WARNINGnbEvent` ) as 'WARNINGnbEvent', " .
			"avg( `UNKNOWNTimeScheduled` ) as 'UNKNOWNTimeScheduled', " .
			"avg( `UNKNOWNnbEvent` ) as 'UNKNOWNnbEvent', " .
			"avg( `CRITICALTimeScheduled` ) as 'CRITICALTimeScheduled', " .
			"avg( `CRITICALnbEvent` ) as 'CRITICALnbEvent' " .
			"FROM `log_archive_service` WHERE `date_start` >= " . $sd . " AND `date_end` <= " . $ed .
			" AND `service_id` IN (" .
			"SELECT `service_service_id` FROM `servicegroup_relation` WHERE `servicegroup_sg_id` = '" . $servicegroup_id ."') group by date_end, date_start order by date_start desc";
	$res = & $pearDB->query($rq);
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
		$start = $h["date_start"] + 5000;			
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

		$tab_report[date("d/m/Y", $start)] = $tab_tmp;

		  }

?>