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
	 *  Getting hostgroup to report 
	 */
	isset($_GET["item"]) ? $id = $_GET["item"] : $id = "NULL";
	isset($_POST["item"]) ? $id = $_POST["item"] : $id;
	
	/*
	 * Forms
	 */
	/* Hostgroup Selection */
	$items = getAllHostgroupsForReporting($is_admin, $lcaHostGroupstr);
	$form = new HTML_QuickForm('formItem', 'post', "?p=".$p);
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	$select =& $form->addElement('select', 'item', _("Hosts Group"), $items, array("onChange" =>"this.form.submit();"));
	$form->addElement('hidden', 'period', $get_period);
	$form->addElement('hidden', 'start', $get_date_start);
	$form->addElement('hidden', 'end', $get_date_end);
	if (isset($id))
		$form->setDefaults(array('item' => $id));
	/* Set hostgroup id with period selection form */
	if ($id != "NULL") {
		$formPeriod->addElement('hidden', 'item', $id);
	}
	/*
	 * END OF FORMS
	 */
	 
	/*
	 * Stats Display for selected hostgroup
	 */
	if (isset($id) && $id != "NULL"){
			/* Getting periods values */
			$dates = getPeriodToReport();
			$start_date = $dates[0];
			$end_date = $dates[1];
			/* Getting hostgroup and his hosts stats */
			$hostgroupStats = array();
			$hostgroupStats = getLogInDbForHostGroup($id, $start_date, $end_date, $reportingTimePeriod) ;
			/* Flash chart datas */
			$pie_chart_get_str =  "&value[down]=".$hostgroupStats["average"]["DOWN_TP"]."&value[up]=".
						$hostgroupStats["average"]["UP_TP"]."&value[unreachable]=".$hostgroupStats["average"]["UNREACHABLE_TP"]."&value[undetermined]=".$hostgroupStats["average"]["UNDETERMINED_TP"];
			/* Exporting variables for ihtml */
			$tpl->assign('name', $items[$id]);
			$tpl->assign('pie_chart_get_str', $pie_chart_get_str);
			$tpl->assign('totalAlert', $hostgroupStats["average"]["TOTAL_ALERTS"]);
			$tpl->assign('summary',  $hostgroupStats["average"]);
			/* removing average infos from table */
			$hostgroupFinalStats = array();
			foreach ($hostgroupStats as $key => $value)
				if ($key != "average")
					$hostgroupFinalStats[$key] = $value;
					
			$tpl->assign("components", $hostgroupFinalStats);
			$tpl->assign('period_name', _(" From "));
			$tpl->assign('date_start', date("d/m/Y H:i",$start_date));
			$tpl->assign('to', _(" To "));
			$tpl->assign('date_end', date("d/m/Y H:i", $end_date));
			$tpl->assign('start', $start_date);
			$tpl->assign('end', $end_date);
			$tpl->assign('period', $period);
	//		$tpl->assign('period', $var_url_export_csv);
			$formPeriod->setDefaults(array('period' => $period));
			$tpl->assign('id', $id);
	}

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
		 /* CSV export */
		$tpl->assign("link_csv_url", "./include/reporting/dashboard/csvExport/csv_HostGroupLogs.php?sid=".$sid."&hostgroup=".$id);
		$tpl->assign("link_csv_name", _("Export in CSV format"));
		/* Status colors */
		$color = substr($oreon->optGen["color_up"],1).':'.substr($oreon->optGen["color_down"],1).':'.substr($oreon->optGen["color_unreachable"],1).':'.substr($oreon->optGen["color_undetermined"],1);
		/* Ajax timeline */
		$type = 'HostGroup';
		include("./include/reporting/dashboard/ajaxReporting_js.php");
	} else {
		?><script type="text/javascript"> function initTimeline() {;} </script><?php
	}
	$tpl->display("template/viewHostGroupLog.ihtml");
?>