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

function testServiceDependencyExistence($name = null)
{
    global $pearDB;
    global $form;

    CentreonDependency::purgeObsoleteDependencies($pearDB);

    $id = null;
    if (isset($form)) {
        $id = $form->getSubmitValue('dep_id');
    }
    $DBRESULT = $pearDB->query("SELECT dep_name, dep_id FROM dependency WHERE dep_name = '".htmlentities($name, ENT_QUOTES, "UTF-8")."'");
    $dep = $DBRESULT->fetchRow();
    #Modif case
    if ($DBRESULT->numRows() >= 1 && $dep["dep_id"] == $id) {
        return true;
    } #Duplicate entry
    elseif ($DBRESULT->numRows() >= 1 && $dep["dep_id"] != $id) {
        return false;
    } else {
        return true;
    }
}

function testCycleH($childs = null)
{
    global $pearDB;
    global $form;
    $parents = array();
    $childs = array();
    if (isset($form)) {
        $parents = $form->getSubmitValue('dep_hSvPar');
        $childs = $form->getSubmitValue('dep_hSvChi');
        $childs = array_flip($childs);
    }
    foreach ($parents as $parent) {
        if (array_key_exists($parent, $childs)) {
            return false;
        }
    }
    return true;
}

function deleteServiceDependencyInDB($dependencies = array())
{
    global $pearDB, $oreon;
    foreach ($dependencies as $key => $value) {
        $DBRESULT2 = $pearDB->query("SELECT dep_name FROM `dependency` WHERE `dep_id` = '".$key."' LIMIT 1");
        $row = $DBRESULT2->fetchRow();

        $DBRESULT = $pearDB->query("DELETE FROM dependency WHERE dep_id = '".$key."'");
        $oreon->CentreonLogAction->insertLog("service dependency", $key, $row['dep_name'], "d");
    }
}

function multipleServiceDependencyInDB($dependencies = array(), $nbrDup = array())
{
    foreach ($dependencies as $key => $value) {
        global $pearDB, $oreon;
        $DBRESULT = $pearDB->query("SELECT * FROM dependency WHERE dep_id = '".$key."' LIMIT 1");
        $row = $DBRESULT->fetchRow();
        $row["dep_id"] = '';
        for ($i = 1; $i <= $nbrDup[$key]; $i++) {
            $val = null;
            foreach ($row as $key2 => $value2) {
                $key2 == "dep_name" ? ($dep_name = $value2 = $value2."_".$i) : null;
                $val ? $val .= ($value2!=null?(", '".$value2."'"):", NULL") : $val .= ($value2!=null?("'".$value2."'"):"NULL");
                if ($key2 != "dep_id") {
                    $fields[$key2] = $value2;
                }
                if (isset($dep_name)) {
                    $fields["dep_name"] = $dep_name;
                }
            }
            if (isset($dep_name) && testServiceDependencyExistence($dep_name)) {
                $val ? $rq = "INSERT INTO dependency VALUES (".$val.")" : $rq = null;
                $pearDB->query($rq);
                $DBRESULT = $pearDB->query("SELECT MAX(dep_id) FROM dependency");
                $maxId = $DBRESULT->fetchRow();
                if (isset($maxId["MAX(dep_id)"])) {
                    $DBRESULT = $pearDB->query("SELECT * FROM dependency_serviceParent_relation WHERE dependency_dep_id = '".$key."'");
                    $fields["dep_hSvPar"] = "";
                    while ($service = $DBRESULT->fetchRow()) {
                        $DBRESULT2 = $pearDB->query("INSERT INTO dependency_serviceParent_relation VALUES ('', '".$maxId["MAX(dep_id)"]."', '".$service["service_service_id"]."', '".$service["host_host_id"]."')");
                        $fields["dep_hSvPar"] .= $service["service_service_id"] . ",";
                    }
                    $fields["dep_hSvPar"] = trim($fields["dep_hSvPar"], ",");
                    $DBRESULT = $pearDB->query("SELECT * FROM dependency_serviceChild_relation WHERE dependency_dep_id = '".$key."'");
                    $fields["dep_hSvChi"] = "";
                    while ($service = $DBRESULT->fetchRow()) {
                        $DBRESULT2 = $pearDB->query("INSERT INTO dependency_serviceChild_relation VALUES ('', '".$maxId["MAX(dep_id)"]."', '".$service["service_service_id"]."', '".$service["host_host_id"]."')");
                        $fields["dep_hSvChi"] .= $service["service_service_id"] . ",";
                    }
                    $fields["dep_hSvChi"] = trim($fields["dep_hSvChi"], ",");
                    $oreon->CentreonLogAction->insertLog("service dependency", $maxId["MAX(dep_id)"], $dep_name, "a", $fields);
                }
            }
        }
    }
}

function updateServiceDependencyInDB($dep_id = null)
{
    if (!$dep_id) {
        exit();
    }
    updateServiceDependency($dep_id);
    updateServiceDependencyServiceParents($dep_id);
    updateServiceDependencyServiceChilds($dep_id);
    updateServiceDependencyHostChildren($dep_id);
}

