<?php

/*
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
 *
 */


include_once __DIR__ . "/../../class/centreonLog.class.php";
$centreonLog = new CentreonLog();

//error specific content
$versionOfTheUpgrade = 'UPGRADE - 21.10.4: ';

/**
 * Query with transaction
 */
try {
    $pearDB->beginTransaction();
    $errorMessage = 'Unable to delete logger entry in cb_tag';
    $statement = $pearDB->query("DELETE FROM cb_tag WHERE tagname = 'logger'");
    $errorMessage = 'Unable to update the description in cb_field';
    $statement = $pearDB->query("
        UPDATE cb_field
        SET `description` = 'Time in seconds to wait between each connection attempt. The default value is 30s.'
        WHERE `cb_field_id` = 31
    ");

    $errorMessage  = 'Unable to delete logger entry in cb_tag';
    $statement = $pearDB->query("DELETE FROM cb_tag WHERE tagname = 'logger'");

    $pearDB->commit();
} catch (\Exception $e) {
    if ($pearDB->inTransaction()) {
        $pearDB->rollBack();
    }
    $centreonLog->insertLog(
        4,
        $versionOfTheUpgrade . $errorMessage .
        " - Code : " . (int)$e->getCode() .
        " - Error : " . $e->getMessage() .
        " - Trace : " . $e->getTraceAsString()
    );
    throw new \Exception($versionOfTheUpgrade . $errorMessage, (int)$e->getCode(), $e);
}
