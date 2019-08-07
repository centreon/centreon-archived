<?php
/*
 * Copyright 2005-2019 Centreon
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

include_once __DIR__ . "/../../class/centreonLog.class.php";
$centreonLog = new CentreonLog();

try {
    // Add HTTPS connexion to Remote Server
    if (!$pearDB->isColumnExist('remote_servers', 'http_method')) {
        $pearDB->query(
            "ALTER TABLE remote_servers ADD COLUMN `http_method` enum('http','https') NOT NULL DEFAULT 'http'"
        );
    }
    if (!$pearDB->isColumnExist('remote_servers', 'http_port')) {
        $pearDB->query(
            "ALTER TABLE remote_servers ADD COLUMN `http_port` int(11) NULL DEFAULT NULL"
        );
    }
    if (!$pearDB->isColumnExist('remote_servers', 'no_check_certificate')) {
        $pearDB->query(
            "ALTER TABLE remote_servers ADD COLUMN `no_check_certificate` enum('0','1') NOT NULL DEFAULT '0'"
        );
    }
    if (!$pearDB->isColumnExist('remote_servers', 'no_proxy')) {
        $pearDB->query(
            "ALTER TABLE remote_servers ADD COLUMN `no_proxy` enum('0','1') NOT NULL DEFAULT '0'"
        );
    }
} catch (\PDOException $e) {
    $centreonLog->insertLog(
        2,
        "UPGRADE : Unable to process 19.04.1 upgrade"
    );
}
