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
	/*
	 * Required files
	 */
	require_once './include/reporting/dashboard/initReport.php';
	/*
	 *  Getting host to report 
	 */
	isset($_GET["host"]) ? $id = $_GET["host"] : $id = "NULL";
	isset($_POST["host"]) ? $id = $_POST["host"] : $id;
	 	
	/*
	 * Forms
	 */
	/* Host Selection */
	$formHost = new HTML_QuickForm('formHost', 'post', "?p=".$p);
	$redirect =& $formHost->addElement('hidden', 'o');
	$redirect->setValue($o);
	$hosts = getAllHostsForReporting($is_admin, $lcaHoststr);
	$selHost =& $formHost->addElement('select', 'host', _("Host"), $hosts, array("onChange" =>"this.form.submit();"));
	$formHost->addElement('hidden', 'period', $period);
	$formHost->addElement('hidden', 'start', $get_date_start);
	$formHost->addElement('hidden', 'end', $get_date_end);
	if (isset($id))
		$formHost->setDefaults(array('host' => $id));
	/* Set host id with period selection form */
	if ($id != "NULL")
		$formPeriod->addElement('hidden', 'host', $id);
	/*
	 * END OF FORMS
	 */
	/*
	 * Stats Display for selected host
	 */
	if (isset($id) && $id != "NULL"){
			/* Getting periods values */
			$dates = getPeriodToReport();
			$start_date = $dates[0];
			$end_date = $dates[1];
			$formPeriod->setDefaults(array('period' => $period));
			/* Getting host and his services stats */
			echo $start_date." ".$end_date."---<br>";
			$hostStats = array();
			$hostStats = getLogInDbForHost($id, $start_date, $end_date, $reportingTimePeriod) ;
			$hostServicesStats = array();
			$hostServicesStats =  getLogInDbForHostSVC($id, $start_date, $end_date, $reportingTimePeriod);
			/* Flash chart datas */
			$pie_chart_get_str =  "&value[down]=".$hostStats["DOWN_TP"]."&value[up]=".
						$hostStats["UP_TP"]."&value[unreachable]=".$hostStats["UNREACHABLE_TP"]."&value[undetermined]=".$hostStats["UNDETERMINED_TP"];
			/* Exporting variables for ihtml */
			$tpl->assign('name', $hosts[$id]);
			$tpl->assign('pie_chart_get_str', $pie_chart_get_str);
			$tpl->assign('totalAlert', $hostStats["TOTAL_ALERTS"]);
			$tpl->assign('totalTime',  $hostStats["TOTAL_TIME_F"]);
			$tpl->assign('summary',  $hostStats);
			$tpl->assign("components_avg", array_pop($hostServicesStats));
			$tpl->assign("components", $hostServicesStats);
			$tpl->assign('period_name', _(" From "));
			$tpl->assign('date_start', date("d/m/Y H:i",$start_date));
			$tpl->assign('to', _(" To "));
			$tpl->assign('date_end', date("d/m/Y H:i",$end_date));
			$tpl->assign('period', $period);
			$tpl->assign('start', $start_date);
			$tpl->assign('end', $end_date);
			$tpl->assign('host_id', $id);
	//		$tpl->assign('period', $var_url_export_csv);
	}
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
	if (isset($id) && $id != ""){
		# CSV export
		$tpl->assign("link_csv_url", "./include/reporting/dashboard/csvExport/csv_HostLogs.php?sid=".$sid."&host=".$id);
		$tpl->assign("link_csv_name", _("Export in CSV format"));
		# Status colors
		$color = substr($oreon->optGen["color_up"],1).':'.substr($oreon->optGen["color_down"],1).':'.substr($oreon->optGen["color_unreachable"],1).':'.substr($oreon->optGen["color_undetermined"],1);
		#Ajax timeline
		$type = 'Host';
		include("./include/reporting/dashboard/ajaxReporting_js.php");
	} else {
		?><script type="text/javascript"> function initTimeline() {;} </script> <?php
	}
	$tpl->display("template/viewHostLog.ihtml");
?>