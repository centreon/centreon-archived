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

require_once __DIR__ . '/../../class/centreonLog.class.php';

$centreonLog = new CentreonLog();

//error specific content
$versionOfTheUpgrade = 'UPGRADE - 22.04.1: ';
$errorMessage = '';

try {
    $pearDB->beginTransaction();

    $errorMessage = "Unable to update 'custom_configuration' column on 'provider_configuration' table";
    updateOpenIdConfiguration($pearDB);

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

/**
 * Update OpenID Configuration with Auto Import options
 *
 * @param CentreonDB $pearDB
 */
function updateOpenIdConfiguration(CentreonDB $pearDB): void
{
    $statement = $pearDB->query("SELECT custom_configuration FROM provider_configuration WHERE name='openid'");
    if ($statement !== false && $result = $statement->fetch(\PDO::FETCH_ASSOC)) {
        $openIdCustomConfiguration = json_decode($result['custom_configuration'], true);
        $openIdCustomConfiguration["auto_import"] = false;
        $openIdCustomConfiguration["contact_template_id"] = null;
        $openIdCustomConfiguration["email_bind_attribute"] = null;
        $openIdCustomConfiguration["alias_bind_attribute"] = null;
        $openIdCustomConfiguration["fullname_bind_attribute"] = null;

        $statement = $pearDB->prepare(
            "UPDATE provider_configuration SET custom_configuration = :customConfiguration
            WHERE name='openid'"
        );
        $statement->bindValue(':customConfiguration', json_encode($openIdCustomConfiguration), \PDO::PARAM_STR);
        $statement->execute();
    } else {
        throw new \Exception('No custom_configuration for open_id has been found');
    }
}