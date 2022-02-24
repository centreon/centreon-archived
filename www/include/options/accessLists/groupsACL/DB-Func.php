<?php

/*
 * Copyright 2005-2022 Centreon
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

require_once _CENTREON_PATH_ . 'www/class/centreonLDAP.class.php';
require_once _CENTREON_PATH_ . 'www/class/centreonContactgroup.class.php';

/**
 * Set the Acl group changed flag to 1
 *
 * @param $db CentreonDB
 * @param $aclGroupId int
 */
function setAclGroupChanged($db, $aclGroupId)
{
    $prepare = $db->prepare(
        "UPDATE acl_groups SET acl_group_changed = '1' WHERE acl_group_id = :id"
    );
    $prepare->bindValue(':id', $aclGroupId, \PDO::PARAM_INT);
    $prepare->execute();
}

/**
 * Test if group exists
 *
 * @param null $name
 * @return bool
 */
function testGroupExistence($name = null)
{
    global $pearDB, $form;

    $id = null;

    if (isset($form)) {
        $id = $form->getSubmitValue('acl_group_id');
    }
    $query = "SELECT acl_group_id, acl_group_name "
        . "FROM acl_groups "
        . "WHERE acl_group_name = '" . htmlentities($name, ENT_QUOTES, "UTF-8") . "' ";
    $dbResult = $pearDB->query($query);
    $cg = $dbResult->fetch();
    if ($dbResult->rowCount() >= 1 && $cg["acl_group_id"] == $id) {
        return true;
    } elseif ($dbResult->rowCount() >= 1 && $cg["acl_group_id"] != $id) {
        # Duplicate entry
        return false;
    } else {
        return true;
    }
}

/**
 * @param null $acl_group_id
 * @param array $groups
 */
function enableGroupInDB($acl_group_id = null, $groups = array())
{
    global $pearDB, $centreon;

    if (!$acl_group_id && !count($groups)) {
        return;
    }

    if ($acl_group_id) {
        $groups = array($acl_group_id => "1");
    }

    foreach ($groups as $key => $value) {
        $pearDB->query("UPDATE acl_groups SET acl_group_activate = '1' WHERE acl_group_id = '" . $key . "'");
        $query = "SELECT acl_group_name FROM `acl_groups` WHERE acl_group_id = '" . (int)$key . "' LIMIT 1";
        $dbResult = $pearDB->query($query);
        $row = $dbResult->fetch();
        $centreon->CentreonLogAction->insertLog("access group", $key, $row['acl_group_name'], "enable");
    }
}

/**
 * @param null $acl_group_id
 * @param array $groups
 */
function disableGroupInDB($acl_group_id = null, $groups = array())
{
    global $pearDB, $centreon;

    if (!$acl_group_id && !count($groups)) {
        return;
    }
    if ($acl_group_id) {
        $groups = array($acl_group_id => "1");
    }

    foreach ($groups as $key => $value) {
        $pearDB->query("UPDATE acl_groups SET acl_group_activate = '0' WHERE acl_group_id = '" . $key . "'");
        $query = "SELECT acl_group_name FROM `acl_groups` WHERE acl_group_id = '" . (int)$key . "' LIMIT 1";
        $dbResult = $pearDB->query($query);
        $row = $dbResult->fetch();
        $centreon->CentreonLogAction->insertLog("access group", $key, $row['acl_group_name'], "disable");
    }
}

/**
 *
 * Delete the selected group in DB
 * @param $groups
 */
function deleteGroupInDB($groups = array())
{
    global $pearDB, $centreon;

    foreach ($groups as $key => $value) {
        $query = "SELECT acl_group_name FROM `acl_groups` WHERE acl_group_id = '" . (int)$key . "' LIMIT 1";
        $dbResult = $pearDB->query($query);
        $row = $dbResult->fetch();
        $pearDB->query("DELETE FROM acl_groups WHERE acl_group_id = '" . $key . "'");
        $centreon->CentreonLogAction->insertLog("access group", $key, $row['acl_group_name'], "d");
    }
}

/**
 *
 * Duplicate the selected group
 * @param $groups
 * @param $nbrDup
 */
