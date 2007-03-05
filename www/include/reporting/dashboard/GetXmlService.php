<?
/**
Oreon is developped with Apache Licence 2.0 :
http://www.apache.org/licenses/LICENSE-2.0.txt
Developped by : Cedrick Facon

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

	#
	## pearDB init
	#
	require_once 'DB.php';
	
	$buffer = null;
	$buffer  = '<?xml version="1.0"?>';
	$buffer .= '<data>';


	if(isset($_GET["oreonPath"]) && isset($_GET["hostID"]) && isset($_GET["serviceID"]) &&
	   isset($_GET["color"]) && isset($_GET["today_ok"])&& isset($_GET["today_critical"])&& 
	   isset($_GET["today_unknown"])&& isset($_GET["today_pending"]))
	{
		list($colorOK, $colorWARNING, $colorCRITICAL, $colorPENDING, $colorUNKNOWN)= split (":", $_GET["color"], 5);

		$oreonPath = $_GET["oreonPath"];
		include_once($oreonPath . "/www/oreon.conf.php");
		$dsn = array(
			     'phptype'  => 'mysql',
			     'username' => $conf_oreon['user'],
			     'password' => $conf_oreon['password'],
			     'hostspec' => $conf_oreon['host'],
			     'database' => $conf_oreon['db'],
			     );
		$options = array(
				 'debug'       => 2,
				 'portability' => DB_PORTABILITY_ALL ^ DB_PORTABILITY_LOWERCASE,
				 );
			
		$pearDB =& DB::connect($dsn, $options);
		if (PEAR::isError($pearDB)) 
		  die("Connecting probems with oreon database : " . $pearDB->getMessage());		
		$pearDB->setFetchMode(DB_FETCHMODE_ASSOC);


		function create_date_timeline_format($time_unix)
		{
//			date("m d Y G:i:s", $start)
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
			"12"=> "Dec"
			);
			$date = date('m', $time_unix);
			$date = $tab_month[$date] .  date(" d Y G:i:s", $time_unix);
			return $date;
		}

		#
		## class init
		#			
		class Duration
		{
			function toString ($duration, $periods = null)
		    {
		        if (!is_array($duration)) {
		            $duration = Duration::int2array($duration, $periods);
		        }
		        return Duration::array2string($duration);
		    }
		 
		    function int2array ($seconds, $periods = null)
		    {        
		        // Define time periods
		        if (!is_array($periods)) {
		            $periods = array (
		                    'y'	=> 31556926,
		                    'M' => 2629743,
		                    'w' => 604800,
		                    'd' => 86400,
		                    'h' => 3600,
		                    'm' => 60,
		                    's' => 1
		                    );
		        }
		 
		        // Loop
		        $seconds = (int) $seconds;
		        foreach ($periods as $period => $value) {
		            $count = floor($seconds / $value);
		 
		            if ($count == 0) {
		                continue;
		            }
		 
		            $values[$period] = $count;
		            $seconds = $seconds % $value;
		        }
		 
		        // Return
		        if (empty($values)) {
		            $values = null;
		        }
		        return $values;
		    }
		 
		    function array2string ($duration)
		    {
		        if (!is_array($duration)) {
		            return false;
		        }
		        foreach ($duration as $key => $value) {
		            $segment = $value . '' . $key;
		            $array[] = $segment;
		        }
		        $str = implode(' ', $array);
		        return $str;
		    }
		}

			
		$rq = 'SELECT ' .
		' * FROM `log_archive_service` WHERE host_id = ' . $_GET["hostID"] . ' AND service_id = ' . $_GET["serviceID"] .
		' order by date_start desc';
			
		$res = & $pearDB->query($rq);
	

		  while ($h =& $res->fetchRow()) {
			$oktime = $h["OKTimeScheduled"];
			$criticaltime = $h["CRITICALTimeScheduled"];
			$warningtime = $h["WARNINGTimeScheduled"];
			$unknowntime = $h["UNKNOWNTimeScheduled"];
	
			$tt = 0 + ($h["date_end"] - $h["date_start"]);
			if(($oktime + $criticaltime + $warningtime + $unknowntime) < $tt)
				$pendingtime = 	$tt - ($oktime + $criticaltime + $warningtime + $unknowntime);
			else
			$pendingtime = 0;


			if($oktime > 0)
			$pok = 0 +round(($oktime / $tt * 100),2);
			else
			$pok = "0.00";
						
			if($criticaltime > 0)
			$pcritical = 0 +round(($criticaltime / $tt * 100),2);
			else
			$pcritical = "0.00";

			if($warningtime > 0)
			$pwarning = 0 +round(($warningtime / $tt * 100),2);
			else
			$pwarning = "0.00";

			if($unknowntime > 0)
			$punknown = 0 +round(($unknowntime / $tt * 100),2);
			else
			$punknown = "0.00";

			if($pendingtime > 0)
			$ppending = 0 +round(($pendingtime / $tt * 100),2);
			else
			$ppending = "0.00";

			$sortTab = array();
			$ntab = array();
			$sortTab["#" . $colorOK] = $pok;
			$sortTab["#" . $colorCRITICAL] = $pcritical;
			$sortTab["#" . $colorWARNING] = $pwarning;
			$sortTab["#" . $colorUNKNOWN] = $punknown;
			$sortTab["#" . $colorPENDING] = $ppending;


			$t = 0 + ($h["date_end"] - $h["date_start"]);
			
			$t = round(($t - ($t * 0.11574074074)),2);
			$start = $h["date_start"] + 5000;			
			

			$tp = round(($pwarning * $t / 100 ),2);
			if($pwarning > 0){
				$end = $h["date_start"] + $tp + 5000;
			$buffer .= '<event ';
			$buffer .= ' start="' .create_date_timeline_format($start) . ' GMT"';
			$buffer .= ' end="' . create_date_timeline_format($end). ' GMT"';
			$buffer .= ' color="#' . $colorWARNING . '"';
			$buffer .= ' isDuration="true" ';
			$buffer .= ' title= "' . (($pwarning > 0) ? $pwarning : "0") . '%" >' ;
			$buffer .= ' Duration: ' . Duration::toString($tt);
			$buffer .= '~br~ WarningTime: ' . Duration::toString($warningtime);		
			$buffer .= '</event>';		
			}
			$tp = round(($punknown * $t / 100 ),2);
			if($punknown > 0){
				$end = $h["date_start"] + $tp + 5000;
				$buffer .= '<event ';
				$buffer .= ' start="' .create_date_timeline_format($start) . ' GMT"';
				$buffer .= ' end="' . create_date_timeline_format($end). ' GMT"';
				$buffer .= ' color="#' . $colorUNKNOWN . '"';
				$buffer .= ' isDuration="true" ';
				$buffer .= ' title= "' . (($punknown > 0) ? $punknown : "0") . '%" >' ;
				$buffer .= ' Duration: ' . Duration::toString($tt);
				$buffer .= '~br~ UnknownTime: ' . Duration::toString($unknowntime);		
				$buffer .= '</event>';	
			}

			$tp = round(($ppending * $t / 100 ),2);
			if($ppending > 0){
				$end = $h["date_start"] + 5001;
				$buffer .= '<event ';
				$buffer .= ' start="' .create_date_timeline_format($start) . ' GMT"';
				$buffer .= ' end="' . create_date_timeline_format($end). ' GMT"';
				$buffer .= ' color="#' . $colorPENDING . '"';
				$buffer .= ' isDuration="true" ';
				$buffer .= ' title= "' . (($ppending > 0) ? $ppending : "0") . '%" >' ;
				$buffer .= ' Duration: ' . Duration::toString($tt);
				$buffer .= '~br~ PendingTime: ' . Duration::toString($pendingtime);		
				$buffer .= '</event>';	
			}

			$tp = round(($pcritical * $t / 100 ),2);
			if($pcritical > 0){
				$end = $h["date_start"] + $tp + 5000;
				$buffer .= '<event ';
				$buffer .= ' start="' .create_date_timeline_format($start) . ' GMT"';
				$buffer .= ' end="' . create_date_timeline_format($end). ' GMT"';
				$buffer .= ' color="#' . $colorCRITICAL . '"';
				$buffer .= ' isDuration="true" ';
				$buffer .= ' title= "' . (($pcritical > 0) ? $pcritical : "0") . '%" >' ;
				$buffer .= ' Duration: ' . Duration::toString($tt);
				$buffer .= '~br~ Critical: ' . Duration::toString($criticaltime);
				$buffer .= '</event>';
			}

			$tp = round(($pok * $t / 100 ),2);
			if($pok > 0){
				$end = $h["date_start"] + $tp + 5000;
				$buffer .= '<event ';
				$buffer .= ' start="' .create_date_timeline_format($start) . ' GMT"';
				$buffer .= ' end="' . create_date_timeline_format($end). ' GMT"';
				$buffer .= ' color="#' . $colorOK . '"';
				$buffer .= ' isDuration="true" ';
				$buffer .= ' title= "' . (($pok > 0) ? $pok : "0") .   '%" >' ;
				$buffer .= ' Duration: ' . Duration::toString($tt);
				$buffer .= '~br~ Oktime: ' . Duration::toString($oktime);		
				$buffer .= '~br~ tt: ' . $tt;		
				$buffer .= '~br~ time: ' . $pendingtime;		
				$buffer .= '~br~ time: ' . $unknowntime;
				$buffer .= '~br~ time: ' . $warningtime;
				$buffer .= '~br~ time: ' . $criticaltime;
				$buffer .= '</event>';
			}
		  }

#
## Today purcent if period 
#
/*
	$day = date("d",time());
	$year = date("Y",time());
	$month = date("m",time());
	$today_start = mktime(0, 0, 0, $month, $day, $year);
	$today_end = time();

	$t = 0 + ($today_end - $today_start);
	$t = round(($t - ($t * 0.11574074074)),2);
	$start = $today_start + 5000;

	$tp = round(($_GET["today_unreachable"] * $t / 100 ),2);
	if($_GET["today_unreachable"] > 0)
	$end = $today_start + $tp + 5000;
	else
	$end = $today_start + 5001;
	$buffer .= '<event ';
	$buffer .= ' start="' .create_date_timeline_format($start) . ' GMT"';
	$buffer .= ' end="' . create_date_timeline_format($end). ' GMT"';
	$buffer .= ' color="#' . $colorWARNING . '"';
	$buffer .= ' isDuration="true" ';
	$buffer .= ' title= "' . $_GET["today_unreachable"] . '%" >' ;
	$buffer .= ' Duration: ' . Duration::toString($tt);
	$buffer .= '~br~ UnReachableTime: ' . Duration::toString(0+$_GET["today_unreachable"] * $tt / 100);		
	$buffer .= '</event>';

	$tp = round(($_GET["today_pending"] * $t / 100 ),2);
	if($_GET["today_pending"] > 0)
	$end = $today_start + $tp + 5000;
	else
	$end = $today_start + 5001;
	$buffer .= '<event ';
	$buffer .= ' start="' .create_date_timeline_format($start) . ' GMT"';
	$buffer .= ' end="' . create_date_timeline_format($end). ' GMT"';
	$buffer .= ' color="#' . $colorUNKNOWN . '"';
	$buffer .= ' isDuration="true" ';
	$buffer .= ' title= "' . $_GET["today_pending"] . '%" >' ;
	$buffer .= ' Duration: ' . Duration::toString($tt);
	$buffer .= '~br~ PendingTime: ' . Duration::toString($_GET["today_pending"] * $tt / 100);		
	$buffer .= '</event>';



	$tp = round(($_GET["today_down"] * $t / 100 ),2);
	if($_GET["today_down"] > 0)
	$end = $today_start + $tp + 5000;
	else
	$end = $today_start + 5001;
	$buffer .= '<event ';
	$buffer .= ' start="' .create_date_timeline_format($start) . ' GMT"';
	$buffer .= ' end="' . create_date_timeline_format($end). ' GMT"';
	$buffer .= ' color="#' . $colorCRITICAL . '"';
	$buffer .= ' isDuration="true" ';
	$buffer .= ' title= "' . $_GET["today_down"] . '%" >' ;
	$buffer .= ' Duration: ' . Duration::toString($tt);
	$buffer .= '~br~ Downtime: ' . Duration::toString($_GET["today_down"] * $tt / 100);
	$buffer .= '</event>';


	$tp = round(($_GET["today_up"] * $t / 100 ),2);
	if($_GET["today_up"] > 0)
	$end = $today_start + $tp + 5000;
	else
	$end = $today_start + 5001;
	$buffer .= '<event ';
	$buffer .= ' start="' .create_date_timeline_format($start) . ' GMT"';
	$buffer .= ' end="' . create_date_timeline_format($end). ' GMT"';
	$buffer .= ' color="#' . $colorOK . '"';
	$buffer .= ' isDuration="true" ';
	$buffer .= ' title= "' . $_GET["today_up"] . '%" >' ;
	$buffer .= ' Duration: ' . Duration::toString($tt);
	$buffer .= '~br~ Uptime: ' . Duration::toString($_GET["today_up"] * $tt / 100);	
	$buffer .= '</event>';
*/
		  
	}
	else
	{
		$buffer .= '<error>error</error>';
	}

	$buffer .= '</data>';

/*
$buffer =
'
<data>
<event start="Jan 28 2007 1:23:19 GMT" end="Jan 28 2007 22:19:19 GMT" color="#19EE11" isDuration="true" title="98.64%">
 Duration: 1d ~br~ Uptime: 23h 40m 25s</event>
</data>';
*/

	header('Content-Type: text/xml');
	echo $buffer;
?>
