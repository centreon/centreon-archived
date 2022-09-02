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
        $dbResult2 = $pearDB->query("SELECT dep_name FROM `dependency` WHERE `dep_id` = '" . $key . "' LIMIT 1");
        $row = $dbResult2->fetch();

        $dbResult = $pearDB->query("DELETE FROM dependency WHERE dep_id = '" . $key . "'");
        $oreon->CentreonLogAction->insertLog("service dependency", $key, $row['dep_name'], "d");
    }
}

function multipleServiceDependencyInDB($dependencies = array(), $nbrDup = array())
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
            if (isset($dep_name) && testServiceDependencyExistence($dep_name)) {
                $val ? $rq = "INSERT INTO dependency VALUES (" . $val . ")" : $rq = null;
                $pearDB->query($rq);
                $dbResult = $pearDB->query("SELECT MAX(dep_id) FROM dependency");
                $maxId = $dbResult->fetch();
                if (isset($maxId["MAX(dep_id)"])) {
                    $query = "SELECT * FROM dependency_hostChild_relation WHERE dependency_dep_id = '" . $key . "'";
                    $dbResult = $pearDB->query($query);
                    $fields["dep_hostPar"] = "";
                    while ($host = $dbResult->fetch()) {
                        $query = "INSERT INTO dependency_hostChild_relation VALUES ('" . $maxId["MAX(dep_id)"] .
                            "', '" . $host["host_host_id"] . "')";
                        $pearDB->query($query);
                        $fields["dep_hostPar"] .= $host["host_host_id"] . ",";
                    }
                    $fields["dep_hostPar"] = trim($fields["dep_hostPar"], ",");

                    $query = "SELECT * FROM dependency_serviceParent_relation WHERE dependency_dep_id = '" . $key . "'";
                    $dbResult = $pearDB->query($query);
                    $fields["dep_hSvPar"] = "";
                    while ($service = $dbResult->fetch()) {
                        $query = "INSERT INTO dependency_serviceParent_relation VALUES ('" .
                            $maxId["MAX(dep_id)"] . "', '" . $service["service_service_id"] . "', '" .
                            $service["host_host_id"] . "')";
                        $pearDB->query($query);
                        $fields["dep_hSvPar"] .= $service["service_service_id"] . ",";
                    }
                    $fields["dep_hSvPar"] = trim($fields["dep_hSvPar"], ",");
                    $query = "SELECT * FROM dependency_serviceChild_relation WHERE dependency_dep_id = '" . $key . "'";
                    $dbResult = $pearDB->query($query);
                    $fields["dep_hSvChi"] = "";
                    while ($service = $dbResult->fetch()) {
                        $query = "INSERT INTO dependency_serviceChild_relation VALUES ('" . $maxId["MAX(dep_id)"] .
                            "', '" . $service["service_service_id"] . "', '" . $service["host_host_id"] . "')";
                        $pearDB->query($query);
                        $fields["dep_hSvChi"] .= $service["service_service_id"] . ",";
                    }
                    $fields["dep_hSvChi"] = trim($fields["dep_hSvChi"], ",");
                    $oreon->CentreonLogAction->insertLog(
                        "service dependency",
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

/**
 * Create a service dependency
 *
 * @param array<string, mixed> $ret
 * @return int
 */
function insertServiceDependency($ret = array()): int
{
    global $form, $pearDB, $centreon;
    if (!count($ret)) {
        $ret = $form->getSubmitValues();
    }

    $resourceValues = sanitizeResourceParameters($ret);
    $statement = $pearDB->prepare(
        "INSERT INTO `dependency`
        (dep_name, dep_description, inherits_parent, execution_failure_criteria,
         notification_failure_criteria, dep_comment)
        VALUES (:depName, :depDescription, :inheritsParent, :executionFailure,
                :notificationFailure, :depComment)"
    );
    $statement->bindValue(':depName', $resourceValues['dep_name'], \PDO::PARAM_STR);
    $statement->bindValue(':depDescription', $resourceValues['dep_description'], \PDO::PARAM_STR);
    $statement->bindValue(':inheritsParent', $resourceValues['inherits_parent'], \PDO::PARAM_STR);
    $statement->bindValue(
        ':executionFailure',
        $resourceValues['execution_failure_criteria'] ?? null,
        \PDO::PARAM_STR
    );
    $statement->bindValue(
        ':notificationFailure',
        $resourceValues['notification_failure_criteria'] ?? null,
        \PDO::PARAM_STR
    );
    $statement->bindValue(':depComment', $resourceValues['dep_comment'], \PDO::PARAM_STR);
    $statement->execute();

    $dbResult = $pearDB->query("SELECT MAX(dep_id) FROM dependency");
    $depId = $dbResult->fetch();

    $fields = CentreonLogAction::prepareChanges($ret);
    $centreon->CentreonLogAction->insertLog(
        "service dependency",
        $depId["MAX(dep_id)"],
        $resourceValues["dep_name"],
        "a",
        $fields
    );
    return ((int) $depId["MAX(dep_id)"]);
}

/**
 * Update a service dependency
 *
 * @param null|int $depId
 */
function updateServiceDependency($depId = null): void
{
    if (!$depId) {
        exit();
    }
    global $form, $pearDB, $centreon;

    $resourceValues = sanitizeResourceParameters($form->getSubmitValues());
    $statement = $pearDB->prepare(
        "UPDATE `dependency`
        SET dep_name = :depName,
        dep_description = :depDescription,
        inherits_parent = :inheritsParent,
        execution_failure_criteria = :executionFailure,
        notification_failure_criteria = :notificationFailure,
        dep_comment = :depComment
        WHERE dep_id = :depId"
    );
    $statement->bindValue(':depName', $resourceValues['dep_name'], \PDO::PARAM_STR);
    $statement->bindValue(':depDescription', $resourceValues['dep_description'], \PDO::PARAM_STR);
    $statement->bindValue(':inheritsParent', $resourceValues['inherits_parent'], \PDO::PARAM_STR);
    $statement->bindValue(
        ':executionFailure',
        $resourceValues['execution_failure_criteria'] ?? null,
        \PDO::PARAM_STR
    );
    $statement->bindValue(
        ':notificationFailure',
        $resourceValues['notification_failure_criteria'] ?? null,
        \PDO::PARAM_STR
    );
    $statement->bindValue(':depComment', $resourceValues['dep_comment'], \PDO::PARAM_STR);
    $statement->bindValue(':depId', $depId, \PDO::PARAM_INT);
    $statement->execute();

    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($resourceValues);
    $centreon->CentreonLogAction->insertLog(
        "service dependency",
        $depId,
        $resourceValues["dep_name"],
        "c",
        $fields
    );
}

/**
 * sanitize resources parameter for Create / Update a service dependency
 *
 * @param array<string, mixed> $resources
 * @return array<string, mixed>
 */
function sanitizeResourceParameters(array $resources): array
{
    $sanitizedParameters = [];
    $sanitizedParameters['dep_name'] = filter_var($resources['dep_name'], FILTER_SANITIZE_STRING);
    if (empty($sanitizedParameters['dep_name'])) {
        throw new InvalidArgumentException(_("Dependency name can't be empty"));
    }

    $sanitizedParameters['dep_description'] = filter_var($resources['dep_description'], FILTER_SANITIZE_STRING);
    if (empty($sanitizedParameters['dep_description'])) {
        throw new InvalidArgumentException(_("Dependency description can't be empty"));
    }

    $resources["inherits_parent"]["inherits_parent"] == 1
        ? $sanitizedParameters["inherits_parent"] = '1'
        : $sanitizedParameters["inherits_parent"] = '0';

    if (isset($resources["execution_failure_criteria"]) && is_array($resources["execution_failure_criteria"])) {
        $sanitizedParameters['execution_failure_criteria'] = filter_var(
            implode(
                ",",
                array_keys($resources["execution_failure_criteria"])
            ),
            FILTER_SANITIZE_STRING
        );
    }

    if (isset($resources["notification_failure_criteria"]) && is_array($resources["notification_failure_criteria"])) {
        $sanitizedParameters['notification_failure_criteria'] = filter_var(
            implode(
                ",",
                array_keys($resources["notification_failure_criteria"])
            ),
            FILTER_SANITIZE_STRING
        );
    }
    $sanitizedParameters['dep_comment'] = filter_var($resources['dep_comment'], FILTER_SANITIZE_STRING);
    return $sanitizedParameters;
}

function updateServiceDependencyServiceParents($dep_id = null, $ret = array())
{
    if (!$dep_id) {
        exit();
    }
    global $form;
    global $pearDB;
    if (!count($ret)) {
        $ret = $form->getSubmitValues();
    }
    $rq = "DELETE FROM dependency_serviceParent_relation ";
    $rq .= "WHERE dependency_dep_id = '" . $dep_id . "'";
    $dbResult = $pearDB->query($rq);
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
            $rq .= "('" . $dep_id . "', '" . $exp[1] . "', '" . $exp[0] . "')";
            $dbResult = $pearDB->query($rq);
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
    if (!count($ret)) {
        $ret = $form->getSubmitValues();
    }
    $rq = "DELETE FROM dependency_serviceChild_relation ";
    $rq .= "WHERE dependency_dep_id = '" . $dep_id . "'";
    $dbResult = $pearDB->query($rq);
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
            $rq .= "('" . $dep_id . "', '" . $exp[1] . "', '" . $exp[0] . "')";
            $dbResult = $pearDB->query($rq);
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
    if (!count($ret)) {
        $ret = $form->getSubmitValues();
    }
    $rq = "DELETE FROM dependency_hostChild_relation ";
    $rq .= "WHERE dependency_dep_id = '" . $dep_id . "'";
    $dbResult = $pearDB->query($rq);
    if (isset($ret["dep_hHostChi"])) {
        $ret1 = $ret["dep_hHostChi"];
    } else {
        $ret1 = CentreonUtils::mergeWithInitialValues($form, "dep_hHostChi");
    }
    for ($i = 0; $i < count($ret1); $i++) {
        $rq = "INSERT INTO dependency_hostChild_relation ";
        $rq .= "(dependency_dep_id, host_host_id) ";
        $rq .= "VALUES ";
        $rq .= "('" . $dep_id . "', '" . $ret1[$i] . "')";
        $dbResult = $pearDB->query($rq);
    }
}
