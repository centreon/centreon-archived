#!@PHP_BIN@
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

require_once realpath(__DIR__ . "/../config/centreon.config.php");
include_once _CENTREON_PATH_ . "/cron/centAcl-Func.php";
include_once _CENTREON_PATH_ . "/www/class/centreonDB.class.php";
include_once _CENTREON_PATH_ . "/www/class/centreonLog.class.php";

$centreonLog = new CentreonLog();
$pearDB = new CentreonDB();

// Default session duration
define('SESSION_DEFAULT_DURATION', 120);

try {
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
        // log removed sessions in login.log
        foreach ($userSessions as $userSession) {
            $centreonLog->insertLog(1, "Remove expired session of: " . $userSession);
        }
    }
} catch (Exception $e) {
    programExit($e->getMessage());
}
