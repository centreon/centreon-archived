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

if (!isset($oreon)) {
    exit();
}

function testServiceGroupDependencyExistence($name = null)
{
    global $pearDB;
    global $form;

    CentreonDependency::purgeObsoleteDependencies($pearDB);

    $id = null;
    if (isset($form)) {
        $id = $form->getSubmitValue('dep_id');
    }
    $query = "SELECT dep_name, dep_id FROM dependency WHERE dep_name = '" .
        htmlentities($name, ENT_QUOTES, "UTF-8") . "'";
    $dbResult = $pearDB->query($query);
    $dep = $dbResult->fetch();
    #Modif case
    if ($dbResult->rowCount() >= 1 && $dep["dep_id"] == $id) {
        return true;
    } #Duplicate entry
    elseif ($dbResult->rowCount() >= 1 && $dep["dep_id"] != $id) {
        return false;
    } else {
        return true;
    }
}

function testServiceGroupDependencyCycle($childs = null)
{
    global $pearDB;
    global $form;
    $parents = array();
    $childs = array();
    if (isset($form)) {
        $parents = $form->getSubmitValue('dep_sgParents');
        $childs = $form->getSubmitValue('dep_sgChilds');
        $childs = array_flip($childs);
    }
    foreach ($parents as $parent) {
        if (array_key_exists($parent, $childs)) {
            return false;
        }
    }
    return true;
}

function deleteServiceGroupDependencyInDB($dependencies = array())
{
    global $pearDB, $oreon;
    foreach ($dependencies as $key => $value) {
        $dbResult2 = $pearDB->query("SELECT dep_name FROM `dependency` WHERE `dep_id` = '" . $key . "' LIMIT 1");
        $row = $dbResult2->fetch();

        $pearDB->query("DELETE FROM dependency WHERE dep_id = '" . $key . "'");
        $oreon->CentreonLogAction->insertLog("servicegroup dependency", $key, $row['dep_name'], "d");
    }
}

function multipleServiceGroupDependencyInDB($dependencies = array(), $nbrDup = array())
{
    foreach ($dependencies as $key => $value) {
        global $pearDB, $oreon;
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
            if (isset($dep_name) && testServiceGroupDependencyExistence($dep_name)) {
                $val ? $rq = "INSERT INTO dependency VALUES (" . $val . ")" : $rq = null;
                $pearDB->query($rq);
                $dbResult = $pearDB->query("SELECT MAX(dep_id) FROM dependency");
                $maxId = $dbResult->fetch();
                if (isset($maxId["MAX(dep_id)"])) {
                    $query = "SELECT DISTINCT servicegroup_sg_id FROM dependency_servicegroupParent_relation " .
                        "WHERE dependency_dep_id = '" . $key . "'";
                    $dbResult = $pearDB->query($query);
                    $fields["dep_sgParents"] = "";
                    while ($sg = $dbResult->fetch()) {
                        $query = "INSERT INTO dependency_servicegroupParent_relation " .
                            "VALUES ('" . $maxId["MAX(dep_id)"] . "', '" . $sg["servicegroup_sg_id"] . "')";
                        $pearDB->query($query);
                        $fields["dep_sgParents"] .= $sg["servicegroup_sg_id"] . ",";
                    }
                    $fields["dep_sgParents"] = trim($fields["dep_sgParents"], ",");
                    $dbResult->closeCursor();
                    $query = "SELECT DISTINCT servicegroup_sg_id FROM dependency_servicegroupChild_relation " .
                        "WHERE dependency_dep_id = '" . $key . "'";
                    $dbResult = $pearDB->query($query);
                    $fields["dep_sgChilds"] = "";
                    while ($sg = $dbResult->fetch()) {
                        $query = "INSERT INTO dependency_servicegroupChild_relation " .
                            "VALUES ('" . $maxId["MAX(dep_id)"] . "', '" . $sg["servicegroup_sg_id"] . "')";
                        $pearDB->query($query);
                        $fields["dep_sgChilds"] .= $sg["servicegroup_sg_id"] . ",";
                    }
                    $fields["dep_sgChilds"] = trim($fields["dep_sgChilds"], ",");
                    $oreon->CentreonLogAction->insertLog(
                        "servicegroup dependency",
                        $maxId["MAX(dep_id)"],
                        $dep_name,
                        "a",
                        $fields
                    );
                    $dbResult->closeCursor();
                }
            }
        }
    }
}

