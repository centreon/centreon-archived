<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus - Christophe Coraboeuf - Cedrick Facon

Adapted to Pear library Quickform & Template_PHPLIB by Merethis company, under direction of Cedrick Facon

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

	function my_getTimeTamps($dateSTR)
	{
		list($m,$d,$y) = split('/',$dateSTR);
		return (mktime(0,0,0,$m,$d,$y));
	}
	function trim_value(&$value)
	{
	   $value = trim($value);
	}
	function getLogData($time_event, $host, $service, $status, $output, $type){
		global $lang;
		$tab_img = array("UP" => './img/icones/12x12/recovery.gif',
						"DOWN" => './img/icones/12x12/alert.gif',
						"UNREACHABLE" => './img/icones/12x12/queue.gif',
						"NONE" => './img/icones/12x12/info.gif',
						);
		$tab["time"] = date($lang["header_format"], $time_event);
		$tab["timeb"] = $time_event;
		$tab["host"] = $host;
		$tab["service"] = $service;
		$tab["status"] = $status;
		$tab["output"] = $output;
		$tab["type"] = $type;
		$tab["img"] = $tab_img[$status];		
		return $tab ;
	}
		
	isset ($_GET["host"]) ? $mhost = $_GET["host"] : $mhost = NULL;
	isset ($_POST["host"]) ? $mhost = $_POST["host"] : $mhost = $mhost;

	isset ($_GET["service"]) ? $mservice = $_GET["service"] : $mservice = NULL;
	isset ($_POST["service"]) ? $mservice = $_POST["service"] : $mservice = $mservice;

	require_once './class/other.class.php';
	require_once './include/common/common-Func.php';
	include("./include/monitoring/log/choose_log_file.php");

	$day = date("d",time());
	$year = date("Y",time());
	$month = date("m",time());
	$hour = date("G",time());
	$minute = date("i",time());
	$second = date("s",time());
	$start_date_day = mktime(0, 0, 0, $month, $day, $year);
	if(isset($_POST["period"]))
	{
		if($_POST["period"] == "today")
		{
			$start_date_select = mktime(0, 0, 0, $month, $day, $year);
			$end_date_select = time();
		}
		else if($_POST["period"] == "last24hours")
		{
			$start_date_select = time() - (24*60*60);
			$end_date_select = time();
		}
		else if($_POST["period"] == "yesterday")
		{
			$start_date_select = mktime(0, 0, 0, $month, $day-1, $year);
			$end_date_select = mktime(0, 0, 0, $month, $day, $year);
		}
		else if($_POST["period"] == "thisweek")//a fair
		{
			$dd = (date("D",mktime(0, 0, 0, $month, $day, $year)));
			for($ct = 1; $dd != "Mon" ;$ct++)
				$dd = (date("D",mktime(0, 0, 0, $month, ($day-$ct), $year)));
			$start_date_select = mktime(0, 0, 0, $month, $day-$ct, $year);
			$end_date_select = time();
		}
		else if($_POST["period"] == "last7days")
		{
			$start_date_select = mktime($hour, $minute, $second, $month, $day-7, $year);
			$end_date_select = mktime($hour, $minute, $second, $month, $day, $year);
		}
		else if($_POST["period"] == "last30days")// attention au 31
		{
			$start_date_select = mktime($hour, $minute, $second, $month, $day-30, $year);
			$end_date_select = mktime($hour, $minute, $second, $month, $day, $year);
		}
		else if($_POST["period"] == "lastyear")// attention au 31
		{
			$start_date_select = mktime(23, 59, 60, 12, 31, $year-1);
			$end_date_select = mktime(23, 59, 60, 12, 31, $year);
		}
		else if($_POST["period"] == "thismonth")
		{
			$start_date_select = mktime(0, 0, 0, $month, 0, $year);
			$end_date_select = mktime($hour, $minute, $second, $month, $day, $year);
		}
		else if($_POST["period"] == "thisyear")
		{
			$start_date_select = mktime(0, 0, 0, 0, 0, $year);
			$end_date_select = mktime($hour, $minute, $second, $month, $day, $year);
		}
		else // lastmonth
		{
			$start_date_select = mktime(23, 59, 60, $month-1, 0, $year);
			$end_date_select = mktime(23, 59, 60, $month, 0, $year);
		}		
	}
		else
		{
			$start_date_select = mktime(0, 0, 0, $month, $day, $year);
			$end_date_select = time();
		}

	if(isset($_POST["end"]) && isset($_POST["start"]))
	{
		if(!$_POST["end"])
			$end = time();
		else
			$end = my_getTimeTamps($_POST["end"]);
			
		$endday = date("d",$end);
		$endyear = date("Y",$end);
		$endmonth = date("m",$end);
		$endhour = date("G",$end);
		$endminute = date("i",$end);
		$endsecond = date("s",$end);
		$end_date_select = mktime(23, 59, 59, $endmonth, $endday, $endyear);
		if(!$_POST["start"])
			$start = mktime(0, 0, 0, $endmonth, $endday, $endyear);
		else
			$start = my_getTimeTamps($_POST["start"]);
		$start_date_select = $start;
	}

	$host_id = getMyHostID($mhost);
	$service_id = getMyServiceID($mservice, $host_id);

