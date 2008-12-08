<?php
/*
 * Copyright 2005-2009 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 * 
 * This program is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software 
 * Foundation ; either version 2 of the License.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with 
 * this program; if not, see <http://www.gnu.org/licenses>.
 * 
 * Linking this program statically or dynamically with other modules is making a 
 * combined work based on this program. Thus, the terms and conditions of the GNU 
 * General Public License cover the whole combination.
 * 
 * As a special exception, the copyright holders of this program give MERETHIS 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of MERETHIS choice, provided that 
 * MERETHIS also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 * SVN : $URL
 * SVN : $Id: initReport.php 7139 2008-11-24 17:19:45Z jmathis $
 * 
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
	$period = (isset($_POST["period"])) ? $_POST["period"] : ""; 
	$period = (isset($_GET["period"])) ? $_GET["period"] : $period;
	$get_date_start = (isset($_POST["start"])) ? $_POST["start"] : "";
	$get_date_start = (isset($_GET["start"])) ? $_GET["start"] : $get_date_start;
	$get_date_end = (isset($_POST["end"])) ? $_POST["end"] : "";
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
	$formPeriod->addElement('select', 'period', _("Predefined : "), $periodList);
	$formPeriod->addElement('hidden', 'timeline', "1");
	$formPeriod->addElement('header', 'title', _("Custom selection"));
	$formPeriod->addElement('text', 'start', _("Begin date"));
	$formPeriod->addElement('button', "startD", _("Modify"), array("onclick"=>"displayDatePicker('start')"));
	$formPeriod->addElement('text', 'end', _("End date"));
	$formPeriod->addElement('button', "endD", _("Modify"), array("onclick"=>"displayDatePicker('end')"));
	$formPeriod->addElement('submit', 'submit', _("View"));
	$formPeriod->setDefaults(array('period' => $period, "start" => $get_date_start, "end" => $get_date_end));
?>