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
$versionOfTheUpgrade = 'UPGRADE - 22.10.0-beta.1: ';
$errorMessage = '';

try {
    $errorMessage = "Impossible to update 'cb_field' table";
    $pearDB->query("ALTER TABLE cb_field MODIFY description VARCHAR(510) DEFAULT NULL");

    $errorMessage = "Impossible to update 'hosts' table";
    if (! str_contains(strtolower($pearDBO->getColumnType('hosts', 'notification_number')), 'bigint')) {
        $pearDBO->beginTransaction();
        $pearDBO->query("UPDATE `hosts` SET `notification_number`= 0 WHERE `notification_number`< 0");
        $pearDBO->query("ALTER TABLE `hosts` MODIFY `notification_number` BIGINT(20) UNSIGNED DEFAULT NULL");
    }

    $errorMessage = "Impossible to update 'services' table";
    if (! str_contains(strtolower($pearDBO->getColumnType('services', 'notification_number')), 'bigint')) {
        $pearDBO->beginTransaction();
        $pearDBO->query("UPDATE `services` SET `notification_number`= 0 WHERE `notification_number`< 0");
        $pearDBO->query("ALTER TABLE `services` MODIFY `notification_number` BIGINT(20) UNSIGNED DEFAULT NULL");
    }

    $errorMessage = "Impossible to create 'security_provider_contact_group_relation'";
    $pearDB->query("CREATE TABLE IF NOT EXISTS `security_provider_contact_group_relation` (
        `claim_value` VARCHAR(255) NOT NULL,
        `contact_group_id` int(11) NOT NULL,
        `provider_configuration_id` int(11) NOT NULL,
        PRIMARY KEY (`claim_value`, `contact_group_id`, `provider_configuration_id`),
        CONSTRAINT `security_provider_contact_group_id`
          FOREIGN KEY (`contact_group_id`)
          REFERENCES `contactgroup` (`cg_id`) ON DELETE CASCADE,
        CONSTRAINT `security_provider_configuration_provider_id`
          FOREIGN KEY (`provider_configuration_id`)
          REFERENCES `provider_configuration` (`id`) ON DELETE CASCADE
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

    $pearDB->beginTransaction();

    $errorMessage = "Unable to delete 'oreon_web_path' and color options from database";
    $pearDB->query("DELETE FROM `options` WHERE `key` = 'oreon_web_path' OR `key` LIKE 'color_%'");

    $errorMessage = "Unable to delete 'appKey' information from database";
    $pearDB->query("DELETE FROM `informations` WHERE `key` = 'appKey'");

    $errorMessage = "Impossible to add new BBDO streams";
    createBbdoStreamConfigurationForms($pearDB);

    $errorMessage = "Impossible to update pollers ACLs";
    updatePollerAcls($pearDB);

    $errorMessage = "Impossible to update OpenID Provider configuration";
    updateOpenIdCustomConfiguration($pearDB);
    $pearDB->commit();

    if ($pearDB->isColumnExist('remote_servers', 'app_key') === 1) {
        $errorMessage = "Unable to drop 'app_key' from remote_servers table";
        $pearDB->query("ALTER TABLE remote_servers DROP COLUMN `app_key`");
    }

    if ($pearDB->isColumnExist('remote_servers', 'server_id') === 0) {
        $errorMessage = "Unable to add 'server_id' column to remote_servers table";
        $pearDB->query(
            "ALTER TABLE remote_servers
            ADD COLUMN `server_id` int(11) NOT NULL"
        );

        migrateRemoteServerRelations($pearDB);

        $errorMessage = "Unable to add foreign key constraint of remote_servers.server_id";
        $pearDB->query(
            "ALTER TABLE remote_servers
            ADD CONSTRAINT `remote_server_nagios_server_ibfk_1`
            FOREIGN KEY(`server_id`) REFERENCES `nagios_server` (`id`)
            ON DELETE CASCADE"
        );
    }
} catch (\Exception $e) {
    if ($pearDB->inTransaction()) {
        $pearDB->rollBack();
    }

    if ($pearDBO->inTransaction()) {
        $pearDBO->rollBack();
    }

    $centreonLog->insertLog(
        4,
        $versionOfTheUpgrade . $errorMessage .
        " - Code : " . (int)$e->getCode() .
        " - Error : " . $e->getMessage() .
        " - Trace : " . $e->getTraceAsString()
    );

    throw new \Exception($versionOfTheUpgrade . $errorMessage, (int) $e->getCode(), $e);
}

/**
 * Manage relations between remote servers and nagios servers
 *
 * @param \CentreonDB $pearDB
 */
function migrateRemoteServerRelations(\CentreonDB $pearDB): void
{
    $processedIps = [];

    $selectServerStatement = $pearDB->prepare(
        "SELECT id FROM nagios_server WHERE ns_ip_address = :ip_address"
    );
    $deleteRemoteStatement = $pearDB->prepare(
        "DELETE FROM remote_servers WHERE id = :id"
    );
    $updateRemoteStatement = $pearDB->prepare(
        "UPDATE remote_servers SET server_id = :server_id WHERE id = :id"
    );

    $result = $pearDB->query(
        "SELECT id, ip FROM remote_servers"
    );
    while ($remote = $result->fetch()) {
        $remoteIp = $remote['ip'];
        $remoteId = $remote['id'];
        if (in_array($remoteIp, $processedIps)) {
            $deleteRemoteStatement->bindValue(':id', $remoteId, \PDO::PARAM_INT);
            $deleteRemoteStatement->execute();
        }

        $processedIps[] = $remoteIp;

        $selectServerStatement->bindValue(':ip_address', $remoteIp, \PDO::PARAM_STR);
        $selectServerStatement->execute();
        if ($server = $selectServerStatement->fetch()) {
            $updateRemoteStatement->bindValue(':server_id', $server['id'], \PDO::PARAM_INT);
            $updateRemoteStatement->bindValue(':id', $remoteId, \PDO::PARAM_INT);
            $updateRemoteStatement->execute();
        } else {
            $deleteRemoteStatement->bindValue(':id', $remoteId, \PDO::PARAM_INT);
            $deleteRemoteStatement->execute();
        }
    }
}

/**
 * @param CentreonDB $pearDB
 * @throws \Exception
 */
function updatePollerAcls(CentreonDB $pearDB): void
{
    $stmt = $pearDB->query(
        "SELECT topology_id FROM topology WHERE topology_page = 60901"
    );
    $pollersTopologyId = $stmt->fetch();
    if ($pollersTopologyId === false) {
        return;
    }
    $pollersTopologyId = (int) $pollersTopologyId['topology_id'];

    updatePollerActionsAcls($pearDB, $pollersTopologyId);
    updatePollerMenusAcls($pearDB, $pollersTopologyId);
}

/**
 * @param CentreonDB $pearDB
 * @param int $topologyId
 * @throws \Exception
 */
function updatePollerMenusAcls(CentreonDB $pearDB, int $topologyId): void
{
    $stmt = $pearDB->prepare(
        "UPDATE acl_topology_relations SET access_right = '1'
        WHERE access_right = '2' AND topology_topology_id = :topologyId"
    );
    $stmt->bindValue(':topologyId', $topologyId, \PDO::PARAM_INT);
    $stmt->execute();

    $stmt = $pearDB->prepare("UPDATE topology SET readonly = '1' WHERE topology_id = :topologyId");
    $stmt->bindValue(':topologyId', $topologyId, \PDO::PARAM_INT);
    $stmt->execute();
}

/**
 * @param CentreonDB $pearDB
 * @param int $topologyId
 * @throws \Exception
 */
function updatePollerActionsAcls(CentreonDB $pearDB, int $topologyId): void
{
    // Get ACL action ids linked to pollers page with read/write access
    $stmt = $pearDB->prepare(
        "SELECT DISTINCT(gar.acl_action_id) FROM acl_group_actions_relations gar
        JOIN acl_group_topology_relations gtr ON gar.acl_group_id = gtr.acl_group_id
        JOIN acl_topology_relations tr ON tr.acl_topo_id = gtr.acl_topology_id
        WHERE tr.topology_topology_id = :topologyId AND tr.access_right = '1'"
    );
    $stmt->bindValue(':topologyId', $topologyId, \PDO::PARAM_INT);
    $stmt->execute();

    $actionIdsToUpdate = $stmt->fetchAll(\PDO::FETCH_COLUMN, 0);
    if (empty($actionIdsToUpdate)) {
        return;
    }

    // Get ACL action ids linked to pollers page without read/write access
    $stmt = $pearDB->prepare(
        "SELECT DISTINCT(gar.acl_action_id) FROM acl_group_actions_relations gar
        JOIN acl_group_topology_relations gtr ON gar.acl_group_id = gtr.acl_group_id
        WHERE gtr.acl_topology_id NOT IN (
            SELECT acl_topo_id FROM acl_topology_relations
            WHERE topology_topology_id = :topologyId AND access_right = '1'
        )"
    );
    $stmt->bindValue(':topologyId', $topologyId, \PDO::PARAM_INT);
    $stmt->execute();

    $actionIdsToExclude = $stmt->fetchAll(\PDO::FETCH_COLUMN, 0);

    foreach ($actionIdsToUpdate as $actionId) {
        /**
         * Do not update ACL action linked to write AND read only / none pollers page access
         * so the most restrictive access wins
         */
        if (in_array($actionId, $actionIdsToExclude)) {
            continue;
        }

        $stmt = $pearDB->prepare(
            "INSERT INTO acl_actions_rules (acl_action_rule_id, acl_action_name) VALUES
            (:actionId, 'create_edit_poller_cfg'), (:actionId, 'delete_poller_cfg')"
        );
        $stmt->bindValue(':actionId', $actionId);
        $stmt->execute();
    }
}

/**
 * @param CentreonDb $pearDB
 */
function createBbdoStreamConfigurationForms(CentreonDb $pearDB): void
{
    $streams = insertStreams($pearDB);
    $fields = getFieldsDetails();

    $tagTypeRelationStmt = $pearDB->prepare('INSERT INTO cb_tag_type_relation VALUES (1, :typeId, 0), (2, :typeId, 0)');

    foreach ($streams as $id => $name) {
        $tagTypeRelationStmt->bindValue(':typeId', $id, \PDO::PARAM_INT);
        $tagTypeRelationStmt->execute();

        $fields[$name] = insertFields($pearDB, $fields[$name]);
        linkFieldsToStreamType($pearDB, $id, $fields[$name]);
    }
}

/**
 * @param CentreonDB $pearDB
 * @param int $streamTypeId
 * @param array<string,string|int|null> $fields
 */
function linkFieldsToStreamType(CentreonDB $pearDB, int $streamTypeId, array $fields): void
{
    $typeFieldRelationStmt = $pearDB->prepare(
        'INSERT INTO cb_type_field_relation
        (cb_type_id, cb_field_id, is_required, order_display, jshook_name, jshook_arguments)
        VALUES (:typeId, :fieldId, :isRequired, :orderDisplay, :jshook_name, :jshook_arguments)'
    );

    foreach ($fields as $key => $field) {
        $typeFieldRelationStmt->bindValue(':typeId', $streamTypeId, \PDO::PARAM_INT);
        $typeFieldRelationStmt->bindValue(':fieldId', $field['id'], \PDO::PARAM_INT);
        $typeFieldRelationStmt->bindValue(':isRequired', $field['isRequired'], \PDO::PARAM_STR);
        $typeFieldRelationStmt->bindValue(':orderDisplay', $key, \PDO::PARAM_STR);
        $typeFieldRelationStmt->bindValue(':jshook_name', $field['jsHook'] ?? null, \PDO::PARAM_STR);
        $typeFieldRelationStmt->bindValue(':jshook_arguments', $field['jsArguments'] ?? null, \PDO::PARAM_STR);
        $typeFieldRelationStmt->execute();
    }
}

/**
 * @param CentreonDB $pearDB
 * @return array<int,string>
 */
function insertStreams(CentreonDB $pearDB): array
{
    $pearDB->query("INSERT INTO cb_module VALUES (NULL, 'BBDO', NULL, NULL, 0, 1)");
    $moduleId = $pearDB->lastInsertId();

    $stmt = $pearDB->prepare(
        "INSERT INTO cb_type (type_name, type_shortname, cb_module_id) VALUES
        ('BBDO Server', 'bbdo_server', :moduleId),
        ('BBDO Client', 'bbdo_client', :moduleId)"
    );
    $stmt->bindValue(':moduleId', $moduleId, \PDO::PARAM_INT);
    $stmt->execute();

    $stmt = $pearDB->query(
        "SELECT cb_type_id, type_shortname FROM cb_type WHERE type_shortname in ('bbdo_server', 'bbdo_client')"
    );

    return $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
}

/**
 * @param CentreonDB $pearDB
 * @param array<string,string|int|null> $fields
 * @return array<string,string|int|null>
 */
function insertFields(CentreonDB $pearDB, array $fields): array
{
    $fieldStmt = $pearDB->prepare(
        "INSERT INTO cb_field (fieldname, displayname, fieldtype, description) VALUES
        (:fieldname, :displayname, :fieldtype, :description)"
    );

    foreach ($fields as &$field) {
        $fieldStmt->bindValue(':fieldname', $field['fieldname'], \PDO::PARAM_STR);
        $fieldStmt->bindValue(':displayname', $field['displayname'], \PDO::PARAM_STR);
        $fieldStmt->bindValue(':fieldtype', $field['fieldtype'], \PDO::PARAM_STR);
        $fieldStmt->bindValue(':description', $field['description'], \PDO::PARAM_STR);
        $fieldStmt->execute();

        $field['id'] = $pearDB->lastInsertId();

        if (in_array($field['fieldtype'], ['radio', 'multiselect'])) {
            insertFieldOptions($pearDB, $field);
        }
    }

    return $fields;
}

/**
 * @param CentreonDB $pearDB
 * @param array<string,string|int|null> $field
 * @throws \Exception
 */
function insertFieldOptions(CentreonDB $pearDB, array $field): void
{
    if (in_array($field['fieldname'], ['encryption', 'compression', 'retention'])) {
        $field['optionListId'] = findListIdByFieldname($pearDB, 'config');
    } else {
        $field['optionListId'] = findListIdByFieldname($pearDB, $field['fieldname']);
    }

    $fieldOptionsStmt = $pearDB->prepare(
        "INSERT INTO cb_list (cb_list_id, cb_field_id, default_value) VALUES (:listId, :fieldId, :defaultValue)"
    );
    $fieldOptionsStmt->bindValue(':listId', $field['optionListId'], \PDO::PARAM_INT);
    $fieldOptionsStmt->bindValue(':fieldId', $field['id'], \PDO::PARAM_INT);
    $fieldOptionsStmt->bindValue(':defaultValue', $field['defaultValue'], \PDO::PARAM_STR);
    $fieldOptionsStmt->execute();

    if ($field['fieldname'] === 'transport_protocol') {
        insertGrpcListOptions($pearDB, $field['optionListId']);
    }
}

/**
 * Retrieve a list id based on an existing field name already attached to it
 *
 * @param CentreonDB $pearDB
 * @param string $fieldname
 * @return int
 * @throws \Exception
 */
function findListIdByFieldname(CentreonDB $pearDB, string $fieldname): int
{
    $stmt = $pearDB->prepare(
        "SELECT l.cb_list_id FROM cb_list l, cb_field f
        WHERE l.cb_field_id = f.cb_field_id AND f.fieldname = :fieldname"
    );
    $stmt->bindValue(':fieldname', $fieldname, \PDO::PARAM_STR);
    $stmt->execute();

    $listId  = $stmt->fetchColumn();

    if ($listId === false) {
        if ($fieldname === 'transport_protocol') {
            $stmt = $pearDB->query("SELECT MAX(cb_list_id) FROM cb_list_values");
            $maxId = $stmt->fetchColumn();
            if ($maxId === false) {
                throw new Exception("Cannot find biggest cb_list_id in cb_list_values table");
            }
            $listId = $maxId + 1;
        } else {
            throw new Exception("Cannot find cb_list_id in cb_list_values table");
        }
    }
    return $listId;
}

/**
 * @param CentreonDB $pearDB
 * @param int $listId
 * @return int id of the newly created list
 * @throws \Exception
 */
function insertGrpcListOptions(CentreonDB $pearDB, int $listId): void
{
    $stmt = $pearDB->prepare("SELECT 1 FROM cb_list_values where cb_list_id = :listId");
    $stmt->bindValue(':listId', $listId, \PDO::PARAM_INT);
    $stmt->execute();

    $doesListExist = $stmt->fetchColumn();
    if ($doesListExist) {
        return;
    }

    $insertStmt = $pearDB->prepare(
        "INSERT INTO cb_list_values VALUES (:listId, 'gRPC', 'gRPC'), (:listId, 'TCP', 'TCP')"
    );
    $insertStmt->bindValue(':listId', $listId, \PDO::PARAM_INT);
    $insertStmt->execute();
}

/**
 * @return array{bbdo_server:<string,string|int|null>,bbdo_client:<string,string|int|null>}
 */
function getFieldsDetails(): array
{
    $bbdoServer = [
        [
            "fieldname" => 'host',
            "displayname" => 'Listening address (optional)',
            "fieldtype" => 'text',
            "description" =>  'Fill in this field only if you want to specify the address on which Broker '
                . 'should listen',
            "isRequired" => 0
        ],
        [
            "fieldname" => 'port',
            "displayname" => 'Listening port',
            "fieldtype" => 'text',
            "description" => 'TCP port on which Broker should listen',
            "isRequired" => 1
        ],
        [
            "fieldname" => 'transport_protocol',
            "displayname" => 'Transport protocol',
            "fieldtype" => 'radio',
            "description" => 'The transport protocol can be either TCP (binary flow over TCP) or gRPC (HTTP2)',
            "isRequired" => 1,
            "defaultValue" => 'gRPC',
            "optionListId" => null,
            "jsHook" => 'bbdoStreams',
            "jsArguments" => '{"target": "authorization", "value": "gRPC"}',
        ],
        [
            "fieldname" => 'authorization',
            "displayname" => 'Authorization token (optional)',
            "fieldtype" => 'password',
            "description" => 'Authorization token to be requested from the client (must be the same for both client '
                . 'and server)',
            "isRequired" => 0,
        ],
        [
            "fieldname" => 'encryption',
            "displayname" => 'Enable TLS encryption',
            "fieldtype" => 'radio',
            "description" => 'Enable TLS 1.3 encryption',
            "isRequired" => 1,
            "defaultValue" => 'no',
            "optionListId" => null,
            "jsHook" => 'bbdoStreams',
            "jsArguments" => '{"target": ["private_key", "certificate"], "value": "yes"}',
        ],
        [
            "fieldname" => 'private_key',
            "displayname" => 'Private key path',
            "fieldtype" => 'text',
            "description" => 'Full path to the file containing the private key in PEM format (required for encryption)',
            "isRequired" => 0,
        ],
        [
            "fieldname" => 'certificate',
            "displayname" => 'Certificate path',
            "fieldtype" => 'text',
            "description" => 'Full path to the file containing the certificate in PEM format (required for encryption)',
            "isRequired" => 0,
        ],
        [
            "fieldname" => 'compression',
            "displayname" => 'Compression',
            "fieldtype" => 'radio',
            "description" => 'Enable data compression',
            "isRequired" => 1,
            "defaultValue" => 'no',
            "optionListId" => null,
            "jsHook" => 'bbdoStreams',
            "jsArguments" => '{"tag": "output"}',
        ],
        [
            "fieldname" => 'retention',
            "displayname" => 'Enable retention',
            "fieldtype" => 'radio',
            "description" => 'Enable data retention until the client is connected',
            "isRequired" => 1,
            "defaultValue" => 'no',
            "optionListId" => null,
            "jsHook" => 'bbdoStreams',
            "jsArguments" => '{"tag": "output"}',
        ],
        [
            "fieldname" => 'category',
            "displayname" => 'Filter on event categories',
            "fieldtype" => 'multiselect',
            "description" => 'Broker event categories to filter. If none is selected, all categories of events will '
                . 'be processed',
            "isRequired" => 0,
            "defaultValue" => null,
            "optionListId" => null,
        ],
    ];

    $bbdoClient = [
        [
            "fieldname" => 'host',
            "displayname" => 'Server address',
            "fieldtype" => 'text',
            "description" => 'Address of the server to which the client should connect',
            "isRequired" => 1,
        ],
        [
            "fieldname" => 'port',
            "displayname" => 'Server port',
            "fieldtype" => 'int',
            "description" => 'TCP port of the server to which the client should connect',
            "isRequired" => 1,
        ],
        [
            "fieldname" => 'retry_interval',
            "displayname" => 'Retry interval (seconds)',
            "fieldtype" => 'int',
            "description" => 'Number of seconds between a lost or failed connection and the next try',
            "isRequired" => 0,
        ],
        [
            "fieldname" => 'transport_protocol',
            "displayname" => 'Transport protocol',
            "fieldtype" => 'radio',
            "description" => 'The transport protocol can be either TCP (binary flow over TCP) or gRPC (HTTP2)',
            "isRequired" => 1,
            "defaultValue" => 'gRPC',
            "optionListId" => null,
            "jsHook" => 'bbdoStreams',
            "jsArguments" => '{"target": "authorization", "value": "gRPC"}',
        ],
        [
            "fieldname" => 'authorization',
            "displayname" => 'Authorization token (optional)',
            "fieldtype" => 'password',
            "description" => 'Authorization token expected by the server (must be the same for both client and server)',
            "isRequired" => 0,
        ],
        [
            "fieldname" => 'encryption',
            "displayname" => 'Enable TLS encryption',
            "fieldtype" => 'radio',
            "description" => 'Enable TLS 1.3 encryption',
            "isRequired" => 1,
            "defaultValue" => 'no',
            "optionListId" => null,
            "jsHook" => 'bbdoStreams',
            "jsArguments" => '{"target": ["ca_certificate", "ca_name"], "value": "yes"}',
        ],
        [
            "fieldname" => 'ca_certificate',
            "displayname" => 'Trusted CA\'s certificate path (optional)',
            "fieldtype" => 'text',
            "description" => "If the server's certificate is signed by an untrusted Certification Authority (CA), "
                . "then specify the certificate's path.\nIf the server's certificate is self-signed, then specify "
                . "its path.\n You can also add the certificate to the store of certificates trusted by the operating "
                . "system.\nThe file must be in PEM format.",
            "isRequired" => 0,
        ],
        [
            "fieldname" => 'ca_name',
            "displayname" => 'Certificate Common Name (optional)',
            "fieldtype" => 'text',
            "description" => "If the Common Name (CN) of the certificate is different from the value in the "
                . "\"Server address\" field, the CN must be provided here",
            "isRequired" => 0,
        ],
        [
            "fieldname" => 'compression',
            "displayname" => 'Compression',
            "fieldtype" => 'radio',
            "description" => 'Enable data compression',
            "isRequired" => 1,
            "defaultValue" => 'no',
            "optionListId" => null,
            "jsHook" => 'bbdoStreams',
            "jsArguments" => '{"tag": "output"}',
        ],
        [
            "fieldname" => 'category',
            "displayname" => 'Filter on event categories',
            "fieldtype" => 'multiselect',
            "description" => 'Broker event categories to filter. If none is selected, all categories of events will '
                . 'be processed',
            "isRequired" => 0,
            "defaultValue" => null,
            "optionListId" => 6,
        ],
    ];

    return ['bbdo_server' => $bbdoServer, 'bbdo_client' => $bbdoClient];
}

/**
 * Update Open ID Provider with new parameters.
 *
 * @param CentreonDB $pearDB
 */
function updateOpenIdCustomConfiguration(CentreonDB $pearDB): void
{
    $statement = $pearDB->query("SELECT custom_configuration FROM provider_configuration WHERE `name`='openid'");

    if ($result = $statement->fetch()) {
        $customConfiguration = json_decode($result['custom_configuration'], true);

        /**
         * Remove trusted & blacklist client addresses from root of custom configuration
         * and add them to authentication conditions
         */
        $trustedClientAddresses = $customConfiguration['trusted_client_addresses'];
        $blacklistClientAddresses = $customConfiguration['blacklist_client_addresses'];
        unset($customConfiguration['trusted_client_addresses']);
        unset($customConfiguration['blacklist_client_addresses']);

        /**
         * Remove claim name and contact group id as they are know handled in roles mapping and groups mapping.
         */
        unset($customConfiguration['claim_name']);
        unset($customConfiguration['contact_group_id']);
        $customConfiguration['authentication_conditions'] = [
            'is_enabled' => false,
            'attribute_path' => '',
            'endpoint' => ['type' => 'introspection_endpoint', 'custom_endpoint' => ''],
            'authorized_values' => [],
            'trusted_client_addresses' => $trustedClientAddresses,
            'blacklist_client_addresses' => $blacklistClientAddresses
        ];
        $customConfiguration['roles_mapping'] = [
            'is_enabled' => false,
            'apply_only_first_role' => false,
            'attribute_path' => '',
            'endpoint' => ['type' => 'introspection_endpoint', 'custom_endpoint' => '']
        ];
        $customConfiguration['groups_mapping'] = [
            'is_enabled' => false,
            'attribute_path' => '',
            'endpoint' => ['type' => 'introspection_endpoint', 'custom_endpoint' => ''],
        ];

        $encodedConfiguration = json_encode($customConfiguration);

        $statement = $pearDB->prepare(
            "UPDATE provider_configuration SET custom_configuration = :customConfiguration WHERE `name`='openid'"
        );
        $statement->bindValue(':customConfiguration', $encodedConfiguration, \PDO::PARAM_STR);
        $statement->execute();
    }
}