function updateServiceGroupDependencyInDB($dep_id = null)
{
    if (!$dep_id) {
        exit();
    }
    updateServiceGroupDependency($dep_id);
    updateServiceGroupDependencyServiceGroupParents($dep_id);
    updateServiceGroupDependencyServiceGroupChilds($dep_id);
}

function insertServiceGroupDependencyInDB($ret = array())
{
    $dep_id = insertServiceGroupDependency($ret);
    updateServiceGroupDependencyServiceGroupParents($dep_id, $ret);
    updateServiceGroupDependencyServiceGroupChilds($dep_id, $ret);
    return ($dep_id);
}

function insertServiceGroupDependency($ret = array())
{
    global $form;
    global $pearDB, $oreon;
    if (!count($ret)) {
        $ret = $form->getSubmitValues();
    }
    $rq = "INSERT INTO dependency ";
    $rq .= "(dep_name, dep_description, inherits_parent, execution_failure_criteria, " .
        "notification_failure_criteria, dep_comment) ";
    $rq .= "VALUES (";
    isset($ret["dep_name"]) && $ret["dep_name"] != null
        ? $rq .= "'" . htmlentities($ret["dep_name"], ENT_QUOTES, "UTF-8") . "', "
        : $rq .= "NULL, ";
    isset($ret["dep_description"]) && $ret["dep_description"] != null
        ? $rq .= "'" . htmlentities($ret["dep_description"], ENT_QUOTES, "UTF-8") . "', "
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
        ? $rq .= "'" . htmlentities($ret["dep_comment"], ENT_QUOTES, "UTF-8") . "' "
        : $rq .= "NULL ";
    $rq .= ")";
    $pearDB->query($rq);
    $dbResult = $pearDB->query("SELECT MAX(dep_id) FROM dependency");
    $dep_id = $dbResult->fetch();

    $fields["dep_name"] = htmlentities($ret["dep_name"], ENT_QUOTES, "UTF-8");
    $fields["dep_description"] = htmlentities($ret["dep_description"], ENT_QUOTES, "UTF-8");
    $fields["inherits_parent"] = $ret["inherits_parent"]["inherits_parent"];
    $fields["execution_failure_criteria"] = (isset($ret["execution_failure_criteria"]) ? implode(",", array_keys($ret["execution_failure_criteria"])) : '');
    $fields["notification_failure_criteria"] = implode(",", array_keys($ret["notification_failure_criteria"]));
    $fields["dep_comment"] = htmlentities($ret["dep_comment"], ENT_QUOTES, "UTF-8");
    $fields["dep_sgParents"] = "";
    if (isset($ret["dep_sgParents"])) {
        $fields["dep_sgParents"] = implode(",", $ret["dep_sgParents"]);
    }
    $fields["dep_sgChilds"] = "";
    if (isset($ret["dep_sgChilds"])) {
        $fields["dep_sgChilds"] = implode(",", $ret["dep_sgChilds"]);
    }
    $oreon->CentreonLogAction->insertLog(
        "servicegroup dependency",
        $dep_id["MAX(dep_id)"],
        htmlentities($ret["dep_name"], ENT_QUOTES, "UTF-8"),
        "a",
        $fields
    );

    return ($dep_id["MAX(dep_id)"]);
}

