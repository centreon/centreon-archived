<?php

/*
 * Copyright 2005-2021 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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

require_once realpath(__DIR__ . "/../../../../../config/centreon.config.php");
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

// DB connexion
$pearDB = new CentreonDB();
$pearDBO = new CentreonDB("centstorage");

$sid = session_id();
if (!empty($sid) && isset($_SESSION['centreon'])) {
    $oreon = $_SESSION['centreon'];
    $res = $pearDB->prepare("SELECT COUNT(*) as count FROM session WHERE user_id = :id");
    $res->bindValue(':id', (int)$oreon->user->user_id, PDO::PARAM_INT);
    $res->execute();
    $row = $res->fetch(\PDO::FETCH_ASSOC);
    if ($row['count'] < 1) {
        get_error('bad session id');
    }
} else {
    get_error('need session id!');
}

$centreon = $oreon;

// getting host and service id
$hostId = filter_var(
    $_GET['host'] ?? $_POST['host'] ?? null,
    FILTER_VALIDATE_INT
);

$serviceId = filter_var(
    $_GET['service'] ?? $_POST['service'] ?? null,
    FILTER_VALIDATE_INT
);

// finding the user's allowed resources
$services = $centreon->user->access->getHostServiceAclConf($hostId, 'broker', null);

//checking if the user has ACL rights for this resource
if (!$centreon->user->admin
    && $serviceId !== null
    && (!array_key_exists($serviceId, $services))
) {
    echo '<div align="center" style="color:red">' .
        '<b>You are not allowed to access this service</b></div>';
    exit();
}

// Getting time interval to report
$dates = getPeriodToReport();
$startDate =  htmlentities($_GET['start'], ENT_QUOTES, "UTF-8");
$endDate =  htmlentities($_GET['end'], ENT_QUOTES, "UTF-8");
$hostName = getHostNameFromId($hostId);
$serviceDescription = getServiceDescriptionFromId($serviceId);

// file type setting
header("Cache-Control: public");
header("Pragma: public");
header("Content-Type: application/octet-stream");
header("Content-disposition: attachment ; filename=" . $hostName .  "_"  . $serviceDescription . ".csv");

echo _("Host") . ";"
    . _("Service") . ";"
    . _("Begin date") . "; "
    . _("End date") . "; "
    . _("Duration") . "\n";

echo $hostName . "; "
    . $serviceDescription . "; "
    . date(_("d/m/Y H:i:s"), $startDate) . "; "
    . date(_("d/m/Y H:i:s"), $endDate) . "; "
    . ($endDate - $startDate) . "s\n";
echo "\n";

echo _("Status") . ";"
    . _("Time") . ";"
    . _("Total Time") . ";"
    . _("Mean Time") . "; "
    . _("Alert") . "\n";

$reportingTimePeriod = getreportingTimePeriod();
$servicesStats = getServicesLogs(
    [[
        'hostId' => $hostId,
        'serviceId' => $serviceId
    ]],
    $startDate,
    $endDate,
    $reportingTimePeriod
);
$serviceStats = $servicesStats[$hostId][$serviceId];

echo "OK;"
    . $serviceStats["OK_T"] . "s;"
    . $serviceStats["OK_TP"] . "%;"
    . $serviceStats["OK_MP"] . "%;"
    . $serviceStats["OK_A"] . ";\n";

echo "WARNING;"
    . $serviceStats["WARNING_T"] . "s;"
    . $serviceStats["WARNING_TP"] . "%;"
    . $serviceStats["WARNING_MP"] . "%;"
    . $serviceStats["WARNING_A"] . ";\n";

echo "CRITICAL;"
    . $serviceStats["CRITICAL_T"] . "s;"
    . $serviceStats["CRITICAL_TP"] . "%;"
    . $serviceStats["CRITICAL_MP"] . "%;"
    . $serviceStats["CRITICAL_A"] . ";\n";

echo "UNKNOWN;"
    . $serviceStats["UNKNOWN_T"] . "s;"
    . $serviceStats["UNKNOWN_TP"] . "%;"
    . $serviceStats["UNKNOWN_MP"] .  "%;"
    . $serviceStats["UNKNOWN_A"] . ";\n";

echo _("SCHEDULED DOWNTIME") . ";"
    . $serviceStats["MAINTENANCE_T"] . "s;"
    . $serviceStats["MAINTENANCE_TP"] . "%;;;\n";

echo "UNDETERMINED;"
    . $serviceStats["UNDETERMINED_T"] . "s;"
    . $serviceStats["UNDETERMINED_TP"] . "%;;;\n";
echo "\n";
echo "\n";

// Getting evolution of service stats in time
echo _("Day") . ";"
    . _("Duration") . ";"
    . _("OK") . " (s); "
    . _("OK") . " %; "
    . _("OK") . " Alert;"
    . _("Warning") . " (s); "
    . _("Warning") . " %;"
    . _("Warning") . " Alert;"
    . _("Unknown") . " (s); "
    . _("Unknown") . " %;"
    . _("Unknown") . " Alert;"
    . _("Critical") . " (s); "
    . _("Critical") . " %;"
    . _("Critical") . " Alert;"
    . _("Day") . ";\n";

$dbResult = $pearDBO->prepare(
    "SELECT  * FROM `log_archive_service` " .
    "WHERE `host_id` = :hostId " .
    "AND `service_id` = :serviceId " .
    "AND `date_start` >= :startDate " .
    "AND `date_end` <= :endDate " .
    "ORDER BY `date_start` DESC"
);
$dbResult->bindValue(':hostId', $hostId, PDO::PARAM_INT);
$dbResult->bindValue(':serviceId', $serviceId, PDO::PARAM_INT);
$dbResult->bindValue(':startDate', $startDate, PDO::PARAM_INT);
$dbResult->bindValue(':endDate', $endDate, PDO::PARAM_INT);
$dbResult->execute();

while ($row = $dbResult->fetch()) {
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
    echo $row["date_start"] . ";"
        . $duration . ";"
        . $row["OKTimeScheduled"] . "s;"
        . $row["OK_MP"] . "%;"
        . $row["OKnbEvent"] . ";"
        . $row["WARNINGTimeScheduled"] . "s;"
        . $row["WARNING_MP"] . "%;"
        . $row["WARNINGnbEvent"] . ";"
        . $row["UNKNOWNTimeScheduled"] . "s;"
        . $row["UNKNOWN_MP"] . "%;"
        . $row["UNKNOWNnbEvent"] . ";"
        . $row["CRITICALTimeScheduled"] . "s;"
        . $row["CRITICAL_MP"] . "%;"
        . $row["CRITICALnbEvent"] . ";"
        . date("Y-m-d H:i:s", $row["date_start"]) . ";\n";
}
$dbResult->closeCursor();
