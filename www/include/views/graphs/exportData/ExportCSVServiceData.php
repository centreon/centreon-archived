<?php
/*
 * Copyright 2005-2015 Centreon
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

function get_error($str)
{
    echo $str . "<br />";
    exit(0);
}

require_once realpath(dirname(__FILE__) . "/../../../../../config/centreon.config.php");
include_once _CENTREON_PATH_ . "www/class/centreonDB.class.php";

$pearDB = new CentreonDB();
$pearDBO = new CentreonDB("centstorage");

session_start();
session_write_close();

$sid = session_id();
if (isset($sid)) {
    $res = $pearDB->query("SELECT * FROM session WHERE session_id = '" . $sid . "'");
    if (!$session = $res->fetchRow()) {
        get_error('bad session id');
    }
} else {
    get_error('need session id !');
}

$index = filter_var(
    $_GET['index'] ?? $_POST['index'] ?? null,
    FILTER_VALIDATE_INT
);
$period = filter_var(
    $_GET['period'] ?? $_POST['period'] ?? 'today',
    FILTER_SANITIZE_STRING
);
$start = filter_var(
    $_GET['start'] ?? null,
    FILTER_VALIDATE_INT
);
$end = filter_var(
    $_GET['end'] ?? null,
    FILTER_VALIDATE_INT
);
$chartId = filter_var(
    $_GET['chartId'] ?? null,
    FILTER_SANITIZE_STRING
);

if (isset($chartId)) {
    list($hostId, $serviceId) = explode('_', $_GET['chartId']);
    if (!isset($hostId) || !isset($serviceId)) {
        die('Resource not found');
    }

    // Making sure that splitted values are int.
    if (is_numeric($hostId) && is_numeric($serviceId)) {
        $query = 'SELECT id'
            . ' FROM index_data'
            . ' WHERE host_id = :hostId'
            . ' AND service_id = :serviceId';

        $stmt = $pearDBO->prepare($query);
        $stmt->bindValue(':hostId', $hostId, \PDO::PARAM_INT);
        $stmt->bindValue(':serviceId', $serviceId, \PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount()) {
            $row = $stmt->fetchRow();
            $index = $row['id'];
        } else {
            die('Resource not found');
        }
    }
}

$stmt = $pearDBO->prepare(
    'SELECT host_name, service_description FROM index_data WHERE id = :index'
);
$stmt->bindValue(':index', $index, \PDO::PARAM_INT);
$stmt->execute();
while ($res = $stmt->fetchRow()) {
    $hName = $res["host_name"];
    $sName = $res["service_description"];
}

header("Content-Type: application/csv-tab-delimited-table");
if (isset($hName) && isset($sName)) {
    header("Content-disposition: filename=" . $hName . "_" . $sName . ".csv");
} else {
    header("Content-disposition: filename=" . $index . ".csv");
}

$listMetric = array();
$datas = array();
$listEmptyMetric = array();

$stmt = $pearDBO->prepare(
    'SELECT DISTINCT metric_id, metric_name ' .
    'FROM metrics, index_data ' .
    'WHERE metrics.index_id = index_data.id AND id = :index ORDER BY metric_name'
);

$stmt->bindValue(':index', $index, \PDO::PARAM_INT);
$stmt->execute();

while ($index_data = $stmt->fetchRow()) {
    $listMetric[$index_data["metric_id"]] = $index_data["metric_name"];
    $listEmptyMetric[$index_data["metric_id"]] = '';
    $stmt2 = $pearDBO->prepare(
        "SELECT ctime,value FROM data_bin WHERE id_metric = ':metricId' " .
        "AND ctime >= ':start' AND ctime < ':end'"
    );
    $stmt2->bindValue(':start', $start, \PDO::PARAM_INT);
    $stmt2->bindValue(':end', $end, \PDO::PARAM_INT);
    $stmt2->bindValue(':metricId', $index_data["metric_id"], \PDO::PARAM_INT);
    $stmt2->execute();
    while ($data = $stmt2->fetchRow()) {
        $datas[$data["ctime"]][$index_data["metric_id"]] = $data["value"];
    }
}

// Order by timestamp
ksort($datas);
foreach ($datas as $key => $data) {
    $datas[$key] = $data + $listEmptyMetric;
    // Order by metric
    ksort($datas[$key]);
}

print "time;humantime";
if (count($listMetric)) {
    ksort($listMetric);
    print ";" . implode(';', $listMetric);
}
print "\n";

foreach ($datas as $ctime => $tab) {
    print $ctime . ";" . date("Y-m-d H:i:s", $ctime);
    foreach ($tab as $metric_value) {
        if ($metric_value !== '') {
            printf(";%f", $metric_value);
        } else {
            print(";");
        }
    }
    print "\n";
}
