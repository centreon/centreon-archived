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

/**
 * Indicates if the resource name has already been used
 *
 * @global CentreonDB $pearDB
 * @global HTML_QuickFormCustom $form
 * @param string $name
 * @param int $instanceId
 * @return boolean Return false if the resource name has already been used
 */
function testExistence($name = null, $instanceId = null)
{
    global $pearDB, $form;

    $id = 0;
    $instanceIds = array();
    if (isset($form)) {
        $id = (int) $form->getSubmitValue('resource_id');
        $instanceIds = $form->getSubmitValue('instance_id');
        $instanceIds = filter_var_array(
            $instanceIds,
            FILTER_VALIDATE_INT
        );
        if (in_array(false, $instanceIds)) {
            return true;
        }
    } elseif (!is_null($instanceId) && $instanceId) {
        $instanceIds = array((int) $instanceId);
    }
    if (empty($instanceIds)) {
        return true;
    }
    $prepare = $pearDB->prepare(
        "SELECT cr.resource_name, crir.resource_id, crir.instance_id "
        . "FROM cfg_resource cr, cfg_resource_instance_relations crir "
        . "WHERE cr.resource_id = crir.resource_id "
        . "AND crir.instance_id IN (" . implode(",", $instanceIds) . ") "
        . "AND cr.resource_name = :resource_name"
    );
    $prepare->bindValue(':resource_name', $name, \PDO::PARAM_STR);
    $prepare->execute();
    $total = $prepare->rowCount();
    $result = $prepare->fetch(\PDO::FETCH_ASSOC);
    if ($total >= 1 && $result["resource_id"] == $id) {
        /**
         * In case of modification
         */
        return true;
    } elseif ($total >= 1 && $result["resource_id"] != $id) {
        /**
         * In case of duplicate
         */
        return false;
    } else {
        return true;
    }
}

/**
 * Deletes resources
 *
 * @global CentreonDB $pearDB
 * @param int[] $resourceIds Resource ids to delete
 */
function deleteResourceInDB($resourceIds = array())
{
    global $pearDB;

    foreach (array_keys($resourceIds) as $currentResourceId) {
        if (is_int($currentResourceId)) {
            $pearDB->query(
                "DELETE FROM cfg_resource WHERE resource_id = $currentResourceId"
            );
        }
    }
}

/**
 * Enables a resource
 *
 * @global CentreonDB $pearDB
 * @param int[] $resourceId Resource id to enable
 */
function enableResourceInDB($resourceId)
{
    global $pearDB;

    if (is_int($resourceId)) {
        $pearDB->query(
            "UPDATE cfg_resource SET resource_activate = '1' "
            . "WHERE resource_id = $resourceId"
        );
    }
}

/**
 * Disables a resource
 *
 * @global CentreonDB $pearDB
 * @param int $resourceId Resource id to disable
 */
function disableResourceInDB($resourceId)
{
    global $pearDB;
    if (is_int($resourceId)) {
        $pearDB->query(
            "UPDATE cfg_resource SET resource_activate = '0' "
            . "WHERE resource_id = $resourceId"
        );
    }
}
/**
 * Duplicates resource
 *
 * @global CentreonDB $pearDB
 * @param type $resourceIds List of resource id to duplicate
 * @param type $nbrDup Number of copy
 */
function multipleResourceInDB($resourceIds = array(), $nbrDup = array())
{
    global $pearDB;

    foreach (array_keys($resourceIds) as $resourceId) {
        if (is_int($resourceId)) {
            $dbResult = $pearDB->query("SELECT * FROM cfg_resource WHERE resource_id = $resourceId LIMIT 1");
            $resourceConfigurations = $dbResult->fetch();
            $resourceConfigurations["resource_id"] = null;
            for ($newIndex = 1; $newIndex <= $nbrDup[$resourceId]; $newIndex++) {
                $val = null;
                $resourceName = null;
                foreach ($resourceConfigurations as $cfgName => $cfgValue) {
                    if ($cfgName == 'resource_name') {
                        $cfgValue .= '_' . $newIndex;
                        $resourceName = $cfgValue;
                    }
                    if (is_null($val)) {
                        $val .= ($cfgValue == null)
                            ? 'NULL'
                            : "'" . $pearDB->escape($cfgValue) . "'";
                    } else {
                        $val .= ($cfgValue == null)
                            ? ', NULL'
                            : ", '" . $pearDB->escape($cfgValue) . "'";
                    }
                }
                if (testExistence($resourceName) && !is_null($val)) {
                    $pearDB->query(
                        "INSERT INTO cfg_resource VALUES ($val)"
                    );
                    $lastId = $pearDB->lastInsertId();
                    $pearDB->query(
                        "INSERT INTO cfg_resource_instance_relations ("
                        . "SELECT $lastId, instance_id "
                        . "FROM cfg_resource_instance_relations "
                        . "WHERE resource_id = $resourceId)"
                    );
                }
            }
        }
    }
}

function updateResourceInDB($resource_id = null)
{
    if (!$resource_id) {
        return;
    }
    updateResource((int) $resource_id);
    insertInstanceRelations((int) $resource_id);
}

