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


	if(isset($_GET["oreonPath"]) && isset($_GET["hostID"]) && isset($_GET["color"]) && isset($_GET["today_up"])&& isset($_GET["today_down"])&& isset($_GET["today_unreachable"])&& isset($_GET["today_pending"]))
	{
		list($colorUP, $colorDOWN, $colorUNREACHABLE, $colorUNKNOWN)= split (":", $_GET["color"], 4);

		# use for debug only
		#$colorUP = "red";
		#$colorDOWN = "red"; 
		#$colorUNREACHABLE = "red"; 
		#$colorUNKNOWN = "red";

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
		' * FROM `log_archive_host` WHERE host_id = ' . $_GET["hostID"] .
		' order by date_start desc';
			
		$res = & $pearDB->query($rq);

		  while ($h =& $res->fetchRow()) {
			$uptime = $h["UPTimeScheduled"];
			$downtime = $h["DOWNTimeScheduled"];
			$unreachalbetime = $h["UNREACHABLETimeScheduled"];

			$tt = 0 + ($h["date_end"] - $h["date_start"]);
			if(($uptime + $downtime + $unreachalbetime) < $tt)
				$undeterminatetime = 0 + $tt - ($uptime + $downtime + $unreachalbetime);
			else
			$undeterminatetime = 0;


			if($unreachalbetime > 0)
			$punreach = 0 +round(($unreachalbetime / $tt * 100),2);
			else
			$punreach = "0.00";

			if($uptime > 0)
			$pup = 0 +round(($uptime / $tt * 100),2);
			else
			$pup = "0.00";
			
			if($downtime > 0)
			$pdown = 0 +round(($downtime / $tt * 100),2);
			else
			$pdown = "0.00";
			
			if($undeterminatetime > 0)
			$pundet = 0 +round(($undeterminatetime / $tt * 100),2);
			else
			$pundet = "0.00";

			$t = 0 + ($h["date_end"] - $h["date_start"]);			
			$t = round(($t - ($t * 0.11574074074)),2);
			$start = $h["date_start"] + 5000;			


			$tp = round(($pundet * $t / 100 ),2);
			if($pundet > 0){
				$end = $h["date_start"] + $tp + 5000;
				$buffer .= '<event ';
				$buffer .= ' start="' .create_date_timeline_format($start) . ' GMT"';
				$buffer .= ' end="' . create_date_timeline_format($end). ' GMT"';
				$buffer .= ' color="#' . $colorUNKNOWN . '"';
				$buffer .= ' isDuration="true" ';
				$buffer .= ' title= "' . (($pundet > 0) ? $pundet : "0") . '%" >' ;
				$buffer .= ' Duration: ' . Duration::toString($tt);
				$buffer .= '~br~ PendingTime: ' . $undeterminatetime;
				$buffer .= '</event>';		
			}

			$tp = round(($punreach * $t / 100 ),2);
			if($punreach > 0){
				$end = $h["date_start"] + $tp + 5000;
				$buffer .= '<event ';
				$buffer .= ' start="' .create_date_timeline_format($start) . ' GMT"';
				$buffer .= ' end="' . create_date_timeline_format($end). ' GMT"';
				$buffer .= ' color="#' . $colorUNREACHABLE . '"';
				$buffer .= ' isDuration="true" ';
				$buffer .= ' title= "' . (($punreach > 0) ? $punreach : "0") . '%" >' ;
				$buffer .= ' Duration: ' . Duration::toString($tt);
				$buffer .= '~br~ UnReachableTime: ' . $unreachalbetime;		
				$buffer .= '</event>';		
			}


			$tp = round(($pdown * $t / 100 ),2);
			if($pdown > 0){
				$end = $h["date_start"] + $tp + 5000;
				$buffer .= '<event ';
				$buffer .= ' start="' .create_date_timeline_format($start) . ' GMT"';
				$buffer .= ' end="' . create_date_timeline_format($end). ' GMT"';
				$buffer .= ' color="#' . $colorDOWN . '"';
				$buffer .= ' isDuration="true" ';
				$buffer .= ' title= "' . (($pdown > 0) ? $pdown : "0") . '%" >' ;
				$buffer .= ' Duration: ' . Duration::toString($tt);
				$buffer .= '~br~ Downtime: ' . $downtime;
				$buffer .= '</event>';
			}


			$tp = round(($pup * $t / 100 ),2);
			if($pup > 0){
				$end = $h["date_start"] + $tp + 5000;
				$buffer .= '<event ';
				$buffer .= ' start="' .create_date_timeline_format($start) . ' GMT"';
				$buffer .= ' end="' . create_date_timeline_format($end). ' GMT"';
				$buffer .= ' color="#' . $colorUP . '"';
				$buffer .= ' isDuration="true" ';
				$buffer .= ' title= "' . (($pup > 0) ? $pup : "0") .   '%" >' ;
				$buffer .= ' Duration: ' . Duration::toString($tt);
				$buffer .= '~br~ Uptime: ' . $uptime;		
				$buffer .= '</event>';	
			}
		  }

#
## Today purcent if period 
#
	$day = date("d",time());
	$year = date("Y",time());
	$month = date("m",time());
	$today_start = mktime(0, 0, 0, $month, $day, $year);
	$today_end = time();

	$t = 0 + ($today_end - $today_start);
	$t = round(($t - ($t * 0.11574074074)),2);
	$start = $today_start + 5000;

	$tp = round(($_GET["today_pending"] * $t / 100 ),2);
	if($_GET["today_pending"] > 0){
		$end = $today_start + $tp + 5000;
		$buffer .= '<event ';
		$buffer .= ' start="' .create_date_timeline_format($start) . ' GMT"';
		$buffer .= ' end="' . create_date_timeline_format($end). ' GMT"';
		$buffer .= ' color="#' . $colorUNKNOWN . '"';
		$buffer .= ' isDuration="true" ';
		$buffer .= ' title= "' . $_GET["today_pending"] . '%" >' ;
		$buffer .= ' Duration: ' . Duration::toString($tt);
		$buffer .= '~br~ PendingTime: ' . Duration::toString($_GET["today_pending"] * $tt / 100);		
		$buffer .= '</event>';
	}
	$tp = round(($_GET["today_unreachable"] * $t / 100 ),2);
	if($_GET["today_unreachable"] > 0){
		$end = $today_start + $tp + 5000;
		$buffer .= '<event ';
		$buffer .= ' start="' .create_date_timeline_format($start) . ' GMT"';
		$buffer .= ' end="' . create_date_timeline_format($end). ' GMT"';
		$buffer .= ' color="#' . $colorUNREACHABLE . '"';
		$buffer .= ' isDuration="true" ';
		$buffer .= ' title= "' . $_GET["today_unreachable"] . '%" >' ;
		$buffer .= ' Duration: ' . Duration::toString($tt);
		$buffer .= '~br~ UnReachableTime: ' . Duration::toString(0+$_GET["today_unreachable"] * $tt / 100);		
		$buffer .= '</event>';
	}
	$tp = round(($_GET["today_down"] * $t / 100 ),2);
	if($_GET["today_down"] > 0){
		$end = $today_start + $tp + 5000;
		$buffer .= '<event ';
		$buffer .= ' start="' .create_date_timeline_format($start) . ' GMT"';
		$buffer .= ' end="' . create_date_timeline_format($end). ' GMT"';
		$buffer .= ' color="#' . $colorDOWN . '"';
		$buffer .= ' isDuration="true" ';
		$buffer .= ' title= "' . $_GET["today_down"] . '%" >' ;
		$buffer .= ' Duration: ' . Duration::toString($tt);
		$buffer .= '~br~ Downtime: ' . Duration::toString($_GET["today_down"] * $tt / 100);
		$buffer .= '</event>';
	}

	$tp = round(($_GET["today_up"] * $t / 100 ),2);
	if($_GET["today_up"] > 0){
		$end = $today_start + $tp + 5000;
		$buffer .= '<event ';
		$buffer .= ' start="' .create_date_timeline_format($start) . ' GMT"';
		$buffer .= ' end="' . create_date_timeline_format($end). ' GMT"';
		$buffer .= ' color="#' . $colorUP . '"';
		$buffer .= ' isDuration="true" ';
		$buffer .= ' title= "' . $_GET["today_up"] . '%" >' ;
		$buffer .= ' Duration: ' . Duration::toString($tt);
		$buffer .= '~br~ Uptime: ' . Duration::toString($_GET["today_up"] * $tt / 100);	
		$buffer .= '</event>';
	}
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
