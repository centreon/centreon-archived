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
	$items  = $oreon->user->access->getHostServices(($oreon->broker->getBroker() == "broker" ? $pearDBO : $pearDBndo), $host_id);

	$itemsForUrl = array();
	foreach ($items as $key => $value) {
		$itemsForUrl[str_replace(":", "%3A", $key)] = str_replace(":", "%3A", $value);
	}
	$service_name = $itemsForUrl[$service_id];

	$select = $form->addElement('select', 'item', _("Service"), $items, array("onChange" =>"this.form.submit();"));
	$form->addElement('hidden', 'period', $period);
	$form->addElement('hidden', 'StartDate', $get_date_start);
	$form->addElement('hidden', 'EndDate', $get_date_end);
	$form->addElement('hidden', 'p', $p);
	$redirect = $form->addElement('hidden', 'o');
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
		 * Chart datas
		 */
                $tpl->assign('service_ok', $serviceStats["OK_TP"]);
                $tpl->assign('service_warning', $serviceStats["WARNING_TP"]);
                $tpl->assign('service_critical', $serviceStats["CRITICAL_TP"]);
                $tpl->assign('service_unknown', $serviceStats["UNKNOWN_TP"]);
                $tpl->assign('service_undetermined', $serviceStats["UNDETERMINED_TP"]);
                $tpl->assign('service_maintenance', $serviceStats["MAINTENANCE_TP"]);

		/*
		 * Exporting variables for ihtml
		 */
		$tpl->assign('host_name', $host_name);
		$tpl->assign('name', $itemsForUrl[$service_id]);
		$tpl->assign('totalAlert', $serviceStats["TOTAL_ALERTS"]);
		$tpl->assign('totalTime',  $serviceStats["TOTAL_TIME_F"]);
		$tpl->assign('summary',  $serviceStats);
		$tpl->assign('from', _("From"));
		$tpl->assign('date_start', date(_("d/m/Y H:i"), $start_date));
		$tpl->assign('to', _("to"));
		$tpl->assign('date_end', date(_("d/m/Y H:i"), $end_date));
		$formPeriod->setDefaults(array('period' => $period));
		$tpl->assign('id', $service_id);
	}
	$tpl->assign('resumeTitle', _("Service state"));
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
		$tpl->assign("link_csv_url", "./include/reporting/dashboard/csvExport/csv_ServiceLogs.php?host=".$host_id."&service=".$service_id."&start=".$start_date."&end=".$end_date);
		$tpl->assign("link_csv_name", _("Export in CSV format"));

		/*
		 * status colors
		 */
		$color = substr($oreon->optGen["color_up"],1).':'.substr($oreon->optGen["color_down"],1).
						':'.substr($oreon->optGen["color_unreachable"],1).':'.substr($oreon->optGen["color_undetermined"], 1).
						':'.substr($oreon->optGen["color_maintenance"],1);

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
