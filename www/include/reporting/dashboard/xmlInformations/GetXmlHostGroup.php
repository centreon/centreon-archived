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
	 * Definition of status
	 */
	$state 		= array("UP" => _("UP"), "DOWN" => _("DOWN"), "UNREACHABLE" => _("UNREACHABLE"), "UNDETERMINED" => _("UNDETERMINED"));
	$statesTab 	= array("UP", "DOWN", "UNREACHABLE");
	
	$buffer = new CentreonXML();
	$buffer->startElement("data");	
	
	if (isset($_GET["id"]) && isset($_GET["color"])){
		
		$color = array();
		foreach ($_GET["color"] as $key => $value) {
			$color[$key] = htmlentities($value, ENT_QUOTES, "UTF-8");
		}
		
		$hosts_id = $oreon->user->access->getHostHostGroupAclConf($_GET["id"], $oreon->broker->getBroker());
        if (count($hosts_id) > 0) {
            $rq = 'SELECT `date_start`, `date_end`, sum(`UPnbEvent`) as UPnbEvent, sum(`DOWNnbEvent`) as DOWNnbEvent, sum(`UNREACHABLEnbEvent`) as UNREACHABLEnbEvent, ' .
                    'avg( `UPTimeScheduled` ) as "UPTimeScheduled", '.
                    'avg( `DOWNTimeScheduled` ) as "DOWNTimeScheduled", ' .
                    'avg( `UNREACHABLETimeScheduled` ) as "UNREACHABLETimeScheduled", ' .
                    'avg( `UNDETERMINEDTimeScheduled` ) as "UNDETERMINEDTimeScheduled" ' .
                    'FROM `log_archive_host` WHERE `host_id` IN (' . implode(',', array_keys($hosts_id)) . ') GROUP BY date_end, date_start ORDER BY date_start desc';
            $DBRESULT = $pearDBO->query($rq);
            while ($row = $DBRESULT->fetchRow()) {
                fillBuffer($statesTab, $row, $color);
            }
            $DBRESULT->free();
		}
	} else	{
		$buffer->writeElement("error", "error");
	}
	$buffer->endElement();	
	
	header('Content-Type: text/xml');
	$buffer->output();
?>
