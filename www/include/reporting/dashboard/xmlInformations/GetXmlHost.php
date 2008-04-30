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

	#
	## pearDB init
	#
	require_once 'DB.php';
	require_once("/etc/centreon/centreon.conf.php");
	require_once $centreon_path.'www/class/other.class.php';
	
	$buffer = null;
	$buffer  = '<?xml version="1.0"?>';
	$buffer .= '<data>';

	if (isset($_GET["oreonPath"]) && isset($_GET["hostID"]) && isset($_GET["color"]) && isset($_GET["today_up"])&& isset($_GET["today_down"])&& isset($_GET["today_unreachable"])&& isset($_GET["today_pending"]))
	{
		list($colorUP, $colorDOWN, $colorUNREACHABLE, $colorUNKNOWN)= split (":", $_GET["color"], 4);

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

		$dsn = array(
			     'phptype'  => 'mysql',
			     'username' => $conf_oreon['user'],
			     'password' => $conf_oreon['password'],
			     'hostspec' => $conf_oreon['hostCentstorage'],
			     'database' => $conf_oreon['dbcstg'],
			     );
		

		$pearDBO =& DB::connect($dsn, $options);
		if (PEAR::isError($pearDB)) 
		  die("Connecting probems with oreon database : " . $pearDBO->getMessage());		
		$pearDBO->setFetchMode(DB_FETCHMODE_ASSOC);

		function create_date_timeline_format($time_unix)	{
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
			
		$rq = 'SELECT  * FROM `log_archive_host` WHERE host_id = ' . $_GET["hostID"] . ' order by date_start desc';			
		$res = & $pearDBO->query($rq);

		  while ($h =& $res->fetchRow()) {
			$uptime = $h["UPTimeScheduled"];
			$downtime = $h["DOWNTimeScheduled"];
			$unreachalbetime = $h["UNREACHABLETimeScheduled"];

			$tt = 0 + ($h["date_end"] - $h["date_start"]);
			if (($uptime + $downtime + $unreachalbetime) < $tt)
				$undeterminatetime = 0 + $tt - ($uptime + $downtime + $unreachalbetime);
			else
				$undeterminatetime = 0;
			
			if ($unreachalbetime > 0)
				$punreach = 0 +round(($unreachalbetime / $tt * 100),2);
			else
				$punreach = "0.00";

			if ($uptime > 0)
				$pup = 0 +round(($uptime / $tt * 100),2);
			else
				$pup = "0.00";
			
			if ($downtime > 0)
				$pdown = 0 +round(($downtime / $tt * 100),2);
			else
			 	$pdown = "0.00";
			
			if ($undeterminatetime > 0)
				$pundet = 0 +round(($undeterminatetime / $tt * 100),2);
			else
				$pundet = "0.00";

			$t = 0 + ($h["date_end"] - $h["date_start"]);
			$t = round(($t - ($t * 0.11574074074)),2);
			$start = $h["date_start"] + 5000;

			#
			## make bubul
			#
			$bubultab = '{table class=bulleDashtab}';
			$bubultab .= '{tr}{td class=bulleDashleft colspan=3}Day: '. date("d/m/Y", $start) .' --  Duration: '.Duration::toString($tt).'{/td}{td class=bulleDashleft }Alert{/td}{/tr}';
			$bubultab .= '{tr}{td class=bulleDashleft style="background:#'.$colorUP.';"  }Up:{/td}{td class=bulleDash}'. Duration::toString($uptime) .'{/td}{td class=bulleDash}'.(($pup > 0) ? $pup : "0").'%{/td}{td class=bulleDash}'.$h["UPnbEvent"].'{/td}{/tr}';
			$bubultab .= '{tr}{td class=bulleDashleft style="background:#'.$colorDOWN.';" }Down:{/td}{td class=bulleDash}'.Duration::toString($downtime).'{/td}{td class=bulleDash}'.(($pdown > 0) ? $pdown : "0").'%{/td}{td class=bulleDash}'.$h["DOWNnbEvent"].'{/td}{/tr}';
			$bubultab .= '{tr}{td class=bulleDashleft style="background:#'.$colorUNREACHABLE.';" }Unreachable:{/td}{td class=bulleDash}'.Duration::toString($unreachalbetime).'{/td}{td class=bulleDash}'.(($punreach > 0) ? $punreach : "0").'%{/td}{td class=bulleDash}'.$h["UNREACHABLEnbEvent"].'{/td}{/tr}';
			$bubultab .= '{tr}{td class=bulleDashleft style="background:#cccccc;" }Undeterminated:{/td}{td class=bulleDash}'.Duration::toString($undeterminatetime).'{/td}{td class=bulleDash}'.(($pundet > 0) ? $pundet : "0").'%{/td}{/tr}';
			$bubultab .= '{/table}';
			
			$tp = round(($pundet * $t / 100 ),2);
			if ($pundet > 0){
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
			if ($punreach > 0){
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
			if ($pdown > 0){
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
			if ($pup > 0){
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
		$bubultab  = '{table class=bulleDashtab}';
		$bubultab .= '{tr}{td class=bulleDashleft colspan=3}Day: '. date("d/m/Y", $start) .' --  Duration: '.Duration::toString($t).'{/td}{td class=bulleDashleft }Alert{/td}{/tr}';
		$bubultab .= '{tr}{td class=bulleDashleft style="background:#'.$colorUP.';"  }Up:{/td}{td class=bulleDash}'. Duration::toString($_GET["today_up"] * $t / 100) .'{/td}{td class=bulleDash}'.$_GET["today_up"].'%{/td}{td class=bulleDash}'.$_GET["today_UPnbEvent"].'{/td}{/tr}';
		$bubultab .= '{tr}{td class=bulleDashleft style="background:#'.$colorDOWN.';" }Down:{/td}{td class=bulleDash}'.Duration::toString($_GET["today_down"] * $t / 100).'{/td}{td class=bulleDash}'.$_GET["today_down"].'%{/td}{td class=bulleDash}'.$_GET["today_DOWNnbEvent"].'{/td}{/tr}';
		$bubultab .= '{tr}{td class=bulleDashleft style="background:#'.$colorUNREACHABLE.';" }Unreachable:{/td}{td class=bulleDash}'.Duration::toString(0+$_GET["today_unreachable"] * $t / 100) .'{/td}{td class=bulleDash}'.$_GET["today_unreachable"].'%{/td}{td class=bulleDash}'.$_GET["today_UNREACHABLEnbEvent"].'{/td}{/tr}';
		$bubultab .= '{tr}{td class=bulleDashleft style="background:#cccccc;" }Undeterminated:{/td}{td class=bulleDash}'.Duration::toString($_GET["today_pending"] * $t / 100).'{/td}{td class=bulleDash}'.$_GET["today_pending"].'%{/td}{/tr}';
		$bubultab .= '{/table}';
	
		$t = round(($t - ($t * 0.11574074074)),2);
		$tp = round(($_GET["today_pending"] * $t / 100 ),2);
	
		if ($_GET["today_pending"] > 0){
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
		if ($_GET["today_unreachable"] > 0){
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
		if ($_GET["today_down"] > 0){
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
		if ($_GET["today_up"] > 0){
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
	} else {
		$buffer .= '<error>error</error>';
	}

	$buffer .= '</data>';
	header('Content-Type: text/xml');
	echo $buffer;
?>