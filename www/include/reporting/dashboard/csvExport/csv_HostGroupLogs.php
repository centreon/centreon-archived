<?php
/*
 * Copyright 2005-2019 Centreon
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
 * SVN : $URL$
 * SVN : $Id$
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

// DB Connexion
$pearDB = new CentreonDB();
$pearDBO = new CentreonDB("centstorage");

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

// Getting hostgroup id
$hostgroupId = null;
if (!empty($_POST['hostgroup']) || !empty($_GET['hostgroup'])) {
    $hostgroupId = filter_var(
        $_GET["hostgroup"] ?? $_POST['hostgroup'],
        FILTER_VALIDATE_INT
    );
}

if ($hostgroupId === false) {
    throw new \InvalidArgumentException('Bad parameters');
}

// finding the user's allowed hostgroups
$allowedHostgroups = $centreon->user->access->getHostGroupAclConf(null, 'broker');

//checking if the user has ACL rights for this resource
if (!$centreon->user->admin
    && $hostgroupId !== null
    && !array_key_exists($hostgroupId, $allowedHostgroups)
) {
    echo '<div align="center" style="color:red">' .
        '<b>You are not allowed to access this host group</b></div>';
    exit();
}

// Getting time interval to report
$dates = getPeriodToReport();

$startDate = null;
$endDate = null;

if (!empty($_GET['start'])) {
    $startDate = filter_var($_GET['start'], FILTER_VALIDATE_INT);
}

if (!empty($_GET['end'])) {
    $endDate = filter_var($_GET['end'], FILTER_VALIDATE_INT);
}

if ($startDate === false || $endDate === false) {
    throw new \InvalidArgumentException('Bad parameters');
}

$hostgroupName = getHostgroupNameFromId($hostgroupId);

// file type setting
header("Cache-Control: public");
header("Pragma: public");
header("Content-Type: application/octet-stream");
header("Content-disposition: filename=" . $hostgroupName . ".csv");

echo _("Hostgroup") . ";"
    . _("Begin date") . "; "
    . _("End date") . "; "
    . _("Duration") . "\n";
echo $hostgroupName . "; "
    . date(_("d/m/Y H:i:s"), $startDate) . "; "
    . date(_("d/m/Y H:i:s"), $endDate) . "; "
    . ($endDate - $startDate) . "s\n";
echo "\n";
echo _("Status") . ";"
    . _("Total Time") . ";"
    . _("Mean Time") . "; "
    . _("Alert") . "\n";

// Getting stats on Host
$reportingTimePeriod = getreportingTimePeriod();
$hostgroupStats = array();
$hostgroupStats = getLogInDbForHostGroup($hostgroupId, $startDate, $endDate, $reportingTimePeriod);

echo _("UP") . ";"
    . $hostgroupStats["average"]["UP_TP"] . "%;"
    . $hostgroupStats["average"]["UP_MP"] . "%;"
    . $hostgroupStats["average"]["UP_A"] . ";\n";
echo _("DOWN") . ";"
    . $hostgroupStats["average"]["DOWN_TP"] . "%;"
    . $hostgroupStats["average"]["DOWN_MP"] . "%;"
    . $hostgroupStats["average"]["DOWN_A"] . ";\n";
echo _("UNREACHABLE") . ";"
    . $hostgroupStats["average"]["UNREACHABLE_TP"] . "%;"
    . $hostgroupStats["average"]["UNREACHABLE_MP"] . "%;"
    . $hostgroupStats["average"]["UNREACHABLE_A"] . ";\n";
echo _("SCHEDULED DOWNTIME") . ";"
    . $hostgroupStats["average"]["MAINTENANCE_TP"] . "%;\n";
echo _("UNDETERMINED") . ";"
    . $hostgroupStats["average"]["UNDETERMINED_TP"] . "%;\n";
echo "\n\n";

echo _("Hosts") . ";"
    . _("Up") . " %;"
    . _("Up Mean Time") . " %;"
    . _("Up") . " " . _("Alert") . ";"
    . _("Down") . " %;"
    . _("Down Mean Time") . " %;"
    . _("Down") . " " . _("Alert") . ";"
    . _("Unreachable") . " %;"
    . _("Unreachable Mean Time") . " %;"
    . _("Unreachable") ." " . _("Alert") . ";"
    . _("Scheduled Downtimes") . " %;"
    . _("Undetermined") ." %;\n";

foreach ($hostgroupStats as $key => $tab) {
    if ($key != "average") {
        echo $tab["NAME"] . ";"
            . $tab["UP_TP"] . "%;"
            . $tab["UP_MP"] . "%;"
            . $tab["UP_A"] . ";"
            . $tab["DOWN_TP"] . "%;"
            . $tab["DOWN_MP"] . "%;"
            . $tab["DOWN_A"] .";"
            . $tab["UNREACHABLE_TP"] . "%;"
            . $tab["UNREACHABLE_MP"] . "%;"
            . $tab["UNREACHABLE_A"] . ";"
            . $tab["MAINTENANCE_TP"] . "%;"
            . $tab["UNDETERMINED_TP"]."%;\n";
    }
}
echo "\n";
echo "\n";

// getting all hosts from hostgroup
$str = "";
$dbResult = $pearDB->prepare(
    "SELECT host_host_id FROM `hostgroup_relation` " .
    "WHERE `hostgroup_hg_id` = :hostgroupId"
);
$dbResult->bindValue(':hostgroupId', $hostgroupId, PDO::PARAM_INT);
$dbResult->execute();

while ($hg = $dbResult->fetch()) {
    if ($str != "") {
        $str .= ", ";
    }
    $str .= "'" . $hg["host_host_id"] . "'";
}
if ($str == "") {
    $str = "''";
}
unset($hg);
unset($dbResult);

// Getting hostgroup stats evolution
$dbResult = $pearDBO->prepare(
    "SELECT `date_start`, `date_end`, sum(`UPnbEvent`) as UPnbEvent, sum(`DOWNnbEvent`) as DOWNnbEvent, "
    . "sum(`UNREACHABLEnbEvent`) as UNREACHABLEnbEvent, "
    . "avg( `UPTimeScheduled` ) as UPTimeScheduled, "
    . "avg( `DOWNTimeScheduled` ) as DOWNTimeScheduled, "
    . "avg( `UNREACHABLETimeScheduled` ) as UNREACHABLETimeScheduled "
    . "FROM `log_archive_host` WHERE `host_id` IN (" . $str . ") "
    . "AND `date_start` >= :startDate "
    . "AND `date_end` <= :endDate "
    . "GROUP BY `date_end`, `date_start` ORDER BY `date_start` desc"
);
$dbResult->bindValue(':startDate', $startDate, PDO::PARAM_INT);
$dbResult->bindValue(':endDate', $endDate, PDO::PARAM_INT);
$dbResult->execute();

echo _("Day") . ";"
    . _("Duration") . ";"
    . _("Up Mean Time") . ";"
    . _("Up Alert") . ";"
    . _("Down Mean Time") . ";"
    . _("Down Alert") . ";"
    . _("Unreachable Mean Time") . ";"
    . _("Unreachable Alert") . _("Day") . ";\n";

while ($row = $dbResult->fetch()) {
    $duration = $row["UPTimeScheduled"] + $row["DOWNTimeScheduled"] + $row["UNREACHABLETimeScheduled"];

    // Percentage by status
    $row["UP_MP"] = round($row["UPTimeScheduled"] * 100 / $duration, 2);
    $row["DOWN_MP"] = round($row["DOWNTimeScheduled"] * 100 / $duration, 2);
    $row["UNREACHABLE_MP"] = round($row["UNREACHABLETimeScheduled"] * 100 / $duration, 2);

    echo $row["date_start"] . ";"
        . $duration . "s;"
        . $row["UP_MP"] . "%;"
        . $row["UPnbEvent"] . ";"
        . $row["DOWN_MP"] . "%;"
        . $row["DOWNnbEvent"] . ";"
        . $row["UNREACHABLE_MP"] . "%;"
        . $row["UNREACHABLEnbEvent"] . ";"
        . date("Y-m-d H:i:s", $row["date_start"]) . ";\n";
}
$dbResult->closeCursor();
