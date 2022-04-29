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

/**
 * @param null $name
 * @return bool
 */
function testExistence($name = null)
{
    global $pearDB, $form;
    $id = null;

    if (isset($form)) {
        $id = $form->getSubmitValue('acl_res_id');
    }
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $statement = $pearDB->prepare("SELECT acl_res_name, acl_res_id FROM `acl_resources` WHERE acl_res_name = :name");
    $statement->bindValue(':name', $name, \PDO::PARAM_STR);
    $statement->execute();
    if (($lca = $statement->fetch()) && $lca["acl_res_id"] != $id) {
        return false;
    }
    return true;
}

/**
 * @param null $aclResId
 * @param array $acls
 */
function enableLCAInDB($aclResId = null, $acls = array())
{
    global $pearDB, $centreon;

    if (!$aclResId && !count($acls)) {
        return;
    }
    if ($aclResId) {
        $acls = array($aclResId => "1");
    }

    foreach ($acls as $key => $value) {
        $query = "UPDATE `acl_groups` SET `acl_group_changed` = '1' " .
            "WHERE acl_group_id IN (SELECT acl_group_id FROM acl_res_group_relations WHERE acl_res_id = '$key')";
        $pearDB->query($query);
        $query = "UPDATE `acl_resources` SET acl_res_activate = '1', `changed` = '1' " .
            "WHERE `acl_res_id` = '" . $key . "'";
        $pearDB->query($query);
        $query = "SELECT acl_res_name FROM `acl_resources` WHERE acl_res_id = '" . (int)$key . "' LIMIT 1";
        $dbResult = $pearDB->query($query);
        $row = $dbResult->fetch();
        $centreon->CentreonLogAction->insertLog("resource access", $key, $row['acl_res_name'], "enable");
    }
}

/**
 * @param null $aclResId
 * @param array $acls
 */
function disableLCAInDB($aclResId = null, $acls = array())
{
    global $pearDB, $centreon;

    if (!$aclResId && !count($acls)) {
        return;
    }

    if ($aclResId) {
        $acls = array($aclResId => "1");
    }

    foreach ($acls as $key => $value) {
        $query = "UPDATE `acl_groups` SET `acl_group_changed` = '1' " .
            "WHERE acl_group_id IN (SELECT acl_group_id FROM acl_res_group_relations WHERE acl_res_id = '$key')";
        $pearDB->query($query);
        $query = "UPDATE `acl_resources` SET acl_res_activate = '0', `changed` = '1' " .
            "WHERE `acl_res_id` = '" . $key . "'";
        $pearDB->query($query);
        $query = "SELECT acl_res_name FROM `acl_resources` WHERE acl_res_id = '" . (int)$key . "' LIMIT 1";
        $dbResult = $pearDB->query($query);
        $row = $dbResult->fetch();
        $centreon->CentreonLogAction->insertLog("resource access", $key, $row['acl_res_name'], "disable");
    }
}

/**
 *
 * Delete ACL entry in DB
 * @param $acls
 */
function deleteLCAInDB($acls = array())
{
    global $pearDB, $centreon;

    foreach ($acls as $key => $value) {
        $query = "SELECT acl_res_name FROM `acl_resources` WHERE acl_res_id = '" . (int)$key . "' LIMIT 1";
        $dbResult = $pearDB->query($query);
        $row = $dbResult->fetch();
        $query = "UPDATE `acl_groups` SET `acl_group_changed` = '1' " .
            "WHERE acl_group_id IN (SELECT acl_group_id FROM acl_res_group_relations WHERE acl_res_id = '$key')";
        $pearDB->query($query);

        $pearDB->query("DELETE FROM `acl_resources` WHERE acl_res_id = '" . $key . "'");
        $centreon->CentreonLogAction->insertLog("resource access", $key, $row['acl_res_name'], "d");
    }
}

/**
 *
 * Duplicate Resources ACL
 * @param $lcas
 * @param $nbrDup
 */
