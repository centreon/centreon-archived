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

include_once("@CENTREON_ETC@/centreon.conf.php");

require_once $centreon_path . "/www/class/centreonDB.class.php";
require_once $centreon_path . "/www/class/centreonXML.class.php";

/** ************************************
 * start init db
 */
$pearDB = new CentreonDB();

$mnftr_id = $pearDB->escape($_POST["mnftr_id"]);

if (false === is_numeric($mnftr_id)) {
    header('HTTP/1.1 406 Not Acceptable');
    exit();
}


/** ************************************
 * start XML Flow
 */
$buffer = new CentreonXML();
$buffer->startElement("traps");

$empty = 0;
if (isset($_POST["mnftr_id"])){
    $traps = array();
    if ($_POST["mnftr_id"] == -1) {
        $DBRESULT = $pearDB->query("SELECT traps_id, traps_name FROM traps ORDER BY traps_name");
    } else if ($_POST["mnftr_id"] == -2) {
        $empty = 1;
    } else if ($_POST["mnftr_id"] != 0) {
        $DBRESULT = $pearDB->query("SELECT traps_id, traps_name FROM traps WHERE manufacturer_id = " . $mnftr_id . " ORDER BY traps_name");
    }

    if ($empty != 1) {
        while ($trap = $DBRESULT->fetchRow()){
            $buffer->startElement("trap");
            $buffer->writeElement("id", $trap["traps_id"]);
            $buffer->writeElement("name", $trap["traps_name"]);
            $buffer->endElement();
        }
        $DBRESULT->free();
    }
} else {
    $buffer->writeElement("error", "mnftr_id not found");
}
$buffer->endElement();

header('Content-Type: text/xml');
$buffer->output();
