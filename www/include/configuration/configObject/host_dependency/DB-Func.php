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

function testHostDependencyExistence($name = null)
{
    global $pearDB;
    global $form;

    CentreonDependency::purgeObsoleteDependencies($pearDB);

    $id = null;
    if (isset($form)) {
        $id = $form->getSubmitValue('dep_id');
    }
    $query = "SELECT dep_name, dep_id FROM dependency WHERE dep_name = '" . CentreonDB::escape($name) . "'";
    $dbResult = $pearDB->query($query);
    $dep = $dbResult->fetch();

    if ($dbResult->rowCount() >= 1 && $dep["dep_id"] == $id) {
        return true;
    } elseif ($dbResult->rowCount() >= 1 && $dep["dep_id"] != $id) {
        return false;
    } else {
        return true;
    }
}

function testHostDependencyCycle($childs = null)
{
    global $pearDB;
    global $form;
    $parents = array();
    $childs = array();
    if (isset($form)) {
        $parents = $form->getSubmitValue('dep_hostParents');
        $childs = $form->getSubmitValue('dep_hostChilds');
        $childs = array_flip($childs);
    }
    foreach ($parents as $parent) {
        if (array_key_exists($parent, $childs)) {
            return false;
        }
    }
    return true;
}

function deleteHostDependencyInDB($dependencies = array())
{
    global $pearDB, $centreon;

    foreach ($dependencies as $key => $value) {
        $dbResult2 = $pearDB->query("SELECT dep_name FROM `dependency` WHERE `dep_id` = '" . $key . "' LIMIT 1");
        $row = $dbResult2->fetch();
        $dbResult = $pearDB->query("DELETE FROM dependency WHERE dep_id = '" . $key . "'");
        $centreon->CentreonLogAction->insertLog("host dependency", $key, $row['dep_name'], "d");
    }
}

function multipleHostDependencyInDB($dependencies = array(), $nbrDup = array())
{
    foreach ($dependencies as $key => $value) {
        global $pearDB, $centreon;
        $dbResult = $pearDB->query("SELECT * FROM dependency WHERE dep_id = '" . $key . "' LIMIT 1");
        $row = $dbResult->fetch();
        $row["dep_id"] = null;
        for ($i = 1; $i <= $nbrDup[$key]; $i++) {
            $val = null;
            foreach ($row as $key2 => $value2) {
                $key2 == "dep_name" ? ($dep_name = $value2 = $value2 . "_" . $i) : null;
                $val
                    ? $val .= ($value2 != null ? (", '" . $value2 . "'") : ", NULL")
                    : $val .= ($value2 != null ? ("'" . $value2 . "'") : "NULL");
                if ($key2 != "dep_id") {
                    $fields[$key2] = $value2;
                }
                if (isset($dep_name)) {
                    $fields["dep_name"] = $dep_name;
                }
            }
            if (isset($dep_name) && testHostDependencyExistence($dep_name)) {
                $val ? $rq = "INSERT INTO dependency VALUES (" . $val . ")" : $rq = null;
                $pearDB->query($rq);
                $dbResult = $pearDB->query("SELECT MAX(dep_id) FROM dependency");
                $maxId = $dbResult->fetch();
                if (isset($maxId["MAX(dep_id)"])) {
                    $query = "SELECT service_service_id, host_host_id FROM dependency_serviceChild_relation " .
                        "WHERE dependency_dep_id = " . $key;
                    $dbResult = $pearDB->query($query);
                    $fields["dep_serviceChilds"] = "";
                    while ($service = $dbResult->fetch()) {
                        $query = "INSERT INTO dependency_serviceChild_relation VALUES ('" .
                            $maxId["MAX(dep_id)"] . "', '" . $service["service_service_id"] . "', '" .
                            $service["host_host_id"] . "')";
                        $pearDB->query($query);
                        $fields["dep_serviceChilds"] .= $service["host_host_id"] .
                            '-' . $service["service_service_id"] . ",";
                    }

                    $query = "SELECT DISTINCT host_host_id FROM dependency_hostParent_relation " .
                        "WHERE dependency_dep_id = '" . $key . "'";
                    $dbResult = $pearDB->query($query);
                    $fields["dep_hostParents"] = "";
                    while ($host = $dbResult->fetch()) {
                        $query = "INSERT INTO dependency_hostParent_relation " .
                            "VALUES ('" . $maxId["MAX(dep_id)"] . "', '" . $host["host_host_id"] . "')";
                        $pearDB->query($query);
                        $fields["dep_hostParents"] .= $host["host_host_id"] . ",";
                    }
                    $fields["dep_hostParents"] = trim($fields["dep_hostParents"], ",");
                    $dbResult->closeCursor();
                    $query = "SELECT DISTINCT host_host_id FROM dependency_hostChild_relation " .
                        "WHERE dependency_dep_id = '" . $key . "'";
                    $dbResult = $pearDB->query($query);
                    $fields["dep_hostChilds"] = "";
                    while ($host = $dbResult->fetch()) {
                        $query = "INSERT INTO dependency_hostChild_relation " .
                            "VALUES ('" . $maxId["MAX(dep_id)"] . "', '" . $host["host_host_id"] . "')";
                        $pearDB->query($query);
                        $fields["dep_hostChilds"] .= $host["host_host_id"] . ",";
                    }
                    $fields["dep_hostChilds"] = trim($fields["dep_hostChilds"], ",");
                    $dbResult->closeCursor();
                    $centreon->CentreonLogAction->insertLog(
                        "host dependency",
                        $maxId["MAX(dep_id)"],
                        $dep_name,
                        "a",
                        $fields
                    );
                }
            }
        }
    }
}

