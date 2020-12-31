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
$versionOfTheUpgrade = 'UPGRADE - 21.04.0-beta.1 : ';

/**
 * Queries needing exception management and rollback if failing
 */
try {
    $tables = [
        'dependency_hostChild_relation',
        'dependency_hostParent_relation',
        'dependency_hostgroupChild_relation',
        'dependency_hostgroupParent_relation',
        'dependency_metaserviceChild_relation',
        'dependency_metaserviceParent_relation',
        'dependency_serviceChild_relation',
        'dependency_serviceParent_relation',
        'dependency_servicegroupChild_relation',
        'dependency_servicegroupParent_relation'
    ];
    $pearDB->beginTransaction();

    foreach ($tables as $table) {
        $errorMessage = "Unable to check $tables dependencies";
        $result = $pearDB->query(
            "SELECT COUNT( * ) AS duplicate, host_host_id, service_service_id, dependency_dep_id
        FROM {$tables}
        GROUP BY host_host_id, dependency_dep_id, service_service_id"
        );
        while ($row = $result->fetch()) {
            if ($row['duplicate'] > 1) {
                $depId = $row['dependency_dep_id'];
                $hostId = $row['host_host_id'];
                $serviceId = $row['service_service_id'];

                $errorMessage = "Unable to delete $tables";
                $pearDB->query(
                    "DELETE FROM {$tables}
                WHERE dependency_dep_id = " . $depId . "
                AND host_host_id =  " . $hostId . "
                AND service_service_id = " . $serviceId
                );
                $pearDB->commit();

                $errorMessage = "Unable to insert $tables";
                $pearDB->query(
                    "INSERT INTO {$tables}
                VALUES ( " . $depId . ", " . $serviceId . ", " . $hostId . ")"
                );
                $pearDB->commit();
            }
        }
    }
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