//echo $service_id . "**<br>";


#
## recupere les log host en base
#
$rq = 'SELECT ' .
	'sum(UPTimeScheduled)' .
	' as Tup,' .				
	'sum(DOWNTimeScheduled)' .
	' as Tdown,' .
	'sum(UNREACHABLETimeScheduled)' .
	' as Tunreach, ' .				
	'sum(UNDETERMINATETimeScheduled)' .
	' as Tnone, ' .
	'min(date_start) as log_date_start,' .
	'max(date_end) as log_date_end,' .
	'sum(UNREACHABLETimeScheduled) as unreach FROM `log_archive_host` WHERE host_id = ' . $host_id  .
	' AND date_start >=  ' . $start_date_select .
	' AND date_end <= ' . $end_date_select;

	$res = & $pearDB->query($rq);
	if (PEAR::isError($res)){
	  die($res->getMessage());
	} else {
	  while ($h = $res->fetchRow()){

	$Tup = 0 + $h["Tup"];
	$Tdown = 0 + $h["Tdown"];
	$Tunreach = 0 + $h["Tunreach"];
	$Tnone = 0 + $h["Tnone"];
	
	  }
	}
#
## recupere les log svc en base
#
$rq = 'SELECT ' .
	'sum(OKTimeScheduled)' .
	' as Tok,' .				
	'sum(WARNINGTimeScheduled)' .
	' as Twarn,' .
	'sum(UNKNOWNTimeScheduled)' .
	' as Tunknown, ' .				
	'sum(UNDETERMINATETimeScheduled)' .
	' as Tnone, ' .
	'sum(CRITICALTimeScheduled)' .
	' as Tcri, ' .
	'min(date_start) as log_date_start,' .
	'max(date_end) as log_date_end' .
	' FROM `log_archive_service` WHERE host_id = ' . $host_id  .
	' AND service_id =  ' . $service_id .
	' AND date_start >=  ' . $start_date_select .
	' AND date_end <= ' . $end_date_select;

	$res = & $pearDB->query($rq);
	$tab_svc_bdd = array();
	if (PEAR::isError($res)){
	  die($res->getMessage());
	} else { 
	  while ($s = $res->fetchRow()){
		$tab_svc_bdd["Tok"] = 0 + $s["Tok"];
		$tab_svc_bdd["Twarn"] = 0 + $s["Twarn"];
		$tab_svc_bdd["Tunknown"] = 0 + $s["Tunknown"];
		$tab_svc_bdd["Tnone"] = 0 + $s["Tnone"];
		$tab_svc_bdd["Tcri"] = 0 + $s["Tcri"];
		
		
	  }
	}

	#
	## fourchette de temps
	#
	$period = array();
	$period["today"] = "Today";
	$period["last24hours"] = "Last 24 Hours";
	$period["yesterday"] = "Yesterday";
	$period["thisweek"] = "This Week";
	$period["last7days"] = "Last 7 Days";
	$period["thismonth"] = "This Month";
	$period["last30days"] = "Last 30 Days";
	$period["lastmonth"] = "Last Month";
	$period["thisyear"] = "This Year";
	$period["lastyear"] = "Last Year";

	$formPeriod1 = new HTML_QuickForm('FormPeriod1', 'post', "?p=".$p);

	isset($mhost) ? $formPeriod1->addElement('hidden', 'host', $mhost) : NULL;
	isset($mservice) ? $formPeriod1->addElement('hidden', 'service', $mservice) : NULL;
	
	$formPeriod1->addElement('header', 'title', $lang["m_predefinedPeriod"]);
	$selHost = $formPeriod1->addElement('select', 'period', $lang["m_predefinedPeriod"], $period, array("onChange" =>"this.form.submit();"));	


	$formPeriod2 = new HTML_QuickForm('FormPeriod2', 'post', "?p=".$p);
	isset($mhost) ? $formPeriod2->addElement('hidden', 'host', $mhost) : NULL;
	isset($mservice) ? $formPeriod2->addElement('hidden', 'service', $mservice) : NULL;
	$formPeriod2->addElement('header', 'title', $lang["m_customizedPeriod"]);
	$formPeriod2->addElement('text', 'start', $lang["m_start"]);
	$formPeriod2->addElement('button', "startD", $lang['modify'], array("onclick"=>"displayDatePicker('start')"));
	$formPeriod2->addElement('text', 'end', $lang["m_end"]);
	$formPeriod2->addElement('button', "endD", $lang['modify'], array("onclick"=>"displayDatePicker('end')"));

	$sub = $formPeriod2->addElement('submit', 'submit', $lang["m_view"]);
	$res = $formPeriod2->addElement('reset', 'reset', $lang["reset"]);