function updateHostDependencyInDB($dep_id = null)
{
    if (!$dep_id) {
        exit();
    }
    updateHostDependency($dep_id);
    updateHostDependencyHostParents($dep_id);
    updateHostDependencyHostChilds($dep_id);
    updateHostDependencyServiceChildren($dep_id);
}

function insertHostDependencyInDB($ret = array())
{
    $dep_id = insertHostDependency($ret);
    updateHostDependencyHostParents($dep_id, $ret);
    updateHostDependencyHostChilds($dep_id, $ret);
    updateHostDependencyServiceChildren($dep_id, $ret);
    return ($dep_id);
}

function insertHostDependency($ret = array())
{
    global $form, $pearDB, $centreon;

    if (!count($ret)) {
        $ret = $form->getSubmitValues();
    }

    $rq = "INSERT INTO dependency ";
    $rq .= "(dep_name, dep_description, inherits_parent, execution_failure_criteria, " .
        "notification_failure_criteria, dep_comment) ";
    $rq .= "VALUES (";
    isset($ret["dep_name"]) && $ret["dep_name"] != null
        ? $rq .= "'" . CentreonDB::escape($ret["dep_name"]) . "', "
        : $rq .= "NULL, ";
    isset($ret["dep_description"]) && $ret["dep_description"] != null
        ? $rq .= "'" . CentreonDB::escape($ret["dep_description"]) . "', "
        : $rq .= "NULL, ";
    isset($ret["inherits_parent"]["inherits_parent"]) && $ret["inherits_parent"]["inherits_parent"] != null
        ? $rq .= "'" . $ret["inherits_parent"]["inherits_parent"] . "', "
        : $rq .= "NULL, ";
    isset($ret["execution_failure_criteria"]) && $ret["execution_failure_criteria"] != null
        ? $rq .= "'" . implode(",", array_keys($ret["execution_failure_criteria"])) . "', "
        : $rq .= "NULL, ";
    isset($ret["notification_failure_criteria"]) && $ret["notification_failure_criteria"] != null
        ? $rq .= "'" . implode(",", array_keys($ret["notification_failure_criteria"])) . "', "
        : $rq .= "NULL, ";
    isset($ret["dep_comment"]) && $ret["dep_comment"] != null
        ? $rq .= "'" . CentreonDB::escape($ret["dep_comment"]) . "' "
        : $rq .= "NULL ";
    $rq .= ")";
    $pearDB->query($rq);
    $dbResult = $pearDB->query("SELECT MAX(dep_id) FROM dependency");
    $dep_id = $dbResult->fetch();

    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($ret);

    $centreon->CentreonLogAction->insertLog(
        "host dependency",
        $dep_id["MAX(dep_id)"],
        CentreonDB::escape($ret["dep_name"]),
        "a",
        $fields
    );
    return ($dep_id["MAX(dep_id)"]);
}

