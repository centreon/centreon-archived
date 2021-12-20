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

if (!isset($centreon)) {
    exit();
}

require_once __DIR__ . '/../../../../../bootstrap.php';
require_once __DIR__ . '/../../../../class/centreonAuth.class.php';
require_once __DIR__ . '/../../../../class/centreonContact.class.php';

/**
 * @param null $name
 * @return bool
 */
function testContactExistence($name = null)
{
    global $pearDB, $form, $centreon;

    $id = null;
    if (isset($form)) {
        $id = $form->getSubmitValue('contact_id');
    }

    $query = "SELECT contact_name, contact_id FROM contact WHERE contact_name = '" .
        htmlentities($centreon->checkIllegalChar($name), ENT_QUOTES, "UTF-8") . "'";
    $dbResult = $pearDB->query($query);
    $contact = $dbResult->fetch();

    if ($dbResult->rowCount() >= 1 && $contact["contact_id"] == $id) {
        return true;
    } elseif ($dbResult->rowCount() >= 1 && $contact["contact_id"] != $id) {
        return false;
    } else {
        return true;
    }
}

/**
 * @param null $alias
 * @return bool
 */
function testAliasExistence($alias = null)
{
    global $pearDB, $form;
    $id = null;
    if (isset($form)) {
        $id = $form->getSubmitValue('contact_id');
    }
    $query = "SELECT contact_alias, contact_id FROM contact WHERE contact_alias = '" .
        htmlentities($alias, ENT_QUOTES, "UTF-8") . "'";
    $dbResult = $pearDB->query($query);
    $contact = $dbResult->fetch();

    if ($dbResult->rowCount() >= 1 && $contact["contact_id"] == $id) {
        return true;
    } elseif ($dbResult->rowCount() >= 1 && $contact["contact_id"] != $id) {
        return false;
    } else {
        return true;
    }
}

/**
 * @param null $ct_id
 * @return bool
 */
