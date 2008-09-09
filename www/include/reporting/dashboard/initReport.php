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
 * For information : contact@centreon.com
 */

	if (!isset($oreon))
		exit;
		
	$path = "./include/reporting/dashboard";
	/*
	 * Required files
	 */	
	require_once 'HTML/QuickForm.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	require_once './class/other.class.php';
	require_once './include/reporting/dashboard/common-Func.php';
	require_once './include/reporting/dashboard/DB-Func.php';
	require_once './include/common/common-Func.php';
	require_once './include/common/common-Func-ACL.php';
	$debug = 0;
	
	/*
	 * QuickForm templates
	 */
	$attrsTextI		= array("size"=>"3");
	$attrsText 		= array("size"=>"30");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"40");
	

	/* Smarty template initialization */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "");
	$tpl->assign('o', $o);
	/*
	 *  Assign centreon path
	 */
	
	$tpl->assign("centreon_path", $centreon_path);
	
	/*
	 * Translations and styles
	 */
	 
	$oreon->optGen["color_undetermined"] = "#F0F0F0";

	$tpl->assign('style_ok', "class='ListColCenter' style='background:" . $oreon->optGen["color_ok"]."'");
	$tpl->assign('style_ok_alert', "class='ListColCenter' style='width: 25px; background:" . $oreon->optGen["color_ok"]."'");
	$tpl->assign('style_warning' , "class='ListColCenter' style='background:" . $oreon->optGen["color_warning"]."'");
	$tpl->assign('style_warning_alert' , "class='ListColCenter' style='width: 25px; background:" . $oreon->optGen["color_warning"]."'");
	$tpl->assign('style_critical' , "class='ListColCenter' style='background:" . $oreon->optGen["color_critical"]."'");
	$tpl->assign('style_critical_alert' , "class='ListColCenter' style='width: 25px; background:" . $oreon->optGen["color_critical"]."'");
	$tpl->assign('style_unknown' , "class='ListColCenter' style='background:" . $oreon->optGen["color_unknown"]."'");
	$tpl->assign('style_unknown_alert' , "class='ListColCenter' style='width: 25px; background:" . $oreon->optGen["color_unknown"]."'");
	$tpl->assign('style_pending' , "class='ListColCenter' style='background:" . $oreon->optGen["color_undetermined"]."'");
	$tpl->assign('style_pending_alert' , "class='ListColCenter' style='width: 25px; background:" . $oreon->optGen["color_undetermined"]."'");

	$tpl->assign('actualTitle', _(" Actual "));

	$tpl->assign('serviceTitle', _("Service"));
	$tpl->assign('hostTitle', _("Host"));
	$tpl->assign("allTilte",  _("All"));
	$tpl->assign("averageTilte",  _("Average"));

	$tpl->assign('OKTitle', _("OK"));
	$tpl->assign('WarningTitle', _("Warning"));
	$tpl->assign('UnknownTitle', _("Unknown"));
	$tpl->assign('CriticalTitle', _("Critical"));
	$tpl->assign('PendingTitle', _("Undetermined"));

	$tpl->assign('stateLabel', ("State"));
	$tpl->assign('totalLabel', ("Total"));
	$tpl->assign('durationLabel', ("Duration"));
	$tpl->assign('totalTimeLabel', ("Total Time"));
	$tpl->assign('meanTimeLabel', ("Mean Time"));
	$tpl->assign('alertsLabel', ("Alerts"));

	$tpl->assign('DateTitle', _("Date"));
	$tpl->assign('EventTitle', _("Event"));
	$tpl->assign('InformationsTitle', _("Info"));

	$tpl->assign('periodTitle', _("Period Selection"));
	$tpl->assign('resumeTitle', _("Host state"));
	$tpl->assign('logTitle', _("Today's Host log"));
	$tpl->assign('svcTitle', _("State Breakdowns For Host Services"));
	/*
	 * Definition of status
	 */
	 $state["UP"] = _("UP");
	 $state["DOWN"] = _("DOWN");
	 $state["UNREACHABLE"] = _("UNREACHABLE");
	 $state["UNDETERMINED"] = _("UNDETERMINED");
	 $tpl->assign('states', $state);
	 /*
	  * CSS Definition for status colors
	  */
	$style["UP"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_up"]."'";
	$style["DOWN"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_down"]."'";
	$style["UNREACHABLE"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_unreachable"]."'";		
	$style["UNDETERMINED"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_undetermined"]."'";
	$tpl->assign('style', $style);
	/*
	 * Init Timeperiod List
	 */
	
	# Getting period table list to make the form period selection (today, this week etc.)
	$periodList = getPeriodList();

	$color = array();
	$color["UNKNOWN"] =  substr($oreon->optGen["color_unknown"], 1);
	$color["UP"] =  substr($oreon->optGen["color_up"], 1);
	$color["DOWN"] =  substr($oreon->optGen["color_down"], 1);
	$color["UNREACHABLE"] =  substr($oreon->optGen["color_unreachable"], 1);
	$tpl->assign('color', $color);
	
	/*
	 * Getting timeperiod by day (example : 9:30 to 19:30 on monday,tue,wed,thu,fri)
	 */
	$reportingTimePeriod = getreportingTimePeriod();
	/*
	 * CSV export parameters
	 */
	 $var_url_export_csv = "";
	 /*
	  * LCA
	  */
	$lcaHostByName = getLcaHostByName($pearDB);
	$lcaHostByID = getLcaHostByID($pearDB);
	$lcaHoststr = getLCAHostStr($lcaHostByID["LcaHost"]);
	$lcaHostGroupstr = getLCAHGStr($lcaHostByID["LcaHostGroup"]);

	/* 
	 * setting variables for link with services
	 */
	$get_period = (isset($_POST["period"])) ? $_POST["period"] : ""; 
	$get_period = (isset($_GET["period"])) ? $_GET["period"] : $get_period;
	$get_start = (isset($_POST["start"])) ? $_POST["start"] : "";
	$get_start = (isset($_GET["start"])) ? $_GET["start"] : $get_start;
	$get_end = (isset($_POST["end"])) ? $_POST["end"] : "";
	$get_end = (isset($_GET["end"])) ? $_GET["end"] : $get_end;
	if ($get_start == "" && $get_end == "" && $get_period == "")
		$get_period = "yesterday";
	$tpl->assign("get_date_start", $get_start);
	$tpl->assign("get_date_end", $get_end);
	$tpl->assign("get_period", $get_period);
	/*
	 * Period Selection form
	 */
	$formPeriod = new HTML_QuickForm('FormPeriod', 'post', "?p=".$p);
	$formPeriod->addElement('select', 'period', _("Predefined : "), $periodList);
	$formPeriod->addElement('hidden', 'timeline', "1");
	$formPeriod->addElement('header', 'title', _("Custom selection"));
	$formPeriod->addElement('text', 'start', _("Begin date"));
	$formPeriod->addElement('button', "startD", _("Modify"), array("onclick"=>"displayDatePicker('start')"));
	$formPeriod->addElement('text', 'end', _("End date"));
	$formPeriod->addElement('button', "endD", _("Modify"), array("onclick"=>"displayDatePicker('end')"));
	$formPeriod->addElement('submit', 'submit', _("View"));
	$formPeriod->setDefaults(array('period' => $get_period, "start" => $get_start, "end" => $get_end));
?>