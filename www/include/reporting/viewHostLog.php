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

	$time_startR = microtime_float();

	$path = "./include/reporting/";
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "");

	$tpl->assign('o', $o);

	# LCA 
	$lcaHostByName = getLcaHostByName($pearDB);
	$isRestreint = HadUserLca($pearDB);
	
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

	require_once './class/other.class.php';
	require_once './include/common/common-Func.php';
	include("./include/monitoring/log/choose_log_file.php");



if($mhost)	{
	$day = date("d",time());
	$year = date("Y",time());
	$month = date("m",time());
	$hour = date("G",time());
	$minute = date("i",time());
	$second = date("s",time());
	$start_date_day = mktime(0, 0, 0, $month, $day, $year);
	if(isset($_POST["period"])){
		if($_POST["period"] == "today"){
			$start_date_select = mktime(0, 0, 0, $month, $day, $year);
			$end_date_select = time();
		} else if($_POST["period"] == "last24hours"){
			$start_date_select = time() - (24*60*60);
			$end_date_select = time();
		} else if($_POST["period"] == "yesterday"){
			$start_date_select = mktime(0, 0, 0, $month, $day-1, $year);
			$end_date_select = mktime(0, 0, 0, $month, $day, $year);
		} else if($_POST["period"] == "thisweek"){//a fair
			$dd = (date("D",mktime(0, 0, 0, $month, $day, $year)));
			for($ct = 1; $dd != "Mon" ;$ct++)
				$dd = (date("D",mktime(0, 0, 0, $month, ($day-$ct), $year)));
			$start_date_select = mktime(0, 0, 0, $month, $day-$ct, $year);
			$end_date_select = time();
		} else if($_POST["period"] == "last7days"){
			$start_date_select = mktime(0, 0, 0, $month, $day-7, $year);
			$end_date_select = mktime($hour, $minute, $second, $month, $day, $year);
		} else if($_POST["period"] == "last30days"){// attention au 31 
			$start_date_select = mktime(0, 0, 0, $month, $day-30, $year);
			$end_date_select = mktime($hour, $minute, $second, $month, $day, $year);
		} else if($_POST["period"] == "lastyear"){// attention au 31 
			$start_date_select = mktime(23, 59, 60, 12, 31, $year-1);
			$end_date_select = mktime(23, 59, 60, 12, 31, $year);
		} else if($_POST["period"] == "thismonth") {
			$start_date_select = mktime(0, 0, 0, $month, 0, $year);
			$end_date_select = mktime($hour, $minute, $second, $month, $day, $year);
		} else if($_POST["period"] == "thisyear"){
			$start_date_select = mktime(0, 0, 0, 0, 0, $year);
			$end_date_select = mktime($hour, $minute, $second, $month, $day, $year);
		} else { // lastmonth
			$start_date_select = mktime(23, 59, 60, $month-1, 0, $year);
			$end_date_select = mktime(23, 59, 60, $month, 0, $year);
		}		
	} else {
		$start_date_select = mktime(0, 0, 0, $month, $day, $year);
		$end_date_select = time();
	}

	if(isset($_POST["end"]) && isset($_POST["start"])){
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
$sd = $start_date_select;
$ed = $end_date_select;

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
	' AND date_start >=  ' . ($start_date_select-1) .
	' AND date_end <= ' . $end_date_select;

	$res = & $pearDB->query($rq);
	if (PEAR::isError($res)){
	  die($res->getMessage());
	} else {
	  while ($h =& $res->fetchRow()){

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
	'service_id, ' .
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
	' AND date_start >=  ' . ($start_date_select-1) .
	' AND date_end <= ' . $end_date_select .
	' GROUP BY service_id';
	$res = & $pearDB->query($rq);
	$tab_svc_bdd = array();
	if (PEAR::isError($res)){
	  die($res->getMessage());
	} else { 
	  while ($s =& $res->fetchRow()){ 	
		$tab_svc_bdd[$s["service_id"]]["Tok"] = 0 + $s["Tok"];
		$tab_svc_bdd[$s["service_id"]]["Twarn"] = 0 + $s["Twarn"];
		$tab_svc_bdd[$s["service_id"]]["Tunknown"] = 0 + $s["Tunknown"];
		$tab_svc_bdd[$s["service_id"]]["Tnone"] = 0 + $s["Tnone"];
		$tab_svc_bdd[$s["service_id"]]["Tcri"] = 0 + $s["Tcri"];
	  }
	}

}

	#
	## Selection de l'host
	#

	$formHost = new HTML_QuickForm('formHost', 'post', "?p=".$p);
	$formHost->addElement('header', 'title', "...selection host...");

	isset($_POST["period"]) ? $formHost->addElement('hidden', 'period', $_POST["period"]) : NULL;
	isset($_POST["end"]) ? $formHost->addElement('hidden', 'end', $_POST["end"]) : NULL;
	isset($_POST["start"]) ? $formHost->addElement('hidden', 'start', $_POST["start"]) : NULL;
	
	$res =& $pearDB->query("SELECT host_name FROM host where host_activate = '1' and host_register = '1' ORDER BY host_name");
	if (PEAR::isError($res))
		print "Mysql Error : ".$res->getMessage();
	while ($res->fetchInto($h))
		if ($oreon->user->admin || !$isRestreint || ($isRestreint && isset($lcaHostByName["LcaHost"][$h["host_name"]])))
			$host[$h["host_name"]] = $h["host_name"];	
			
	$selHost =& $formHost->addElement('select', 'host', $lang["h"], $host, array("onChange" =>"this.form.submit();"));

	if (isset($_POST["host"])){
		$formHost->setDefaults(array('host' => $_POST["host"]));
	}else if (isset($_GET["host"])){
		$formHost->setDefaults(array('host' => $_GET["host"]));
	}
	
	#
	## fourchette de temps
	#
	$period = array();
	$period["today"] = "Today";
//	$period["last24hours"] = "Last 24 Hours";
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
	
	$formPeriod1->addElement('header', 'title', $lang["m_predefinedPeriod"]);
	$selHost =& $formPeriod1->addElement('select', 'period', $lang["m_predefinedPeriod"], $period, array("onChange" =>"this.form.submit();"));	


	$formPeriod2 = new HTML_QuickForm('FormPeriod2', 'post', "?p=".$p);
	isset($mhost) ? $formPeriod2->addElement('hidden', 'host', $mhost) : NULL;
	$formPeriod2->addElement('header', 'title', $lang["m_customizedPeriod"]);
	$formPeriod2->addElement('text', 'start', $lang["m_start"]);
	$formPeriod2->addElement('button', "startD", $lang['modify'], array("onclick"=>"displayDatePicker('start')"));
	$formPeriod2->addElement('text', 'end', $lang["m_end"]);
	$formPeriod2->addElement('button', "endD", $lang['modify'], array("onclick"=>"displayDatePicker('end')"));

	$sub =& $formPeriod2->addElement('submit', 'submit', $lang["m_view"]);
	$res =& $formPeriod2->addElement('reset', 'reset', $lang["reset"]);

if($mhost)
{

// parser que pour l'host demandé
function parseFile($file,$end_time, $mhost){
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

				if($res1[0] == $mhost)
					$tab_log[$a++] = getLogData($time_event, $res1[0], "", $res1[1], $res1[4], $type);
				
			}
			if (!strncmp($type, "CURRENT SERVICE STATE", 21) || !strncmp($type, "INITIAL SERVICE STATE", 21))
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
			if (!strncmp($type, "HOST ALERT", 10) )
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

				if($res1[0] == $mhost)
					$tab_log[$a++] = getLogData($time_event, $res1[0], "", $res1[1], $res1[4], $type);

				$tablist[$res1[0]]["current_state"] = $res1[1];
				$tablist[$res1[0]]["current_time"] = $time_event; //save time
			}
			#
			## services associed
			#
			else if (!strncmp($type, "SERVICE ALERT", 13))
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
						$tab_tmp["timeNONE"] += ($time_event-$tab_tmp["current_time"]);
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

if($start_date_day < ($end_date_select))
{
	$tmp = $oreon->Nagioscfg["log_file"];
	$tab = parseFile($tmp,time(), $mhost);
	$tab_log = $tab["tab_log"];
	foreach($tab as $host => $htab)
	{
		if ($host == $mhost)
		{
			#
			## last host alert
			#
			if(!strncmp($htab["current_state"], "UP", 2))
				$htab["timeUP"] += ($end_date_select-$htab["current_time"]);
			elseif(!strncmp($htab["current_state"], "DOWN", 4))
				$htab["timeDOWN"] += ($end_date_select-$htab["current_time"]);
			elseif(!strncmp($htab["current_state"], "UNREACHABLE", 11))
				$htab["timeUNREACHABLE"] += ($end_date_select-$htab["current_time"]);
			else
				$htab["timeNONE"] += ($end_date_select-$htab["current_time"]);
				
			#
			## add log day
			#		
			$Tup += $htab["timeUP"];
			$Tdown += $htab["timeDOWN"];
			$Tunreach += $htab["timeUNREACHABLE"];
			$Tnone += (($end_date_select - $start_date_select) - ($Tup + $Tdown + $Tunreach));
			$tab_svc =array();
			$i = 0;			
			while (list($key, $value) = each($htab["tab_svc_log"])) {
				$tab_tmp = $value;
				$tab_tmp["svcName"] = $key;
				if(!strncmp($tab_tmp["current_state"], "OK", 2))
					$tab_tmp["timeOK"] += (time()-$tab_tmp["current_time"]);
				elseif(!strncmp($tab_tmp["current_state"], "WARNING", 7))
					$tab_tmp["timeWARNING"] += (time()-$tab_tmp["current_time"]);
				elseif(!strncmp($tab_tmp["current_state"], "UNKNOWN", 7))
					$tab_tmp["timeUNKNOWN"] += (time()-$tab_tmp["current_time"]);
				elseif(!strncmp($tab_tmp["current_state"], "CRITICAL", 8))
					$tab_tmp["timeCRITICAL"] += (time()-$tab_tmp["current_time"]);
				else
					$tab_tmp["timeNONE"] += (time()-$tab_tmp["current_time"]);	
						
				$tt = $end_date_select - $start_date_select;
				$svc_id = $tab_tmp["service_id"];				
				$archive_svc_ok =  isset($tab_svc_bdd[$svc_id]["Tok"]) ? $tab_svc_bdd[$svc_id]["Tok"] : 0;
				$archive_svc_warn = isset($tab_svc_bdd[$svc_id]["Twarn"]) ? $tab_svc_bdd[$svc_id]["Twarn"] : 0;
				$archive_svc_unknown = isset($tab_svc_bdd[$svc_id]["Tunknown"]) ? $tab_svc_bdd[$svc_id]["Tunknown"] : 0;
				$archive_svc_cri = isset($tab_svc_bdd[$svc_id]["Tcri"]) ? $tab_svc_bdd[$svc_id]["Tcri"] : 0;
				$tab_tmp["PtimeOK"] = round(($archive_svc_ok +$tab_tmp["timeOK"]) / $tt *100,3);
				$tab_tmp["PtimeWARNING"] = round(($archive_svc_warn+$tab_tmp["timeWARNING"]) / $tt *100,3);
				$tab_tmp["PtimeUNKNOWN"] = round(($archive_svc_unknown+$tab_tmp["timeUNKNOWN"]) / $tt *100,3);
				$tab_tmp["PtimeCRITICAL"] = round(($archive_svc_cri+$tab_tmp["timeCRITICAL"]) / $tt *100,3);
				$tab_tmp["PtimeNONE"] = round( ( $tt - (($archive_svc_ok+$tab_tmp["timeOK"]) 
													 + ($archive_svc_warn+$tab_tmp["timeWARNING"])
													 + ($archive_svc_unknown+$tab_tmp["timeUNKNOWN"])
													 + ($archive_svc_cri+$tab_tmp["timeCRITICAL"])))  / $tt *100,3);				
				$tab_svc[$i++] = $tab_tmp;
			}
		}
	}
}
else // today is not in the period
{
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
}
#
## calculate host %
#
$tab_resume = array();
$tab = array();
$timeTOTAL = $end_date_select - $start_date_select;
$Tnone = $timeTOTAL - ($Tup + $Tdown + $Tunreach);
if($Tnone < 0)
$Tnone = 0;


$tab["state"] = $lang["m_UpTitle"];
$tab["time"] = Duration::toString($Tup);
$tab["pourcentTime"] = round($Tup/$timeTOTAL*100,2);
$tab["pourcentkTime"] = round($Tup/$timeTOTAL*100,2);
$tab["style"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_up"]."'";
$tab_resume[0] = $tab;

$tab["state"] = $lang["m_DownTitle"];
$tab["time"] = Duration::toString($Tdown);
$tab["pourcentTime"] = round($Tdown/$timeTOTAL*100,2);
$tab["pourcentkTime"] = round($Tdown/$timeTOTAL*100,2);
$tab["style"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_down"]."'";
$tab_resume[1] = $tab;

$tab["state"] = $lang["m_UnreachableTitle"];
$tab["time"] = Duration::toString($Tunreach);
$tab["pourcentTime"] = round($Tunreach/$timeTOTAL*100,2);
$tab["pourcentkTime"] = round($Tunreach/$timeTOTAL*100,2);
$tab["style"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_unreachable"]."'";
$tab_resume[2] = $tab;

$tab["state"] = $lang["m_PendingTitle"];
$tab["time"] = Duration::toString($Tnone);
$tab["pourcentTime"] = round($Tnone/$timeTOTAL*100,2);
$tab["pourcentkTime"] = round($Tnone/$timeTOTAL*100,2);
$tab["style"] = "class='ListColCenter' style='background:" . $oreon->optGen["color_unknown"]."'";
$tab_resume[3] = $tab;


	$start_date_select = date("d/m/Y G:i:s", $start_date_select);
	$end_date_select =  date("d/m/Y G:i:s", $end_date_select);

	$tpl->assign('host_name', $mhost);		
	$status = "";
	foreach ($tab_resume  as $tb)
		if($tb["pourcentTime"] > 0)
			$status .= "&value[".$tb["state"]."]=".$tb["pourcentTime"];  
        
	$tpl->assign('status', $status);		
	$tpl->assign("tab_resume", $tab_resume);
	if(isset($tab_svc))
	$tpl->assign("tab_svc", $tab_svc);		
	$tpl->assign("tab_log", $tab_log);
	$tpl->assign('infosTitle', $start_date_select." => ".$end_date_select);	
}## end of period requirement

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
			
	$tpl->assign('periodTitle', $lang["m_selectPeriodTitle"]);
	$tpl->assign('resumeTitle', $lang["m_hostResumeTitle"]);
	$tpl->assign('logTitle', $lang["m_hostLogTitle"]);
	$tpl->assign('svcTitle', $lang["m_hostSvcAssocied"]);


	$renderer1 = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$formPeriod1->accept($renderer1);
	$tpl->assign('formPeriod1', $renderer1->toArray());	
	$renderer2 = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$formPeriod2->accept($renderer2);	
	$tpl->assign('formPeriod2', $renderer2->toArray());


	#Apply a template definition
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$formHost->accept($renderer);
	$tpl->assign('formHost', $renderer->toArray());


	$tpl->assign('lang', $lang);
	$tpl->assign("p", $p);
	$tpl->display("viewHostLog.ihtml");


	$rq = 'SELECT ' .
	' * FROM `log_archive_host` WHERE host_id = ' . $host_id .
	' AND date_start >=  ' . ($sd-1) .
	' AND date_end <= ' . $ed .
	' order by date_start desc';

/*
	$res = & $pearDB->query($rq);

			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
	} else 
	{
	  while ($h =& $res->fetchRow())
	  {

		echo date("d/m/Y G:i:s", $h["date_start"]);
		echo " -> ";
		echo date("d/m/Y G:i:s", $h["date_end"]);
		echo "<br>"; 
	
	  }
	}

*/

	$time_startR2 = microtime_float();
	$time_R = $time_startR2 - $time_startR;
	//print $time_R;
	
?>