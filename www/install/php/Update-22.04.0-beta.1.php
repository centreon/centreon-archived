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
    // Add custom_configuration to provider configurations
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

    // Insert default Security Policy
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

    // Move old password from contact to contact_password
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

    // Add JS Effect to contact
    $errorMessage = 'Impossible to add "contact_js_effects" column to "contact" table';
    if (!$pearDB->isColumnExist('contact', 'contact_js_effects')) {
        $pearDB->query(
            "ALTER TABLE `contact`
            ADD COLUMN `contact_js_effects` enum('0','1') DEFAULT '0'
            AFTER `contact_comment`"
        );
    }

    // Update Broker information
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

    // Add login blocking mechanism to contact
    $errorMessage = 'Impossible to add "login_attempts" and "blocking_time" columns to "contact" table';
    $pearDB->query(
        "ALTER TABLE `contact`
        ADD `login_attempts` INT(11) UNSIGNED DEFAULT NULL,
        ADD `blocking_time` BIGINT(20) UNSIGNED DEFAULT NULL"
    );

    $errorMessage = "Impossible to add default OpenID provider configuration";
    insertOpenIdConfiguration($pearDB);

    $errorMessage = "Unable to alter table security_token";
    $pearDB->query("ALTER TABLE `security_token` MODIFY `token` varchar(4096)");
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
 * insert OpenId Configuration
 *
 * @param CentreonDB $pearDB
 */
function insertOpenIdConfiguration(CentreonDB $pearDB): void
{
    $pearDB->beginTransaction();
    // Move OpenID Connect information to openid provider configuration.
    $statement = $pearDB->query("SELECT * FROM options WHERE `key` LIKE 'openid_%'");
    if (($result = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
        $isActive = $result['openid_connect_enable'] === '1';
        $isForced = $result['openid_connect_mode'] === '0'; //'0' OpenId Connect Only, '1' Mixed
        $customConfiguration = [
            "trusted_client_addresses" => explode(',', $result['openid_connect_trusted_clients']),
            "blacklist_client_addresses" => explode(',', $result['openid_connect_blacklist_clients']),
            "base_url" => !empty($result['openid_connect_base_url']) ? $result['openid_connect_base_url'] : null,
            "authorization_endpoint" => !empty($result['openid_connect_authorization_endpoint'])
                ? $result['openid_connect_authorization_endpoint']
                : null,
            "token_endpoint" => !empty($result['openid_connect_token_endpoint'])
                ? $result['openid_connect_token_endpoint']
                : null,
            "introspection_token_endpoint" => !empty($result['openid_connect_introspection_endpoint'])
                ? $result['openid_connect_introspection_endpoint']
                : null,
            "userinfo_endpoint" => !empty($result['openid_connect_userinfo_endpoint'])
                ? $result['openid_connect_userinfo_endpoint']
                : null,
            "endsession_endpoint" => !empty($result['openid_connect_end_session_endpoint'])
                ? $result['openid_connect_end_session_endpoint']
                : null,
            "connection_scopes" => explode(" ", $result['openid_connect_scope']),
            "login_claim" => !empty($result['openid_connect_login_claim'])
                ? $result['openid_connect_login_claim']
                : null,
            "client_id" => !empty($result['openid_connect_client_id'])
                ? $result['openid_connect_client_id']
                : null,
            "client_secret" => !empty($result['openid_connect_client_secret'])
                ? $result['openid_connect_client_secret']
                : null,
            "authentication_type" => $result['openid_connect_client_basic_auth'] === '1'
                ? 'client_secret_basic'
                : 'client_secret_post',
            "verify_peer" => $result['openid_connect_verify_peer'] === '1' ? false : true // '1' is Verify Peer disable
        ];

        $statement2 = $pearDB->prepare(
            "INSERT INTO provider_configuration (`type`,`name`,`custom_configuration`,`is_active`,`is_forced`)
            VALUES ('openid','openid', :customConfiguration, :isActive, :isForced)"
        );
        $statement2->bindValue(':customConfiguration', json_encode($customConfiguration), \PDO::PARAM_STR);
        $statement2->bindValue(':isActive', $isActive ? '1' : '0', \PDO::PARAM_STR);
        $statement2->bindValue(':isForced', $isForced ? '1' : '0', \PDO::PARAM_STR);
        $statement2->execute();

        $pearDB->query("DELETE FROM options WHERE `key` LIKE 'open_id%'");
    } else {
        $customConfiguration = [
            "trusted_client_addresses" => [],
            "blacklist_client_addresses" => [],
            "base_url" => null,
            "authorization_endpoint" => null,
            "token_endpoint" => null,
            "introspection_token_endpoint" => null,
            "userinfo_endpoint" => null,
            "endsession_endpoint" => null,
            "connection_scopes" => [],
            "login_claim" => null,
            "client_id" => null,
            "client_secret" => null,
            "authentication_type" => "client_secret_post",
            "verify_peer" => true
        ];
        $statement = $pearDB->prepare(
            "INSERT INTO provider_configuration (`type`,`name`,`custom_configuration`,`is_active`,`is_forced`)
            VALUES ('openid','openid', :customConfiguration, false, false)"
        );
        $statement->bindValue(':customConfiguration', json_encode($customConfiguration), \PDO::PARAM_STR);
        $statement->execute();
    }
    $pearDB->commit();
}
