<?php
/*
 * Copyright 2005-2015 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
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


require_once _CENTREON_PATH_ . '/www/class/centreonUtils.class.php';

/**
 *
 * Test that a contact doesn't exists
 * @param $name
 */
function testContactExistence($name = null)
{
    global $pearDB, $form, $centreon;

    $id = null;
    if (isset($form)) {
        $id = $form->getSubmitValue('contact_id');
    }

    $DBRESULT = $pearDB->query("SELECT contact_name, contact_id FROM contact WHERE contact_name = '" . htmlentities($centreon->checkIllegalChar($name), ENT_QUOTES, "UTF-8") . "'");
    $contact = $DBRESULT->fetchRow();

    if ($DBRESULT->numRows() >= 1 && $contact["contact_id"] == $id) {
        return true;
    } elseif ($DBRESULT->numRows() >= 1 && $contact["contact_id"] != $id) {
        return false;
    } else {
        return true;
    }
}

/**
 *
 * Test that alias is not use
 * @param $alias
 */
function testAliasExistence($alias = null)
{
    global $pearDB, $form;
    $id = null;
    if (isset($form)) {
        $id = $form->getSubmitValue('contact_id');
    }

    $DBRESULT = $pearDB->query("SELECT contact_alias, contact_id FROM contact WHERE contact_alias = '" . htmlentities($alias, ENT_QUOTES, "UTF-8") . "'");
    $contact = $DBRESULT->fetchRow();

    if ($DBRESULT->numRows() >= 1 && $contact["contact_id"] == $id) {
        return true;
    } elseif ($DBRESULT->numRows() >= 1 && $contact["contact_id"] != $id) {
        return false;
    } else {
        return true;
    }
}

/**
 *
 * Check if a least one contact is enable
 */
function keepOneContactAtLeast($ct_id = null)
{
    global $pearDB, $form, $centreon;

    if (!isset($contact_id)) {
        $contact_id = $ct_id;
    } elseif (isset($_GET["contact_id"])) {
        $contact_id = htmlentities($_GET["contact_id"], ENT_QUOTES, "UTF-8");
        ;
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
    $DBRESULT = $pearDB->query("SELECT COUNT(*) AS nbr_valid
            FROM contact
            WHERE contact_activate = '1'
            AND contact_oreon = '1'
            AND contact_id <> '" . $pearDB->escape($contact_id) . "'");
    $contacts = $DBRESULT->fetchRow();

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
        $DBRESULT = $pearDB->query("UPDATE contact SET contact_activate = '1' WHERE contact_id = '" . intval($key). "'");

        $DBRESULT2 = $pearDB->query("SELECT contact_name FROM `contact` WHERE `contact_id` = '" . intval($key) . "' LIMIT 1");
        $row = $DBRESULT2->fetchRow();

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
            $pearDB->query("UPDATE contact SET contact_activate = '0' WHERE contact_id = '" . intval($key) . "'");

            $DBRESULT2 = $pearDB->query("SELECT contact_name FROM `contact` WHERE `contact_id` = '" . intval($key) . "' LIMIT 1");
            $row = $DBRESULT2->fetchRow();

            $centreon->CentreonLogAction->insertLog("contact", $key, $row['contact_name'], "disable");
        }
    }
}

/**
 *
 * Delete Contacts
 * @param $contacts
 */
function deleteContactInDB($contacts = array())
{
    global $pearDB, $centreon;

    foreach ($contacts as $key => $value) {
        $DBRESULT2 = $pearDB->query("SELECT contact_name FROM `contact` WHERE `contact_id` = '" . intval($key) . "' LIMIT 1");
        $row = $DBRESULT2->fetchRow();

        $DBRESULT = $pearDB->query("DELETE FROM contact WHERE contact_id = '" . intval($key) . "'");
        $centreon->CentreonLogAction->insertLog("contact", $key, $row['contact_name'], "d");
    }
}

/**
 *
 * Duplicate contacts
 * @param $contacts
 * @param $nbrDup
 */
