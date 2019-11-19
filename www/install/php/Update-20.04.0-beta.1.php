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

try {
    $pearDB->query(
        "UPDATE `contact` SET `contact_autologin_key` = NULL WHERE `contact_autologin_key` =''"
    );
} catch (\PDOException $e) {
    $centreonLog->insertLog(
        2,
        "UPGRADE : 19.10.2 Unable to set default contact_autologin_key"
    );
}


// Move broker xml files to json format
try {
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
        $configFilenames[$row['config_filename']] = $fileName;
        $statement->bindValue(':value', $fileName, \PDO::PARAM_STR);
        $statement->bindValue(':id', $row['config_id'], \PDO::PARAM_INT);
        $statement->execute();
    }
} catch (\PDOException $e) {
    $centreonLog->insertLog(
        2,
        "UPGRADE : 20.04.0-beta.1 Unable to move broker configuration from xml format to json format"
    );
    throw new \PDOException($e);
}

// Move engine module xml files to json format
try {
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
    }
} catch (\PDOException $e) {
    $centreonLog->insertLog(
        2,
        "UPGRADE : 20.04.0-beta.1 Unable to move engine's broker modules configuration from xml to json format"
    );
    throw new \PDOException($e);
}
