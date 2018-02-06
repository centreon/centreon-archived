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
 * SVN : $URL$
 * SVN : $Id$
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

/*
 * DB Connexion
 */
$pearDB    = new CentreonDB();
$pearDBO    = new CentreonDB("centstorage");

if (!isset($_SESSION["centreon"])) {
    CentreonSession::start();
    if (!CentreonSession::checkSession(session_id(), $pearDB)) {
        print "Bad Session";
        exit();
    }
}

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

$centreon = $oreon;

isset($_GET["servicegroup"]) ? $id = htmlentities($_GET["servicegroup"], ENT_QUOTES, "UTF-8") : $id = "NULL";
isset($_POST["servicegroup"]) ? $id = htmlentities($_POST["servicegroup"], ENT_QUOTES, "UTF-8") : $id = $id;

/*
 * Getting time interval to report
 */
$dates = getPeriodToReport();
$start_date = htmlentities($_GET['start'], ENT_QUOTES, "UTF-8");
$end_date = htmlentities($_GET['end'], ENT_QUOTES, "UTF-8");
$servicegroup_name = getServiceGroupNameFromId($id);

/*
 * file type setting
 */
header("Cache-Control: public");
header("Pragma: public");
header("Content-Type: application/octet-stream");
header("Content-disposition: filename=".$servicegroup_name.".csv");

echo _("ServiceGroup").";"._("Begin date")."; "._("End date")."; "._("Duration")."\n";
echo $servicegroup_name.";".date(_("d/m/Y H:i:s"), $start_date)."; "
    . date(_("d/m/Y H:i:s"), $end_date)."; ".($end_date - $start_date)."s\n\n";
/*
 * Getting service group start
 */
echo _("Status").";"._("Time").";"._("Total Time").";"._("Mean Time")."; "._("Alert")."\n";
$reportingTimePeriod = getreportingTimePeriod();
$stats = array();
$stats = getLogInDbForServicesGroup($id, $start_date, $end_date, $reportingTimePeriod);
echo _("OK").";".$stats["average"]["OK_TP"]."%;".$stats["average"]["OK_MP"]. "%;".$stats["average"]["OK_A"].";\n";
echo _("WARNING").";".$stats["average"]["WARNING_TP"]."%;"
    . $stats["average"]["WARNING_MP"]. "%;".$stats["average"]["WARNING_A"].";\n";
echo _("CRITICAL").";".$stats["average"]["CRITICAL_TP"]."%;"
    . $stats["average"]["CRITICAL_MP"]. "%;".$stats["average"]["CRITICAL_A"].";\n";
echo _("UNKNOWN").";".$stats["average"]["UNKNOWN_TP"]."%;"
    . $stats["average"]["UNKNOWN_MP"]. "%;".$stats["average"]["UNKNOWN_A"].";\n";
echo _("UNDETERMINED").";".$stats["average"]["UNDETERMINED_TP"]."%;;;\n\n";
/*
 * Services group services stats
 */
echo _("Host").";"._("Service").";"._("OK Time").";"._("OK Mean Time").";"._("OK Alerts"). ";".
    _("WARNING Time").";"._("WARNING Mean Time").";"._("WARNING Alerts"). ";".
    _("CRITICAL Time").";"._("CRITICAL Mean Time").";"._("CRITICAL Alerts"). ";".
    _("UNKNOWN Time").";"._("UNKNOWN Mean Time").";"._("UNKNOWN Alerts"). ";".
    _("UNDETERMINED Time").";"._("UNDETERMINED Mean Time").";"._("UNDETERMINED Alerts")."\n";
foreach ($stats as $key => $tab) {
    if ($key != "average") {
        echo $tab["HOST_NAME"]. ";".$tab["SERVICE_DESC"].";".$tab["OK_TP"]. "%;".$tab["OK_MP"]. "%;".$tab["OK_A"].
                          ";".$tab["WARNING_TP"]. "%;".$tab["WARNING_MP"]. "%;".$tab["WARNING_A"].
                          ";".$tab["CRITICAL_TP"]. "%;".$tab["CRITICAL_MP"]. "%;".$tab["CRITICAL_A"].
                          ";".$tab["UNKNOWN_TP"]. "%;".$tab["UNKNOWN_MP"]. "%;".$tab["UNKNOWN_A"].
                          ";".$tab["UNDETERMINED_TP"]. "%;;\n";
    }
}
echo "\n\n";

/*
 * Services group stats evolution
 */
echo _("Day").";"._("Duration").";"
               ._("OK Mean Time").";"._("OK Alert").";"
               ._("Warning Mean Time").";"._("Warning Alert").";"
               ._("Unknown Mean Time").";"._("Unknown Alert").";"
               ._("Critical Mean Time").";"._("Critical Alert").";"
               ._("Day")."\n";
$str = "";
$request = "SELECT `service_service_id` FROM `servicegroup_relation` WHERE `servicegroup_sg_id` = '".$id."'";
$DBRESULT = $pearDB->query($request);
while ($sg = $DBRESULT->fetchRow()) {
    if ($str != "") {
        $str .= ", ";
    }
    $str .= "'" . $sg["service_service_id"] . "'";
}
$DBRESULT->free();
if ($str == "") {
    $str = "''";
}
unset($sg);
unset($DBRESULT);

$request =  "SELECT `date_start`, `date_end`, sum(`OKnbEvent`) as OKnbEvent, sum(`CRITICALnbEvent`) as CRITICALnbEvent,"
            . " sum(`WARNINGnbEvent`) as WARNINGnbEvent, sum(`UNKNOWNnbEvent`) as UNKNOWNnbEvent, "
            . "avg( `OKTimeScheduled` ) as OKTimeScheduled, "
            . "avg( `WARNINGTimeScheduled` ) as WARNINGTimeScheduled, "
            . "avg( `UNKNOWNTimeScheduled` ) as UNKNOWNTimeScheduled, "
            . "avg( `CRITICALTimeScheduled` ) as CRITICALTimeScheduled "
            . "FROM `log_archive_service` WHERE `service_id` IN (".$str.") "
            . "AND `date_start` >= '".$start_date."' "
            . "AND `date_end` <= '".$end_date."' "
            . "GROUP BY `date_end`, `date_start` order by `date_start` desc";
$res = $pearDBO->query($request);
$statesTab = array("OK", "WARNING", "CRITICAL", "UNKNOWN");
while ($row = $res->fetchRow()) {
    $duration = $row["date_end"] - $row["date_start"];

    /* Percentage by status */
    $duration = $row["OKTimeScheduled"] + $row["WARNINGTimeScheduled"] + $row["UNKNOWNTimeScheduled"]
                + $row["CRITICALTimeScheduled"];
    $row["OK_MP"] = round($row["OKTimeScheduled"] * 100 / $duration, 2);
    $row["WARNING_MP"] = round($row["WARNINGTimeScheduled"] * 100 / $duration, 2);
    $row["UNKNOWN_MP"] = round($row["UNKNOWNTimeScheduled"] * 100 / $duration, 2);
    $row["CRITICAL_MP"] = round($row["CRITICALTimeScheduled"] * 100 / $duration, 2);

    echo $row["date_start"].";".$duration."s;"
        . $row["OK_MP"]."%;".$row["OKnbEvent"].";"
        . $row["WARNING_MP"]."%;".$row["WARNINGnbEvent"].";"
        . $row["UNKNOWN_MP"]."%;".$row["UNKNOWNnbEvent"].";"
        . $row["CRITICAL_MP"]."%;".$row["CRITICALnbEvent"].";"
        . date("Y-m-d H:i:s", $row["date_start"]).";\n";
}
$res->free();
