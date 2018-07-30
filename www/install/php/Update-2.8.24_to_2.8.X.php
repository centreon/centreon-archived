<?php
/*
 * Copyright 2005-2018 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 *
 */

/*
 * Create remote servers table for keeping track of remote instances
 */
$query = 'CREATE TABLE IF NOT EXISTS `remote_servers` (' .
    '`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,' .
    '`ip` VARCHAR(16) NOT NULL,' .
    '`app_key` VARCHAR(40) NOT NULL,' .
    '`version` VARCHAR(16) NOT NULL,' .
    '`is_connected` TINYINT(1) NOT NULL DEFAULT 0,' .
    '`created_at` TIMESTAMP NOT NULL,' .
    '`connected_at` TIMESTAMP NULL' .
    ') ENGINE=InnoDB DEFAULT CHARSET=utf8;';

$pearDBO->query($query);

/*
 * Generate random key for application key
 */

$uniqueKey = md5(uniqid(rand(), TRUE));

$query = "INSERT INTO `informations` (`key`,`value`) VALUES ('appKey', '$uniqueKey')";
$pearDBO->query($query);

// Add column to topology table to mark which pages are with React
$query = "ALTER TABLE `topology` ADD COLUMN `is_react` ENUM('0', '1') NOT NULL DEFAULT '0' AFTER `readonly`";
$pearDBO->query($query);