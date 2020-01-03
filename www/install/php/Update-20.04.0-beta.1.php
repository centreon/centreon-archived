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
include_once __DIR__ . "/../../class/centreonLog.class.php";
$centreonLog = new CentreonLog();

//error specific content
$versionOfTheUpgrade = 'UPGRADE - 20.04.0-beta.1 : ';
$errorMessage = '';

/**
 * @internal : Queries needing exception management and rollback if failing
 */
try {
    $pearDB->beginTransaction();
    /**
     * Move broker xml files to json format
     */
    $result = $pearDB->query(
        "SELECT config_id, config_filename
        FROM cfg_centreonbroker"
    );

    $statement = $pearDB->prepare(
        "UPDATE cfg_centreonbroker
        SET config_filename = :value
        WHERE config_id = :id"
    );

    $configFilenames = [];
    while ($row = $result->fetch()) {
        $fileName = str_replace('.xml', '.json', $row['config_filename']);

        // saving data for next engine module modifications
        $configFilenames[$row['config_filename']] = $fileName;

        $statement->bindValue(':value', $fileName, \PDO::PARAM_STR);
        $statement->bindValue(':id', $row['config_id'], \PDO::PARAM_INT);
        // saving error message to be thrown in case of failure
        $errorMessage = "Unable to move broker configuration from xml format to json format";
    }

    /**
     * Move engine module xml files to json format
     */
    $result = $pearDB->query(
        "SELECT bk_mod_id, broker_module
        FROM cfg_nagios_broker_module"
    );

    $statement = $pearDB->prepare(
        "UPDATE cfg_nagios_broker_module
        SET broker_module = :value
        WHERE bk_mod_id = :id"
    );
    while ($row = $result->fetch()) {
        $fileName = $row['broker_module'];
        foreach ($configFilenames as $oldName => $newName) {
            $fileName = str_replace($oldName, $newName, $fileName);
        }
        $statement->bindValue(':value', $fileName, \PDO::PARAM_STR);
        $statement->bindValue(':id', $row['bk_mod_id'], \PDO::PARAM_INT);
        $statement->execute();
        $errorMessage = "Unable to move engine's broker modules configuration from xml to json format";
    }

    /**
     * Change broker sql output form
     */
    // set common error message on failure
    $partialErrorMessage = "Unable to move engine's broker modules configuration from xml to json format";

    // reorganise existing input form
    $pearDB->query(
        "UPDATE cb_type_field_relation AS A INNER JOIN cb_type_field_relation AS B ON A.cb_type_id = B.cb_type_id
        SET A.`order_display` = 8 
        WHERE B.`cb_field_id` = (SELECT f.cb_field_id FROM cb_field f WHERE f.fieldname = 'buffering_timeout')"
    );
    $errorMessage = $partialErrorMessage . " - While trying to update 'cb_type_field_relation' table data";

    // add new connections_count input
    $pearDB->query(
        "INSERT INTO `cb_field` (`fieldname`, `displayname`, `description`, `fieldtype`, `external`) 
        VALUES ('connections_count', 'Number of connection to the database', 'Usually cpus/2', 'int', NULL)"
    );
    $errorMessage = $partialErrorMessage . " - While trying to insert in 'cb_field' table new values";

    // add relation
    $pearDB->query(
        "INSERT INTO `cb_type_field_relation` (
            `cb_type_id`,
            `cb_field_id`,
            `is_required`,
            `order_display`,
            `jshook_name`,
            `jshook_arguments`
        )
        VALUES (
            (SELECT `cb_type_id` FROM `cb_type` WHERE `type_shortname` = 'sql'),
            (SELECT `cb_field_id` FROM `cb_field` WHERE `fieldname` = 'connections_count'),
            0,
            7,
            'countConnections',
            '{\"target\": \"connections_count\"}'
        )"
    );
    $errorMessage = $partialErrorMessage . " - While trying to insert in 'cb_type_field_relation' table new values";

    $pearDB->commit();
    $centreonLog->insertLog(4, $versionOfTheUpgrade . "Successful update");
} catch (\Exception $e) {
    $pearDB->rollBack();
    $msg = $versionOfTheUpgrade . $errorMessage . " - Error : " . $e->getMessage();
    $centreonLog->insertLog(4, $msg);
    throw new \Exception ($msg);
} finally {
    /**
     * @internal : Queries which doesn't need rollback and won't throw an exception
     */

    /**
     * replace autologin keys using NULL instead of empty string
     */
    $query = "UPDATE `contact` SET `contact_autologin_key` = NULL WHERE `contact_autologin_key` =''";
    if (false === $pearDB->query($query)) {
        $centreonLog->insertLog(
            2,
            $versionOfTheUpgrade . "Unable to set default contact_autologin_key. No error was thrown."
        );
    }
}
