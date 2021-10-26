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
$versionOfTheUpgrade = 'UPGRADE - 21.10.0-rc.1: ';

/**
 * Query with transaction
 */
try {
    $pearDB->beginTransaction();

    $errorMessage = 'Impossible to clean openid options';

    $defaultValues = [
        'openid_connect_enable' => '0',
        'openid_connect_mode' => '1',
        'openid_connect_trusted_clients' => '',
        'openid_connect_blacklist_clients' => '',
        'openid_connect_base_url' => '',
        'openid_connect_authorization_endpoint' => '',
        'openid_connect_token_endpoint' => '',
        'openid_connect_introspection_endpoint' => '',
        'openid_connect_userinfo_endpoint' => '',
        'openid_connect_end_session_endpoint' => '',
        'openid_connect_scope' => '',
        'openid_connect_login_claim' => '',
        'openid_connect_redirect_url' => '',
        'openid_connect_client_id' => '',
        'openid_connect_client_secret' => '',
        'openid_connect_client_basic_auth' => '0',
        'openid_connect_verify_peer' => '0',
    ];

    $result = $pearDB->query("SELECT * FROM `options` WHERE options.key LIKE 'openid%'");
    $generalOptions = [];
    while ($row = $result->fetch()) {
        $generalOptions[$row["key"]] = $row["value"];
    }

    foreach ($defaultValues as $defaultValueName => $defautValue) {
        if (!isset($generalOptions[$defaultValueName])) {
            $statement = $pearDB->prepare('INSERT INTO `options` (`key`, `value`) VALUES (:option_key, :option_value)');
            $statement->bindValue(':option_key', $defaultValueName, \PDO::PARAM_STR);
            $statement->bindValue(':option_value', $defautValue, \PDO::PARAM_STR);
            $statement->execute();
        }
    }

    /**
     * Retrieve user filters
     */
    $statement = $pearDB->query(
        "SELECT `id`, `criterias` FROM `user_filter` WHERE `page_name` = 'events-view'"
    );

    $fixedCriteriaFilters = [];

    /**
     * Sort filter criteria was not correctly added during the 21.04.0
     * upgrade. It should be an array and not an object
     */
    $errorMessage = "Cannot parse filter values in user_filter table.";
    while ($filter = $statement->fetch()) {
        $id = $filter['id'];
        $decodedCriterias = json_decode($filter['criterias'], true);
        foreach ($decodedCriterias as $criteriaKey => $criteria) {
            if (
                $criteria['name'] === 'sort'
                && is_array($criteria['value'])
                && count($criteria['value']) === 2
                && $criteria['value'][0] === 'status_severity_code'
                && !in_array($criteria['value'][1], ['asc', 'desc'])
            ) {
                $decodedCriterias[$criteriaKey]['value'][1] = 'desc';
            }
        }

        $fixedCriteriaFilters[$id] = json_encode($decodedCriterias);
    }

    /**
     * UPDATE SQL request on filters
     */
    $errorMessage = "Unable to update filter sort values in user_filter table.";
    foreach ($fixedCriteriaFilters as $id => $criterias) {
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
