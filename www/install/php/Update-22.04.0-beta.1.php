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
$versionOfTheUpgrade = 'UPGRADE - 22.04.0-beta.1: ';

try {
    $errorMessage = "Unable to add column 'custom_configuration' to table 'provider_configuration'";
    $pearDB->query(
        "ALTER TABLE `provider_configuration` ADD COLUMN `custom_configuration` JSON NOT NULL AFTER `name`"
    );

    $errorMessage = "Unable to create 'password_expiration_excluded_users' table";
    $pearDB->query(
        "CREATE TABLE `password_expiration_excluded_users` (
        `provider_configuration_id` int(11) NOT NULL,
        `user_id` int(11) NOT NULL,
        CONSTRAINT `password_expiration_excluded_users_provider_configuration_id_fk`
          FOREIGN KEY (`provider_configuration_id`)
          REFERENCES `provider_configuration` (`id`) ON DELETE CASCADE,
        CONSTRAINT `password_expiration_excluded_users_provider_user_id_fk`
          FOREIGN KEY (`user_id`)
          REFERENCES `contact` (`contact_id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
    );

    $errorMessage = "Unable to insert default local security policy configuration";
    $localProviderConfiguration = json_encode([
        "password_security_policy" => [
            "password_length" => 12,
            "has_uppercase_characters" => true,
            "has_lowercase_characters" => true,
            "has_numbers" => true,
            "has_special_characters" => true,
            "attempts" => 5,
            "blocking_duration" => 900,
            "password_expiration_delay" => 7776000,
            "delay_before_new_password" => 3600,
            "can_reuse_passwords" => false,
        ],
    ]);
    $statement = $pearDB->prepare(
        "UPDATE `provider_configuration`
        SET `custom_configuration` = :localProviderConfiguration
        WHERE `name` = 'local'"
    );
    $statement->bindValue(':localProviderConfiguration', $localProviderConfiguration, \PDO::PARAM_STR);
    $statement->execute();

    $errorMessage = "Unable to create table 'contact_password'";
    $pearDB->query(
        "CREATE TABLE `contact_password` (
        `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `password` varchar(255) NOT NULL,
        `contact_id` int(11) NOT NULL,
        `creation_date` BIGINT UNSIGNED NOT NULL,
        PRIMARY KEY (`id`),
        KEY `contact_password_contact_id_fk` (`contact_id`),
        INDEX `creation_date_index` (`creation_date`),
        CONSTRAINT `contact_password_contact_id_fk` FOREIGN KEY (`contact_id`)
        REFERENCES `contact` (`contact_id`) ON DELETE CASCADE)"
    );

    $pearDB->beginTransaction();

    $errorMessage = "Unable to select existing passwords from 'contact' table";
    $dbResult = $pearDB->query(
        "SELECT `contact_id`, `contact_passwd` FROM `contact` WHERE `contact_passwd` IS NOT NULL"
    );
    $statement = $pearDB->prepare(
        "INSERT INTO `contact_password` (`password`, `contact_id`, `creation_date`)
        VALUES (:password, :contactId, :creationDate)"
    );

    $errorMessage = "Unable to insert password in 'contact_password' table";
    while ($row = $dbResult->fetch()) {
        $statement->bindValue(':password', $row['contact_passwd'], \PDO::PARAM_STR);
        $statement->bindValue(':contactId', $row['contact_id'], \PDO::PARAM_INT);
        $statement->bindValue(':creationDate', time(), \PDO::PARAM_INT);
        $statement->execute();
    }

    $pearDB->commit();

    $errorMessage = "Unable to drop column 'contact_passwd' from 'contact' table";
    $pearDB->query("ALTER TABLE `contact` DROP COLUMN `contact_passwd`");

    $errorMessage = 'Impossible to add "contact_js_effects" column to "contact" table';
    if (!$pearDB->isColumnExist('contact', 'contact_js_effects')) {
        $pearDB->query(
            "ALTER TABLE `contact`
            ADD COLUMN `contact_js_effects` enum('0','1') DEFAULT '0'
            AFTER `contact_comment`"
        );
    }

    $errorMessage = 'Unable to update the description in cb_field';
    $statement = $pearDB->query("
        UPDATE cb_field
        SET `description` = 'Time in seconds to wait between each connection attempt (Default value: 30s).'
        WHERE `cb_field_id` = 31
    ");

    $errorMessage = 'Unable to delete logger entry in cb_tag';
    $statement = $pearDB->query("DELETE FROM cb_tag WHERE tagname = 'logger'");

    $errorMessage = 'Unable to delete old logger configuration';
    $statement = $pearDB->query("DELETE FROM cfg_centreonbroker_info WHERE config_group = 'logger'");

    $errorMessage = 'Impossible to add "login_attempts" and "blocking_time" columns to "contact" table';
    $pearDB->query(
        "ALTER TABLE `contact`
        ADD `login_attempts` INT(11) UNSIGNED DEFAULT NULL,
        ADD `blocking_time` BIGINT(20) UNSIGNED DEFAULT NULL"
    );

    /**
     * Add new UnifiedSQl broker output
     */
    $pearDB->beginTransaction();

    $errorMessage = 'Unable to update cb_type table ';
    $pearDB->query(
        "UPDATE `cb_type` set type_name = 'Perfdata Generator (Centreon Storage) - DEPRECATED'
        WHERE type_shortname = 'storage'"
    );
    $pearDB->query(
        "UPDATE `cb_type` set type_name = 'Broker SQL database - DEPRECATED'
        WHERE type_shortname = 'sql'"
    );

    $errorMessage = "Unable to add 'unifed_sql' broker configuration output";
    addNewUnifiedSqlOutput($pearDb);

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
 * Handle new broker output creation 'unified_sql'
 *
 * @param CentreonDB $pearDB
 */
function addNewUnifiedSqlOutput(CentreonDB $pearDB): void
{
    // Add new output type 'unified_sql'
    $statement = $pearDB->query("SELECT cb_module_id FROM cb_module WHERE name = 'Storage'");
    $module = $statement->fetch();
    if ($module === false) {
        throw new Exception("Cannot find 'Storage' module in cb_module table");
    }
    $moduleId = $module['cb_module_id'];

    $pearDB->query(
        "INSERT INTO `cb_type` (`type_name`, `type_shortname`, `cb_module_id`)
        VALUES ('Unified SQL', 'unified_sql', $moduleId)"
    );
    $typeId = $pearDB->lastInsertId();

    // Link new type to tag 'output'
    $statement = $pearDB->query("SELECT cb_tag_id FROM cb_tag WHERE tagname = 'Output'");
    $tag = $statement->fetch();
    if ($tag === false) {
        throw new Exception("Cannot find 'Output' tag in cb_tag table");
    }
    $tagId = $tag['cb_tag_id'];

    $pearDB->query(
        "INSERT INTO `cb_tag_type_relation` (`cb_tag_id`, `cb_type_id`, `cb_type_uniq`)
        VALUES ($tagId, $typeId, 0)"
    );

    // Create new field 'unified_sql_db_type' with fixed value
    $pearDB->query("INSERT INTO options VALUES ('unified_sql_db_type', 'mysql')");

    $pearDB->query(
        "INSERT INTO `cb_field` (fieldname, displayname, description, fieldtype, external)
        VALUES ('db_type', 'DB type', 'Target DBMS.', 'text', 'T=options:C=value:CK=key:K=unified_sql_db_type')"
    );
    $fieldId = $pearDB->lastInsertId();

    // Add form fields for 'unified_sql' output
    $inputs = [];
    $statement = $pearDB->query(
        "SELECT DISTINCT(tfr.cb_field_id), tfr.is_required FROM cb_type_field_relation tfr, cb_type t, cb_field f
        WHERE tfr.cb_type_id = t.cb_type_id
        AND t.type_shortname in ('sql', 'storage')
        AND tfr.cb_field_id = f.cb_field_id
        AND f.fieldname NOT LIKE 'db_type'
        ORDER BY tfr.order_display"
    );
    $inputs = $statement->fetchAll();
    if (empty($inputs)) {
        throw new Exception("Cannot find fields in cb_type_field_relation table");
    }

    $inputs[] = ['cb_field_id' => $fieldId, 'is_required' => 1];

    $query = "INSERT INTO `cb_type_field_relation` (`cb_type_id`, `cb_field_id`, `is_required`, `order_display`)";
    foreach ($inputs as $key => $input) {
        $order = $key + 1;
        $query .= $key === 0 ? " VALUES " : ", ";
        $query .= "($typeId, " . $input['cb_field_id'] . ", " . $input['is_required'] . ", $order)";
    }
    $pearDB->query($query);
}
