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

	if (!isset($oreon))
		exit;
		
	$debug = 0;
	
	/*
	 * QuickForm templates
	 */
	$attrsTextI		= array("size"=>"3");
	$attrsText 		= array("size"=>"30");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"40");
	
	/*
	 *  Assign centreon path
	 */
	
	$tpl->assign("centreon_path", $centreon_path);
	
	/*
	 * Translations and styles
	 */

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

	$tpl->assign('actualTitle', _(" Actual "));

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
	
	/*
	 * Init Timeperiod List
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

	$color = array();
	$color["UNKNOWN"] =  substr($oreon->optGen["color_unknown"], 1);
	$color["UP"] =  substr($oreon->optGen["color_up"], 1);
	$color["DOWN"] =  substr($oreon->optGen["color_down"], 1);
	$color["UNREACHABLE"] =  substr($oreon->optGen["color_unreachable"], 1);
	$tpl->assign('color', $color);
	
?>