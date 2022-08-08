<?php

/**
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
$versionOfTheUpgrade = 'UPGRADE - 20.10.0-beta.2 : ';

/**
 * Queries needing exception management and rollback if failing
 */
try {
    $pearDB->beginTransaction();

    /*
     * Move keycloak configuration to OpenId Connect one
     */
    $errorMessage = "Unable to move Keycloak configuration to OpenId Connect";
    $result = $pearDB->query(
        "SELECT * FROM options WHERE options.key IN ('keycloak_enable', 'keycloak_mode', 'keycloak_url',
        'keycloak_redirect_url', 'keycloak_realm', 'keycloak_client_id', 'keycloak_client_secret',
        'keycloak_trusted_clients', 'keycloak_blacklist_clients')"
    );

    $keycloak = [];
    while ($row = $result->fetch()) {
        $keycloak[$row['key']] = $row['value'];
    }

    $keycloakBaseUrl = null;
    if (!empty($keycloak['keycloak_url']) && !empty($keycloak['keycloak_realm'])) {
        $keycloakUrl = $keycloak['keycloak_url'] . "/realms/" .
            $keycloak['keycloak_realm'] . "/protocol/openid-connect";
    }
    $openIdConnect = [
        'openid_connect_enable' => $keycloak['keycloak_enable'] ?? null,
        'openid_connect_mode' => $keycloak['keycloak_mode'] ?? null,
        'openid_connect_base_url' => $keycloakBaseUrl,
        'openid_connect_authorization_endpoint' => isset($keycloak['keycloak_url']) ? '/auth' : null,
        'openid_connect_token_endpoint' => isset($keycloak['keycloak_url']) ? '/token' : null,
        'openid_connect_introspection_endpoint' => isset($keycloak['keycloak_url'])  ? '/introspect' : null,
        'openid_connect_redirect_url' => $keycloak['keycloak_redirect_url'] ?? null,
        'openid_connect_client_id' => $keycloak['keycloak_client_id'] ?? null,
        'openid_connect_client_secret' => $keycloak['keycloak_client_secret'] ?? null,
        'openid_connect_trusted_clients' => $keycloak['keycloak_trusted_clients'] ?? null,
        'openid_connect_blacklist_clients' => $keycloak['keycloak_blacklist_clients'] ?? null
    ];

    $statement = $pearDB->prepare(
        "INSERT INTO options (`key`, `value`) VALUES (:key, :value)"
    );
    foreach ($openIdConnect as $key => $value) {
        if (!is_null($value)) {
            $statement->bindValue(':key', $key, \PDO::PARAM_STR);
            $statement->bindValue(':value', $value, \PDO::PARAM_STR);
            $statement->execute();
        }
    }

    $pearDB->query(
        "DELETE FROM options WHERE options.key IN ('keycloak_enable', 'keycloak_mode', 'keycloak_url',
        'keycloak_redirect_url', 'keycloak_realm', 'keycloak_client_id', 'keycloak_client_secret',
        'keycloak_trusted_clients', 'keycloak_blacklist_clients')"
    );

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
