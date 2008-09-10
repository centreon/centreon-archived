<?php
/*
 * Created on 1 sept. 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
	require_once 'DB.php';
	require_once("@CENTREON_ETC@/centreon.conf.php");
	 /*
	  * returns a connection to centstorage database
	  */
	function getCentStorageConnection() { 
		global $conf_centreon;
		$dsn = array('phptype'  => 'mysql',
			     	'username' => $conf_centreon['user'],
			     	'password' => $conf_centreon['password'],
			     	'hostspec' => $conf_centreon['hostCentstorage'],
			     	'database' => $conf_centreon['dbcstg']);
		$options = array('debug'       => 2,
				 		'portability' => DB_PORTABILITY_ALL ^ DB_PORTABILITY_LOWERCASE,);
		$pearDB =& DB::connect($dsn, $options);
		if (PEAR::isError($pearDB)) 
		  die("Connecting probems with oreon database : " . $pearDB->getMessage());		
		$pearDB->setFetchMode(DB_FETCHMODE_ASSOC);
		return $pearDB;
	}			
	/*
	 * returns a connection to centreon database
	 */
	function getCentreonConnection() {
		global $conf_centreon;
		$dsn = array('phptype'  => 'mysql',
			     	'username' => $conf_centreon['user'],
			     	'password' => $conf_centreon['password'],
			     	'hostspec' => $conf_centreon['hostCentreon'],
			     	'database' => $conf_centreon['db']);
     	$options = array('debug'       => 2,
	 		'portability' => DB_PORTABILITY_ALL ^ DB_PORTABILITY_LOWERCASE,);
		$pearDBO =& DB::connect($dsn, $options);
		if (PEAR::isError($pearDBO)) 
		  die("Connecting probems with centstorage database : " . $pearDBO->getMessage());		
		$pearDBO->setFetchMode(DB_FETCHMODE_ASSOC);
		return $pearDBO;
	 }
	/*
	 * Create a XML node for each day stats (in $row) for a service, a servicegroup, an host or an hostgroup
	 */
	function fillBuffer($statesTab, $row, $color, $buffer) {
		$statTab = array();
		$totalTime = 0;
		$sumTime = 0;
		foreach($statesTab as $key => $value) {
			if (isset($row[$value."TimeScheduled"])) {
				$statTab[$value."_T"] = $row[$value."TimeScheduled"];
				$totalTime += $row[$value."TimeScheduled"];
			}else
				$statTab[$value."_T"] = 0;
			if (isset($row[$value."nbEvent"]))
				$statTab[$value."_A"] = $row[$value."nbEvent"];
			else
				$statTab[$value."_A"] = 0;
			
		}
		$date_start = $row["date_start"];
		$date_end = $row["date_end"];
		foreach($statesTab as $key => $value) {
			if ($totalTime)
				$statTab[$value."_MP"] = round(($statTab[$value."_T"] / ($totalTime) * 100),2);
			else
				$statTab[$value."_MP"] = 0;
		}
		/*
		 * Popup generation for each day
		 */
		$detailPopup = '{table class=bulleDashtab}';
		$detailPopup .= '	{tr}{td class=bulleDashleft colspan=3}Day: '. date("d/m/Y", $date_start) .' --  Duration: '.Duration::toString($totalTime).'{/td}{td class=bulleDashleft }Alert{/td}{/tr}';
		foreach($statesTab as $key => $value) {
			$detailPopup .= '	{tr}' .
							'		{td class=bulleDashleft style="background:'.$color[$value].';"  }'._($value).':{/td}' .
							'		{td class=bulleDash}'. Duration::toString($statTab[$value."_T"]) .'{/td}' .
							'		{td class=bulleDash}'.$statTab[$value."_MP"].'%{/td}'.
							'		{td class=bulleDash}'.$statTab[$value."_A"].'{/td}';
			$detailPopup .= '	{/tr}';
		}
		$detailPopup .= '{/table}';
	
		$t = $totalTime;
		$t = round(($t - ($t * 0.11574074074)),2);
		
		foreach($statesTab as $key => $value) {
			if ($statTab[$value."_MP"] > 0){
				$day = date("d", $date_start);
				$year = date("Y", $date_start);
				$month = date("m", $date_start);
				$start = mktime(0, 0, 0, $month, $day, $year);
				$start += ($statTab[$value."_T"]/100*2);
				$end = $start + ($statTab[$value."_T"]/100*96);
				$buffer .= '<event ';
				$buffer .= '	start="' .create_date_timeline_format($start) . ' GMT"';
				$buffer .= '	end="' . create_date_timeline_format($end). ' GMT"';
				$buffer .= '	color="'.$color[$value].'"';
				$buffer .= '	isDuration="true" ';
				$buffer .= '	title= "'.$statTab[$value."_MP"].'%" >' ;
				$buffer .= '	'.$detailPopup;
				$buffer .= '</event>';	
			}
		}
		return $buffer;
	}
?>
