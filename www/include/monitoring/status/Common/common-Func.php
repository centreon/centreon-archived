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

	function getServiceObjectId($svc_description, $host_name, $monObj)
    {
        static $hostSvcTab = array();

 		if (!isset($hostSvcTab[$host_name])) {
     	    $rq = "SELECT s.service_object_id as service_id, s.display_name as service_description ".
     	    	  "FROM ".$monObj->ndoPrefix. "services s, ".$monObj->ndoPrefix."hosts h " .
     	          "WHERE s.host_object_id = h.host_object_id " .
     	          "AND h.display_name LIKE '" . $monObj->DBNdo->escape($host_name) . "' ";
     		$res = $monObj->DBNdo->query($rq);
     		$hostSvcTab[$host_name] = array();
     		while ($row = $res->fetchRow()) {
     		    $hostSvcTab[$host_name][$row['service_description']] = $row['service_id'];
     		}
 		}
 		if (isset($hostSvcTab[$host_name]) && isset($hostSvcTab[$host_name][$svc_description])) {
 		    return $hostSvcTab[$host_name][$svc_description];
 		}
 		return null;
    }

	function get_Host_Status($host_name, $pearDBndo, $general_opt){
		global $ndo_base_prefix;

		$ndo_base_prefix = "nagios_";

		 $rq = "SELECT nhs.current_state FROM `" .$ndo_base_prefix."hoststatus` nhs, `" .$ndo_base_prefix."hosts` nh " .
 	            "WHERE nh.display_name = '".$host_name."'" .
 	            "AND nh.host_object_id = nhs.host_object_id" ;
		$DBRESULT = $pearDBndo->query($rq);
		$status = $DBRESULT->fetchRow();
		unset($DBRESULT);
		return $status["current_state"];
	}

	function getMyIndexGraph4Service($host_name = NULL, $service_description = NULL, $pearDBO)	{
		if ((!isset($service_description) || !$service_description ) || (!isset($host_name) || !$host_name))
			return NULL;

		$DBRESULT = $pearDBO->query("SELECT id FROM index_data i, metrics m WHERE i.host_name = '".$host_name."' " .
									"AND m.hidden = '0' " .
									"AND i.service_description = '".$service_description."' " .
									"AND i.id = m.index_id");
		if ($DBRESULT->numRows())	{
			$row = $DBRESULT->fetchRow();
			return $row["id"];
		}
		return 0;
	}

?>