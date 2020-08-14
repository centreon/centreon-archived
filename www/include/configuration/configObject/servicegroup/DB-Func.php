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

    $statement = $pearDB->prepare("SELECT sg_name, sg_id FROM servicegroup WHERE sg_name = :sg_name");
    $statement->bindValue(':sg_name', $sgName, \PDO::PARAM_STR);
    $statement->execute();
    $sg = $statement->fetch();
    if ($statement->rowCount() >= 1 && $sg["sg_id"] != $id) {
        return false;
    } else {
        return true;
    }
}

function enableServiceGroupInDB($sg_id = null)
{
    if (!$sg_id) {
        return;
    }

    global $pearDB, $centreon;

    $sgId = filter_var($sg_id, FILTER_VALIDATE_INT);

    $statement = $pearDB->prepare("UPDATE servicegroup SET sg_activate = '1' WHERE sg_id = :sg_id");
    $statement->bindValue(':sg_id', $sgId, \PDO::PARAM_INT);
    $statement->execute();

    $statement2 = $pearDB->prepare("SELECT sg_name FROM `servicegroup` WHERE `sg_id` = :sg_id LIMIT 1");
    $statement2->bindValue(':sg_id', $sgId, \PDO::PARAM_INT);
    $statement2->execute();
    $row = $statement2->fetch();
    $centreon->CentreonLogAction->insertLog("servicegroup", $sg_id, $row['sg_name'], "enable");
}

function disableServiceGroupInDB($sg_id = null)
{
    if (!$sg_id) {
        return;
    }
    global $pearDB, $centreon;

    $sgId = filter_var($sg_id, FILTER_VALIDATE_INT);

    $statement = $pearDB->prepare("UPDATE servicegroup SET sg_activate = '0' WHERE sg_id = :sg_id");
    $statement->bindValue(':sg_id', $sgId, \PDO::PARAM_INT);
    $statement->execute();

    $statement2 = $pearDB->prepare("SELECT sg_name FROM `servicegroup` WHERE `sg_id` = :sg_id LIMIT 1");
    $statement2->bindValue(':sg_id', $sgId, \PDO::PARAM_INT);
    $statement2->execute();
    $row = $statement2->fetch();
    $centreon->CentreonLogAction->insertLog("servicegroup", $sg_id, $row['sg_name'], "disable");
}

function deleteServiceGroupInDB($serviceGroups = [])
{
    global $pearDB, $centreon;

    foreach (array_keys($serviceGroups) as $key) {
        $sgId = filter_var($key, FILTER_VALIDATE_INT);
        $statement = $pearDB->prepare("SELECT sg_name FROM `servicegroup` WHERE `sg_id` = :sg_id LIMIT 1");
        $statement->bindValue(':sg_id', $sgId, \PDO::PARAM_INT);
        $statement->execute();
        $row = $statement->fetch();

        $statement2 = $pearDB->prepare("DELETE FROM servicegroup WHERE sg_id = :sg_id");
        $statement2->bindValue(':sg_id', $sgId, \PDO::PARAM_INT);
        $statement2->execute();
        $centreon->CentreonLogAction->insertLog("servicegroup", $key, $row['sg_name'], "d");
    }
    $centreon->user->access->updateACL();
}

