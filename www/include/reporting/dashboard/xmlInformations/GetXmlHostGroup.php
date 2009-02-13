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
 * SVN : $URL
 * SVN : $Id: GetXmlHostGroup.php 7139 2008-11-24 17:19:45Z jmathis $
 * 
 */
 
	
	require_once "@CENTREON_ETC@/centreon.conf.php";
	
	require_once $centreon_path.'www/include/reporting/dashboard/common-Func.php';
	require_once $centreon_path.'www/class/other.class.php';
	require_once $centreon_path.'www/class/centreonXML.class.php';
	require_once $centreon_path.'www/include/reporting/dashboard/xmlInformations/common-Func.php';
	/*
	 * Definition of status
	 */
	$state["UP"] = _("UP");
	$state["DOWN"] = _("DOWN");
	$state["UNREACHABLE"] = _("UNREACHABLE");
	$state["UNDETERMINED"] = _("UNDETERMINED");
	
	$buffer = new CentreonXML();
	$buffer->startElement("data");	
	
	if (isset($_GET["id"]) && isset($_GET["color"])){
		$color = array();
		$get_color = $_GET["color"];
		foreach ($get_color as $key => $value)
			$color[$key] = $value;

		$pearDBO = getCentStorageConnection();
		$pearDB = getCentreonConnection();
		$str = "";
		$request = "SELECT host_host_id FROM `hostgroup_relation` WHERE `hostgroup_hg_id` = '" . $_GET["id"] ."'";
		$DBRESULT = & $pearDB->query($request);
		while ($hg =& $DBRESULT->fetchRow()) {
			if ($str != "")
				$str .= ", ";
			$str .= $hg["host_host_id"]; 
		}
		unset($hg);
		unset($DBRESULT);
		
		$rq = 'SELECT `date_start`, `date_end`, sum(`UPnbEvent`) as UPnbEvent, sum(`DOWNnbEvent`) as DOWNnbEvent, sum(`UNREACHABLEnbEvent`) as UNREACHABLEnbEvent, ' .
				'avg( `UPTimeScheduled` ) as "UPTimeScheduled", '.
				'avg( `DOWNTimeScheduled` ) as "DOWNTimeScheduled", ' .
				'avg( `UNREACHABLETimeScheduled` ) as "UNREACHABLETimeScheduled", ' .
				'avg( `UNDETERMINEDTimeScheduled` ) as "UNDETERMINEDTimeScheduled" ' .
				'FROM `log_archive_host` WHERE `host_id` IN ('.$str.') GROUP BY date_end, date_start ORDER BY date_start desc';
		$DBRESULT = & $pearDBO->query($rq);
			$statesTab = array("UP", "DOWN", "UNREACHABLE");
		while ($row =& $DBRESULT->fetchRow()) {
			fillBuffer($statesTab, $row, $color);
		  }
	} else	{
		$buffer->writeElement("error", "error");
	}
	$buffer->endElement();	
	header('Content-Type: text/xml');
	$buffer->output();
?>