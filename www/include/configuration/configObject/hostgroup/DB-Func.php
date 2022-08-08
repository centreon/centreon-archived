<?php

/*
 * Copyright 2005-2020 Centreon
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

function getHGParents($hg_id, $parentList, $pearDB)
{
    /*
	 * Get Parent Groups
	 */
    $statement = $pearDB->prepre("SELECT hg_parent_id FROM hostgroup_hg_relation WHERE hg_child_id = :hg_child_id");
    $statement->bindValue(':hg_child_id', (int) $hg_id, \PDO::PARAM_INT);
    $statement->execute();
    while (($hgs = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
        $parentList[$hgs["hg_parent_id"]] = $hgs["hg_parent_id"];
        $parentList = getHGParents($hgs["hg_parent_id"], $parentList, $pearDB);
    }
    $statement->closeCursor();
    unset($hgs);
    return $parentList;
}

function testHostGroupExistence($name = null)
{
    global $pearDB, $form, $centreon;
    $id = null;
    if (isset($form)) {
        $id = $form->getSubmitValue('hg_id');
    }

    $query = "SELECT hg_name, hg_id FROM hostgroup WHERE hg_name = '" .
        CentreonDB::escape($centreon->checkIllegalChar($name)) . "'";
    $dbResult = $pearDB->query($query);
    $hg = $dbResult->fetch();
    #Modif case
    if ($dbResult->rowCount() >= 1 && $hg["hg_id"] == $id) {
        return true;
    } #Duplicate entry
    elseif ($dbResult->rowCount() >= 1 && $hg["hg_id"] != $id) {
        return false;
    } else {
        return true;
    }
}

function enableHostGroupInDB($hg_id = null, $hg_arr = array())
{
    global $pearDB, $centreon;

    if (!$hg_id && !count($hg_arr)) {
        return;
    }

    if ($hg_id) {
        $hg_arr = array($hg_id => "1");
    }

    foreach ($hg_arr as $key => $value) {
        $pearDB->query("UPDATE hostgroup SET hg_activate = '1' WHERE hg_id = '" . $key . "'");
        $dbResult2 = $pearDB->query("SELECT hg_name FROM `hostgroup` WHERE `hg_id` = '" . $key . "' LIMIT 1");
        $row = $dbResult2->fetch();
        $centreon->CentreonLogAction->insertLog("hostgroup", $key, $row['hg_name'], "enable");
    }
}

function disableHostGroupInDB($hg_id = null, $hg_arr = array())
{
    global $pearDB, $centreon;

    if (!$hg_id && !count($hg_arr)) {
        return;
    }
    if ($hg_id) {
        $hg_arr = array($hg_id => "1");
    }

    foreach ($hg_arr as $key => $value) {
        $pearDB->query("UPDATE hostgroup SET hg_activate = '0' WHERE hg_id = '" . $key . "'");
        $dbResult2 = $pearDB->query("SELECT hg_name FROM `hostgroup` WHERE `hg_id` = '" . $key . "' LIMIT 1");
        $row = $dbResult2->fetch();
        $centreon->CentreonLogAction->insertLog("hostgroup", $key, $row['hg_name'], "disable");
    }
}

/**
 * @param int $hgId
 */
function removeRelationLastHostgroupDependency(int $hgId): void
{
    global $pearDB;

    $query = 'SELECT count(dependency_dep_id) AS nb_dependency , dependency_dep_id AS id 
              FROM dependency_hostgroupParent_relation 
              WHERE dependency_dep_id = (SELECT dependency_dep_id FROM dependency_hostgroupParent_relation 
                                         WHERE hostgroup_hg_id =  ' . $hgId . ')';
    $dbResult = $pearDB->query($query);
    $result = $dbResult->fetch();

    //is last parent
    if ($result['nb_dependency'] == 1) {
        $pearDB->query("DELETE FROM dependency WHERE dep_id = " . $result['id']);
    }
}

