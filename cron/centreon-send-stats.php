<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../www/class/centreonRestHttp.class.php';
require_once __DIR__ . '/../config/config-statistics.php';
require_once __DIR__ . '/../www/class/centreonStatistics.class.php';

$sendStatistics = 0;
$isRemote = 0;

$db = $dependencyInjector['configuration_db'];
$result = $db->query("SELECT `value` FROM `options` WHERE `key` = 'send_statistics'");
if ($row = $result->fetch()) {
    (int)$sendStatistics = $row['value'];
}

$result = $db->query("SELECT `value` FROM `informations` WHERE `key` = 'isRemote'");
if ($row = $result->fetch()) {
    (int)$isRemote = $row['value'];
}

if ($sendStatistics && !$isRemote) {
    $http = new CentreonRestHttp();
    $oStatistics = new CentreonStatistics();
    $timestamp = time();
    $UUID = $oStatistics->getCentreonUUID();
    if (empty($UUID)) {
        \error_log($timestamp . " : No UUID specified");
        return;
    }
    $versions = $oStatistics->getVersion();
    $infos = $oStatistics->getPlatformInfo();
    $timez = $oStatistics->getPlatformTimezone();

    // Construct the object gathering datas
    $data = array(
        'timestamp' => $timestamp,
        'UUID' => $UUID,
        'versions' => $versions,
        'infos' => $infos,
        'timezone' => $timez
    );

    $returnData = $http->call(CENTREON_STATS_URL, 'POST', $data, array(), true);
    echo "statusCode :" . $returnData['statusCode'] . ',body : ' . $returnData['body'];
}
