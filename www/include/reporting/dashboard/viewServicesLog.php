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
 * SVN : $URL$
 * SVN : $Id$
 * 
 */

	if (!isset($oreon))
		exit;
	/*
	 * Required files
	 */
	require_once './include/reporting/dashboard/initReport.php';
	
	/*
	 *  Getting service to report 
	 */
	isset($_GET["host_id"]) ? $host_id = $_GET["host_id"] : $host_id = "NULL";
	isset($_POST["host_id"]) ? $host_id = $_POST["host_id"] : $host_id;
	isset($_GET["item"]) ? $service_id = $_GET["item"] : $service_id = "NULL";
	isset($_POST["item"]) ? $service_id = $_POST["item"] : $service_id;
	
	/*
	 * FORMS
	 */
	$form = new HTML_QuickForm('formItem', 'post', "?p=".$p);

	$host_name = getMyHostName($host_id);
	$items  = $oreon->user->access->getHostServices($pearDBndo, $host_id);	
	$select =& $form->addElement('select', 'item', _("Service"), $items, array("onChange" =>"this.form.submit();"));
	$form->addElement('hidden', 'period', $period);
	$form->addElement('hidden', 'start', $get_date_start);
	$form->addElement('hidden', 'end', $get_date_end);
	$form->addElement('hidden', 'p', $p);
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	/*
	 * Set service id with period selection form
	 */	
	if ($service_id != "NULL" && $host_id != "NULL") {
		$formPeriod->addElement('hidden', 'item', $service_id);
		$formPeriod->addElement('hidden', 'host_id', $host_id);
		$form->addElement('hidden', 'host_id', $host_id);
		$form->setDefaults(array('item' => $service_id));
	}
		 
	/*
	 * Stats Display for selected service
	 */
	if (isset($host_id) && $host_id != "NULL" && isset($service_id) && $service_id != "NULL"){
		/*
		 * Getting periods values
		 */
		$dates = getPeriodToReport();
		$start_date = $dates[0];
		$end_date = $dates[1];
		
		/* 
		 * Getting hostgroup and his hosts stats
		 */
		$serviceStats = array();
		$serviceStats = getLogInDbForOneSVC($host_id, $service_id, $start_date, $end_date, $reportingTimePeriod) ;
		
		/* 
		 * Flash chart datas
		 */
		$pie_chart_get_str =  "&value[ok]=".$serviceStats["OK_TP"]."&value[warning]=".
					$serviceStats["WARNING_TP"]."&value[critical]=".$serviceStats["CRITICAL_TP"].
					"&value[unknown]=".$serviceStats["UNKNOWN_TP"]."&value[undetermined]=".$serviceStats["UNDETERMINED_TP"];
		
		/* 
		 * Exporting variables for ihtml
		 */
		$tpl->assign('host_name', $host_name);
		$tpl->assign('name', $items[$service_id]);
		$tpl->assign('pie_chart_get_str', $pie_chart_get_str);
		$tpl->assign('totalAlert', $serviceStats["TOTAL_ALERTS"]);
		$tpl->assign('totalTime',  $serviceStats["TOTAL_TIME_F"]);
		$tpl->assign('summary',  $serviceStats);
		$tpl->assign('from', _(" From "));
		$tpl->assign('date_start', date("d/m/Y H:i", $start_date));
		$tpl->assign('to', _(" to "));
		$tpl->assign('date_end', date("d/m/Y H:i", $end_date));
		$tpl->assign('resumeTitle', _("Service state"));
		$formPeriod->setDefaults(array('period' => $period));
		$tpl->assign('id', $service_id);
	}
	
	$tpl->assign('p', $p);
	
	/*
	 * Rendering forms
	 */
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$formPeriod->accept($renderer);
	$tpl->assign('formPeriod', $renderer->toArray());
	
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
	$tpl->assign('formItem', $renderer->toArray());
	
	/*
	 * Ajax timeline and CSV export initialization
	 */
	if (isset($host_id) && $host_id != "NULL" && isset($service_id) && $service_id != "NULL"){
		/* 
		 * CSV Export
		 */
		$tpl->assign("link_csv_url", "./include/reporting/dashboard/csvExport/csv_ServiceLogs.php?sid=".$sid."&host=".$host_id."&service=".$service_id."&start=".$start_date."&end=".$end_date);
		$tpl->assign("link_csv_name", _("Export in CSV format"));
		
		/* 
		 * status colors
		 */
		$color = substr($oreon->optGen["color_up"],1).':'.substr($oreon->optGen["color_down"],1).':'.substr($oreon->optGen["color_unreachable"],1).':'.substr($oreon->optGen["color_undetermined"],1);
		
		/* 
		 * Ajax timeline
		 */
		$type = 'Service';
		include("./include/reporting/dashboard/ajaxReporting_js.php");

	} else {
		?><script type="text/javascript"> function initTimeline() {;} </script> <?php
	}
	$tpl->display("template/viewServicesLog.ihtml");
?>