function multipleContactInDB($contacts = array(), $nbrDup = array())
{
    global $pearDB, $centreon;

    foreach ($contacts as $key => $value) {
        $DBRESULT = $pearDB->query("SELECT * FROM contact WHERE contact_id = '" . intval($key) . "' LIMIT 1");
        $row = $DBRESULT->fetchRow();
        $row["contact_id"] = '';
        for ($i = 1; $i <= $nbrDup[$key]; $i++) {
            $val = null;
            foreach ($row as $key2 => $value2) {
                $key2 == "contact_name" ? ($contact_name = $value2 = $value2 . "_" . $i) : null;
                $key2 == "contact_alias" ? ($contact_alias = $value2 = $value2 . "_" . $i) : null;
                $val ? $val .= ($value2 != null ? (", '" . $value2 . "'") : ", NULL") : $val .= ($value2 != null ? ("'" . $value2 . "'") : "NULL");
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

            if (isset($row['contact_name'])) {
                $row["contact_name"] = $centreon->checkIllegalChar($row["contact_name"]);
            }

            if (testContactExistence($contact_name) && testAliasExistence($contact_alias)) {
                $val ? $rq = "INSERT INTO contact VALUES (" . $val . ")" : $rq = null;
                $DBRESULT = $pearDB->query($rq);
                $DBRESULT = $pearDB->query("SELECT MAX(contact_id) FROM contact");
                $maxId = $DBRESULT->fetchRow();
                if (isset($maxId["MAX(contact_id)"])) {

                    /*
                     * ACL update
                     */
                    $query = "SELECT DISTINCT acl_group_id FROM acl_group_contacts_relations " .
                        "WHERE contact_contact_id = " . (int)$key;
                    $dbResult = $pearDB->query($query);
                    $fields["contact_aclRelation"] = "";
                    while ($aclRelation = $dbResult->fetchRow()) {
                        $query = "INSERT INTO acl_group_contacts_relations VALUES ('', '" .
                            $maxId["MAX(contact_id)"] . "', '" . $aclRelation["acl_group_id"] . "')";
                        $pearDB->query($query);
                        $fields["contact_aclRelation"] .= $aclRelation["acl_group_id"] . ",";
                    }
                    $fields["contact_aclRelation"] = trim($fields["contact_aclRelation"], ",");

                    /*
                     * Command update
                     */
                    $DBRESULT = $pearDB->query("SELECT DISTINCT command_command_id FROM contact_hostcommands_relation WHERE contact_contact_id = '" . intval($key) . "'");
                    $fields["contact_hostNotifCmds"] = "";
                    while ($hostCmd = $DBRESULT->fetchRow()) {
                        $DBRESULT2 = $pearDB->query("INSERT INTO contact_hostcommands_relation VALUES ('', '" . $maxId["MAX(contact_id)"] . "', '" . $hostCmd["command_command_id"] . "')");
                        $fields["contact_hostNotifCmds"] .= $hostCmd["command_command_id"] . ",";
                    }
                    $fields["contact_hostNotifCmds"] = trim($fields["contact_hostNotifCmds"], ",");

                    /*
                     * Commands update
                     */
                    $DBRESULT = $pearDB->query("SELECT DISTINCT command_command_id FROM contact_servicecommands_relation WHERE contact_contact_id = '" . intval($key) . "'");
                    $fields["contact_svNotifCmds"] = "";
                    while ($serviceCmd = $DBRESULT->fetchRow()) {
                        $DBRESULT2 = $pearDB->query("INSERT INTO contact_servicecommands_relation VALUES ('', '" . $maxId["MAX(contact_id)"] . "', '" . $serviceCmd["command_command_id"] . "')");
                        $fields["contact_svNotifCmds"] .= $serviceCmd["command_command_id"] . ",";
                    }
                    $fields["contact_svNotifCmds"] = trim($fields["contact_svNotifCmds"], ",");

                    /*
                     * Contact groups
                     */
                    $DBRESULT = $pearDB->query("SELECT DISTINCT contactgroup_cg_id FROM contactgroup_contact_relation WHERE contact_contact_id = '" . intval($key) . "'");
                    $fields["contact_cgNotif"] = "";
                    while ($Cg = $DBRESULT->fetchRow()) {
                        $DBRESULT2 = $pearDB->query("INSERT INTO contactgroup_contact_relation VALUES ('', '" . $maxId["MAX(contact_id)"] . "', '" . $Cg["contactgroup_cg_id"] . "')");
                        $fields["contact_cgNotif"] .= $Cg["contactgroup_cg_id"] . ",";
                    }
                    $fields["contact_cgNotif"] = trim($fields["contact_cgNotif"], ",");
                    $centreon->CentreonLogAction->insertLog("contact", $maxId["MAX(contact_id)"], $contact_name, "a", $fields);
                }
            }
        }
    }
}

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

function insertContactInDB($ret = array())
{
    $contact_id = insertContact($ret);
    updateContactHostCommands($contact_id, $ret);
    updateContactServiceCommands($contact_id, $ret);
    updateContactContactGroup($contact_id, $ret);
    updateAccessGroupLinks($contact_id);
    return ($contact_id);
}

function insertContact($ret = array())
{
    global $form, $pearDB, $centreon, $encryptType;

    $utilsObject = new CentreonUtils();

    if (!count($ret)) {
        $ret = $form->getSubmitValues();
    }

    $ret["contact_name"] = $centreon->checkIllegalChar($ret["contact_name"]);
    $bindParams = sanitizeFormContactParameters($ret);

    // Build Query Dynamically
    $rq = "INSERT INTO `contact` (`contact_id` ,";
    $rq .= implode(', ', array_keys($bindParams));
    $rq .= ") VALUES (NULL, ";
    for($i = 0; $i < count($bindParams); $i++) {
        $rq .= "?, ";
    }
    $rq = rtrim($rq, ", ");
    $rq .= ")";


    $pearDB->query($rq, $bindParams);
    $DBRESULT = $pearDB->query("SELECT MAX(contact_id) FROM contact");
    $contact_id = $DBRESULT->fetchRow();

    if (isset($ret["contact_passwd"])) {
        if ($encryptType == 1) {
            $ret["contact_passwd"] = $ret["contact_passwd2"] = $utilsObject->encodePass($ret["contact_passwd"], 'md5');
        } elseif ($encryptType == 2) {
            $ret["contact_passwd"] = $ret["contact_passwd2"] = $utilsObject->encodePass($ret["contact_passwd"], 'sha1');
        } else {
            $ret["contact_passwd"] = $ret["contact_passwd2"] = $utilsObject->encodePass($ret["contact_passwd"], 'md5');
        }
    }

    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($ret);
    $centreon->CentreonLogAction->insertLog("contact", $contact_id["MAX(contact_id)"], $ret["contact_name"], "a", $fields);

    return ($contact_id["MAX(contact_id)"]);
}

function updateContact($contact_id = null)
{
    global $form, $pearDB, $centreon, $encryptType;
    if (!$contact_id) {
        return;
    }
    $ret = array();
    $ret = $form->getSubmitValues();
    $bindParams = sanitizeFormContactParameters($ret);
    $utilsObject = new CentreonUtils();

    // remove illegal chars in data sent by the user
    $ret["contact_name"] = CentreonUtils::escapeSecure($ret["contact_name"], CentreonUtils::ESCAPE_ILLEGAL_CHARS);
    $ret["contact_alias"] = CentreonUtils::escapeSecure($ret["contact_alias"], CentreonUtils::ESCAPE_ILLEGAL_CHARS);

    $rq = "UPDATE contact SET ";
    foreach(array_keys($bindParams) as $parameter) {
        $rq .= $parameter . " = ?, ";
    }
    $rq = rtrim($rq, ", ");
    $rq .= " WHERE contact_id = '" . (int) $contact_id . "'";

    $pearDB->query($rq, $bindParams);

    if (isset($ret["contact_lang"]) && $ret["contact_lang"] != null && $contact_id == $centreon->user->get_id()) {
        $centreon->user->set_lang($ret["contact_lang"]);
    }

    if (isset($ret["contact_passwd"])) {
        if ($encryptType == 1) {
            $ret["contact_passwd"] = $ret["contact_passwd2"] = $utilsObject->encodePass($ret["contact_passwd"], 'md5');
        } elseif ($encryptType == 2) {
            $ret["contact_passwd"] = $ret["contact_passwd2"] = $utilsObject->encodePass($ret["contact_passwd"], 'sha1');
        } else {
            $ret["contact_passwd"] = $ret["contact_passwd2"] = $utilsObject->encodePass($ret["contact_passwd"], 'md5');
        }
    }

    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($ret);
    $centreon->CentreonLogAction->insertLog("contact", $contact_id, $ret["contact_name"], "c", $fields);
}

function updateContact_MC($contact_id = null)
{
    global $form, $pearDB, $centreon, $encryptType;

    if (!$contact_id) {
        return;
    }

    $utilsObject = new CentreonUtils();

    $ret = array();
    $ret = $form->getSubmitValues();
    $bindParams = sanitizeFormContactParameters($ret);
    $rq = "UPDATE contact SET ";
    foreach(array_keys($bindParams) as $parameter) {
        $rq .= $parameter . " = ?, ";
    }
    $rq = rtrim($rq, ", ");
    $rq .= " WHERE contact_id = '" . (int) $contact_id . "'";

    $pearDB->query($rq, $bindParams);

    $DBRESULT2 = $pearDB->query("SELECT contact_name FROM `contact` WHERE contact_id='" . intval($contact_id) . "' LIMIT 1");
    $row = $DBRESULT2->fetchRow();

    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($ret);
    $centreon->CentreonLogAction->insertLog("contact", $contact_id, $row["contact_name"], "mc", $fields);
}

function updateContactHostCommands($contact_id = null, $ret = array())
{
    global $form, $pearDB;

    if (!$contact_id) {
        return;
    }
    $rq = "DELETE FROM contact_hostcommands_relation ";
    $rq .= "WHERE contact_contact_id = '" . intval($contact_id) . "'";
    $DBRESULT = $pearDB->query($rq);
    if (isset($ret["contact_hostNotifCmds"])) {
        $ret = $ret["contact_hostNotifCmds"];
    } else {
        $ret = $form->getSubmitValue("contact_hostNotifCmds");
    }

    for ($i = 0; $i < count($ret); $i++) {
        $rq = "INSERT INTO contact_hostcommands_relation ";
        $rq .= "(contact_contact_id, command_command_id) ";
        $rq .= "VALUES ";
        $rq .= "('" . intval($contact_id) . "', '" . $ret[$i] . "')";
        $DBRESULT = $pearDB->query($rq);
    }
}

/*
 * For massive change. We just add the new list if the elem doesn't exist yet
 */

function updateContactHostCommands_MC($contact_id = null, $ret = array())
{
    global $form, $pearDB;
    if (!$contact_id) {
        return;
    }
    $rq = "SELECT * FROM contact_hostcommands_relation ";
    $rq .= "WHERE contact_contact_id = '" . intval($contact_id) . "'";
    $DBRESULT = $pearDB->query($rq);
    $cmds = array();
    while ($arr = $DBRESULT->fetchRow()) {
        $cmds[$arr["command_command_id"]] = $arr["command_command_id"];
    }
    $ret = $form->getSubmitValue("contact_hostNotifCmds");
    for ($i = 0; $i < count($ret); $i++) {
        if (!isset($cmds[$ret[$i]])) {
            $rq = "INSERT INTO contact_hostcommands_relation ";
            $rq .= "(contact_contact_id, command_command_id) ";
            $rq .= "VALUES ";
            $rq .= "('" . intval($contact_id) . "', '" . $ret[$i] . "')";
            $DBRESULT = $pearDB->query($rq);
        }
    }
}

function updateContactServiceCommands($contact_id = null, $ret = array())
{
    global $form, $pearDB;
    if (!$contact_id) {
        return;
    }
    $rq = "DELETE FROM contact_servicecommands_relation ";
    $rq .= "WHERE contact_contact_id = '" . intval($contact_id) . "'";
    $DBRESULT = $pearDB->query($rq);
    if (isset($ret["contact_svNotifCmds"])) {
        $ret = $ret["contact_svNotifCmds"];
    } else {
        $ret = $form->getSubmitValue("contact_svNotifCmds");
    }
    for ($i = 0; $i < count($ret); $i++) {
        $rq = "INSERT INTO contact_servicecommands_relation ";
        $rq .= "(contact_contact_id, command_command_id) ";
        $rq .= "VALUES ";
        $rq .= "('" . intval($contact_id) . "', '" . $ret[$i] . "')";
        $DBRESULT = $pearDB->query($rq);
    }
}

/*
 * For massive change. We just add the new list if the elem doesn't exist yet
 */

function updateContactServiceCommands_MC($contact_id = null, $ret = array())
{
    global $form, $pearDB;
    if (!$contact_id) {
        return;
    }
    $rq = "SELECT * FROM contact_servicecommands_relation ";
    $rq .= "WHERE contact_contact_id = '" . intval($contact_id) . "'";
    $DBRESULT = $pearDB->query($rq);
    $cmds = array();
    while ($arr = $DBRESULT->fetchRow()) {
        $cmds[$arr["command_command_id"]] = $arr["command_command_id"];
    }
    $ret = $form->getSubmitValue("contact_svNotifCmds");
    for ($i = 0; $i < count($ret); $i++) {
        if (!isset($cmds[$ret[$i]])) {
            $rq = "INSERT INTO contact_servicecommands_relation ";
            $rq .= "(contact_contact_id, command_command_id) ";
            $rq .= "VALUES ";
            $rq .= "('" . intval($contact_id) . "', '" . $ret[$i] . "')";
            $DBRESULT = $pearDB->query($rq);
        }
    }
}

function updateContactContactGroup($contact_id = null, $ret = array())
{
    global $centreon, $form, $pearDB;
    if (!$contact_id) {
        return;
    }
    $rq = "DELETE FROM contactgroup_contact_relation "
        . "WHERE contact_contact_id = '" . intval($contact_id) . "' "
        . "AND ( "
        . "    contactgroup_cg_id IN (SELECT cg_id FROM contactgroup WHERE cg_type = 'local') "
        . "    OR contact_contact_id IN (SELECT contact_id FROM contact WHERE contact_auth_type = 'local') "
        . ") ";
    $DBRESULT = $pearDB->query($rq);

    if (isset($ret["contact_cgNotif"])) {
        $ret = $ret["contact_cgNotif"];
    } else {
        $ret = CentreonUtils::mergeWithInitialValues($form, 'contact_cgNotif');
    }
    for ($i = 0; $i < count($ret); $i++) {
        $rq = "INSERT INTO contactgroup_contact_relation ";
        $rq .= "(contact_contact_id, contactgroup_cg_id) ";
        $rq .= "VALUES ";
        $rq .= "('" . intval($contact_id) . "', '" . $ret[$i] . "')";
        $DBRESULT = $pearDB->query($rq);
    }
    CentreonCustomView::syncContactGroupCustomView($centreon, $pearDB, $contact_id);
}

/*
 * For massive change. We just add the new list if the elem doesn't exist yet
 */

function updateContactContactGroup_MC($contact_id = null, $ret = array())
{
    global $centreon, $form, $pearDB;
    if (!$contact_id) {
        return;
    }
    $rq = "SELECT * FROM contactgroup_contact_relation ";
    $rq .= "WHERE contact_contact_id = '" . intval($contact_id) . "'";
    $DBRESULT = $pearDB->query($rq);
    $cmds = array();
    while ($arr = $DBRESULT->fetchRow()) {
        $cmds[$arr["contactgroup_cg_id"]] = $arr["contactgroup_cg_id"];
    }
    $ret = $form->getSubmitValue("contact_cgNotif");

    for ($i = 0; $i < count($ret); $i++) {
        if (!isset($cmds[$ret[$i]])) {
            $rq = "INSERT INTO contactgroup_contact_relation ";
            $rq .= "(contact_contact_id, contactgroup_cg_id) ";
            $rq .= "VALUES ";
            $rq .= "('" . intval($contact_id) . "', '" . $ret[$i] . "')";
            $DBRESULT = $pearDB->query($rq);
        }
    }
    CentreonCustomView::syncContactGroupCustomView($centreon, $pearDB, $contact_id);
}

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
        $tmpContacts["contact_name"][$select_key] = str_replace(array(" ", ","), array("_", "_"), $tmpContacts["contact_name"][$select_key]);
        $arId = $tmpContacts["ar_id"][$select_key];

        if (isset($tmpContacts["contact_name"][$select_key]) && testContactExistence($tmpContacts["contact_name"][$select_key])) {
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
        $query = "SELECT contact_id FROM contact WHERE contact_ldap_dn = '" . $pearDB->escape($tmpContacts["dn"][$select_key]) . "'";
        $res = $pearDB->query($query);
        if (PEAR::isError($res)) {
            return false;
        }
        $row = $res->fetchRow();
        $contact_id = $row['contact_id'];

        if (!isset($ldapInstances[$arId])) {
            $ldapInstances[$arId] = new CentreonLDAP($pearDB, null, $arId);
            $ldapAdmin = new CentreonLDAPAdmin($pearDB);
            $ldap = $ldapInstances[$arId];
            $opt = $ldapAdmin->getGeneralOptions($arId);
            if (isset($opt['ldap_contact_tmpl']) && $opt['ldap_contact_tmpl']) {
                $contactTemplates[$arId] = $opt['ldap_contact_tmpl'];
            }
        } else {
            $ldap = $ldapInstances[$arId];
        }
        if ($contact_id) {
            $sqlUpdate = "UPDATE contact SET ar_id = " . $pearDB->escape($arId) . " %s  WHERE contact_id = " . intval($contact_id);
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
            $res = $pearDB->query($query);
            if (PEAR::isError($res)) {
                return false;
            }
            /*
             * Insert the relation between contact and contact group
             */
            while ($row = $res->fetchRow()) {
                $query = "INSERT INTO contactgroup_contact_relation (contactgroup_cg_id, contact_contact_id)
                                          VALUES (" . $row['cg_id'] . ", " . intval($contact_id) . ")";
                $pearDB->query($query);
            }
        }
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
    $pearDB->query("DELETE FROM acl_group_contacts_relations WHERE contact_contact_id = '" . intval($contact_id) . "'");

    if (isset($ret['contact_acl_groups'])) {
        $ret = $ret['contact_acl_groups'];
    } else {
        $ret = CentreonUtils::mergeWithInitialValues($form, 'contact_acl_groups');
    }

    for ($i = 0; $i < count($ret); $i++) {
        $rq = "INSERT INTO acl_group_contacts_relations ";
        $rq .= "(contact_contact_id, acl_group_id) ";
        $rq .= "VALUES ";
        $rq .= "('" . intval($contact_id) . "', '" . $ret[$i] . "')";
        $DBRESULT = $pearDB->query($rq);
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
        $DBRESULT = $pearDB->query("DELETE FROM acl_group_contacts_relations WHERE contact_contact_id = '" . intval($contact_id) . "'");
    }
    if (isset($ret["contact_acl_groups"])) {
        foreach ($ret["contact_acl_groups"] as $key => $value) {
            $rq = "INSERT INTO acl_group_contacts_relations ";
            $rq .= "(contact_contact_id, acl_group_id) ";
            $rq .= "VALUES ";
            $rq .= "('" . intval($contact_id) . "', '" . $value . "')";
            $DBRESULT = $pearDB->query($rq);
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
    if ($res->numRows()) {
        $row = $res->fetchRow();
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
function sanitizeFormContactParameters($ret)
{
    global $encryptType;
    $utilsObject = new CentreonUtils();
    $bindParams = [];
    foreach ($ret as $inputName => $inputValue) {
        switch ($inputName) {
            case 'timeperiod_tp_id':
            case 'timeperiod_tp_id2':
            case 'contact_template_id':
                $bindParams[$inputName] = (filter_var($inputValue, FILTER_VALIDATE_INT) === false)
                        ? null
                        : (int) $inputValue;
                break;
            case 'contact_location':
                $bindParams[$inputName] =
                    (filter_var($inputValue, FILTER_VALIDATE_INT) === false)
                        ? 0
                        : (int) $inputValue;
                break;
            case 'contact_register':
                $bindParams[$inputName] =
                    (filter_var($inputValue, FILTER_VALIDATE_INT) === false)
                        ? 1
                        : (int) $inputValue;
                break;
            case 'contact_hostNotifOpts':
                $bindParams['contact_host_notification_options'] =
                    (($inputValue = filter_var(
                        implode(",", array_keys($inputValue)),
                        FILTER_SANITIZE_STRING
                    )) === false)
                        ? null
                        : $inputValue;
                break;
            case 'contact_svNotifOpts':
                $bindParams['contact_service_notification_options'] =
                    (($inputValue = filter_var(
                        implode(",", array_keys($inputValue)),
                        FILTER_SANITIZE_STRING
                    )) === false)
                        ? null
                        : $inputValue;
                break;
            case 'contact_oreon':
                 // ldap import, then force contact to be a user
                if (isset($_POST['contact_select']['select'])) {
                    $bindParams[$inputName] = '1';
                } else {
                    $bindParams[$inputName] =
                        in_array($inputValue[$inputName], ['0', '1'])
                            ? $inputValue[$inputName]
                            : null;
                }
                break;
            case 'contact_activate':
                $bindParams[$inputName] =
                    in_array($inputValue[$inputName], ['0', '1'])
                        ? $inputValue[$inputName]
                        : null;
                break;
            case 'reach_api':
            case 'reach_api_rt':
                $bindParams[$inputName] =
                    in_array($inputValue[$inputName], ['0', '1'])
                        ? (int) $inputValue[$inputName]
                        : 0;
                break;
            case 'contact_enable_notifications':
                $bindParams[$inputName] =
                    in_array($inputValue[$inputName], ['0', '1', '2'])
                        ? $inputValue[$inputName]
                        : '2';
                break;
            case 'contact_admin':
                $bindParams[$inputName] =
                    in_array($inputValue[$inputName], ['0', '1'])
                        ? $inputValue[$inputName]
                        : '0';
                break;
            case 'contact_type_msg':
                $bindParams[$inputName] =
                    in_array($inputValue, ['txt', 'html', 'pdf'])
                        ? $inputValue
                        : 'txt';
                break;
            case 'contact_passwd':
                if (!empty($inputValue)) {
                    $password = $utilsObject->encodePass(
                        $inputValue,
                        $encryptType == 2 ?  'sha1' : 'md5'
                    );
                    $bindParams[$inputName] = $password;
                }
                break;
            case 'contact_lang':
                if (!empty($inputValue)) {
                    $bindParams[$inputName] =
                        (($inputValue = filter_var($inputValue, FILTER_SANITIZE_STRING)) === false)
                            ? 'browser'
                            : $inputValue;
                }
                break;
            case 'contact_auth_type':
                if (!empty($inputValue)) {
                    $bindParams[$inputName] =
                        (($inputValue = filter_var($inputValue, FILTER_SANITIZE_STRING)) === false)
                            ? 'local'
                            : $inputValue;
                }
                break;
            case 'contact_name':
            case 'contact_alias':
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
                if (!empty($inputValue)) {
                    if ($inputValue = filter_var($inputValue, FILTER_SANITIZE_STRING)) {
                        $bindParams[$inputName] = $inputValue;
                    } else {
                        throw new \InvalidArgumentException('Bad Parameter');
                    }
                }
                break;
        }
    }
    return $bindParams;
}
