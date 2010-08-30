<?php
/*
 * Copyright 2005-2010 MERETHIS
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
 * SVN : $URL$
 * SVN : $Id$
 * 
 */
 
 	if (!isset($oreon))
		exit;
	
	if (!isset($search))
		$search = "";
	
	/*
	 * Required files
	 */
	require_once './include/reporting/dashboard/initReport.php';
	
	/*
	 *  Getting host to report 
	 */
	isset($_GET["host"]) ? $id = $_GET["host"] : $id = "NULL";
	isset($_POST["host"]) ? $id = $_POST["host"] : htmlentities($id, ENT_QUOTES, "UTF-8");
	
	/*
	 * Formulary
	 */
	
	/* 
	 * Host Selection 
	 */
	$formHost = new HTML_QuickForm('formHost', 'post', "?p=".$p);
	$redirect =& $formHost->addElement('hidden', 'o');
	$redirect->setValue($o);
	
	$hosts = getAllHostsForReporting($is_admin, $lcaHoststr, $search);
	$selHost =& $formHost->addElement('select', 'host', _("Host"), $hosts, array("onChange" =>"this.form.submit();"));
	$formHost->addElement('hidden', 'period', $period);
	/*$formHost->addElement('hidden', 'StartDate', $get_date_start);
	$formHost->addElement('hidden', 'EndDate', $get_date_end);*/
	if (isset($id))
		$formHost->setDefaults(array('host' => $id));
	
	/* 
	 * Set host id with period selection form 
	 */
	if ($id != "NULL")
		$formPeriod->addElement('hidden', 'host', $id);

	/*
	 * Stats Display for selected host
	 */
	if (isset($id) && $id != "NULL"){
		
		/* 
		 * Getting periods values 
		 */
		$dates = getPeriodToReport();
		$start_date = $dates[0];
		$end_date = $dates[1];
		$formPeriod->setDefaults(array('period' => $period));
		
		/* 
		 * Getting host and his services stats 
		 */
		$hostStats = array();
		$hostStats = getLogInDbForHost($id, $start_date, $end_date, $reportingTimePeriod) ;
		$hostServicesStats = array();
		$hostServicesStats =  getLogInDbForHostSVC($id, $start_date, $end_date, $reportingTimePeriod);
		
		/* 
		 * Flash chart datas 
		 */
		$pie_chart_get_str =  "&value[down]=".$hostStats["DOWN_TP"]."&value[up]=".
		$hostStats["UP_TP"]."&value[unreachable]=".$hostStats["UNREACHABLE_TP"]."&value[undetermined]=".$hostStats["UNDETERMINED_TP"];
		
		/* 
		 * Exporting variables for ihtml 
		 */
		$tpl->assign("name", $hosts[$id]);
		$tpl->assign("pie_chart_get_str", $pie_chart_get_str);
		$tpl->assign("totalAlert", $hostStats["TOTAL_ALERTS"]);
		$tpl->assign("totalTime",  $hostStats["TOTAL_TIME_F"]);
		$tpl->assign("summary",  $hostStats);
		$tpl->assign("components_avg", array_pop($hostServicesStats));
		$tpl->assign("components", $hostServicesStats);
		$tpl->assign("period_name", _("From"));
		$tpl->assign("date_start", date(_("d/m/Y H:i"),$start_date));
		$tpl->assign("to", _("to"));
		$tpl->assign("date_end", date(_("d/m/Y H:i"),$end_date));
		$tpl->assign("period", $period);		
		$tpl->assign("host_id", $id);
		$tpl->assign("Alert", _("Alert"));
	}
	$tpl->assign("resumeTitle", _("Host state"));
	
	/*
	 * Rendering Forms
	 */
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$formPeriod->accept($renderer);
	$tpl->assign('formPeriod', $renderer->toArray());
	
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$formHost->accept($renderer);
	$tpl->assign('formHost', $renderer->toArray());
	
	/*
	 * Ajax TimeLine and CSV export initialization 
	 */
	if (isset($id) && $id != "NULL"){
		/*
		 * CSV export
		 */
		$tpl->assign("link_csv_url", "./include/reporting/dashboard/csvExport/csv_HostLogs.php?sid=".$sid."&host=".$id."&start=".$start_date."&end=".$end_date);
		$tpl->assign("link_csv_name", _("Export in CSV format"));
		
		/*
		 * Status colors
		 */
		$color = substr($oreon->optGen["color_up"],1).':'.substr($oreon->optGen["color_down"],1).':'.substr($oreon->optGen["color_unreachable"],1).':'.substr($oreon->optGen["color_undetermined"],1);
		
		/*
		 * Ajax timeline
		 */
		$type = 'Host';
		include("./include/reporting/dashboard/ajaxReporting_js.php");
	
	} else {
		?><script type="text/javascript"> function initTimeline() {;} </script> <?php
	}
	$tpl->display("template/viewHostLog.ihtml");
?>