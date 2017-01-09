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

function testContactGroupExistence($name = null)
{
    global $pearDB, $form, $centreon;
    $id = null;

    if (isset($form)) {
        $id = $form->getSubmitValue('cg_id');
    }

    $DBRESULT = $pearDB->query("SELECT `cg_name`, `cg_id` FROM `contactgroup` WHERE `cg_name` = '" . htmlentities($centreon->checkIllegalChar($name)) . "'");
    $cg = $DBRESULT->fetchRow();

    if ($DBRESULT->numRows() >= 1 && $cg["cg_id"] == $id) {
        /*
         * Modif case
         */
        return true;
    } elseif ($DBRESULT->numRows() >= 1 && $cg["cg_id"] != $id) {
        /*
         * Duplicate entry
         */
        return false;
    } else {
        return true;
    }
}

function enableContactGroupInDB($cg_id = null)
{
    global $pearDB, $centreon;

    if (!$cg_id) {
        return;
    }
    $DBRESULT = $pearDB->query("UPDATE `contactgroup` SET `cg_activate` = '1' WHERE `cg_id` = '" . intval($cg_id) . "'");
    
    $DBRESULT2 = $pearDB->query("SELECT cg_name FROM `contactgroup` WHERE `cg_id` = '" . intval($cg_id) . "' LIMIT 1");
    $row = $DBRESULT2->fetchRow();

    $centreon->CentreonLogAction->insertLog("contactgroup", $cg_id, $row['cg_name'], "enable");
}

function disableContactGroupInDB($cg_id = null)
{
    global $pearDB, $centreon;

    if (!$cg_id) {
        return;
    }
    $DBRESULT = $pearDB->query("UPDATE `contactgroup` SET `cg_activate` = '0' WHERE `cg_id` = '" . intval($cg_id) . "'");
    
    $DBRESULT2 = $pearDB->query("SELECT cg_name FROM `contactgroup` WHERE `cg_id` = '" . intval($cg_id) . "' LIMIT 1");
    $row = $DBRESULT2->fetchRow();
    
    $centreon->CentreonLogAction->insertLog("contactgroup", $cg_id, $row['cg_name'], "disable");
}

function deleteContactGroupInDB($contactGroups = array())
{
    global $pearDB, $centreon;

    foreach ($contactGroups as $key => $value) {
        $DBRESULT2 = $pearDB->query("SELECT cg_name FROM `contactgroup` WHERE `cg_id` = '" . intval($key) . "' LIMIT 1");
        $row = $DBRESULT2->fetchRow();

        $DBRESULT = $pearDB->query("DELETE FROM `contactgroup` WHERE `cg_id` = '" . intval($key) . "'");
        
        $centreon->CentreonLogAction->insertLog("contactgroup", $key, $row['cg_name'], "d");
    }
}

function multipleContactGroupInDB($contactGroups = array(), $nbrDup = array())
{
    global $pearDB, $centreon;

    foreach ($contactGroups as $key => $value) {
        $DBRESULT = $pearDB->query("SELECT * FROM `contactgroup` WHERE `cg_id` = '" . intval($key) . "' LIMIT 1");

        $row = $DBRESULT->fetchRow();
        $row["cg_id"] = '';
        for ($i = 1; $i <= $nbrDup[$key]; $i++) {
            $val = null;
            foreach ($row as $key2 => $value2) {
                $key2 == "cg_name" ? ($cg_name = $value2 = $value2 . "_" . $i) : null;
                $val ? $val .= ", '" . $value2 . "'" : $val .= "'" . $value2 . "'";
                if ($key2 != "cg_id") {
                    $fields[$key2] = $value2;
                }
                if (isset($cg_name)) {
                    $fields["cg_name"] = $cg_name;
                }
            }
            if (isset($cg_name) && testContactGroupExistence($cg_name)) {
                $val ? $rq = "INSERT INTO `contactgroup` VALUES (" . $val . ")" : $rq = null;
                $DBRESULT = $pearDB->query($rq);

                $DBRESULT = $pearDB->query("SELECT MAX(cg_id) FROM `contactgroup`");
                $maxId = $DBRESULT->fetchRow();

                if (isset($maxId["MAX(cg_id)"])) {
                    $DBRESULT = $pearDB->query("SELECT DISTINCT `cgcr`.`contact_contact_id` FROM `contactgroup_contact_relation` `cgcr` WHERE `cgcr`.`contactgroup_cg_id` = '" . intval($key) . "'");
                    $fields["cg_contacts"] = "";
                    while ($cct = $DBRESULT->fetchRow()) {
                        $DBRESULT2 = $pearDB->query("INSERT INTO `contactgroup_contact_relation` VALUES ('', '" . $cct["contact_contact_id"] . "', '" . $maxId["MAX(cg_id)"] . "')");
                        $fields["cg_contacts"] .= $cct["contact_contact_id"] . ",";
                    }
                    $fields["cg_contacts"] = trim($fields["cg_contacts"], ",");
                    $centreon->CentreonLogAction->insertLog("contactgroup", $maxId["MAX(cg_id)"], $cg_name, "a", $fields);
                }
            }
        }
    }
}

