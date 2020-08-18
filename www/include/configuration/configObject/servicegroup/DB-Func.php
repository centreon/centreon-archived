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

function testServiceGroupExistence($name = null)
{
    global $pearDB, $form, $centreon;

    $id = null;

    if (isset($form)) {
        $id = $form->getSubmitValue('sg_id');
    }
    $sgName = filter_var($name, FILTER_SANITIZE_STRING);


    $statement = $pearDB->prepare("SELECT sg_name, sg_id FROM servicegroup WHERE sg_name = ?");
    $result = $pearDB->execute($statement, array($sgName));
    $sg = $result->fetchRow();
    if ($result->numRows() >= 1 && $sg["sg_id"] !== $id) {
        return false;
    } else {
        return true;
    }
}

function enableServiceGroupInDB($sgId = null)
{
    if (!$sgId) {
        return;
    }
    global $pearDB, $centreon;

    $sgId = filter_var($sgId, FILTER_VALIDATE_INT);
    $statement = $pearDB->prepare("UPDATE servicegroup SET sg_activate = '1' WHERE sg_id = ?");
    $pearDB->execute($statement, array($sgId));
    $statement2 = $pearDB->prepare("SELECT sg_name FROM `servicegroup` WHERE `sg_id` = ? LIMIT 1");
    $result = $pearDB->execute($statement2, array($sgId));
    $row = $result->fetchRow();
    $centreon->CentreonLogAction->insertLog("servicegroup", $sgId, $row['sg_name'], "enable");
}

function disableServiceGroupInDB($sgId = null)
{
    if (!$sgId) {
        return;
    }
    global $pearDB, $centreon;

    $sgId = filter_var($sgId, FILTER_VALIDATE_INT);
    $statement = $pearDB->prepare("UPDATE servicegroup SET sg_activate = '0' WHERE sg_id = ?");
    $pearDB->execute($statement, array($sgId));
    $statement2 = $pearDB->prepare("SELECT sg_name FROM `servicegroup` WHERE `sg_id` = ? LIMIT 1");
    $result = $pearDB->execute($statement2, array($sgId));
    $row = $result->fetchRow();
    $centreon->CentreonLogAction->insertLog("servicegroup", $sgId, $row['sg_name'], "disable");
}

function deleteServiceGroupInDB($serviceGroups = array())
{
    global $pearDB, $centreon;

    $statement = $pearDB->prepare("SELECT sg_name FROM `servicegroup` WHERE `sg_id` = ? LIMIT 1");
    $statement2 = $pearDB->prepare("DELETE FROM servicegroup WHERE sg_id = ? ");

    foreach ($serviceGroups as $key => $value) {
        $sgId = filter_var($key, FILTER_VALIDATE_INT);
        $result = $pearDB->execute($statement, array($sgId));
        $row = $result->fetchRow();
        $pearDB->execute($statement2, array($sgId));
        $centreon->CentreonLogAction->insertLog("servicegroup", $key, $row['sg_name'], "d");
    }
    $centreon->user->access->updateACL();
}

