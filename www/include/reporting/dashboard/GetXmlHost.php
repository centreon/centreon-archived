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
	//	' AND date_start >=  ' . ($sd-1) .
	//	' AND date_end <= ' . $ed .
		' order by date_start desc';
			
		$res = & $pearDB->query($rq);
	

		  while ($h =& $res->fetchRow()) {
			$uptime = $h["UPTimeScheduled"];
			$downtime = $h["DOWNTimeScheduled"];
			$unreachalbetime = $h["UNREACHABLETimeScheduled"];
			$undeterminatetime = $h["UNDETERMINATETimeScheduled"];
	
			$tt = 86400 ;
			if(($uptime + $downtime + $unreachalbetime + $undeterminatetime) < $tt)
				$pending = 	$tt - ($uptime + $downtime + $unreachalbetime + $undeterminatetime);
			else
			$pending = 0;

			if($uptime > 0)			
			$pup = 0 +round(($uptime / $tt * 100),2);
			else
			$pup = "0.00";
			if($downtime > 0)
			$pdown = 0 +round(($downtime / $tt * 100),2);
			else
			$pdown = "0.00";
			if($unreachalbetime > 0)
			$punreach = 0 +round(($unreachalbetime / $tt * 100),2);
			else
			$punreach = "0.00";
			if($pending > 0)
			$pundet = 0 +round(($pending / $tt * 100),2);
			else
			$pundet = "0.00";

			$sortTab = array();
			$ntab = array();
			$sortTab["#" . $colorUP] = $pup;
			$sortTab["#" . $colorDOWN] = $pdown;
			$sortTab["#" . $colorUNREACHABLE] = $punreach;
			$sortTab["#" . $colorUNKNOWN] = $pundet;			

			$t = 0 + ($h["date_end"] - $h["date_start"]);
			if($t > 20000)
			$t = $t - 10000;
			
			if($punreach > 0){
				$t1 = round(($t - ($punreach * $t / 100) ),2);
				$start = $h["date_start"] + 5000;
				$end = $h["date_end"] - $t1 -5000;
				$buffer .= '<event ';
				$buffer .= ' start="' .date("m d Y G:i:s", $start) . ' GMT"';
				$buffer .= ' end="' . date("m d Y G:i:s", $end). ' GMT"';
				$buffer .= ' color="#' . $colorUNREACHABLE . '"';
				$buffer .= ' isDuration="true" ';
				$buffer .= ' title= "' . $punreach . '%" >' ;
				$buffer .= '</event>';		
			}
			else{
				$t1 = round(($t - ($punreach * $t / 100) ),2);
				$start = $h["date_start"] + 5000;
				$end = $h["date_start"] + 5001;
				$buffer .= '<event ';
				$buffer .= ' start="' .date("m d Y G:i:s", $start) . ' GMT"';
				$buffer .= ' end="' . date("m d Y G:i:s", $end). ' GMT"';
				$buffer .= ' color="#' . $colorUNREACHABLE . '"';
				$buffer .= ' isDuration="true" ';
				$buffer .= ' title= "" >' ;
				$buffer .= '</event>';		
			}



			if($pundet > 0){
				
				$t1 = round(($t - ($pundet * $t / 100) ),2);
				$start = $h["date_start"] + 5000;
				$end = $h["date_end"] - $t1 -5000;
				$buffer .= '<event ';
				$buffer .= ' start="' .date("m d Y G:i:s", $start) . ' GMT"';
				$buffer .= ' end="' . date("m d Y G:i:s", $end). ' GMT"';
				$buffer .= ' color="#' . $colorUNKNOWN . '"';
				$buffer .= ' isDuration="true" ';
				$buffer .= ' title= "' . $pundet . '%" >' ;
				$buffer .= '</event>';		
			}
			else{				
				$t1 = round(($t - ($pundet * $t / 100) ),2);
				$start = $h["date_start"] + 5000;
				$end = $h["date_start"] + 5001;
				$buffer .= '<event ';
				$buffer .= ' start="' .date("m d Y G:i:s", $start) . ' GMT"';
				$buffer .= ' end="' . date("m d Y G:i:s", $end). ' GMT"';
				$buffer .= ' color="#' . $colorUNKNOWN . '"';
				$buffer .= ' isDuration="true" ';
				$buffer .= ' title= "" >' ;
				$buffer .= '</event>';		
			}


			if($pdown > 0){
				$t1 = round(($t - ($pdown * $t / 100) ),2);
				$start = $h["date_start"] + 5000;
				$end = $h["date_end"] - $t1 -5000;
				$buffer .= '<event ';
				$buffer .= ' start="' .date("m d Y G:i:s", $start) . ' GMT"';
				$buffer .= ' end="' . date("m d Y G:i:s", $end). ' GMT"';
				$buffer .= ' color="#' . $colorDOWN . '"';
				$buffer .= ' isDuration="true" ';
				$buffer .= ' title= "' . $pdown . '%" >' ;
				$buffer .= '</event>';		
			}
			else{
				$t1 = round(($t - ($pdown * $t / 100) ),2);
				$start = $h["date_start"] + 5000;
				$end = $h["date_start"] + 5001;
				$buffer .= '<event ';
				$buffer .= ' start="' .date("m d Y G:i:s", $start) . ' GMT"';
				$buffer .= ' end="' . date("m d Y G:i:s", $end). ' GMT"';
				$buffer .= ' color="#' . $colorDOWN . '"';
				$buffer .= ' isDuration="true" ';
				$buffer .= ' title= "" >' ;
				$buffer .= '</event>';
			}

			if($pup > 0){
				$t1 = round(($t - ($pup * $t / 100) ),2);
				$start = $h["date_start"] + 5000;
				$end = $h["date_end"] - $t1 -5000;
				$buffer .= '<event ';
				$buffer .= ' start="' .date("m d Y G:i:s", $start) . ' GMT"';
				$buffer .= ' end="' . date("m d Y G:i:s", $end). ' GMT"';
				$buffer .= ' color="#' . $colorUP . '"';
				$buffer .= ' isDuration="true" ';
				$buffer .= ' title= "' . $pup . '%" >' ;
				$buffer .= '</event>';
			}
			else{
				$t1 = round(($t - ($pup * $t / 100) ),2);
				$start = $h["date_start"] + 5000;
				$end = $h["date_start"] + 5001;
				$buffer .= '<event ';
				$buffer .= ' start="' .date("m d Y G:i:s", $start) . ' GMT"';
				$buffer .= ' end="' . date("m d Y G:i:s", $end). ' GMT"';
				$buffer .= ' color="#' . $colorUP . '"';
				$buffer .= ' isDuration="true" ';
				$buffer .= ' title= "" >' ;
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
	if($_GET["today_pending"] > 0)
	$end = $today_start + $tp + 5000;
	else
	$end = $today_start + 5001;
	$buffer .= '<event ';
	$buffer .= ' start="' .date("m d Y G:i:s", $start) . ' GMT"';
	$buffer .= ' end="' . date("m d Y G:i:s", $end). ' GMT"';
	$buffer .= ' color="#' . $colorUNREACHABLE . '"';
	$buffer .= ' isDuration="true" ';
	$buffer .= ' title= "' . $_GET["today_pending"] . '%" >' ;
	$buffer .= '</event>';

	$tp = round(($_GET["today_unreachable"] * $t / 100 ),2);
	if($_GET["today_unreachable"] > 0)
	$end = $today_start + $tp + 5000;
	else
	$end = $today_start + 5001;
	$buffer .= '<event ';
	$buffer .= ' start="' .date("m d Y G:i:s", $start) . ' GMT"';
	$buffer .= ' end="' . date("m d Y G:i:s", $end). ' GMT"';
	$buffer .= ' color="#' . $colorUNKNOWN . '"';
	$buffer .= ' isDuration="true" ';
	$buffer .= ' title= "' . $_GET["today_unreachable"] . '%" >' ;
	$buffer .= '</event>';



	$tp = round(($_GET["today_down"] * $t / 100 ),2);
	if($_GET["today_down"] > 0)
	$end = $today_start + $tp + 5000;
	else
	$end = $today_start + 5001;
	$buffer .= '<event ';
	$buffer .= ' start="' .date("m d Y G:i:s", $start) . ' GMT"';
	$buffer .= ' end="' . date("m d Y G:i:s", $end). ' GMT"';
	$buffer .= ' color="#' . $colorDOWN . '"';
	$buffer .= ' isDuration="true" ';
	$buffer .= ' title= "' . $_GET["today_down"] . '%" >' ;
	$buffer .= '</event>';


	$tp = round(($_GET["today_up"] * $t / 100 ),2);
	if($_GET["today_up"] > 0)
	$end = $today_start + $tp + 5000;
	else
	$end = $today_start + 5001;
	$buffer .= '<event ';
	$buffer .= ' start="' .date("m d Y G:i:s", $start) . ' GMT"';
	$buffer .= ' end="' . date("m d Y G:i:s", $end). ' GMT"';
	$buffer .= ' color="#' . $colorUP . '"';
	$buffer .= ' isDuration="true" ';
	$buffer .= ' title= "' . $_GET["today_up"] . '%" >' ;
	$buffer .= '</event>';

	}
	else
	{
		$buffer .= '<error>error</error>';
	}

	$buffer .= '</data>';
	header('Content-Type: text/xml');
	echo $buffer;
?>