function insertContactGroupInDB($ret = array())
{
    $cg_id = insertContactGroup($ret);
    updateContactGroupContacts($cg_id, $ret);
    updateContactGroupAclGroups($cg_id, $ret);
    return $cg_id;
}

function insertContactGroup($ret)
{
    global $form, $pearDB, $centreon;

    if (!count($ret)) {
        $ret = $form->getSubmitValues();
    }

    $ret["cg_name"] = $centreon->checkIllegalChar($ret["cg_name"]);

    $rq = "INSERT INTO `contactgroup` (`cg_name`, `cg_alias`, `cg_comment`, `cg_activate`) ";
    $rq .= "VALUES ('" . CentreonDB::escape($ret["cg_name"]) . "', '" . CentreonDB::escape($ret["cg_alias"]) . "', '" . CentreonDB::escape($ret["cg_comment"]) . "', '" . $ret["cg_activate"]["cg_activate"] . "')";
    $DBRESULT = $pearDB->query($rq);

    $DBRESULT = $pearDB->query("SELECT MAX(cg_id) FROM `contactgroup`");
    $cg_id = $DBRESULT->fetchRow();
    
    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($ret);
    $centreon->CentreonLogAction->insertLog("contactgroup", $cg_id["MAX(cg_id)"], CentreonDB::escape($ret["cg_name"]), "a", $fields);
    return ($cg_id["MAX(cg_id)"]);
}

function updateContactGroupInDB($cg_id = null, $params = array())
{
    if (!$cg_id) {
        return;
    }

    updateContactGroup($cg_id, $params);
    updateContactGroupContacts($cg_id, $params);
    updateContactGroupAclGroups($cg_id, $params);
}

function updateContactGroup($cg_id = null, $params = array())
{
    global $form, $pearDB, $centreon;
    if (!$cg_id) {
        return;
    }
    $ret = array();
    if (count($params)) {
        $ret = $params;
    } else {
        $ret = $form->getSubmitValues();
    }

    $ret["cg_name"] = $centreon->checkIllegalChar($ret["cg_name"]);

    $rq = "UPDATE `contactgroup` ";
    $rq .= "SET `cg_name` = '" . CentreonDB::escape($ret["cg_name"]) . "', " .
            "`cg_alias` = '" . CentreonDB::escape($ret["cg_alias"]) . "', " .
            "`cg_comment` = '" . CentreonDB::escape($ret["cg_comment"]) . "', " .
            "`cg_activate` = '" . $ret["cg_activate"]["cg_activate"] . "' " .
            "WHERE `cg_id` = '" . intval($cg_id) . "'";
    $DBRESULT = $pearDB->query($rq);
    
    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($ret);
    $centreon->CentreonLogAction->insertLog("contactgroup", $cg_id, CentreonDB::escape($ret["cg_name"]), "c", $fields);
}

function updateContactGroupContacts($cg_id, $ret = array())
{
    global $centreon, $form, $pearDB;
    if (!$cg_id) {
        return;
    }

    $rq = "DELETE FROM `contactgroup_contact_relation` WHERE `contactgroup_cg_id` = '" . intval($cg_id) . "'";
    $DBRESULT = $pearDB->query($rq);

    if (isset($ret["cg_contacts"])) {
        $ret = $ret["cg_contacts"];
    } else {
        $ret = CentreonUtils::mergeWithInitialValues($form, 'cg_contacts');
    }

    for ($i = 0; $i < count($ret); $i++) {
        $rq = "INSERT INTO `contactgroup_contact_relation` (`contact_contact_id`, `contactgroup_cg_id`) ";
        $rq .= "VALUES ('" . $ret[$i] . "', '" . intval($cg_id) . "')";
        $DBRESULT = $pearDB->query($rq);

        CentreonCustomView::syncContactGroupCustomView($centreon, $pearDB, $ret[$i]);
    }
}

function updateContactGroupAclGroups($cg_id, $ret = array())
{
    global $form, $pearDB;

    if (!$cg_id) {
        return;
    }

    $rq = "DELETE FROM `acl_group_contactgroups_relations` WHERE `cg_cg_id` = " . intval($cg_id);
    $res = $pearDB->query($rq);

    if (isset($ret["cg_acl_groups"])) {
        $ret = $ret["cg_acl_groups"];
    } else {
        $ret = CentreonUtils::mergeWithInitialValues($form, 'cg_acl_groups');
    }

    for ($i = 0; $i < count($ret); $i++) {
        $rq = "INSERT INTO `acl_group_contactgroups_relations` (`acl_group_id`, `cg_cg_id`) ";
        $rq .= "VALUES ('" . $ret[$i] . "', '" . intval($cg_id) . "')";
        $DBRESULT = $pearDB->query($rq);
    }
}

/**
 * Get contact group id by name
 *
 * @param string $name
 * @return int
 */
function getContactGroupIdByName($name)
{
    global $pearDB;

    $id = 0;
    $res = $pearDB->query("SELECT cg_id FROM contactgroup WHERE cg_name = '" . CentreonDB::escape($name) . "'");
    if ($res->numRows()) {
        $row = $res->fetchRow();
        $id = $row['cg_id'];
    }
    return $id;
}
