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
	$redirect = $formHost->addElement('hidden', 'o');
	$redirect->setValue($o);

	$hosts = getAllHostsForReporting($is_admin, $lcaHoststr, $search);
	$selHost = $formHost->addElement('select', 'host', _("Host"), $hosts, array("onChange" =>"this.form.submit();"));
	$formHost->addElement('hidden', 'period', $period);
	$formHost->addElement('hidden', 'StartDate', $get_date_start);
	$formHost->addElement('hidden', 'EndDate', $get_date_end);
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
		 * Chart datas
		 */
                $tpl->assign('host_up', $hostStats["UP_TP"]);
                $tpl->assign('host_down', $hostStats["DOWN_TP"]);
                $tpl->assign('host_unreachable', $hostStats["UNREACHABLE_TP"]);
                $tpl->assign('host_undetermined', $hostStats["UNDETERMINED_TP"]);
                $tpl->assign('host_maintenance', $hostStats["MAINTENANCE_TP"]);

		/*
		 * Exporting variables for ihtml
		 */
		$tpl->assign("name", $hosts[$id]);
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
		$tpl->assign("link_csv_url", "./include/reporting/dashboard/csvExport/csv_HostLogs.php?host=".$id."&start=".$start_date."&end=".$end_date);
		$tpl->assign("link_csv_name", _("Export in CSV format"));

		/*
		 * Status colors
		 */
		$color = substr($oreon->optGen["color_up"],1).':'.substr($oreon->optGen["color_down"],1).
						':'.substr($oreon->optGen["color_unreachable"],1).':'.substr($oreon->optGen["color_undetermined"],1).
						':'.substr($oreon->optGen["color_maintenance"],1);

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
