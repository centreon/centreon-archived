<?php
/*
 * Copyright 2005-2016 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

require_once realpath(dirname(__FILE__) . "/../../../../../config/centreon.config.php");
require_once _CENTREON_PATH_ . "www/class/centreonDB.class.php";
include_once _CENTREON_PATH_ . "www/include/common/common-Func.php";
include_once _CENTREON_PATH_ . "www/include/reporting/dashboard/common-Func.php";
require_once _CENTREON_PATH_ . "www/class/centreonUser.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonSession.class.php";
require_once _CENTREON_PATH_ . "www/class/centreon.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonDuration.class.php";
include_once _CENTREON_PATH_ . "www/include/reporting/dashboard/DB-Func.php";

session_start();
session_write_close();

/*
 * DB connexion
 */
$pearDB    = new CentreonDB();
$pearDBO    = new CentreonDB("centstorage");

$sid = session_id();
if (!empty($sid) && isset($_SESSION['centreon'])) {
    $oreon = $_SESSION['centreon'];
    $query = "SELECT user_id FROM session WHERE user_id = '".$pearDB->escape($oreon->user->user_id)."'";
    $res = $pearDB->query($query);
    if (!$res->numRows()) {
        get_error('bad session id');
    }
} else {
    get_error('need session id!');
}

/*
 * getting host and service id
 */
isset($_GET["host"]) ? $host_id =  htmlentities($_GET["host"], ENT_QUOTES, "UTF-8") : $host_id = "NULL";
isset($_POST["host"]) ? $host_id =  htmlentities($_POST["host"], ENT_QUOTES, "UTF-8") : $host_id;
isset($_GET["service"]) ? $service_id =  htmlentities($_GET["service"], ENT_QUOTES, "UTF-8") : $service_id = "NULL";
isset($_POST["service"]) ? $service_id =  htmlentities($_POST["service"], ENT_QUOTES, "UTF-8") : $service_id;

/*
 * Getting time interval to report
 */
$dates = getPeriodToReport();
$start_date =  htmlentities($_GET['start'], ENT_QUOTES, "UTF-8");
$end_date =  htmlentities($_GET['end'], ENT_QUOTES, "UTF-8");
$host_name = getHostNameFromId($host_id);
$service_description = getServiceDescriptionFromId($service_id);

/*
 * file type setting
 */

header("Cache-Control: public");
header("Pragma: public");
header("Content-Type: application/octet-stream");
header("Content-disposition: attachment ; filename=".$host_name. "_" .$service_description.".csv");

echo _("Host").";"._("Service").";"._("Begin date")."; "._("End date")."; "._("Duration")."\n";
echo $host_name."; ".$service_description."; ".date(_("d/m/Y H:i:s"), $start_date)."; "
    . date(_("d/m/Y H:i:s"), $end_date)."; ".($end_date - $start_date)."s\n";
echo "\n";

echo _("Status").";"._("Time").";"._("Total Time").";"._("Mean Time")."; "._("Alert")."\n";
$reportingTimePeriod = getreportingTimePeriod();
$serviceStats = getLogInDbForOneSVC($host_id, $service_id, $start_date, $end_date, $reportingTimePeriod);
echo "OK;".$serviceStats["OK_T"]."s;".$serviceStats["OK_TP"]."%;"
    . $serviceStats["OK_MP"]. "%;".$serviceStats["OK_A"].";\n";
echo "WARNING;".$serviceStats["WARNING_T"]."s;".$serviceStats["WARNING_TP"]."%;"
    . $serviceStats["WARNING_MP"]. "%;".$serviceStats["WARNING_A"].";\n";
echo "CRITICAL;".$serviceStats["CRITICAL_T"]."s;".$serviceStats["CRITICAL_TP"]."%;"
    . $serviceStats["CRITICAL_MP"]. "%;".$serviceStats["CRITICAL_A"].";\n";
echo "UNKNOWN;".$serviceStats["UNKNOWN_T"]."s;".$serviceStats["UNKNOWN_TP"]."%;"
    . $serviceStats["UNKNOWN_MP"]. "%;".$serviceStats["UNKNOWN_A"].";\n";
echo "UNDETERMINED;".$serviceStats["UNDETERMINED_T"]."s;"
    . $serviceStats["UNDETERMINED_TP"]."%;;;\n";
echo "\n";
echo "\n";

/*
 * Getting evolution of service stats in time
 */
echo _("Day").";"._("Duration").";"
               ._("OK")." "._("Time")."; "._("OK")."; "._("OK")." Alert;"
               ._("Warning")." "._("Time")."; "._("Warning").";"._("Warning")." Alert;"
               ._("Unknown")." "._("Time")."; "._("Unknown").";"._("Unknown")." Alert;"
               ._("Critical")." "._("Time")."; "._("Critical").";"._("Critical")." Alert;"
               ._("Day").";\n";

$request = "SELECT  * FROM `log_archive_service` " .
        "WHERE `host_id` = '".$host_id."' " .
        "AND `service_id` = '".$service_id."' " .
        "AND `date_start` >= '".$start_date."' " .
        "AND `date_end` <= '".$end_date."' " .
        "ORDER BY `date_start` DESC";
$DBRESULT = $pearDBO->query($request);
while ($row = $DBRESULT->fetchRow()) {
    $duration = $row["date_end"] - $row["date_start"];
    /* Percentage by status */
    $duration = $row["OKTimeScheduled"]
        + $row["WARNINGTimeScheduled"]
        + $row["UNKNOWNTimeScheduled"]
        + $row["CRITICALTimeScheduled"];
    $row["OK_MP"] = round($row["OKTimeScheduled"] * 100 / $duration, 2);
    $row["WARNING_MP"] = round($row["WARNINGTimeScheduled"] * 100 / $duration, 2);
    $row["UNKNOWN_MP"] = round($row["UNKNOWNTimeScheduled"] * 100 / $duration, 2);
    $row["CRITICAL_MP"] = round($row["CRITICALTimeScheduled"] * 100 / $duration, 2);
    echo $row["date_start"].";".$duration.";"
        . $row["OKTimeScheduled"]."s;".$row["OK_MP"]."%;".$row["OKnbEvent"].";"
        . $row["WARNINGTimeScheduled"]."s;".$row["WARNING_MP"]."%;".$row["WARNINGnbEvent"].";"
        . $row["UNKNOWNTimeScheduled"]."s;".$row["UNKNOWN_MP"]."%;".$row["UNKNOWNnbEvent"].";"
        . $row["CRITICALTimeScheduled"]."s;".$row["CRITICAL_MP"]."%;".$row["CRITICALnbEvent"].";"
        . date("Y-m-d H:i:s", $row["date_start"]).";\n";
}
$DBRESULT->free();