function multipleGroupInDB($groups = array(), $nbrDup = array())
{
    global $pearDB, $centreon;

    foreach ($groups as $key => $value) {
        $dbResult = $pearDB->prepare("SELECT * FROM acl_groups WHERE acl_group_id = :aclGroupId LIMIT 1");
        $dbResult->bindValue('aclGroupId', $key, PDO::PARAM_INT);
        $dbResult->execute();
        $row = $dbResult->fetch();
        $row["acl_group_id"] = '';

        for ($i = 1; $i <= $nbrDup[$key]; $i++) {
            $val = null;
            foreach ($row as $key2 => $value2) {
                $key2 == "acl_group_name" ? ($acl_group_name = $value2 = $value2 . "_" . $i) : null;
                $val ? $val .= ($value2 != null ? (", '" . $value2 . "'") : ", NULL")
                    : $val .= ($value2 != null ? ("'" . $value2 . "'") : "NULL");
                if ($key2 != "acl_group_id") {
                    $fields[$key2] = $value2;
                }
                if (isset($acl_group_name)) {
                    $fields["acl_group_name"] = $acl_group_name;
                }
            }

            if (testGroupExistence($acl_group_name) && $val) {
                $val ? $rq = "INSERT INTO acl_groups VALUES (" . $val . ")" : $rq = null;
                $pearDB->query($rq);
                $dbResult = $pearDB->query("SELECT MAX(acl_group_id) FROM acl_groups");
                $maxId = $dbResult->fetch();
                $dbResult->closeCursor();

                /*
				 * Duplicate Links
				 */
                duplicateContacts($key, $maxId["MAX(acl_group_id)"], $pearDB);
                duplicateContactGroups($key, $maxId["MAX(acl_group_id)"], $pearDB);
                duplicateResources($key, $maxId["MAX(acl_group_id)"], $pearDB);
                duplicateActions($key, $maxId["MAX(acl_group_id)"], $pearDB);
                duplicateMenus($key, $maxId["MAX(acl_group_id)"], $pearDB);

                $centreon->CentreonLogAction->insertLog(
                    "access group",
                    $maxId["MAX(acl_group_id)"],
                    $acl_group_name,
                    "a",
                    $fields
                );
            }
        }
    }
}

/**
 *
 * Insert group in DB
 * @param $ret
 */
function insertGroupInDB($ret = array())
{
    global $form, $centreon;

    $acl_group_id = insertGroup($ret);
    updateGroupContacts($acl_group_id, $ret);
    updateGroupContactGroups($acl_group_id);
    updateGroupActions($acl_group_id);
    updateGroupResources($acl_group_id);
    updateGroupMenus($acl_group_id);

    $ret = $form->getSubmitValues();
    $fields = CentreonLogAction::prepareChanges($ret);
    $centreon->CentreonLogAction->insertLog("access group", $acl_group_id, $ret['acl_group_name'], "a", $fields);

    return $acl_group_id;
}

/**
 * Insert a new access group
 *
 * @param $groupInfos Array containing group's informations
 * @global $form    HTML_QuickFormCustom
 * @global $pearDB  CentreonDB
 * @return int Return id of the new access group
 */
function insertGroup($groupInfos)
{
    global $form, $pearDB;

    if (!count($groupInfos)) {
        $groupInfos = $form->getSubmitValues();
    }

    $isAclGroupActivate = false;
    if (isset($groupInfos['acl_group_activate'])
        && isset($groupInfos['acl_group_activate']['acl_group_activate'])
        && $groupInfos['acl_group_activate']['acl_group_activate'] == '1'
    ) {
        $isAclGroupActivate = true;
    }

    $request = "INSERT INTO acl_groups "
            . "(acl_group_name, acl_group_alias, acl_group_activate) "
            . "VALUES (:group_name, :group_alias, :is_activate)";

    $prepare = $pearDB->prepare($request);
    $prepare->bindValue(
        ':group_name',
        $groupInfos['acl_group_name'],
        \PDO::PARAM_STR
    );
    $prepare->bindValue(
        ':group_alias',
        $groupInfos['acl_group_alias'],
        \PDO::PARAM_STR
    );
    $prepare->bindValue(
        ':group_alias',
        $groupInfos['acl_group_alias'],
        \PDO::PARAM_STR
    );
    $prepare->bindValue(
        ':is_activate',
        ($isAclGroupActivate ? '1': '0'),
        \PDO::PARAM_STR
    );
    return $prepare->execute()
        ? $pearDB->lastInsertId()
        : null;
}

/**
 *
 * Update Group in DB
 * @param $acl_group_id
 */
function updateGroupInDB($acl_group_id = null)
{
    if (!$acl_group_id) {
        return;
    }
    global $form, $centreon;

    updateGroup($acl_group_id);
    updateGroupContacts($acl_group_id);
    updateGroupContactGroups($acl_group_id);
    updateGroupActions($acl_group_id);
    updateGroupResources($acl_group_id);
    updateGroupMenus($acl_group_id);

    $ret = $form->getSubmitValues();
    $fields = CentreonLogAction::prepareChanges($ret);
    $centreon->CentreonLogAction->insertLog("access group", $acl_group_id, $ret['acl_group_name'], "c", $fields);
}

