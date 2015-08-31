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

ini_set("display_errors", "Off");

/**
 * Include configuration
 */
include_once "@CENTREON_ETC@/centreon.conf.php";

/**
 * Include Classes / Methods
 */
include_once $centreon_path . "www/class/centreonDB.class.php";
include_once $centreon_path . "www/include/common/common-Func.php";

/** *****************************************
 * Connect MySQL DB
 */
$pearDB 	= new CentreonDB();
$pearDBO 	= new CentreonDB("centstorage");

/**
 * Security check
 */
(isset($_GET["sid"])) ? $sid = htmlentities($_GET["sid"], ENT_QUOTES, "UTF-8") : $sid = "-1";

/**
 * Check Session ID
 */
if (isset($sid)){
    $sid = htmlentities($sid, ENT_QUOTES, "UTF-8");
    $res = $pearDB->query("SELECT * FROM session WHERE session_id = '".$sid."'");
    if ($session = $res->fetchRow()) {
        $_POST["sid"] = $sid;
    } else
        get_error('bad session id');
} else {
    get_error('need session identifiant !');
}

/**
 * save of the XML flow in $flow
 */
$csv_flag = 1; //setting the csv_flag variable to change limit in SQL request of getODSXmlLog.php when CSV exporting
ob_start();
require_once $centreon_path."www/include/eventLogs/GetXmlLog.php";
$flow = ob_get_contents();
ob_end_clean();

$nom = "EventLog";

/**
 * Send Headers
 */

header("Content-Type: application/csv-tab-delimited-table");
header("Content-disposition: filename=".$nom.".csv");
header("Cache-Control: cache, must-revalidate");
header("Pragma: public");

/**
 * Read flow
 */
$xml = new SimpleXMLElement($flow);

echo _("Begin date")."; "._("End date").";\n";
echo date('d/m/y (H:i:s)', intval($xml->infos->start)).";".date('d/m/y (H:i:s)', intval($xml->infos->end))."\n";
echo "\n";

echo _("Type").";"._("Notification").";"._("Alert").";"._("error")."\n";
echo ";".$xml->infos->notification.";".$xml->infos->alert.";".$xml->infos->error."\n";
echo "\n";

echo _("Host").";"._("Up").";"._("Down").";"._("Unreachable")."\n";
echo ";".$xml->infos->up.";".$xml->infos->down.";".$xml->infos->unreachable."\n";
echo "\n";

echo _("Service").";"._("Ok").";"._("Warning").";"._("Critical").";"._("Unknown")."\n";
echo ";".$xml->infos->ok.";".$xml->infos->warning.";".$xml->infos->critical.";".$xml->infos->unknown."\n";
echo "\n";

echo _("Day").";"._("Time").";"._("Host").";"._("Address").";"._("Service").";"._("Status").";"._("Type").";"._("Retry").";"._("Output").";"._("Contact").";"._("Cmd")."\n";
foreach ($xml->line as $line) {
    echo $line->date.";".$line->time.";".$line->host_name.";".$line->address.";".$line->service_description.";".$line->status.";".$line->type.";".$line->retry.";".$line->output.";".$line->contact.";".$line->contact_cmd."\n";
}