function keepOneContactAtLeast($ct_id = null)
{
    global $pearDB, $form, $centreon;

    if (!isset($contact_id)) {
        $contact_id = $ct_id;
    } elseif (isset($_GET["contact_id"])) {
        $contact_id = htmlentities($_GET["contact_id"], ENT_QUOTES, "UTF-8");
    } else {
        $contact_id = $form->getSubmitValue('contact_id');
    }

    if (isset($form)) {
        $cct_oreon = $form->getSubmitValue('contact_oreon');
        $cct_activate = $form->getSubmitValue('contact_activate');
    } else {
        $cct_oreon = 0;
        $cct_activate = 0;
    }

    if ($contact_id == $centreon->user->get_id()) {
        return false;
    }

    /*
     * Get activated contacts
     */
    $dbResult = $pearDB->query("SELECT COUNT(*) AS nbr_valid
            FROM contact
            WHERE contact_activate = '1'
            AND contact_oreon = '1'
            AND contact_id <> '" . $pearDB->escape($contact_id) . "'");
    $contacts = $dbResult->fetch();

    if ($contacts["nbr_valid"] == 0) {
        if ($cct_oreon == 0 || $cct_activate == 0) {
            return false;
        }
    }
    return true;
}

/**
 *
 * Enable contacts
 * @param $contact_id
 * @param $contact_arr
 */
function enableContactInDB($contact_id = null, $contact_arr = array())
{
    global $pearDB, $centreon;

    if (!$contact_id && !count($contact_arr)) {
        return;
    }
    if ($contact_id) {
        $contact_arr = array($contact_id => "1");
    }

    foreach ($contact_arr as $key => $value) {
        $pearDB->query("UPDATE contact SET contact_activate = '1' WHERE contact_id = '" . (int)$key . "'");

        $query = "SELECT contact_name FROM `contact` WHERE `contact_id` = '" . (int)$key . "' LIMIT 1";
        $dbResult2 = $pearDB->query($query);
        $row = $dbResult2->fetch();

        $centreon->CentreonLogAction->insertLog("contact", $key, $row['contact_name'], "enable");
    }
}

/**
 *
 * Disable Contacts
 * @param $contact_id
 * @param $contact_arr
 */
function disableContactInDB($contact_id = null, $contact_arr = array())
{
    global $pearDB, $centreon;

    if (!$contact_id && !count($contact_arr)) {
        return;
    }
    if ($contact_id) {
        $contact_arr = array($contact_id => "1");
    }

    foreach ($contact_arr as $key => $value) {
        if (keepOneContactAtLeast($key)) {
            $pearDB->query("UPDATE contact SET contact_activate = '0' WHERE contact_id = '" . (int)$key . "'");
            $query = "SELECT contact_name FROM `contact` WHERE `contact_id` = '" . (int)$key . "' LIMIT 1";
            $dbResult2 = $pearDB->query($query);
            $row = $dbResult2->fetch();

            $centreon->CentreonLogAction->insertLog("contact", $key, $row['contact_name'], "disable");
        }
    }
}

/**
 * Delete Contacts
 * @param array $contacts
 */
function deleteContactInDB($contacts = array())
{
    global $pearDB, $centreon;

    // getting the contact name for the logs
    $contactNameStmt = $pearDB->prepare(
        "SELECT contact_name FROM `contact` WHERE `contact_id` = :contactId LIMIT 1"
    );

    $contactTokenStmt = $pearDB->prepare(
        "SELECT token FROM `security_authentication_tokens` WHERE `user_id` = :contactId"
    );

    $deleteTokenStmt = $pearDB->prepare(
        "DELETE FROM `security_token` WHERE `token` = :token"
    );

    $deleteContactStmt = $pearDB->prepare(
        "DELETE FROM contact WHERE contact_id = :contactId"
    );

    $pearDB->beginTransaction();
    try {
        foreach ($contacts as $key => $value) {
            $contactNameStmt->bindValue(':contactId', (int)$key, \PDO::PARAM_INT);
            $contactNameStmt->execute();
            $row = $contactNameStmt->fetch();

            $contactTokenStmt->bindValue(':contactId', (int)$key, \PDO::PARAM_INT);
            $contactTokenStmt->execute();
            while ($rowContact = $contactTokenStmt->fetch()) {
                $deleteTokenStmt->bindValue(':token', $rowContact['token'], \PDO::PARAM_STR);
                $deleteTokenStmt->execute();
            }

            $deleteContactStmt->bindValue(':contactId', (int)$key, \PDO::PARAM_INT);
            $deleteContactStmt->execute();

            $centreon->CentreonLogAction->insertLog("contact", $key, $row['contact_name'], "d");
        }
        $pearDB->commit();
    } catch (\PDOException $e) {
        $pearDB->rollBack();
    }
}

/**
 * Synchronize LDAP with contacts' data
 * Used for massive sync request
 * @param array $contacts
 */
function synchronizeContactWithLdap(array $contacts = array()): void
{
    global $pearDB;
    $centreonLog = new CentreonLog();

    // checking if at least one LDAP configuration is still enabled
    $ldapEnable = $pearDB->query(
        "SELECT `value` FROM `options` WHERE `key` = 'ldap_auth_enable'"
    );
    $rowLdapEnable = $ldapEnable->fetch();

    if ($rowLdapEnable['value'] === '1') {
        // getting the contact name for the logs
        $contactNameStmt = $pearDB->prepare(
            "SELECT contact_name, `ar_id`
            FROM `contact`
            WHERE `contact_id` = :contactId
            AND `ar_id` IS NOT NULL"
        );

        // requiring a manual synchronization at next login of the contact
        $stmtRequiredSync = $pearDB->prepare(
            'UPDATE contact
            SET `contact_ldap_required_sync` = "1"
            WHERE contact_id = :contactId'
        );

        // checking if the contact is currently logged in Centreon
        $activeSession = $pearDB->prepare(
            "SELECT session_id FROM `session` WHERE user_id = :contactId"
        );

        // disconnecting the active user from centreon
        $logoutContact = $pearDB->prepare(
            "DELETE FROM session WHERE session_id = :userSessionId"
        );

        $successfullySync = [];
        $pearDB->beginTransaction();
        try {
            foreach ($contacts as $key => $value) {
                $contactNameStmt->bindValue(':contactId', (int)$key, \PDO::PARAM_INT);
                $contactNameStmt->execute();
                $rowContact = $contactNameStmt->fetch();
                if (!$rowContact['ar_id']) {
                    // skipping chosen contacts not bound to an LDAP
                    continue;
                }

                $stmtRequiredSync->bindValue(':contactId', (int)$key, \PDO::PARAM_INT);
                $stmtRequiredSync->execute();

                $activeSession->bindValue(':contactId', (int)$key, \PDO::PARAM_INT);
                $activeSession->execute();
                //disconnecting every session logged in using this contact data
                while ($rowSession = $activeSession->fetch()) {
                    $logoutContact->bindValue(':userSessionId', $rowSession['session_id'], \PDO::PARAM_STR);
                    $logoutContact->execute();
                }
                $successfullySync[] = $rowContact['contact_name'];
            }
            $pearDB->commit();
            foreach ($successfullySync as $key => $value) {
                $centreonLog->insertLog(
                    3, //ldap.log
                    "LDAP MULTI SYNC : Successfully planned LDAP synchronization for " . $value
                );
            }
        } catch (\PDOException $e) {
            $pearDB->rollBack();
            throw new Exception('Bad Request : ' . $e);
        }
    } else {
        // unable to plan the manual LDAP request of the contacts
        $centreonLog->insertLog(
            3,
            "LDAP MANUAL SYNC : No LDAP configuration is enabled"
        );
    }
}

/**
 * Duplicate a list of contact
 *
 * @param array $contacts list of contact ids to duplicate
 * @param array $nbrDup Number of duplication per contact id
 * @return array List of the new contact ids
 */
function multipleContactInDB($contacts = array(), $nbrDup = array())
{
    global $pearDB, $centreon;
    $newContactIds = [];
    foreach ($contacts as $key => $value) {
        $newContactIds[$key] = [];
        $dbResult = $pearDB->query(
            "SELECT `contact`.*, cp.password
            FROM contact
            LEFT JOIN contact_password cp ON cp.contact_id = contact.contact_id
            WHERE `contact`.contact_id = '" . (int)$key . "' LIMIT 1"
        );
        $row = $dbResult->fetch();
        $row["contact_id"] = null;
        for ($i = 1; $i <= $nbrDup[$key]; $i++) {
            $val = null;
            foreach ($row as $key2 => $value2) {
                if ($key2 !== "password") {
                    $key2 == "contact_name" ? ($contact_name = $value2 = $value2 . "_" . $i) : null;
                    $key2 == "contact_alias" ? ($contact_alias = $value2 = $value2 . "_" . $i) : null;
                    $val ? $val .= ($value2 != null ? (", '" . $value2 . "'") : ", NULL") : $val .=
                        ($value2 != null ? ("'" . $value2 . "'") : "NULL");
                    if ($key2 != "contact_id") {
                        $fields[$key2] = $value2;
                    }
                    if (isset($contact_name)) {
                        $fields["contact_name"] = $contact_name;
                    }
                    if (isset($contact_alias)) {
                        $fields["contact_alias"] = $contact_alias;
                    }
                }
            }

            if (isset($row['contact_name'])) {
                $row["contact_name"] = $centreon->checkIllegalChar($row["contact_name"]);
            }

            if (testContactExistence($contact_name) && testAliasExistence($contact_alias)) {
                $val ? $rq = "INSERT INTO contact VALUES (" . $val . ")" : $rq = null;
                $pearDB->query($rq);
                $lastId = $pearDB->lastInsertId();
                if (isset($lastId)) {
                    /**
                     * Don't insert password for a contact_template.
                     */
                    if ($row['password'] !== null) {
                        $statement = $pearDB->prepare(
                            "INSERT INTO contact_password (password, contact_id, creation_date)
                            VALUES (:password, :contactId, :creationDate)"
                        );
                        $statement->bindValue(':password', $row['password'], \PDO::PARAM_STR);
                        $statement->bindValue(':contactId', $lastId, \PDO::PARAM_INT);
                        $statement->bindValue(':creationDate', time(), \PDO::PARAM_INT);
                        $statement->execute();
                    }
                    $newContactIds[$key][] = $lastId;
                    /*
                     * ACL update
                     */
                    $query = "SELECT DISTINCT acl_group_id FROM acl_group_contacts_relations " .
                        "WHERE contact_contact_id = " . (int)$key;
                    $dbResult = $pearDB->query($query);
                    $fields["contact_aclRelation"] = "";
                    while ($aclRelation = $dbResult->fetch()) {
                        $query = "INSERT INTO acl_group_contacts_relations VALUES ('" .
                            $lastId . "', '" . $aclRelation["acl_group_id"] . "')";
                        $pearDB->query($query);
                        $fields["contact_aclRelation"] .= $aclRelation["acl_group_id"] . ",";
                    }
                    $fields["contact_aclRelation"] = trim($fields["contact_aclRelation"], ",");

                    /*
                     * Command update
                     */
                    $query = "SELECT DISTINCT command_command_id FROM contact_hostcommands_relation " .
                        "WHERE contact_contact_id = '" . (int)$key . "'";
                    $dbResult = $pearDB->query($query);
                    $fields["contact_hostNotifCmds"] = "";
                    while ($hostCmd = $dbResult->fetch()) {
                        $query = "INSERT INTO contact_hostcommands_relation VALUES ('" .
                            $lastId . "', '" . $hostCmd["command_command_id"] . "')";
                        $pearDB->query($query);
                        $fields["contact_hostNotifCmds"] .= $hostCmd["command_command_id"] . ",";
                    }
                    $fields["contact_hostNotifCmds"] = trim($fields["contact_hostNotifCmds"], ",");

                    /*
                     * Commands update
                     */
                    $query = "SELECT DISTINCT command_command_id FROM contact_servicecommands_relation " .
                        "WHERE contact_contact_id = '" . (int)$key . "'";
                    $dbResult = $pearDB->query($query);
                    $fields["contact_svNotifCmds"] = "";
                    while ($serviceCmd = $dbResult->fetch()) {
                        $query = "INSERT INTO contact_servicecommands_relation VALUES ('" .
                            $lastId . "', '" . $serviceCmd["command_command_id"] . "')";
                        $pearDB->query($query);
                        $fields["contact_svNotifCmds"] .= $serviceCmd["command_command_id"] . ",";
                    }
                    $fields["contact_svNotifCmds"] = trim($fields["contact_svNotifCmds"], ",");

                    /*
                     * Contact groups
                     */
                    $query = "SELECT DISTINCT contactgroup_cg_id FROM contactgroup_contact_relation " .
                        "WHERE contact_contact_id = '" . (int)$key . "'";
                    $dbResult = $pearDB->query($query);
                    $fields["contact_cgNotif"] = "";
                    while ($Cg = $dbResult->fetch()) {
                        $query = "INSERT INTO contactgroup_contact_relation VALUES ('" .
                            $lastId . "', '" . $Cg["contactgroup_cg_id"] . "')";
                        $pearDB->query($query);
                        $fields["contact_cgNotif"] .= $Cg["contactgroup_cg_id"] . ",";
                    }
                    $fields["contact_cgNotif"] = trim($fields["contact_cgNotif"], ",");
                    $centreon->CentreonLogAction->insertLog(
                        "contact",
                        $lastId,
                        $contact_name,
                        "a",
                        $fields
                    );
                }
            }
        }
    }

    return $newContactIds;
}

/**
 * @param null $contact_id
 * @param bool $from_MC
 */
function updateContactInDB($contact_id = null, $from_MC = false)
{
    global $form;

    if (!$contact_id) {
        return;
    }

    $ret = $form->getSubmitValues();
    # Global function to use
    if ($from_MC) {
        updateContact_MC($contact_id);
    } else {
        updateContact($contact_id);
    }
    # Function for updating host commands
    # 1 - MC with deletion of existing cmds
    # 2 - MC with addition of new cmds
    # 3 - Normal update
    if (isset($ret["mc_mod_hcmds"]["mc_mod_hcmds"]) && $ret["mc_mod_hcmds"]["mc_mod_hcmds"]) {
        updateContactHostCommands($contact_id);
    } elseif (isset($ret["mc_mod_hcmds"]["mc_mod_hcmds"]) && !$ret["mc_mod_hcmds"]["mc_mod_hcmds"]) {
        updateContactHostCommands_MC($contact_id);
    } else {
        updateContactHostCommands($contact_id);
    }
    # Function for updating service commands
    # 1 - MC with deletion of existing cmds
    # 2 - MC with addition of new cmds
    # 3 - Normal update
    if (isset($ret["mc_mod_svcmds"]["mc_mod_svcmds"]) && $ret["mc_mod_svcmds"]["mc_mod_svcmds"]) {
        updateContactServiceCommands($contact_id);
    } elseif (isset($ret["mc_mod_svcmds"]["mc_mod_svcmds"]) && !$ret["mc_mod_svcmds"]["mc_mod_svcmds"]) {
        updateContactServiceCommands_MC($contact_id);
    } else {
        updateContactServiceCommands($contact_id);
    }
    # Function for updating contact groups
    # 1 - MC with deletion of existing cg
    # 2 - MC with addition of new cg
    # 3 - Normal update
    if (isset($ret["mc_mod_cg"]["mc_mod_cg"]) && $ret["mc_mod_cg"]["mc_mod_cg"]) {
        updateContactContactGroup($contact_id);
    } elseif (isset($ret["mc_mod_cg"]["mc_mod_cg"]) && !$ret["mc_mod_cg"]["mc_mod_cg"]) {
        updateContactContactGroup_MC($contact_id);
    } else {
        updateContactContactGroup($contact_id);
    }

    /**
     * ACL
     */
    if (isset($ret["mc_mod_acl"]["mc_mod_acl"]) && $ret["mc_mod_acl"]["mc_mod_acl"]) {
        updateAccessGroupLinks($contact_id);
    } elseif (isset($ret["mc_mod_acl"]["mc_mod_acl"]) && !$ret["mc_mod_acl"]["mc_mod_acl"]) {
        updateAccessGroupLinks_MC($contact_id, $ret["mc_mod_acl"]["mc_mod_acl"]);
    } else {
        updateAccessGroupLinks($contact_id);
    }
}

/**
 * @param array $ret
 * @return mixed
 */
function insertContactInDB($ret = array())
{
    $contact_id = insertContact($ret);
    updateContactHostCommands($contact_id, $ret);
    updateContactServiceCommands($contact_id, $ret);
    updateContactContactGroup($contact_id, $ret);
    updateAccessGroupLinks($contact_id);
    return ($contact_id);
}

/**
 * @param array $ret
 * @return mixed
 */
function insertContact($ret = array())
{
    global $form, $pearDB, $centreon, $dependencyInjector;

    if (!count($ret)) {
        $ret = $form->getSubmitValues();
    }
    $ret["contact_name"] = $centreon->checkIllegalChar($ret["contact_name"]);

    $bindParams = sanitizeFormContactParameters($ret);
    $params = [];
    foreach (array_keys($bindParams) as $token) {
        $params[] = ltrim($token, ':');
    }
    $rq = "INSERT INTO `contact` ( contact_id, ";
    $rq .= implode(', ', $params) . ")";
    $rq .= " VALUES (NULL, " . implode(", ", array_keys($bindParams)) . " )";

    $stmt = $pearDB->prepare($rq);
    foreach ($bindParams as $token => $bindValues) {
        foreach ($bindValues as $paramType => $value) {
            $stmt->bindValue($token, $value, $paramType);
        }
    }

    $stmt->execute();
    $dbResult = $pearDB->query("SELECT MAX(contact_id) FROM contact");
    $contactId = $dbResult->fetch();

    if (isset($ret["contact_passwd"]) && !empty($ret["contact_passwd"])) {
        $ret["contact_passwd"] = password_hash($ret["contact_passwd"], \CentreonAuth::PASSWORD_HASH_ALGORITHM);
        $ret["contact_passwd2"] = $ret["contact_passwd"];

        $contact = new \CentreonContact($pearDB);
        $contact->insertPasswordByContactId($contactId, $ret["contact_passwd"]);
    }

    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($ret);
    $centreon->CentreonLogAction->insertLog(
        "contact",
        $contactId["MAX(contact_id)"],
        $ret["contact_name"],
        "a",
        $fields
    );

    return ($contactId["MAX(contact_id)"]);
}

/**
 * @param int|null $contactId
 */
function updateContact($contactId = null)
{
    global $form, $pearDB, $centreon, $encryptType, $dependencyInjector;
    if (!$contactId) {
        return;
    }
    $ret = array();
    $ret = $form->getSubmitValues();
    // remove illegal chars in data sent by the user
    $ret['contact_name'] = CentreonUtils::escapeSecure($ret['contact_name'], CentreonUtils::ESCAPE_ILLEGAL_CHARS);
    $ret['contact_alias'] = CentreonUtils::escapeSecure($ret['contact_alias'], CentreonUtils::ESCAPE_ILLEGAL_CHARS);
    $bindParams = sanitizeFormContactParameters($ret);

    //Build Query with only setted values.
    $rq = "UPDATE contact SET ";
    foreach (array_keys($bindParams) as $token) {
        $rq .= ltrim($token, ':') . " = " . $token . ", ";
    }
    $rq = rtrim($rq, ', ');
    $rq .= " WHERE contact_id = :contactId";

    $stmt = $pearDB->prepare($rq);
    foreach ($bindParams as $token => $bindValues) {
        foreach ($bindValues as $paramType => $value) {
            $stmt->bindValue($token, $value, $paramType);
        }
    }
    $stmt->bindValue(':contactId', $contactId, \PDO::PARAM_INT);
    $stmt->execute();

    if (isset($ret["contact_lang"]) && $ret["contact_lang"] != null && $contactId == $centreon->user->get_id()) {
        $centreon->user->set_lang($ret["contact_lang"]);
    }

    if (isset($ret["contact_passwd"]) && !empty($ret["contact_passwd"])) {
        $ret["contact_passwd"] = password_hash($ret["contact_passwd"], \CentreonAuth::PASSWORD_HASH_ALGORITHM);
        $ret["contact_passwd2"] = $ret["contact_passwd"];

        $contact = new \CentreonContact($pearDB);
        $contact->updatePasswordByContactId($contactId, $ret["contact_passwd"]);
    }

    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($ret);
    $centreon->CentreonLogAction->insertLog("contact", $contactId, $ret["contact_name"], "c", $fields);
}

/**
 * @param null $contact_id
 */
function updateContact_MC($contact_id = null)
{
    global $form, $pearDB, $centreon;

    if ($contact_id == null || $contact_id === false) {
        return;
    }

    $ret = array();
    $ret = $form->getSubmitValues();
    $bindParams = sanitizeFormContactParameters($ret);
    $rq = "UPDATE contact SET ";
    foreach (array_keys($bindParams) as $token) {
        $rq .= ltrim($token, ':') . " = " . $token . ", ";
    }
    $rq = rtrim($rq, ', ');
    $rq .= " WHERE contact_id = :contactId";

    $stmt = $pearDB->prepare($rq);
    foreach ($bindParams as $token => $bindValues) {
        foreach ($bindValues as $paramType => $value) {
            $stmt->bindValue($token, $value, $paramType);
        }
    }
    $stmt->bindValue(':contactId', $contact_id, \PDO::PARAM_INT);
    $stmt->execute();

    /**
     * Prepare Log.
     */
    $query = "SELECT contact_name FROM `contact` WHERE contact_id='" . (int)$contact_id . "' LIMIT 1";
    $dbResult2 = $pearDB->query($query);
    $row = $dbResult2->fetch();

    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($ret);
    $centreon->CentreonLogAction->insertLog("contact", $contact_id, $row["contact_name"], "mc", $fields);
}

/**
 * @param null $contact_id
 * @param array $ret
 */
function updateContactHostCommands($contact_id = null, $ret = array())
{
    global $form, $pearDB;

    if (!$contact_id) {
        return;
    }
    $rq = "DELETE FROM contact_hostcommands_relation ";
    $rq .= "WHERE contact_contact_id = '" . (int)$contact_id . "'";
    $dbResult = $pearDB->query($rq);
    if (isset($ret["contact_hostNotifCmds"])) {
        $ret = $ret["contact_hostNotifCmds"];
    } else {
        $ret = $form->getSubmitValue("contact_hostNotifCmds");
    }

    if (is_array($ret) || $ret instanceof Countable) {
        $resultsCount = count($ret);
    } else {
        $resultsCount = 0;
    }

    for ($i = 0; $i < $resultsCount; $i++) {
        $rq = "INSERT INTO contact_hostcommands_relation ";
        $rq .= "(contact_contact_id, command_command_id) ";
        $rq .= "VALUES ";
        $rq .= "(" . (int)$contact_id . ", " . $ret[$i] . ")";
        $dbResult = $pearDB->query($rq);
    }
}

/*
 * For massive change. We just add the new list if the elem doesn't exist yet
 */
/**
 * @param null $contact_id
 * @param array $ret
 */
function updateContactHostCommands_MC($contact_id = null, $ret = array())
{
    global $form, $pearDB;
    if (!$contact_id) {
        return;
    }
    $rq = "SELECT * FROM contact_hostcommands_relation ";
    $rq .= "WHERE contact_contact_id = '" . (int)$contact_id . "'";
    $dbResult = $pearDB->query($rq);
    $cmds = array();
    while ($arr = $dbResult->fetch()) {
        $cmds[$arr["command_command_id"]] = $arr["command_command_id"];
    }
    $ret = $form->getSubmitValue("contact_hostNotifCmds");
    if (!empty($ret)) {
        for ($i = 0; $i < count($ret); $i++) {
            if (!isset($cmds[$ret[$i]])) {
                $rq = "INSERT INTO contact_hostcommands_relation ";
                $rq .= "(contact_contact_id, command_command_id) ";
                $rq .= "VALUES ";
                $rq .= "(" . (int)$contact_id . ", " . $ret[$i] . ")";
                $dbResult = $pearDB->query($rq);
            }
        }
    }
}

/**
 * @param null $contact_id
 * @param array $ret
 */
function updateContactServiceCommands($contact_id = null, $ret = array())
{
    global $form, $pearDB;
    if (!$contact_id) {
        return;
    }
    $rq = "DELETE FROM contact_servicecommands_relation ";
    $rq .= "WHERE contact_contact_id = '" . (int)$contact_id . "'";
    $dbResult = $pearDB->query($rq);
    if (isset($ret["contact_svNotifCmds"])) {
        $ret = $ret["contact_svNotifCmds"];
    } else {
        $ret = $form->getSubmitValue("contact_svNotifCmds");
    }

    if (is_array($ret) || $ret instanceof Countable) {
        $resultsCount = count($ret);
    } else {
        $resultsCount = 0;
    }

    for ($i = 0; $i < $resultsCount; $i++) {
        $rq = "INSERT INTO contact_servicecommands_relation ";
        $rq .= "(contact_contact_id, command_command_id) ";
        $rq .= "VALUES ";
        $rq .= "(" . (int)$contact_id . ", " . $ret[$i] . ")";
        $dbResult = $pearDB->query($rq);
    }
}

/*
 * For massive change. We just add the new list if the elem doesn't exist yet
 */
/**
 * @param null $contact_id
 * @param array $ret
 */
function updateContactServiceCommands_MC($contact_id = null, $ret = array())
{
    global $form, $pearDB;
    if (!$contact_id) {
        return;
    }
    $rq = "SELECT * FROM contact_servicecommands_relation ";
    $rq .= "WHERE contact_contact_id = '" . (int)$contact_id . "'";
    $dbResult = $pearDB->query($rq);
    $cmds = array();
    while ($arr = $dbResult->fetch()) {
        $cmds[$arr["command_command_id"]] = $arr["command_command_id"];
    }
    $ret = $form->getSubmitValue("contact_svNotifCmds");
    if (!empty($ret)) {
        for ($i = 0; $i < count($ret); $i++) {
            if (!isset($cmds[$ret[$i]])) {
                $rq = "INSERT INTO contact_servicecommands_relation ";
                $rq .= "(contact_contact_id, command_command_id) ";
                $rq .= "VALUES ";
                $rq .= "('" . (int)$contact_id . "', '" . $ret[$i] . "')";
                $dbResult = $pearDB->query($rq);
            }
        }
    }
}

/**
 * @param null $contact_id
 * @param array $ret
 */
function updateContactContactGroup($contact_id = null, $ret = array())
{
    global $centreon, $form, $pearDB;
    if (!$contact_id) {
        return;
    }
    $rq = "DELETE FROM contactgroup_contact_relation "
        . "WHERE contact_contact_id = '" . (int)$contact_id . "' "
        . "AND ( "
        . "    contactgroup_cg_id IN (SELECT cg_id FROM contactgroup WHERE cg_type = 'local') "
        . "    OR contact_contact_id IN (SELECT contact_id FROM contact WHERE contact_auth_type = 'local') "
        . ") ";
    $dbResult = $pearDB->query($rq);

    if (isset($ret["contact_cgNotif"])) {
        $ret = $ret["contact_cgNotif"];
    } else {
        $ret = CentreonUtils::mergeWithInitialValues($form, 'contact_cgNotif');
    }
    for ($i = 0; $i < count($ret); $i++) {
        $rq = "INSERT INTO contactgroup_contact_relation ";
        $rq .= "(contact_contact_id, contactgroup_cg_id) ";
        $rq .= "VALUES ";
        $rq .= "('" . (int)$contact_id . "', '" . $ret[$i] . "')";
        $dbResult = $pearDB->query($rq);
    }
    CentreonCustomView::syncContactGroupCustomView($centreon, $pearDB, $contact_id);
}

/*
 * For massive change. We just add the new list if the elem doesn't exist yet
 */
/**
 * @param null $contact_id
 * @param array $ret
 */
function updateContactContactGroup_MC($contact_id = null, $ret = array())
{
    global $centreon, $form, $pearDB;
    if (!$contact_id) {
        return;
    }
    $dbResult = $pearDB->prepare(
        "SELECT * FROM contactgroup_contact_relation " .
        "WHERE contact_contact_id = :contactId"
    );
    $dbResult->bindValue(':contactId', (int)$contact_id, \PDO::PARAM_INT);
    $dbResult->execute();
    $cmds = array();
    while ($arr = $dbResult->fetch()) {
        $cmds[$arr["contactgroup_cg_id"]] = $arr["contactgroup_cg_id"];
    }
    $ret = $form->getSubmitValue("contact_cgNotif");
    if (!empty($ret)) {
        for ($i = 0; $i < count($ret); $i++) {
            if (!isset($cmds[$ret[$i]])) {
                $dbResult = $pearDB->prepare(
                    "INSERT INTO contactgroup_contact_relation " .
                    "(contact_contact_id, contactgroup_cg_id) " .
                    "VALUES (:contactId, :contactgroupId)"
                );
                $dbResult->bindValue(':contactId', (int)$contact_id, PDO::PARAM_INT);
                $dbResult->bindValue(':contactgroupId', $ret[$i], PDO::PARAM_INT);
                $dbResult->execute();
            }
        }
    }
    CentreonCustomView::syncContactGroupCustomView($centreon, $pearDB, $contact_id);
}

/**
 * @param array $tmpContacts
 * @return bool
 */
function insertLdapContactInDB($tmpContacts = array())
{
    global $nbr, $centreon, $pearDB;
    $tmpConf = array();
    $ldapInstances = array();
    $contactTemplates = array();
    foreach ($tmpContacts["select"] as $select_key => $select_value) {
        if ($tmpContacts['contact_name'][$select_key] == '-') {
            $tmpContacts['contact_name'][$select_key] = $tmpContacts["contact_alias"][$select_key];
        }
        $tmpContacts["contact_name"][$select_key] = str_replace(
            array(" ", ","),
            array("_", "_"),
            $tmpContacts["contact_name"][$select_key]
        );
        $arId = $tmpContacts["ar_id"][$select_key];

        if (
            isset($tmpContacts["contact_name"][$select_key])
            && testContactExistence($tmpContacts["contact_name"][$select_key])
        ) {
            $tmpConf["contact_name"] = $tmpContacts["contact_name"][$select_key];
            $tmpConf["contact_alias"] = $tmpContacts["contact_alias"][$select_key];
            $tmpConf["contact_email"] = $tmpContacts["contact_email"][$select_key];
            $tmpConf["contact_pager"] = $tmpContacts["contact_pager"][$select_key];
            $tmpConf["contact_oreon"]["contact_oreon"] = "0";
            $tmpConf["contact_admin"]["contact_admin"] = "0";
            $tmpConf["contact_type_msg"] = "txt";
            $tmpConf["contact_lang"] = "en_US";
            $tmpConf["contact_auth_type"] = "ldap";
            $tmpConf["contact_ldap_dn"] = $tmpContacts["dn"][$select_key];
            $tmpConf["contact_activate"]["contact_activate"] = "1";
            $tmpConf["contact_comment"] = "Ldap Import - " . date("d/m/Y - H:i:s", time());
            $tmpConf["contact_location"] = "0";
            $tmpConf["contact_register"] = "1";
            $tmpConf["contact_enable_notifications"]["contact_enable_notifications"] = "2";
            insertContactInDB($tmpConf);
            unset($tmpConf);
        }
        /*
         * Get the contact_id
         */
        $query = "SELECT contact_id FROM contact WHERE contact_ldap_dn = '" .
            $pearDB->escape($tmpContacts["dn"][$select_key]) . "'";
        try {
            $res = $pearDB->query($query);
        } catch (\PDOException $e) {
            return false;
        }
        $row = $res->fetch();
        $contact_id = $row['contact_id'];

        if (!isset($ldapInstances[$arId])) {
            $ldap = new CentreonLDAP($pearDB, null, $arId);
            $ldapAdmin = new CentreonLDAPAdmin($pearDB);
            $opt = $ldapAdmin->getGeneralOptions($arId);
            if (isset($opt['ldap_contact_tmpl']) && $opt['ldap_contact_tmpl']) {
                $contactTemplates[$arId] = $opt['ldap_contact_tmpl'];
            }
        } else {
            $ldap = $ldapInstances[$arId];
        }
        if ($contact_id) {
            $sqlUpdate = "UPDATE contact SET ar_id = " . $pearDB->escape($arId) .
                " %s  WHERE contact_id = " . (int)$contact_id;
            $tmplSql = "";
            if (isset($contactTemplates[$arId])) {
                $tmplSql = ", contact_template_id = " . $pearDB->escape($contactTemplates[$arId]);
            }
            $pearDB->query(sprintf($sqlUpdate, $tmplSql));
        }
        $listGroup = array();
        if (false !== $ldap->connect()) {
            $listGroup = $ldap->listGroupsForUser($tmpContacts["dn"][$select_key]);
        }
        if (count($listGroup) > 0) {
            $query = "SELECT cg_id FROM contactgroup WHERE cg_name IN ('" . join("','", $listGroup) . "')";
            try {
                $res = $pearDB->query($query);
            } catch (\PDOException $e) {
                return false;
            }

            // Insert the relation between contact and contactgroups
            while ($row = $res->fetch()) {
                $query = "INSERT INTO contactgroup_contact_relation (contactgroup_cg_id, contact_contact_id) " .
                    "VALUES (" . $row['cg_id'] . ", " . (int) $contact_id . ")";
                $pearDB->query($query);
            }
        }

        //Insert a relation between LDAP's default contactgroup and the contact
        $ldap->addUserToLdapDefaultCg(
            $arId,
            $contact_id
        );
    }
    return true;
}

/**
 *
 * Update ACL groups links with this user
 * @param $contact_id
 */
function updateAccessGroupLinks($contact_id, $ret = array())
{
    global $form, $pearDB;

    if (!$contact_id) {
        return;
    }

    /*
     * Empty all ACL Links
     */
    $pearDB->query("DELETE FROM acl_group_contacts_relations WHERE contact_contact_id = '" . (int)$contact_id . "'");

    if (isset($ret['contact_acl_groups'])) {
        $ret = $ret['contact_acl_groups'];
    } else {
        $ret = CentreonUtils::mergeWithInitialValues($form, 'contact_acl_groups');
    }

    for ($i = 0; $i < count($ret); $i++) {
        $rq = "INSERT INTO acl_group_contacts_relations ";
        $rq .= "(contact_contact_id, acl_group_id) ";
        $rq .= "VALUES ";
        $rq .= "('" . (int)$contact_id . "', '" . $ret[$i] . "')";
        $dbResult = $pearDB->query($rq);
    }
}

/**
 *
 * Update ACL groups links with this user during massive changes
 * @param $contact_id
 * @param $ret
 */
function updateAccessGroupLinks_MC($contact_id, $flag)
{
    global $form, $pearDB;

    if (!$contact_id) {
        return;
    }

    $ret = array();
    $ret = $form->getSubmitValues();

    /*
     * Empty all ACL Links
     */
    if ($flag) {
        $query = "DELETE FROM acl_group_contacts_relations WHERE contact_contact_id = '" . (int)$contact_id . "'";
        $pearDB->query($query);
    }
    if (isset($ret["contact_acl_groups"])) {
        foreach ($ret["contact_acl_groups"] as $key => $value) {
            $rq = "INSERT INTO acl_group_contacts_relations ";
            $rq .= "(contact_contact_id, acl_group_id) ";
            $rq .= "VALUES ";
            $rq .= "('" . (int)$contact_id . "', '" . $value . "')";
            $dbResult = $pearDB->query($rq);
        }
    }
}

/**
 * Get contact ID by name
 *
 * @param string $name
 * @return int
 */
function getContactIdByName($name)
{
    global $pearDB;

    $id = 0;
    $res = $pearDB->query("SELECT contact_id FROM contact WHERE contact_name = '" . $pearDB->escape($name) . "'");
    if ($res->rowCount()) {
        $row = $res->fetch();
        $id = $row['contact_id'];
    }
    return $id;
}


/**
 * Sanitize all the contact parameters from the contact form and return a ready to bind array.
 *
 * @param array $ret
 * @return array
 */
function sanitizeFormContactParameters(array $ret): array
{
    global $encryptType, $dependencyInjector;
    $bindParams = [];
    foreach ($ret as $inputName => $inputValue) {
        switch ($inputName) {
            case 'timeperiod_tp_id':
            case 'timeperiod_tp_id2':
            case 'contact_template_id':
                $bindParams[':' . $inputName] = [
                    \PDO::PARAM_INT => (filter_var($inputValue, FILTER_VALIDATE_INT) === false)
                        ? null
                        : (int) $inputValue
                ];
                break;
            case 'contact_location':
                $bindParams[':' . $inputName] = [
                    \PDO::PARAM_INT => (filter_var($inputValue, FILTER_VALIDATE_INT) === false)
                        ? 0
                        : (int) $inputValue
                ];
                break;
            case 'contact_register':
                $bindParams[':' . $inputName] = [
                    \PDO::PARAM_INT => (filter_var($inputValue, FILTER_VALIDATE_INT) === false)
                        ? 1
                        : (int) $inputValue
                ];
                break;
            case 'contact_hostNotifOpts':
                $inputValue = filter_var(implode(",", array_keys($inputValue)), FILTER_SANITIZE_STRING);
                if (empty($inputValue)) {
                    $bindParams[':contact_host_notification_options'] = [\PDO::PARAM_STR => null];
                } else {
                    $bindParams[':contact_host_notification_options'] = [\PDO::PARAM_STR => $inputValue];
                }
                break;
            case 'contact_svNotifOpts':
                $inputValue = filter_var(implode(",", array_keys($inputValue)), FILTER_SANITIZE_STRING);
                if (empty($inputValue)) {
                    $bindParams[':contact_service_notification_options'] = [\PDO::PARAM_STR => null];
                } else {
                    $bindParams[':contact_service_notification_options'] = [\PDO::PARAM_STR => $inputValue];
                }
                break;
            case 'contact_oreon':
                // ldap import, then force contact to be a user
                if (isset($_POST['contact_select']['select'])) {
                    $bindParams[':' . $inputName] = [
                        \PDO::PARAM_STR => '1'
                    ];
                } else {
                    $bindParams[':' . $inputName] = [
                        \PDO::PARAM_STR => in_array($inputValue[$inputName], ['0', '1'])
                            ? $inputValue[$inputName]
                            : null
                    ];
                }
                break;
            case 'contact_activate':
                $bindParams[':' . $inputName] = [
                    \PDO::PARAM_STR => in_array($inputValue[$inputName], ['0', '1'])
                        ? $inputValue[$inputName]
                        : null
                ];
                break;
            case 'reach_api':
            case 'reach_api_rt':
                $bindParams[':' . $inputName] = [
                    \PDO::PARAM_INT => in_array($inputValue[$inputName], ['0', '1'])
                        ? (int) $inputValue[$inputName]
                        : 0
                ];
                break;
            case 'contact_enable_notifications':
                $bindParams[':' . $inputName] = [
                    \PDO::PARAM_STR => in_array($inputValue[$inputName], ['0', '1', '2'])
                        ? $inputValue[$inputName]
                        : '2'
                ];
                break;
            case 'contact_admin':
                $bindParams[':' . $inputName] = [
                    \PDO::PARAM_STR => in_array($inputValue[$inputName], ['0', '1'])
                        ? $inputValue[$inputName]
                        : '0'
                ];
                break;
            case 'contact_type_msg':
                $bindParams[':' . $inputName] = [
                    \PDO::PARAM_STR => in_array($inputValue, ['txt', 'html', 'pdf'])
                        ? $inputValue
                        : 'txt'
                ];
                break;
            case 'contact_lang':
                if (!empty($inputValue)) {
                    $inputValue = filter_var($inputValue, FILTER_SANITIZE_STRING);
                    if (empty($inputValue)) {
                        $bindParams[':' . $inputName] = [\PDO::PARAM_STR => 'browser'];
                    } else {
                        $bindParams[':' . $inputName] = [\PDO::PARAM_STR => $inputValue];
                    }
                }
                break;
            case 'contact_auth_type':
                if (!empty($inputValue)) {
                    $inputValue = filter_var($inputValue, FILTER_SANITIZE_STRING);
                    if (empty($inputValue)) {
                        $bindParams[':' . $inputName] = [\PDO::PARAM_STR => 'local'];
                    } else {
                        $bindParams[':' . $inputName] = [\PDO::PARAM_STR => $inputValue];
                    }
                }
                break;
            case 'contact_alias':
            case 'contact_name':
                if (
                    $inputValue = filter_var(
                        $inputValue ?? "",
                        FILTER_SANITIZE_STRING,
                        FILTER_FLAG_NO_ENCODE_QUOTES
                    )
                ) {
                    if (!empty($inputValue)) {
                        $bindParams[':' . $inputName] = [\PDO::PARAM_STR => $inputValue];
                    } else {
                        throw new \InvalidArgumentException('Bad Parameter');
                    }
                }
                break;
            case 'contact_autologin_key':
            case 'contact_email':
            case 'contact_pager':
            case 'contact_comment':
            case 'contact_ldap_dn':
            case 'contact_address1':
            case 'contact_address2':
            case 'contact_address3':
            case 'contact_address4':
            case 'contact_address5':
            case 'contact_address6':
                if (
                    $inputValue = filter_var(
                        $inputValue ?? "",
                        FILTER_SANITIZE_STRING,
                        FILTER_FLAG_NO_ENCODE_QUOTES
                    )
                ) {
                    if (!empty($inputValue)) {
                        $bindParams[':' . $inputName] = [\PDO::PARAM_STR => $inputValue];
                    }
                }
                break;
        }
    }
    return $bindParams;
}

/**
 * Validate password creation using defined security policy.
 *
 * @param array $fields
 * @return mixed
 */
function validatePasswordCreation(array $fields)
{
    global $pearDB;
    $errors = [];
    $password = $fields['contact_passwd'];
    if (empty($password)) {
        return true;
    }
    try {
        $statement = $pearDB->query("SELECT * from password_security_policy");
    } catch (\PDOException $e) {
        return false;
    }
    $passwordPolicy = $statement->fetch(\PDO::FETCH_ASSOC);

    if (strlen($password) < (int) $passwordPolicy['password_length']) {
        $errors['contact_passwd'] = sprintf(
            _("Your password should be %d characters long."),
            (int) $passwordPolicy['password_length']
        );
    }
    if ((bool) $passwordPolicy['uppercase_characters'] === true && !preg_match('/[A-Z]/', $password)) {
        $errors['contact_passwd'] = _("Your password should contains uppercase characters.");
    }
    if ((bool) $passwordPolicy['lowercase_characters'] === true && !preg_match('/[a-z]/', $password)) {
        $errors['contact_passwd'] = _("Your password should contains lowercase characters.");
    }
    if ((bool) $passwordPolicy['integer_characters'] === true && !preg_match('/[0-9]/', $password)) {
        $errors['contact_passwd'] = _("Your password should contains integer characters.");
    }
    if ((bool) $passwordPolicy['special_characters'] === true && !preg_match('/[@$!%*?&]/', $password)) {
        $errors['contact_passwd'] = _("Your password should contains special characters form the list '@$!%*?&'.");
    }

    return count($errors) > 0 ? $errors : true;
}

/**
 * Validate password creation using defined security policy.
 *
 * @param array $fields
 * @return mixed
 */
function validatePasswordModification(array $fields)
{
    global $pearDB;
    $errors = [];
    $password = $fields['contact_passwd'];
    $contactId = $fields['contact_id'];
    if (empty($password)) {
        return true;
    }
    try {
        $statement1 = $pearDB->query("SELECT * from password_security_policy");
        $passwordPolicy = $statement1->fetch(\PDO::FETCH_ASSOC);
        $statement2 = $pearDB->prepare(
            "SELECT creation_date FROM contact_password " .
            "WHERE contact_id = :contactId ORDER BY creation_date DESC LIMIT 1"
        );
        $statement2->bindValue(':contactId', $contactId, \PDO::PARAM_INT);
        $statement2->execute();
    } catch (\PDOException $e) {
        return false;
    }
    if ($passwordCreationDate = $statement2->fetchColumn()) {
        $passwordCreationDate = (int) $passwordCreationDate;
        $delayBeforeNewPassword = (int) $passwordPolicy['delay_before_new_password'];
        $isPasswordCanBeChanged = $passwordCreationDate + $delayBeforeNewPassword < time();
        if (!$isPasswordCanBeChanged) {
            $errors['contact_passwd'] = _(
                "You can't change your password because the delay before changing password is not over."
            );
        }
    };
    if ((bool) $passwordPolicy['can_reuse_password'] === false) {
        try {
            $statement = $pearDB->prepare(
                "SELECT id, password FROM `contact_password` WHERE `contact_id` = :contactId"
            );
            $statement->bindParam(':contactId', $contactId, \PDO::PARAM_INT);
            $statement->execute();

            $passwordHistory = $statement->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($passwordHistory as $contactPassword) {
                if (password_verify($password, $contactPassword['password'])) {
                    $errors['contact_passwd'] = _(
                        "Your password has already been used. " .
                        "Please choose a different password from the previous three."
                    );
                    break;
                }
            }
        } catch (\PDOException $e) {
            return false;
        }
    }

    return count($errors) > 0 ? $errors : true;
}
