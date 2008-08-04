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

	if (!$is_admin)
		$lca = getLcaHostByName($pearDB);	

	# Smarty template Init
	$path = "./include/reporting/dashboard";
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "");
	$tpl->assign('o', $o);

	isset ($_GET["host"]) ? $mhost = $_GET["host"] : $mhost = NULL;
	isset ($_POST["host"]) ? $mhost = $_POST["host"] : $mhost = $mhost;	

	require_once 'HTML/QuickForm.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	require_once './class/other.class.php';
	require_once './include/reporting/dashboard/common-Func.php';
	require_once './include/common/common-Func.php';
	require_once './include/common/common-Func-ACL.php';	
	require_once './include/reporting/dashboard/initReport.php';
	require_once './include/reporting/dashboard/dataEngine/HostGroupLog.php';

	$mhost = purgeVar($mhost);
	
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

	$host = array();
	
	$host[""] = "";
	$DBRESULT =& $pearDB->query("SELECT host_name FROM host where host_activate = '1' and host_register = '1' ORDER BY host_name");
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getMessage();
	while ($DBRESULT->fetchInto($h))
		if (!isset($lca) || isset($lca["LcaHost"][$h['host_name']]))
			$host[$h["host_name"]] = $h["host_name"];	
	#
	## Form begin
	#
	
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title', _("Add a Service downtime"));
	
	#
	## Indicator basic information
	#
	
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
    
    $selHost =& $form->addElement('select', 'file', _("Log file"), $tableFile1, array("onChange" =>"this.form.submit();"));
	$selHost =& $form->addElement('select', 'host', _("Host"), $host, array("onChange" =>"this.form.submit();"));
	isset($_POST["host"]) ?	$form->setDefaults(array('file' => $_POST["host"])) : $form->setDefaults(array('file' => $oreon->Nagioscfg["log_file"]));
	
	$log = NULL;	
	$tab_log = array();

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	# LCA
	if (!$is_admin){
		$lcaHostByName = getLcaHostByName($pearDB);
		$lcaHGByName = getLcaHostByName($pearDB);
		$lcaHostByID = getLcaHostByID($pearDB);
		$lcaHoststr = getLCAHostStr($lcaHostByID["LcaHost"]);
		$lcaHostGroupstr = getLCAHGStr($lcaHostByID["LcaHostGroup"]);
	}

	#
	## Selected ?
	#		
	isset ($_GET["servicegroup"]) ? $mservicegroup = $_GET["servicegroup"] : $mservicegroup = NULL;
	isset ($_POST["servicegroup"]) ? $mservicegroup = $_POST["servicegroup"] : $mservicegroup = $mservicegroup;

	$mservicegroup = purgeVar($mservicegroup);

	#
	## Select form part 1
	#
	$formservicegroup = new HTML_QuickForm('formHost', 'post', "?p=".$p);

	#
	## period selection
	#
	$period = (isset($_POST["period"])) ? $_POST["period"] : "today"; 
	$period = (isset($_GET["period"])) ? $_GET["period"] : $period;

	if ($mservicegroup)	{
		$end_date_select = 0;
		$start_date_select= 0;
		if ($period == "" && $_POST["end"] != NULL && $_POST["start"] != NULL) {
			$end = (isset($_POST["end"])) ? $_POST["end"] : NULL;
			$end = (isset($_GET["end"])) ? $_GET["end"] : $end;
			$start = (isset($_POST["start"])) ? $_POST["start"] : NULL;
			$start = (isset($_GET["start"])) ? $_GET["start"] : $start;
			getDateSelect_customized($end_date_select, $start_date_select, $start,$end);
			$formservicegroup->addElement('hidden', 'end', $end);
			$formservicegroup->addElement('hidden', 'start', $start);
			$var_url_export_csv = "&amp;period=customized&amp;start=".$start."&amp;end="."$end"."&amp;lang=" .$oreon->user->get_lang();
		} else {
			$var_url_export_csv = "&amp;period=".$period."&amp;lang=" .$oreon->user->get_lang();
			getDateSelect_predefined($end_date_select, $start_date_select, $period);
			$formservicegroup->addElement('hidden', 'period', $period);
		}
		$servicegroup_id = getMyservicegroupID($mservicegroup);
		$sd = $start_date_select;
		$ed = $end_date_select;

		/*
		 * database log
		 */
		 
		$sbase = array();
		$Tup = NULL;
		$Tdown = NULL;
		$Tunreach = NULL;
		$Tnone = NULL;
		getLogInDbForServicesGroup($sbase, $pearDB, $pearDBO, $servicegroup_id, $start_date_select, $end_date_select, $today_start, $today_end);
	}

	#
	## Select form part 2
	#
	$lcaSG = getLCASG($pearDB);	
	$servicegroup = array();
	$servicegroup[""] = "";
	$res =& $pearDB->query("SELECT DISTINCT sg_name, sg_id FROM servicegroup sg, servicegroup_relation sg_r WHERE sg.sg_activate = '1' and sg.sg_id = sg_r.servicegroup_sg_id ORDER BY sg_name;");
	while ($res->fetchInto($sg)){
			if ($is_admin || isset($lcaSG[$sg['sg_id']]))			
				$servicegroup[$sg["sg_name"]] = $sg["sg_name"];
	}
	
	$selHost =& $formservicegroup->addElement('select', 'servicegroup', _("Service Group"), $servicegroup, array("onChange" =>"this.form.submit();"));
	if (isset($_POST["servicegroup"])){
		$formservicegroup->setDefaults(array('servicegroup' => $_POST["servicegroup"]));
	} else if (isset($_GET["servicegroup"])) {
		$formservicegroup->setDefaults(array('servicegroup' => $_GET["servicegroup"]));
	}

	#
	## Time select
	#
	
	# Getting period table list to make the form period selection (today, this week etc.)
	$periodList = getPeriodList();

	$formPeriod = new HTML_QuickForm('FormPeriod', 'post', "?p=".$p);
	$selHost =& $formPeriod->addElement('select', 'period', _("Predefined:"), $periodList);

	isset($mservicegroup) ? $formPeriod->addElement('hidden', 'servicegroup', $mservicegroup) : NULL;

	$formPeriod->addElement('hidden', 'timeline', "1");
	$formPeriod->addElement('header', 'title', _("If customized period..."));
	$formPeriod->addElement('text', 'start', _("Begin date"));
	$formPeriod->addElement('button', "startD", _("Modify"), array("onclick"=>"displayDatePicker('start')"));
	$formPeriod->addElement('text', 'end', _("End date"));

	$formPeriod->addElement('button', "endD", _("Modify"), array("onclick"=>"displayDatePicker('end')"));
	$sub =& $formPeriod->addElement('submit', 'submit', _("View"));

	if ($period == "customized") {
		$formPeriod->setDefaults(array('start' => date("m/d/Y", $start_date_select)));
		$formPeriod->setDefaults(array('end' => date("m/d/Y", $end_date_select)));
	}

	/*
	 * ressource selected
	 */
	$today_ok = 0;
	$today_warning = 0;
	$today_unknown = 0;
	$today_critical = 0;
	$today_OKnbEvent = 0;
	$today_UNKNOWNnbEvent = 0;
	$today_WARNINGnbEvent = 0;
	$today_CRITICALnbEvent = 0;
	
	if ($mservicegroup){
		$tpl->assign('infosTitle', _("Duration : ") . Duration::toString($end_date_select - $start_date_select));
		$tpl->assign('servicegroup_name', $mservicegroup);
		
		/*
		 * today log for xml timeline
		 */
		$today_ok = 0 + $sbase["average"]["today"]["Tok"];
		$today_warning = 0 + $sbase["average"]["today"]["Twarning"];
		$today_unknown = 0 + $sbase["average"]["today"]["Tunknown"];
		
		$today_OKnbEvent = 0 + $sbase["average"]["today"]["OKnbEvent"];
		$today_UNKNOWNnbEvent = 0 + $sbase["average"]["today"]["UNKNOWNnbEvent"];
		$today_WARNINGnbEvent = 0 + $sbase["average"]["today"]["WARNINGnbEvent"];
		$today_CRITICALnbEvent = 0 + $sbase["average"]["today"]["CRITICALnbEvent"];

		$tab_log = array();
		$day = date("d",time());
		$year = date("Y",time());
		$month = date("m",time());
		$startTimeOfThisDay = mktime(0, 0, 0, $month, $day, $year);
		$tab_svc_list_average = array();
		$tab_svc_list_average = array();
		$tab_svc_list_average["PTOK"] = 0;
		$tab_svc_list_average["PAOK"] = 0;
		$tab_svc_list_average["PTW"] = 0;
		$tab_svc_list_average["PAW"] = 0;
		$tab_svc_list_average["PTU"] = 0;
		$tab_svc_list_average["PAU"] = 0;
		$tab_svc_list_average["PTC"] = 0;
		$tab_svc_list_average["PAC"] = 0;
		$tab_svc_list_average["PTN"] = 0;
		$tab_svc_list_average["PKTOK"] = 0;
		$tab_svc_list_average["PKTW"] = 0;
		$tab_svc_list_average["PKTU"] = 0;
		$tab_svc_list_average["PKTC"] = 0;
		$tab_svc_list_average["nb_svc"] = 0;

		$tab_hosts = array();
		$day_current_start = 0;
		$day_current_end = time() + 1;
		$time = time();

		/*
		 * calculate resume
		 */
		$tab_resume = array();
		$tab = array();
		$timeTOTAL = $end_date_select - $start_date_select;	
		
		$Tok = $sbase["average"]["Tok"];
		$Twarning = $sbase["average"]["Twarning"];
		$Tcritical = $sbase["average"]["Tcritical"];
		$Tunknown = $sbase["average"]["Tunknown"];
		$Tnone = $timeTOTAL - ($Tok + $Twarning + $Tunknown + $Tcritical);
		
		if	($Tnone <= 1)
			$Tnone = 0;	
		
		# [OK] Service Group state
		$tab["state"] = _("OK");
		$tab["time"] = Duration::toString($Tok);
		$tab["timestamp"] = $Tok;
		$tab["pourcentTime"] = round($Tok/($timeTOTAL+1)*100,2) ;
		$tab["pourcentkTime"] = round($Tok/($timeTOTAL-$Tnone+1)*100,2). "%";
		$tab["nbAlert"] = $sbase["average"]["OKnbEvent"];
		$tab["style"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_ok"]."'";
		$tab_resume[0] = $tab;

		# [WARNING] Service Group state
		$tab["state"] = _("Warning");
		$tab["time"] = Duration::toString($Twarning);
		$tab["timestamp"] = $Twarning;
		$tab["pourcentTime"] = round($Twarning/$timeTOTAL*100,2);
		$tab["pourcentkTime"] = round($Twarning/($timeTOTAL-$Tnone+1)*100,2)."%";
		$tab["nbAlert"] = $sbase["average"]["WARNINGnbEvent"];
		$tab["style"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_warning"]."'";
		$tab_resume[1] = $tab;
		
		# [CRITICAL] Service Group state		
		$tab["state"] = _("Critical");
		$tab["time"] = Duration::toString($Tcritical);
		$tab["timestamp"] = $Tcritical;
		$tab["pourcentTime"] = round($Tcritical/$timeTOTAL*100,2);
		$tab["pourcentkTime"] = round($Tcritical/($timeTOTAL-$Tnone+1)*100,2)."%";
		$tab["nbAlert"] = $sbase["average"]["CRITICALnbEvent"];
		$tab["style"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_critical"]."'";
		$tab_resume[2] = $tab;

		# [UNKNOWN] Service Group state			
		$tab["state"] = _("Unknown");
		$tab["time"] = Duration::toString($Tunknown);
		$tab["timestamp"] = $Tunknown;
		$tab["pourcentTime"] = round($Tunknown/$timeTOTAL*100,2);
		$tab["pourcentkTime"] = round($Tunknown/($timeTOTAL-$Tnone+1)*100,2)."%";
		$tab["nbAlert"] = $sbase["average"]["UNKNOWNnbEvent"];
		$tab["style"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_unknown"]."'";
		$tab_resume[3] = $tab;

		# [UNDETERMINED] Service Group state				
		$tab["state"] = _("Undetermined");
		$tab["time"] = Duration::toString($Tnone);
		$tab["timestamp"] = $Tnone;
		$tab["pourcentTime"] = round($Tnone/$timeTOTAL*100,2);
		$tab["pourcentkTime"] = null;
		$tab["nbAlert"] = "";
		$tab["style"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_undetermined"]."'";
		$tab_resume[4] = $tab;

		/*
		 * Calculate table list
		 */
		$i = 0;
		foreach ($sbase as $svc_name => $tab)
		{
			if ($svc_name != "average"){
				$tab_tmp = array();
				$tab_tmp["hostName"] = getMyHostName($tab["host_id"]);
				$tab_tmp["serviceName"] = getMyServiceName($tab["svc_id"]);
				$tab_tmp["svc_id"] = $tab["svc_id"];
				if ($is_admin || (isset($lcaHostByID["LcaHost"][$tab["host_id"]]) && $lcaHostByID["LcaHost"][$tab["host_id"]]))					
					$tab_tmp["lcaHost"] = 1;	
				else
					$tab_tmp["lcaHost"] = 0;
				$tt = $end_date_select - $start_date_select;

				$tab_tmp["PtimeOK"] = round($tab["Tok"] / $tt *100,2);
				$tab_tmp["PtimeWARNING"] = round( $tab["Twarning"]/ $tt *100,2);
				$tab_tmp["PtimeUNKNOWN"] = round( $tab["Tunknown"]/ $tt *100,2);
				$tab_tmp["PtimeCRITICAL"] = round( $tab["Tcritical"]/ $tt *100,2);
				$tab_tmp["PtimeUNDETERMINED"] = round( ( $tt - ($tab["Tok"] + $tab["Twarning"] + $tab["Tunknown"] + $tab["Tcritical"] ))  / $tt *100,2);
				$tmp_none = $tt - ($tab["Tok"] + $tab["Twarning"] + $tab["Tunknown"]);
				
				$tab_tmp["OKnbEvent"] = isset($tab["TokNBAlert"]) ? $tab["TokNBAlert"] : 0;
				$tab_tmp["WARNINGnbEvent"] = isset($tab["WARNINGnbEvent"]) ? $tab["WARNINGnbEvent"] : 0;
				$tab_tmp["UNKNOWNnbEvent"] = isset($tab["UNKNOWNnbEvent"]) ? $tab["UNKNOWNnbEvent"] : 0;
				$tab_tmp["CRITICALnbEvent"] = isset($tab["TcriticalNBAlert"]) ? $tab["TcriticalNBAlert"] : 0;

				$kt = $tt - $tmp_none;
				if ($kt > 0){
					$tab_tmp["PktimeOK"] = $tab["Tok"] ? round($tab["Tok"] / ($kt) *100,2): 0;
					$tab_tmp["PktimeWARNING"] = $tab["Twarning"] ? round( $tab["Twarning"]/ ($kt) *100,2):0;
					$tab_tmp["PktimeUNKNOWN"] =  $tab["Tunknown"] ? round( $tab["Tunknown"]/ ($kt) *100,2):0;
					$tab_tmp["PktimeCRITICAL"] =  $tab["Tcritical"] ? round( $tab["Tcritical"]/ ($kt) *100,2):0;
				} else{
					$tab_tmp["PktimeOK"] = 0;
					$tab_tmp["PktimeWARNING"] = 0;
					$tab_tmp["PktimeUNKNOWN"] = 0;
					$tab_tmp["PktimeCRITICAL"] = 0;					
				}
				
				$tab_tmp["PtimeOK"] = number_format($tab_tmp["PtimeOK"], 1, '.', '');
				$tab_tmp["PtimeWARNING"] = number_format($tab_tmp["PtimeWARNING"], 1, '.', '');
				$tab_tmp["PtimeUNKNOWN"] = number_format($tab_tmp["PtimeUNKNOWN"], 1, '.', '');
				$tab_tmp["PtimeCRITICAL"] = number_format($tab_tmp["PtimeCRITICAL"], 1, '.', '');
				$tab_tmp["PtimeUNDETERMINED"] = number_format($tab_tmp["PtimeUNDETERMINED"], 1, '.', '');
				$tab_tmp["PtimeUNDETERMINED"] = ($tab_tmp["PtimeUNDETERMINED"] < 0.1) ? 0.0 : $tab_tmp["PtimeUNDETERMINED"];

				$tab_tmp["PktimeOK"] = number_format($tab_tmp["PktimeOK"], 1, '.', '');
				$tab_tmp["PktimeWARNING"] = number_format($tab_tmp["PktimeWARNING"], 1, '.', '');
				$tab_tmp["PktimeUNKNOWN"] = number_format($tab_tmp["PktimeUNKNOWN"], 1, '.', '');
				$tab_tmp["PktimeCRITICAL"] = number_format($tab_tmp["PktimeCRITICAL"], 1, '.', '');
	
				#
				## fill average svc table
				#
				$tab_svc_list_average["PTOK"] += $tab_tmp["PtimeOK"];
				$tab_svc_list_average["PAOK"]  += $tab_tmp["OKnbEvent"];
				$tab_svc_list_average["PTW"] += $tab_tmp["PtimeWARNING"];
				$tab_svc_list_average["PAW"] += $tab_tmp["WARNINGnbEvent"];
				$tab_svc_list_average["PTU"] += $tab_tmp["PtimeUNKNOWN"];
				$tab_svc_list_average["PAU"] += $tab_tmp["UNKNOWNnbEvent"];
				$tab_svc_list_average["PTC"] += $tab_tmp["PtimeCRITICAL"];
				$tab_svc_list_average["PAC"] += $tab_tmp["CRITICALnbEvent"];
				$tab_svc_list_average["PTN"] += $tab_tmp["PtimeUNDETERMINED"];
				$tab_svc_list_average["PKTOK"] += $tab_tmp["PktimeOK"];
				$tab_svc_list_average["PKTW"]+= $tab_tmp["PktimeWARNING"];
				$tab_svc_list_average["PKTU"]+= $tab_tmp["PktimeUNKNOWN"];
				$tab_svc_list_average["PKTC"] += $tab_tmp["PktimeCRITICAL"];
				$tab_svc_list_average["nb_svc"]+= 1;

				$tab_svc[$i++] = $tab_tmp;
			}
		}


		## calculate svc average
		# Alert
		if ($tab_svc_list_average["PAOK"] > 0)
			$tab_svc_list_average["PAOK"] = number_format($tab_svc_list_average["PAOK"] / $tab_svc_list_average["nb_svc"], 1, '.', '');
		if ($tab_svc_list_average["PAW"] > 0)
			$tab_svc_list_average["PAW"] = number_format($tab_svc_list_average["PAW"] / $tab_svc_list_average["nb_svc"], 1, '.', '');
		if ($tab_svc_list_average["PAU"] > 0)
			$tab_svc_list_average["PAU"] = number_format($tab_svc_list_average["PAU"] / $tab_svc_list_average["nb_svc"], 1, '.', '');
		if ($tab_svc_list_average["PAC"] > 0)
			$tab_svc_list_average["PAC"] = number_format($tab_svc_list_average["PAC"] / $tab_svc_list_average["nb_svc"], 1, '.', '');
		
		# Time
		if ($tab_svc_list_average["PTOK"] > 0)
			$tab_svc_list_average["PTOK"] = number_format($tab_svc_list_average["PTOK"] / $tab_svc_list_average["nb_svc"], 3, '.', '');
		if ($tab_svc_list_average["PTW"] > 0)
			$tab_svc_list_average["PTW"] = number_format($tab_svc_list_average["PTW"] / $tab_svc_list_average["nb_svc"], 3, '.', '');
		if ($tab_svc_list_average["PTC"] > 0)
			$tab_svc_list_average["PTC"] = number_format($tab_svc_list_average["PTC"] / $tab_svc_list_average["nb_svc"], 3, '.', '');
		if ($tab_svc_list_average["PTU"] > 0)
			$tab_svc_list_average["PTU"] = number_format($tab_svc_list_average["PTU"] / $tab_svc_list_average["nb_svc"], 3, '.', '');
		if ($tab_svc_list_average["PTN"] > 0)
			$tab_svc_list_average["PTN"] = number_format($tab_svc_list_average["PTN"] / $tab_svc_list_average["nb_svc"], 3, '.', '');

		# %
		if ($tab_svc_list_average["PKTOK"] > 0)
			$tab_svc_list_average["PKTOK"] = number_format($tab_svc_list_average["PKTOK"] / $tab_svc_list_average["nb_svc"], 3, '.', '');
		if ($tab_svc_list_average["PKTW"] > 0)
			$tab_svc_list_average["PKTW"] = number_format($tab_svc_list_average["PKTW"] / $tab_svc_list_average["nb_svc"], 3, '.', '');
		if ($tab_svc_list_average["PKTC"] > 0)
			$tab_svc_list_average["PKTC"] = number_format($tab_svc_list_average["PKTC"] / $tab_svc_list_average["nb_svc"], 3, '.', '');
		if ($tab_svc_list_average["PKTU"] > 0)
			$tab_svc_list_average["PKTU"] = number_format($tab_svc_list_average["PKTU"] / $tab_svc_list_average["nb_svc"], 3, '.', '');

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
		$totalAlert = $sbase["average"]["UNKNOWNnbEvent"] + $sbase["average"]["WARNINGnbEvent"] + $sbase["average"]["OKnbEvent"] + $sbase["average"]["CRITICALnbEvent"];

		$tpl->assign('totalAlert', $totalAlert);
		$tpl->assign('totalTime', Duration::toString($totalTime));
		$tpl->assign('totalpTime', $totalpTime);
		$tpl->assign('totalpkTime', $totalpkTime);
		$tpl->assign('status', $status);
		$tpl->assign("tab_resume", $tab_resume);
		$tpl->assign("tab_svc_list_average", $tab_svc_list_average);
		$tpl->assign('period_name', _(" From "));		
		$tpl->assign('date_start_select', $start_date_select);
		$tpl->assign('to', _(" to "));
		$tpl->assign('date_end_select', $end_date_select);
		$tpl->assign('infosTitle', _("Duration : ") . Duration::toString($tt));
	}
	
	if (isset($tab_svc))
		$tpl->assign("tab_svc", $tab_svc);

	$tpl->assign('tab_log', $tab_log);
	$tpl->assign('actualTitle', _(" Actual "));
	$tpl->assign('style_ok', "class='ListColCenter' style='background:" . $oreon->optGen["color_ok"]."'");
	$tpl->assign('style_ok_alert', "class='ListColCenter' style='width: 25px; background:" . $oreon->optGen["color_ok"]."'");
	$tpl->assign('style_critical', "class='ListColCenter' style='background:" . $oreon->optGen["color_critical"]."'");
	$tpl->assign('style_critical_alert', "class='ListColCenter' style='width: 25px; background:" . $oreon->optGen["color_critical"]."'");
	$tpl->assign('style_warning' , "class='ListColCenter' style='background:" . $oreon->optGen["color_warning"]."'");
	$tpl->assign('style_warning_alert' , "class='ListColCenter' style='width: 25px; background:" . $oreon->optGen["color_warning"]."'");
	$tpl->assign('style_unknown' , "class='ListColCenter' style='background:" . $oreon->optGen["color_unknown"]."'");
	$tpl->assign('style_unknown_alert' , "class='ListColCenter' style='width: 25px; background:" . $oreon->optGen["color_unknown"]."'");
	$tpl->assign('style_undetermined' , "class='ListColCenter' style='background:" . $oreon->optGen["color_undetermined"]."'");
	$tpl->assign('style_undetermined_alert' , "class='ListColCenter' style='width: 25px; background:" . $oreon->optGen["color_undetermined"]."'");
	$tpl->assign('hostTitle', _("Host"));
	$tpl->assign('serviceTilte', _("Service"));
	$tpl->assign('allTilte',  _("All"));
	$tpl->assign('averageTilte',  _("Average"));

	$tpl->assign('OKTitle', _("OK"));
	$tpl->assign('WarningTitle', _("Warning"));
	$tpl->assign('UnknownTitle', _("Unknown"));
	$tpl->assign('CriticalTitle', _("Critical"));
	$tpl->assign('UndeterminedTitle', _("Undetermined"));

	$tpl->assign('StateTitle', _("Status"));
	$tpl->assign('TimeTitle', _("Time"));
	$tpl->assign('TimeTotalTitle', _("Total Time"));
	$tpl->assign('KnownTimeTitle', _("Known Time"));
	$tpl->assign('AlertTitle', _("Alert"));
	$tpl->assign('DateTitle', _("Date"));
	$tpl->assign('EventTitle', _("Event"));
	$tpl->assign('InformationsTitle', _("Info"));
	$tpl->assign('periodTitle', _("Period Selection"));
	$tpl->assign('resumeTitle', _("Service Group state"));
	$tpl->assign('logTitle', _("Today's Host log"));
	$tpl->assign('svcTitle', _("State Breakdowns For Host Services"));

	$formPeriod->setDefaults(array('period' => $period));

	$tpl->assign('hostID', getMyHostID($mservicegroup));
	
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$formPeriod->accept($renderer);
	
	$tpl->assign('formPeriod', $renderer->toArray());

	#Apply a template definition
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$formservicegroup->accept($renderer);
	$tpl->assign('formservicegroup', $renderer->toArray());
	$tpl->assign("p", $p);

	if ($mservicegroup){
		$tpl->assign("link_csv_url", "./include/reporting/dashboard/csvExport/csv_ServiceGroupLogs.php?sid=".$sid."&servicegroup=".$mservicegroup.$var_url_export_csv);
		$tpl->assign("link_csv_name", "Export CSV");
	}

	# For today in timeline
	$tt = 0 + ($today_end - $today_start);
	$today_pending = $tt - ($today_warning + $today_ok + $today_critical + $today_unknown);
	$today_pending = round(($today_pending/$tt *100),2);
	$today_ok = ($today_ok <= 0) ? 0 : round($today_ok / $tt *100,2);
	$today_critical = ($today_critical <= 0) ? 0 : round($today_critical / $tt *100,2);
	$today_warning = ($today_warning <= 0) ? 0 : round($today_warning / $tt *100,2);
	$today_unknown = ($today_unknown <= 0) ? 0 : round($today_unknown / $tt *100,2);
	$today_pending = ($today_pending < 0.1) ? "0" : $today_pending;

	if ($mservicegroup)	{
		
		$color = substr($oreon->optGen["color_ok"],1) .':'.
		 		 substr($oreon->optGen["color_warning"],1) .':'.
		 		 substr($oreon->optGen["color_critical"],1) .':'. 
		 		 substr($oreon->optGen["color_unknown"],1).':'.
		 		 substr($oreon->optGen["color_undetermined"],1);

		$today_var = '&svc_group_id='.$servicegroup_id.'&today_ok='.$today_ok . '&today_critical='.$today_critical . '&today_warning='.$today_warning.'&today_unknown='.$today_unknown. '&today_pending=' . $today_pending;
		$today_var .= '&today_OKnbEvent='.$today_OKnbEvent.'&today_UNKNOWNnbEvent='.$today_UNKNOWNnbEvent.'&today_WARNINGnbEvent='.$today_WARNINGnbEvent.'&today_CRITICALnbEvent='.$today_CRITICALnbEvent;
		$type = 'ServiceGroup';
		$host_id = $servicegroup_id;

		include('ajaxReporting_js.php');
		
	} else {
		?>
		<script type="text/javascript">
		function initTimeline() {;}
		</script>
		<?php
	}
	
	$tpl->display("template/viewServicesGroupLog.ihtml");
?>