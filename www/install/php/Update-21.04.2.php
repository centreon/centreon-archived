<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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

include_once __DIR__ . "/../../class/centreonLog.class.php";
$centreonLog = new CentreonLog();

//error specific content
$versionOfTheUpgrade = 'UPGRADE - 21.04.2: ';

$pearDB = new CentreonDB('centreon', 3, false);

-- Add missing link between _Module_Meta and localhost poller


/**
 * Query with transaction
 */
try {
    $pearDB->beginTransaction();
    /**
     * Retreive Meta Host Id
     */
    $statement = $pearDB->query(
        "SELECT `host_id` FROM `host` WHERE `host_name` = '_Module_Meta'"
    );

    /*
     * Add missing relation
     */
    if ($moduleMeta = $statement->fetch()) {
        $moduleMetaId = $moduleMeta['host_id'];
        $errorMessage = "Unable to add relation between Module Meta and default poller.";
        $statement = $pearDB->prepare(
            "INSERT INTO ns_host_relation(`nagios_server_id`, `host_host_id`)
            VALUES(
                (SELECT id FROM nagios_server WHERE localhost = '1'),
                (:moduleMetaId)
            )
            ON DUPLICATE KEY UPDATE nagios_server_id = nagios_server_id"
        );
        $statement->bindValue(':moduleMetaId', (int) $moduleMetaId, \PDO::PARAM_INT);
        $statement->execute();
    }
    $pearDB->commit();
} catch (\Exception $e) {
    $pearDB->rollBack();
    $centreonLog->insertLog(
        4,
        $versionOfTheUpgrade . $errorMessage .
        " - Code : " . (int)$e->getCode() .
        " - Error : " . $e->getMessage() .
        " - Trace : " . $e->getTraceAsString()
    );
    throw new \Exception($versionOfTheUpgrade . $errorMessage, (int)$e->getCode(), $e);
}
