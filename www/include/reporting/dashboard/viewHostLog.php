<?php
/**
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus - Cedrick Facon

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
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

	require_once './class/other.class.php';
	require_once './include/common/common-Func.php';
	require_once './include/common/common-Func-ACL.php';
	require_once 'HostLog.php';
	include("./include/monitoring/log/choose_log_file.php");


	#
	## Selection de l'host
	#
	$formHost = new HTML_QuickForm('formHost', 'post', "?p=".$p);
$var_url_export_csv = "";
		
	if($mhost)	{
		if($period == "customized") {
			$formHost->addElement('hidden', 'end', $end);
			$formHost->addElement('hidden', 'start', $start);
			$var_url_export_csv = "&period=customized&start=".$start_var."&end="."$end_var"."&lang=" .$oreon->user->get_lang();
		}
		else {
			$var_url_export_csv = "&period=".$period."&lang=" .$oreon->user->get_lang();
			$formHost->addElement('hidden', 'period', $period);
		}
	}

	#
	## Selection de l'host/service (suite)
	#
	$res =& $pearDB->query("SELECT host_name FROM host where host_activate = '1' AND host_id IN (".$lcaHoststr.") and host_register = '1' ORDER BY host_name");

	while ($res->fetchInto($h)){
		if (IsHostReadable($lcaHostByName, $h["host_name"]))
			$host[$h["host_name"]] = $h["host_name"];
	}
	$selHost =& $formHost->addElement('select', 'host', $lang["h"], $host, array("onChange" =>"this.form.submit();"));

	if (isset($_POST["host"])){
		$formHost->setDefaults(array('host' => $_POST["host"]));
	}else if (isset($_GET["host"])){
		$formHost->setDefaults(array('host' => $_GET["host"]));
	}

	#
	## Time select
	#
	$periodList = array();
	$periodList[""] = "";
	$periodList["today"] = $lang["today"];
	$periodList["yesterday"] = $lang["yesterday"];
	$periodList["thisweek"] = $lang["thisweek"];
	$periodList["last7days"] = $lang["last7days"];
	$periodList["thismonth"] = $lang["thismonth"];
	$periodList["last30days"] = $lang["last30days"];
	$periodList["lastmonth"] = $lang["lastmonth"];
	$periodList["thisyear"] = $lang["thisyear"];
	$periodList["lastyear"] = $lang["lastyear"];
	$periodList["customized"] = $lang["m_customizedPeriod"];
	
	$formPeriod = new HTML_QuickForm('FormPeriod', 'post', "?p=".$p."&type_period=predefined");
	$selHost =& $formPeriod->addElement('select', 'period', $lang["m_predefinedPeriod"], $periodList);

	isset($mhost) ? $formPeriod->addElement('hidden', 'host', $mhost) : NULL;
	$formPeriod->addElement('hidden', 'timeline', "1");

	$formPeriod->addElement('hidden', 'timeline', "1");
	$formPeriod->addElement('header', 'title', $lang["m_if_custom"]);
	$formPeriod->addElement('text', 'start', $lang["m_start"]);
	$formPeriod->addElement('button', "startD", $lang['modify'], array("onclick"=>"displayDatePicker('start')"));
	$formPeriod->addElement('text', 'end', $lang["m_end"]);
	$formPeriod->addElement('button', "endD", $lang['modify'], array("onclick"=>"displayDatePicker('end')"));
	$sub =& $formPeriod->addElement('submit', 'submit', $lang["m_view"]);
	$res =& $formPeriod->addElement('reset', 'reset', $lang["reset"]);





	if($mhost){
		$i=0;



	$tpl->assign('infosTitle', $lang["m_duration"] . Duration::toString($end_date_select - $start_date_select));


	$tpl->assign('host_name', $mhost);


	$tpl->assign('totalAlert', $totalAlert);

	$tpl->assign('totalTime', Duration::toString($totalTime));
	$tpl->assign('totalpTime', $totalpTime);
	$tpl->assign('totalpkTime', $totalpkTime);

	$tpl->assign('status', $status);
	
	
	$tab_resume[0]["style"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_up"]."'";
	$tab_resume[1]["style"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_down"]."'";
	$tab_resume[2]["style"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_unreachable"]."'";		
	$tab_resume[3]["style"] =  "class='ListColCenter' style='background:#cccccc'";
	
	$tpl->assign("tab_resume", $tab_resume);
	if(isset($tab_svc))
	$tpl->assign("tab_svc", $tab_svc);
	$tpl->assign("tab_svc_average", $tab_svc_average);

	$tt = 0 + ($ed - $sd);

	$tpl->assign('infosTitle', $lang["m_duration"] . Duration::toString($tt));
	}## end of period requirement

	$tpl->assign("tab_log", $tab_log);

	$tpl->assign('actualTitle', $lang["actual"]);

	$tpl->assign('date_start_select', $start_date_select);
	$tpl->assign('date_end_select', $end_date_select);
	$tpl->assign('to', $lang["m_to"]);
	$tpl->assign('period_name', $lang["m_period"]);
	$tpl->assign('period', $var_url_export_csv);


	$tpl->assign('style_ok', "class='ListColCenter' style='background:" . $oreon->optGen["color_ok"]."'");
	$tpl->assign('style_ok_alert', "class='ListColCenter' style='width: 25px; background:" . $oreon->optGen["color_ok"]."'");
	$tpl->assign('style_warning' , "class='ListColCenter' style='background:" . $oreon->optGen["color_warning"]."'");
	$tpl->assign('style_warning_alert' , "class='ListColCenter' style='width: 25px; background:" . $oreon->optGen["color_warning"]."'");
	$tpl->assign('style_critical' , "class='ListColCenter' style='background:" . $oreon->optGen["color_critical"]."'");
	$tpl->assign('style_critical_alert' , "class='ListColCenter' style='width: 25px; background:" . $oreon->optGen["color_critical"]."'");
	$tpl->assign('style_unknown' , "class='ListColCenter' style='background:" . $oreon->optGen["color_unknown"]."'");
	$tpl->assign('style_unknown_alert' , "class='ListColCenter' style='width: 25px; background:" . $oreon->optGen["color_unknown"]."'");
	$tpl->assign('style_pending' , "class='ListColCenter' style='background:#cccccc'");
	$tpl->assign('style_pending_alert' , "class='ListColCenter' style='width: 25px; background:#cccccc'");

	$tpl->assign('serviceTilte', $lang["m_serviceTilte"]);
	$tpl->assign('hostTitle', $lang["m_hostTitle"]);
	$tpl->assign("allTilte",  $lang["m_allTilte"]);
	$tpl->assign("averageTilte",  $lang["m_averageTilte"]);

	$tpl->assign('OKTitle', $lang["m_OKTitle"]);
	$tpl->assign('WarningTitle', $lang["m_WarningTitle"]);
	$tpl->assign('UnknownTitle', $lang["m_UnknownTitle"]);
	$tpl->assign('CriticalTitle', $lang["m_CriticalTitle"]);
	$tpl->assign('PendingTitle', $lang["m_PendingTitle"]);

	$tpl->assign('StateTitle', $lang["m_StateTitle"]);
	$tpl->assign('TimeTitle', $lang["m_TimeTitle"]);
	$tpl->assign('TimeTotalTitle', $lang["m_TimeTotalTitle"]);
	$tpl->assign('KnownTimeTitle', $lang["m_KnownTimeTitle"]);
	$tpl->assign('AlertTitle', $lang["m_AlertTitle"]);


	$tpl->assign('DateTitle', $lang["m_DateTitle"]);
	$tpl->assign('EventTitle', $lang["m_EventTitle"]);
	$tpl->assign('InformationsTitle', $lang["m_InformationsTitle"]);

	$tpl->assign('periodTitle', $lang["m_selectPeriodTitle"]);
	$tpl->assign('resumeTitle', $lang["m_hostResumeTitle"]);
	$tpl->assign('logTitle', $lang["m_hostLogTitle"]);
	$tpl->assign('svcTitle', $lang["m_hostSvcAssocied"]);

	$formPeriod->setDefaults(array('period' => $period));



	$tpl->assign('hostID', getMyHostID($mhost));
	$color = array();
	$color["UNKNOWN"] =  substr($oreon->optGen["color_unknown"], 1);
	$color["UP"] =  substr($oreon->optGen["color_up"], 1);
	$color["DOWN"] =  substr($oreon->optGen["color_down"], 1);
	$color["UNREACHABLE"] =  substr($oreon->optGen["color_unreachable"], 1);
	$tpl->assign('color', $color);

	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$formPeriod->accept($renderer);
	$tpl->assign('formPeriod', $renderer->toArray());

	#Apply a template definition
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$formHost->accept($renderer);
	$tpl->assign('formHost', $renderer->toArray());
	$tpl->assign('lang', $lang);
	$tpl->assign("p", $p);

	if($mhost){
		$tpl->assign("link_csv_url", "./include/reporting/dashboard/ExportCSV_HostLog.php?sid=".$sid."&host=".$mhost.$var_url_export_csv);
		$tpl->assign("link_csv_name", "Export CSV");
	}

	# For today in timeline
	$tt = 0 + ($today_end - $today_start);
	$today_pending = $tt - ($today_down + $today_up + $today_unreachable);
	$today_pending = round(($today_pending/$tt *100),2);
	$today_up = ($today_up <= 0) ? 0 : round($today_up / $tt *100,2);
	$today_down = ($today_down <= 0) ? 0 : round($today_down / $tt *100,2);
	$today_unreachable = ($today_unreachable <= 0) ? 0 : round($today_unreachable / $tt *100,2);
	$today_pending = ($today_pending < 0.1) ? "0" : $today_pending;

	if($mhost)	{
		$color = substr($oreon->optGen["color_up"],1) .':'.
		 		 substr($oreon->optGen["color_down"],1) .':'.
		 		 substr($oreon->optGen["color_unreachable"],1) .':'. 
		 		 substr($oreon->optGen["color_unknown"],1);
	
		$today_var = '&today_up='.$today_up . '&today_down='.$today_down.'&today_unreachable='.$today_unreachable. '&today_pending=' . $today_pending;
		$today_var .= '&today_UPnbEvent='.$today_UPnbEvent.'&today_UNREACHABLEnbEvent='.$today_UNREACHABLEnbEvent.'&today_DOWNnbEvent='.$today_DOWNnbEvent;

		$type = 'Host';
		include('ajaxReporting_js.php');
	}
	else {
			?>
			<SCRIPT LANGUAGE="JavaScript">
			function initTimeline() {
				;
			}
			</SCRIPT>
			<?php
		}

	$tpl->display("template/viewHostLog.ihtml");

?>