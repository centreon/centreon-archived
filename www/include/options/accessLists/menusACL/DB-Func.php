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
 * SVN : $URL$
 * SVN : $Id$
 *
 */

if (!isset($centreon)) {
    exit();
}

function testExistence($name = null)
{
    global $pearDB, $form;
    $id = null;
    if (isset($form)) {
        $id = $form->getSubmitValue('lca_id');
    }
    $query = "SELECT acl_topo_name, acl_topo_id FROM `acl_topology` WHERE acl_topo_name = '" . $name . "'";
    $DBRESULT = $pearDB->query($query);
    $lca = $DBRESULT->fetchRow();
    #Modif case
    if ($DBRESULT->rowCount() >= 1 && $lca["acl_topo_id"] == $id) {
        return true;
    } #Duplicate entry
    elseif ($DBRESULT->rowCount() >= 1 && $lca["acl_topo_id"] != $id) {
        return false;
    } else {
        return true;
    }
}

/**
 * @param null $acl_topo_id
 * @param array $acls
 */
function enableLCAInDB($acl_topo_id = null, $acls = array())
{
    global $pearDB, $centreon;
    if (!$acl_topo_id && !count($acls)) {
        return;
    }
    if ($acl_topo_id) {
        $acls = array($acl_topo_id => "1");
    }

    foreach ($acls as $key => $value) {
        $pearDB->query("UPDATE `acl_topology` SET acl_topo_activate = '1' WHERE `acl_topo_id` = '" . $key . "'");
        $query = "SELECT acl_topo_name FROM `acl_topology` WHERE acl_topo_id = '" . intval($key) . "' LIMIT 1";
        $dbResult = $pearDB->query($query);
        $row = $dbResult->fetchRow();
        $centreon->CentreonLogAction->insertLog("menu access", $key, $row['acl_topo_name'], "enable");
    }
}

/**
 * @param null $acl_topo_id
 * @param array $acls
 */
function disableLCAInDB($acl_topo_id = null, $acls = array())
{
    global $pearDB, $centreon;
    if (!$acl_topo_id && !count($acls)) {
        return;
    }

    if ($acl_topo_id) {
        $acls = array($acl_topo_id => "1");
    }

    foreach ($acls as $key => $value) {
        $pearDB->query("UPDATE `acl_topology` SET acl_topo_activate = '0' WHERE `acl_topo_id` = '" . $key . "'");
        $query = "SELECT acl_topo_name FROM `acl_topology` WHERE acl_topo_id = '" . intval($key) . "' LIMIT 1";
        $dbResult = $pearDB->query($query);
        $row = $dbResult->fetchRow();
        $centreon->CentreonLogAction->insertLog("menu access", $key, $row['acl_topo_name'], "disable");
    }
}

/**
 * @param array $acls
 */
function deleteLCAInDB($acls = array())
{
    global $pearDB, $centreon;
    foreach ($acls as $key => $value) {
        $query = "SELECT acl_topo_name FROM `acl_topology` WHERE acl_topo_id = '" . intval($key) . "' LIMIT 1";
        $dbResult = $pearDB->query($query);
        $row = $dbResult->fetchRow();
        $pearDB->query("DELETE FROM `acl_topology` WHERE acl_topo_id = '" . $key . "'");
        $centreon->CentreonLogAction->insertLog("menu access", $key, $row['acl_topo_name'], "d");
    }
}

/**
 * @param array $lcas
 * @param array $nbrDup
 */
function multipleLCAInDB($lcas = array(), $nbrDup = array())
{
    global $pearDB, $centreon;
    foreach ($lcas as $key => $value) {
        $DBRESULT = $pearDB->query("SELECT * FROM `acl_topology` WHERE acl_topo_id = '" . $key . "' LIMIT 1");
        $row = $DBRESULT->fetchRow();
        $row["acl_topo_id"] = '';
        for ($i = 1; $i <= $nbrDup[$key]; $i++) {
            $val = null;
            foreach ($row as $key2 => $value2) {
                $key2 == "acl_topo_name" ? ($acl_name = $value2 = $value2 . "_" . $i) : null;
                $val ? $val .= ($value2 != null ? (", '" . $value2 . "'") : ", NULL")
                    : $val .= ($value2 != null ? ("'" . $value2 . "'") : "NULL");
                if ($key2 != "acl_topo_id") {
                    $fields[$key2] = $value2;
                }
                if (isset($acl_topo_name)) {
                    $fields["acl_topo_name"] = $acl_topo_name;
                }
            }
            if (testExistence($acl_name)) {
                $val ? $rq = "INSERT INTO acl_topology VALUES (" . $val . ")" : $rq = null;
                $pearDB->query($rq);
                $DBRESULT = $pearDB->query("SELECT MAX(acl_topo_id) FROM acl_topology");
                $maxId = $DBRESULT->fetchRow();
                $DBRESULT->closeCursor();
                if (isset($maxId["MAX(acl_topo_id)"])) {
                    $maxTopoId = $maxId['MAX(acl_topo_id)'];

                    $query = "INSERT INTO acl_topology_relations (acl_topo_id, topology_topology_id, access_right) " .
                        "(SELECT $maxTopoId, topology_topology_id, access_right FROM acl_topology_relations " .
                        "WHERE acl_topo_id = " . $pearDB->escape($key) . ")";
                    $pearDB->query($query);

                    $query = "INSERT INTO acl_group_topology_relations (acl_topology_id, acl_group_id) " .
                        "(SELECT $maxTopoId, acl_group_id FROM acl_group_topology_relations WHERE acl_topology_id = " .
                        $pearDB->escape($key) . ")";
                    $pearDB->query($query);

                    $centreon->CentreonLogAction->insertLog(
                        "menu access",
                        $maxId["MAX(acl_topo_id)"],
                        $acl_name,
                        "a",
                        $fields
                    );
                }
            }
        }
    }
}