/**
 * Update the selected group
 *
 * @param int $acl_group_id
 * @global $form HTML_QuickFormCustom
 * @global $pearDB CentreonDB
 */
function updateGroup($aclGroupId = null)
{
    global $form, $pearDB;

    if (is_null($aclGroupId)) {
        return;
    }

    $groupInfos = $form->getSubmitValues();

    $isAclGroupActivate = false;
    if (isset($groupInfos['acl_group_activate'])
        && isset($groupInfos['acl_group_activate']['acl_group_activate'])
        && $groupInfos['acl_group_activate']['acl_group_activate'] == '1'
    ) {
        $isAclGroupActivate = true;
    }

    $request = "UPDATE acl_groups "
        . "SET acl_group_name = :acl_group_name, "
        . "acl_group_alias = :acl_group_alias, "
        . "acl_group_activate = :is_activate "
        . "WHERE acl_group_id = :acl_group_id";

    $prepare = $pearDB->prepare($request);
    $prepare->bindValue(
        ':acl_group_name',
        $groupInfos["acl_group_name"],
        \PDO::PARAM_STR
    );
    $prepare->bindValue(
        ':acl_group_alias',
        $groupInfos["acl_group_alias"],
        \PDO::PARAM_STR
    );
    $prepare->bindValue(
        ':is_activate',
        ($isAclGroupActivate ? '1': '0'),
        \PDO::PARAM_STR
    );
    $prepare->bindValue(
        ':acl_group_id',
        $aclGroupId,
        \PDO::PARAM_INT
    );

    $prepare->execute();

    setAclGroupChanged($pearDB, $aclGroupId);
}

/**
 *
 * Update Contacts lists
 * @param $acl_group_id
 * @param $ret
 */
function updateGroupContacts($acl_group_id, $ret = array())
{
    global $form, $pearDB;

    if (!$acl_group_id) {
        return;
    }

    $rq = "DELETE FROM acl_group_contacts_relations WHERE acl_group_id = '" . $acl_group_id . "'";
    $dbResult = $pearDB->query($rq);
    if (isset($_POST["cg_contacts"])) {
        foreach ($_POST["cg_contacts"] as $id) {
            $rq = "INSERT INTO acl_group_contacts_relations ";
            $rq .= "(contact_contact_id, acl_group_id) ";
            $rq .= "VALUES ";
            $rq .= "('" . $id . "', '" . $acl_group_id . "')";
            $dbResult = $pearDB->query($rq);
        }
    }
}

/**
 *
 * Update contact group list
 * @param $acl_group_id
 * @param $ret
 */
function updateGroupContactGroups($acl_group_id, $ret = array())
{
    global $form, $pearDB;

    if (!$acl_group_id) {
        return;
    }

    $rq = "DELETE FROM acl_group_contactgroups_relations WHERE acl_group_id = '" . $acl_group_id . "'";
    $dbResult = $pearDB->query($rq);
    if (isset($_POST["cg_contactGroups"])) {
        $cg = new CentreonContactgroup($pearDB);
        foreach ($_POST["cg_contactGroups"] as $id) {
            if (!is_numeric($id)) {
                $res = $cg->insertLdapGroup($id);
                if ($res != 0) {
                    $id = $res;
                } else {
                    continue;
                }
            }
            $rq = "INSERT INTO acl_group_contactgroups_relations ";
            $rq .= "(cg_cg_id, acl_group_id) ";
            $rq .= "VALUES ";
            $rq .= "('" . $id . "', '" . $acl_group_id . "')";
            $dbResult = $pearDB->query($rq);
        }
    }
}

/**
 *
 * Update Group actions
 * @param $acl_group_id
 * @param $ret
 */
function updateGroupActions($acl_group_id, $ret = array())
{
    global $form, $pearDB;

    if (!$acl_group_id) {
        return;
    }

    $rq = "DELETE FROM acl_group_actions_relations WHERE acl_group_id = '" . $acl_group_id . "'";
    $dbResult = $pearDB->query($rq);
    if (isset($_POST["actionAccess"])) {
        foreach ($_POST["actionAccess"] as $id) {
            $rq = "INSERT INTO acl_group_actions_relations ";
            $rq .= "(acl_action_id, acl_group_id) ";
            $rq .= "VALUES ";
            $rq .= "('" . $id . "', '" . $acl_group_id . "')";
            $dbResult = $pearDB->query($rq);
        }
    }
}

