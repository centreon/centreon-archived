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
	if (!isset($oreon))
		exit;

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

	$path = "./include/reporting/dashboard";

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "");
	$tpl->assign('o', $o);
	require_once './class/other.class.php';
	require_once './include/common/common-Func.php';
	require_once('simple-func.php');
	require_once('reporting-func.php');
	include("./include/monitoring/log/choose_log_file.php");

	# LCA
	$lcaHostByName = getLcaHostByName($pearDB);

	isset ($_GET["host"]) ? $mhost = $_GET["host"] : $mhost = NULL;
	isset ($_POST["host"]) ? $mhost = $_POST["host"] : $mhost = $mhost;

	isset ($_GET["service"]) ? $mservice = $_GET["service"] : $mservice = NULL;
	isset ($_POST["service"]) ? $mservice = $_POST["service"] : $mservice = $mservice;

	#
	## Selection de l'host/service
	#
	$formService = new HTML_QuickForm('formService', 'post', "?p=".$p);


	#
	## period selection
	#
	$type_period = (isset($_GET["type_period"])) ? $_GET["type_period"] : "predefined";
	$type_period = (isset($_POST["type_period"])) ? $_POST["type_period"] : $type_period;
	$period1 = "today";
	if($mhost)	{
		$end_date_select = 0;
		$start_date_select= 0;
		$period1 = 0;
		if($type_period == "customized") {

			if(isset($_POST["start"]) && isset($_POST["end"])){
				$start = (isset($_POST["start"])) ? $_POST["start"] : NULL;
				$end = (isset($_POST["end"])) ? $_POST["end"] : NULL;
				getDateSelect_customized($end_date_select, $start_date_select, $start,$end);
			}
			else{
				$end = (isset($_GET["end"])) ? $_GET["end"] : $end;
				$start = (isset($_GET["start"])) ? $_GET["start"] : $start;
				$end_date_select = $end;
				$start_date_select = $start;
			}
			$formService->addElement('hidden', 'end', $end);
			$formService->addElement('hidden', 'start', $start);
			$period1 = "NULL";
		}
		else {
			$period1 = (isset($_POST["period"])) ? $_POST["period"] : NULL; 
			$period1 = (isset($_GET["period"])) ? $_GET["period"] : $period1;
			getDateSelect_predefined($end_date_select, $start_date_select, $period1);
			$formService->addElement('hidden', 'period', $period1);
			$period1 = is_null($period1) ? "today" : $period1;
		}
		$host_id = getMyHostID($mhost);
		$sd = $start_date_select;
		$ed = $end_date_select;

		#
		## recupere les log host en base
		#
		$Tup = NULL;
		$Tdown = NULL;
		$Tunreach = NULL;
		$Tnone = NULL;
		getLogInDbForHost($Tup, $Tdown, $Tunreach, $Tnone, $pearDB, $host_id, $start_date_select, $end_date_select);
		$tab_svc_bdd = array();
		getLogInDbForSVC($tab_svc_bdd, $pearDB, $host_id, $start_date_select, $end_date_select);
	}

	#
	## Selection de l'host/service (suite)
	#
	$formService->addElement('hidden', 'timeline', "1");
	$formService->addElement('hidden', 'host', $mhost);
	$formService->addElement('hidden', 'type_period', $type_period);
	$serviceList = array();
	$serviceList = getMyHostServices(getMyHostID($mhost));
	$selService =& $formService->addElement('select', 'service', $lang["m_svc"], $serviceList, array("onChange" =>"this.form.submit();"));
	$formService->setDefaults(array('service' => $mservice));


	#
	## fourchette de temps
	#
	$period = array();
	$period[""] = "";
	$period["today"] = $lang["today"];
	$period["yesterday"] = $lang["yesterday"];
	$period["thisweek"] = $lang["thisweek"];
	$period["last7days"] = $lang["last7days"];
	$period["thismonth"] = $lang["thismonth"];
	$period["last30days"] = $lang["last30days"];
	$period["lastmonth"] = $lang["lastmonth"];
	$period["thisyear"] = $lang["thisyear"];
	$period["lastyear"] = $lang["lastyear"];
	$formPeriod1 = new HTML_QuickForm('FormPeriod1', 'post', "?p=".$p."&type_period=predefined");
	isset($mhost) ? $formPeriod1->addElement('hidden', 'host', $mhost) : NULL;
	isset($mservice) ? $formPeriod1->addElement('hidden', 'service', $mservice) : NULL;
	$formPeriod1->addElement('header', 'title', $lang["m_predefinedPeriod"]);
	$selHost = $formPeriod1->addElement('select', 'period', $lang["m_predefinedPeriod"], $period, array("onChange" =>"this.form.submit();"));	
	$formPeriod1->setDefaults(array('period' => $period1));
	$formPeriod2 = new HTML_QuickForm('FormPeriod2', 'post', "?p=".$p."&type_period=customized");
	isset($mhost) ? $formPeriod2->addElement('hidden', 'host', $mhost) : NULL;
	isset($mservice) ? $formPeriod2->addElement('hidden', 'service', $mservice) : NULL;
	$formPeriod2->addElement('header', 'title', $lang["m_customizedPeriod"]);
	$formPeriod2->addElement('text', 'start', $lang["m_start"]);
	$formPeriod2->addElement('button', "startD", $lang['modify'], array("onclick"=>"displayDatePicker('start')"));
	$formPeriod2->addElement('text', 'end', $lang["m_end"]);
	$formPeriod2->addElement('button', "endD", $lang['modify'], array("onclick"=>"displayDatePicker('end')"));
	$sub = $formPeriod2->addElement('submit', 'submit', $lang["m_view"]);
	$res = $formPeriod2->addElement('reset', 'reset', $lang["reset"]);

	if($type_period == "customized") {
		$formPeriod2->setDefaults(array('start' => date("m/d/Y", $start_date_select)));
		$formPeriod2->setDefaults(array('end' => date("m/d/Y", $end_date_select)));
	}
	
	if($mhost){
		## if today is include in the time period
		$tab_log = array();
		$tab_svc = array();
		$day = date("d",time());
		$year = date("Y",time());
		$month = date("m",time());
		$startTimeOfThisDay = mktime(0, 0, 0, $month, $day, $year);


	$tab_hosts = array();	
	$day_current_start = 0;
	$day_current_end = time() + 1;
	
	parseFile($oreon->Nagioscfg["log_file"], $time, $tab_hosts, $tab_services,$day_current_start, $day_current_end, 1);	

	if ($startTimeOfThisDay  < ($end_date_select)){
		$end_date_select = time();
		if (isset($tab_services[getMyServiceName($mservice)][$mhost])){
			$tab_svc = $tab_services[getMyServiceName($mservice)][$mhost];
			if(!strncmp($tab_svc["current_state"], "OK", 2))
				$tab_svc["timeOK"] += (time()-$tab_svc["current_time"]);
			elseif(!strncmp($tab_svc["current_state"], "WARNING", 7))
				$tab_svc["timeWARNING"] += (time()-$tab_svc["current_time"]);
			elseif(!strncmp($tab_svc["current_state"], "UNKNOWN", 7))
				$tab_svc["timeUNKNOWN"] += (time()-$tab_svc["current_time"]);
			elseif(!strncmp($tab_svc["current_state"], "CRITICAL", 8))
				$tab_svc["timeCRITICAL"] += (time()-$tab_svc["current_time"]);
			else
				$tab_svc["timeNONE"] += (time()-$tab_svc["current_time"]);


			$today_ok = $tab_svc["timeOK"];
			$today_warning = $tab_svc["timeWARNING"];
			$today_unknown = $tab_svc["timeUNKNOWN"];
			$today_uncritical = $tab_svc["timeCRITICAL"];

				
			$tt = $end_date_select - $start_date_select;
			$svc_id = $tab_svc["service_id"];
			$archive_svc_ok =  isset($tab_svc_bdd[$svc_id]["Tok"]) ? $tab_svc_bdd[$svc_id]["Tok"] : 0;
			$archive_svc_warn = isset($tab_svc_bdd[$svc_id]["Twarn"]) ? $tab_svc_bdd[$svc_id]["Twarn"] : 0;
			$archive_svc_unknown = isset($tab_svc_bdd[$svc_id]["Tunknown"]) ? $tab_svc_bdd[$svc_id]["Tunknown"] : 0;
			$archive_svc_cri = isset($tab_svc_bdd[$svc_id]["Tcri"]) ? $tab_svc_bdd[$svc_id]["Tcri"] : 0;

			$tab_svc["timeOK"] += $archive_svc_ok;
			$tab_svc["timeWARNING"] += $archive_svc_warn;
			$tab_svc["timeUNKNOWN"] += $archive_svc_unknown;
			$tab_svc["timeCRITICAL"] +=$archive_svc_cri;
			$tab_svc["timeNONE"] += $tt - ($tab_svc["timeOK"] + $tab_svc["timeWARNING"] + $tab_svc["timeUNKNOWN"] + $tab_svc["timeCRITICAL"]);




			$tab_svc["PtimeOK"] = round(($tab_svc["timeOK"]) / $tt *100,3);
			$tab_svc["PtimeWARNING"] = round(($tab_svc["timeWARNING"]) / $tt *100,3);
			$tab_svc["PtimeUNKNOWN"] = round(($tab_svc["timeUNKNOWN"]) / $tt *100,3);
			$tab_svc["PtimeCRITICAL"] = round(($tab_svc["timeCRITICAL"]) / $tt *100,3);
			$tab_svc["PtimeNONE"] = round( ( $tt - (($tab_svc["timeOK"])
												 + ($tab_svc["timeWARNING"])
												 + ($tab_svc["timeUNKNOWN"])
												 + ($tab_svc["timeCRITICAL"])))  / $tt *100,3);

			$tab_svc["PktimeOK"] = round(($tab_svc["timeOK"]) / ($tt-$tab_svc["timeNONE"]) *100,3);
			$tab_svc["PktimeWARNING"] = round(($tab_svc["timeWARNING"]) / ($tt-$tab_svc["timeNONE"]) *100,3);
			$tab_svc["PktimeUNKNOWN"] = round(($tab_svc["timeUNKNOWN"]) / ($tt-$tab_svc["timeNONE"]) *100,3);
			$tab_svc["PktimeCRITICAL"] = round(($tab_svc["timeCRITICAL"]) / ($tt-$tab_svc["timeNONE"]) *100,3);
			$tab_svc["PktimeNONE"] = round( ( $tt - (($tab_svc["timeOK"])
												 + ($tab_svc["timeWARNING"])
												 + ($tab_svc["timeUNKNOWN"])
												 + ($tab_svc["timeCRITICAL"])))  / $tt *100,3);


			# les lignes suivante ne servent qu'a corriger un bug mineur correspondant a un decalage d'une seconde...
			$tab_svc["PtimeOK"] = number_format($tab_svc["PtimeOK"], 1, '.', '');
			$tab_svc["PtimeWARNING"] = number_format($tab_svc["PtimeWARNING"], 1, '.', '');
			$tab_svc["PtimeUNKNOWN"] = number_format($tab_svc["PtimeUNKNOWN"], 1, '.', '');
			$tab_svc["PtimeCRITICAL"] = number_format($tab_svc["PtimeCRITICAL"], 1, '.', '');
			$tab_svc["PtimeNONE"] = number_format($tab_svc["PtimeNONE"], 1, '.', '');
			$tab_svc["PtimeNONE"] = ($tab_svc["PtimeNONE"] < 0.1) ? "0.0" : $tab_svc["PtimeNONE"];

			$tab_svc["PktimeOK"] = number_format($tab_svc["PktimeOK"], 1, '.', '');
			$tab_svc["PktimeWARNING"] = number_format($tab_svc["PktimeWARNING"], 1, '.', '');
			$tab_svc["PktimeUNKNOWN"] = number_format($tab_svc["PktimeUNKNOWN"], 1, '.', '');
			$tab_svc["PktimeCRITICAL"] = number_format($tab_svc["PktimeCRITICAL"], 1, '.', '');
			#end
		}
	} else { // today is not in the period		
		$tab_svc = array();

		if (isset($tab_services[getMyServiceName($mservice)][$mhost])){
			$tab_svc = $tab_services[getMyServiceName($mservice)][$mhost];
			if(!strncmp($tab_svc["current_state"], "OK", 2))
				$tab_svc["timeOK"] += (time()-$tab_svc["current_time"]);
			elseif(!strncmp($tab_svc["current_state"], "WARNING", 7))
				$tab_svc["timeWARNING"] += (time()-$tab_svc["current_time"]);
			elseif(!strncmp($tab_svc["current_state"], "UNKNOWN", 7))
				$tab_svc["timeUNKNOWN"] += (time()-$tab_svc["current_time"]);
			elseif(!strncmp($tab_svc["current_state"], "CRITICAL", 8))
				$tab_svc["timeCRITICAL"] += (time()-$tab_svc["current_time"]);
			else
				$tab_svc["timeNONE"] += (time()-$tab_svc["current_time"]);

			$today_ok = $tab_svc["timeOK"];
			$today_warning = $tab_svc["timeWARNING"];
			$today_unknown = $tab_svc["timeUNKNOWN"];
			$today_uncritical = $tab_svc["timeCRITICAL"];
		}

		$svc_id = $mservice;
		$tab_svc_bdd = array();
		getLogInDbForOneSVC($tab_svc_bdd, $pearDB, $host_id, $svc_id, $start_date_select, $end_date_select);			
		$tab_svc["svcName"] = getMyServiceName($mservice);
		$tt = $end_date_select - $start_date_select;
		$tab_svc["timeOK"] = (isset($tab_svc_bdd[$svc_id]["Tok"])) ? $tab_svc_bdd[$svc_id]["Tok"] : 0;
		$tab_svc["timeWARNING"] = (isset($tab_svc_bdd[$svc_id]["Twarn"])) ? $tab_svc_bdd[$svc_id]["Twarn"] : 0;
		$tab_svc["timeUNKNOWN"] = (isset($tab_svc_bdd[$svc_id]["Tunknown"])) ? $tab_svc_bdd[$svc_id]["Tunknown"] : 0;
		$tab_svc["timeCRITICAL"] = (isset($tab_svc_bdd[$svc_id]["Tcri"])) ? $tab_svc_bdd[$svc_id]["Tcri"] : 0;
		$tab_svc["timeNONE"] = $tt - ($tab_svc["timeOK"] + $tab_svc["timeWARNING"] + $tab_svc["timeUNKNOWN"] + $tab_svc["timeCRITICAL"]);



		$tab_svc["PtimeOK"] = round($tab_svc["timeOK"] / $tt *100,3);
		$tab_svc["PtimeWARNING"] = round( $tab_svc["timeWARNING"]/ $tt *100,3);
		$tab_svc["PtimeUNKNOWN"] = round( $tab_svc["timeUNKNOWN"]/ $tt *100,3);
		$tab_svc["PtimeCRITICAL"] = round( $tab_svc["timeCRITICAL"]/ $tt *100,3);
		$tab_svc["PtimeNONE"] = round(($tab_svc["timeNONE"])  / $tt *100,3);

		if($tt != $tab_svc["timeNONE"]){
			$tab_svc["PktimeOK"] = round($tab_svc["timeOK"] / ($tt-$tab_svc["timeNONE"]) *100,3);
			$tab_svc["PktimeWARNING"] = round( $tab_svc["timeWARNING"]/ ($tt-$tab_svc["timeNONE"]) *100,3);
			$tab_svc["PktimeUNKNOWN"] = round( $tab_svc["timeUNKNOWN"]/ ($tt-$tab_svc["timeNONE"]) *100,3);
			$tab_svc["PktimeCRITICAL"] = round( $tab_svc["timeCRITICAL"]/ ($tt-$tab_svc["timeNONE"]) *100,3);
		}else {
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

		$tab_svc["PktimeOK"] = number_format($tab_svc["PktimeOK"], 1, '.', '');
		$tab_svc["PktimeWARNING"] = number_format($tab_svc["PktimeWARNING"], 1, '.', '');
		$tab_svc["PktimeUNKNOWN"] = number_format($tab_svc["PktimeUNKNOWN"], 1, '.', '');
		$tab_svc["PktimeCRITICAL"] = number_format($tab_svc["PktimeCRITICAL"], 1, '.', '');
		#end		
	}
}	

	## calculate service  resume
	$tab_resume = array();
	$tab = array();	
	if($mservice && $mhost){
		$tab["state"] = $lang["m_OKTitle"];
		$tab["time"] = Duration::toString($tab_svc["timeOK"]);
		$tab["pourcentTime"] = $tab_svc["PtimeOK"];
		$tab["pourcentkTime"] = $tab_svc["PktimeOK"]." %";
		$tab["style"] = " style='background:" . $oreon->optGen["color_ok"]."'";
		$tab_resume[0] = $tab;

		$tab["state"] = $lang["m_CriticalTitle"];
		$tab["time"] = Duration::toString($tab_svc["timeCRITICAL"]);
		$tab["pourcentTime"] = $tab_svc["PtimeCRITICAL"];
		$tab["pourcentkTime"] = $tab_svc["PktimeCRITICAL"]." %";
		$tab["style"] = " style='background:" . $oreon->optGen["color_critical"]."'";
		$tab_resume[1] = $tab;
		
		$tab["state"] = $lang["m_WarningTitle"];
		$tab["time"] = Duration::toString($tab_svc["timeWARNING"]);
		$tab["pourcentTime"] = $tab_svc["PtimeWARNING"];
		$tab["pourcentkTime"] = $tab_svc["PktimeWARNING"]." %";
		$tab["style"] = " style='background:" . $oreon->optGen["color_warning"]."'";
		$tab_resume[2] = $tab;
		
		$tab["state"] = $lang["m_UnknownTitle"];
		$tab["time"] = Duration::toString($tab_svc["timeUNKNOWN"]);
		$tab["pourcentTime"] = $tab_svc["PtimeUNKNOWN"];
		$tab["pourcentkTime"] = $tab_svc["PktimeUNKNOWN"]." %";
		$tab["style"] = " style='background:" . $oreon->optGen["color_unknown"]."'";
		$tab_resume[3] = $tab;
		
		$tab["state"] = $lang["m_PendingTitle"];
		$tab_svc["timeNONE"] = $tab_svc["timeNONE"] < 0 ? 0 : $tab_svc["timeNONE"];
		$tab["time"] = Duration::toString($tab_svc["timeNONE"]);

		$tab["pourcentTime"] = $tab_svc["PtimeNONE"];
		$tab["pourcentkTime"] = null;
		$tab["style"] = " style='background:#cccccc'";
		$tab_resume[4] = $tab;
	}

	$start_date_select = date("d/m/Y (G:i:s)", $start_date_select);
	$end_date_select =  date("d/m/Y (G:i:s)", $end_date_select);

	$path = "./include/reporting/dashboard/";
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "");

	$tpl->assign('o', $o);
	$tpl->assign('mhost', $mhost);
	$tpl->assign('hostTitle', $lang["h"]);
	$tpl->assign('actualTitle', $lang["actual"]);
	$tpl->assign('date_start_select', $start_date_select);
	$tpl->assign('date_end_select', $end_date_select);
	$tpl->assign('to', $lang["m_to"]);
	$tpl->assign('period', $lang["m_period"]);

	if($mservice && $mhost)
		$tpl->assign('infosTitle', $lang["m_duration"] . Duration::toString($tt));	

	$tpl->assign('periodTitle', $lang["m_selectPeriodTitle"]);
	$tpl->assign('resumeTitle', $lang["m_serviceResumeTitle"]);
	$tpl->assign('logTitle', $lang["m_hostLogTitle"]);
	$tpl->assign('svcTitle', $lang["m_hostSvcAssocied"]);
	$tpl->assign('style_ok', "class='ListColCenter' style='background:".$oreon->optGen["color_ok"]."'");
	$tpl->assign('style_warning' , "class='ListColCenter' style='background:".$oreon->optGen["color_warning"]."'");
	$tpl->assign('style_critical' , "class='ListColCenter' style='background:".$oreon->optGen["color_critical"]."'");
	$tpl->assign('style_unknown' , "class='ListColCenter' style='background:".$oreon->optGen["color_unknown"]."'");
	$tpl->assign('style_pending' , "class='ListColCenter' style='background:".$oreon->optGen["color_pending"]."'");

	$tpl->assign('serviceTilte', $lang["m_serviceTilte"]);
	$tpl->assign('OKTitle', $lang["m_OKTitle"]);
	$tpl->assign('WarningTitle', $lang["m_WarningTitle"]);
	$tpl->assign('UnknownTitle', $lang["m_UnknownTitle"]);
	$tpl->assign('CriticalTitle', $lang["m_CriticalTitle"]);
	$tpl->assign('PendingTitle', $lang["m_PendingTitle"]);

	$tpl->assign('StateTitle', $lang["m_StateTitle"]);
	$tpl->assign('TimeTitle', $lang["m_TimeTitle"]);
	$tpl->assign('TimeTotalTitle', $lang["m_TimeTotalTitle"]);
	$tpl->assign('KnownTimeTitle', $lang["m_KnownTimeTitle"]);
	$tpl->assign('DateTitle', $lang["m_DateTitle"]);
	$tpl->assign('EventTitle', $lang["m_EventTitle"]);
	$tpl->assign('HostTitle', $lang["m_HostTitle"]);
	$tpl->assign('InformationsTitle', $lang["m_InformationsTitle"]);

	$tpl->assign('infosTitle1', $mhost);
	$tpl->assign('infosTitle2', $start_date_select." => ".$end_date_select);		
	$tpl->assign('host_name', $mhost);		
	$tpl->assign('service_name', getMyServiceName($mservice));		

	$status = "";
	foreach ($tab_resume  as $tb)
		if($tb["pourcentTime"] >= 0)
			$status .= "&value[".$tb["state"]."]=".$tb["pourcentTime"];  
        
	$tpl->assign('status', $status);		
	$tpl->assign('hostID', getMyHostID($mhost));
	$color = array();
	$color["UNKNOWN"] =  substr($oreon->optGen["color_unknown"], 1);
	$color["UP"] =  substr($oreon->optGen["color_up"], 1);
	$color["DOWN"] =  substr($oreon->optGen["color_down"], 1);
	$color["UNREACHABLE"] =  substr($oreon->optGen["color_unreachable"], 1);
	$tpl->assign('color', $color);

	$renderer1 = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$formPeriod1->accept($renderer1);
	$tpl->assign('formPeriod1', $renderer1->toArray());	

	$renderer2 = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$formPeriod2->accept($renderer2);	
	$tpl->assign('formPeriod2', $renderer2->toArray());

	#Apply a template definition
	$renderer3 = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$formService->accept($renderer3);
	
	$tpl->assign('formService', $renderer3->toArray());
	$tpl->assign("tab_resume", $tab_resume);
	$tpl->assign("tab_log", $tab_log);
	$tpl->assign('lang', $lang);
	$tpl->assign("p", $p);
	
	
	# For today in timeline
	$tt = 0 + ($today_end - $today_start);

	$today_none = $tt - ($today_warning + $today_ok + $today_unknown + $today_critical);
	$today_none = round(($today_none/$tt *100),2);
	$today_ok = ($today_ok <= 0) ? 0 : round($today_ok / $tt *100,2);
	$today_warning = ($today_warning <= 0) ? 0 : round($today_warning / $tt *100,2);
	$today_unknown = ($today_unknown <= 0) ? 0 : round($today_unknown / $tt *100,2);
	$today_critical = ($today_critical <= 0) ? 0 : round($today_critical / $tt *100,2);

	$today_none = ($today_none < 0.1) ? "0" : $today_none;

if($mhost)	{
	$color = substr($oreon->optGen["color_ok"],1) .':'.
	 		 substr($oreon->optGen["color_warning"],1) .':'.
	 		 substr($oreon->optGen["color_critical"],1) .':'. 
	 		 substr($oreon->optGen["color_pending"],1) .':'. 
	 		 substr($oreon->optGen["color_unknown"],1);

	$today_var = '&serviceID='.$mservice.'&today_ok='.$today_ok . '&today_critical='.$today_critical.'&today_unknown='.$today_unknown. '&today_pending=' . $today_none. '&today_warning=' . $today_warning;
	$today_var .= '&today_WARNINGnbEvent=&today_CRITICALnbEvent=&today_OKnbEvent=';
	$type = 'Service';
	include('ajaxReporting_js.php');
}
else {
?>
<SCRIPT LANGUAGE="JavaScript">
function initTimeline() {
	;
}
</SCRIPT>
<?
}	
	
	$tpl->display("template/viewServicesLog.ihtml");
?>