/**
 * @param null $acl_id
 */
function updateLCAInDB($acl_id = null)
{
    global $form, $centreon;
    if (!$acl_id) {
        return;
    }
    updateLCA($acl_id);
    updateLCATopology($acl_id);
    updateGroups($acl_id);
    $ret = $form->getSubmitValues();
    $fields = CentreonLogAction::prepareChanges($ret);
    $centreon->CentreonLogAction->insertLog("menu access", $acl_id, $ret['acl_topo_name'], "c", $fields);
}

/**
 * @return mixed
 */
function insertLCAInDB()
{
    global $form, $centreon;

    $acl_id = insertLCA();
    updateLCATopology($acl_id);
    updateGroups($acl_id);
    $ret = $form->getSubmitValues();
    $fields = CentreonLogAction::prepareChanges($ret);
    $centreon->CentreonLogAction->insertLog("menu access", $acl_id, $ret['acl_topo_name'], "a", $fields);

    return ($acl_id);
}

/**
 * @return mixed
 */
function insertLCA()
{
    global $form, $pearDB;
    $ret = array();
    $ret = $form->getSubmitValues();
    $rq = "INSERT INTO `acl_topology` (acl_topo_name, acl_topo_alias, acl_topo_activate, acl_comments) ";
    $rq .= "VALUES ('" . $pearDB->escape($ret["acl_topo_name"]) . "', '" .
        $pearDB->escape($ret["acl_topo_alias"]) . "', '" .
        $pearDB->escape($ret["acl_topo_activate"]["acl_topo_activate"]) . "', '" .
        $pearDB->escape($ret['acl_comments']) . "')";
    $pearDB->query($rq);
    $DBRESULT = $pearDB->query("SELECT MAX(acl_topo_id) FROM `acl_topology`");
    $acl = $DBRESULT->fetchRow();
    return ($acl["MAX(acl_topo_id)"]);
}

function updateLCA($acl_id = null)
{
    global $form, $pearDB;
    if (!$acl_id) {
        return;
    }
    $ret = array();
    $ret = $form->getSubmitValues();
    $rq = "UPDATE `acl_topology`
			   SET acl_topo_name = '" . $pearDB->escape($ret["acl_topo_name"]) . "',
			       acl_topo_alias = '" . $pearDB->escape($ret["acl_topo_alias"]) . "',
			       acl_topo_activate = '" . $pearDB->escape($ret["acl_topo_activate"]["acl_topo_activate"]) . "',
			       acl_comments = '" . $pearDB->escape($ret['acl_comments']) . "'
			   WHERE acl_topo_id = '" . $acl_id . "'";
    $pearDB->query($rq);
}

function updateLCATopology($acl_id = null)
{
    global $form, $pearDB;
    if (!$acl_id) {
        return;
    }
    $pearDB->query("DELETE FROM acl_topology_relations WHERE acl_topo_id = '" . $acl_id . "'");
    $ret = $form->getSubmitValue("acl_r_topos");
    foreach ($ret as $key => $value) {
        if (isset($ret) && $key != 0) {
            $query = "INSERT INTO acl_topology_relations (acl_topo_id, topology_topology_id, access_right) " .
                "VALUES ('" . $acl_id . "', '" . $key . "', " . $value . ")";
            $pearDB->query($query);
        }
    }
}

function updateGroups($acl_id = null)
{
    global $form, $pearDB;
    if (!$acl_id) {
        return;
    }
    $pearDB->query("DELETE FROM acl_group_topology_relations WHERE acl_topology_id = '" . $acl_id . "'");
    $ret = $form->getSubmitValue("acl_groups");
    if (isset($ret)) {
        foreach ($ret as $key => $value) {
            if (isset($value)) {
                $query = "INSERT INTO acl_group_topology_relations (acl_topology_id, acl_group_id) " .
                    "VALUES ('" . $acl_id . "', '" . $value . "')";
                $pearDB->query($query);
            }
        }
    }
}
