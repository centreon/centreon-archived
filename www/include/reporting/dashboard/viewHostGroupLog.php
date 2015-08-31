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

	if (!isset($oreon))
		exit;

	if (!isset($search))
		$search = "";

	/*
	 * Required files
	 */
	require_once './include/reporting/dashboard/initReport.php';

	/*
	 *  Getting hostgroup to report
	 */
	isset($_GET["item"]) ? $id = $_GET["item"] : $id = "NULL";
	isset($_POST["item"]) ? $id = $_POST["item"] : $id;
	isset($_POST["search"]) ? $search = $_POST["search"] : "";

	/*
	 * Formulary
	 *
	 * Hostgroup Selection
	 *
	 */

	$form = new HTML_QuickForm('formItem', 'post', "?p=".$p);

	$items = getAllHostgroupsForReporting($is_admin, $lcaHostGroupstr, $search);
	$select = $form->addElement('select', 'item', _("Host Group"), $items, array("onChange" =>"this.form.submit();"));

	$form->addElement('hidden', 'period', $period);
	$form->addElement('hidden', 'StartDate', $get_date_start);
	$form->addElement('hidden', 'EndDate', $get_date_end);
	$redirect = $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	if (isset($id))
		$form->setDefaults(array('item' => $id));

	/*
	 * Set hostgroup id with period selection form
	 */
	if ($id != "NULL") {
		$formPeriod->addElement('hidden', 'item', $id);
	}

	/*
	 * Stats Display for selected hostgroup
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
		$hostgroupStats = array();
		$hostgroupStats = getLogInDbForHostGroup($id, $start_date, $end_date, $reportingTimePeriod) ;

		/*
		 * Chart datas
		 */
                $tpl->assign('hostgroup_up', $hostgroupStats["average"]["UP_TP"]);
                $tpl->assign('hostgroup_down', $hostgroupStats["average"]["DOWN_TP"]);
                $tpl->assign('hostgroup_unreachable', $hostgroupStats["average"]["UNREACHABLE_TP"]);
                $tpl->assign('hostgroup_undetermined', $hostgroupStats["average"]["UNDETERMINED_TP"]);
                $tpl->assign('hostgroup_maintenance', $hostgroupStats["average"]["MAINTENANCE_TP"]);

		/*
		 * Exporting variables for ihtml
		 */
		$tpl->assign('name', $items[$id]);
		$tpl->assign('totalAlert', $hostgroupStats["average"]["TOTAL_ALERTS"]);
		$tpl->assign('summary',  $hostgroupStats["average"]);

		/*
		 * removing average infos from table
		 */
		$hostgroupFinalStats = array();
		foreach ($hostgroupStats as $key => $value) {
			if ($key != "average")
				$hostgroupFinalStats[$key] = $value;
		}

		$tpl->assign("components", $hostgroupFinalStats);
		$tpl->assign('period_name', _("From"));
		$tpl->assign('date_start', date(_("d/m/Y H:i"),$start_date));
		$tpl->assign('to', _("to"));
		$tpl->assign('date_end', date(_("d/m/Y H:i"), $end_date));
		$tpl->assign('period', $period);
		$formPeriod->setDefaults(array('period' => $period));
		$tpl->assign('id', $id);
		$tpl->assign('Alert', _("Alert"));
	}
	$tpl->assign('resumeTitle', _("Hosts group state"));

	/*
	 * Rendering Forms
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
		$tpl->assign("link_csv_url", "./include/reporting/dashboard/csvExport/csv_HostGroupLogs.php?hostgroup=".$id."&start=".$start_date."&end=".$end_date);
		$tpl->assign("link_csv_name", _("Export in CSV format"));

		/*
		 * Status colors
		 */
		$color = substr($oreon->optGen["color_up"],1).
				':'.substr($oreon->optGen["color_down"],1).
				':'.substr($oreon->optGen["color_unreachable"],1).
				':'.substr($oreon->optGen["color_maintenance"],1).
				':'.substr($oreon->optGen["color_undetermined"],1);

		/*
		 * Ajax timeline
		 */
		$type = 'HostGroup';
		include("./include/reporting/dashboard/ajaxReporting_js.php");

	} else {
		?><script type="text/javascript"> function initTimeline() {;} </script><?php
	}

	$tpl->display("template/viewHostGroupLog.ihtml");
?>