function insertServiceDependencyInDB($ret = array())
{
    $dep_id = insertServiceDependency($ret);
    updateServiceDependencyServiceParents($dep_id, $ret);
    updateServiceDependencyServiceChilds($dep_id, $ret);
    updateServiceDependencyHostChildren($dep_id, $ret);
    return ($dep_id);
}

function insertServiceDependency($ret = array())
{
    global $form;
    global $pearDB, $oreon;
    if (!count($ret)) {
        $ret = $form->getSubmitValues();
    }
    $rq = "INSERT INTO dependency ";
    $rq .= "(dep_name, dep_description, inherits_parent, execution_failure_criteria, notification_failure_criteria, dep_comment) ";
    $rq .= "VALUES (";
    isset($ret["dep_name"]) && $ret["dep_name"] != null ? $rq .= "'".htmlentities($ret["dep_name"], ENT_QUOTES, "UTF-8")."', " : $rq .= "NULL, ";
    isset($ret["dep_description"]) && $ret["dep_description"] != null ? $rq .= "'".htmlentities($ret["dep_description"], ENT_QUOTES, "UTF-8")."', " : $rq .= "NULL, ";
    isset($ret["inherits_parent"]["inherits_parent"]) && $ret["inherits_parent"]["inherits_parent"] != null ? $rq .= "'".$ret["inherits_parent"]["inherits_parent"]."', " : $rq .= "NULL, ";
    isset($ret["execution_failure_criteria"]) && $ret["execution_failure_criteria"] != null ? $rq .= "'".implode(",", array_keys($ret["execution_failure_criteria"]))."', " : $rq .= "NULL, ";
    isset($ret["notification_failure_criteria"]) && $ret["notification_failure_criteria"] != null ? $rq .= "'".implode(",", array_keys($ret["notification_failure_criteria"]))."', " : $rq .= "NULL, ";
    isset($ret["dep_comment"]) && $ret["dep_comment"] != null ? $rq .= "'".htmlentities($ret["dep_comment"], ENT_QUOTES, "UTF-8")."' " : $rq .= "NULL ";
    $rq .= ")";
    $DBRESULT = $pearDB->query($rq);
    $DBRESULT = $pearDB->query("SELECT MAX(dep_id) FROM dependency");
    $dep_id = $DBRESULT->fetchRow();

    $fields["dep_name"] = htmlentities($ret["dep_name"], ENT_QUOTES, "UTF-8");
    $fields["dep_description"] = htmlentities($ret["dep_description"], ENT_QUOTES, "UTF-8");
    if (isset($ret["inherits_parent"]["inherits_parent"])) {
        $fields["inherits_parent"] = $ret["inherits_parent"]["inherits_parent"];
    }
    if (isset($ret["execution_failure_criteria"])) {
        $fields["execution_failure_criteria"] = implode(",", array_keys($ret["execution_failure_criteria"]));
    }
    if (isset($ret["notification_failure_criteria"])) {
        $fields["notification_failure_criteria"] = implode(",", array_keys($ret["notification_failure_criteria"]));
    }
    $fields["dep_comment"] = htmlentities($ret["dep_comment"], ENT_QUOTES, "UTF-8");
    $fields["dep_hSvPar"] = "";
    if (isset($ret["dep_hSvPar"])) {
        $fields["dep_hSvPar"] = implode(",", $ret["dep_hSvPar"]);
    }
    $fields["dep_hSvChi"] = "";
    if (isset($ret["dep_hSvChi"])) {
        $fields["dep_hSvChi"] = implode(",", $ret["dep_hSvChi"]);
    }
    $oreon->CentreonLogAction->insertLog("service dependency", $dep_id["MAX(dep_id)"], htmlentities($ret["dep_name"], ENT_QUOTES, "UTF-8"), "a", $fields);
    return ($dep_id["MAX(dep_id)"]);
}