/**
 *
 * Update Menu Access
 * @param $acl_group_id
 * @param $ret
 */
function updateGroupMenus($acl_group_id, $ret = array())
{
    global $form, $pearDB;

    if (!$acl_group_id) {
        return;
    }

    $rq = "DELETE FROM acl_group_topology_relations WHERE acl_group_id = '" . $acl_group_id . "'";
    $dbResult = $pearDB->query($rq);
    if (isset($_POST["menuAccess"])) {
        foreach ($_POST["menuAccess"] as $id) {
            $rq = "INSERT INTO acl_group_topology_relations ";
            $rq .= "(acl_topology_id, acl_group_id) ";
            $rq .= "VALUES ";
            $rq .= "('" . $id . "', '" . $acl_group_id . "')";
            $dbResult = $pearDB->query($rq);
        }
    }
}

/**
 *
 * Update Group ressources
 * @param $acl_group_id
 * @param $ret
 */
function updateGroupResources($acl_group_id, $ret = array())
{
    global $form, $pearDB;

    if (!$acl_group_id) {
        return;
    }

    $query = 'DELETE argr '
        . 'FROM acl_res_group_relations argr '
        . 'JOIN acl_resources ar ON argr.acl_res_id = ar.acl_res_id '
        . 'WHERE argr.acl_group_id = ' . $acl_group_id . ' '
        . 'AND ar.locked = 0 ';
    $pearDB->query($query);
    if (isset($_POST["resourceAccess"])) {
        foreach ($_POST["resourceAccess"] as $id) {
            $rq = "INSERT INTO acl_res_group_relations ";
            $rq .= "(acl_res_id, acl_group_id) ";
            $rq .= "VALUES ";
            $rq .= "('" . $id . "', '" . $acl_group_id . "')";
            $pearDB->query($rq);
        }
    }
}

/**
 *
 * Duplicate Contacts lists
 * @param $acl_group_id
 * @param $ret
 */
function duplicateContacts($idTD, $acl_id, $pearDB)
{
    $request = "INSERT INTO acl_group_contacts_relations (contact_contact_id, acl_group_id) "
        . "SELECT contact_contact_id, '$acl_id' AS acl_group_id "
        . "FROM acl_group_contacts_relations "
        . "WHERE acl_group_id = '$idTD'";
    $pearDB->query($request);
}

/**
 *
 * Duplicate Contactgroups lists
 * @param $acl_group_id
 * @param $ret
 */
function duplicateContactGroups($idTD, $acl_id, $pearDB)
{
    $request = "INSERT INTO acl_group_contactgroups_relations (cg_cg_id, acl_group_id) "
        . "SELECT cg_cg_id, '$acl_id' AS acl_group_id "
        . "FROM acl_group_contactgroups_relations "
        . "WHERE acl_group_id = '$idTD'";
    $pearDB->query($request);
}

/**
 *
 * Duplicate Resources lists
 * @param $acl_group_id
 * @param $ret
 */
function duplicateResources($idTD, $acl_id, $pearDB)
{
    $request = "INSERT INTO acl_res_group_relations (acl_res_id, acl_group_id) "
        . "SELECT acl_res_id, '$acl_id' AS acl_group_id "
        . "FROM acl_res_group_relations "
        . "WHERE acl_group_id = '$idTD'";
    $pearDB->query($request);
}

/**
 *
 * Duplicate Actions lists
 * @param $acl_group_id
 * @param $ret
 */
function duplicateActions($idTD, $acl_id, $pearDB)
{
    $request = "INSERT INTO acl_group_actions_relations (acl_action_id, acl_group_id) "
        . "SELECT acl_action_id, '$acl_id' AS acl_group_id "
        . "FROM acl_group_actions_relations "
        . "WHERE acl_group_id = '$idTD'";
    $pearDB->query($request);
}

/**
 *
 * Duplicate Menu lists
 * @param $acl_group_id
 * @param $ret
 */
function duplicateMenus($idTD, $acl_id, $pearDB)
{
    $request = "INSERT INTO acl_group_topology_relations (acl_topology_id, acl_group_id) "
        . "SELECT acl_topology_id, '$acl_id' AS acl_group_id "
        . "FROM acl_group_topology_relations "
        . "WHERE acl_group_id = '$idTD'";
    $pearDB->query($request);
}

/**
 * Rule for test if a ldap contactgroup name already exists
 *
 * @param array $listCgs The list of contactgroups to validate
 * @return boolean
 */
function testCg($list)
{
    return CentreonContactgroup::verifiedExists($list);
}
