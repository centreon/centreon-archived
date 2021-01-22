#!@PHP_BIN@
<?php

/*
 * Copyright 2005-2021 Centreon
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
 */

require_once realpath(__DIR__ . "/../config/centreon.config.php");
include_once _CENTREON_PATH_ . "/cron/centAcl-Func.php";
include_once _CENTREON_PATH_ . "/www/class/centreonDB.class.php";
include_once _CENTREON_PATH_ . "/www/class/centreonLog.class.php";

$centreonLog = new CentreonLog();

// Default session duration
define('SESSION_DEFAULT_DURATION', 120);

try {
    // Init DB connections
    $pearDB = new CentreonDB();

    /**
     * Remove expired sessions
     */
    // Find duration of session_expiration
    $sessionDuration = SESSION_DEFAULT_DURATION;
    $durationQuery = $pearDB->query("SELECT `value` from options where `key` = 'session_expire'");
    if (($duration = $durationQuery->fetch(\PDO::FETCH_ASSOC)) !== false) {
        $sessionDuration = $duration['value'];
    } else {
        // cannot find the default value. setting a new value
        $pearDB->query("DELETE FROM options WHERE `key` = 'session_expire'");
        $pearDB->query("INSERT INTO options (`key`, `value`) VALUES ('session_expire', '$sessionDuration')");
        $centreonLog->insertLog(
                1,
                "Cannot find session duration value. Setting a default value of " .
                SESSION_DEFAULT_DURATION . " minutes"
        );
    }

    // Get users sessions list
    $sessionQuery = $pearDB->query(
        "SELECT s.id, s.last_reload, c.contact_alias AS account
        FROM `session` s INNER JOIN `contact` c ON c.contact_id = s.user_id"
    );
    $expiredSessions = [];
    $userSessions = [];
    $expirationTime = time() - ($sessionDuration * 60);
    while ($row = $sessionQuery->fetch(\PDO::FETCH_ASSOC)) {
        if ($row['last_reload'] < $expirationTime) {
            $expiredSessions[] = $row['id'];
            $userSessions[] = $row['account'];
        }
    }
    if (!empty($expiredSessions)) {
        // remove the sessions
        $expiredSessions = implode(', ', $expiredSessions);
        $pearDB->query("DELETE FROM session WHERE `id` IN ($expiredSessions)");
        // log session removed in login.log
        foreach ($userSessions as $userSession) {
            $centreonLog->insertLog(1, "Remove expired session of : " . $userSession);
        }
    }
} catch (Exception $e) {
    programExit($e->getMessage());
}