// parser que pour l'host demandé
function parseFile($file,$end_time,$host_name,$service_name){
	$start_time = 0;
	$log = NULL;
	$matches = "";

	if (file_exists($file) && !($log = fopen($file, "r")))
		echo "pel_cant_open" . $file . "<br>";
	$tab_log = array();	
	$tab_svc_log = array();
	$tablist = array();
	$res1 = array();	

	if ($log)
		for ($a=0, $b= 0, $i = 0; $str = fgets($log); $i++){
			if (preg_match("/^\[([0-9]*)\] (.+)/", $str, $matches)){				
				$time_event = $matches[1];
				$res = preg_split("/:/", $matches[2], 2);
			if (isset($res[1]))
				$res1 = preg_split("/;/", $res[1]);
			$type = $res[0];
				array_walk($res1, 'trim_value');
			#
			## find the log's start time
			#
			if ($i == 0)// take start time
			$start_time = $time_event;
			if (!strncmp($type, "LOG ROTATION", 12))
			{
					$start_time = $time_event;
			}			
			else if (!strncmp($type, "CURRENT HOST STATE", 18) || !strncmp($type, "INITIAL HOST STATE", 18)){
				$tablist[$res1[0]] = array();
				$tablist[$res1[0]]["current_time"] = $start_time;
				$tablist[$res1[0]]["current_state"] = $res1[1];
				$tablist[$res1[0]]["timeUP"] = 0;
				$tablist[$res1[0]]["timeDOWN"] = 0;
				$tablist[$res1[0]]["timeUNREACHABLE"] = 0;
				$tablist[$res1[0]]["timeNONE"] = 0;
				$tablist[$res1[0]]["start_time"] = $start_time;
				$tablist[$res1[0]]["tab_svc_log"] = array();
			}
			
			
			if ((!strncmp($type, "CURRENT SERVICE STATE", 21) || !strncmp($type, "INITIAL SERVICE STATE", 21)) && $res1[0] == $host_name && $res1[1] == $service_name)
			{
				$tablist[$res1[0]]["tab_svc_log"][$res1[1]] = array();
				$tab_tmp = array();
				$tab_tmp["current_state"] = $res1[2];
				$tab_tmp["current_time"] = $start_time;
				$tab_tmp["timeOK"] = 0;
				$tab_tmp["timeWARNING"] = 0;
				$tab_tmp["timeUNKNOWN"] = 0;
				$tab_tmp["timeCRITICAL"] = 0;
				$tab_tmp["timeNONE"] = 0;
				$tab_tmp["service_id"] = getMyServiceID($res1[1],getMyHostID($res1[0]));				
				$tablist[$res1[0]]["tab_svc_log"][$res1[1]] = $tab_tmp;
			}
			#
			## host
			#
			
			if (!strncmp($type, "HOST ALERT", 10) && $res1[0] == $host_name)
			{
				if(!isset($tablist[$res1[0]]))
				{
					$tablist[$res1[0]] = array();
					$tablist[$res1[0]]["current_time"] = $start_time;
					$tablist[$res1[0]]["current_state"] = "NONE";
					$tablist[$res1[0]]["timeUP"] = 0;
					$tablist[$res1[0]]["timeDOWN"] = 0;
					$tablist[$res1[0]]["timeUNREACHABLE"] = 0;
					$tablist[$res1[0]]["timeNONE"] = 0;
					$tablist[$res1[0]]["start_time"] = $start_time;
					$tablist[$res1[0]]["tab_svc_log"] = array();
				}						
				if(!strncmp($tablist[$res1[0]]["current_state"], "UP", 2))
				$tablist[$res1[0]]["timeUP"] += ($time_event-$tablist[$res1[0]]["current_time"]);
				elseif(!strncmp($tablist[$res1[0]]["current_state"], "DOWN", 4))
				$tablist[$res1[0]]["timeDOWN"] += ($time_event-$tablist[$res1[0]]["current_time"]);
				elseif(!strncmp($tablist[$res1[0]]["current_state"], "UNREACHABLE", 11))
				$tablist[$res1[0]]["timeUNREACHABLE"] += ($time_event-$tablist[$res1[0]]["current_time"]);
				else				
				$tablist[$res1[0]]["timeNONE"] += ($time_event-$tablist[$res1[0]]["current_time"]);
				$tab_log[$a++] = getLogData($time_event, $res1[0], "", $res1[1], $res1[4], $type);//filtre
				$tablist[$res1[0]]["current_state"] = $res1[1];
				$tablist[$res1[0]]["current_time"] = $time_event; //save time
			}
			
			#
			## service associed
			#
			 if (!strncmp($type, "SERVICE ALERT", 13))
			{
				if(isset($tablist[$res1[0]]["tab_svc_log"][$res1[1]]))
				{
					$tab_tmp = array();
					$tab_tmp = $tablist[$res1[0]]["tab_svc_log"][$res1[1]];
					if(!strncmp($tab_tmp["current_state"], "OK", 2))
						$tab_tmp["timeOK"] += ($time_event-$tab_tmp["current_time"]);
					elseif(!strncmp($tab_tmp["current_state"], "WARNING", 7))
						$tab_tmp["timeWARNING"] += ($time_event-$tab_tmp["current_time"]);
					elseif(!strncmp($tab_tmp["current_state"], "UNKNOWN", 7))
						$tab_tmp["timeUNKNOWN"] += ($time_event-$tab_tmp["current_time"]);
					elseif(!strncmp($tab_tmp["current_state"], "CRITICAL", 8))
						$tab_tmp["timeCRITICAL"] += ($time_event-$tab_tmp["current_time"]);
					else
					{
						echo "none: " . $tab_tmp["current_state"] . "<br>";
						$tab_tmp["timeNONE"] += ($time_event-$tab_tmp["current_time"]);
						
					}	
					//$tab_log[$a++] = getLogData($time_event, $res1[0], "", $res1[1], $res1[4], $type);				
					$tab_tmp["current_time"] = $time_event; //save time
					$tab_tmp["current_state"] = $res1[2]; //save time
					$tablist[$res1[0]]["tab_svc_log"][$res1[1]] = $tab_tmp;
				}
			}
		}
	}
	$tablist["time_start"] = $start_time;
	$tablist["tab_log"] = $tab_log;
	return($tablist);
}


