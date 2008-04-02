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

	$tab_svc = array();

	$path = "./include/reporting/dashboard";

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "");
	$tpl->assign('o', $o);
	require_once './class/other.class.php';
	require_once './include/common/common-Func.php';
	require_once 'simple-func.php';
	require_once 'reporting-func.php';
	//include("./include/monitoring/log/choose_log_file.php");
	
	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	if (!$is_admin)
		$lca = getLcaHostByName($pearDB);
	
	isset ($_GET["host"]) ? $mhost = $_GET["host"] : $mhost = NULL;
	isset ($_POST["host"]) ? $mhost = $_POST["host"] : $mhost = $mhost;

	isset ($_GET["service"]) ? $mservice = $_GET["service"] : $mservice = NULL;
	isset ($_POST["service"]) ? $mservice = $_POST["service"] : $mservice = $mservice;

	$tableFile2 = array();
	if ($handle  = @opendir($oreon->Nagioscfg["log_archive_path"]))	{
		while ($file = @readdir($handle))
			if (is_file($oreon->Nagioscfg["log_archive_path"]."/$file"))	{
				preg_match("/nagios\-([0-9]*)\-([0-9]*)\-([0-9]*)\-([0-9]*).log/", $file, $matches);
				$time = mktime("0", "0", "0", $matches[1], $matches[2], $matches[3]) - 1;
				$tableFile2[$file] =  "  " . date(_("Y/m/d"), $time) . " ";
			}
		@closedir($handle);
	}
	krsort($tableFile2);

	$tableFile3 = array($oreon->Nagioscfg["log_file"] => " -- " . _("Today") . " -- ");
	$tableFile1 = array_merge($tableFile3, $tableFile2);

	$host = array();
	
	$host[""] = "";
	$DBRESULT =& $pearDB->query("SELECT host_name FROM host where host_activate = '1' and host_register = '1' ORDER BY host_name");
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getMessage();
	while ($DBRESULT->fetchInto($h))
		if (!isset($lca) || isset($lca["LcaHost"][$h['host_name']]))
			$host[$h["host_name"]] = $h["host_name"];

	$debug = 0;
	$attrsTextI		= array("size"=>"3");
	$attrsText 		= array("size"=>"30");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"40");
	
	#
	## Form begin
	#
	
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title', _("Add a Service downtime"));
	
	#
	## Indicator basic information
	#
	
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
    
    $selHost =& $form->addElement('select', 'file', _("Log file"), $tableFile1, array("onChange" =>"this.form.submit();"));
	$selHost =& $form->addElement('select', 'host', _("Host"), $host, array("onChange" =>"this.form.submit();"));
	isset($_POST["host"]) ?	$form->setDefaults(array('file' => $_POST["host"])) : $form->setDefaults(array('file' => $oreon->Nagioscfg["log_file"]));
	
	$log = NULL;	
	$tab_log = array();	

	require_once 'ServicesLog.php';

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
		if($period == "customized") {
			$formService->addElement('hidden', 'end', $end);
			$formService->addElement('hidden', 'start', $start);
			$var_url_export_csv = "&period=customized&start=".$start."&end="."$end"."&lang=" .$oreon->user->get_lang();
		}
		else {
			$formService->addElement('hidden', 'period', $period);
			$var_url_export_csv = "&period=".$period."&lang=" .$oreon->user->get_lang();
		}
	}

	#
	## Select form part 2
	#
	$formService->addElement('hidden', 'timeline', "1");
	$formService->addElement('hidden', 'host', $mhost);
	$serviceList = array();
	$serviceList = getMyHostServices(getMyHostID($mhost));
	$selService =& $formService->addElement('select', 'service', _(" Service "), $serviceList, array("onChange" =>"this.form.submit();"));
	$formService->setDefaults(array('service' => $mservice));

	#
	## fourchette de temps
	#
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
	$formPeriod = new HTML_QuickForm('FormPeriod1', 'post', "?p=".$p."&type_period=predefined");
	$selHost = $formPeriod->addElement('select', 'period', _("Predefined:"), $periodList);

	isset($mhost) ? $formPeriod->addElement('hidden', 'host', $mhost) : NULL;
	isset($mservice) ? $formPeriod->addElement('hidden', 'service', $mservice) : NULL;

	$formPeriod->addElement('header', 'title', _("If customized period..."));
	$formPeriod->setDefaults(array('period' => $period));
	$formPeriod->addElement('text', 'start', _("Begin date"));
	$formPeriod->addElement('button', "startD", _("Modify"), array("onclick"=>"displayDatePicker('start')"));
	$formPeriod->addElement('text', 'end', _("End date"));
	$formPeriod->addElement('button', "endD", _("Modify"), array("onclick"=>"displayDatePicker('end')"));
	$sub = $formPeriod->addElement('submit', 'submit', _("View"));
	$res = $formPeriod->addElement('reset', 'reset', _("Reset"));

	if($period == "customized") {

		$formPeriod->setDefaults(array('start' => date("m/d/Y", $sd)));
		$formPeriod->setDefaults(array('end' => date("m/d/Y", $ed)));
	}

	$path = "./include/reporting/dashboard/";
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "");

	$tpl->assign('totalAlert', $totalAlert);

	$tpl->assign('totalTime', Duration::toString($totalTime));
	$tpl->assign('totalpTime', $totalpTime);
	$tpl->assign('totalpkTime', $totalpkTime);
	$tpl->assign("allTilte",  _("All"));


	$tpl->assign('o', $o);
	$tpl->assign('mhost', $mhost);
	$tpl->assign('hostTitle', _("Host"));
	$tpl->assign('actualTitle', _(" Actual "));
	$tpl->assign('date_start_select', $start_date_select);
	$tpl->assign('date_end_select', $end_date_select);
	$tpl->assign('to', _(" to "));
	$tpl->assign('period', _(" From "));

	$tt = 0 + ($ed - $sd);

	if($mservice && $mhost)
		$tpl->assign('infosTitle', _("Duration : ") . Duration::toString($tt));	

	$tpl->assign('periodTitle', _("Period Selection"));
	$tpl->assign('resumeTitle', _("Service state"));
	$tpl->assign('logTitle', _("Today's Host log"));
	$tpl->assign('svcTitle', _("State Breakdowns For Host Services"));
	$tpl->assign('style_ok', "class='ListColCenter' style='background:".$oreon->optGen["color_ok"]."'");
	$tpl->assign('style_warning' , "class='ListColCenter' style='background:".$oreon->optGen["color_warning"]."'");
	$tpl->assign('style_critical' , "class='ListColCenter' style='background:".$oreon->optGen["color_critical"]."'");
	$tpl->assign('style_unknown' , "class='ListColCenter' style='background:".$oreon->optGen["color_unknown"]."'");
	$tpl->assign('style_pending' , "class='ListColCenter' style='background:".$oreon->optGen["color_pending"]."'");

	$tpl->assign('serviceTilte', _("Service"));
	$tpl->assign('OKTitle', _("OK"));
	$tpl->assign('WarningTitle', _("Warning"));
	$tpl->assign('UnknownTitle', _("Unknown"));
	$tpl->assign('CriticalTitle', _("Critical"));
	$tpl->assign('PendingTitle', _("Undetermined"));

	$tpl->assign('StateTitle', _("Status"));
	$tpl->assign('TimeTitle', _("Time"));
	$tpl->assign('TimeTotalTitle', _("Total Time"));
	$tpl->assign('KnownTimeTitle', _("Known Time"));
	$tpl->assign('DateTitle', _("Date"));
	$tpl->assign('EventTitle', _("Event"));
	$tpl->assign('HostTitle', _("Host"));
	$tpl->assign('InformationsTitle', _("Info"));
	$tpl->assign('AlertTitle', _("Alert"));

	$tpl->assign('infosTitle1', $mhost);
	$tpl->assign('infosTitle2', $start_date_select." => ".$end_date_select);		
	$tpl->assign('host_name', $mhost);
	$tpl->assign('service_name', $service_name);		

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

	$tab_resume[0]["style"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_ok"]."'";
	$tab_resume[1]["style"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_critical"]."'";
	$tab_resume[2]["style"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_warning"]."'";		
	$tab_resume[3]["style"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_unknown"]."'";		
	$tab_resume[4]["style"] =  "class='ListColCenter' style='background:#cccccc'";

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


	if($mhost){
		$tpl->assign("link_csv_url", "./include/reporting/dashboard/ExportCSV_ServiceLog.php?sid=".$sid."&host=".$mhost."&service=".$mservice.$var_url_export_csv);
		$tpl->assign("link_csv_name", "Export CSV");
		$color = substr($oreon->optGen["color_ok"],1) .':'.
		 		 substr($oreon->optGen["color_warning"],1) .':'.
		 		 substr($oreon->optGen["color_critical"],1) .':'.
		 		 substr($oreon->optGen["color_pending"],1) .':';
	
		$today_var = '&serviceID='.$mservice.'&today_ok='.$today_ok . '&today_critical='.$today_critical.'&today_unknown='.$today_unknown. '&today_pending=' . $today_none. '&today_warning=' . $today_warning;
		$today_var .= '&today_WARNINGnbEvent='.$today_WARNINGnbEvent.'&today_CRITICALnbEvent='.$today_CRITICALnbEvent.'&today_OKnbEvent='.$today_OKnbEvent.'&today_UNKNOWNnbEvent='.$today_UNKNOWNnbEvent;
		$type = 'Service';
		include('ajaxReporting_js.php');
	}
	else {
	?>
	<script type="text/javascript">
	function initTimeline() {
		;
	}
	</SCRIPT>
<?php
}	
	
	$tpl->display("template/viewServicesLog.ihtml");
?>