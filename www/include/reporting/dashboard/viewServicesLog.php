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
	 *  Getting service to report 
	 */
	isset($_GET["host_id"]) ? $host_id = $_GET["host_id"] : $host_id = "NULL";
	isset($_POST["host_id"]) ? $host_id = $_POST["host_id"] : $host_id;
	isset($_GET["item"]) ? $service_id = $_GET["item"] : $service_id = "NULL";
	isset($_POST["item"]) ? $service_id = $_POST["item"] : $service_id;
	/*
	 * FORMS
	 */	
	/* service Selection */
	$host_name = getHostNameFromId($host_id);
	$items  = getHostServices($host_id);
	$form = new HTML_QuickForm('formItem', 'post', "?p=".$p);
	$form->addElement('hidden', 'p', $p);
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	$select =& $form->addElement('select', 'item', _("Service"), $items, array("onChange" =>"this.form.submit();"));
	$form->addElement('hidden', 'period', $period);
	$form->addElement('hidden', 'start', $get_date_start);
	$form->addElement('hidden', 'end', $get_date_end);
	/* Set service id with period selection form */	
	if ($service_id != "NULL" && $host_id != "NULL") {
		$formPeriod->addElement('hidden', 'item', $service_id);
		$formPeriod->addElement('hidden', 'host_id', $host_id);
		$form->addElement('hidden', 'host_id', $host_id);
		$form->setDefaults(array('item' => $service_id));
	}
	
	/* page id */
	$tpl->assign('p', $p);
	/*
	 * END OF FORMS
	 */
	 
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
			/* Getting hostgroup and his hosts stats */
			$serviceStats = array();
			$serviceStats = getLogInDbForOneSVC($host_id, $service_id, $start_date, $end_date, $reportingTimePeriod) ;
			/* Flash chart datas */
			$pie_chart_get_str =  "&value[ok]=".$serviceStats["OK_TP"]."&value[warning]=".
						$serviceStats["WARNING_TP"]."&value[critical]=".$serviceStats["CRITICAL_TP"].
						"&value[unknown]=".$serviceStats["UNKNOWN_TP"]."&value[undetermined]=".$serviceStats["UNDETERMINED_TP"];
			/* Exporting variables for ihtml */
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
	//		$tpl->assign('period', $var_url_export_csv);
			$formPeriod->setDefaults(array('period' => $period));
			$tpl->assign('id', $service_id);
	}
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
		/* CSV Export */
		$tpl->assign("link_csv_url", "./include/reporting/dashboard/csvExport/csv_ServiceLogs.php?sid=".$sid."&host=".$host_id."&service=".$service_id);
		$tpl->assign("link_csv_name", _("Export in CSV format"));
		/* status colors */
		$color = substr($oreon->optGen["color_up"],1).':'.substr($oreon->optGen["color_down"],1).':'.substr($oreon->optGen["color_unreachable"],1).':'.substr($oreon->optGen["color_undetermined"],1);
		/* Ajax timeline */
		$type = 'Service';
		include("./include/reporting/dashboard/ajaxReporting_js.php");
	} else {
		?><script type="text/javascript"> function initTimeline() {;} </script> <?php
	}
	$tpl->display("template/viewServicesLog.ihtml");
?>