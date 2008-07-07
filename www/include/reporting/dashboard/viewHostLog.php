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

	# Smarty template Init
	$path = "./include/reporting/dashboard";
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "");
	$tpl->assign('o', $o);

	isset ($_GET["host"]) ? $mhost = $_GET["host"] : $mhost = NULL;
	isset ($_POST["host"]) ? $mhost = $_POST["host"] : $mhost = $mhost;

	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	require_once './class/other.class.php';
	require_once './include/common/common-Func.php';
	require_once './include/common/common-Func-ACL.php';
	
	require_once("./include/reporting/dashboard/initReport.php");
		
	$tableFile2 = array();
	if ($handle  = @opendir($oreon->Nagioscfg["log_archive_path"]))	{
		while ($file = @readdir($handle))
			if (is_file($oreon->Nagioscfg["log_archive_path"]."/$file"))	{
				preg_match("/nagios\-([0-9]*)\-([0-9]*)\-([0-9]*)\-([0-9]*).log/", $file, $matches);
				$time = mktime("0", "0", "0", $matches[1], $matches[2], $matches[3]) - 1;
				$tableFile2[$file] =  "  " . date(_("Y/m/d"), $time) . " ";
			}
		@closedir($handle);
	}
	krsort($tableFile2);
	
	$tableFile3 = array($oreon->Nagioscfg["log_file"] => " -- " . _("Today") . " -- ");
	$tableFile1 = array_merge($tableFile3, $tableFile2);

	$host = array(""=>"");
	$DBRESULT =& $pearDB->query("SELECT `host_name` FROM `host` WHERE `host_activate` = '1' AND `host_register` = '1' ORDER BY `host_name`");
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getMessage();
	while ($DBRESULT->fetchInto($h))
		if ($is_admin || isset($lca["LcaHost"][$h['host_name']]))
			$host[$h["host_name"]] = $h["host_name"];
	
	/*
	 * Form begin
	 */
		
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title', _("Add a Service downtime"));
	
	/*
	 * Indicator basic information
	 */
	
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
    
    $selHost =& $form->addElement('select', 'file', _("Log file"), $tableFile1, array("onChange" =>"this.form.submit();"));
	$selHost =& $form->addElement('select', 'host', _("Host"), $host, array("onChange" =>"this.form.submit();"));
	isset($_POST["host"]) ?	$form->setDefaults(array('file' => $_POST["host"])) : $form->setDefaults(array('file' => $oreon->Nagioscfg["log_file"]));
	
	#
	## Selection de l'host
	#
	$formHost = new HTML_QuickForm('formHost', 'post', "?p=".$p);

	$var_url_export_csv = "";
	
	#
	## Selection de l'host/service (suite)
	#
	
	$lcaSTR = "";
	if (!$is_admin){
		$lca = getLcaHostByID($pearDB);
		$lcaHoststr = getLCAHostStr($lca["LcaHost"]);
		$lcaSTR = " AND host_id IN (".$lcaHoststr.") ";
	}	
	$res =& $pearDB->query("SELECT host_name FROM host WHERE host_activate = '1' $lcaSTR AND host_register = '1' ORDER BY host_name");		
	while ($h =& $res->fetchRow())
		if (!isset($lca) || isset($lca["LcaHost"][$h['host_name']]))
			$host[$h["host_name"]] = $h["host_name"];

	$selHost =& $formHost->addElement('select', 'host', _("Host"), $host, array("onChange" =>"this.form.submit();"));

	$formHost->setDefaults(array('host' => $mhost));
	$formPeriod = new HTML_QuickForm('FormPeriod', 'post', "?p=".$p."&type_period=predefined");
	$selHost =& $formPeriod->addElement('select', 'period', _("Predefined : "), $periodList);

	isset($mhost) ? $formPeriod->addElement('hidden', 'host', $mhost) : NULL;

	$formPeriod->addElement('hidden', 'timeline', "1");
	$formPeriod->addElement('header', 'title', _("If customized period..."));
	$formPeriod->addElement('text', 'start', _("Begin date"));
	$formPeriod->addElement('button', "startD", _("Modify"), array("onclick"=>"displayDatePicker('start')"));
	$formPeriod->addElement('text', 'end', _("End date"));
	$formPeriod->addElement('button', "endD", _("Modify"), array("onclick"=>"displayDatePicker('end')"));
	$sub =& $formPeriod->addElement('submit', 'submit', _("View"));
	$res =& $formPeriod->addElement('reset', 'reset', _("Reset"));

	function purgeVar($mhost){
		$mhost = str_replace("\'", '', $mhost);	
		$mhost = str_replace("\"", '', $mhost);	
		$tab_mhost = split(";", $mhost);
		$mhost = $tab_mhost[0];
		unset($tab_mhost);
		return $mhost;
	}

	$mhost = purgeVar($mhost);

	if ($mhost){
		$i=0;
		/*
		 * Get Datas
		 */
		require_once './include/reporting/dashboard/dataEngine/HostLog.php';
	
		$tpl->assign('infosTitle', _("Duration : ") . Duration::toString($end_date_select - $start_date_select));
		$tpl->assign('host_name', $mhost);
		global $host_name;
		$host_name = $mhost;
	
		$tpl->assign('totalAlert', $totalAlert);
		$tpl->assign('totalTime', Duration::toString($totalTime));
		$tpl->assign('totalpTime', $totalpTime);
		$tpl->assign('totalpkTime', $totalpkTime);
	
		global $status;
		$tpl->assign('status', $status);
		
		$tab_resume[0]["style"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_up"]."'";
		$tab_resume[1]["style"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_down"]."'";
		$tab_resume[2]["style"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_unreachable"]."'";		
		$tab_resume[3]["style"] =  "class='ListColCenter' style='background:#cccccc'";
		
		$tpl->assign("tab_resume", $tab_resume);
		if (isset($tab_svc))
			$tpl->assign("tab_svc", $tab_svc);
		$tpl->assign("tab_svc_average", $tab_svc_average);
	
		$tt = 0 + ($ed - $sd);
	
		$tpl->assign('infosTitle', _("Duration : ") . Duration::toString($tt));

		$tpl->assign('date_start_select', $start_date_select);
		$tpl->assign('date_end_select', $end_date_select);
		$tpl->assign('to', _(" to "));
		$tpl->assign('period_name', _(" From "));
//		$tpl->assign('period', $var_url_export_csv);
	
		$formPeriod->setDefaults(array('period' => $period));
		$tpl->assign('hostID', getMyHostID($mhost));
		
		/*
		 *  For today in timeline
		 */
		$tt = 0 + ($today_end - $today_start);
		$today_pending = $tt - ($today_down + $today_up + $today_unreachable);
		$today_pending = round(($today_pending/$tt *100),2);
		$today_up = ($today_up <= 0) ? 0 : round($today_up / $tt *100,2);
		$today_down = ($today_down <= 0) ? 0 : round($today_down / $tt *100,2);
		$today_unreachable = ($today_unreachable <= 0) ? 0 : round($today_unreachable / $tt *100,2);
		$today_pending = ($today_pending < 0.1) ? "0" : $today_pending;
	}
	
	$tpl->assign("p", $p);

	/*
	 * Template for Periodes
	 */
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$formPeriod->accept($renderer);
	$tpl->assign('formPeriod', $renderer->toArray());

	/*
	 * Apply a template definition
	 */
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$formHost->accept($renderer);
	$tpl->assign('formHost', $renderer->toArray());

	if ($mhost){
		$tpl->assign("link_csv_url", "./include/reporting/dashboard/csvExport/csv_HostLogs.php?sid=".$sid."&host=".$mhost.$var_url_export_csv);
		$tpl->assign("link_csv_name", _("Export in CSV format"));
	}

	if ($mhost)	{
		/*
		 * Colors
		 */
		$color = substr($oreon->optGen["color_up"],1).':'.substr($oreon->optGen["color_down"],1).':'.substr($oreon->optGen["color_unreachable"],1).':'.substr($oreon->optGen["color_unknown"],1);
	
		$today_var  = '&today_up='.$today_up . '&today_down='.$today_down.'&today_unreachable='.$today_unreachable. '&today_pending=' . $today_pending;
		$today_var .= '&today_UPnbEvent='.$today_UPnbEvent.'&today_UNREACHABLEnbEvent='.$today_UNREACHABLEnbEvent.'&today_DOWNnbEvent='.$today_DOWNnbEvent;

		$type = 'Host';
		include("./include/reporting/dashboard/ajaxReporting_js.php");
	} else {
		?><script type="text/javascript"> function initTimeline() {;} </SCRIPT> <?php
	}
	$tpl->display("template/viewHostLog.ihtml");
?>