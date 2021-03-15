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
$versionOfTheUpgrade = 'UPGRADE - 21.04.0-beta.1: ';

$pearDB = new CentreonDB('centreon', 3, false);

$criteriasConcordanceArray = [
    'states' => [
        'acknowledged' => 'Acknowledged',
        'in_downtime' => 'In downtime',
        'unhandled_problems' => 'Unhandled',
    ],
    'resource_types' => [
        'host' => 'Host',
        'service' => 'Service',
    ],
    'statuses' => [
        'OK' => 'Ok',
        'UP' => 'Up',
        'WARNING' => 'Warning',
        'DOWN' => 'Down',
        'CRITICAL' => 'Critical',
        'UNREACHABLE' => 'Unreachable',
        'UNKNOWN' => 'Unknown',
        'PENDING' => 'Pending'
    ],
];

try {
    $pearDB->beginTransaction();
    /**
     * Retrieve user filters
     */
    $statement = $pearDB->query(
        "SELECT `id`, `criterias` FROM `user_filter`"
    );

    $translatedFilters = [];

    while ($filter = $statement->fetch()) {
        $id = $filter['id'];
        $decodedCriterias = json_decode($filter['criterias'], true);
        // Adding the default sorting in the criterias
        foreach ($decodedCriterias as $criteriaKey => $criteria) {
            $name = $criteria['name'];
            // Checking if the filter contains criterias we want to migrate
            if (array_key_exists($name, $criteriasConcordanceArray) === true) {
                foreach ($criteria['value'] as $index => $value) {
                    $decodedCriterias[$criteriaKey]['value'][$index]['name'] =
                        $criteriasConcordanceArray[$name][$value['id']];
                }
            }
        }

        $decodedCriterias[] = [
            'name' => 'sort',
            'type' => 'array',
            'value' => [
                'status_severity_code' => "asc"
            ],
        ];

        $translatedFilters[$id] = json_encode($decodedCriterias);
    }

    /**
     * UPDATE SQL request on the filters
     */

    foreach ($translatedFilters as $id => $criterias) {
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
