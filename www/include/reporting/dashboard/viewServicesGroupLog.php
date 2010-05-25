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
	
	/*
	 * Required files
	 */
	require_once './include/reporting/dashboard/initReport.php';
	
	/*
	 *  Getting servic egroup to report 
	 */
	isset($_GET["item"]) ? $id = $_GET["item"] : $id = "NULL";
	isset($_POST["item"]) ? $id = $_POST["item"] : $id;
	isset($_POST["search"]) ? $search = $_POST["search"] : "";
	
	/*
	 * FORMS
	 */

	$form = new HTML_QuickForm('formItem', 'post', "?p=".$p);
	
	$items = getAllServicesgroupsForReporting($search);
	$form->addElement('select', 'item', _("Service Group"), $items, array("onChange" =>"this.form.submit();"));
	$form->addElement('hidden', 'period', $period);
	$form->addElement('hidden', 'StartDate', $get_date_start);
	$form->addElement('hidden', 'EndDate', $get_date_end);
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	if (isset($id))
		$form->setDefaults(array('item' => $id));
	
	/* 
	 * Set servicegroup id with period selection form
	 */	
	if ($id != "NULL")
		$formPeriod->addElement('hidden', 'item', $id);
	 
	/*
	 * Stats Display for selected services group
	 */
	if (isset($id) && $id != "NULL"){
		
		/* 
		 * Getting periods values
		 */
		$dates = getPeriodToReport();
		$start_date = $dates[0];
		$end_date = $dates[1];
		
		/*
		 * Getting hostgroup and his hosts stats
		 */
		$servicesgroupStats = array();
		$servicesgroupStats = getLogInDbForServicesGroup($id, $start_date, $end_date, $reportingTimePeriod) ;

		/* 
		 * Flash chart datas
		 */
		$pie_chart_get_str =  "&value[ok]=".$servicesgroupStats["average"]["OK_TP"]."&value[warning]=".
								$servicesgroupStats["average"]["WARNING_TP"]."&value[critical]=".$servicesgroupStats["average"]["CRITICAL_TP"].
								"&value[unknown]=".$servicesgroupStats["average"]["UNKNOWN_TP"]."&value[undetermined]=".$servicesgroupStats["average"]["UNDETERMINED_TP"];

		/* 
		 * Exporting variables for ihtml
		 */
		$tpl->assign('name', $items[$id]);
		$tpl->assign('pie_chart_get_str', $pie_chart_get_str);
		$tpl->assign('totalAlert', $servicesgroupStats["average"]["TOTAL_ALERTS"]);
		$tpl->assign('summary',  $servicesgroupStats["average"]);

		/* 
		 * Removing average infos from table
		 */
		$servicesgroupFinalStats = array();
		foreach ($servicesgroupStats as $key => $value) {
			if ($key != "average")
				$servicesgroupFinalStats[$key] = $value;
		}
		
		$tpl->assign("components", $servicesgroupFinalStats);
		$tpl->assign('period_name', _("From"));
		$tpl->assign('date_start', date(_("d/m/Y H:i"),$start_date));
		$tpl->assign('to', _("to"));
		$tpl->assign('date_end', date(_("d/m/Y H:i"),$end_date));
		$tpl->assign('period', $period);		
		$formPeriod->setDefaults(array('period' => $period));
		$tpl->assign('id', $id);
		$tpl->assign('Alert', _("Alert"));
	}
	$tpl->assign('resumeTitle', _("Service group state"));
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
	if (isset($id) && $id != "NULL"){
		/* 
		 * CSV export
		 */
		$tpl->assign("link_csv_url", "./include/reporting/dashboard/csvExport/csv_ServiceGroupLogs.php?sid=".$sid."&servicegroup=".$id."&start=".$start_date."&end=".$end_date);
		$tpl->assign("link_csv_name", _("Export in CSV format"));

		/* 
		 * Status colors 
		 */
		$color = substr($oreon->optGen["color_up"],1).':'.substr($oreon->optGen["color_down"],1).':'.substr($oreon->optGen["color_unreachable"],1).':'.substr($oreon->optGen["color_undetermined"],1);

		/*
		 * Ajax timeline
		 */
		$type = 'ServiceGroup';
		include("./include/reporting/dashboard/ajaxReporting_js.php");
		
	} else {
		?><script type="text/javascript"> function initTimeline() {;} </script> <?php
	}
	
	$tpl->display("template/viewServicesGroupLog.ihtml");
?>