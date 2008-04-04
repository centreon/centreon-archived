<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus - Cedrick Facon 
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

	if (!isset($oreon))
		exit;

	if (!$is_admin){
		$lca = getLcaHostByName($pearDB);	
	}

	# Smarty template Init
	$path = "./include/reporting/dashboard";
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "");
	$tpl->assign('o', $o);

	isset ($_GET["host"]) ? $mhost = $_GET["host"] : $mhost = NULL;
	isset ($_POST["host"]) ? $mhost = $_POST["host"] : $mhost = $mhost;

	require_once './class/other.class.php';
	require_once './include/common/common-Func.php';
	require_once './include/common/common-Func-ACL.php';
	require_once 'HostLog.php';

	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	if (!$is_admin)
		$lca = getLcaHostByName($pearDB);
	
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

	#
	## Selection de l'host
	#
	$formHost = new HTML_QuickForm('formHost', 'post', "?p=".$p);

	$var_url_export_csv = "";
		
	if($mhost)	{
		if($period == "customized") {
			$formHost->addElement('hidden', 'end', $end);
			$formHost->addElement('hidden', 'start', $start);
			$var_url_export_csv = "&period=customized&start=".$start_var."&end="."$end_var"."&lang=" .$oreon->user->get_lang();
		}
		else {
			$var_url_export_csv = "&period=".$period."&lang=" .$oreon->user->get_lang();
			$formHost->addElement('hidden', 'period', $period);
		}
	}

	#
	## Selection de l'host/service (suite)
	#
	$res =& $pearDB->query("SELECT host_name FROM host where host_activate = '1' AND host_id IN (".$lcaHoststr.") and host_register = '1' ORDER BY host_name");

	while ($h = $res->fetchRow()){
		if (!isset($lca) || isset($lca["LcaHost"][$h['host_name']]))
			$host[$h["host_name"]] = $h["host_name"];
	}
	$selHost =& $formHost->addElement('select', 'host', _("Host"), $host, array("onChange" =>"this.form.submit();"));

	if (isset($_POST["host"])){
		$formHost->setDefaults(array('host' => $_POST["host"]));
	} else if (isset($_GET["host"])){
		$formHost->setDefaults(array('host' => $_GET["host"]));
	}

	#
	## Time select
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
	
	$formPeriod = new HTML_QuickForm('FormPeriod', 'post', "?p=".$p."&type_period=predefined");
	$selHost =& $formPeriod->addElement('select', 'period', _("Predefined:"), $periodList);

	isset($mhost) ? $formPeriod->addElement('hidden', 'host', $mhost) : NULL;
	$formPeriod->addElement('hidden', 'timeline', "1");

	$formPeriod->addElement('hidden', 'timeline', "1");
	$formPeriod->addElement('header', 'title', _("If customized period..."));
	$formPeriod->addElement('text', 'start', _("Begin date"));
	$formPeriod->addElement('button', "startD", _("Modify"), array("onclick"=>"displayDatePicker('start')"));
	$formPeriod->addElement('text', 'end', _("End date"));
	$formPeriod->addElement('button', "endD", _("Modify"), array("onclick"=>"displayDatePicker('end')"));
	$sub =& $formPeriod->addElement('submit', 'submit', _("View"));
	$res =& $formPeriod->addElement('reset', 'reset', _("Reset"));

	if ($mhost){
		$i=0;
	
	$tpl->assign('infosTitle', _("Duration : ") . Duration::toString($end_date_select - $start_date_select));
	$tpl->assign('host_name', $mhost);
	global $host_name;
	$host_name = $mhost;

	$tpl->assign('totalAlert', $totalAlert);
	$tpl->assign('totalTime', Duration::toString($totalTime));
	$tpl->assign('totalpTime', $totalpTime);
	$tpl->assign('totalpkTime', $totalpkTime);

	global $status;
	$tpl->assign('status', $status);
	
	$tab_resume[0]["style"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_up"]."'";
	$tab_resume[1]["style"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_down"]."'";
	$tab_resume[2]["style"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_unreachable"]."'";		
	$tab_resume[3]["style"] =  "class='ListColCenter' style='background:#cccccc'";
	
	$tpl->assign("tab_resume", $tab_resume);
	if (isset($tab_svc))
		$tpl->assign("tab_svc", $tab_svc);
	$tpl->assign("tab_svc_average", $tab_svc_average);

	$tt = 0 + ($ed - $sd);

	$tpl->assign('infosTitle', _("Duration : ") . Duration::toString($tt));
	}## end of period requirement

	$tpl->assign("tab_log", $tab_log);
	$tpl->assign('actualTitle', _(" Actual "));

	$tpl->assign('date_start_select', $start_date_select);
	$tpl->assign('date_end_select', $end_date_select);
	$tpl->assign('to', _(" to "));
	$tpl->assign('period_name', _(" From "));
	$tpl->assign('period', $var_url_export_csv);

	$tpl->assign('style_ok', "class='ListColCenter' style='background:" . $oreon->optGen["color_ok"]."'");
	$tpl->assign('style_ok_alert', "class='ListColCenter' style='width: 25px; background:" . $oreon->optGen["color_ok"]."'");
	$tpl->assign('style_warning' , "class='ListColCenter' style='background:" . $oreon->optGen["color_warning"]."'");
	$tpl->assign('style_warning_alert' , "class='ListColCenter' style='width: 25px; background:" . $oreon->optGen["color_warning"]."'");
	$tpl->assign('style_critical' , "class='ListColCenter' style='background:" . $oreon->optGen["color_critical"]."'");
	$tpl->assign('style_critical_alert' , "class='ListColCenter' style='width: 25px; background:" . $oreon->optGen["color_critical"]."'");
	$tpl->assign('style_unknown' , "class='ListColCenter' style='background:" . $oreon->optGen["color_unknown"]."'");
	$tpl->assign('style_unknown_alert' , "class='ListColCenter' style='width: 25px; background:" . $oreon->optGen["color_unknown"]."'");
	$tpl->assign('style_pending' , "class='ListColCenter' style='background:#cccccc'");
	$tpl->assign('style_pending_alert' , "class='ListColCenter' style='width: 25px; background:#cccccc'");

	$tpl->assign('serviceTilte', _("Service"));
	$tpl->assign('hostTitle', _("Host"));
	$tpl->assign("allTilte",  _("All"));
	$tpl->assign("averageTilte",  _("Average"));

	$tpl->assign('OKTitle', _("OK"));
	$tpl->assign('WarningTitle', _("Warning"));
	$tpl->assign('UnknownTitle', _("Unknown"));
	$tpl->assign('CriticalTitle', _("Critical"));
	$tpl->assign('PendingTitle', _("Undetermined"));

	$tpl->assign('StateTitle', _("Status"));
	$tpl->assign('TimeTitle', _("Time"));
	$tpl->assign('TimeTotalTitle', _("Total Time"));
	$tpl->assign('KnownTimeTitle', _("Known Time"));
	$tpl->assign('AlertTitle', _("Alert"));

	$tpl->assign('DateTitle', _("Date"));
	$tpl->assign('EventTitle', _("Event"));
	$tpl->assign('InformationsTitle', _("Info"));

	$tpl->assign('periodTitle', _("Period Selection"));
	$tpl->assign('resumeTitle', _("Host state"));
	$tpl->assign('logTitle', _("Today's Host log"));
	$tpl->assign('svcTitle', _("State Breakdowns For Host Services"));

	$formPeriod->setDefaults(array('period' => $period));

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
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$formHost->accept($renderer);
	$tpl->assign('formHost', $renderer->toArray());
	$tpl->assign('lang', $lang);
	$tpl->assign("p", $p);

	if($mhost){
		$tpl->assign("link_csv_url", "./include/reporting/dashboard/ExportCSV_HostLog.php?sid=".$sid."&host=".$mhost.$var_url_export_csv);
		$tpl->assign("link_csv_name", "Export CSV");
	}

	# For today in timeline
	$tt = 0 + ($today_end - $today_start);
	$today_pending = $tt - ($today_down + $today_up + $today_unreachable);
	$today_pending = round(($today_pending/$tt *100),2);
	$today_up = ($today_up <= 0) ? 0 : round($today_up / $tt *100,2);
	$today_down = ($today_down <= 0) ? 0 : round($today_down / $tt *100,2);
	$today_unreachable = ($today_unreachable <= 0) ? 0 : round($today_unreachable / $tt *100,2);
	$today_pending = ($today_pending < 0.1) ? "0" : $today_pending;

	if ($mhost)	{
		$color = substr($oreon->optGen["color_up"],1) .':'.
		 		 substr($oreon->optGen["color_down"],1) .':'.
		 		 substr($oreon->optGen["color_unreachable"],1) .':'. 
		 		 substr($oreon->optGen["color_unknown"],1);
	
		$today_var = '&today_up='.$today_up . '&today_down='.$today_down.'&today_unreachable='.$today_unreachable. '&today_pending=' . $today_pending;
		$today_var .= '&today_UPnbEvent='.$today_UPnbEvent.'&today_UNREACHABLEnbEvent='.$today_UNREACHABLEnbEvent.'&today_DOWNnbEvent='.$today_DOWNnbEvent;

		$type = 'Host';
		include('ajaxReporting_js.php');
	} else {
		?>
		<script type="text/javascript">
		function initTimeline() {
			;
		}
		</SCRIPT>
		<?php
	}
	$tpl->display("template/viewHostLog.ihtml");
?>