function updateHostDependency($dep_id = null)
{
    global $form, $pearDB, $centreon;

    if (!$dep_id) {
        exit();
    }

    $ret = array();
    $ret = $form->getSubmitValues();
    $rq = "UPDATE dependency SET ";
    $rq .= "dep_name = ";
    isset($ret["dep_name"]) && $ret["dep_name"] != null
        ? $rq .= "'" . CentreonDB::escape($ret["dep_name"]) . "', "
        : $rq .= "NULL, ";
    $rq .= "dep_description = ";
    isset($ret["dep_description"]) && $ret["dep_description"] != null
        ? $rq .= "'" . CentreonDB::escape($ret["dep_description"]) . "', "
        : $rq .= "NULL, ";
    $rq .= "inherits_parent = ";
    isset($ret["inherits_parent"]["inherits_parent"]) && $ret["inherits_parent"]["inherits_parent"] != null
        ? $rq .= "'" . $ret["inherits_parent"]["inherits_parent"] . "', "
        : $rq .= "NULL, ";
    $rq .= "execution_failure_criteria = ";
    isset($ret["execution_failure_criteria"]) && $ret["execution_failure_criteria"] != null
        ? $rq .= "'" . implode(",", array_keys($ret["execution_failure_criteria"])) . "', "
        : $rq .= "NULL, ";
    $rq .= "notification_failure_criteria = ";
    isset($ret["notification_failure_criteria"]) && $ret["notification_failure_criteria"] != null
        ? $rq .= "'" . implode(",", array_keys($ret["notification_failure_criteria"])) . "', "
        : $rq .= "NULL, ";
    $rq .= "dep_comment = ";
    isset($ret["dep_comment"]) && $ret["dep_comment"] != null
        ? $rq .= "'" . CentreonDB::escape($ret["dep_comment"]) . "' "
        : $rq .= "NULL ";
    $rq .= "WHERE dep_id = '" . $dep_id . "'";
    $pearDB->query($rq);

    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($ret);
    $centreon->CentreonLogAction->insertLog(
        "host dependency",
        $dep_id,
        CentreonDB::escape($ret["dep_name"]),
        "c",
        $fields
    );
}

function updateHostDependencyHostParents($dep_id = null, $ret = array())
{
    if (!$dep_id) {
        exit();
    }
    global $form;
    global $pearDB;
    $rq = "DELETE FROM dependency_hostParent_relation ";
    $rq .= "WHERE dependency_dep_id = '" . $dep_id . "'";
    $dbResult = $pearDB->query($rq);
    if (isset($ret["dep_hostParents"])) {
        $ret = $ret["dep_hostParents"];
    } else {
        $ret = CentreonUtils::mergeWithInitialValues($form, 'dep_hostParents');
    }
    for ($i = 0; $i < count($ret); $i++) {
        $rq = "INSERT INTO dependency_hostParent_relation ";
        $rq .= "(dependency_dep_id, host_host_id) ";
        $rq .= "VALUES ";
        $rq .= "('" . $dep_id . "', '" . $ret[$i] . "')";
        $dbResult = $pearDB->query($rq);
    }
}

function updateHostDependencyHostChilds($dep_id = null, $ret = array())
{
    global $form, $pearDB;

    if (!$dep_id) {
        exit();
    }

    $rq = "DELETE FROM dependency_hostChild_relation ";
    $rq .= "WHERE dependency_dep_id = '" . $dep_id . "'";
    $dbResult = $pearDB->query($rq);
    if (isset($ret["dep_hostChilds"])) {
        $ret = $ret["dep_hostChilds"];
    } else {
        $ret = CentreonUtils::mergeWithInitialValues($form, 'dep_hostChilds');
    }
    for ($i = 0; $i < count($ret); $i++) {
        $rq = "INSERT INTO dependency_hostChild_relation ";
        $rq .= "(dependency_dep_id, host_host_id) ";
        $rq .= "VALUES ";
        $rq .= "('" . $dep_id . "', '" . $ret[$i] . "')";
        $dbResult = $pearDB->query($rq);
    }
}

/**
 * Update Host Dependency Service Children
 */
function updateHostDependencyServiceChildren($dep_id = null, $ret = array())
{
    global $form, $pearDB;

    if (!$dep_id) {
        exit();
    }

    $rq = "DELETE FROM dependency_serviceChild_relation ";
    $rq .= "WHERE dependency_dep_id = '" . $dep_id . "'";
    $dbResult = $pearDB->query($rq);
    if (isset($ret["dep_hSvChi"])) {
        $ret = $ret["dep_hSvChi"];
    } else {
        $ret = CentreonUtils::mergeWithInitialValues($form, 'dep_hSvChi');
    }
    for ($i = 0; $i < count($ret); $i++) {
        $exp = explode("-", $ret[$i]);
        if (count($exp) == 2) {
            $rq = "INSERT INTO dependency_serviceChild_relation ";
            $rq .= "(dependency_dep_id, service_service_id, host_host_id) ";
            $rq .= "VALUES ";
            $rq .= "('" . $dep_id . "', '" . $exp[1] . "', '" . $exp[0] . "')";
            $dbResult = $pearDB->query($rq);
        }
    }
}