#
## if today is include in the time period
#
$tab_log = array();
if((time() - (24*60*60)) < $end_date_select)
{
	$tmp = $oreon->Nagioscfg["log_file"];
	$tab = parseFile($tmp,time(),$mhost,$mservice);
	
	
	//$mtime_start = $tab["time_start"];
	$tab_log = $tab["tab_log"];
	foreach($tab as $host => $htab)
	{
		if ($host == $mhost)
		{
			/*

				
			#
			## add log day
			#		
			$Tup += $htab["timeUP"];
			$Tdown += $htab["timeDOWN"];
			$Tunreach += $htab["timeUNREACHABLE"];
			$Tnone += (($end_date_select - $start_date_select) - ($Tup + $Tdown + $Tunreach));
			$tab_svc =array();
			$i = 0;			
			*/
			while (list($key, $value) = each($htab["tab_svc_log"])) {
				if($key == $mservice)
				{
					$tab_tmp = $value;
					$tab_tmp["svcName"] = $key;
					if(!strncmp($tab_tmp["current_state"], "OK", 2))
						$tab_svc_bdd["Tok"] += (time()-$tab_tmp["current_time"]);
					elseif(!strncmp($tab_tmp["current_state"], "WARNING", 7))
						$tab_svc_bdd["Twarn"] += (time()-$tab_tmp["current_time"]);
					elseif(!strncmp($tab_tmp["current_state"], "UNKNOWN", 7))
						$tab_svc_bdd["Tunknown"]  += (time()-$tab_tmp["current_time"]);
					elseif(!strncmp($tab_tmp["current_state"], "CRITICAL", 8))
						$tab_svc_bdd["Tcri"] += (time()-$tab_tmp["current_time"]);
					else
						$tab_svc_bdd["Tnone"] += (time()-$tab_tmp["current_time"]);	
				}
			}
		}
	}
}

