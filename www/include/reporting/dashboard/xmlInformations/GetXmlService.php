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

	if (isset($_GET["hostID"]) && isset($_GET["serviceID"]) && isset($_GET["color"]) && isset($_GET["today_ok"])&& isset($_GET["today_critical"]) && isset($_GET["today_unknown"])&& isset($_GET["today_pending"])){

		list($colorOK, $colorWARNING, $colorCRITICAL, $colorPENDING, $colorUNKNOWN)= split (":", $_GET["color"], 5);

		$dsn = array(
			     'phptype'  => 'mysql',
			     'username' => $conf_centreon['user'],
			     'password' => $conf_centreon['password'],
			     'hostspec' => $conf_centreon['hostCentreon'],
			     'database' => $conf_centreon['db'],
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
			     'username' => $conf_centreon['user'],
			     'password' => $conf_centreon['password'],
			     'hostspec' => $conf_centreon['hostCentstorage'],
			     'database' => $conf_centreon['dbcstg'],
			     );
		

		$pearDBO =& DB::connect($dsn, $options);
		if (PEAR::isError($pearDB)) 
		  die("Connecting probems with centstorage database : " . $pearDBO->getMessage());		
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
			$date = $tab_month[date('m', $time_unix)].date(" d Y G:i:s", $time_unix);
			return $date;
		}
			
		$request = "SELECT  * FROM `log_archive_service` WHERE host_id = '".$_GET["hostID"]."' AND service_id = ".$_GET["serviceID"]." ORDER BY date_start DESC";
		$DBRESULT =& $pearDBO->query($request);
		while ($h =& $DBRESULT->fetchRow()) {

			$oktime = $h["OKTimeScheduled"];
			$criticaltime = $h["CRITICALTimeScheduled"];
			$warningtime = $h["WARNINGTimeScheduled"];
			$unknowntime = $h["UNKNOWNTimeScheduled"];
	
			$tt = 0 + ($h["date_end"] - $h["date_start"]);
			
			(($oktime + $criticaltime + $warningtime + $unknowntime) < $tt) ? $pendingtime = 	$tt - ($oktime + $criticaltime + $warningtime + $unknowntime) : $pendingtime = 0;
			($oktime > 0) ? $pok = 0 +round(($oktime / $tt * 100),2) : $pok = "0.00";
			($criticaltime > 0) ? $pcritical = 0 +round(($criticaltime / $tt * 100),2) : $pcritical = "0.00";
			($warningtime > 0) ? $pwarning = 0 +round(($warningtime / $tt * 100),2) : $pwarning = "0.00";
			($unknowntime > 0) ? $punknown = 0 +round(($unknowntime / $tt * 100),2) : $punknown = "0.00";
			($pendingtime > 0) ? $ppending = 0 +round(($pendingtime / $tt * 100),2) : $ppending = "0.00";

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

			/*
			 * create bull for all days
			 */
			$bulleDashtab = '{table class=bulleDashtab}';
			$bulleDashtab .= '{tr}{td class=bulleDashleft colspan=3}'. date("d/m/Y", $start) .' - Duration: '.Duration::toString($tt).'{/td}{td class=bulleDashleft }Alert{/td}{/tr}';
			$bulleDashtab .= '{tr}{td class=bulleDashleft style="background:#'.$colorOK.';"  }OK:{/td}{td class=bulleDash}'. Duration::toString($oktime) .'{/td}{td class=bulleDash}'.(($pok > 0) ? $pok : "0").'%{/td}{td class=bulleDash}'.$h["OKnbEvent"].'{/td}{/tr}';
			$bulleDashtab .= '{tr}{td class=bulleDashleft style="background:#'.$colorCRITICAL.';"  }Critical:{/td}{td class=bulleDash}'. Duration::toString($criticaltime) .'{/td}{td class=bulleDash}'.(($pcritical > 0) ? $pcritical : "0").'%{/td}{td class=bulleDash}'.$h["CRITICALnbEvent"].'{/td}{/tr}';
			$bulleDashtab .= '{tr}{td class=bulleDashleft style="background:#'.$colorWARNING.';"  }Warning:{/td}{td class=bulleDash}'. Duration::toString($warningtime) .'{/td}{td class=bulleDash}'.(($pwarning > 0) ? $pwarning : "0").'%{/td}{td class=bulleDash}'.$h["WARNINGnbEvent"].'{/td}{/tr}';
			$bulleDashtab .= '{tr}{td class=bulleDashleft style="background:#cccccc;"  }Unknown:{/td}{td class=bulleDash}'. Duration::toString($pendingtime) .'{/td}{td class=bulleDash}'.(($ppending > 0) ? $ppending : "0").'%{/td}{td class=bulleDash}-{/td}{/tr}';
			$bulleDashtab .= '{tr}{td class=bulleDashleft style="background:#'.$colorUNKNOWN.';"  }Undeterminated:{/td}{td class=bulleDash}'. Duration::toString($unknowntime) .'{/td}{td class=bulleDash}'.(($punknown > 0) ? $punknown : "0").'%{/td}{/tr}';
			$bulleDashtab .= '{/table}';
			
			$tp = round(($punknown * $t / 100 ),2);
			if ($punknown > 0){
				$end = $h["date_start"] + $tp + 5000;
				$buffer .= '<event ';
				$buffer .= ' start="' .create_date_timeline_format($start) . ' GMT"';
				$buffer .= ' end="' . create_date_timeline_format($end). ' GMT"';
				$buffer .= ' color="#' . $colorUNKNOWN . '"';
				$buffer .= ' isDuration="true" ';
				$buffer .= ' title= "' . (($punknown > 0) ? $punknown : "0") . '%" >' ;
				$buffer .= $bulleDashtab;
				$buffer .= '</event>';	
			}

			$tp = round(($ppending * $t / 100 ),2);
			if ($ppending > 0){
				$end = $h["date_start"] + $tp + 5000;
				$buffer .= '<event ';
				$buffer .= ' start="' .create_date_timeline_format($start) . ' GMT"';
				$buffer .= ' end="' . create_date_timeline_format($end). ' GMT"';
				$buffer .= ' color="#cccccc"';
				$buffer .= ' isDuration="true" ';
				$buffer .= ' title= "' . (($ppending > 0) ? $ppending : "0") . '%" >' ;
				$buffer .= $bulleDashtab;
				$buffer .= '</event>';	
			}

			$tp = round(($pwarning * $t / 100 ),2);
			if ($pwarning > 0){
				$end = $h["date_start"] + $tp + 5000;
				$buffer .= '<event ';
				$buffer .= ' start="' .create_date_timeline_format($start) . ' GMT"';
				$buffer .= ' end="' . create_date_timeline_format($end). ' GMT"';
				$buffer .= ' color="#' . $colorWARNING . '"';
				$buffer .= ' isDuration="true" ';
				$buffer .= ' title= "' . (($pwarning > 0) ? $pwarning : "0") . '%" >' ;
				$buffer .= $bulleDashtab;
				$buffer .= '</event>';		
			}
			
			$tp = round(($pcritical * $t / 100 ),2);
			if ($pcritical > 0){
				$end = $h["date_start"] + $tp + 5000;
				$buffer .= '<event ';
				$buffer .= ' start="' .create_date_timeline_format($start) . ' GMT"';
				$buffer .= ' end="' . create_date_timeline_format($end). ' GMT"';
				$buffer .= ' color="#' . $colorCRITICAL . '"';
				$buffer .= ' isDuration="true" ';
				$buffer .= ' title= "' . (($pcritical > 0) ? $pcritical : "0") . '%" >' ;
				$buffer .= $bulleDashtab;
				$buffer .= '</event>';
			}

			$tp = round(($pok * $t / 100 ),2);
			if ($pok > 0){
				$end = $h["date_start"] + $tp + 5000;
				$buffer .= '<event ';
				$buffer .= ' start="' .create_date_timeline_format($start) . ' GMT"';
				$buffer .= ' end="' . create_date_timeline_format($end). ' GMT"';
				$buffer .= ' color="#' . $colorOK . '"';
				$buffer .= ' isDuration="true" ';
				$buffer .= ' title= "' . (($pok > 0) ? $pok : "0") .   '%" >' ;
				$buffer .= $bulleDashtab;
				$buffer .= '</event>';
			}
		}
		unset($DBRESULT);	
		/*
		 * Today purcent if period
		 */	
		 
		$day = date("d",time());
		$year = date("Y",time());
		$month = date("m",time());
		$today_start = mktime(0, 0, 0, $month, $day, $year);
		$today_end = time();
	
		$t = $today_end - $today_start;
		$start = $today_start + 5000;
	
		/*
		 * Create bull for today
		 */
		$bulleDashtab = '{table class=bulleDashtab}';
		$bulleDashtab .= '{tr}{td class=bulleDashleft colspan=3}'. date("d/m/Y", $start) .' -- Duration: '.Duration::toString($t).'{/td}{td class=bulleDashleft }Alert{/td}{/tr}';
		$bulleDashtab .= '{tr}{td class=bulleDashleft style="background:#'.$colorOK.';"  }OK:{/td}{td class=bulleDash}'.Duration::toString($_GET["today_ok"] * $t / 100) .'{/td}{td class=bulleDash}'.(($_GET["today_ok"] > 0) ? $_GET["today_ok"] : "0").'%{/td}{td class=bulleDash}'.$_GET["today_OKnbEvent"].'{/td}{/tr}';
		$bulleDashtab .= '{tr}{td class=bulleDashleft style="background:#'.$colorCRITICAL.';"  }Critical:{/td}{td class=bulleDash}'.Duration::toString($_GET["today_critical"] * $t / 100) .'{/td}{td class=bulleDash}'.(($_GET["today_critical"] > 0) ? $_GET["today_critical"] : "0").'%{/td}{td class=bulleDash}'.$_GET["today_CRITICALnbEvent"].'{/td}{/tr}';
		$bulleDashtab .= '{tr}{td class=bulleDashleft style="background:#'.$colorWARNING.';"  }Warning:{/td}{td class=bulleDash}'. Duration::toString($_GET["today_warning"] * $t / 100) .'{/td}{td class=bulleDash}'.(($_GET["today_warning"] > 0) ? $_GET["today_warning"] : "0").'%{/td}{td class=bulleDash}'.$_GET["today_WARNINGnbEvent"].'{/td}{/tr}';
		$bulleDashtab .= '{tr}{td class=bulleDashleft style="background:#'.$colorUNKNOWN.';"  }Unknown:{/td}{td class=bulleDash}'. Duration::toString($_GET["today_unknown"] * $t / 100) .'{/td}{td class=bulleDash}'.(($_GET["today_unknown"] > 0) ? $_GET["today_unknown"] : "0").'%{/td}{td class=bulleDash}'.$_GET["today_UNKNOWNnbEvent"].'{/td}{/tr}';
		$bulleDashtab .= '{tr}{td class=bulleDashleft style="background:#cccccc;"  }Undeterminated:{/td}{td class=bulleDash}'.  Duration::toString($_GET["today_pending"] * $t / 100) .'{/td}{td class=bulleDash}'.(($_GET["today_pending"] > 0) ? $_GET["today_pending"] : "0").'%{/td}{/tr}';
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

		$tp = round(($_GET["today_unknown"] * $t / 100 ),2);
		if ($_GET["today_unknown"] > 0){
			$end = $today_start + $tp + 5000;
			$buffer .= '<event ';
			$buffer .= ' start="' .create_date_timeline_format($start) . ' GMT"';
			$buffer .= ' end="' . create_date_timeline_format($end). ' GMT"';
			$buffer .= ' color="#' . $colorUNKNOWN . '"';
			$buffer .= ' isDuration="true" ';
			$buffer .= ' title= "' . $_GET["today_unknown"] . '%" >' ;
			$buffer .= $bulleDashtab;
			$buffer .= '</event>';
		}

		$tp = round(($_GET["today_warning"] * $t / 100 ),2);
		if ($_GET["today_warning"] > 0){
			$end = $today_start + $tp + 5000;
			$buffer .= '<event ';
			$buffer .= ' start="' .create_date_timeline_format($start) . ' GMT"';
			$buffer .= ' end="' . create_date_timeline_format($end). ' GMT"';
			$buffer .= ' color="#' . $colorWARNING . '"';
			$buffer .= ' isDuration="true" ';
			$buffer .= ' title= "' . $_GET["today_warning"] . '%" >' ;
			$buffer .= $bulleDashtab;
			$buffer .= '</event>';
		}
		
		$tp = round(($_GET["today_critical"] * $t / 100 ),2);
		if ($_GET["today_critical"] > 0){
			$end = $today_start + $tp + 5000;
			$buffer .= '<event ';
			$buffer .= ' start="' .create_date_timeline_format($start) . ' GMT"';
			$buffer .= ' end="' . create_date_timeline_format($end). ' GMT"';
			$buffer .= ' color="#' . $colorCRITICAL . '"';
			$buffer .= ' isDuration="true" ';
			$buffer .= ' title= "' . $_GET["today_critical"] . '%" >' ;
			$buffer .= $bulleDashtab;
			$buffer .= '</event>';
		}
	
		$tp = round(($_GET["today_ok"] * $t / 100 ),2);
		if ($_GET["today_ok"] > 0){
			$end = $today_start + $tp + 5000;
			$buffer .= '<event ';
			$buffer .= ' start="' .create_date_timeline_format($start) . ' GMT"';
			$buffer .= ' end="' . create_date_timeline_format($end). ' GMT"';
			$buffer .= ' color="#' . $colorOK . '"';
			$buffer .= ' isDuration="true" ';
			$buffer .= ' title= "' . $_GET["today_ok"] . '%" >' ;
			$buffer .= $bulleDashtab;
			$buffer .= '</event>';
		}
	} else {
		$buffer .= '<error>error</error>';
	}

	$buffer .= '</data>';
	header('Content-Type: text/xml');
	echo $buffer;
?>