<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */
	
	require_once "@CENTREON_ETC@/centreon.conf.php";
	
	require_once $centreon_path."www/include/reporting/dashboard/common-Func.php";
	require_once $centreon_path."www/class/centreonDuration.class.php";
	require_once $centreon_path."www/class/centreonXML.class.php";
	require_once $centreon_path."www/class/centreonDB.class.php";
	require_once $centreon_path."www/include/reporting/dashboard/xmlInformations/common-Func.php";
		
	$buffer = new CentreonXML();
	$buffer->startElement("data");	

	$pearDB 	= new CentreonDB();
	$pearDBO 	= new CentreonDB("centstorage");

    $sid = session_id();
    
	$DBRESULT = $pearDB->query("SELECT * FROM session WHERE session_id = '" . $pearDB->escape($sid) . "'");
	if (!$DBRESULT->numRows())
		exit();
	

	/*
	 * Initiate Table
	 */
	$state 		= array("OK" => _("OK"), "WARNING" => _("WARNING"), "CRITICAL" => _("CRITICAL"), "UNKNOWN" => _("UNKNOWN"), "UNDETERMINED" => _("UNDETERMINED"));
	$statesTab 	= array("OK", "WARNING", "CRITICAL", "UNKNOWN");
	
	if (isset($_GET["host_id"]) && isset($_GET["id"]) && isset($_GET["color"])){
		
		$color = array();
		foreach ($_GET["color"] as $key => $value) {
			$color[$key] = htmlentities($value, ENT_QUOTES, "UTF-8");
		}
	
		$DBRESULT = $pearDBO->query("SELECT  * FROM `log_archive_service` WHERE host_id = '".$pearDBO->escape($_GET["host_id"])."' AND service_id = ".$pearDBO->escape($_GET["id"])." ORDER BY `date_start` DESC");
		while ($row = $DBRESULT->fetchRow()) {
			fillBuffer($statesTab, $row, $color);
		}
		$DBRESULT->free();
		
	} else {
		$buffer->writeElement("error", "error");		
	}
	$buffer->endElement();
	
	header('Content-Type: text/xml');
	$buffer->output();
?>