function deleteHostGroupInDB($hostGroups = array())
{
    global $pearDB, $centreon;

    foreach ($hostGroups as $key => $value) {
        removeRelationLastHostgroupDependency((int)$key);
        $rq = "SELECT @nbr := (SELECT COUNT( * ) FROM host_service_relation WHERE service_service_id = " .
            "hsr.service_service_id GROUP BY service_service_id ) AS nbr, hsr.service_service_id FROM " .
            "host_service_relation hsr WHERE hsr.hostgroup_hg_id = '" . $key . "'";
        $dbResult = $pearDB->query($rq);

        $statement = $pearDB->prepare("DELETE FROM service WHERE service_id = :service_id");
        while ($row = $dbResult->fetch()) {
            if ($row["nbr"] == 1) {
                $statement->bindValue(':service_id', (int) $row["service_service_id"], \PDO::PARAM_INT);
                $statement->execute();
            }
        }
        $dbResult3 = $pearDB->query("SELECT hg_name FROM `hostgroup` WHERE `hg_id` = '" . $key . "' LIMIT 1");
        $row = $dbResult3->fetch();

        $pearDB->query("DELETE FROM hostgroup WHERE hg_id = '" . $key . "'");
        $centreon->CentreonLogAction->insertLog("hostgroup", $key, $row['hg_name'], "d");
    }
    $centreon->user->access->updateACL();
}

function multipleHostGroupInDB($hostGroups = array(), $nbrDup = array())
{
    global $pearDB, $centreon, $is_admin;

    $hgAcl = array();
    foreach ($hostGroups as $key => $value) {
        $dbResult = $pearDB->query("SELECT * FROM hostgroup WHERE hg_id = '" . $key . "' LIMIT 1");
        $row = $dbResult->fetch();
        $row["hg_id"] = null;
        for ($i = 1; $i <= $nbrDup[$key]; $i++) {
            $val = null;
            $rq = null;
            foreach ($row as $key2 => $value2) {
                $key2 == "hg_name" ? ($hg_name = $value2 = $value2 . "_" . $i) : null;
                $val
                    ? $val .= ($value2 != null ? (", '" . $pearDB->escape($value2) . "'") : ", NULL")
                    : $val .= ($value2 != null ? ("'" . $pearDB->escape($value2) . "'") : "NULL");
                if ($key2 != "hg_id") {
                    $fields[$key2] = $value2;
                }
                if (isset($fields["hg_name"])) {
                    $fields["hg_name"] = $hg_name;
                }
            }
            if (testHostGroupExistence($hg_name)) {
                $val ? $rq = "INSERT INTO hostgroup VALUES (" . $val . ")" : $rq = null;
                $pearDB->query($rq);
                $dbResult = $pearDB->query("SELECT MAX(hg_id) FROM hostgroup");
                $maxId = $dbResult->fetch();
                if (isset($maxId["MAX(hg_id)"])) {
                    $hgAcl[$maxId["MAX(hg_id)"]] = $key;
                    if (!$is_admin) {
                        $resource_list = $centreon->user->access->getResourceGroups();
                        if (count($resource_list)) {
                            $query = "INSERT INTO `acl_resources_hg_relations` (acl_res_id, hg_hg_id) 
                                    VALUES (:acl_res_id, :hg_hg_id)";
                            $statement = $pearDB->prepare($query);
                            foreach ($resource_list as $res_id => $res_name) {
                                $statement->bindValue(':acl_res_id', (int) $res_id, \PDO::PARAM_INT);
                                $statement->bindValue(':hg_hg_id', (int) $maxId["MAX(hg_id)"], \PDO::PARAM_INT);
                                $statement->execute();
                            }
                            unset($resource_list);
                        }
                    }

                    $query = "SELECT DISTINCT hgr.host_host_id FROM hostgroup_relation hgr " .
                        "WHERE hgr.hostgroup_hg_id = '" . $key . "'";
                    $dbResult = $pearDB->query($query);
                    $fields["hg_hosts"] = "";
                    $query = "INSERT INTO hostgroup_relation VALUES (NULL, :hg_id, :host_host_id)";
                    $statement = $pearDB->prepare($query);
                    while ($host = $dbResult->fetch()) {
                        $statement->bindValue(':hg_id', (int) $maxId["MAX(hg_id)"], \PDO::PARAM_INT);
                        $statement->bindValue(':host_host_id', (int) $host["host_host_id"], \PDO::PARAM_INT);
                        $statement->execute();
                        $fields["hg_hosts"] .= $host["host_host_id"] . ",";
                    }
                    $fields["hg_hosts"] = trim($fields["hg_hosts"], ",");
                    $query = "SELECT DISTINCT cghgr.contactgroup_cg_id FROM contactgroup_hostgroup_relation cghgr " .
                        "WHERE cghgr.hostgroup_hg_id = '" . $key . "'";
                    $dbResult = $pearDB->query($query);
                    $query = "INSERT INTO contactgroup_hostgroup_relation 
                        VALUES (NULL, :contactgroup_cg_id, :hostgroup_hg_id)";
                    $statement = $pearDB->prepare($query);
                    while ($cg = $dbResult->fetch()) {
                        $statement->bindValue(':contactgroup_cg_id', (int) $cg["contactgroup_cg_id"], \PDO::PARAM_INT);
                        $statement->bindValue(':hostgroup_hg_id', (int) $maxId["MAX(hg_id)"], \PDO::PARAM_INT);
                        $statement->execute();
                    }
                    $centreon->CentreonLogAction->insertLog("hostgroup", $maxId["MAX(hg_id)"], $hg_name, "a", $fields);
                }
            }
        }
    }
    CentreonACL::duplicateHgAcl($hgAcl);
    $centreon->user->access->updateACL();
}

