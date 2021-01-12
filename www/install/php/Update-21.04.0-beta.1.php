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
    $errorMessage = "Unable to update dependency_hostChild_relation";
    $pearDB->query(
        "ALTER IGNORE TABLE `dependency_hostChild_relation`
        ADD UNIQUE (`dependency_dep_id`, `host_host_id`)"
    );
    $errorMessage = "Unable to update dependency_hostParent_relation";
    $pearDB->query(
        "ALTER IGNORE TABLE `dependency_hostParent_relation`
        ADD UNIQUE (`dependency_dep_id`, `host_host_id`)"
    );
    $errorMessage = "Unable to update dependency_hostgroupChild_relation";
    $pearDB->query(
        "ALTER IGNORE TABLE `dependency_hostgroupChild_relation`
        ADD UNIQUE (`dependency_dep_id`, `hostgroup_hg_id`)"
    );
    $errorMessage = "Unable to update dependency_hostgroupParent_relation";
    $pearDB->query(
        "ALTER IGNORE TABLE `dependency_hostgroupParent_relation`
        ADD UNIQUE (`dependency_dep_id`, `hostgroup_hg_id`)"
    );
    $errorMessage = "Unable to update dependency_metaserviceChild_relation";
    $pearDB->query(
        "ALTER IGNORE TABLE `dependency_metaserviceChild_relation`
        ADD UNIQUE (`dependency_dep_id`, `meta_service_meta_id`)"
    );
    $errorMessage = "Unable to update dependency_metaserviceParent_relation";
    $pearDB->query(
        "ALTER IGNORE TABLE `dependency_metaserviceParent_relation`
        ADD UNIQUE (`dependency_dep_id`, `meta_service_meta_id`)"
    );
    $errorMessage = "Unable to update dependency_serviceChild_relation";
    $pearDB->query(
        "ALTER IGNORE TABLE `dependency_serviceChild_relation`
        ADD UNIQUE (`dependency_dep_id`, `service_service_id`, `host_host_id`)"
    );
    $errorMessage = "Unable to update dependency_serviceParent_relation";
    $pearDB->query(
        "ALTER IGNORE TABLE `dependency_serviceParent_relation`
        ADD UNIQUE (`dependency_dep_id`, `service_service_id`, `host_host_id`)"
    );
    $errorMessage = "Unable to update dependency_servicegroupChild_relation";
    $pearDB->query(
        "ALTER IGNORE TABLE `dependency_servicegroupChild_relation`
        ADD UNIQUE (`dependency_dep_id`, `servicegroup_sg_id`)"
    );
    $errorMessage = "Unable to update dependency_servicegroupParent_relation";
    $pearDB->query(
        "ALTER IGNORE TABLE `dependency_servicegroupParent_relation`
        ADD UNIQUE (`dependency_dep_id`, `servicegroup_sg_id`)"
    );
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
