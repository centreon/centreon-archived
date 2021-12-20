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

$versionOfTheUpgrade = 'UPGRADE - 22.04.0-beta.1: ';

$pearDB = new CentreonDB();

try {
    $errorMessage = "Unable to create table 'password_security_policy'";
    $pearDB->query(
        "CREATE TABLE `password_security_policy` (
        `password_length` tinyint UNSIGNED NOT NULL DEFAULT 12,
        `uppercase_characters` enum('0', '1') NOT NULL DEFAULT '1',
        `lowercase_characters` enum('0', '1') NOT NULL DEFAULT '1',
        `integer_characters` enum('0', '1') NOT NULL DEFAULT '1',
        `special_characters` enum('0', '1') NOT NULL DEFAULT '1',
        `attempts` int(11) UNSIGNED NOT NULL DEFAULT 5,
        `blocking_duration` int(11) UNSIGNED NOT NULL DEFAULT 900,
        `password_expiration` int(11) UNSIGNED NOT NULL DEFAULT 7776000,
        `delay_before_new_password` int(11) UNSIGNED NOT NULL DEFAULT 3600,
        `can_reuse_password` enum('0', '1') NOT NULL DEFAULT '0')"
    );

    $errorMessage = "Unable to insert default configuration in 'password_security_policy'";
    $pearDB->query(
        "INSERT INTO `password_security_policy`
        (`password_length`, `uppercase_characters`, `lowercase_characters`, `integer_characters`,
        `special_characters`, `attempts`, `blocking_duration`, `password_expiration`, `delay_before_new_password`)
        VALUES (12, '1', '1', '1', '1', 5, 900, 7776000, 3600)"
    );

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

    $errorMessage = "Unable to select existing passwords from 'contact' table";
    $dbResult = $pearDB->query(
        "SELECT `contact_id`, `contact_passwd` FROM `contact` WHERE `contact_passwd` IS NOT NULL"
    );
    $statement = $pearDB->prepare(
        "INSERT INTO `contact_password` (`password`, `contact_id`, `creation_date`)
        VALUES (:password, :contactId, :creationDate)"
    );
    while ($row = $dbResult->fetch()) {
        $errorMessage = "Unable to insert password in 'contact_password' table";
        $statement->bindValue(':password', $row['contact_passwd']);
        $statement->bindValue(':contactId', $row['contact_id']);
        $statement->bindValue(':creationDate', time());
        $statement->execute();
    }
    $errorMessage = "Unable to drop column 'contact_passwd' from 'contact' table";
    $dbResult = $pearDB->query("ALTER TABLE `contact` DROP COLUMN `contact_passwd`");

    $errorMessage = 'Impossible to add "contact_js_effects" column to "contact" table';

    if (!$pearDB->isColumnExist('contact', 'contact_js_effects')) {
        $pearDB->query(
            "ALTER TABLE `contact`
            ADD COLUMN `contact_js_effects` enum('0','1') DEFAULT '0'
            AFTER `contact_comment`"
        );
    }
} catch (\Exception $e) {
    $centreonLog->insertLog(
        4,
        $versionOfTheUpgrade . $errorMessage .
        " - Code : " . (int)$e->getCode() .
        " - Error : " . $e->getMessage() .
        " - Trace : " . $e->getTraceAsString()
    );

    throw new \Exception($versionOfTheUpgrade . $errorMessage, (int)$e->getCode(), $e);
}