function insertHostGroupInDB($ret = array())
{
    global $centreon;

    $hg_id = insertHostGroup($ret);
    updateHostGroupHosts($hg_id, $ret);
    $centreon->user->access->updateACL();
    return $hg_id;
}

function updateHostGroupInDB($hg_id = null, $ret = array(), $increment = false)
{
    global $centreon;
    if (!$hg_id) {
        return;
    }
    updateHostGroup($hg_id, $ret);
    updateHostGroupHosts($hg_id, $ret, $increment);
    $centreon->user->access->updateACL();
}

function insertHostGroup($ret = array())
{
    global $form, $pearDB, $centreon, $is_admin;

    if (!count($ret)) {
        $ret = $form->getSubmitValues();
    }

    $ret["hg_name"] = $centreon->checkIllegalChar($ret["hg_name"]);

    $rq = "INSERT INTO hostgroup ";
    $rq .= "(hg_name, hg_alias, hg_notes, hg_notes_url, hg_action_url, hg_icon_image, hg_map_icon_image, " .
        "hg_rrd_retention, hg_comment, geo_coords, hg_activate) ";
    $rq .= "VALUES (";
    isset($ret["hg_name"]) && $ret["hg_name"]
        ? $rq .= "'" . $pearDB->escape($ret["hg_name"]) . "', "
        : $rq .= "NULL,";
    isset($ret["hg_alias"]) && $ret["hg_alias"]
        ? $rq .= "'" . $pearDB->escape($ret["hg_alias"]) . "', "
        : $rq .= "NULL,";
    isset($ret["hg_notes"]) && $ret["hg_notes"]
        ? $rq .= "'" . $pearDB->escape($ret["hg_notes"]) . "', "
        : $rq .= "NULL,";
    isset($ret["hg_notes_url"]) && $ret["hg_notes_url"]
        ? $rq .= "'" . $pearDB->escape($ret["hg_notes_url"]) . "', "
        : $rq .= "NULL,";
    isset($ret["hg_action_url"]) && $ret["hg_action_url"]
        ? $rq .= "'" . $pearDB->escape($ret["hg_action_url"]) . "', "
        : $rq .= "NULL,";
    isset($ret["hg_icon_image"]) && $ret["hg_icon_image"]
        ? $rq .= "'" . $pearDB->escape($ret["hg_icon_image"]) . "', "
        : $rq .= "NULL,";
    isset($ret["hg_map_icon_image"]) && $ret["hg_map_icon_image"]
        ? $rq .= "'" . $pearDB->escape($ret["hg_map_icon_image"]) . "', "
        : $rq .= "NULL,";
    isset($ret["hg_rrd_retention"]) && $ret["hg_rrd_retention"]
        ? $rq .= "'" . $pearDB->escape($ret["hg_rrd_retention"]) . "', "
        : $rq .= "NULL,";
    isset($ret["hg_comment"]) && $ret["hg_comment"]
        ? $rq .= "'" . $pearDB->escape($ret["hg_comment"]) . "', "
        : $rq .= "NULL, ";
    isset($ret["geo_coords"]) && $ret["geo_coords"]
        ? $rq .= "'" . $pearDB->escape($ret["geo_coords"]) . "', "
        : $rq .= "NULL, ";
    isset($ret["hg_activate"]["hg_activate"]) && $ret["hg_activate"]["hg_activate"]
        ? $rq .= "'" . $ret["hg_activate"]["hg_activate"] . "'"
        : $rq .= "'0'";
    $rq .= ")";

    $pearDB->query($rq);
    $dbResult = $pearDB->query("SELECT MAX(hg_id) FROM hostgroup");
    $hg_id = $dbResult->fetch();

    if (!$centreon->user->admin) {
        $resource_list = $centreon->user->access->getResourceGroups();
        if (count($resource_list)) {
            $query = "INSERT INTO `acl_resources_hg_relations` (acl_res_id, hg_hg_id) 
                VALUES (:acl_res_id, :hg_hg_id)";
            $statement = $pearDB->prepare($query);
            foreach ($resource_list as $res_id => $res_name) {
                $statement->bindValue(':acl_res_id', (int) $res_id, \PDO::PARAM_INT);
                $statement->bindValue(':hg_hg_id', (int) $hg_id["MAX(hg_id)"], \PDO::PARAM_INT);
                $statement->execute();
            }
            unset($resource_list);
        }
    }

    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($ret);
    $centreon->CentreonLogAction->insertLog(
        "hostgroup",
        $hg_id["MAX(hg_id)"],
        CentreonDB::escape($ret["hg_name"]),
        "a",
        $fields
    );

    return ($hg_id["MAX(hg_id)"]);
}

