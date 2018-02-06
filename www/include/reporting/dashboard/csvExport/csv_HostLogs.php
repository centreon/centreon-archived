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
require_once _CENTREON_PATH_ . "www/class/centreonDuration.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonUser.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonSession.class.php";
require_once _CENTREON_PATH_ . "www/class/centreon.class.php";
include_once _CENTREON_PATH_ . "www/include/reporting/dashboard/DB-Func.php";

/*
 * DB connexion
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

$centreon = $_SESSION["centreon"];

/*
 * Checking session
 */
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
 * getting host id
 */
isset($_GET["host"]) ? $id = htmlentities($_GET["host"], ENT_QUOTES, "UTF-8") : $id = null;
isset($_POST["host"]) ? $id = htmlentities($_POST["host"], ENT_QUOTES, "UTF-8") : $id;

/*
 * Getting time interval to report
 */
$dates = getPeriodToReport();
$start_date = htmlentities($_GET['start'], ENT_QUOTES, "UTF-8");
$end_date = htmlentities($_GET['end'], ENT_QUOTES, "UTF-8");
$host_name = getHostNameFromId($id);

/*
 * file type setting
 */
header("Cache-Control: public");
header("Pragma: public");
header("Content-Type: application/octet-stream");
header("Content-disposition: filename=".$host_name.".csv");

echo _("Host").";"._("Begin date")."; "._("End date")."; "._("Duration")."\n";
echo $host_name."; ".date(_("d/m/Y H:i:s"), $start_date)."; "
    . date(_("d/m/Y H:i:s"), $end_date)."; ".($end_date - $start_date) ."s\n";
echo "\n";
echo _("Status").";"._("Duration").";"._("Total Time").";"._("Mean Time")."; "._("Alert")."\n";
/*
 * Getting stats on Host
 */
$reportingTimePeriod = getreportingTimePeriod();
$hostStats = getLogInDbForHost($id, $start_date, $end_date, $reportingTimePeriod);
echo _("DOWN").";".$hostStats["DOWN_T"]."s;".$hostStats["DOWN_TP"]."%;"
    . $hostStats["DOWN_MP"]."%;".$hostStats["DOWN_A"].";\n";
echo _("UP").";".$hostStats["UP_T"]."s;".$hostStats["UP_TP"]."%;".$hostStats["UP_MP"]."%;".$hostStats["UP_A"].";\n";
echo _("UNREACHABLE").";".$hostStats["UNREACHABLE_T"]."s;".$hostStats["UNREACHABLE_TP"]."%;"
    . $hostStats["UNREACHABLE_MP"]."%;".$hostStats["UNREACHABLE_A"].";\n";
echo _("UNDETERMINED").";".$hostStats["UNDETERMINED_T"]."s;".$hostStats["UNDETERMINED_TP"]."%;\n";
echo "\n";

echo _("Service").";"._("OK")."; "._("OK")." Alert;"
               ._("Warning")."; "._("Warning")." Alert;"
               ._("Critical")."; "._("Critical")." Alert;"
               ._("Unknown")."; "._("Unknown")." Alert;"
               ._("Undetermined").";\n";
$hostServicesStats =  getLogInDbForHostSVC($id, $start_date, $end_date, $reportingTimePeriod);
foreach ($hostServicesStats as $tab) {
    if (isset($tab["DESCRIPTION"]) && $tab["DESCRIPTION"] != "") {
        echo $tab["DESCRIPTION"]. ";".$tab["OK_TP"]. "%;".$tab["OK_A"]
        . ";".$tab["WARNING_TP"]. "%;".$tab["WARNING_A"]
        . ";".$tab["CRITICAL_TP"]. "%;".$tab["CRITICAL_A"]
        . ";".$tab["UNKNOWN_TP"]. "%;".$tab["UNKNOWN_A"]
        . ";".$tab["UNDETERMINED_TP"]. "%;;\n";
    }
}

echo "\n";
echo "\n";

/*
 * Evolution of host availability in time
 */
echo _("Day").";"._("Duration").";".
     _("Up")." "._("Time").";"._("Up").";"._("Up")." "._("Alert").";".
     _("Down")." "._("Time").";"._("Down").";"._("Down")." "._("Alert").";".
     _("Unreachable")." "._("Time").";"._("Unreachable").";"._("Unreachable")." "._("Alert").
     _("Day").";\n";
$rq = "SELECT  * " .
        "FROM `log_archive_host` " .
        "WHERE `host_id` = '".$id."' " .
        "AND `date_start` >= '".$start_date."' " .
        "AND `date_end` <= '".$end_date."' " .
        "ORDER BY `date_start` desc";
$DBRESULT = $pearDBO->query($rq);
while ($row = $DBRESULT->fetchRow()) {
    $duration = $row["UPTimeScheduled"] + $row["DOWNTimeScheduled"] + $row["UNREACHABLETimeScheduled"];
        /* Percentage by status */
        $row["UP_MP"] = round($row["UPTimeScheduled"] * 100 / $duration, 2);
    $row["DOWN_MP"] = round($row["DOWNTimeScheduled"] * 100 / $duration, 2);
    $row["UNREACHABLE_MP"] = round($row["UNREACHABLETimeScheduled"] * 100 / $duration, 2);
    echo $row["date_start"].";".$duration.";".
            $row["UPTimeScheduled"].";".$row["UP_MP"]."%;".$row["UPnbEvent"].";".
            $row["DOWNTimeScheduled"].";".$row["DOWN_MP"]."%;".$row["DOWNnbEvent"].";".
            $row["UNREACHABLETimeScheduled"].";".$row["UNREACHABLE_MP"]."%;".$row["UNREACHABLEnbEvent"].";".
            date("Y-m-d H:i:s", $row["date_start"]).";\n";
}