function multipleLCAInDB($lcas = array(), $nbrDup = array())
{
    global $pearDB, $centreon;

    foreach ($lcas as $key => $value) {
        $dbResult = $pearDB->query("SELECT * FROM `acl_resources` WHERE acl_res_id = '" . $key . "' LIMIT 1");
        $row = $dbResult->fetch();
        $row["acl_res_id"] = '';

        for ($i = 1; $i <= $nbrDup[$key]; $i++) {
            $val = null;
            foreach ($row as $key2 => $value2) {
                $key2 == "acl_res_name" ? ($acl_name = $value2 = $value2 . "_" . $i) : null;
                $val ? $val .= ($value2 != null ? (", '" . $value2 . "'") : ", NULL")
                    : $val .= ($value2 != null ? ("'" . $value2 . "'") : "NULL");
                if ($key2 != "acl_res_id") {
                    $fields[$key2] = $value2;
                }
                if (isset($acl_res_name)) {
                    $fields["acl_res_name"] = $acl_res_name;
                }
            }

            if (testExistence($acl_name)) {
                $val ? $rq = "INSERT INTO acl_resources VALUES (" . $val . ")" : $rq = null;
                $pearDB->query($rq);

                $dbResult = $pearDB->query("SELECT MAX(acl_res_id) FROM acl_resources");
                $maxId = $dbResult->fetch();
                $dbResult->closeCursor();

                if (isset($maxId["MAX(acl_res_id)"])) {
                    duplicateGroups($key, $maxId["MAX(acl_res_id)"], $pearDB);
                    $centreon->CentreonLogAction->insertLog(
                        "resource access",
                        $maxId["MAX(acl_res_id)"],
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
 * @param $idTD
 * @param $acl_id
 * @param $pearDB
 */
function duplicateGroups($idTD, $acl_id, $pearDB)
{
    $query = "INSERT INTO acl_res_group_relations (acl_res_id, acl_group_id) " .
        "SELECT :acl_id AS acl_res_id, acl_group_id FROM acl_res_group_relations WHERE acl_res_id = :acl_res_id";
    $statement = $pearDB->prepare($query);
    $statement->bindValue(':acl_id', (int)$acl_id, \PDO::PARAM_INT);
    $statement->bindValue(':acl_res_id', (int)$idTD, \PDO::PARAM_INT);
    $statement->execute();
    //host categories
    $query = "INSERT INTO acl_resources_hc_relations (acl_res_id, hc_id) " .
        "(SELECT :acl_id, hc_id FROM acl_resources_hc_relations WHERE acl_res_id = :acl_res_id)";
    $statement = $pearDB->prepare($query);
    $statement->bindValue(':acl_id', (int)$acl_id, \PDO::PARAM_INT);
    $statement->bindValue(':acl_res_id', (int)$idTD, \PDO::PARAM_INT);
    $statement->execute();
    //hostgroups
    $query = "INSERT INTO acl_resources_hg_relations (acl_res_id, hg_hg_id) " .
        "(SELECT :acl_id, hg_hg_id FROM acl_resources_hg_relations WHERE acl_res_id = :acl_res_id)";
    $statement = $pearDB->prepare($query);
    $statement->bindValue(':acl_id', (int)$acl_id, \PDO::PARAM_INT);
    $statement->bindValue(':acl_res_id', (int)$idTD, \PDO::PARAM_INT);
    $statement->execute();

    //host exceptions
    $query = "INSERT INTO acl_resources_hostex_relations (acl_res_id, host_host_id) " .
        "(SELECT :acl_id, host_host_id FROM acl_resources_hostex_relations WHERE acl_res_id = :acl_res_id)";
    $statement = $pearDB->prepare($query);
    $statement->bindValue(':acl_id', (int)$acl_id, \PDO::PARAM_INT);
    $statement->bindValue(':acl_res_id', (int)$idTD, \PDO::PARAM_INT);
    $statement->execute();

    //hosts
    $query = "INSERT INTO acl_resources_host_relations (acl_res_id, host_host_id) " .
        "(SELECT :acl_id, host_host_id FROM acl_resources_host_relations WHERE acl_res_id = :acl_res_id)";
    $statement = $pearDB->prepare($query);
    $statement->bindValue(':acl_id', (int)$acl_id, \PDO::PARAM_INT);
    $statement->bindValue(':acl_res_id', (int)$idTD, \PDO::PARAM_INT);
    $statement->execute();

    //meta
    $query = "INSERT INTO acl_resources_meta_relations (acl_res_id, meta_id) " .
        "(SELECT :acl_id, meta_id FROM acl_resources_meta_relations WHERE acl_res_id = :acl_res_id)";
    $statement = $pearDB->prepare($query);
    $statement->bindValue(':acl_id', (int)$acl_id, \PDO::PARAM_INT);
    $statement->bindValue(':acl_res_id', (int)$idTD, \PDO::PARAM_INT);
    $statement->execute();

    //poller
    $query = "INSERT INTO acl_resources_poller_relations (acl_res_id, poller_id) " .
        "(SELECT :acl_id, poller_id FROM acl_resources_poller_relations WHERE acl_res_id = :acl_res_id)";
    $statement = $pearDB->prepare($query);
    $statement->bindValue(':acl_id', (int)$acl_id, \PDO::PARAM_INT);
    $statement->bindValue(':acl_res_id', (int)$idTD, \PDO::PARAM_INT);
    $statement->execute();

    //service categories
    $query = "INSERT INTO acl_resources_sc_relations (acl_res_id, sc_id) " .
        "(SELECT :acl_id, sc_id FROM acl_resources_sc_relations WHERE acl_res_id = :acl_res_id)";
    $statement = $pearDB->prepare($query);
    $statement->bindValue(':acl_id', (int)$acl_id, \PDO::PARAM_INT);
    $statement->bindValue(':acl_res_id', (int)$idTD, \PDO::PARAM_INT);
    $statement->execute();

    //service groups
    $query = "INSERT INTO acl_resources_sg_relations (acl_res_id, sg_id) " .
        "(SELECT :acl_id, sg_id FROM acl_resources_sg_relations WHERE acl_res_id = :acl_res_id)";
    $statement = $pearDB->prepare($query);
    $statement->bindValue(':acl_id', (int)$acl_id, \PDO::PARAM_INT);
    $statement->bindValue(':acl_res_id', (int)$idTD, \PDO::PARAM_INT);
    $statement->execute();
}

/**
 * @param $idTD
 * @param $acl_id
 * @param $pearDB
 */
function duplicateContactGroups($idTD, $acl_id, $pearDB)
{
    $query = "INSERT INTO acl_res_group_relations (acl_res_id, acl_group_id) " .
        "SELECT acl_res_id, '$acl_id' AS acl_group_id FROM acl_res_group_relations WHERE acl_group_id = '$idTD'";
    $pearDB->query($query);
}

/**
 *
 * Update ACL entry
 * @param $acl_id
 */
function updateLCAInDB($acl_id = null)
{
    global $form, $centreon;

    if (!$acl_id) {
        return;
    }

    updateLCA($acl_id);
    updateGroups($acl_id);
    updateHosts($acl_id);
    updateHostGroups($acl_id);
    updateHostexcludes($acl_id);
    updateServiceCategories($acl_id);
    updateHostCategories($acl_id);
    updateServiceGroups($acl_id);
    updateMetaServices($acl_id);
    updatePollers($acl_id);

    $ret = $form->getSubmitValues();
    $fields = CentreonLogAction::prepareChanges($ret);
    $centreon->CentreonLogAction->insertLog("resource access", $acl_id, $ret['acl_res_name'], "c", $fields);
}

/**
 *
 * Insert ACL entry
 */
function insertLCAInDB()
{
    global $form, $centreon;

    $acl_id = insertLCA();
    updateGroups($acl_id);
    updateHosts($acl_id);
    updateHostGroups($acl_id);
    updateHostexcludes($acl_id);
    updateServiceCategories($acl_id);
    updateHostCategories($acl_id);
    updateServiceGroups($acl_id);
    updateMetaServices($acl_id);
    updatePollers($acl_id);

    $ret = $form->getSubmitValues();
    $fields = CentreonLogAction::prepareChanges($ret);
    $centreon->CentreonLogAction->insertLog("resource access", $acl_id, $ret['acl_res_name'], "a", $fields);

    return ($acl_id);
}


/**
 *
 * Insert LCA in DB
 */
function insertLCA()
{
    global $form, $pearDB;

    $ret = [];
    $ret = $form->getSubmitValues();

    $resourceValues = sanitizeResourceParameters($ret);

    $statement = $pearDB->prepare(
        "INSERT INTO `acl_resources`
        (acl_res_name, acl_res_alias, all_hosts, all_hostgroups, all_servicegroups, acl_res_activate,
        changed, acl_res_comment)
        VALUES (:aclResourceName, :aclResourceAlias, :allHosts, :allHostGroups, :allServiceGroups, :aclResourceActivate,
        1, :aclResourceComment)"
    );
    $statement->bindValue(':aclResourceName', $resourceValues['acl_res_name'], \PDO::PARAM_STR);
    $statement->bindValue(':aclResourceAlias', $resourceValues['acl_res_alias'], \PDO::PARAM_STR);
    $statement->bindValue(':allHosts', $resourceValues['all_hosts'], \PDO::PARAM_STR);
    $statement->bindValue(':allHostGroups', $resourceValues['all_hostgroups'], \PDO::PARAM_STR);
    $statement->bindValue(':allServiceGroups', $resourceValues['all_servicegroups'], \PDO::PARAM_STR);
    $statement->bindValue(':aclResourceActivate', $resourceValues['acl_res_activate'], \PDO::PARAM_STR);
    $statement->bindValue(':aclResourceComment', $resourceValues['acl_res_comment'], \PDO::PARAM_STR);
    $statement->execute();
    $dbResult = $pearDB->query("SELECT MAX(acl_res_id) FROM `acl_resources`");
    $acl = $dbResult->fetch();

    return ($acl["MAX(acl_res_id)"]);
}

/**
 *
 * Update resource ACL in DB
 * @param int|null $aclId
 */
function updateLCA(?int $aclId = null): void
{
    global $form, $pearDB;

    if (!$aclId) {
        return;
    }

    $ret = [];
    $ret = $form->getSubmitValues();

    $resourceValues = sanitizeResourceParameters($ret);

    $statement = $pearDB->prepare(
        "UPDATE `acl_resources`
        SET acl_res_name = :aclResourceName,
        acl_res_alias = :aclResourceAlias,
        all_hosts = :allHosts,
        all_hostgroups = :allHostGroups,
        all_servicegroups = :allServiceGroups,
        acl_res_activate = :aclResourceActivate,
        acl_res_comment = :aclResourceComment,
        changed = 1
        WHERE acl_res_id = :aclId
        "
    );
    $statement->bindValue(':aclResourceName', $resourceValues['acl_res_name'], \PDO::PARAM_STR);
    $statement->bindValue(':aclResourceAlias', $resourceValues['acl_res_alias'], \PDO::PARAM_STR);
    $statement->bindValue(':allHosts', $resourceValues['all_hosts'], \PDO::PARAM_STR);
    $statement->bindValue(':allHostGroups', $resourceValues['all_hostgroups'], \PDO::PARAM_STR);
    $statement->bindValue(':allServiceGroups', $resourceValues['all_servicegroups'], \PDO::PARAM_STR);
    $statement->bindValue(':aclResourceActivate', $resourceValues['acl_res_activate'], \PDO::PARAM_STR);
    $statement->bindValue(':aclResourceComment', $resourceValues['acl_res_comment'], \PDO::PARAM_STR);
    $statement->bindValue(':aclId', $aclId, \PDO::PARAM_INT);
    $statement->execute();
}

/** ****************
 *
 * @param $acl_id
 * @return unknown_type
 */
function updateGroups($acl_id = null)
{
    global $form, $pearDB;

    if (!$acl_id) {
        return;
    }

    $statement = $pearDB->prepare("DELETE FROM acl_res_group_relations WHERE acl_res_id = :acl_res_id");
    $statement->bindValue(':acl_res_id', (int)$acl_id, \PDO::PARAM_INT);
    $statement->execute();
    $ret = array();
    $ret = $form->getSubmitValue("acl_groups");
    if (isset($ret)) {
        foreach ($ret as $key => $value) {
            if (isset($value)) {
                $query = "INSERT INTO acl_res_group_relations (acl_res_id, acl_group_id) VALUES (:acl_res_id, 
                                                                       :acl_group_id)";
                $statement = $pearDB->prepare($query);
                $statement->bindValue(':acl_res_id', (int)$acl_id, \PDO::PARAM_INT);
                $statement->bindValue(':acl_group_id', (int)$value, \PDO::PARAM_INT);
                $statement->execute();
            }
        }
    }
}

/** ******************
 *
 * @param $acl_id
 * @return unknown_type
 */
function updateHosts($acl_id = null)
{
    global $form, $pearDB;

    if (!$acl_id) {
        return;
    }

    $statement = $pearDB->prepare("DELETE FROM acl_resources_host_relations WHERE acl_res_id = :acl_res_id");
    $statement->bindValue(':acl_res_id', (int)$acl_id, \PDO::PARAM_INT);
    $statement->execute();
    $ret = array();
    $ret = $form->getSubmitValue("acl_hosts");
    if (isset($ret)) {
        foreach ($ret as $key => $value) {
            if (isset($value)) {
                $query = "INSERT INTO acl_resources_host_relations (acl_res_id, host_host_id) VALUES (:acl_res_id, 
                                                                            :host_host_id)";
                $statement = $pearDB->prepare($query);
                $statement->bindValue(':acl_res_id', (int)$acl_id, \PDO::PARAM_INT);
                $statement->bindValue(':host_host_id', (int)$value, \PDO::PARAM_INT);
                $statement->execute();
            }
        }
    }
}

/** ******************
 *
 * @param $acl_id
 * @return unknown_type
 */
function updatePollers($acl_id = null)
{
    global $form, $pearDB;

    if (!$acl_id) {
        return;
    }

    $statement = $pearDB->prepare("DELETE FROM acl_resources_poller_relations WHERE acl_res_id = :acl_res_id");
    $statement->bindValue(':acl_res_id', (int)$acl_id, \PDO::PARAM_INT);
    $statement->execute();
    $ret = array();
    $ret = $form->getSubmitValue("acl_pollers");
    if (isset($ret)) {
        foreach ($ret as $key => $value) {
            if (isset($value)) {
                $query = "INSERT INTO acl_resources_poller_relations (acl_res_id, poller_id) VALUES (:acl_res_id, 
                                                                           :poller_id)";
                $statement = $pearDB->prepare($query);
                $statement->bindValue(':acl_res_id', (int)$acl_id, \PDO::PARAM_INT);
                $statement->bindValue(':poller_id', (int)$value, \PDO::PARAM_INT);
                $statement->execute();
            }
        }
    }
}

/** ********************
 *
 * @param $acl_id
 * @return unknown_type
 */
function updateHostexcludes($acl_id = null)
{
    global $form, $pearDB;

    if (!$acl_id) {
        return;
    }

    $statement = $pearDB->prepare("DELETE FROM acl_resources_hostex_relations WHERE acl_res_id = :acl_res_id");
    $statement->bindValue(':acl_res_id', (int)$acl_id, \PDO::PARAM_INT);
    $statement->execute();
    $ret = array();
    $ret = $form->getSubmitValue("acl_hostexclude");
    if (isset($ret)) {
        foreach ($ret as $key => $value) {
            if (isset($value)) {
                $query = "INSERT INTO acl_resources_hostex_relations (acl_res_id, host_host_id) VALUES (:acl_res_id, 
                                                                              :host_host_id)";
                $statement = $pearDB->prepare($query);
                $statement->bindValue(':acl_res_id', (int)$acl_id, \PDO::PARAM_INT);
                $statement->bindValue(':host_host_id', (int)$value, \PDO::PARAM_INT);
                $statement->execute();
            }
        }
    }
}

/**
 *
 * Update hostgroups entry in DB
 * @param $acl_id
 */
function updateHostGroups($acl_id = null)
{
    global $form, $pearDB;

    if (!$acl_id) {
        return;
    }

    $statement = $pearDB->prepare("DELETE FROM acl_resources_hg_relations WHERE acl_res_id = :acl_res_id");
    $statement->bindValue(':acl_res_id', (int)$acl_id, \PDO::PARAM_INT);
    $statement->execute();
    $ret = array();
    $ret = $form->getSubmitValue("acl_hostgroup");
    if (isset($ret)) {
        foreach ($ret as $key => $value) {
            if (isset($value)) {
                $query = "INSERT INTO acl_resources_hg_relations (acl_res_id, hg_hg_id) VALUES (:acl_res_id, 
                                                                      :hg_hg_id)";
                $statement = $pearDB->prepare($query);
                $statement->bindValue(':acl_res_id', (int)$acl_id, \PDO::PARAM_INT);
                $statement->bindValue(':hg_hg_id', (int)$value, \PDO::PARAM_INT);
                $statement->execute();
            }
        }
    }
}

/**
 *
 * Update Service categories entries in DB
 * @param $acl_id
 */
function updateServiceCategories($acl_id = null)
{
    global $form, $pearDB;

    if (!$acl_id) {
        return;
    }

    $statement = $pearDB->prepare("DELETE FROM acl_resources_sc_relations WHERE acl_res_id = :acl_res_id");
    $statement->bindValue(':acl_res_id', (int)$acl_id, \PDO::PARAM_INT);
    $statement->execute();
    $ret = array();
    $ret = $form->getSubmitValue("acl_sc");
    if (isset($ret)) {
        foreach ($ret as $key => $value) {
            if (isset($value)) {
                $query = "INSERT INTO acl_resources_sc_relations (acl_res_id, sc_id) VALUES (:acl_res_id, :sc_id)";
                $statement = $pearDB->prepare($query);
                $statement->bindValue(':acl_res_id', (int)$acl_id, \PDO::PARAM_INT);
                $statement->bindValue(':sc_id', (int)$value, \PDO::PARAM_INT);
                $statement->execute();
            }
        }
    }
}

/**
 *
 * Update HG entries in DB
 * @param $acl_id
 */
function updateHostCategories($acl_id = null)
{
    global $form, $pearDB;

    if (!$acl_id) {
        return;
    }

    $statement = $pearDB->prepare("DELETE FROM acl_resources_hc_relations WHERE acl_res_id = :acl_res_id");
    $statement->bindValue(':acl_res_id', (int)$acl_id, \PDO::PARAM_INT);
    $statement->execute();
    $ret = array();
    $ret = $form->getSubmitValue("acl_hc");
    if (isset($ret)) {
        foreach ($ret as $key => $value) {
            if (isset($value)) {
                $query = "INSERT INTO acl_resources_hc_relations (acl_res_id, hc_id) VALUES (:acl_res_id, :hc_id)";
                $statement = $pearDB->prepare($query);
                $statement->bindValue(':acl_res_id', (int)$acl_id, \PDO::PARAM_INT);
                $statement->bindValue(':hc_id', (int)$value, \PDO::PARAM_INT);
                $statement->execute();
            }
        }
    }
}

/**
 *
 * Update Service groups entries in DB
 * @param $acl_id
 */
function updateServiceGroups($acl_id = null)
{
    global $form, $pearDB;

    if (!$acl_id) {
        return;
    }

    $statement = $pearDB->prepare("DELETE FROM acl_resources_sg_relations WHERE acl_res_id = :acl_res_id");
    $statement->bindValue(':acl_res_id', (int)$acl_id, \PDO::PARAM_INT);
    $statement->execute();
    $ret = array();
    $ret = $form->getSubmitValue("acl_sg");
    if (isset($ret)) {
        foreach ($ret as $key => $value) {
            if (isset($value)) {
                $query = "INSERT INTO acl_resources_sg_relations (acl_res_id, sg_id) VALUES (:acl_res_id, :sg_id)";
                $statement = $pearDB->prepare($query);
                $statement->bindValue(':acl_res_id', (int)$acl_id, \PDO::PARAM_INT);
                $statement->bindValue(':sg_id', (int)$value, \PDO::PARAM_INT);
                $statement->execute();
            }
        }
    }
}

/**
 *
 * Update Meta services entries in DB
 * @param $acl_id
 */
function updateMetaServices($acl_id = null)
{
    global $form, $pearDB;

    if (!$acl_id) {
        return;
    }

    $statement = $pearDB->prepare("DELETE FROM acl_resources_meta_relations WHERE acl_res_id = :acl_res_id");
    $statement->bindValue(':acl_res_id', (int)$acl_id, \PDO::PARAM_INT);
    $statement->execute();
    $ret = array();
    $ret = $form->getSubmitValue("acl_meta");
    if (isset($ret)) {
        foreach ($ret as $key => $value) {
            if (isset($value)) {
                $query = "INSERT INTO acl_resources_meta_relations (acl_res_id, meta_id) VALUES (:acl_res_id, :meta_id)";
                $statement = $pearDB->prepare($query);
                $statement->bindValue(':acl_res_id', (int)$acl_id, \PDO::PARAM_INT);
                $statement->bindValue(':meta_id', (int)$value, \PDO::PARAM_INT);
                $statement->execute();
            }
        }
    }
}

/**
 * sanitize resources parameter for Create / Update a Resource ACL
 *
 * @param array<string, mixed> $resources
 * @return array<string, mixed>
 */
function sanitizeResourceParameters(array $resources): array
{
    $sanitizedParameters = [];
    $sanitizedParameters['acl_res_name'] = filter_var($resources['acl_res_name'], FILTER_SANITIZE_STRING);

    if (empty($sanitizedParameters['acl_res_name'])) {
        throw new InvalidArgumentException(_("ACL Resource name can't be empty"));
    }

    $sanitizedParameters['acl_res_alias'] = filter_var($resources['acl_res_alias'], FILTER_SANITIZE_STRING);
    $sanitizedParameters['acl_res_comment'] = filter_var($resources['acl_res_comment'], FILTER_SANITIZE_STRING);

    // set default value for unconsistent FILTER_VALIDATE_INT
    $default = ["options" => ["default" => 0]];
    // Cast to string as it will be inserted as an enum '0','1'
    $sanitizedParameters['all_hosts'] =
        (string) filter_var($resources['all_hosts']['all_hosts'] ?? null, FILTER_VALIDATE_INT, $default);

    $sanitizedParameters['all_hostgroups'] =
        (string) filter_var($resources['all_hostgroups']['all_hostgroups'] ?? null, FILTER_VALIDATE_INT, $default);

    $sanitizedParameters['all_servicegroups'] =
        (string) filter_var(
            $resources['all_servicegroups']['all_servicegroups'] ?? null,
            FILTER_VALIDATE_INT,
            $default
        );

    $sanitizedParameters['acl_res_activate'] =
        (string) filter_var($resources['acl_res_activate']['acl_res_activate'] ?? null, FILTER_VALIDATE_INT, $default);

    return $sanitizedParameters;
}
