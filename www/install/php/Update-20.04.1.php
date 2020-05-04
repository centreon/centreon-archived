<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
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

// get current configuration data
require_once __DIR__ . '/../../../config/centreon.config.php';

require_once __DIR__ . '/../../class/centreonLog.class.php';
$centreonLog = new CentreonLog();

// error specific content
$versionOfTheUpgrade = 'UPGRADE - 20.04.1 : ';
$errorMessage = '';

/**
 * Queries needing exception management BUT no rollback if failing
 */
try {
    /*
     * Get user data to generate a new config file for the gorgone daemon module
     */

    // get engine command
    $res = $pearDB->query(
        "SELECT command_file FROM cfg_nagios cn
        JOIN nagios_server ns ON ns.id = cn.nagios_id
        WHERE localhost = '1'"
    );
    $engineCommand = $res->fetch()['command_file'];

    // escape double quotes and backslashes
    $needle = ['\\', '"'];
    $escape = ['\\\\', '\"'];
    $password = str_replace($needle, $escape, password);

    // set macro keys
    $pattern = [
        '/--ADDRESS--/',
        '/--DBPORT--/',
        '/--DBUSER--/',
        '/--DBPASS--/',
        '/--CONFDB--/',
        '/--STORAGEDB--/',
        '/--CENTREON_VARLIB--/',
        '/--CENTREON_CACHEDIR--/',
        '/--CENTREON_SPOOL--/',
        '/--CENTREON_TRAPDIR--/',
        '/--HTTPSERVERADDRESS--/',
        '/--HTTPSERVERPORT--/',
        '/--SSLMODE--/',
        '/--GORGONE_VARLIB--/',
        '/--ENGINE_COMMAND--/'

    ];

    // set default values for these parameters
    $userValues = [
        hostCentreon,
        port,
        user,
        $password,
        db,
        dbcstg,
        _CENTREON_VARLIB_,
        _CENTREON_CACHEDIR_,
        '/var/spool/centreon',
        '/etc/snmp/centreon_traps',
        '0.0.0.0',
        '8085',
        'false',
        '/var/lib/centreon-gorgone',
        $engineCommand
    ];

    /**
     * check if the file has already been generated on a 20.04.0-beta or not
     * if already exists, generate a new file
     *
     * @param string - path to the file
     *
     * @return string - corrected filename
     */
    function returnFinalFileName(string $destinationFile)
    {
        if (file_exists($destinationFile)) {
            $destinationFile .= '.new';
        }

        return $destinationFile;
    }

    /*
     * database configuration file
     */
    $fileTpl = __DIR__ . '/../var/databaseTemplate.yaml';
    if (!file_exists($fileTpl) || 0 === filesize($fileTpl)) {
        $errorMessage = 'Database configuration template is empty or missing';
        throw new \InvalidArgumentException($errorMessage);
    }
    $content = file_get_contents($fileTpl);
    $content = preg_replace($pattern, $userValues, $content);
    $destinationFile = returnFinalFileName(_CENTREON_ETC_ . '/config.d/10-database.yaml');
    file_put_contents($destinationFile, $content);

    if (!file_exists($destinationFile) || 0 === filesize($destinationFile)) {
        $errorMessage = 'Database configuration file is not created properly';
        throw new \InvalidArgumentException($errorMessage);
    }

    /*
     * gorgone configuration file for centreon. Created in the centreon-gorgone folder
     */
    $fileTpl = __DIR__ . '/../var/gorgone/gorgoneCentralTemplate.yaml';
    if (!file_exists($fileTpl) || 0 === filesize($fileTpl)) {
        $errorMessage = 'Gorgone configuration template is empty or missing';
        throw new \InvalidArgumentException($errorMessage);
    }
    $content = file_get_contents($fileTpl);
    $content = preg_replace($pattern, $userValues, $content);
    $destinationFolder = _CENTREON_ETC_ . '/../centreon-gorgone';
    $destinationFile = returnFinalFileName($destinationFolder . '/config.d/40-gorgoned.yaml');

    // checking if mandatory centreon-gorgone configuration sub-folder already exists
    if (!file_exists($destinationFolder . '/config.d')) {
        $errorMessage = 'Gorgone configuration folder does not exist. ' .
            'Please reinstall the centreon-gorgone package and retry';
        throw new \InvalidArgumentException($errorMessage);
    }
    file_put_contents($destinationFile, $content);

    if (!file_exists($destinationFile) || 0 === filesize($destinationFile)) {
        $errorMessage = 'Gorgone configuration file is not created properly';
        throw new \InvalidArgumentException($errorMessage);
    }
} catch (\Exception $e) {
    $centreonLog->insertLog(
        4,
        $versionOfTheUpgrade . $errorMessage .
        " - Code : " . $e->getCode() .
        " - Error : " . $e->getMessage() .
        " - Trace : " . $e->getTraceAsString()
    );
    throw new \Exception($versionOfTheUpgrade . $errorMessage, $e->getCode(), $e);
}
