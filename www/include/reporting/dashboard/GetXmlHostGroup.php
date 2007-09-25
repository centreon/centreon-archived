<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus

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
		include_once($oreonPath . "/www/centreon.conf.php");
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
				'date_start, date_end, ' .
				'avg( `UPTimeScheduled` ) as "UPTimeScheduled", ' .
				'avg( `UPnbEvent` ) as "UPnbEvent", ' .
				'avg( `DOWNTimeScheduled` ) as "DOWNTimeScheduled", ' .
				'avg( `DOWNnbEvent` ) as "DOWNnbEvent", ' .
				'avg( `UNREACHABLETimeScheduled` ) as "UNREACHABLETimeScheduled", ' .
				'avg( `UNREACHABLEnbEvent` ) as "UNREACHABLEnbEvent" ' .
				'FROM `log_archive_host` WHERE `host_id` IN (' .
				'SELECT host_host_id FROM `hostgroup_relation` WHERE `hostgroup_hg_id` = ' . $_GET["hostID"] .') group by date_end, date_start order by date_start desc';

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

			#
			## make bubul
			#
			$bubultab = '{table class=bubultab}';
			$bubultab .= '{tr}{td class=bubuleft colspan=3}Day: '. date("d/m/Y", $start) .' --  Duration: '.Duration::toString($tt).'{/td}{td class=bubuleft }Alert{/td}{/tr}';
			$bubultab .= '{tr}{td class=bubuleft style="background:#'.$colorUP.';"  }Up:{/td}{td class=bubul}'. Duration::toString($uptime) .'{/td}{td class=bubul}'.(($pup > 0) ? $pup : "0").'%{/td}{td class=bubul}'.number_format($h["UPnbEvent"], 1,'.','').'{/td}{/tr}';
			$bubultab .= '{tr}{td class=bubuleft style="background:#'.$colorDOWN.';" }Down:{/td}{td class=bubul}'.Duration::toString($downtime).'{/td}{td class=bubul}'.(($pdown > 0) ? $pdown : "0").'%{/td}{td class=bubul}'.number_format($h["DOWNnbEvent"], 1,'.','').'{/td}{/tr}';
			$bubultab .= '{tr}{td class=bubuleft style="background:#'.$colorUNREACHABLE.';" }Unreachable:{/td}{td class=bubul}'.Duration::toString($unreachalbetime).'{/td}{td class=bubul}'.(($punreach > 0) ? $punreach : "0").'%{/td}{td class=bubul}'.number_format($h["UNREACHABLEnbEvent"], 1,'.','').'{/td}{/tr}';
			$bubultab .= '{tr}{td class=bubuleft style="background:#cccccc;" }Undeterminated:{/td}{td class=bubul}'.Duration::toString($undeterminatetime).'{/td}{td class=bubul}'.(($pundet > 0) ? $pundet : "0").'%{/td}{/tr}';
			$bubultab .= '{/table}';
			
			$tp = round(($pundet * $t / 100 ),2);
			if($pundet > 0){
				$end = $h["date_start"] + $tp + 5000;
				$buffer .= '<event ';
				$buffer .= ' start="' .create_date_timeline_format($start) . ' GMT"';
				$buffer .= ' end="' . create_date_timeline_format($end). ' GMT"';
				$buffer .= ' color="#cccccc"';
				$buffer .= ' isDuration="true" ';
				$buffer .= ' title= "' . (($pundet > 0) ? $pundet : "0") . '%" >' ;
				$buffer .= $bubultab;
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
				$buffer .= $bubultab;
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
				$buffer .= $bubultab;
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
				$buffer .= $bubultab;
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
	$start = $today_start + 5000;

	$NbAlert = "Unknown";


	#
	## make bubul
	#
	$bubultab = '{table class=bubultab}';
	$bubultab .= '{tr}{td class=bubuleft colspan=3}Day: '. date("d/m/Y", $start) .' --  Duration: '.Duration::toString($t).'{/td}{td class=bubuleft }Alert{/td}{/tr}';
	$bubultab .= '{tr}{td class=bubuleft style="background:#'.$colorUP.';"  }Up:{/td}{td class=bubul}'. Duration::toString($_GET["today_up"] * $t / 100) .'{/td}{td class=bubul}'.$_GET["today_up"].'%{/td}{td class=bubul}'.$_GET["today_UPnbEvent"].'{/td}{/tr}';
	$bubultab .= '{tr}{td class=bubuleft style="background:#'.$colorDOWN.';" }Down:{/td}{td class=bubul}'.Duration::toString($_GET["today_down"] * $t / 100).'{/td}{td class=bubul}'.$_GET["today_down"].'%{/td}{td class=bubul}'.$_GET["today_DOWNnbEvent"].'{/td}{/tr}';
	$bubultab .= '{tr}{td class=bubuleft style="background:#'.$colorUNREACHABLE.';" }Unreachable:{/td}{td class=bubul}'.Duration::toString(0+$_GET["today_unreachable"] * $t / 100) .'{/td}{td class=bubul}'.$_GET["today_unreachable"].'%{/td}{td class=bubul}'.$_GET["today_UNREACHABLEnbEvent"].'{/td}{/tr}';
	$bubultab .= '{tr}{td class=bubuleft style="background:#cccccc;" }Undeterminated:{/td}{td class=bubul}'.Duration::toString($_GET["today_pending"] * $t / 100).'{/td}{td class=bubul}'.$_GET["today_pending"].'%{/td}{/tr}';
	$bubultab .= '{/table}';

	$t = round(($t - ($t * 0.11574074074)),2);
	$tp = round(($_GET["today_pending"] * $t / 100 ),2);

	if($_GET["today_pending"] > 0){
		$end = $today_start + $tp + 5000;
		$buffer .= '<event ';
		$buffer .= ' start="' .create_date_timeline_format($start) . ' GMT"';
		$buffer .= ' end="' . create_date_timeline_format($end). ' GMT"';
		$buffer .= ' color="#cccccc"';
		$buffer .= ' isDuration="true" ';
		$buffer .= ' title= "' . $_GET["today_pending"] . '%" >' ;
		$buffer .= $bubultab;
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
		$buffer .= $bubultab;
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
		$buffer .= $bubultab;
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
		$buffer .= $bubultab;
		$buffer .= '</event>';
	}
	}
	else
	{
		$buffer .= '<error>error</error>';
	}

	$buffer .= '</data>';

	header('Content-Type: text/xml');
	echo $buffer;
?>
