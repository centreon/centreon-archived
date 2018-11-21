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

function testExistence($name = null, $instanceId = null)
{
    global $pearDB, $form;

    $id = 0;
    $instances = array();
    if (isset($form)) {
        $id = $form->getSubmitValue('resource_id');
        $instances = $form->getSubmitValue('instance_id');
    } elseif (!is_null($instanceId) && $instanceId) {
        $instances = array($instanceId);
    }
    $instances = array_filter($instances);
    if (!count($instances)) {
        return true;
    }
    $DBRESULT = $pearDB->query("SELECT cr.resource_name, crir.resource_id, crir.instance_id
                                FROM cfg_resource cr, cfg_resource_instance_relations crir
                                WHERE cr.resource_id = crir.resource_id
                                AND crir.instance_id IN (".implode(",", $instances).")
                                AND cr.resource_name = '".$pearDB->escape($name)."'");
    $res = $DBRESULT->fetchRow();
    if ($DBRESULT->numRows() >= 1 && $res["resource_id"] == $id) {
        return true;
    } elseif ($DBRESULT->numRows() >= 1 && $res["resource_id"] != $id) {
        return false;
    } else {
        return true;
    }
}

/**
 * Retrieve the smallest suffix available for this resource name from database
 *
 * @global CentreonDB $pearDB DB connector
 * @param string $resourceName Resource name to process
 * @param string $separator Character used to separate the resource name and suffix
 * @return int The smallest suffix available for this resource name
 * @throws Exception
 */
function getFirstAvailableSuffixId($resourceName, $separator = '_')
{
    global $pearDB;
    /**
     * To avoid that this colum value will be interpreted
     * like regular expression in getFirstAvailableSuffixId
     * function
     */
    $resourceName = preg_quote($resourceName);

    $resourceName = CentreonDB::escape($resourceName);
    $query = "SELECT CAST(SUBSTRING_INDEX(resource_name,'_',-1) AS INT) AS suffix "
        . "FROM cfg_resource WHERE resource_name REGEXP '^" . $resourceName. $separator . "[0-9]+$' "
        . "ORDER BY suffix";
    $results = $pearDB->query($query);
    $availableSuffix = array();
    while ($result = $results->fetchRow()) {
        $suffix = (int) $result['suffix'];
        if (!in_array($suffix, $availableSuffix)) {
            $availableSuffix[] = $suffix;
        }
    }
    // Get the biggest suffix found
    $biggestSuffix = $availableSuffix[count($availableSuffix) - 1];
    // Get the smaller suffix available
    $smallestSuffixAvailable = 1;
    for ($suffix = 1; $suffix <= $biggestSuffix + 1; $suffix++) {
        if (!in_array($suffix, $availableSuffix)) {
            $smallestSuffixAvailable = $suffix;
            break; // We found it, we can go out
        }
    }
    return $smallestSuffixAvailable;
}

/**
 * Delete a list of resource's id
 *
 * @global CentreonDB $pearDB DB connector
 * @param int[] $resourceIds
 * @throws Exception
 */
function deleteResourcesInDB($resourceIds = array())
{
    global $pearDB;

    $resourceIds = filter_var_array(
        $resourceIds,
        FILTER_VALIDATE_INT
    );

    if (in_array(false, $resourceIds)) {
        return;
    }

    foreach (array_keys($resourceIds) as $id) {
        $pearDB->query(
            'DELETE FROM cfg_resource WHERE resource_id = ' . ((int) $id)
        );
    }
}

/**
 * Enable a poller resource
 *
 * @global CentreonDB $pearDB DB connector
 * @param int $resourceId Id of the resource
 * @throws Exception
 */
function enableResourceInDB($resourceId = null)
{
    global $pearDB;

    if (!is_int($resourceId)) {
        return;
    }

    $pearDB->query(
        'UPDATE cfg_resource SET resource_activate = \'1\' WHERE resource_id = ' . ((int) $resourceId)
    );
}

/**
 * Disable a poller resource
 *
 * @global CentreonDB $pearDB DB connector
 * @param int $resourceId Id of the resource
 * @throws Exception
 */
function disableResourceInDB($resourceId = null)
{
    global $pearDB;
    if (!is_int($resourceId)) {
        return;
    }
    $pearDB->query(
        'UPDATE cfg_resource SET resource_activate = \'0\' WHERE resource_id = '
        . ((int) $resourceId)
    );
}

/**
 * Duplicate resources
 *
 * @param int[] $resourceIds List of resource id to duplicate
 * @param int[] $nbrDup Number of copy
 * @throws Exception
 */
function duplicateResources($resourceIds = array(), $nbrDup = array())
{
    global $pearDB;

    $resourceIds = filter_var_array(
        $resourceIds,
        FILTER_VALIDATE_INT
    );
    if (in_array(false, $resourceIds)) {
        return;
    }

    $nbrDup = filter_var_array(
        $nbrDup,
        FILTER_VALIDATE_INT
    );
    if (in_array(false, $nbrDup)) {
        return;
    }

    foreach (array_keys($resourceIds) as $resourceId) {
        $results = $pearDB->query(
            'SELECT * FROM cfg_resource WHERE resource_id = '
            . ((int) $resourceId) . ' LIMIT 1'
        );
        $row = $results->fetchRow();
        if (count($row)) {
            $row["resource_id"] = '';
            for ($i = 1; $i <= $nbrDup[$resourceId]; $i++) {
                $queryValues = null;
                $resourceName = null;
                foreach ($row as $columName => $columValue) {
                    if ($columName === "resource_name") {
                        $columValue .= '_' . getFirstAvailableSuffixId($columValue);
                        $resourceName = CentreonDB::escape($columValue);
                    } else {
                        $columValue = CentreonDB::escape($columValue);
                    }

                    if (is_null($queryValues)) {
                        $queryValues .= ($columValue != null
                            ? ("'" . $columValue . "'")
                            : "NULL");
                    } else {
                        $queryValues .= ($columValue != null
                            ? (", '" . $columValue . "'")
                            : ", NULL");
                    }
                }

                if (!is_null($queryValues) && testExistence($resourceName)) {
                    $pearDB->query(
                        'INSERT INTO cfg_resource VALUES (' . $queryValues . ')'
                    );
                }
            }
        }
    }
}

/**
 * Update a poller resource
 *
 * @param int $resourceId Id of the resource
 * @see updateResource() Update a resource
 * @see insertInstanceRelations() Create relation between resource and poller
 * @throws Exception
 */
function updateResourceInDB($resourceId = null)
{
    if (!is_int($resourceId)) {
        return;
    }

    updateResource($resourceId);
    insertInstanceRelations($resourceId);
}

/**
 * Update a resource
 *
 * @global HTML_QuickForm $form
 * @global CentreonDB $pearDB
 * @global Centreon $centreon
 * @param int $resourceId Id of the resource
 * @throws Exception
 */
function updateResource($resourceId)
{
    global $form, $pearDB, $centreon;

    if (!is_int($resourceId)) {
        return;
    }
    $ret = $form->getSubmitValues();
    $rq = "UPDATE cfg_resource "
        . "SET resource_name = '" . $pearDB->escape($ret["resource_name"]) . "', "
        . "resource_line = '" . $pearDB->escape($ret["resource_line"]) . "', "
        . "resource_comment= '" . $pearDB->escape($ret["resource_comment"]) . "', "
        . "resource_activate= '" . $ret["resource_activate"]["resource_activate"] . "' "
        . 'WHERE resource_id = ' . ((int) $resourceId);
    $DBRESULT = $pearDB->query($rq);

    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($ret);
    $centreon->CentreonLogAction->insertLog(
        "resource",
        $resourceId,
        CentreonDB::escape($ret["resource_name"]),
        "c",
        $fields
    );
}

/**
 * Insert a resource in database
 *
 * @return int Id of the new resource
 * @throws Exception
 */
function insertResourceInDB()
{
    $resourceId = insertResource();
    insertInstanceRelations($resourceId);
    return $resourceId;
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
    isset($ret["resource_name"]) && $ret["resource_name"] != null ? $rq .= "'".$pearDB->escape($ret["resource_name"])."', " : $rq .= "NULL, ";
    isset($ret["resource_line"]) && $ret["resource_line"] != null ? $rq .= "'".$pearDB->escape($ret["resource_line"])."', " : $rq .= "NULL, ";
    isset($ret["resource_comment"]) && $ret["resource_comment"] != null ? $rq .= "'".$pearDB->escape($ret["resource_comment"])."', " : $rq .= "NULL, ";
    isset($ret["resource_activate"]["resource_activate"]) && $ret["resource_activate"]["resource_activate"] != null ? $rq .= "'".$ret["resource_activate"]["resource_activate"]."'" : $rq .= "NULL";
    $rq .= ")";
    $DBRESULT = $pearDB->query($rq);
    $DBRESULT = $pearDB->query("SELECT MAX(resource_id) FROM cfg_resource");
    $resource_id = $DBRESULT->fetchRow();

    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($ret);
    $centreon->CentreonLogAction->insertLog("resource", $resource_id["MAX(resource_id)"], CentreonDB::escape($ret["resource_name"]), "a", $fields);

    return (int) ($resource_id["MAX(resource_id)"]);
}

/**
 * Create relation between resource and poller
 *
 * @global HTML_QuickForm $form
 * @global CentreonDB $pearDB
 * @param int $resourceId Id of the resource
 * @param int|null $instanceId Id of the poller
 * @throws Exception
 */
function insertInstanceRelations($resourceId, $instanceId = null)
{
    global $form, $pearDB;

    if (!is_int($resourceId)) {
        return;
    }

    if (!is_null($instanceId) && !is_int($instanceId)) {
        return;
    }

    $pearDB->query(
        'DELETE FROM cfg_resource_instance_relations WHERE resource_id = ' . $resourceId
    );
    $query = 'INSERT INTO cfg_resource_instance_relations (resource_id, instance_id) VALUES ';

    if (!is_null($instanceId)) {
        $instances = array($instanceId);
    } else {
        $instances = filter_var_array(
            CentreonUtils::mergeWithInitialValues($form, 'instance_id'),
            FILTER_VALIDATE_INT
        );
        if (in_array(false, $instances)) {
            return;
        }
    }
    $queryValues = '';
    foreach ($instances as $instanceId) {
        if (!empty($queryValues)) {
            $queryValues .= ', ';
        }
        $queryValues .= '(' . $resourceId .", " . $instanceId . ')';
    }
    if ($queryValues) {
        $pearDB->query($query . $queryValues);
    }
}

/**
 * Returns all pollers HTML links for a given resource
 *
 * @global CentreonDB @pearDB DB connector
 * @param int $resourceId Id of the resource
 * @return string Return all pollers HTML links
 * @throws Exception
 */
function getLinkedPollerList($resourceId)
{
    global $pearDB;

    $htmlLinks = "";

    if (is_int($resourceId)) {
        return $htmlLinks;
    }

    $DBRESULT = $pearDB->query(
        'SELECT ns.name, ns.id '
        . 'FROM cfg_resource_instance_relations nsr, cfg_resource r, nagios_server ns '
        . 'WHERE nsr.resource_id = r.resource_id '
        . 'AND nsr.instance_id = ns.id '
        . 'AND nsr.resource_id = ' . ((int) $resourceId)
    );

    while ($data = $DBRESULT->fetchRow()) {
        $htmlLinks .= "<a href='main.php?p=60901&o=c&server_id=".$data["id"]."'>".$data["name"]."</a> ";
    }

    unset($DBRESULT);
    return $htmlLinks;
}
