<?php
/*
 * Copyright 2005-2011 MERETHIS
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

	$DBRESULT = $pearDB->query("SELECT * FROM session WHERE session_id = '" . htmlentities($_GET['session'], ENT_QUOTES, "UTF-8") . "'");
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