function updateServiceGroupDependency($dep_id = null)
{
    if (!$dep_id) {
        exit();
    }
    global $form;
    global $pearDB, $oreon;
    $ret = array();
    $ret = $form->getSubmitValues();
    $rq = "UPDATE dependency SET ";
    $rq .= "dep_name = ";
    isset($ret["dep_name"]) && $ret["dep_name"] != null
        ? $rq .= "'" . htmlentities($ret["dep_name"], ENT_QUOTES, "UTF-8") . "', "
        : $rq .= "NULL, ";
    $rq .= "dep_description = ";
    isset($ret["dep_description"]) && $ret["dep_description"] != null
        ? $rq .= "'" . htmlentities($ret["dep_description"], ENT_QUOTES, "UTF-8") . "', "
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
        ? $rq .= "'" . htmlentities($ret["dep_comment"], ENT_QUOTES, "UTF-8") . "' "
        : $rq .= "NULL ";
    $rq .= "WHERE dep_id = '" . $dep_id . "'";
    $pearDB->query($rq);

    $fields["dep_name"] = htmlentities($ret["dep_name"], ENT_QUOTES, "UTF-8");
    $fields["dep_description"] = htmlentities($ret["dep_description"], ENT_QUOTES, "UTF-8");
    $fields["inherits_parent"] = $ret["inherits_parent"]["inherits_parent"];
    $fields["execution_failure_criteria"] = implode(",", array_keys($ret["execution_failure_criteria"]));
    $fields["notification_failure_criteria"] = implode(",", array_keys($ret["notification_failure_criteria"]));
    $fields["dep_comment"] = htmlentities($ret["dep_comment"], ENT_QUOTES, "UTF-8");
    $fields["dep_sgParents"] = "";
    if (isset($ret["dep_sgParents"])) {
        $fields["dep_sgParents"] = implode(",", $ret["dep_sgParents"]);
    }
    $fields["dep_sgChilds"] = "";
    if (isset($ret["dep_sgChilds"])) {
        $fields["dep_sgChilds"] = implode(",", $ret["dep_sgChilds"]);
    }
    $oreon->CentreonLogAction->insertLog(
        "servicegroup dependency",
        $dep_id,
        htmlentities($ret["dep_name"], ENT_QUOTES, "UTF-8"),
        "c",
        $fields
    );
}

function updateServiceGroupDependencyServiceGroupParents($dep_id = null, $ret = array())
{
    if (!$dep_id) {
        exit();
    }
    global $form;
    global $pearDB;
    if (!count($ret)) {
        $ret = $form->getSubmitValues();
    }
    $rq = "DELETE FROM dependency_servicegroupParent_relation ";
    $rq .= "WHERE dependency_dep_id = '" . $dep_id . "'";
    $dbResult = $pearDB->query($rq);
    if (isset($ret["dep_sgParents"])) {
        $ret = $ret["dep_sgParents"];
    } else {
        $ret = CentreonUtils::mergeWithInitialValues($form, 'dep_sgParents');
    }
    for ($i = 0; $i < count($ret); $i++) {
        $rq = "INSERT INTO dependency_servicegroupParent_relation ";
        $rq .= "(dependency_dep_id, servicegroup_sg_id) ";
        $rq .= "VALUES ";
        $rq .= "('" . $dep_id . "', '" . $ret[$i] . "')";
        $dbResult = $pearDB->query($rq);
    }
}

function updateServiceGroupDependencyServiceGroupChilds($dep_id = null, $ret = array())
{
    if (!$dep_id) {
        exit();
    }
    global $form;
    global $pearDB;
    if (!count($ret)) {
        $ret = $form->getSubmitValues();
    }
    $rq = "DELETE FROM dependency_servicegroupChild_relation ";
    $rq .= "WHERE dependency_dep_id = '" . $dep_id . "'";
    $dbResult = $pearDB->query($rq);
    if (isset($ret["dep_sgChilds"])) {
        $ret = $ret["dep_sgChilds"];
    } else {
        $ret = CentreonUtils::mergeWithInitialValues($form, 'dep_sgChilds');
    }
    for ($i = 0; $i < count($ret); $i++) {
        $rq = "INSERT INTO dependency_servicegroupChild_relation ";
        $rq .= "(dependency_dep_id, servicegroup_sg_id) ";
        $rq .= "VALUES ";
        $rq .= "('" . $dep_id . "', '" . $ret[$i] . "')";
        $dbResult = $pearDB->query($rq);
    }
}
