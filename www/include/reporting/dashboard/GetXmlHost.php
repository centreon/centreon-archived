<?php



#
## pearDB init
#
	require_once 'DB.php';	

	$buffer = null;
	$buffer  = '<?xml version="1.0"?>';
	$buffer .= '<data>';

//if(isset($_GET["oreonPath"]) && isset($_GET["hostID"]) && isset($_GET["color"]))
if((isset($_POST["oreonPath"]) && isset($_POST["hostID"]) && isset($_POST["color"])) || (isset($_GET["oreonPath"]) && isset($_GET["hostID"]) && isset($_GET["color"])))
{
	
	
	list($colorUP, $colorDOWN, $colorUNREACHABLE, $colorUNKNOWN)= split (":", $_GET["color"], 4);


//echo "->" . $_GET["oreonPath"];

	$oreonPath = "/srv/oreon/";

//	$oreonPath = $_POST["oreonPath"];

//echo 	"--" . $oreonPath . "--<br>";

	include_once($oreonPath . "www/oreon.conf.php");

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
	
//echo $rq;	
	
		$res = & $pearDB->query($rq);
	
		if (PEAR::isError($res)) {
					print "Mysql Error : ".$res->getMessage();
		} else 
		{
		  while ($h =& $res->fetchRow())
		  {
			$uptime = $h["UPTimeScheduled"];
			$downtime = $h["DOWNTimeScheduled"];
			$unreachalbetime = $h["UNREACHABLETimeScheduled"];
			$undeterminatetime = $h["UNDETERMINATETimeScheduled"];
	
			$tt = 	$uptime + $downtime + $unreachalbetime + $undeterminatetime;
	
			$pup = $uptime / $tt * 100;
			$pdown = $downtime / $tt * 100;
			$punreach = $unreachalbetime / $tt * 100;
			$pundet = $undeterminatetime / $tt * 100;


			$sortTab = array();
			$ntab = array();
			$sortTab["#" . $colorUP] = $pup;
			$sortTab["#" . $colorDOWN] = $pdown;
			$sortTab["#" . $colorUNREACHABLE] = $punreach;
			$sortTab["#" . $colorUNKNOWN] = $pundet;
			
			asort($sortTab);
			$sortTab = array_reverse($sortTab);
			array_pop($sortTab);
			array_pop($sortTab);
			array_pop($sortTab);
			$sortTab = array_keys($sortTab);

/*			
			$buffer .= '<event start="' . date("m d Y G:i:s", $h["date_start"]) . ' GMT" end="' .date("m d Y G:i:s", $h["date_end"]). ' GMT"   color="' . $sortTab[0] . '" isDuration="true">';
			$buffer .= 'up: ' . $pup . '%';
			$buffer .= ' down: ' . $pdown . '%';
			$buffer .= ' unreachable: ' . $punreach . '%';
			$buffer .= ' undeterminate: ' . $pundet . '%';	
			$buffer .= '</event>';
*/

//			$buffer .= '<event start="09 14 2006 23:00:00 GMT" end="09 14 2006 23:59:59 GMT"  color="' . $sortTab[0] . '" isDuration="true">';
			$buffer .= '<event start="' . date("m d Y G:i:s", $h["date_start"]) . ' GMT" end="' .date("m d Y G:i:s", $h["date_end"]). ' GMT"   color="' . $sortTab[0] . '" isDuration="true">';
			$buffer .= 'up: ' . $pup . '%';
			$buffer .= ' down: ' . $pdown . '%';
			$buffer .= ' unreachable: ' . $punreach . '%';
			$buffer .= ' undeterminate: ' . $pundet . '%';	
			$buffer .= '</event>';


//$buffer .= '<event start="09 14 2006 23:00:00 GMT" end="09 14 2006 23:59:59 GMT" color="red" isDuration="true">up: 100% down: 0% unreachable: 0% undeterminate: 0%</event>';


	
		  }
		}

	
}
else
{
	$buffer .= 'error';
}

	$buffer .= '</data>';
	header('Content-Type: text/xml');
	echo $buffer;


?>