function updateServiceDependency($dep_id = null)
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
    isset($ret["dep_name"]) && $ret["dep_name"] != null ? $rq .= "'".htmlentities($ret["dep_name"], ENT_QUOTES, "UTF-8")."', " : $rq .= "NULL, ";
    $rq .= "dep_description = ";
    isset($ret["dep_description"]) && $ret["dep_description"] != null ? $rq .= "'".htmlentities($ret["dep_description"], ENT_QUOTES, "UTF-8")."', " : $rq .= "NULL, ";
    $rq .= "inherits_parent = ";
    isset($ret["inherits_parent"]["inherits_parent"]) && $ret["inherits_parent"]["inherits_parent"] != null ? $rq .= "'".$ret["inherits_parent"]["inherits_parent"]."', " : $rq .= "NULL, ";
    $rq .= "execution_failure_criteria = ";
    isset($ret["execution_failure_criteria"]) && $ret["execution_failure_criteria"] != null ? $rq .= "'".implode(",", array_keys($ret["execution_failure_criteria"]))."', " : $rq .= "NULL, ";
    $rq .= "notification_failure_criteria = ";
    isset($ret["notification_failure_criteria"]) && $ret["notification_failure_criteria"] != null ? $rq .= "'".implode(",", array_keys($ret["notification_failure_criteria"]))."', " : $rq .= "NULL, ";
    $rq .= "dep_comment = ";
    isset($ret["dep_comment"]) && $ret["dep_comment"] != null ? $rq .= "'".htmlentities($ret["dep_comment"], ENT_QUOTES, "UTF-8")."' " : $rq .= "NULL ";
    $rq .= "WHERE dep_id = '".$dep_id."'";
    $DBRESULT = $pearDB->query($rq);

    $fields["dep_name"] = htmlentities($ret["dep_name"], ENT_QUOTES, "UTF-8");
    $fields["dep_description"] = htmlentities($ret["dep_description"], ENT_QUOTES, "UTF-8");
    $fields["inherits_parent"] = $ret["inherits_parent"]["inherits_parent"];
    if (isset($ret["execution_failure_criteria"])) {
        $fields["execution_failure_criteria"] = implode(",", array_keys($ret["execution_failure_criteria"]));
    }
    if (isset($ret["notification_failure_criteria"])) {
        $fields["notification_failure_criteria"] = implode(",", array_keys($ret["notification_failure_criteria"]));
    }
    $fields["dep_comment"] = htmlentities($ret["dep_comment"], ENT_QUOTES, "UTF-8");
    $fields["dep_hSvPar"] = "";
    if (isset($ret["dep_hSvPar"])) {
        $fields["dep_hSvPar"] = implode(",", $ret["dep_hSvPar"]);
    }
    $fields["dep_hSvChi"] = "";
    if (isset($ret["dep_hSvChi"])) {
        $fields["dep_hSvChi"] = implode(",", $ret["dep_hSvChi"]);
    }
    $oreon->CentreonLogAction->insertLog("service dependency", $dep_id, htmlentities($ret["dep_name"], ENT_QUOTES, "UTF-8"), "c", $fields);
}

function updateServiceDependencyServiceParents($dep_id = null, $ret = array())
{
    if (!$dep_id) {
        exit();
    }
    global $form;
    global $pearDB;
    $rq = "DELETE FROM dependency_serviceParent_relation ";
    $rq .= "WHERE dependency_dep_id = '".$dep_id."'";
    $DBRESULT = $pearDB->query($rq);
    if (isset($ret["dep_hSvPar"])) {
        $ret1 = $ret["dep_hSvPar"];
    } else {
        $ret1 = CentreonUtils::mergeWithInitialValues($form, "dep_hSvPar");
    }
    for ($i = 0; $i < count($ret1); $i++) {
        $exp = explode("-", $ret1[$i]);
        if (count($exp) == 2) {
            $rq = "INSERT INTO dependency_serviceParent_relation ";
            $rq .= "(dependency_dep_id, service_service_id, host_host_id) ";
            $rq .= "VALUES ";
            $rq .= "('".$dep_id."', '".$exp[1]."', '".$exp[0]."')";
            $DBRESULT = $pearDB->query($rq);
        }
    }
}

function updateServiceDependencyServiceChilds($dep_id = null, $ret = array())
{
    if (!$dep_id) {
        exit();
    }
    global $form;
    global $pearDB;
    $rq = "DELETE FROM dependency_serviceChild_relation ";
    $rq .= "WHERE dependency_dep_id = '".$dep_id."'";
    $DBRESULT = $pearDB->query($rq);
    if (isset($ret["dep_hSvChi"])) {
        $ret1 = $ret["dep_hSvChi"];
    } else {
        $ret1 = CentreonUtils::mergeWithInitialValues($form, "dep_hSvChi");
    }
    for ($i = 0; $i < count($ret1); $i++) {
        $exp = explode("-", $ret1[$i]);
        if (count($exp) == 2) {
            $rq = "INSERT INTO dependency_serviceChild_relation ";
            $rq .= "(dependency_dep_id, service_service_id, host_host_id) ";
            $rq .= "VALUES ";
            $rq .= "('".$dep_id."', '".$exp[1]."', '".$exp[0]."')";
            $DBRESULT = $pearDB->query($rq);
        }
    }
}

    /**
     * Update Service Dependency Host Children
     */
function updateServiceDependencyHostChildren($dep_id = null, $ret = array())
{
    if (!$dep_id) {
        exit();
    }
    global $form;
    global $pearDB;
    $rq = "DELETE FROM dependency_hostChild_relation ";
    $rq .= "WHERE dependency_dep_id = '".$dep_id."'";
    $DBRESULT = $pearDB->query($rq);
    if (isset($ret["dep_hHostChi"])) {
        $ret1 = $ret["dep_hHostChi"];
    } else {
        $ret1 = CentreonUtils::mergeWithInitialValues($form, "dep_hHostChi");
    }
    for ($i = 0; $i < count($ret1); $i++) {
        $rq = "INSERT INTO dependency_hostChild_relation ";
        $rq .= "(dependency_dep_id, host_host_id) ";
        $rq .= "VALUES ";
        $rq .= "('".$dep_id."', '".$ret1[$i]."')";
        $DBRESULT = $pearDB->query($rq);
    }
}
