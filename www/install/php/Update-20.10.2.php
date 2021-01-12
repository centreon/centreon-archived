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

include_once __DIR__ . "/../../class/centreonLog.class.php";
$centreonLog = new CentreonLog();

//error specific content
$versionOfTheUpgrade = 'UPGRADE - 20.10.2 : ';

/**
 * Queries needing exception management and rollback if failing
 */
try {
    $pearDB->beginTransaction();
    $stmt = $pearDB->query(
        "SELECT contact_id, contact_lang FROM contact
        WHERE contact_lang NOT LIKE '%UTF-8' AND contact_lang <> 'browser' AND contact_lang <> '';"
    );
    $errorMessage = "Unable to Update user language";
    $updateDB = $pearDB->prepare(
        "UPDATE contact SET contact_lang = :lang WHERE contact_id = :contactId"
    );
    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $completeLang = $row['contact_lang'] . '.UTF-8';
            $updateDB->bindValue(':lang', $completeLang, \PDO::PARAM_STR);
            $updateDB->bindValue(':contactId', $row['contact_id'], \PDO::PARAM_INT);
            $updateDB->execute();
    }
    $pearDB->commit();
    $errorMessage = "";
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
