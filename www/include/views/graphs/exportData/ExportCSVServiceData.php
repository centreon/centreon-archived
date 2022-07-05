<?php

/**
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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
 */

require_once realpath(dirname(__FILE__) . '/../../../../../config/centreon.config.php');
include_once _CENTREON_PATH_ . 'www/class/centreonDB.class.php';
include_once './functions.php';

try {
    //setup
    $pearDB = new CentreonDB();

    //checks
    session_start();
    session_write_close();
    $sid = session_id();
    checkSession($sid, $pearDB);

    //retrieve required params for DB query
    $chartId = $_GET['chartId'];
    $indexParam = $_GET['index'] ?? $_POST['index'] ?? 0;
    $startDate = extractDate($_GET['start']);
    $endDate = extractDate($_GET['end']);

    //retrieve data from database
    $pearDBO = new CentreonDB('centstorage');
    $index = findIndex($chartId, $indexParam, $pearDBO);
    $metrics = getMetricsByIndexId($index, $pearDBO);
    $rows = getDataByMetrics($metrics, $startDate, $endDate, $pearDBO);

    //dump results
    $fileName = generateFileNameByIndex($index, $pearDBO);
    sendDownloadResponse($fileName, ['time', 'humantime', ...$metrics], $rows);
} catch (\Exception $e) {
    sendErrorResponse($e->getMessage());
}