else // today is not in the period
{
	/*
	$i=0;
	foreach($tab_svc_bdd as $svc_id => $tab)
	{
		$tab_tmp = array();
		$tab_tmp["svcName"] = getMyServiceName($svc_id);
		$tt = $end_date_select - $start_date_select;
		$tab_tmp["PtimeOK"] = round($tab["Tok"] / $tt *100,3);
		$tab_tmp["PtimeWARNING"] = round( $tab["Twarn"]/ $tt *100,3);
		$tab_tmp["PtimeUNKNOWN"] = round( $tab["Tunknown"]/ $tt *100,3);
		$tab_tmp["PtimeCRITICAL"] = round( $tab["Tcri"]/ $tt *100,3);
		$tab_tmp["PtimeNONE"] = round( ( $tt - ($tab["Tok"] + $tab["Twarn"] + $tab["Tunknown"] + $tab["Tcri"])
											 )  / $tt *100,3);
		$tab_svc[$i++] = $tab_tmp;
	}
	*/
}


#
## calculate service  resume
#
$tab_resume = array();
$tab = array();
$timeTOTAL = $end_date_select - $start_date_select;

/*
$tab_svc_bdd["Tok"]
		$tab_svc_bdd["Twarn"] 
		$tab_svc_bdd["Tunknown"] 
		$tab_svc_bdd["Tnone"] 
		$tab_svc_bdd["Tcri"]
*/

$Tnone = $timeTOTAL - ($tab_svc_bdd["Tok"]
+ $tab_svc_bdd["Twarn"]
+$tab_svc_bdd["Tunknown"]
+$tab_svc_bdd["Tnone"]
+$tab_svc_bdd["Tcri"]);

