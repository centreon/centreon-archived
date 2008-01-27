<?php
/**
Oreon is developped with Apache Licence 2.0 :
http://www.apache.org/licenses/LICENSE-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus - Cedrick Facon

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


	if(isset($_GET["oreonPath"]) && isset($_GET["svc_group_id"]) &&
	   isset($_GET["color"]) && isset($_GET["today_ok"])&& isset($_GET["today_critical"])&& 
	   isset($_GET["today_unknown"])&& isset($_GET["today_pending"]))
	{
		list($colorOK, $colorWARNING, $colorCRITICAL, $colorPENDING, $colorUNKNOWN)= split (":", $_GET["color"], 5);

		$oreonPath = $_GET["oreonPath"];
		include_once($oreonPath . "/etc/centreon.conf.php");
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
		' * FROM `log_archive_service` WHERE host_id = ' . $_GET["hostID"] . ' AND service_id = ' . $_GET["svc_group_id"] .
		' order by date_start desc';
			
		$rq = 'SELECT ' .
				'date_start, date_end, ' .
				'avg( `OKTimeScheduled` ) as "OKTimeScheduled", ' .
				'avg( `OKnbEvent` ) as "OKnbEvent", ' .
				'avg( `WARNINGTimeScheduled` ) as "WARNINGTimeScheduled", ' .
				'avg( `WARNINGnbEvent` ) as "WARNINGnbEvent", ' .
				'avg( `UNKNOWNTimeScheduled` ) as "UNKNOWNTimeScheduled", ' .
				'avg( `UNKNOWNnbEvent` ) as "UNKNOWNnbEvent", ' .
				'avg( `CRITICALTimeScheduled` ) as "CRITICALTimeScheduled", ' .
				'avg( `CRITICALnbEvent` ) as "CRITICALnbEvent" ' .
				'FROM `log_archive_service` WHERE `service_id` IN (' .
				'SELECT `service_service_id` FROM `servicegroup_relation` WHERE `servicegroup_sg_id` = ' . $_GET["svc_group_id"] .') group by date_end, date_start order by date_start desc';
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

			#
			## make bubul
			#
			$bubultab = '{table class=bubultab}';
			$bubultab .= '{tr}{td class=bubuleft colspan=3}Day: '. date("d/m/Y", $start) .' --  Duration: '.Duration::toString($tt).'{/td}{td class=bubuleft }Alert{/td}{/tr}';
			$bubultab .= '{tr}{td class=bubuleft style="background:#'.$colorOK.';"  }OK:{/td}{td class=bubul}'. Duration::toString($oktime) .'{/td}{td class=bubul}'.(($pok > 0) ? $pok : "0").'%{/td}{td class=bubul}'.$h["OKnbEvent"].'{/td}{/tr}';
			$bubultab .= '{tr}{td class=bubuleft style="background:#'.$colorCRITICAL.';"  }Critical:{/td}{td class=bubul}'. Duration::toString($criticaltime) .'{/td}{td class=bubul}'.(($pcritical > 0) ? $pcritical : "0").'%{/td}{td class=bubul}'.$h["CRITICALnbEvent"].'{/td}{/tr}';
			$bubultab .= '{tr}{td class=bubuleft style="background:#'.$colorWARNING.';"  }Warning:{/td}{td class=bubul}'. Duration::toString($warningtime) .'{/td}{td class=bubul}'.(($pwarning > 0) ? $pwarning : "0").'%{/td}{td class=bubul}'.$h["WARNINGnbEvent"].'{/td}{/tr}';
			$bubultab .= '{tr}{td class=bubuleft style="background:#cccccc;"  }Unknown:{/td}{td class=bubul}'. Duration::toString($pendingtime) .'{/td}{td class=bubul}'.(($ppending > 0) ? $ppending : "0").'%{/td}{td class=bubul}-{/td}{/tr}';
			$bubultab .= '{tr}{td class=bubuleft style="background:#'.$colorUNKNOWN.';"  }Undeterminated:{/td}{td class=bubul}'. Duration::toString($unknowntime) .'{/td}{td class=bubul}'.(($punknown > 0) ? $punknown : "0").'%{/td}{/tr}';
			$bubultab .= '{/table}';
			

			$tp = round(($punknown * $t / 100 ),2);
			if($punknown > 0){
				$end = $h["date_start"] + $tp + 5000;
				$buffer .= '<event ';
				$buffer .= ' start="' .create_date_timeline_format($start) . ' GMT"';
				$buffer .= ' end="' . create_date_timeline_format($end). ' GMT"';
				$buffer .= ' color="#' . $colorUNKNOWN . '"';
				$buffer .= ' isDuration="true" ';
				$buffer .= ' title= "' . (($punknown > 0) ? $punknown : "0") . '%" >' ;
//				$buffer .= '~br~ UnknownTime: ' . Duration::toString($unknowntime);
				$buffer .= $bubultab;
				$buffer .= '</event>';	
			}

			$tp = round(($ppending * $t / 100 ),2);
			if($ppending > 0){
				$end = $h["date_start"] + $tp + 5000;
				$buffer .= '<event ';
				$buffer .= ' start="' .create_date_timeline_format($start) . ' GMT"';
				$buffer .= ' end="' . create_date_timeline_format($end). ' GMT"';
				$buffer .= ' color="#cccccc"';
				$buffer .= ' isDuration="true" ';
				$buffer .= ' title= "' . (($ppending > 0) ? $ppending : "0") . '%" >' ;
				$buffer .= $bubultab;
//				$buffer .= '~br~ UndeterminatedTime: ' . Duration::toString($pendingtime);		
				$buffer .= '</event>';	
			}

			$tp = round(($pwarning * $t / 100 ),2);
			if($pwarning > 0){
				$end = $h["date_start"] + $tp + 5000;
			$buffer .= '<event ';
			$buffer .= ' start="' .create_date_timeline_format($start) . ' GMT"';
			$buffer .= ' end="' . create_date_timeline_format($end). ' GMT"';
			$buffer .= ' color="#' . $colorWARNING . '"';
			$buffer .= ' isDuration="true" ';
			$buffer .= ' title= "' . (($pwarning > 0) ? $pwarning : "0") . '%" >' ;
			$buffer .= $bubultab;
//			$buffer .= '~br~ WarningTime: ' . Duration::toString($warningtime);		
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
//				$buffer .= '~br~ CriticalTime: ' . Duration::toString($criticaltime);
				$buffer .= $bubultab;
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
				$buffer .= $bubultab;
//				$buffer .= '~br~ Oktime: ' . Duration::toString($oktime);		
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


	#
	## make bubul
	#
	$bubultab = '{table class=bubultab}';
	$bubultab .= '{tr}{td class=bubuleft colspan=3}Day: '. date("d/m/Y", $start) .' --  Duration: '.Duration::toString($t).'{/td}{td class=bubuleft }Alert{/td}{/tr}';
	$bubultab .= '{tr}{td class=bubuleft style="background:#'.$colorOK.';"  }OK:{/td}{td class=bubul}'.Duration::toString($_GET["today_ok"] * $t / 100) .'{/td}{td class=bubul}'.(($_GET["today_ok"] > 0) ? $_GET["today_ok"] : "0").'%{/td}{td class=bubul}'.$_GET["today_OKnbEvent"].'{/td}{/tr}';
	$bubultab .= '{tr}{td class=bubuleft style="background:#'.$colorCRITICAL.';"  }Critical:{/td}{td class=bubul}'.Duration::toString($_GET["today_critical"] * $t / 100) .'{/td}{td class=bubul}'.(($_GET["today_critical"] > 0) ? $_GET["today_critical"] : "0").'%{/td}{td class=bubul}'.$_GET["today_CRITICALnbEvent"].'{/td}{/tr}';
	$bubultab .= '{tr}{td class=bubuleft style="background:#'.$colorWARNING.';"  }Warning:{/td}{td class=bubul}'. Duration::toString($_GET["today_warning"] * $t / 100) .'{/td}{td class=bubul}'.(($_GET["today_warning"] > 0) ? $_GET["today_warning"] : "0").'%{/td}{td class=bubul}'.$_GET["today_WARNINGnbEvent"].'{/td}{/tr}';
	$bubultab .= '{tr}{td class=bubuleft style="background:#'.$colorUNKNOWN.';"  }Unknown:{/td}{td class=bubul}'. Duration::toString($_GET["today_unknown"] * $t / 100) .'{/td}{td class=bubul}'.(($_GET["today_unknown"] > 0) ? $_GET["today_unknown"] : "0").'%{/td}{td class=bubul}'.$_GET["today_UNKNOWNnbEvent"].'{/td}{/tr}';
	$bubultab .= '{tr}{td class=bubuleft style="background:#cccccc;"  }Undeterminated:{/td}{td class=bubul}'.  Duration::toString($_GET["today_pending"] * $t / 100) .'{/td}{td class=bubul}'.(($_GET["today_pending"] > 0) ? $_GET["today_pending"] : "0").'%{/td}{/tr}';
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
	$tp = round(($_GET["today_unknown"] * $t / 100 ),2);
	if($_GET["today_unknown"] > 0){
		$end = $today_start + $tp + 5000;
		$buffer .= '<event ';
		$buffer .= ' start="' .create_date_timeline_format($start) . ' GMT"';
		$buffer .= ' end="' . create_date_timeline_format($end). ' GMT"';
		$buffer .= ' color="#' . $colorUNKNOWN . '"';
		$buffer .= ' isDuration="true" ';
		$buffer .= ' title= "' . $_GET["today_unknown"] . '%" >' ;
		$buffer .= $bubultab;
		$buffer .= '</event>';
	}
	$tp = round(($_GET["today_warning"] * $t / 100 ),2);
	if($_GET["today_warning"] > 0){
		$end = $today_start + $tp + 5000;
		$buffer .= '<event ';
		$buffer .= ' start="' .create_date_timeline_format($start) . ' GMT"';
		$buffer .= ' end="' . create_date_timeline_format($end). ' GMT"';
		$buffer .= ' color="#' . $colorWARNING . '"';
		$buffer .= ' isDuration="true" ';
		$buffer .= ' title= "' . $_GET["today_warning"] . '%" >' ;
		$buffer .= $bubultab;
		$buffer .= '</event>';
	}
	
	$tp = round(($_GET["today_critical"] * $t / 100 ),2);
	if($_GET["today_critical"] > 0){
		$end = $today_start + $tp + 5000;
		$buffer .= '<event ';
		$buffer .= ' start="' .create_date_timeline_format($start) . ' GMT"';
		$buffer .= ' end="' . create_date_timeline_format($end). ' GMT"';
		$buffer .= ' color="#' . $colorCRITICAL . '"';
		$buffer .= ' isDuration="true" ';
		$buffer .= ' title= "' . $_GET["today_critical"] . '%" >' ;
		$buffer .= $bubultab;
		$buffer .= '</event>';
	}

	$tp = round(($_GET["today_ok"] * $t / 100 ),2);
	if($_GET["today_ok"] > 0){
		$end = $today_start + $tp + 5000;
		$buffer .= '<event ';
		$buffer .= ' start="' .create_date_timeline_format($start) . ' GMT"';
		$buffer .= ' end="' . create_date_timeline_format($end). ' GMT"';
		$buffer .= ' color="#' . $colorOK . '"';
		$buffer .= ' isDuration="true" ';
		$buffer .= ' title= "' . $_GET["today_ok"] . '%" >' ;
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
