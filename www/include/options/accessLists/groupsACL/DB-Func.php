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

require_once _CENTREON_PATH_ . 'www/class/centreonLDAP.class.php';
require_once _CENTREON_PATH_ . 'www/class/centreonContactgroup.class.php';

/**
 * Set the Acl group changed flag to 1
 *
 * @param $db
 * @param $aclGroupId
 */
function setAclGroupChanged($db, $aclGroupId)
{
    $db->query("UPDATE acl_groups SET acl_group_changed = '1' WHERE acl_group_id = " . $db->escape($aclGroupId));
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
        . "WHERE acl_group_name = '" . htmlentities($name, ENT_QUOTES, "UTF-8")."' ";
    $DBRESULT = $pearDB->query($query);
    $cg = $DBRESULT->fetchRow();
    if ($DBRESULT->numRows() >= 1 && $cg["acl_group_id"] == $id) {
        return true;
    } elseif ($DBRESULT->numRows() >= 1 && $cg["acl_group_id"] != $id) {
        # Duplicate entry
        return false;
    } else {
        return true;
    }
}

/**
 *
 * Enable the selected group in DB
 * @param $acl_group_id
 */
function enableGroupInDB($acl_group_id = null)
{
    global $pearDB;

    if (!$acl_group_id) {
        return;
    }
    $pearDB->query("UPDATE acl_groups SET acl_group_activate = '1' WHERE acl_group_id = '".$acl_group_id."'");
}

/**
 *
 * Disable the selected group in DB
 * @param $acl_group_id
 */
function disableGroupInDB($acl_group_id = null)
{
    global $pearDB;

    if (!$acl_group_id) {
        return;
    }
    $pearDB->query("UPDATE acl_groups SET acl_group_activate = '0' WHERE acl_group_id = '".$acl_group_id."'");
}

/**
 *
 * Delete the selected group in DB
 * @param $Groups
 */
function deleteGroupInDB($Groups = array())
{
    global $pearDB;

    foreach ($Groups as $key => $value) {
        $pearDB->query("DELETE FROM acl_groups WHERE acl_group_id = '".$key."'");
    }
}

/**
 *
 * Duplicate the selected group
 * @param $Groups
 * @param $nbrDup
 */