$tab["state"] = $lang["m_OKTitle"];
$tab["time"] = Duration::toString($tab_svc_bdd["Tok"]);
$tab["pourcentTime"] = round($tab_svc_bdd["Tok"]/$timeTOTAL*100,2);
$tab["pourcentkTime"] = round($tab_svc_bdd["Tok"]/$timeTOTAL*100,2);
$tab["style"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_ok"]."'";
$tab_resume[0] = $tab;

$tab["state"] = $lang["m_WarningTitle"];
$tab["time"] = Duration::toString($tab_svc_bdd["Twarn"]);
$tab["pourcentTime"] = round($tab_svc_bdd["Twarn"]/$timeTOTAL*100,2);
$tab["pourcentkTime"] = round($tab_svc_bdd["Twarn"]/$timeTOTAL*100,2);
$tab["style"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_warning"]."'";
$tab_resume[1] = $tab;

$tab["state"] = $lang["m_UnknownTitle"];
$tab["time"] = Duration::toString($tab_svc_bdd["Tunknown"] );
$tab["pourcentTime"] = round($tab_svc_bdd["Tunknown"] /$timeTOTAL*100,2);
$tab["pourcentkTime"] = round($tab_svc_bdd["Tunknown"] /$timeTOTAL*100,2);
$tab["style"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_unknown"]."'";
$tab_resume[2] = $tab;

$tab["state"] = $lang["m_CriticalTitle"];
$tab["time"] = Duration::toString($tab_svc_bdd["Tcri"]);
$tab["pourcentTime"] = round($tab_svc_bdd["Tcri"]/$timeTOTAL*100,2);
$tab["pourcentkTime"] = round($tab_svc_bdd["Tcri"]/$timeTOTAL*100,2);
$tab["style"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_critical"]."'";
$tab_resume[3] = $tab;

$tab["state"] = $lang["m_PendingTitle"];
$tab["time"] = Duration::toString($Tnone);
$tab["pourcentTime"] = round($Tnone/$timeTOTAL*100,2);
$tab["pourcentkTime"] = round($Tnone/$timeTOTAL*100,2);
$tab["style"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_pending"]."'";
$tab_resume[4] = $tab;


$start_date_select = date("d/m/Y G:i:s", $start_date_select);
$end_date_select =  date("d/m/Y G:i:s", $end_date_select);




	$path = "./include/reporting/";
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "");

	$tpl->assign('o', $o);
	

	$tpl->assign('periodTitle', $lang["m_selectPeriodTitle"]);
	$tpl->assign('resumeTitle', $lang["m_serviceResumeTitle"]);
	$tpl->assign('logTitle', $lang["m_hostLogTitle"]);
	$tpl->assign('svcTitle', $lang["m_hostSvcAssocied"]);
	$tpl->assign('style_ok', "class='ListColCenter' style='background:" . $oreon->optGen["color_up"]."'");
	$tpl->assign('style_warning' , "class='ListColCenter' style='background:" . $oreon->optGen["color_warning"]."'");
	$tpl->assign('style_critical' , "class='ListColCenter' style='background:" . $oreon->optGen["color_critical"]."'");
	$tpl->assign('style_unknown' , "class='ListColCenter' style='background:" . $oreon->optGen["color_unknown"]."'");
	$tpl->assign('style_pending' , "class='ListColCenter' style='background:" . $oreon->optGen["color_pending"]."'");


	$tpl->assign('serviceTilte', $lang["m_serviceTilte"]);
	$tpl->assign('OKTitle', $lang["m_OKTitle"]);
	$tpl->assign('WarningTitle', $lang["m_WarningTitle"]);
	$tpl->assign('UnknownTitle', $lang["m_UnknownTitle"]);
	$tpl->assign('CriticalTitle', $lang["m_CriticalTitle"]);
	$tpl->assign('PendingTitle', $lang["m_PendingTitle"]);

	$tpl->assign('StateTitle', $lang["m_StateTitle"]);
	$tpl->assign('TimeTitle', $lang["m_TimeTitle"]);
	$tpl->assign('TimeTotalTitle', $lang["m_TimeTotalTitle"]);
	$tpl->assign('KnownTimeTitle', $lang["m_KnownTimeTitle"]);
	$tpl->assign('DateTitle', $lang["m_DateTitle"]);
	$tpl->assign('EventTitle', $lang["m_EventTitle"]);
	$tpl->assign('HostTitle', $lang["m_HostTitle"]);
	$tpl->assign('InformationsTitle', $lang["m_InformationsTitle"]);


	$tpl->assign('infosTitle1', $mhost);
	$tpl->assign('infosTitle2', $start_date_select." => ".$end_date_select);		
	$tpl->assign('host_name', $mhost);		
	$tpl->assign('service_name', $mservice);		




	$status = "";
	foreach ($tab_resume  as $tb)
		if($tb["pourcentTime"] > 0)
			$status .= "&value[".$tb["state"]."]=".$tb["pourcentTime"];  
        
	$tpl->assign('status', $status);		





	$renderer1 = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$formPeriod1->accept($renderer1);
	$tpl->assign('formPeriod1', $renderer1->toArray());	

	$renderer2 = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$formPeriod2->accept($renderer2);	
	$tpl->assign('formPeriod2', $renderer2->toArray());


	$tpl->assign("tab_resume", $tab_resume);
	$tpl->assign("tab_log", $tab_log);

	$tpl->assign('lang', $lang);
	$tpl->assign("p", $p);
	$tpl->display("viewServicesLog.ihtml");


?>