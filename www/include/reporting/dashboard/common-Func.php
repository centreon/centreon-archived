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


	function my_getTimeTamps($dateSTR)	{
		list($m,$d,$y) = split('/',$dateSTR);
		return (mktime(0,0,0,$m,$d,$y));
	}
	
	function my_getStartDay($date){
		$d = date("d",$date);
		$y = date("Y",$date);
		$m = date("m",$date);
		return (mktime(0,0,0,$m,$d,$y));
	}
	
	function my_getEndDay($date){
		$d = date("d",$date);
		$y = date("Y",$date);
		$m = date("m",$date);
		return (mktime(0,0,0,$m,$d+1,$y));
	}
	
	function my_getNextStartDay($date){
		$d = date("d",$date);
		$y = date("Y",$date);
		$m = date("m",$date);
		return (mktime(0,0,0,$m,$d+1,$y));
	}
	
	function my_getNextEndDay($date){
		$d = date("d",$date);
		$y = date("Y",$date);
		$m = date("m",$date);
		return (mktime(0,0,0,$m,$d+1,$y));
	}
	
	function trim_value(&$value){
	   $value = trim($value);
	}
	
	function getLogData($time_event, $host, $service, $status, $output, $type){
		$tab_img = array("UP" => './img/icones/12x12/recovery.gif',
						"DOWN" => './img/icones/12x12/alert.gif',
						"UNREACHABLE" => './img/icones/12x12/queue.gif',
						"NONE" => './img/icones/12x12/info.gif',
						);

		$tab["time"] = date(_("Y/m/d G:i"), $time_event);
		$tab["timeb"] = $time_event;
		$tab["host"] = $host;
		$tab["service"] = $service;
		$tab["status"] = $status;
		$tab["output"] = $output;
		$tab["type"] = $type;
		$tab["img"] = $tab_img[$status];
		return $tab ;
	}
	
	function getPeriodList(){
	
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
	
	return $periodList;
	}
	
	function create_date_timeline_format($time_unix){
		$tab_month = array(
		"01" => "Jan",
		"02" => "Feb",
		"03"=> "Mar",
		"04"=> "Apr",
		"05" => "May",
		"06"=> "Jun",
		"07"=> "Jul",
		"08"=> "Aug",
		"09"=> "Sep",
		"10"=> "Oct",
		"11"=> "Nov",
		"12"=> "Dec");
		$date = $tab_month[date('m', $time_unix)].date(" d Y G:i:s", $time_unix);
		return $date;
	}

	function formatData($state, $time, $timeTOTAL, $time_none, $nb_alert, $color){
		$tab = array();
		$tab["state"] = _($state);
		$tab["time"] = Duration::toString($time);
		$tab["timestamp"] = $time;
		$tab["pourcentTime"] = round($time/($timeTOTAL+1)*100,2) ;
		if ($state != "Undetermined")
			$tab["pourcentkTime"] = round($time/($timeTOTAL-$time_none+1)*100,2). "%";
		else
			$tab["pourcentkTime"] = NULL;
		$tab["nbAlert"] = $nb_alert;
		$tab["style"] = "class='ListColCenter' style='background:" . $color."'";	
		return $tab;
	}
?>