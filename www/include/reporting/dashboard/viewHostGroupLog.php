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

	#
	## init
	#
	$totalAlert = 0;
	$day = date("d",time());
	$year = date("Y",time());
	$month = date("m",time());
	$today_start = mktime(0, 0, 0, $month, $day, $year);
	$today_end = time();
	$tt = 0;
	$start_date_select = 0;
	$end_date_select = 0;
	$path = "./include/reporting/dashboard";

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "");
	$tpl->assign('o', $o);
	require_once './class/other.class.php';
	require_once './include/common/common-Func.php';
	require_once('simple-func.php');
	require_once('reporting-func.php');
	include("./include/monitoring/log/choose_log_file.php");

	# LCA
	$lcaHostByName = getLcaHostByName($pearDB);
	$lcaHGByName = getLcaHostByName($pearDB);
	$lcaHostByID = getLcaHostByID($pearDB);
	$lcaHoststr = getLCAHostStr($lcaHostByID["LcaHost"]);
	$lcaHostGroupstr = getLCAHGStr($lcaHostByID["LcaHostGroup"]);

	#
	## Selectioned ?
	#		
	isset ($_GET["hostgroup"]) ? $mhostgroup = $_GET["hostgroup"] : $mhostgroup = NULL;
	isset ($_POST["hostgroup"]) ? $mhostgroup = $_POST["hostgroup"] : $mhostgroup = $mhostgroup;

	#
	## Select form part 1
	#
	$formHostGroup = new HTML_QuickForm('formHost', 'post', "?p=".$p);

	#
	## period selection
	#
	$period = (isset($_POST["period"])) ? $_POST["period"] : "today"; 
	$period = (isset($_GET["period"])) ? $_GET["period"] : $period;
	$var_url_export_csv =  "";
	$var_url = "";

	if($mhostgroup)	{
		$end_date_select = 0;
		$start_date_select= 0;
		if($period == "customized") {
			$end = (isset($_POST["end"])) ? $_POST["end"] : NULL;
			$end = (isset($_GET["end"])) ? $_GET["end"] : $end;
			$start = (isset($_POST["start"])) ? $_POST["start"] : NULL;
			$start = (isset($_GET["start"])) ? $_GET["start"] : $start;
			getDateSelect_customized($end_date_select, $start_date_select, $start,$end);
			$formHostGroup->addElement('hidden', 'end', $end);
			$formHostGroup->addElement('hidden', 'start', $start);
			//$var_url_export_csv = "&period=customized&start=".$start_date_select."&end="."$end_date_select";
			$var_url_export_csv = "&period=customized&start=".$start."&end=".$end."&lang=" .$oreon->user->get_lang();
		}
		else {
			$var_url_export_csv = "&period=".$period."&lang=" .$oreon->user->get_lang();
//			$var_url = "&period=".$period;
			getDateSelect_predefined($end_date_select, $start_date_select, $period);
			$formHostGroup->addElement('hidden', 'period', $period);
		}
		$hostgroup_id = getMyHostGroupID($mhostgroup);
		$sd = $start_date_select;
		$ed = $end_date_select;

		#
		## database log
		#
		$hbase = array();
		$Tup = NULL;
		$Tdown = NULL;
		$Tunreach = NULL;
		$Tnone = NULL;
		getLogInDbForHostGroup($hbase, $pearDB, $pearDBO, $hostgroup_id, $start_date_select, $end_date_select, $today_start, $today_end);
	}


	#
	## Select form part 2
	#
	if($oreon->user->admin || !$isRestreint)
		$res =& $pearDB->query("SELECT hg_name FROM hostgroup where hg_activate = '1' ORDER BY hg_name");
	else
		$res =& $pearDB->query("SELECT hg_name FROM hostgroup where hg_activate = '1' AND hg_id IN (".$lcaHostGroupstr.") ORDER BY hg_name");


	$hostgroup = array();
	$hostgroup[""] = "";
	while ($res->fetchInto($hg)){
			$hostgroup[$hg["hg_name"]] = $hg["hg_name"];
	}
	$selHost =& $formHostGroup->addElement('select', 'hostgroup', _("Host"), $hostgroup, array("onChange" =>"this.form.submit();"));
	if (isset($_POST["hostgroup"])){
		$formHostGroup->setDefaults(array('hostgroup' => $_POST["hostgroup"]));
	}else if (isset($_GET["hostgroup"])){
		$formHostGroup->setDefaults(array('hostgroup' => $_GET["hostgroup"]));
	}

	#
	## Time select
	#
	$periodList = array();
	$periodList[""] = "";
	$periodList["today"] = _("Today");
	$periodList["yesterday"] = _("Yesterday");
	$periodList["thisweek"] = _("This Week");
	$periodList["last7days"] = _("Last 7 Days");
	$periodList["thismonth"] = _("This Month");
	$periodList["last30days"] = _("Last 30 Days");
	$periodList["lastmonth"] = _("Last Month");
	$periodList["thisyear"] = _("This Year");
	$periodList["lastyear"] = _("Last Year");
	$periodList["customized"] = _("Customized");

	$formPeriod = new HTML_QuickForm('FormPeriod', 'post', "?p=".$p);
	$selHost =& $formPeriod->addElement('select', 'period', _("Predefined:"), $periodList);

	isset($mhostgroup) ? $formPeriod->addElement('hidden', 'hostgroup', $mhostgroup) : NULL;
	$formPeriod->addElement('hidden', 'timeline', "1");

	$formPeriod->addElement('header', 'title', _("If customized period..."));
	$formPeriod->addElement('text', 'start', _("Begin date"));
	$formPeriod->addElement('button', "startD", _("Modify"), array("onclick"=>"displayDatePicker('start')"));
	$formPeriod->addElement('text', 'end', _("End date"));
	$formPeriod->addElement('button', "endD", _("Modify"), array("onclick"=>"displayDatePicker('end')"));
	$sub =& $formPeriod->addElement('submit', 'submit', _("View"));
	$res =& $formPeriod->addElement('reset', 'reset', _("Reset"));

	if($period == "customized") {
		$formPeriod->setDefaults(array('start' => date("m/d/Y", $start_date_select)));
		$formPeriod->setDefaults(array('end' => date("m/d/Y", $end_date_select)));
	}

	$tpl->assign('infosTitle', _("Duration : ") . Duration::toString($end_date_select - $start_date_select));
	$tpl->assign('hostgroup_name', $mhostgroup);

	#
	## ressource selected
	#
	$today_up = 0;
	$today_down = 0;
	$today_unreachable = 0;
	$today_UPnbEvent = 0;
	$today_UNREACHABLEnbEvent = 0;
	$today_DOWNnbEvent = 0;
	
	if($mhostgroup){
		#
		## today log for xml timeline
		#
		$today_up = 0 + $hbase["average"]["today"]["Tup"];
		$today_down = 0 + $hbase["average"]["today"]["Tdown"];
		$today_unreachable = 0 + $hbase["average"]["today"]["Tunreachable"];
	
		$today_UPnbEvent = 0 + $hbase["average"]["today"]["TupNBAlert"];
		$today_UNREACHABLEnbEvent = 0 + $hbase["average"]["today"]["TunreachableNBAlert"];
		$today_DOWNnbEvent = 0 + $hbase["average"]["today"]["TdownNBAlert"];

		$tab_log = array();
		$day = date("d",time());
		$year = date("Y",time());
		$month = date("m",time());
		$startTimeOfThisDay = mktime(0, 0, 0, $month, $day, $year);
		$tab_host_list_average = array();
		$tab_host_list_average["PTUP"] = 0;
		$tab_host_list_average["PAUP"] = 0;
		$tab_host_list_average["PTD"] = 0;
		$tab_host_list_average["PAD"] = 0;
		$tab_host_list_average["PTUR"] = 0;
		$tab_host_list_average["PAUR"] = 0;
		$tab_host_list_average["PTU"] = 0;
		$tab_host_list_average["PKTup"] = 0;
		$tab_host_list_average["PKTd"] = 0;
		$tab_host_list_average["PKTu"] = 0;
		$tab_host_list_average["nb_host"] = 0;	
		$tab_hosts = array();	
		$day_current_start = 0;
		$day_current_end = time() + 1;
		$time = time();

		#
		## calculate resume
		#
		$tab_resume = array();
		$tab = array();
		$timeTOTAL = $end_date_select - $start_date_select;	
		$Tup = $hbase["average"]["Tup"];
		$Tdown = $hbase["average"]["Tdown"];
		$Tunreach = $hbase["average"]["Tunreachable"];
		$Tnone = $hbase["average"]["Tnone"];
		$Tnone = $timeTOTAL - ($Tup + $Tdown + $Tunreach);
		if($Tnone <= 1)
		$Tnone = 0;	
		$tab["state"] = _("Up");
		$tab["time"] = Duration::toString($Tup);
		$tab["timestamp"] = $Tup;
		$tab["pourcentTime"] = round($Tup/($timeTOTAL+1)*100,2) ;
		$tab["pourcentkTime"] = round($Tup/($timeTOTAL-$Tnone+1)*100,2). "%";
		$tab["nbAlert"] = $hbase["average"]["TupNBAlert"];
		$tab["style"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_up"]."'";
		$tab_resume[0] = $tab;
		$tab["state"] = _("Down");
		$tab["time"] = Duration::toString($Tdown);
		$tab["timestamp"] = $Tdown;
		$tab["pourcentTime"] = round($Tdown/$timeTOTAL*100,2);
		$tab["pourcentkTime"] = round($Tdown/($timeTOTAL-$Tnone+1)*100,2)."%";
		$tab["nbAlert"] = $hbase["average"]["TdownNBAlert"];
		$tab["style"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_down"]."'";
		$tab_resume[1] = $tab;
		$tab["state"] = _("Unreachable");
		$tab["time"] = Duration::toString($Tunreach);
		$tab["timestamp"] = $Tunreach;
		$tab["pourcentTime"] = round($Tunreach/$timeTOTAL*100,2);
		$tab["pourcentkTime"] = round($Tunreach/($timeTOTAL-$Tnone+1)*100,2)."%";
		$tab["nbAlert"] = $hbase["average"]["TunreachableNBAlert"];
		$tab["style"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_unreachable"]."'";
		$tab_resume[2] = $tab;
		$tab["state"] = _("Undetermined");
		$tab["time"] = Duration::toString($Tnone);
		$tab["timestamp"] = $Tnone;
		$tab["pourcentTime"] = round($Tnone/$timeTOTAL*100,2);
		$tab["pourcentkTime"] = null;
		$tab["nbAlert"] = "";
		$tab["style"] = "class='ListColCenter' style='background:#cccccc'";
		$tab_resume[3] = $tab;


		#
		## calculate tablist
		#
		$i=0;
		foreach($hbase as $host_id => $tab)
		{
			if($host_id != "average"){
				$tab_tmp = array();
				$tab_tmp["hostName"] = getMyHostName($host_id);
				$tt = $end_date_select - $start_date_select;
				$tab_tmp["PtimeUP"] = round($tab["Tup"] / $tt *100,2);
				$tab_tmp["PtimeDOWN"] = round( $tab["Tdown"]/ $tt *100,2);
				$tab_tmp["PtimeUNREACHABLE"] = round( $tab["Tunreachable"]/ $tt *100,2);
				$tab_tmp["PtimeUNDETERMINATED"] = round( ( $tt - ($tab["Tup"] + $tab["Tdown"] + $tab["Tunreachable"])													 )  / $tt *100,2);
				$tmp_none = $tt - ($tab["Tup"] + $tab["Tdown"] + $tab["Tunreachable"]);
				$tab_tmp["UPnbEvent"] = isset($tab["TupNBAlert"]) ? $tab["TupNBAlert"] : 0;
				$tab_tmp["DOWNnbEvent"] = isset($tab["TdownNBAlert"]) ? $tab["TdownNBAlert"] : 0;
				$tab_tmp["UNREACHABLEnbEvent"] = isset($tab["TunreachableNBAlert"]) ? $tab["TunreachableNBAlert"] : 0;
				$tab_tmp["PktimeUP"] = $tab["Tup"] ? round($tab["Tup"] / ($tt - $tmp_none) *100,2): 0;
				$tab_tmp["PktimeDOWN"] = $tab["Tdown"] ? round( $tab["Tdown"]/ ($tt - $tmp_none) *100,2):0;
				$tab_tmp["PktimeUNREACHABLE"] =  $tab["Tunreachable"] ? round( $tab["Tunreachable"]/ ($tt - $tmp_none) *100,2):0;
				$tab_tmp["PtimeUP"] = number_format($tab_tmp["PtimeUP"], 1, '.', '');
				$tab_tmp["PtimeDOWN"] = number_format($tab_tmp["PtimeDOWN"], 1, '.', '');
				$tab_tmp["PtimeUNREACHABLE"] = number_format($tab_tmp["PtimeUNREACHABLE"], 1, '.', '');
				$tab_tmp["PtimeUNDETERMINATED"] = number_format($tab_tmp["PtimeUNDETERMINATED"], 1, '.', '');
				$tab_tmp["PtimeUNDETERMINATED"] = ($tab_tmp["PtimeUNDETERMINATED"] < 0.1) ? 0.0 : $tab_tmp["PtimeUNDETERMINATED"];
				$tab_tmp["PktimeUP"] = number_format($tab_tmp["PktimeUP"], 1, '.', '');
				$tab_tmp["PktimeDOWN"] = number_format($tab_tmp["PktimeDOWN"], 1, '.', '');
				$tab_tmp["PktimeUNREACHABLE"] = number_format($tab_tmp["PktimeUNREACHABLE"], 1, '.', '');
	
				#
				## fill average svc table
				#
				$tab_host_list_average["PTUP"] += $tab_tmp["PtimeUP"];
				$tab_host_list_average["PAUP"] += $tab_tmp["UPnbEvent"];
				$tab_host_list_average["PTD"] += $tab_tmp["PtimeDOWN"];
				$tab_host_list_average["PAD"] += $tab_tmp["DOWNnbEvent"];
				$tab_host_list_average["PTUR"] += $tab_tmp["PtimeUNREACHABLE"];
				$tab_host_list_average["PAUR"] += $tab_tmp["UNREACHABLEnbEvent"];
				$tab_host_list_average["PTU"] += $tab_tmp["PtimeUNDETERMINATED"];
				$tab_host_list_average["PKTup"] += $tab_tmp["PktimeUP"];
				$tab_host_list_average["PKTd"] += $tab_tmp["PktimeDOWN"];
				$tab_host_list_average["PKTu"] += $tab_tmp["PktimeUNREACHABLE"];
				$tab_host_list_average["nb_host"] += 1;
				$tab_host[$i++] = $tab_tmp;
			}
		}

		#
		## calculate svc average
		#
		# Alert
		if($tab_host_list_average["PAUP"] > 0)
		$tab_host_list_average["PAUP"] = number_format($tab_host_list_average["PAUP"] / $tab_host_list_average["nb_host"], 1, '.', '');
		if($tab_host_list_average["PAD"] > 0)
		$tab_host_list_average["PAD"] = number_format($tab_host_list_average["PAD"] / $tab_host_list_average["nb_host"], 1, '.', '');
		if($tab_host_list_average["PAUR"] > 0)
		$tab_host_list_average["PAUR"] = number_format($tab_host_list_average["PAUR"] / $tab_host_list_average["nb_host"], 1, '.', '');
		# Time
		if($tab_host_list_average["PTUP"] > 0)
		$tab_host_list_average["PTUP"] = number_format($tab_host_list_average["PTUP"] / $tab_host_list_average["nb_host"], 3, '.', '');
		if($tab_host_list_average["PTD"] > 0)
		$tab_host_list_average["PTD"] = number_format($tab_host_list_average["PTD"] / $tab_host_list_average["nb_host"], 3, '.', '');
		if($tab_host_list_average["PTUR"] > 0)
		$tab_host_list_average["PTUR"] = number_format($tab_host_list_average["PTUR"] / $tab_host_list_average["nb_host"], 3, '.', '');
		if($tab_host_list_average["PTU"] > 0)
		$tab_host_list_average["PTU"] = number_format($tab_host_list_average["PTU"] / $tab_host_list_average["nb_host"], 3, '.', '');
		# %
		if($tab_host_list_average["PKTup"] > 0)
		$tab_host_list_average["PKTup"] = number_format($tab_host_list_average["PKTup"] / $tab_host_list_average["nb_host"], 3, '.', '');
		if($tab_host_list_average["PKTd"] > 0)
		$tab_host_list_average["PKTd"] = number_format($tab_host_list_average["PKTd"] / $tab_host_list_average["nb_host"], 3, '.', '');
		if($tab_host_list_average["PKTu"] > 0)
		$tab_host_list_average["PKTu"] = number_format($tab_host_list_average["PKTu"] / $tab_host_list_average["nb_host"], 3, '.', '');

		$start_date_select = date("d/m/Y (G:i:s)", $start_date_select);
		$end_date_select_save_timestamp =  $end_date_select;
		$end_date_select =  date("d/m/Y (G:i:s)", $end_date_select);
		$status = "";
		$totalTime = 0;
		$totalpTime = 0;
		$totalpkTime = 0;
	
		foreach ($tab_resume  as $tb){
			if($tb["pourcentTime"] >= 0)
				$status .= "&value[".$tb["state"]."]=".$tb["pourcentTime"];
			$totalTime += $tb["timestamp"];
			$totalpTime += $tb["pourcentTime"];
			$totalpkTime += $tb["pourcentkTime"];
		}
		$totalAlert = $hbase["average"]["TunreachableNBAlert"] + $hbase["average"]["TdownNBAlert"] + $hbase["average"]["TupNBAlert"];

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
	$tpl->assign("tab_host_average", $tab_host_list_average);

	}

	if(isset($tab_host))
	$tpl->assign("tab_host", $tab_host);

	$tpl->assign('infosTitle', _("Duration : ") . Duration::toString($tt));
	$tpl->assign("tab_log", $tab_log);
	$tpl->assign('actualTitle', _(" Actual "));
	$tpl->assign('date_start_select', $start_date_select);
	$tpl->assign('date_end_select', $end_date_select);
	$tpl->assign('to', _(" to "));
	$tpl->assign('period_name', _(" From "));
	$tpl->assign('style_up', "class='ListColCenter' style='background:" . $oreon->optGen["color_ok"]."'");
	$tpl->assign('style_up_alert', "class='ListColCenter' style='width: 25px; background:" . $oreon->optGen["color_ok"]."'");
	$tpl->assign('style_down' , "class='ListColCenter' style='background:" . $oreon->optGen["color_down"]."'");
	$tpl->assign('style_down_alert' , "class='ListColCenter' style='width: 25px; background:" . $oreon->optGen["color_down"]."'");
	$tpl->assign('style_unreachable' , "class='ListColCenter' style='background:" . $oreon->optGen["color_unreachable"]."'");
	$tpl->assign('style_unreachable_alert' , "class='ListColCenter' style='width: 25px; background:" . $oreon->optGen["color_unreachable"]."'");
	$tpl->assign('style_undeterminated' , "class='ListColCenter' style='background:" . $oreon->optGen["color_unknown"]."'");
	$tpl->assign('style_undeterminated_alert' , "class='ListColCenter' style='width: 25px; background:" . $oreon->optGen["color_unknown"]."'");
	$tpl->assign('serviceTilte', _("Service"));
	$tpl->assign("allTilte",  _("All"));
	$tpl->assign("averageTilte",  _("Average"));
	$tpl->assign('UpTitle', _("Up"));
	$tpl->assign('DownTitle', _("Down"));
	$tpl->assign('UnreachableTitle', _("Unreachable"));
	$tpl->assign('UndeterminatedTitle', _("Undetermined"));
	$tpl->assign('StateTitle', _("Status"));
	$tpl->assign('TimeTitle', _("Time"));
	$tpl->assign('TimeTotalTitle', _("Total Time"));
	$tpl->assign('KnownTimeTitle', _("Known Time"));
	$tpl->assign('AlertTitle', _("Alert"));
	$tpl->assign('DateTitle', _("Date"));
	$tpl->assign('EventTitle', _("Event"));
	$tpl->assign('HostTitle', _("Host"));
	$tpl->assign('InformationsTitle', _("Info"));
	$tpl->assign('periodTitle', _("Period Selection"));
	$tpl->assign('resumeTitle', _("Host state"));
	$tpl->assign('logTitle', _("Today's Host log"));

	if($mhostgroup){
		$tpl->assign("link_csv_url", "./include/reporting/dashboard/ExportCSV_HostGroupLog.php?sid=".$sid."&hostgroup=".$mhostgroup.$var_url_export_csv);
		$tpl->assign("link_csv_name", "Export CSV");
	}

	$formPeriod->setDefaults(array('period' => $period));

	$tpl->assign('hostID', getMyHostID($mhostgroup));
	$color = array();
	$color["UNREACHABLE"] =  substr($oreon->optGen["color_unknown"], 1);
	$color["UP"] =  substr($oreon->optGen["color_up"], 1);
	$color["DOWN"] =  substr($oreon->optGen["color_down"], 1);
	$color["UNREACHABLE"] =  substr($oreon->optGen["color_unreachable"], 1);
	$tpl->assign('color', $color);
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$formPeriod->accept($renderer);
	$tpl->assign('formPeriod', $renderer->toArray());
	$tpl->assign('period', $var_url_export_csv);

	#Apply a template definition
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$formHostGroup->accept($renderer);
	$tpl->assign('formHostGroup', $renderer->toArray());
	$tpl->assign('lang', $lang);
	$tpl->assign("p", $p);

	# For today in timeline
	$tt = 0 + ($today_end - $today_start);
	$today_pending = $tt - ($today_down + $today_up + $today_unreachable);
	$today_pending = round(($today_pending/$tt *100),2);
	$today_up = ($today_up <= 0) ? 0 : round($today_up / $tt *100,2);
	$today_down = ($today_down <= 0) ? 0 : round($today_down / $tt *100,2);
	$today_unreachable = ($today_unreachable <= 0) ? 0 : round($today_unreachable / $tt *100,2);
	$today_pending = ($today_pending < 0.1) ? "0" : $today_pending;

	if($mhostgroup)	{
		$color = substr($oreon->optGen["color_up"],1) .':'.
		 		 substr($oreon->optGen["color_down"],1) .':'.
		 		 substr($oreon->optGen["color_unreachable"],1) .':'. 
		 		 substr($oreon->optGen["color_unknown"],1);
		$today_var = '&today_up='.$today_up . '&today_down='.$today_down.'&today_unreachable='.$today_unreachable. '&today_pending=' . $today_pending;
		$today_var .= '&today_UPnbEvent='.$today_UPnbEvent.'&today_UNREACHABLEnbEvent='.$today_UNREACHABLEnbEvent.'&today_DOWNnbEvent='.$today_DOWNnbEvent;
		$type = 'HostGroup';
		$host_id = $hostgroup_id;
		include('ajaxReporting_js.php');
	}
	else {
			?>
			<script type="text/javascript">
			function initTimeline() {
				;
			}
			</SCRIPT>
			<?php
		}
	$tpl->display("template/viewHostGroupLog.ihtml");
?>