function updateHostGroup($hg_id, $ret = array())
{
    global $form, $pearDB, $centreon;

    if (!$hg_id) {
        return;
    }

    if (!count($ret)) {
        $ret = $form->getSubmitValues();
    }

    $ret["hg_name"] = $centreon->checkIllegalChar($ret["hg_name"]);

    $rq = "UPDATE hostgroup SET ";
    $rq .= "hg_name = ";
    isset($ret["hg_name"]) && $ret["hg_name"] != null
        ? $rq .= "'" . $pearDB->escape($ret["hg_name"]) . "', "
        : $rq .= "NULL, ";
    $rq .= "hg_alias = ";
    isset($ret["hg_alias"]) && $ret["hg_alias"] != null
        ? $rq .= "'" . $pearDB->escape($ret["hg_alias"]) . "', "
        : $rq .= "NULL, ";
    $rq .= "hg_notes = ";
    isset($ret["hg_notes"]) && $ret["hg_notes"] != null
        ? $rq .= "'" . $pearDB->escape($ret["hg_notes"]) . "', "
        : $rq .= "NULL, ";
    $rq .= "hg_notes_url = ";
    isset($ret["hg_notes_url"]) && $ret["hg_notes_url"] != null
        ? $rq .= "'" . $pearDB->escape($ret["hg_notes_url"]) . "', "
        : $rq .= "NULL, ";
    $rq .= "hg_action_url = ";
    isset($ret["hg_action_url"]) && $ret["hg_action_url"] != null
        ? $rq .= "'" . $pearDB->escape($ret["hg_action_url"]) . "', "
        : $rq .= "NULL, ";
    $rq .= "hg_icon_image = ";
    isset($ret["hg_icon_image"]) && $ret["hg_icon_image"] != null
        ? $rq .= "'" . $pearDB->escape($ret["hg_icon_image"]) . "', "
        : $rq .= "NULL, ";
    $rq .= "hg_map_icon_image = ";
    isset($ret["hg_map_icon_image"]) && $ret["hg_map_icon_image"] != null
        ? $rq .= "'" . $pearDB->escape($ret["hg_map_icon_image"]) . "', "
        : $rq .= "NULL, ";
    $rq .= "hg_rrd_retention = ";
    $rq .= isset($ret["hg_rrd_retention"]) && $ret["hg_rrd_retention"]
        ? "'" . $pearDB->escape($ret["hg_rrd_retention"]) . "', "
        : "NULL, ";
    $rq .= "geo_coords = ";
    isset($ret["geo_coords"]) && $ret["geo_coords"] != null
        ? $rq .= "'" . $pearDB->escape($ret["geo_coords"]) . "', "
        : $rq .= "NULL, ";
    $rq .= "hg_comment = ";
    isset($ret["hg_comment"]) && $ret["hg_comment"] != null
        ? $rq .= "'" . $pearDB->escape($ret["hg_comment"]) . "', "
        : $rq .= "NULL, ";
    $rq .= "hg_activate = ";
    isset($ret["hg_activate"]["hg_activate"]) && $ret["hg_activate"]["hg_activate"] != null
        ? $rq .= "'" . $ret["hg_activate"]["hg_activate"] . "'"
        : $rq .= "NULL ";
    $rq .= " WHERE hg_id = '" . $hg_id . "'";
    $dbResult = $pearDB->query($rq);

    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($ret);
    $centreon->CentreonLogAction->insertLog("hostgroup", $hg_id, $pearDB->escape($ret["hg_name"]), "c", $fields);
}

