<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

	if (!isset($oreon)) {
		exit;
	}

	$path = "./include/reporting/dashboard";

	/*
	 * Required Pear Lib
	 */
	require_once "HTML/QuickForm.php";
	require_once "HTML/QuickForm/Renderer/ArraySmarty.php";

	/*
	 * Require Centreon Class
	 */
	require_once "./class/centreonDuration.class.php";
	require_once "./class/centreonDB.class.php";

	/*
	 * Require centreon common lib
	 */
	require_once "./include/reporting/dashboard/common-Func.php";
	require_once "./include/reporting/dashboard/DB-Func.php";
	require_once "./include/common/common-Func.php";

	/*
	 * Add quickSearch toolbar
	 */
	if ($p != 30702) {
		require_once "./include/common/quickSearch.php";
	}

	/*
	 * Create DB connexion
	 */
	$pearDBO = new CentreonDB("centstorage");
	if ($oreon->broker->getBroker() == "ndo") {
		$pearDBndo 	= new CentreonDB("ndo");
	}

	$debug = 0;

	/*
	 * QuickForm templates
	 */
	$attrsTextI		= array("size"=>"3");
	$attrsText 		= array("size"=>"30");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"40");


	/*
	 * Smarty template initialization
	 */
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
	$oreon->optGen["color_maintenance"] = "#CC99FF";

	$tpl->assign('style_ok', 		"class='ListColCenter' style='background:" . $oreon->optGen["color_ok"]."'");
	$tpl->assign('style_ok_alert', 		"class='ListColCenter' style='width: 25px; background:" . $oreon->optGen["color_ok"]."'");
	$tpl->assign('style_warning' , 		"class='ListColCenter' style='background:" . $oreon->optGen["color_warning"]."'");
	$tpl->assign('style_warning_alert' , 	"class='ListColCenter' style='width: 25px; background:" . $oreon->optGen["color_warning"]."'");
	$tpl->assign('style_critical' , 	"class='ListColCenter' style='background:" . $oreon->optGen["color_critical"]."'");
	$tpl->assign('style_critical_alert' , 	"class='ListColCenter' style='width: 25px; background:" . $oreon->optGen["color_critical"]."'");
	$tpl->assign('style_unknown' , 		"class='ListColCenter' style='background:" . $oreon->optGen["color_unknown"]."'");
	$tpl->assign('style_unknown_alert' , 	"class='ListColCenter' style='width: 25px; background:" . $oreon->optGen["color_unknown"]."'");
	$tpl->assign('style_pending' , 		"class='ListColCenter' style='background:" . $oreon->optGen["color_undetermined"]."'");
	$tpl->assign('style_pending_alert' , 	"class='ListColCenter' style='width: 25px; background:" . $oreon->optGen["color_undetermined"]."'");
	$tpl->assign('style_maintenance' , 		"class='ListColCenter' style='background:" . $oreon->optGen["color_maintenance"]."'");

	$tpl->assign('actualTitle', _("Actual"));

	$tpl->assign('serviceTitle', _("Service"));
	$tpl->assign('hostTitle', _("Host name"));
	$tpl->assign("allTilte",  _("All"));
	$tpl->assign("averageTilte",  _("Average"));

	$tpl->assign('OKTitle', _("OK"));
	$tpl->assign('WarningTitle', _("Warning"));
	$tpl->assign('UnknownTitle', _("Unknown"));
	$tpl->assign('CriticalTitle', _("Critical"));
	$tpl->assign('PendingTitle', _("Undetermined"));
	$tpl->assign('MaintenanceTitle', _("Scheduled downtime"));

	$tpl->assign('stateLabel', _("State"));
	$tpl->assign('totalLabel', _("Total"));
	$tpl->assign('durationLabel', _("Duration"));
	$tpl->assign('totalTimeLabel', _("Total Time"));
	$tpl->assign('meanTimeLabel', _("Mean Time"));
	$tpl->assign('alertsLabel', _("Alerts"));

	$tpl->assign('DateTitle', _("Date"));
	$tpl->assign('EventTitle', _("Event"));
	$tpl->assign('InformationsTitle', _("Info"));

	$tpl->assign('periodTitle', _("Reporting Period"));
	$tpl->assign('periodORlabel', _("or"));
	$tpl->assign('logTitle', _("Today's Host log"));
	$tpl->assign('svcTitle', _("State Breakdowns For Host Services"));

	/*
	 * Definition of status
	 */
	 $state["UP"] = _("UP");
	 $state["DOWN"] = _("DOWN");
	 $state["UNREACHABLE"] = _("UNREACHABLE");
	 $state["UNDETERMINED"] = _("UNDETERMINED");
	 $state["MAINTENANCE"] = _("SCHEDULED DOWNTIME");
	 $tpl->assign('states', $state);

	 /*
	  * CSS Definition for status colors
	  */
	$style["UP"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_up"]."'";
	$style["DOWN"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_down"]."'";
	$style["UNREACHABLE"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_unreachable"]."'";
	$style["UNDETERMINED"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_undetermined"]."'";
	$style["MAINTENANCE"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_maintenance"]."'";
	$tpl->assign('style', $style);

	/*
	 * Init Timeperiod List
	 */

	/*
	 * Getting period table list to make the form period selection (today, this week etc.)
	 */
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
	$lcaHoststr 	= $oreon->user->access->getHostsString("ID", ($oreon->broker->getBroker() == "broker" ? $pearDBO : $pearDBndo));
	$lcaHostGroupstr = $oreon->user->access->getHostGroupsString();
	$lcaSvcstr 	= $oreon->user->access->getServicesString("ID", ($oreon->broker->getBroker() == "broker" ? $pearDBO : $pearDBndo));

	/*
	 * setting variables for link with services
	 */
	$period = (isset($_POST["period"])) ? $_POST["period"] : "";
	$period = (isset($_GET["period"])) ? $_GET["period"] : $period;
	$get_date_start = (isset($_POST["StartDate"])) ? $_POST["StartDate"] : "";
	$get_date_start = (isset($_GET["start"])) ? $_GET["start"] : $get_date_start;
	$get_date_end = (isset($_POST["EndDate"])) ? $_POST["EndDate"] : "";
	$get_date_end = (isset($_GET["end"])) ? $_GET["end"] : $get_date_end;
	if ($get_date_start == "" && $get_date_end == "" && $period == "")
		$period = "yesterday";
	$tpl->assign("get_date_start", $get_date_start);
	$tpl->assign("get_date_end", $get_date_end);
	$tpl->assign("get_period", $period);

	/*
	 * Period Selection form
	 */
	$formPeriod = new HTML_QuickForm('FormPeriod', 'post', "?p=".$p);
	$formPeriod->addElement('select', 'period', "", $periodList, array("id" => "presetPeriod", "onchange"=>"resetFields([this.form.StartDate, this.form.EndDate]);this.form.submit();"));
	$formPeriod->addElement('hidden', 'timeline', "1");
	$formPeriod->addElement('text', 'StartDate', _("From"), array("id"=>"StartDate", "size"=>10, "class"=>"datepicker", "onClick" => "javascript: togglePeriodType();"));
	$formPeriod->addElement('text', 'EndDate', _("to"), array("id"=>"EndDate", "size"=>10, "class"=>"datepicker", "onClick" => "javascript: togglePeriodType();"));
	$formPeriod->addElement('submit', 'button', _("Apply"));
	$formPeriod->setDefaults(array('period' => $period, "StartDate" => $get_date_start, "EndDate" => $get_date_end));
?>
<script src="./include/common/javascript/d3.min.js" language="javascript"></script>
<script src="./include/common/javascript/c3.min.js" language="javascript"></script>
<script type='text/javascript'>
function togglePeriodType()
{
	document.getElementById("presetPeriod").selectedIndex = 0;
}
</script>