/**
 * Updates a resource which is in the form
 *
 * @global HTML_QuickFormCustom $form
 * @global CentreonDB $pearDB
 * @global Centreon $centreon
 * @param int $resourceId
 */
function updateResource($resourceId)
{
    global $form, $pearDB, $centreon;

    if (is_null($resourceId)) {
        return;
    }

    $submitedValues = $form->getSubmitValues();

    $isActivate = false;
    if (
        isset($submitedValues["resource_activate"])
        && isset($submitedValues["resource_activate"]["resource_activate"])
        && $submitedValues["resource_activate"]["resource_activate"] == '1'
    ) {
        $isActivate = true;
    }

    $prepare = $pearDB->prepare(
        "UPDATE cfg_resource "
        . "SET resource_name = :resource_name, resource_line = :resource_line, "
        . "resource_comment= :resource_comment, resource_activate= :is_activate "
        . "WHERE resource_id = :resource_id"
    );

    $prepare->bindValue(
        ':resource_name',
        $pearDB->escape($submitedValues["resource_name"]),
        \PDO::PARAM_STR
    );

    $prepare->bindValue(
        ':resource_line',
        $pearDB->escape($submitedValues["resource_line"]),
        \PDO::PARAM_STR
    );

    $prepare->bindValue(
        ':resource_comment',
        $pearDB->escape($submitedValues["resource_comment"]),
        \PDO::PARAM_STR
    );

    $prepare->bindValue(
        ':is_activate',
        ($isActivate ? '1' : '0'),
        \PDO::PARAM_STR
    );

    $prepare->bindValue(':resource_id', $resourceId, \PDO::PARAM_INT);
    $prepare->execute();

    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($submitedValues);
    $centreon->CentreonLogAction->insertLog(
        'resource',
        $resourceId,
        CentreonDB::escape($submitedValues['resource_name']),
        'c',
        $fields
    );
}

function insertResourceInDB()
{
    $resource_id = insertResource();
    insertInstanceRelations($resource_id);
    return ($resource_id);
}

function insertResource($ret = array())
{
    global $form, $pearDB, $centreon;

    if (!count($ret)) {
        $ret = $form->getSubmitValues();
    }
    $rq = "INSERT INTO cfg_resource ";
    $rq .= "(resource_name, resource_line, resource_comment, resource_activate) ";
    $rq .= "VALUES (";
    isset($ret["resource_name"]) && $ret["resource_name"] != null
        ? $rq .= "'" . $pearDB->escape($ret["resource_name"]) . "', "
        : $rq .= "NULL, ";
    isset($ret["resource_line"]) && $ret["resource_line"] != null
        ? $rq .= "'" . $pearDB->escape($ret["resource_line"]) . "', "
        : $rq .= "NULL, ";
    isset($ret["resource_comment"]) && $ret["resource_comment"] != null
        ? $rq .= "'" . $pearDB->escape($ret["resource_comment"]) . "', "
        : $rq .= "NULL, ";
    isset($ret["resource_activate"]["resource_activate"]) && $ret["resource_activate"]["resource_activate"] != null
        ? $rq .= "'" . $ret["resource_activate"]["resource_activate"] . "'"
        : $rq .= "NULL";
    $rq .= ")";
    $pearDB->query($rq);
    $dbResult = $pearDB->query("SELECT MAX(resource_id) FROM cfg_resource");
    $resource_id = $dbResult->fetch();

    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($ret);
    $centreon->CentreonLogAction->insertLog(
        "resource",
        $resource_id["MAX(resource_id)"],
        CentreonDB::escape($ret["resource_name"]),
        "a",
        $fields
    );

    return ($resource_id["MAX(resource_id)"]);
}

function insertInstanceRelations($resourceId, $instanceId = null)
{
    if (is_numeric($resourceId)) {
        global $pearDB;
        $pearDB->query('DELETE FROM cfg_resource_instance_relations WHERE resource_id = ' . (int) $resourceId);

        if (! is_null($instanceId)) {
            $instances = array($instanceId);
        } else {
            global $form;
            $instances = CentreonUtils::mergeWithInitialValues($form, 'instance_id');
        }

        $subQuery = '';
        foreach ($instances as $instanceId) {
            if (is_numeric($instanceId)) {
                if (!empty($subQuery)) {
                    $subQuery .= ', ';
                }
                $subQuery .= '(' . (int)$resourceId . ', ' . (int)$instanceId . ')';
            }
        }
        if (!empty($subQuery)) {
            $pearDB->query(
                'INSERT INTO cfg_resource_instance_relations (resource_id, instance_id) VALUES ' . $subQuery
            );
        }
    }
}

function getLinkedPollerList($resource_id)
{
    global $pearDB;

    $str = "";
    $query = "SELECT ns.name, ns.id FROM cfg_resource_instance_relations nsr, cfg_resource r, nagios_server ns " .
        "WHERE nsr.resource_id = r.resource_id AND nsr.instance_id = ns.id AND nsr.resource_id = '" .
        $resource_id . "'";
    $dbResult = $pearDB->query($query);
    while ($data = $dbResult->fetch()) {
        $str .= "<a href='main.php?p=60901&o=c&server_id=" . $data["id"] . "'>" . $data["name"] . "</a> ";
    }
    unset($dbResult);
    return $str;
}
