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
$versionOfTheUpgrade = 'UPGRADE - 21.04.1: ';

$pearDB = new CentreonDB('centreon', 3, false);

/**
 * Query with transaction
 */
try {
    $pearDB->beginTransaction();
    /**
     * Retrieve user filters
     */
    $statement = $pearDB->query(
        "SELECT `id`, `criterias` FROM `user_filter`"
    );

    $fixedCriteriaFilters = [];

    /**
     * Sort filter criteria was not correctly added during the 21.04.0
     * upgrade. It should be an array and not an object
     */
    while ($filter = $statement->fetch()) {
        $id = $filter['id'];
        $decodedCriterias = json_decode($filter['criterias'], true);
        foreach ($decodedCriterias as $criteriaKey => $criteria) {
            if ($criteria['name'] === 'sort') {
                $decodedCriterias[$criteriaKey]['value'] = [
                    'status_severity_code',
                    $criteria['value']['status_severity_code']
                ];
            }
        }

        $fixedCriteriaFilters[$id] = json_encode($decodedCriterias);
    }

    /**
     * UPDATE SQL request on filters
     */
    foreach ($fixedCriteriaFilters as $id => $criterias) {
        $errorMessage = "Unable to update filter values in user_filter table.";
        $statement = $pearDB->prepare(
            "UPDATE `user_filter` SET `criterias` = :criterias WHERE `id` = :id"
        );
        $statement->bindValue(':id', (int) $id, \PDO::PARAM_INT);
        $statement->bindValue(':criterias', $criterias, \PDO::PARAM_STR);
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