function multipleServiceGroupInDB($serviceGroups = [], $nbrDup = [])
{
    global $pearDB, $centreon, $is_admin;

    $sgAcl = [];
    foreach (array_keys($serviceGroups) as $key) {
        $sgId = filter_var($key, FILTER_VALIDATE_INT);

        $statement = $pearDB->prepare("SELECT * FROM servicegroup WHERE sg_id = :sg_id LIMIT 1");
        $statement->bindValue(':sg_id', $sgId, \PDO::PARAM_INT);
        $statement->execute();
        $row = $statement->fetch();

        $row["sg_id"] = null;

        for ($i = 1; $i <= $nbrDup[$key]; $i++) {
            $val = null;
            $bindParams = [];
            foreach ($row as $key2 => $value2) {
                switch ($key2) {
                    case 'sg_name':
                        $value2 = filter_var($value2, FILTER_SANITIZE_STRING);
                        $sg_name = $value2 = $value2 . "_" . $i;
                        $bindParams[':sg_name'] = [\PDO::PARAM_STR => $value2];
                        break;
                    case 'sg_alias':
                        $value2 = filter_var($value2, FILTER_SANITIZE_STRING);
                        $bindParams[':sg_alias'] = [\PDO::PARAM_STR => $value2];
                        break;
                    case 'sg_comment':
                        $value2 = filter_var($value2, FILTER_SANITIZE_STRING);
                        $value2
                            ? $bindParams[':sg_comment'] = [\PDO::PARAM_STR => $value2]
                            : $bindParams[':sg_comment'] = [\PDO::PARAM_NULL => null];
                        break;
                    case 'geo_coords':
                        $value2 = filter_var($value2, FILTER_VALIDATE_REGEXP, [
                            "options" => [
                                "regexp" => "/^(-?\d+(\.\d+)?),\s*(-?\d+(\.\d+)?)$/"
                            ]
                        ]);
                        $value2
                            ? $bindParams[':geo_coords'] = [\PDO::PARAM_STR => $value2]
                            : $bindParams[':geo_coords'] = [\PDO::PARAM_NULL => null];
                        break;
                    case 'sg_activate':
                        $value2 = filter_var($value2, FILTER_VALIDATE_REGEXP, [
                            "options" => [
                                "regexp" => "/^0|1$/"
                            ]
                        ]);
                        $value2
                            ? $bindParams[':sg_activate'] = [\PDO::PARAM_STR => $value2]
                            : $bindParams[':sg_activate'] = [\PDO::PARAM_STR => "0"];
                        break;
                }
                $val
                    ? $val .= ($value2 != null ? (", '" . $value2 . "'") : ", NULL")
                    : $val .= ($value2 != null ? ("'" . $value2 . "'") : "NULL");
                if ($key2 != "sg_id") {
                    $fields[$key2] = $value2;
                }
                if (isset($sg_name)) {
                    $fields["sg_name"] = $sg_name;
                }
            }
            if (testServiceGroupExistence($sg_name)) {
                $val
                    ? $query = "
                        INSERT INTO servicegroup
                        VALUES (NULL, :sg_name, :sg_alias, :sg_comment, :geo_coords, :sg_activate)"
                    : $query = null;
                $statement = $pearDB->prepare($query);
                foreach ($bindParams as $token => $bindValues) {
                    foreach ($bindValues as $paramType => $value) {
                        $statement->bindValue($token, $value, $paramType);
                    }
                }
                $statement->execute();

                $dbResult = $pearDB->query("SELECT MAX(sg_id) FROM servicegroup");
                $maxId = $dbResult->fetch();
                if (isset($maxId["MAX(sg_id)"])) {
                    $sgAcl[$maxId["MAX(sg_id)"]] = $sgId;
                    $dbResult->closeCursor();
                    $query = "
                        SELECT DISTINCT sgr.host_host_id, sgr.hostgroup_hg_id, sgr.service_service_id
                        FROM servicegroup_relation sgr WHERE sgr.servicegroup_sg_id = :sg_id";
                    $statement = $pearDB->prepare($query);
                    $statement->bindValue(':sg_id', $sgId, \PDO::PARAM_INT);
                    $statement->execute();
                    $fields["sg_hgServices"] = "";
                    while ($service = $statement->fetch()) {
                        $val = null;
                        $bindParams = [];
                        foreach ($service as $key2 => $value2) {
                            switch ($key2) {
                                case 'host_host_id':
                                    $value2 = filter_var($value2, FILTER_VALIDATE_INT);
                                    $value2
                                        ? $bindParams[':host_host_id'] = [\PDO::PARAM_INT => $value2]
                                        : $bindParams[':host_host_id'] = [\PDO::PARAM_NULL => null];
                                    break;
                                case 'hostgroup_hg_id':
                                    $value2 = filter_var($value2, FILTER_VALIDATE_INT);
                                    $value2
                                        ? $bindParams[':hostgroup_hg_id'] = [\PDO::PARAM_INT => $value2]
                                        : $bindParams[':hostgroup_hg_id'] = [\PDO::PARAM_NULL => null];
                                    break;
                                case 'service_service_id':
                                    $value2 = filter_var($value2, FILTER_VALIDATE_INT);
                                    $value2
                                        ? $bindParams[':service_service_id'] = [\PDO::PARAM_INT => $value2]
                                        : $bindParams[':service_service_id'] = [\PDO::PARAM_NULL => null];
                                    break;
                            }
                            $bindParams[':servicegroup_sg_id'] = [\PDO::PARAM_INT => $maxId["MAX(sg_id)"]];
                            $val
                                ? $val .= ($value2 != null ? (", '" . $value2 . "'") : ", NULL")
                                : $val .= ($value2 != null ? ("'" . $value2 . "'") : "NULL");
                        }
                        $query = "
                            INSERT INTO servicegroup_relation
                            (host_host_id, hostgroup_hg_id, service_service_id, servicegroup_sg_id)
                            VALUES (:host_host_id, :hostgroup_hg_id, :service_service_id, :servicegroup_sg_id)";
                        $statement2 = $pearDB->prepare($query);
                        foreach ($bindParams as $token => $bindValues) {
                            foreach ($bindValues as $paramType => $value) {
                                $statement2->bindValue($token, $value, $paramType);
                            }
                        }
                        $statement2->execute();
                        $fields["sg_hgServices"] .= $service["service_service_id"] . ",";
                    }
                    $fields["sg_hgServices"] = trim($fields["sg_hgServices"], ",");
                    $centreon->CentreonLogAction->insertLog(
                        "servicegroup",
                        $maxId["MAX(sg_id)"],
                        $sg_name,
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

function insertServiceGroupInDB($ret = [])
{
    global $centreon;

    $sg_id = insertServiceGroup($ret);
    updateServiceGroupServices($sg_id, $ret);
    $centreon->user->access->updateACL();
    return $sg_id;
}

function updateServiceGroupInDB($sg_id = null, $ret = [], $increment = false)
{
    global $centreon;

    if (!$sg_id) {
        return;
    }
    updateServiceGroup($sg_id, $ret);
    updateServiceGroupServices($sg_id, $ret, $increment);
    $centreon->user->access->updateACL();
}

function insertServiceGroup($ret = [])
{
    global $form, $pearDB, $centreon;
    if (!count($ret)) {
        $ret = $form->getSubmitValues();
    }
    $bindParams = [];
    foreach ($ret as $key => $value) {
        switch ($key) {
            case 'sg_name':
                $value = filter_var($value, FILTER_SANITIZE_STRING);
                $bindParams[':sg_name'] = [\PDO::PARAM_STR => $value];
                break;
            case 'sg_alias':
                $value = filter_var($value, FILTER_SANITIZE_STRING);
                $bindParams[':sg_alias'] = [\PDO::PARAM_STR => $value];
                break;
            case 'sg_comment':
                $value = filter_var($value, FILTER_SANITIZE_STRING);
                $value
                    ? $bindParams[':sg_comment'] = [\PDO::PARAM_STR => $value]
                    : $bindParams[':sg_comment'] = [\PDO::PARAM_NULL => null];
                break;
            case 'geo_coords':
                $value = filter_var($value, FILTER_VALIDATE_REGEXP, [
                    "options" => [
                        "regexp" => "/^(-?\d+(\.\d+)?),\s*(-?\d+(\.\d+)?)$/"
                    ]
                ]);
                $value
                    ? $bindParams[':geo_coords'] = [\PDO::PARAM_STR => $value]
                    : $bindParams[':geo_coords'] = [\PDO::PARAM_NULL => null];
                break;
            case 'sg_activate':
                $value = filter_var($value['sg_activate'], FILTER_VALIDATE_REGEXP, [
                    "options" => [
                        "regexp" => "/^0|1$/"
                    ]
                ]);
                $value
                    ? $bindParams[':sg_activate'] = [\PDO::PARAM_STR => $value]
                    : $bindParams[':sg_activate'] = [\PDO::PARAM_STR => "0"];
                break;
        }
    }
    $query = "
        INSERT INTO servicegroup (sg_name, sg_alias, sg_comment, geo_coords, sg_activate)
        VALUES (:sg_name, :sg_alias, :sg_comment, :geo_coords, :sg_activate)";
    $statement = $pearDB->prepare($query);
    foreach ($bindParams as $token => $bindValues) {
        foreach ($bindValues as $paramType => $value) {
            $statement->bindValue($token, $value, $paramType);
        }
    }
    $statement->execute();

    $dbResult = $pearDB->query("SELECT MAX(sg_id) FROM servicegroup");
    $sg_id = $dbResult->fetch();
    $dbResult->closeCursor();

    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($ret);
    $centreon->CentreonLogAction->insertLog(
        "servicegroup",
        $sg_id["MAX(sg_id)"],
        htmlentities($ret["sg_name"], ENT_QUOTES, "UTF-8"),
        "a",
        $fields
    );

    return ($sg_id["MAX(sg_id)"]);
}

function updateServiceGroup($sg_id, $ret = [])
{
    global $form, $pearDB, $centreon;

    if (!$sg_id) {
        return;
    }

    if (!count($ret)) {
        $ret = $form->getSubmitValues();
    }

    $bindParams = [];
    $sgId = filter_var($sg_id, FILTER_VALIDATE_INT);
    $bindParams[':sg_id'] = [\PDO::PARAM_INT => $sgId];
    foreach ($ret as $key => $value) {
        switch ($key) {
            case 'sg_name':
                $value = filter_var($value, FILTER_SANITIZE_STRING);
                $bindParams[':sg_name'] = [\PDO::PARAM_STR => $value];
                break;
            case 'sg_alias':
                $value = filter_var($value, FILTER_SANITIZE_STRING);
                $bindParams[':sg_alias'] = [\PDO::PARAM_STR => $value];
                break;
            case 'sg_comment':
                $value = filter_var($value, FILTER_SANITIZE_STRING);
                $value
                    ? $bindParams[':sg_comment'] = [\PDO::PARAM_STR => $value]
                    : $bindParams[':sg_comment'] = [\PDO::PARAM_NULL => null];
                break;
            case 'geo_coords':
                $value = filter_var($value, FILTER_VALIDATE_REGEXP, [
                    "options" => [
                        "regexp" => "/^(-?\d+(\.\d+)?),\s*(-?\d+(\.\d+)?)$/"
                    ]
                ]);
                $value
                    ? $bindParams[':geo_coords'] = [\PDO::PARAM_STR => $value]
                    : $bindParams[':geo_coords'] = [\PDO::PARAM_NULL => null];
                break;
            case 'sg_activate':
                $value = filter_var($value['sg_activate'], FILTER_VALIDATE_REGEXP, [
                    "options" => [
                        "regexp" => "/^0|1$/"
                    ]
                ]);
                $value
                    ? $bindParams[':sg_activate'] = [\PDO::PARAM_STR => $value]
                    : $bindParams[':sg_activate'] = [\PDO::PARAM_STR => "0"];
                break;
        }
    }

    $query = "
        UPDATE servicegroup SET
        sg_name = :sg_name,
        sg_alias = :sg_alias,
        sg_comment = :sg_comment,
        geo_coords = :geo_coords,
        sg_activate = :sg_activate
        WHERE sg_id = :sg_id";
    $statement = $pearDB->prepare($query);
    foreach ($bindParams as $token => $bindValues) {
        foreach ($bindValues as $paramType => $value) {
            $statement->bindValue($token, $value, $paramType);
        }
    }
    $statement->execute();

    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($ret);
    $centreon->CentreonLogAction->insertLog(
        "servicegroup",
        $sg_id,
        htmlentities($ret["sg_name"], ENT_QUOTES, "UTF-8"),
        "c",
        $fields
    );
}

function updateServiceGroupServices($sg_id, $ret = [], $increment = false)
{
    if (!$sg_id) {
        return;
    }
    global $pearDB, $form;

    $sgId = filter_var($sg_id, FILTER_VALIDATE_INT);

    if ($increment == false) {
        $query = "
            DELETE FROM servicegroup_relation
            WHERE servicegroup_sg_id = :sg_id";
        $statement = $pearDB->prepare($query);
        $statement->bindValue(':sg_id', $sgId, \PDO::PARAM_INT);
        $statement->execute();
    }

    /* service templates */
    $retTmp = isset($ret["sg_tServices"]) ? $ret["sg_tServices"] : $form->getSubmitValue("sg_tServices");
    if ($retTmp) {
        for ($i = 0; $i < count($retTmp); $i++) {
            if (isset($retTmp[$i]) && $retTmp[$i]) {
                $t = preg_split("/\-/", $retTmp[$i]);
                $hostHostId = filter_var($t[0], FILTER_VALIDATE_INT);
                $serviceServiceId = filter_var($t[1], FILTER_VALIDATE_INT);
                $query = "
                    SELECT servicegroup_sg_id service FROM servicegroup_relation
                    WHERE host_host_id = :host_host_id AND service_service_id = :service_service_id
                    AND servicegroup_sg_id = :sg_id";

                $statement = $pearDB->prepare($query);
                $statement->bindValue(':host_host_id', $hostHostId, \PDO::PARAM_INT);
                $statement->bindValue(':service_service_id', $serviceServiceId, \PDO::PARAM_INT);
                $statement->bindValue(':sg_id', $sgId, \PDO::PARAM_INT);
                $statement->execute();
                if (!$statement->rowCount()) {
                    $query = "
                        INSERT INTO servicegroup_relation (host_host_id, service_service_id, servicegroup_sg_id)
                        VALUES (:host_host_id, :service_service_id, :servicegroup_sg_id)";
                    $statement2 = $pearDB->prepare($query);
                    $statement2->bindValue(':host_host_id', $hostHostId, \PDO::PARAM_INT);
                    $statement2->bindValue(':service_service_id', $serviceServiceId, \PDO::PARAM_INT);
                    $statement2->bindValue(':servicegroup_sg_id', $sgId, \PDO::PARAM_INT);
                    $statement2->execute();
                }
            }
        }
    }

    /* regular services */
    $retTmp = isset($ret["sg_hServices"])
        ? $ret["sg_hServices"]
        : CentreonUtils::mergeWithInitialValues($form, 'sg_hServices');
    for ($i = 0; $i < count($retTmp); $i++) {
        if (isset($retTmp[$i]) && $retTmp[$i]) {
            $t = preg_split("/\-/", $retTmp[$i]);
            $hostHostId = filter_var($t[0], FILTER_VALIDATE_INT);
            $serviceServiceId = filter_var($t[1], FILTER_VALIDATE_INT);
            $query = "
                SELECT servicegroup_sg_id service FROM servicegroup_relation
                WHERE host_host_id = :host_host_id AND service_service_id = :service_service_id
                AND servicegroup_sg_id = :sg_id";
            $statement = $pearDB->prepare($query);
            $statement->bindValue(':host_host_id', $hostHostId, \PDO::PARAM_INT);
            $statement->bindValue(':service_service_id', $serviceServiceId, \PDO::PARAM_INT);
            $statement->bindValue(':sg_id', $sgId, \PDO::PARAM_INT);
            $statement->execute();
            if (!$statement->rowCount()) {
                $query = "
                    INSERT INTO servicegroup_relation (host_host_id, service_service_id, servicegroup_sg_id)
                    VALUES (:host_host_id, :service_service_id, :servicegroup_sg_id)";
                $statement2 = $pearDB->prepare($query);
                $statement2->bindValue(':host_host_id', $hostHostId, \PDO::PARAM_INT);
                $statement2->bindValue(':service_service_id', $serviceServiceId, \PDO::PARAM_INT);
                $statement2->bindValue(':servicegroup_sg_id', $sgId, \PDO::PARAM_INT);
                $statement2->execute();
            }
        }
    }

    /* hostgroup services */
    $retTmp = isset($ret["sg_hgServices"])
        ? $ret["sg_hgServices"]
        : CentreonUtils::mergeWithInitialValues($form, 'sg_hgServices');
    for ($i = 0; $i < count($retTmp); $i++) {
        $t = preg_split("/\-/", $retTmp[$i]);
        $hostGroupId = filter_var($t[0], FILTER_VALIDATE_INT);
        $serviceServiceId = filter_var($t[1], FILTER_VALIDATE_INT);
        $query = "
            SELECT servicegroup_sg_id service FROM servicegroup_relation
            WHERE hostgroup_hg_id = :hostgroup_hg_id AND service_service_id = :service_service_id
            AND servicegroup_sg_id = :servicegroup_sg_id";
        $statement = $pearDB->prepare($query);
        $statement->bindValue(':hostgroup_hg_id', $hostGroupId, \PDO::PARAM_INT);
        $statement->bindValue(':service_service_id', $serviceServiceId, \PDO::PARAM_INT);
        $statement->bindValue(':servicegroup_sg_id', $sgId, \PDO::PARAM_INT);
        $statement->execute();
        if (!$statement->rowCount()) {
            $query = "
                INSERT INTO servicegroup_relation (hostgroup_hg_id, service_service_id, servicegroup_sg_id)
                VALUES (:hostgroup_hg_id, :service_service_id, :servicegroup_sg_id)";
            $statement2 = $pearDB->prepare($query);
            $statement2->bindValue(':hostgroup_hg_id', $hostGroupId, \PDO::PARAM_INT);
            $statement2->bindValue(':service_service_id', $serviceServiceId, \PDO::PARAM_INT);
            $statement2->bindValue(':servicegroup_sg_id', $sgId, \PDO::PARAM_INT);
            $statement2->execute();
        }
    }
}