function multipleGroupInDB($Groups = array(), $nbrDup = array())
{
    global $pearDB;

    foreach ($Groups as $key => $value) {
        $DBRESULT = $pearDB->query("SELECT * FROM acl_groups WHERE acl_group_id = '".$key."' LIMIT 1");
        $row = $DBRESULT->fetchRow();
        $row["acl_group_id"] = '';

        for ($i = 1; $i <= $nbrDup[$key]; $i++) {
            $val = null;
            foreach ($row as $key2 => $value2) {
                $key2 == "acl_group_name" ? ($acl_group_name = $value2 = $value2."_".$i) : null;
                $val ? $val .= ", '".$value2."'" : $val .= "'".$value2."'";
            }

            if (testGroupExistence($acl_group_name)) {
                $val ? $rq = "INSERT INTO acl_groups VALUES (".$val.")" : $rq = null;
                $DBRESULT = $pearDB->query($rq);
                $DBRESULT = $pearDB->query("SELECT MAX(acl_group_id) FROM acl_groups");
                $maxId = $DBRESULT->fetchRow();
                $DBRESULT->free();

                /*
				 * Duplicate Links
				 */
                duplicateContacts($key, $maxId["MAX(acl_group_id)"], $pearDB);
                duplicateContactGroups($key, $maxId["MAX(acl_group_id)"], $pearDB);
                duplicateResources($key, $maxId["MAX(acl_group_id)"], $pearDB);
                duplicateActions($key, $maxId["MAX(acl_group_id)"], $pearDB);
                duplicateMenus($key, $maxId["MAX(acl_group_id)"], $pearDB);
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
    $acl_group_id = insertGroup($ret);
    updateGroupContacts($acl_group_id, $ret);
    updateGroupContactGroups($acl_group_id);
    updateGroupActions($acl_group_id);
    updateGroupResources($acl_group_id);
    updateGroupMenus($acl_group_id);
    return $acl_group_id;
}

/**
 *
 * Insert Group
 * @param $ret
 */
function insertGroup($ret)
{
    global $form, $pearDB;

    if (!count($ret)) {
        $ret = $form->getSubmitValues();
    }

    $rq = "INSERT INTO acl_groups "
        . "(acl_group_name, acl_group_alias, acl_group_activate) "
        . "VALUES "
        .  "('".htmlentities($ret["acl_group_name"], ENT_QUOTES, "UTF-8")."', "
        . "'" . htmlentities($ret["acl_group_alias"], ENT_QUOTES, "UTF-8") . "', "
        . "'" . htmlentities($ret["acl_group_activate"]["acl_group_activate"], ENT_QUOTES, "UTF-8") . "') ";
    $pearDB->query($rq);
    $DBRESULT = $pearDB->query("SELECT MAX(acl_group_id) FROM acl_groups");
    $cg_id = $DBRESULT->fetchRow();
    return ($cg_id["MAX(acl_group_id)"]);
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

    updateGroup($acl_group_id);
    updateGroupContacts($acl_group_id);
    updateGroupContactGroups($acl_group_id);
    updateGroupActions($acl_group_id);
    updateGroupResources($acl_group_id);
    updateGroupMenus($acl_group_id);
}

/**
 *
 * Upgrade information of the selected group
 * @param $acl_group_id
 */
function updateGroup($acl_group_id = null)
{
    global $form, $pearDB;

    if (!$acl_group_id) {
        return;
    }

    $ret = array();
    $ret = $form->getSubmitValues();
    $rq = "UPDATE acl_groups ";
    $rq .= "SET acl_group_name = '" . htmlentities($ret["acl_group_name"], ENT_QUOTES, "UTF-8") . "', "
        . "acl_group_alias = '".htmlentities($ret["acl_group_alias"], ENT_QUOTES, "UTF-8")."', "
        . "acl_group_activate = '"
        . htmlentities($ret["acl_group_activate"]["acl_group_activate"], ENT_QUOTES, "UTF-8")."' "
        . "WHERE acl_group_id = '".$acl_group_id."'";
    $pearDB->query($rq);
    setAclGroupChanged($pearDB, $acl_group_id);
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

    $rq = "DELETE FROM acl_group_contacts_relations WHERE acl_group_id = '".$acl_group_id."'";
    $DBRESULT = $pearDB->query($rq);
    if (isset($_POST["cg_contacts"])) {
        foreach ($_POST["cg_contacts"] as $id) {
            $rq = "INSERT INTO acl_group_contacts_relations ";
            $rq .= "(contact_contact_id, acl_group_id) ";
            $rq .= "VALUES ";
            $rq .= "('".$id."', '".$acl_group_id."')";
            $DBRESULT = $pearDB->query($rq);
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

    $rq = "DELETE FROM acl_group_contactgroups_relations WHERE acl_group_id = '".$acl_group_id."'";
    $DBRESULT = $pearDB->query($rq);
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
            $rq .= "('".$id."', '".$acl_group_id."')";
            $DBRESULT = $pearDB->query($rq);
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

    $rq = "DELETE FROM acl_group_actions_relations WHERE acl_group_id = '".$acl_group_id."'";
    $DBRESULT = $pearDB->query($rq);
    if (isset($_POST["actionAccess"])) {
        foreach ($_POST["actionAccess"] as $id) {
            $rq = "INSERT INTO acl_group_actions_relations ";
            $rq .= "(acl_action_id, acl_group_id) ";
            $rq .= "VALUES ";
            $rq .= "('".$id."', '".$acl_group_id."')";
            $DBRESULT = $pearDB->query($rq);
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

    $rq = "DELETE FROM acl_group_topology_relations WHERE acl_group_id = '".$acl_group_id."'";
    $DBRESULT = $pearDB->query($rq);
    if (isset($_POST["menuAccess"])) {
        foreach ($_POST["menuAccess"] as $id) {
            $rq = "INSERT INTO acl_group_topology_relations ";
            $rq .= "(acl_topology_id, acl_group_id) ";
            $rq .= "VALUES ";
            $rq .= "('".$id."', '".$acl_group_id."')";
            $DBRESULT = $pearDB->query($rq);
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

    $query = 'DELETE '
        . 'FROM acl_res_group_relations '
        . 'JOIN acl_resources ar ON acl_res_group_relations.acl_res_id = ar.acl_res_id '
        . 'AND acl_res_group_relations.acl_group_id = ' . $acl_group_id . ' '
        . 'AND ar.locked = 0 ';
    $pearDB->query($query);
    if (isset($_POST["resourceAccess"])) {
        foreach ($_POST["resourceAccess"] as $id) {
            $rq = "INSERT INTO acl_res_group_relations ";
            $rq .= "(acl_res_id, acl_group_id) ";
            $rq .= "VALUES ";
            $rq .= "('".$id."', '".$acl_group_id."')";
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
