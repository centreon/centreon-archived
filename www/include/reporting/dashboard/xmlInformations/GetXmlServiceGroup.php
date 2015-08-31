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
    require_once $centreon_path."www/include/reporting/dashboard/DB-Func.php";
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
	
	if (isset($_GET["id"]) && isset($_GET["color"])){
		$color = array();
		foreach ($_GET["color"] as $key => $value) {
			$color[$key] = htmlentities($value, ENT_QUOTES, "UTF-8");
		}
		
		$services = getServiceGroupActivateServices($_GET["id"]);
        if (count($services) > 0) {
            $host_ids = array();
            $service_ids = array();
            foreach ($services as $host_service_id => $host_service_name) {
                $res = explode("_", $host_service_id);
                $host_ids[$res[0]] = 1;
                $service_ids[$res[1]] = 1;
            }

            $request =  'SELECT ' .
                            'date_start, date_end, OKnbEvent, CRITICALnbEvent, WARNINGnbEvent, UNKNOWNnbEvent, ' .
                            'avg( `OKTimeScheduled` ) as "OKTimeScheduled", ' .
                            'avg( `WARNINGTimeScheduled` ) as "WARNINGTimeScheduled", ' .
                            'avg( `UNKNOWNTimeScheduled` ) as "UNKNOWNTimeScheduled", ' .
                            'avg( `CRITICALTimeScheduled` ) as "CRITICALTimeScheduled", ' .
                            'avg( `UNDETERMINEDTimeScheduled` ) as "UNDETERMINEDTimeScheduled" ' .
                            'FROM `log_archive_service` WHERE `host_id` IN (' . implode(',', array_keys($host_ids)) . ') AND `service_id` IN (' . implode(',', array_keys($service_ids)) . ') group by date_end, date_start order by date_start desc';
            $res = $pearDBO->query($request);
            while ($row = $res->fetchRow()) {
                fillBuffer($statesTab, $row, $color);
            }
            $DBRESULT->free();
		}
	} else {
		$buffer->writeElement("error", "error");		
	}
	$buffer->endElement();	

	header('Content-Type: text/xml');
	$buffer->output();
?>