function multipleServiceGroupInDB($serviceGroups = array(), $nbrDup = array())
{
    global $pearDB, $centreon;

    $sgAcl = array();
    $statement = $pearDB->prepare("SELECT * FROM servicegroup WHERE sg_id = ? LIMIT 1");
    foreach ($serviceGroups as $key => $value) {
        $sgId = filter_var($key, FILTER_VALIDATE_INT);
        $result = $pearDB->execute($statement, array($sgId));
        $row = $result->fetchRow();
        $row["sg_id"] = '';
        for ($i = 1; $i <= $nbrDup[$sgId]; $i++) {
            $val = null;
            $rq = null;
            $params = array();
            foreach ($row as $key2 => $value2) {
                switch ($key2) {
                    case 'sg_name':
                        $value2 = filter_var($value2, FILTER_SANITIZE_STRING);
                        $sgName = $value2 = $value2 . "_" . $i;
                        $params[] = $value2;
                        break;
                    case 'sg_alias':
                        $value2 = filter_var($value2, FILTER_SANITIZE_STRING);
                        $params[] = $value2;
                        break;
                    case 'sg_comment':
                        $value2 = filter_var($value2, FILTER_SANITIZE_STRING);
                        $value2
                            ? $params[] = $value2
                            : $params[] = null;
                        break;
                    case 'geo_coords':
                        $value2 = filter_var($value2, FILTER_VALIDATE_REGEXP, [
                            "options" => [
                                "regexp" => "/^(-?\d+(\.\d+)?),\s*(-?\d+(\.\d+)?)$/"
                            ]
                        ]);
                        $value2
                            ? $params[] = $value2
                            : $params[] =  null;
                        break;
                    case 'sg_activate':
                        $value2 = filter_var($value2, FILTER_VALIDATE_REGEXP, [
                            "options" => [
                                "regexp" => "/^0|1$/"
                            ]
                        ]);
                        $value2
                            ? $params[] = $value2
                            : $params[] = "0";
                        break;
                }
                $key2 == "sg_name" ? ($sgName = $value2 = $value2 . "_" . $i) : null;
                $val
                    ? $val .= ", ?"
                    : $val .= "NULL";
                if ($key2 != "sg_id") {
                    $fields[$key2] = $value2;
                }
                if (isset($sgName)) {
                    $fields["sg_name"] = $sgName;
                }
            }
            if (testServiceGroupExistence($sgName)) {
                if ($val) {
                    $statement2 = $pearDB->prepare("INSERT INTO servicegroup VALUES (" . $val . ")");
                    $pearDB->execute($statement2, $params);
                }
                $DBRESULT = $pearDB->query("SELECT MAX(sg_id) FROM servicegroup");
                $maxId = $DBRESULT->fetchRow();
                if (isset($maxId["MAX(sg_id)"])) {
                    $sgAcl[$maxId["MAX(sg_id)"]] = $key;
                    $DBRESULT->free();
                    $statement3 = $pearDB->prepare("
                        SELECT DISTINCT sgr.host_host_id, sgr.hostgroup_hg_id, sgr.service_service_id
                        FROM servicegroup_relation sgr WHERE sgr.servicegroup_sg_id = ?");
                    $result = $pearDB->execute($statement3, array($sgId));
                    $fields["sg_hgServices"] = "";
                    while ($service = $result->fetchRow()) {
                        $params = array();
                        foreach ($service as $key2 => $value2) {
                            switch ($key2) {
                                case 'host_host_id':
                                    $value2 = filter_var($value2, FILTER_VALIDATE_INT);
                                    $value2
                                        ? $params[] = $value2
                                        : $params[] = null;
                                    break;
                                case 'hostgroup_hg_id':
                                    $value2 = filter_var($value2, FILTER_VALIDATE_INT);
                                    $value2
                                        ? $params[] = $value2
                                        : $params[] = null;
                                    break;
                                case 'service_service_id':
                                    $value2 = filter_var($value2, FILTER_VALIDATE_INT);
                                    $value2
                                        ? $params[] = $value2
                                        : $params[] = null;
                                    break;
                            }
                        }
                        $params[] = $maxId["MAX(sg_id)"];
                        $statement4 = $pearDB->prepare("
                            INSERT INTO servicegroup_relation
                            (host_host_id, hostgroup_hg_id, service_service_id, servicegroup_sg_id)
                            VALUES (?, ?, ?, ?)
                        ");
                        $pearDB->execute($statement4, $params);
                        $fields["sg_hgServices"] .= $service["service_service_id"] . ",";
                    }
                    $fields["sg_hgServices"] = trim($fields["sg_hgServices"], ",");
                    $centreon->CentreonLogAction->insertLog(
                        "servicegroup",
                        $maxId["MAX(sg_id)"],
                        $sgName,
                        "a",
                        $fields
                    );
                }
            }
        }
    }
    CentreonACL::duplicateSgAcl($sgAcl);
    $centreon->user->access->updateACL();
}

function insertServiceGroupInDB($ret = array())
{
    global $centreon;

    $sgId = insertServiceGroup($ret);
    updateServiceGroupServices($sgId, $ret);
    $centreon->user->access->updateACL();
    return $sgId;
}

function updateServiceGroupInDB($sgId = null, $ret = array(), $increment = false)
{
    global $centreon;

    if (!$sgId) {
        return;
    }
    updateServiceGroup($sgId, $ret);
    updateServiceGroupServices($sgId, $ret, $increment);
    $centreon->user->access->updateACL();
}

function insertServiceGroup($ret = array())
{
    global $form, $pearDB, $centreon;
    if (!count($ret)) {
        $ret = $form->getSubmitValues();
    }

    $params = array();
    foreach ($ret as $key => $value) {
        switch ($key) {
            case 'sg_name':
                $value = filter_var($value, FILTER_SANITIZE_STRING);
                $params[] = $value;
                break;
            case 'sg_alias':
                $value = filter_var($value, FILTER_SANITIZE_STRING);
                $params[] = $value;
                break;
            case 'sg_comment':
                $value = filter_var($value, FILTER_SANITIZE_STRING);
                $value
                    ? $params[] = $value
                    : $params[] = null;
                break;
            case 'geo_coords':
                $value = filter_var($value, FILTER_VALIDATE_REGEXP, [
                    "options" => [
                        "regexp" => "/^(-?\d+(\.\d+)?),\s*(-?\d+(\.\d+)?)$/"
                    ]
                ]);
                $value
                    ? $params[] = $value
                    : $params[] = null;
                break;
            case 'sg_activate':
                $value = filter_var($value['sg_activate'], FILTER_VALIDATE_REGEXP, [
                    "options" => [
                        "regexp" => "/^0|1$/"
                    ]
                ]);
                $value
                    ? $params[] = $value
                    : $params[] = "0";
                break;
        }
    }
    $statement = $pearDB->prepare("
        INSERT INTO servicegroup (sg_name, sg_alias, geo_coords, sg_activate, sg_comment)
        VALUES (?, ?, ?, ?, ?)
    ");
    $pearDB->execute($statement, $params);

    $result = $pearDB->query("SELECT MAX(sg_id) FROM servicegroup");
    $sgId = $result->fetchRow();
    $result->free();

    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($ret);
    $centreon->CentreonLogAction->insertLog(
        "servicegroup",
        $sgId["MAX(sg_id)"],
        htmlentities($ret["sg_name"], ENT_QUOTES, "UTF-8"),
        "a",
        $fields
    );

    return ($sgId["MAX(sg_id)"]);
}

function updateServiceGroup($sgId, $ret = array())
{
    global $form, $pearDB, $centreon;

    if (!$sgId) {
        return;
    }

    if (!count($ret)) {
        $ret = $form->getSubmitValues();
    }
    $params = array();
    $sgId = filter_var($sgId, FILTER_VALIDATE_INT);
    foreach ($ret as $key => $value) {
        switch ($key) {
            case 'sg_name':
                $value = filter_var($value, FILTER_SANITIZE_STRING);
                $params[] = $value;
                break;
            case 'sg_alias':
                $value = filter_var($value, FILTER_SANITIZE_STRING);
                $params[] = $value;
                break;
            case 'sg_comment':
                $value = filter_var($value, FILTER_SANITIZE_STRING);
                $value
                    ? $params[] = $value
                    : $params[] = null;
                break;
            case 'geo_coords':
                $value = filter_var($value, FILTER_VALIDATE_REGEXP, [
                    "options" => [
                        "regexp" => "/^(-?\d+(\.\d+)?),\s*(-?\d+(\.\d+)?)$/"
                    ]
                ]);
                $value
                    ? $params[] = $value
                    : $params[] = null;
                break;
            case 'sg_activate':
                $value = filter_var($value['sg_activate'], FILTER_VALIDATE_REGEXP, [
                    "options" => [
                        "regexp" => "/^0|1$/"
                    ]
                ]);
                $value
                    ? $params[] = $value
                    : $params[] = "0";
                break;
        }
    }
    $params[] = $sgId;
    $statement = $pearDB->prepare("
        UPDATE servicegroup SET
        sg_name = ?,
        sg_alias = ?,
        geo_coords = ?,
        sg_activate = ?,
        sg_comment = ?
        WHERE sg_id = ?
    ");
    $pearDB->execute($statement, $params);
    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($ret);
    $centreon->CentreonLogAction->insertLog(
        "servicegroup",
        $sgId,
        htmlentities($ret["sg_name"], ENT_QUOTES, "UTF-8"),
        "c",
        $fields
    );
}

function updateServiceGroupServices($sgId, $ret = array(), $increment = false)
{
    if (!$sgId) {
        return;
    }
    global $pearDB, $form;


    $sgId = filter_var($sgId, FILTER_VALIDATE_INT);
    if ($increment == false && $sgId !== false) {
        $statement = $pearDB->prepare("DELETE FROM servicegroup_relation WHERE servicegroup_sg_id = ?");
        $pearDB->execute($statement, $sgId);
    }

    /* service templates */
    $retTmp = isset($ret["sg_tServices"]) ? $ret["sg_tServices"] : $form->getSubmitValue("sg_tServices");
    $statement = $pearDB->prepare("
        SELECT servicegroup_sg_id service FROM servicegroup_relation
        WHERE host_host_id = ? AND service_service_id = ?
        AND servicegroup_sg_id = ?
    ");
    $statement2 = $pearDB->prepare("
        INSERT INTO servicegroup_relation (host_host_id, service_service_id, servicegroup_sg_id)
        VALUES (?, ?, ?)
    ");

    updateServicegroupRelations($statement, $statement2, $retTmp, $sgId);
    /* regular services */
    $retTmp = isset($ret["sg_hServices"])
        ? $ret["sg_hServices"]
        : CentreonUtils::mergeWithInitialValues($form, 'sg_hServices');

    updateServicegroupRelations($statement, $statement2, $retTmp, $sgId);


    $statement = $pearDB->prepare("
        SELECT servicegroup_sg_id service FROM servicegroup_relation
        WHERE hostgroup_hg_id = ? AND service_service_id = ?
        AND servicegroup_sg_id = ?
    ");
    $statement2 = $pearDB->prepare("
        INSERT INTO servicegroup_relation (hostgroup_hg_id, service_service_id, servicegroup_sg_id)
        VALUES (?, ?, ?)
    ");
    /* hostgroup services */
    $retTmp = isset($ret["sg_hgServices"])
        ? $ret["sg_hgServices"]
        : CentreonUtils::mergeWithInitialValues($form, 'sg_hgServices');

    updateServicegroupRelations($statement, $statement2, $retTmp, $sgId);
}

/**
 * Avoid code repetition in updateServiceGroupServices
 */
function updateServicegroupRelations($statement, $statement2, $retTmp, $sgId)
{
    global $pearDB;
    for ($i = 0; $i < count($retTmp); $i++) {
        $params = array();
        if (isset($retTmp[$i]) && $retTmp[$i]) {
            $t = preg_split("/\-/", $retTmp[$i]);
            $hostOrHostGroupId = filter_var($t[0], FILTER_VALIDATE_INT);
            $serviceServiceId = filter_var($t[1], FILTER_VALIDATE_INT);
            array_push($params, $hostOrHostGroupId, $serviceServiceId, $sgId);
            $result = $pearDB->execute($statement, $params);
            if (!$result->numRows()) {
                $pearDB->execute($statement2, $params);
            }
        }
    }
}
