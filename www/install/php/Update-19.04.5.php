<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
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

/**
 * Update session duration value to the max allowed duration set in the php
 * configuration file 50-centreon.ini
 */
try {
    $pearDB->query(
        "UPDATE `contact` SET `contact_autologin_key` = NULL WHERE `contact_autologin_key` =''"
    );

    $stmt = $pearDB->query(
        'SELECT `value` FROM `options` WHERE `key` = "session_expire"'
    );
    $sessionValue = $stmt->fetch();

    if ($sessionValue > 120) {
        $pearDB->query(
            'UPDATE `options` SET `value` = "120"
            WHERE `key` = "session_expire"'
        );
    }
} catch (\PDOException $e) {
    $centreonLog->insertLog(
        2,
        "UPGRADE : 19.04.5 Unable to modify contact autologin key default value"
    );
}

// remove LDAP users missing contact name
// these users have been added using the auto-import LDAP feature and will be re-imported at their next login.
try {
    $pearDB->query('DELETE FROM contact WHERE contact_name = ""');
} catch (\PDOException $e) {
    $centreonLog->insertLog(
        2,
        "UPGRADE : 19.04.5 Unable to delete LDAP auto-imported users with empty contact_name"
    );
}

// correct the DN of manually imported users from an LDAP
try {
    // finding the data of contacts linked to an LDAP
    $stmt = $pearDB->query("SELECT contact_id, contact_name, contact_ldap_dn FROM contact WHERE ar_id is NOT NULL");
    $updateDB = $pearDB->prepare("UPDATE contact SET contact_ldap_dn = :newDn WHERE contact_id = :contactId");
    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        // removing the double slashes if needed and saving the corrected data
        if (strpos($row['contact_ldap_dn'], "\\\\")) {
            $newDn = str_replace("\\\\", "\\", $row['contact_ldap_dn']);
            $updateDB->bindValue(':newDn', $newDn, \PDO::PARAM_STR);
            $updateDB->bindValue(':contactId', $row['contact_id'], \PDO::PARAM_INT);
            $updateDB->execute();
        }
    }
} catch (\PDOException $e) {
    $centreonLog->insertLog(
        2,
        "UPGRADE : 19.04.5 Unable to correct the LDAP DN data"
    );
}
