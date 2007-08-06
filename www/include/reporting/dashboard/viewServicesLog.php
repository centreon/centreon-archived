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
	## Select form part 1
	#
	$formService = new HTML_QuickForm('formService', 'post', "?p=".$p);


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
			getDateSelect_customized($end_date_select, $start_date_select, $start,$end);
			$formService->addElement('hidden', 'end', $end);
			$formService->addElement('hidden', 'start', $start);
		}
		else {
			getDateSelect_predefined($end_date_select, $start_date_select, $period);
			$formService->addElement('hidden', 'period', $period);
		}
		$host_id = getMyHostID($mhost);
		$sd = $start_date_select;
		$ed = $end_date_select;

		#
		## database log
		#
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

	#
	## Select form part 2
	#
	$formService->addElement('hidden', 'timeline', "1");
	$formService->addElement('hidden', 'host', $mhost);
	$serviceList = array();
	$serviceList = getMyHostServices(getMyHostID($mhost));
	$selService =& $formService->addElement('select', 'service', $lang["m_svc"], $serviceList, array("onChange" =>"this.form.submit();"));
	$formService->setDefaults(array('service' => $mservice));

	#
	## fourchette de temps
	#
	$periodList = array();
	$periodList[""] = "";
	$periodList["today"] = $lang["today"];
	$periodList["yesterday"] = $lang["yesterday"];
	$periodList["thisweek"] = $lang["thisweek"];
	$periodList["last7days"] = $lang["last7days"];
	$periodList["thismonth"] = $lang["thismonth"];
	$periodList["last30days"] = $lang["last30days"];
	$periodList["lastmonth"] = $lang["lastmonth"];
	$periodList["thisyear"] = $lang["thisyear"];
	$periodList["lastyear"] = $lang["lastyear"];
	$periodList["customized"] = $lang["m_customizedPeriod"];
	$formPeriod = new HTML_QuickForm('FormPeriod1', 'post', "?p=".$p."&type_period=predefined");
	$selHost = $formPeriod->addElement('select', 'period', $lang["m_predefinedPeriod"], $periodList);

	isset($mhost) ? $formPeriod->addElement('hidden', 'host', $mhost) : NULL;
	isset($mservice) ? $formPeriod->addElement('hidden', 'service', $mservice) : NULL;

	$formPeriod->addElement('header', 'title', $lang["m_if_custom"]);
	$formPeriod->setDefaults(array('period' => $period));
	$formPeriod->addElement('text', 'start', $lang["m_start"]);
	$formPeriod->addElement('button', "startD", $lang['modify'], array("onclick"=>"displayDatePicker('start')"));
	$formPeriod->addElement('text', 'end', $lang["m_end"]);
	$formPeriod->addElement('button', "endD", $lang['modify'], array("onclick"=>"displayDatePicker('end')"));
	$sub = $formPeriod->addElement('submit', 'submit', $lang["m_view"]);
	$res = $formPeriod->addElement('reset', 'reset', $lang["reset"]);

	if($period == "customized") {
		$formPeriod->setDefaults(array('start' => date("m/d/Y", $start_date_select)));
		$formPeriod->setDefaults(array('end' => date("m/d/Y", $end_date_select)));
	}
	
	if($mhost){
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
	if($mservice && $mhost){
		$tab["state"] = $lang["m_OKTitle"];
		$tab["timestamp"] = $tab_svc["timeOK"];
		$tab["time"] = Duration::toString($tab_svc["timeOK"]);
		$tab["pourcentTime"] = $tab_svc["PtimeOK"];
		$tab["pourcentkTime"] = $tab_svc["PktimeOK"]." %";
		$tab["nbAlert"] = $tab_svc["OKnbEvent"];
		$today_OKnbEvent = $tab_svc["OKnbEvent"];
		$tab["style"] = " style='background:" . $oreon->optGen["color_ok"]."'";
		$tab_resume[0] = $tab;

		$tab["state"] = $lang["m_CriticalTitle"];
		$tab["time"] = Duration::toString($tab_svc["timeCRITICAL"]);
		$tab["timestamp"] = $tab_svc["timeCRITICAL"];
		$tab["pourcentTime"] = $tab_svc["PtimeCRITICAL"];
		$tab["pourcentkTime"] = $tab_svc["PktimeCRITICAL"]." %";
		$tab["nbAlert"] = $tab_svc["CRITICALnbEvent"];
		$today_CRITICALnbEvent = $tab_svc["CRITICALnbEvent"];
		$tab["style"] = " style='background:" . $oreon->optGen["color_critical"]."'";
		$tab_resume[1] = $tab;
		
		$tab["state"] = $lang["m_WarningTitle"];
		$tab["time"] = Duration::toString($tab_svc["timeWARNING"]);
		$tab["timestamp"] = $tab_svc["timeWARNING"];
		$tab["pourcentTime"] = $tab_svc["PtimeWARNING"];
		$tab["pourcentkTime"] = $tab_svc["PktimeWARNING"]." %";
		$tab["nbAlert"] = $tab_svc["WARNINGnbEvent"];
		$today_WARNINGnbEvent = $tab_svc["WARNINGnbEvent"];
		$tab["style"] = " style='background:" . $oreon->optGen["color_warning"]."'";
		$tab_resume[2] = $tab;
		
		$tab["state"] = $lang["m_UnknownTitle"];
		$tab["time"] = Duration::toString($tab_svc["timeUNKNOWN"]);
		$tab["timestamp"] = $tab_svc["timeUNKNOWN"];
		$tab["pourcentTime"] = $tab_svc["PtimeUNKNOWN"];
		$tab["pourcentkTime"] = $tab_svc["PktimeUNKNOWN"]." %";
		$tab["nbAlert"] = $tab_svc["UNKNOWNnbEvent"];
		$today_UNKNOWNnbEvent = $tab_svc["UNKNOWNnbEvent"];
		$tab["style"] = " style='background:" . $oreon->optGen["color_unknown"]."'";
		$tab_resume[3] = $tab;
		
		$tab["state"] = $lang["m_PendingTitle"];
		$tab_svc["timeNONE"] = $tab_svc["timeNONE"] < 0 ? 0 : $tab_svc["timeNONE"];
		$tab["time"] = Duration::toString($tab_svc["timeNONE"]);
		$tab["timestamp"] = $tab_svc["timeNONE"];
		$tab["pourcentTime"] = $tab_svc["PtimeNONE"];
		$tab["pourcentkTime"] = null;
		$tab["nbAlert"] = "";
		$tab["style"] = " style='background:#cccccc'";
		$tab_resume[4] = $tab;
	}

	$start_date_select = date("d/m/Y (G:i:s)", $start_date_select);
	$end_date_select =  date("d/m/Y (G:i:s)", $end_date_select);

	$path = "./include/reporting/dashboard/";
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "");


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

	$totalAlert = $tab_svc["OKnbEvent"] + $tab_svc["CRITICALnbEvent"] + $tab_svc["WARNINGnbEvent"] + $tab_svc["UNKNOWNnbEvent"]  ;

	$tpl->assign('totalAlert', $totalAlert);

	$tpl->assign('totalTime', Duration::toString($totalTime));
	$tpl->assign('totalpTime', $totalpTime);
	$tpl->assign('totalpkTime', $totalpkTime);
	$tpl->assign("allTilte",  $lang["m_allTilte"]);


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
	$tpl->assign('HostTitle', $lang["m_hostTitle"]);
	$tpl->assign('InformationsTitle', $lang["m_InformationsTitle"]);
	$tpl->assign('AlertTitle', $lang["m_AlertTitle"]);

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

	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$formPeriod->accept($renderer);
	$tpl->assign('formPeriod', $renderer->toArray());

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

	$today_var = '&serviceID='.$mservice.'&today_ok='.$today_ok . '&today_critical='.$today_critical.'&today_unknown='.$today_unknown. '&today_pending=' . $today_none. '&today_warning=' . $today_warning;
	$today_var .= '&today_WARNINGnbEvent='.$today_WARNINGnbEvent.'&today_CRITICALnbEvent='.$today_CRITICALnbEvent.'&today_OKnbEvent='.$today_OKnbEvent.'&today_UNKNOWNnbEvent='.$today_UNKNOWNnbEvent;
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