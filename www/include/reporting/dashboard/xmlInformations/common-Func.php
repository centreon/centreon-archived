<?php
/*
 * Copyright 2005-2009 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 * 
 * This program is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software 
 * Foundation ; either version 2 of the License.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with 
 * this program; if not, see <http://www.gnu.org/licenses>.
 * 
 * Linking this program statically or dynamically with other modules is making a 
 * combined work based on this program. Thus, the terms and conditions of the GNU 
 * General Public License cover the whole combination.
 * 
 * As a special exception, the copyright holders of this program give MERETHIS 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of MERETHIS choice, provided that 
 * MERETHIS also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 * SVN : $URL$
 * SVN : $Id$
 * 
 */
 	
	require_once "@CENTREON_ETC@/centreon.conf.php";
	require_once $centreon_path."www/class/centreonDB.class.php";
	
	/* Translation */
	require_once ($centreon_path . "www/class/Session.class.php");
	require_once ($centreon_path . "www/class/centreon.class.php");
	require_once ($centreon_path . "www/class/centreonLang.class.php");
	
	
	CentreonSession::start();
	$oreon =& $_SESSION["oreon"];
	$centreonLang = new CentreonLang($centreon_path, $oreon);
	$centreonLang->bindLang();
	
	
	/*
	 * Create a XML node for each day stats (in $row) for a service, a servicegroup, an host or an hostgroup
	 */
	function fillBuffer($statesTab, $row, $color) {
		global $buffer;
		
		$statTab = array();
		$totalTime = 0;
		$sumTime = 0;
		foreach ($statesTab as $key => $value) {
			if (isset($row[$value."TimeScheduled"])) {
				$statTab[$value."_T"] = $row[$value."TimeScheduled"];
				$totalTime += $row[$value."TimeScheduled"];
			} else
				$statTab[$value."_T"] = 0;
			if (isset($row[$value."nbEvent"]))
				$statTab[$value."_A"] = $row[$value."nbEvent"];
			else
				$statTab[$value."_A"] = 0;
			
		}
		$date_start = $row["date_start"];
		$date_end = $row["date_end"];
		foreach ($statesTab as $key => $value) {
			if ($totalTime)
				$statTab[$value."_MP"] = round(($statTab[$value."_T"] / ($totalTime) * 100),2);
			else
				$statTab[$value."_MP"] = 0;
		}
	
		/*
		 * Popup generation for each day
		 */
		$Day = _("Day");
        $Duration = _("Duration");
        $Alert = _("Alert");
		$detailPopup = '{table class=bulleDashtab}';
	 	$detailPopup .= '{tr}{td class=bulleDashleft colspan=3}'.$Day.': '. date("d/m/Y", $date_start) .' --  '.$Duration.': '.CentreonDuration::toString($totalTime).'{/td}{td class=bulleDashleft }'.$Alert.'{/td}{/tr}';
		foreach($statesTab as $key => $value) {
			$detailPopup .= '	{tr}' .
							'		{td class=bulleDashleft style="background:'.$color[$value].';"  }'._($value).':{/td}' .
							'		{td class=bulleDash}'. CentreonDuration::toString($statTab[$value."_T"]) .'{/td}' .
							'		{td class=bulleDash}'.$statTab[$value."_MP"].'%{/td}'.
							'		{td class=bulleDash}'.$statTab[$value."_A"].'{/td}';
			$detailPopup .= '	{/tr}';
		}
		$detailPopup .= '{/table}';
	
		$t = $totalTime;
		$t = round(($t - ($t * 0.11574074074)),2);
		
		foreach ($statesTab as $key => $value) {
			if ($statTab[$value."_MP"] > 0){
				$day = date("d", $date_start);
				$year = date("Y", $date_start);
				$month = date("m", $date_start);
				$start = mktime(0, 0, 0, $month, $day, $year);
				$start += ($statTab[$value."_T"]/100*2);
				$end = $start + ($statTab[$value."_T"]/100*96);
				$buffer->startElement("event");
				$buffer->writeAttribute("start", create_date_timeline_format($start) . " GMT");
				$buffer->writeAttribute("end", create_date_timeline_format($end). " GMT");
				$buffer->writeAttribute("color", $color[$value]);
				$buffer->writeAttribute("isDuration", "true");				
				$buffer->writeAttribute("title", $statTab[$value."_MP"] . "%");				
				$buffer->text($detailPopup, false);
				$buffer->endElement();
			}
		}		
	}
?>