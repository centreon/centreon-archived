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

if (!isset($oreon)) {
    exit();
}

function testExistence($name = null)
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

function testCycle($childs = null)
{
    global $pearDB;
    global $form;
    $parents = array();
    $childs = array();
    if (isset($form)) {
        $parents = $form->getSubmitValue('dep_msParents');
        $childs = $form->getSubmitValue('dep_msChilds');
        $childs = array_flip($childs);
    }
    foreach ($parents as $parent) {
        if (array_key_exists($parent, $childs)) {
            return false;
        }
    }
    return true;
}

function deleteMetaServiceDependencyInDB($dependencies = array())
{
    global $pearDB;
    foreach ($dependencies as $key => $value) {
        $dbResult = $pearDB->query("DELETE FROM dependency WHERE dep_id = '" . $key . "'");
    }
}

function multipleMetaServiceDependencyInDB($dependencies = array(), $nbrDup = array())
{
    foreach ($dependencies as $key => $value) {
        global $pearDB;
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
            }
            if (testExistence($dep_name)) {
                $val ? $rq = "INSERT INTO dependency VALUES (" . $val . ")" : $rq = null;
                $pearDB->query($rq);
                $dbResult = $pearDB->query("SELECT MAX(dep_id) FROM dependency");
                $maxId = $dbResult->fetch();
                if (isset($maxId["MAX(dep_id)"])) {
                    $query = "SELECT DISTINCT meta_service_meta_id FROM dependency_metaserviceParent_relation " .
                        "WHERE dependency_dep_id = '" . $key . "'";
                    $dbResult = $pearDB->query($query);
                    while ($ms = $dbResult->fetch()) {
                        $query = "INSERT INTO dependency_metaserviceParent_relation " .
                            "VALUES ('" . $maxId["MAX(dep_id)"] . "', '" . $ms["meta_service_meta_id"] . "')";
                        $pearDB->query($query);
                    }
                    $dbResult->closeCursor();
                    $query = "SELECT DISTINCT meta_service_meta_id FROM dependency_metaserviceChild_relation " .
                        "WHERE dependency_dep_id = '" . $key . "'";
                    $dbResult = $pearDB->query($query);
                    while ($ms = $dbResult->fetch()) {
                        $query = "INSERT INTO dependency_metaserviceChild_relation VALUES ('" .
                            $maxId["MAX(dep_id)"] . "', '" . $ms["meta_service_meta_id"] . "')";
                        $pearDB->query($query);
                    }
                    $dbResult->closeCursor();
                }
            }
        }
    }
}

function updateMetaServiceDependencyInDB($dep_id = null)
{
    if (!$dep_id) {
        exit();
    }
    updateMetaServiceDependency($dep_id);
    updateMetaServiceDependencyMetaServiceParents($dep_id);
    updateMetaServiceDependencyMetaServiceChilds($dep_id);
}

function insertMetaServiceDependencyInDB()
{
    $dep_id = insertMetaServiceDependency();
    updateMetaServiceDependencyMetaServiceParents($dep_id);
    updateMetaServiceDependencyMetaServiceChilds($dep_id);
    return ($dep_id);
}

/**
 * Create a metaservice dependency
 *
 * @return int
 */
function insertMetaServiceDependency(): int
{
    global $form, $pearDB, $centreon;
    $resourceValues = sanitizeResourceParameters($form->getSubmitValues());

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
    $statement->bindValue(':executionFailure', $resourceValues['execution_failure_criteria'], \PDO::PARAM_STR);
    $statement->bindValue(':notificationFailure', $resourceValues['notification_failure_criteria'], \PDO::PARAM_STR);
    $statement->bindValue(':depComment', $resourceValues['dep_comment'], \PDO::PARAM_STR);
    $statement->execute();

    $dbResult = $pearDB->query("SELECT MAX(dep_id) FROM dependency");
    $depId = $dbResult->fetch();

    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($resourceValues);
    $centreon->CentreonLogAction->insertLog(
        "metaservice dependency",
        $depId["MAX(dep_id)"],
        $resourceValues["dep_name"],
        "a",
        $fields
    );
    return ((int) $depId["MAX(dep_id)"]);
}

/**
 * Update a metaservice dependency
 *
 * @param null|int $depId
 */
function updateMetaServiceDependency($depId = null): void
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
    $statement->bindValue(':executionFailure', $resourceValues['execution_failure_criteria'], \PDO::PARAM_STR);
    $statement->bindValue(':notificationFailure', $resourceValues['notification_failure_criteria'], \PDO::PARAM_STR);
    $statement->bindValue(':depComment', $resourceValues['dep_comment'], \PDO::PARAM_STR);
    $statement->bindValue(':depId', $depId, \PDO::PARAM_INT);
    $statement->execute();

    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($resourceValues);
    $centreon->CentreonLogAction->insertLog(
        "metaservice dependency",
        $depId,
        $resourceValues["dep_name"],
        "c",
        $fields
    );
}

/**
 * sanitize resources parameter for Create / Update a meta service dependency
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

    $sanitizedParameters['execution_failure_criteria'] = filter_var(
        implode(
            ",",
            array_keys($resources["execution_failure_criteria"])
        ),
        FILTER_SANITIZE_STRING
    );

    $sanitizedParameters['notification_failure_criteria'] = filter_var(
        implode(
            ",",
            array_keys($resources["notification_failure_criteria"])
        ),
        FILTER_SANITIZE_STRING
    );
    $sanitizedParameters['dep_comment'] = filter_var($resources['dep_comment'], FILTER_SANITIZE_STRING);
    return $sanitizedParameters;
}

function updateMetaServiceDependencyMetaServiceParents($dep_id = null)
{
    if (!$dep_id) {
        exit();
    }
    global $form;
    global $pearDB;
    $rq = "DELETE FROM dependency_metaserviceParent_relation ";
    $rq .= "WHERE dependency_dep_id = '" . $dep_id . "'";
    $dbResult = $pearDB->query($rq);
    $ret = array();
    $ret = CentreonUtils::mergeWithInitialValues($form, 'dep_msParents');
    for ($i = 0; $i < count($ret); $i++) {
        $rq = "INSERT INTO dependency_metaserviceParent_relation ";
        $rq .= "(dependency_dep_id, meta_service_meta_id) ";
        $rq .= "VALUES ";
        $rq .= "('" . $dep_id . "', '" . $ret[$i] . "')";
        $dbResult = $pearDB->query($rq);
    }
}

function updateMetaServiceDependencyMetaServiceChilds($dep_id = null)
{
    if (!$dep_id) {
        exit();
    }
    global $form;
    global $pearDB;
    $rq = "DELETE FROM dependency_metaserviceChild_relation ";
    $rq .= "WHERE dependency_dep_id = '" . $dep_id . "'";
    $dbResult = $pearDB->query($rq);
    $ret = array();
    $ret = CentreonUtils::mergeWithInitialValues($form, 'dep_msChilds');
    for ($i = 0; $i < count($ret); $i++) {
        $rq = "INSERT INTO dependency_metaserviceChild_relation ";
        $rq .= "(dependency_dep_id, meta_service_meta_id) ";
        $rq .= "VALUES ";
        $rq .= "('" . $dep_id . "', '" . $ret[$i] . "')";
        $dbResult = $pearDB->query($rq);
    }
}
