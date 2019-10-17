<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
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

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../www/class/centreonRestHttp.class.php';
require_once __DIR__ . '/../config/centreon-statistics.config.php';
require_once __DIR__ . '/../www/class/centreonStatistics.class.php';

$sendStatistics = 0;
$isRemote = 0;
$hasValidLicenses = false;
$isImpUser = false;

$db = $dependencyInjector['configuration_db'];

// Check if CEIP is enable
$result = $db->query("SELECT `value` FROM `options` WHERE `key` = 'send_statistics'");
if ($row = $result->fetch()) {
    $sendStatistics = (int)$row['value'];
}

// Check if it's a Central server
$result = $db->query("SELECT `value` FROM `informations` WHERE `key` = 'isRemote'");
if ($row = $result->fetch()) {
    $isRemote = $row['value'];
}

// Check if valid Centreon licences exist
$centreonLicensesDir = "/etc/centreon/license.d/";
if (is_dir($centreonLicensesDir)) {
    if ($dh = opendir($centreonLicensesDir)) {
       $dateNow = new DateTime('NOW');
        while (($file = readdir($dh)) !== false) {
            if (is_file($centreonLicensesDir . $file)) {
                $licenseContent = file_get_contents($centreonLicensesDir . $file);
                if (preg_match('/"end": "(\d{4}\-\d{2}\-\d{2})"/', $licenseContent, $matches)) {
                    $dateLicense = new DateTime($matches[1]);
                    if ($dateLicense >= $dateNow) {
                        $hasValidLicenses = true;
                        break ;
                    }
                }
            }
        }
    }
}

// Check if it's an IMP user
$result = $db->query("SELECT options.value FROM options WHERE options.key = 'impCompanyToken'");
if ($row = $result->fetch()) {
    if (!empty($row['value'])) {
        $isImpUser = true;
    }
}

// Only send telemetry & statistics if it's a Centreon central server
if ($isRemote !== 'yes') {
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
    $additional = [];

    /*
     * Only send statistics if user using a free version has enabled this option
     * or if at least a Centreon license is valid
     */
    if ($sendStatistics || $hasValidLicenses || $isRemote) {
        $additional = $oStatistics->getAdditionalData();
    }

    // Construct the object gathering datas
    $data = array(
        'timestamp' => "$timestamp",
        'UUID' => $UUID,
        'versions' => $versions,
        'infos' => $infos,
        'timezone' => $timez,
        'additional' => $additional
    );

    try {
        $returnData = $http->call(CENTREON_STATS_URL, 'POST', $data, array(), true);
        echo "statusCode :" . $returnData['statusCode'] . ',body : ' . $returnData['body'];
    } catch (Exception $e) {
        echo 'Caught exception: ' .  $e->getMessage() . '\n';
    }
}
