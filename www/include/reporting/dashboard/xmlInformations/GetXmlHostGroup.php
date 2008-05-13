<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus - Cedrick Facon 
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

	require_once 'DB.php';
	require_once("@CENTREON_ETC@/centreon.conf.php");
	require_once $centreon_path.'www/class/other.class.php';
	
	$buffer = null;
	$buffer  = '<?xml version="1.0"?>';
	$buffer .= '<data>';

	if (isset($_GET["hostID"]) && isset($_GET["color"]) && isset($_GET["today_up"])&& isset($_GET["today_down"])&& isset($_GET["today_unreachable"])&& isset($_GET["today_pending"])){

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

		$options = array(
				 'debug'       => 2,
				 'portability' => DB_PORTABILITY_ALL ^ DB_PORTABILITY_LOWERCASE,
				 );
			
		$pearDBO =& DB::connect($dsn, $options);
		if (PEAR::isError($pearDB)) 
		  	die("Connecting probems with oreon database : " . $pearDB->getMessage());		
		$pearDBO->setFetchMode(DB_FETCHMODE_ASSOC);

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
		
		$str = "";
		$request = "SELECT host_host_id FROM `hostgroup_relation` WHERE `hostgroup_hg_id` = '" . $_GET["hostID"] ."'";
		$DBRESULT = & $pearDB->query($request);
		while ($hg =& $DBRESULT->fetchRow()) {
			if ($str != "")
				$str .= ", ";
			$str .= $hg["host_host_id"]; 
		}
		unset($hg);
		unset($DBRESULT);
		
		$rq = 'SELECT date_start, date_end, avg( `UPTimeScheduled` ) as "UPTimeScheduled", ' .
				'avg( `UPnbEvent` ) as "UPnbEvent", avg( `DOWNTimeScheduled` ) as "DOWNTimeScheduled", ' .
				'avg( `DOWNnbEvent` ) as "DOWNnbEvent", avg( `UNREACHABLETimeScheduled` ) as "UNREACHABLETimeScheduled", ' .
				'avg( `UNREACHABLEnbEvent` ) as "UNREACHABLEnbEvent" ' .
				'FROM `log_archive_host` WHERE `host_id` IN ('.$str.') GROUP BY date_end, date_start ORDER BY date_start desc';
		$res = & $pearDBO->query($rq);

		while ($h =& $res->fetchRow()) 
		{
			$uptime 			= $h["UPTimeScheduled"];
			$downtime 			= $h["DOWNTimeScheduled"];
			$unreachalbetime	= $h["UNREACHABLETimeScheduled"];

			$tt = 0 + ($h["date_end"] - $h["date_start"]);
			(($uptime + $downtime + $unreachalbetime) < $tt) ? $undeterminatetime = 0 + $tt - ($uptime + $downtime + $unreachalbetime) : $undeterminatetime = 0;
			($unreachalbetime > 0) ? $punreach = 0 +round(($unreachalbetime / $tt * 100),2) : $punreach = "0.00";
			($uptime > 0) ? $pup = 0 +round(($uptime / $tt * 100),2) : $pup = "0.00";
			($downtime > 0) ? $pdown = 0 +round(($downtime / $tt * 100),2) : $pdown = "0.00";
			($undeterminatetime > 0) ? $pundet = 0 +round(($undeterminatetime / $tt * 100),2) : $pundet = "0.00";

			$t = 0 + ($h["date_end"] - $h["date_start"]);
			$t = round(($t - ($t * 0.11574074074)),2);
			$start = $h["date_start"] + 5000;

			#
			## make bubul
			#
			$bulleDashtab = '{table class=bulleDashtab}';
			$bulleDashtab .= '{tr}{td class=bulleDashleft colspan=3}Day: '. date("d/m/Y", $start) .' --  Duration: '.Duration::toString($tt).'{/td}{td class=bulleDashleft }Alert{/td}{/tr}';
			$bulleDashtab .= '{tr}{td class=bulleDashleft style="background:#'.$colorUP.';"  }Up:{/td}{td class=bulleDash}'. Duration::toString($uptime) .'{/td}{td class=bulleDash}'.(($pup > 0) ? $pup : "0").'%{/td}{td class=bulleDash}'.number_format($h["UPnbEvent"], 1,'.','').'{/td}{/tr}';
			$bulleDashtab .= '{tr}{td class=bulleDashleft style="background:#'.$colorDOWN.';" }Down:{/td}{td class=bulleDash}'.Duration::toString($downtime).'{/td}{td class=bulleDash}'.(($pdown > 0) ? $pdown : "0").'%{/td}{td class=bulleDash}'.number_format($h["DOWNnbEvent"], 1,'.','').'{/td}{/tr}';
			$bulleDashtab .= '{tr}{td class=bulleDashleft style="background:#'.$colorUNREACHABLE.';" }Unreachable:{/td}{td class=bulleDash}'.Duration::toString($unreachalbetime).'{/td}{td class=bulleDash}'.(($punreach > 0) ? $punreach : "0").'%{/td}{td class=bulleDash}'.number_format($h["UNREACHABLEnbEvent"], 1,'.','').'{/td}{/tr}';
			$bulleDashtab .= '{tr}{td class=bulleDashleft style="background:#cccccc;" }Undeterminated:{/td}{td class=bulleDash}'.Duration::toString($undeterminatetime).'{/td}{td class=bulleDash}'.(($pundet > 0) ? $pundet : "0").'%{/td}{/tr}';
			$bulleDashtab .= '{/table}';
			
			$tp = round(($pundet * $t / 100 ),2);
			if ($pundet > 0){
				$end = $h["date_start"] + $tp + 5000;
				$buffer .= '<event ';
				$buffer .= ' start="' .create_date_timeline_format($start) . ' GMT"';
				$buffer .= ' end="' . create_date_timeline_format($end). ' GMT"';
				$buffer .= ' color="#cccccc"';
				$buffer .= ' isDuration="true" ';
				$buffer .= ' title= "' . (($pundet > 0) ? $pundet : "0") . '%" >' ;
				$buffer .= $bulleDashtab;
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
				$buffer .= $bulleDashtab;
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
				$buffer .= $bulleDashtab;
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
				$buffer .= $bulleDashtab;
				$buffer .= '</event>';	
			}

		  }
		 
		/*
		 * Today purcent if period
		 */
		$day = date("d",time());
		$year = date("Y",time());
		$month = date("m",time());
		$today_start = mktime(0, 0, 0, $month, $day, $year);
		$today_end = time();

		$t = 0 + ($today_end - $today_start);
		$start = $today_start + 5000;

		$NbAlert = "Unknown";
	

		/*
		 * Today stats
		 */

		/*
		 * Create Bull XML
		 */
		$bulleDashtab = '{table class=bulleDashtab}';
		$bulleDashtab .= '{tr}{td class=bulleDashleft colspan=3}Day: '. date("d/m/Y", $start) .' --  Duration: '.Duration::toString($t).'{/td}{td class=bulleDashleft }Alert{/td}{/tr}';
		$bulleDashtab .= '{tr}{td class=bulleDashleft style="background:#'.$colorUP.';"  }Up:{/td}{td class=bulleDash}'. Duration::toString($_GET["today_up"] * $t / 100) .'{/td}{td class=bulleDash}'.$_GET["today_up"].'%{/td}{td class=bulleDash}'.$_GET["today_UPnbEvent"].'{/td}{/tr}';
		$bulleDashtab .= '{tr}{td class=bulleDashleft style="background:#'.$colorDOWN.';" }Down:{/td}{td class=bulleDash}'.Duration::toString($_GET["today_down"] * $t / 100).'{/td}{td class=bulleDash}'.$_GET["today_down"].'%{/td}{td class=bulleDash}'.$_GET["today_DOWNnbEvent"].'{/td}{/tr}';
		$bulleDashtab .= '{tr}{td class=bulleDashleft style="background:#'.$colorUNREACHABLE.';" }Unreachable:{/td}{td class=bulleDash}'.Duration::toString(0+$_GET["today_unreachable"] * $t / 100) .'{/td}{td class=bulleDash}'.$_GET["today_unreachable"].'%{/td}{td class=bulleDash}'.$_GET["today_UNREACHABLEnbEvent"].'{/td}{/tr}';
		$bulleDashtab .= '{tr}{td class=bulleDashleft style="background:#cccccc;" }Undeterminated:{/td}{td class=bulleDash}'.Duration::toString($_GET["today_pending"] * $t / 100).'{/td}{td class=bulleDash}'.$_GET["today_pending"].'%{/td}{/tr}';
		$bulleDashtab .= '{/table}';
	
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
			$buffer .= $bulleDashtab;
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
			$buffer .= $bulleDashtab;
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
			$buffer .= $bulleDashtab;
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
			$buffer .= $bulleDashtab;
			$buffer .= '</event>';
		}
	} else	{
		$buffer .= '<error>error</error>';
	}

	$buffer .= '</data>';
	header('Content-Type: text/xml');
	echo $buffer;
?>