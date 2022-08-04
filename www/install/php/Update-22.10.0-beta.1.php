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

    $pearDB->beginTransaction();

    $errorMessage = "Unable to delete 'oreon_web_path' option from database";
    $pearDB->query("DELETE FROM `options` WHERE `key` = 'oreon_web_path'");

    $errorMessage = "Unable to delete 'appKey' information from database";
    $pearDB->query("DELETE FROM `informations` WHERE `key` = 'appKey'");

    $errorMessage = "Impossible to add new BBDO streams";
    createBbdoStreamConfigurationForms($pearDB);

    $pearDB->commit();

    if ($pearDB->isColumnExist('remote_servers', 'app_key') === 1) {
        $errorMessage = "Unable to drop 'app_key' from remote_servers table";
        $pearDB->query("ALTER TABLE remote_servers DROP COLUMN `app_key`");
    }
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

    throw new \Exception($versionOfTheUpgrade . $errorMessage, (int) $e->getCode(), $e);
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

    $pearDB->query("INSERT INTO cb_list_values VALUES (13, 'gRPC', 'gRPC'), (13, 'TCP', 'TCP')");
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
 */
function insertFieldOptions(CentreonDB $pearDB, array $field): void
{
    $fieldOptionsStmt = $pearDB->prepare(
        "INSERT INTO cb_list (cb_list_id, cb_field_id, default_value) VALUES (:listId, :fieldId, :defaultValue)"
    );
    $fieldOptionsStmt->bindValue(':listId', $field['optionListId'], \PDO::PARAM_INT);
    $fieldOptionsStmt->bindValue(':fieldId', $field['id'], \PDO::PARAM_INT);
    $fieldOptionsStmt->bindValue(':defaultValue', $field['defaultValue'], \PDO::PARAM_STR);
    $fieldOptionsStmt->execute();
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
            "optionListId" => 13,
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
            "optionListId" => 1,
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
            "optionListId" => 1,
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
            "optionListId" => 1,
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
            "optionListId" => 13,
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
            "optionListId" => 1,
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
            "optionListId" => 1,
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
