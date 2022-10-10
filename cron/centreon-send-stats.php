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

use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;

$output = new ConsoleOutput();
$logger = new ConsoleLogger($output);

$shouldSendStatistics = false;
$isRemote = false;
$hasValidLicenses = false;
$isImpUser = false;

/**
 * @var CentreonDB $db
 */
$db = $dependencyInjector['configuration_db'];

/**
 * Log a message.
 *
 * If an exception is provided, the message will be stored in the PHP log.
 *
 * @param string $message Message to log
 * @param Throwable $exception
 */
function logger(string $message, Throwable $exception = null)
{
    try {
        $datetime = new DateTime();
        $datetime->setTimezone(new DateTimeZone('UTC'));
        $logEntry = is_null($exception) ? $message : $message . ' - ' . $exception->getMessage();
        printf("%s - %s\n", $datetime->format('Y/m/d H:i:s'), $logEntry);
    } catch (Exception $ex) {
        printf("Exception: %s\n", $ex->getMessage());
    }
}
// Check if CEIP is enable
$result = $db->query("SELECT `value` FROM `options` WHERE `key` = 'send_statistics'");
if ($row = $result->fetch()) {
    $shouldSendStatistics = (bool)$row['value'];
}

// Check if it's a Central server
$result = $db->query("SELECT `value` FROM `informations` WHERE `key` = 'isRemote'");
if ($row = $result->fetch()) {
    $isRemote = $row['value'] === 'yes';
}

// Check if valid Centreon licences exist
$centreonLicensesDir = "/etc/centreon/license.d/";
if (is_dir($centreonLicensesDir) && ($dh = opendir($centreonLicensesDir)) !== false) {
    $dateNow = new DateTime('NOW');
    while (($file = readdir($dh)) !== false) {
        try {
            $statisticsFileName = $centreonLicensesDir . $file;
            if (is_file($statisticsFileName)) {
                $licenseContent = file_get_contents($statisticsFileName);
                if (preg_match('/"end": "(\d{4}\-\d{2}\-\d{2})"/', $licenseContent, $matches)) {
                    $dateLicense = new DateTime((string)$matches[1]);
                    if ($dateLicense >= $dateNow) {
                        $hasValidLicenses = true;
                        break;
                    }
                }
            }
        } catch (Exception $ex) {
            logger('Error while reading statistics file ' . $statisticsFileName, $ex);
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
if ($isRemote === false) {
    try {
        $http = new CentreonRestHttp();
        $oStatistics = new CentreonStatistics($logger);
        $timestamp = time();
        $uuid = $oStatistics->getCentreonUUID();
        if (empty($uuid)) {
            throw new Exception("No UUID specified");
        }
        $versions = $oStatistics->getVersion();
        $infos = $oStatistics->getPlatformInfo();
        $timezone = $oStatistics->getPlatformTimezone();
        $authentication = $oStatistics->getAuthenticationOptions();
        $additional = [];

        /*
         * Only send statistics if user using a free version has enabled this option
         * or if at least a Centreon license is valid
         */
        if ($shouldSendStatistics || $hasValidLicenses) {
            try {
                $additional = $oStatistics->getAdditionalData();
            } catch (\Throwable $e) {
                $logger->error('Cannot get stats from modules');
            }
        }

        // Construct the object gathering datas
        $data = array(
            'timestamp' => "$timestamp",
            'UUID' => $uuid,
            'versions' => $versions,
            'infos' => $infos,
            'timezone' => $timezone,
            'authentication' => $authentication,
            'additional' => $additional
        );

        $returnData = $http->call(CENTREON_STATS_URL, 'POST', $data, array(), true);
        logger(
            sprintf(
                'Response from [%s] : %s,body : %s',
                CENTREON_STATS_URL,
                $returnData['statusCode'],
                $returnData['body']
            )
        );
    } catch (Exception $ex) {
        logger('Got error while sending data to [' . CENTREON_STATS_URL . ']', $ex);
    }
}