function updateHostGroupHosts($hg_id, $ret = array(), $increment = false)
{
    global $form, $pearDB;

    if (!$hg_id) {
        return;
    }

    /*
	 * Special Case, delete relation between host/service, when service
	 * is linked to hostgroup in escalation, dependencies
	 *
	 * Get initial Host list to make a diff after deletion
	 */
    $hostsOLD = array();
    $statement = $pearDB->prepare("SELECT host_host_id FROM hostgroup_relation 
        WHERE hostgroup_hg_id = :hostgroup_hg_id");
    $statement->bindValue(':hostgroup_hg_id', (int) $hg_id, \PDO::PARAM_INT);
    $statement->execute();
    while (($host = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
        $hostsOLD[$host["host_host_id"]] = $host["host_host_id"];
    }
    $statement->closeCursor();

    /*
	 * Get service lists linked to hostgroup
	 */
    $rq = "SELECT service_service_id FROM host_service_relation ";
    $rq .= "WHERE hostgroup_hg_id = '" . $hg_id . "' AND host_host_id IS NULL";
    $dbResult = $pearDB->query($rq);
    $hgSVS = array();
    while ($sv = $dbResult->fetch()) {
        $hgSVS[$sv["service_service_id"]] = $sv["service_service_id"];
    }

    /*
	 * Update Host HG relations
	 */
    if ($increment == false) {
        $rq = "DELETE FROM hostgroup_relation ";
        $rq .= "WHERE hostgroup_hg_id = '" . $hg_id . "'";
        $pearDB->query($rq);
    }

    $ret = isset($ret["hg_hosts"]) ? $ret["hg_hosts"] : CentreonUtils::mergeWithInitialValues($form, 'hg_hosts');

    $hgNEW = array();

    $rq = "INSERT INTO hostgroup_relation (hostgroup_hg_id, host_host_id) VALUES ";
    $query = "SELECT hostgroup_hg_id FROM hostgroup_relation WHERE hostgroup_hg_id = :hostgroup_hg_id
        AND host_host_id = :host_host_id";
    $statement = $pearDB->prepare($query);
    for ($i = 0; $i < count($ret); $i++) {
        $statement->bindValue(':hostgroup_hg_id', (int) $hg_id, \PDO::PARAM_INT);
        $statement->bindValue(':host_host_id', (int) $ret[$i], \PDO::PARAM_INT);
        $statement->execute();
        if (!$statement->rowCount()) {
            if ($i != 0) {
                $rq .= ", ";
            }
            $rq .= " ('" . $hg_id . "', '" . $ret[$i] . "')";
            $hostsNEW[$ret[$i]] = $ret[$i];
        }
    }

    if ($i != 0) {
        $dbResult = $pearDB->query($rq);
    }

    /*
	 * Update HG HG relations
	 */
    if ($increment == false) {
        $statement = $pearDB->prepare("DELETE FROM hostgroup_hg_relation WHERE hg_parent_id = :hg_parent_id");
        $statement->bindValue(':hg_parent_id', (int) $hg_id, \PDO::PARAM_INT);
        $statement->execute();
    }
    isset($ret["hg_hg"]) ? $ret = $ret["hg_hg"] : $ret = $form->getSubmitValue("hg_hg");
    $hgNEW = array();

    $rq = "INSERT INTO hostgroup_hg_relation (hg_parent_id, hg_child_id) VALUES ";
    $loopCount = (is_array($ret) || $ret instanceof Countable) ? count($ret) : 0;

    $query = "SELECT hg_parent_id FROM hostgroup_hg_relation WHERE hg_parent_id = :hg_parent_id
            AND hg_child_id = :hg_child_id";
    $statement = $pearDB->prepare($query);
    for ($i = 0; $i < $loopCount; $i++) {
        $statement->bindValue(':hg_parent_id', (int) $hg_id, \PDO::PARAM_INT);
        $statement->bindValue(':hg_child_id', (int) $ret[$i], \PDO::PARAM_INT);
        $statement->execute();
        if (!$statement->rowCount()) {
            if ($i != 0) {
                $rq .= ", ";
            }
        }
    }
    if ($i != 0) {
        $pearDB->query($rq);
    }

    /*
     * Remove relations that no longer exist (for services by hostgroup)
     */
    $svcObj = new CentreonService($pearDB);
    $svcObj->cleanServiceRelations("escalation_service_relation", "host_host_id", "service_service_id");
    $svcObj->cleanServiceRelations("dependency_serviceChild_relation", "host_host_id", "service_service_id");
    $svcObj->cleanServiceRelations("dependency_serviceParent_relation", "host_host_id", "service_service_id");
    $svcObj->cleanServiceRelations("downtime_service_relation", "host_host_id", "service_service_id");
}
