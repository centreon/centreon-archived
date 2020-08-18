<?php
/*
 * Copyright 2005-2009 Centreon
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
 * Rule that checks whether severity data is set
 */
function checkSeverity($fields)
{
    $arr = [];
    if (isset($fields['hc_type']) && $fields['hc_severity_level'] == "") {
        $arr['hc_severity_level'] = "Severity level is required";
    }
    if (isset($fields['hc_type']) && $fields['hc_severity_icon'] == "") {
        $arr['hc_severity_icon'] = "Severity icon is required";
    }
    if (count($arr)) {
        return $arr;
    }
    return true;
}

function testHostCategorieExistence($name = null)
{
    global $pearDB, $form;

    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $id = null;
    if (isset($form)) {
        $id = $form->getSubmitValue('hc_id');
    }

    $statement = $pearDB->prepare("SELECT hc_name, hc_id FROM hostcategories WHERE hc_name = :hc_name");
    $statement->bindValue(':hc_name', $name, PDO::PARAM_STR);
    $statement->execute();
    $hc = $statement->fetch();

    if ($statement->rowCount() >= 1 && $hc["hc_id"] != $id) {
        return false;
    } else {
        return true;
    }
}

function shouldNotBeEqTo0($value)
{
    if ($value) {
        return true;
    } else {
        return false;
    }
}

function enableHostCategoriesInDB(int $hcId = null, $hcArr = [])
{
    global $pearDB, $centreon;

    if (!$hcId && !count($hcArr)) {
        return;
    }

    if ($hcId) {
        $hcArr = [$hcId => "1"];
    }

    $statement = $pearDB->prepare("UPDATE hostcategories SET hc_activate = '1' WHERE hc_id = :hc_id");
    $statement2 = $pearDB->prepare("SELECT hc_name FROM `hostcategories` WHERE `hc_id` = :hc_id LIMIT 1");
    foreach (array_keys($hcArr) as $key) {
        $hcId = filter_var($key, FILTER_VALIDATE_INT);

        $statement->bindValue(':hc_id', $hcId, \PDO::PARAM_INT);
        $statement->execute();

        $statement2->bindValue(':hc_id', $hcId, \PDO::PARAM_INT);
        $statement2->execute();
        $row = $statement2->fetch();
        $centreon->CentreonLogAction->insertLog("hostcategories", $key, $row['hc_name'], "enable");
    }
}

function disableHostCategoriesInDB(int $hcId = null, $hcArr = [])
{
    global $pearDB, $centreon;

    if (!$hcId && !count($hcArr)) {
        return;
    }
    if ($hcId) {
        $hcArr = [$hcId => "1"];
    }

    $statement = $pearDB->prepare("UPDATE hostcategories SET hc_activate = '0' WHERE hc_id = :hc_id");
    $statement2 = $pearDB->prepare("SELECT hc_name FROM `hostcategories` WHERE `hc_id` = :hc_id LIMIT 1");
    foreach (array_keys($hcArr) as $key) {
        $hcId = filter_var($key, FILTER_VALIDATE_INT);

        $statement->bindValue(':hc_id', $hcId, \PDO::PARAM_INT);
        $statement->execute();

        $statement2->bindValue(':hc_id', $hcId, \PDO::PARAM_INT);
        $statement2->execute();
        $row = $statement2->fetch();
        $centreon->CentreonLogAction->insertLog("hostcategories", $key, $row['hc_name'], "disable");
    }
}

function deleteHostCategoriesInDB($hostCategories = [])
{
    global $pearDB, $centreon;

    $statement = $pearDB->prepare("SELECT hc_name FROM `hostcategories` WHERE `hc_id` = :hc_id LIMIT 1");
    $statement2 = $pearDB->prepare("DELETE FROM hostcategories WHERE hc_id = :hc_id");
    foreach (array_keys($hostCategories) as $key) {
        $hcId = filter_var($key, FILTER_VALIDATE_INT);

        $statement->bindValue(':hc_id', $hcId, \PDO::PARAM_INT);
        $statement->execute();
        $row = $statement->fetch();

        $statement2->bindValue(':hc_id', $hcId, \PDO::PARAM_INT);
        $statement2->execute();
        $centreon->CentreonLogAction->insertLog("hostcategories", $hcId, $row['hc_name'], "d");
    }
    $centreon->user->access->updateACL();
}

function multipleHostCategoriesInDB($hostCategories = [], $nbrDup = [])
{
    global $pearDB, $centreon;

    $hcAcl = [];
    $statement = $pearDB->prepare("SELECT * FROM hostcategories WHERE hc_id = :hc_id LIMIT 1");
    foreach (array_keys($hostCategories) as $key) {
        $hcId = filter_var($key, FILTER_VALIDATE_INT);
        $statement->bindValue(':hc_id', $hcId, \PDO::PARAM_INT);
        $statement->execute();
        $row = $statement->fetch();
        $row["hc_id"] = null;
        for ($i = 1; $i <= $nbrDup[$key]; $i++) {
            $level = false;
            $bindParams = [];
            foreach ($row as $key2 => $value2) {
                switch ($key2) {
                    case 'hc_name':
                        $value2 = filter_var($value2, FILTER_SANITIZE_STRING);
                        $hc_name = $value2 = $value2 . "_" . $i;
                        $bindParams[':hc_name'] = [\PDO::PARAM_STR => $value2];
                        break;
                    case 'hc_alias':
                        $value2 = filter_var($value2, FILTER_SANITIZE_STRING);
                        $bindParams[':hc_alias'] = [\PDO::PARAM_STR => $value2];
                        break;
                    case 'level':
                        $value2 = filter_var($value2, FILTER_VALIDATE_INT);
                        if ($value2) {
                            $bindParams[':level'] = [\PDO::PARAM_INT => $value2];
                            $level = true;
                        } else {
                            $bindParams[':level'] = [\PDO::PARAM_NULL => null];
                        }
                        break;
                    case 'icon_id':
                        $value2 = filter_var($value2, FILTER_VALIDATE_INT);
                        $value2
                            ? $bindParams[':icon_id'] = [\PDO::PARAM_INT => $value2]
                            : $bindParams[':icon_id'] = [\PDO::PARAM_NULL => null];
                        break;
                    case 'hc_comment':
                        $value2 = filter_var($value2, FILTER_SANITIZE_STRING);
                        $value2
                            ? $bindParams[':hc_comment'] = [\PDO::PARAM_STR => $value2]
                            : $bindParams[':hc_comment'] = [\PDO::PARAM_NULL => null];
                        break;
                    case 'hc_activate':
                        $value2 = filter_var($value2, FILTER_VALIDATE_REGEXP, [
                            "options" => [
                                "regexp" => "/^0|1$/"
                            ]
                        ]);
                        $value2
                            ? $bindParams[':hc_activate'] = [\PDO::PARAM_STR => $value2]
                            : $bindParams[':hc_activate'] = [\PDO::PARAM_STR => "0"];
                        break;
                }

                if ($key2 != "hc_id") {
                    $fields[$key2] = $value2;
                }
            }
            $fields["hc_name"] = $hc_name;
            if (testHostCategorieExistence($hc_name)) {
                if (!empty($bindParams)) {
                    $statement2 = $pearDB->prepare("
                        INSERT INTO hostcategories
                        VALUES (NULL, :hc_name, :hc_alias, :level, :icon_id, :hc_comment, :hc_activate)
                    ");
                    foreach ($bindParams as $token => $bindValues) {
                        foreach ($bindValues as $paramType => $value) {
                            $statement2->bindValue($token, $value, $paramType);
                        }
                    }
                    $statement2->execute();
                }
                $dbResult = $pearDB->query("SELECT MAX(hc_id) FROM hostcategories");
                $maxId = $dbResult->fetch();

                if (isset($maxId["MAX(hc_id)"]) && !$level) {
                    $hcAcl[$maxId["MAX(hc_id)"]] = $hcId;

                    $statement3 = $pearDB->prepare("
                        SELECT DISTINCT hgr.host_host_id FROM hostcategories_relation hgr
                        WHERE hgr.hostcategories_hc_id = :hc_id
                    ");
                    $statement3->bindValue(':hc_id', $hcId, \PDO::PARAM_INT);
                    $statement3->execute();
                    $fields["hc_hosts"] = "";
                    while ($host = $statement3->fetch()) {
                        $query = "INSERT INTO hostcategories_relation VALUES ('" . $maxId["MAX(hc_id)"] .
                            "', '" . $host["host_host_id"] . "')";
                        $pearDB->query($query);
                        $fields["hc_hosts"] .= $host["host_host_id"] . ",";
                    }
                    $fields["hc_hosts"] = trim($fields["hc_hosts"], ",");
                    $centreon->CentreonLogAction->insertLog(
                        "hostcategories",
                        $maxId["MAX(hc_id)"],
                        $hc_name,
                        "a",
                        $fields
                    );
                }
            }
        }
    }
    CentreonACL::duplicateHcAcl($hcAcl);
    $centreon->user->access->updateACL();
}

function insertHostCategoriesInDB($ret = [])
{
    global $centreon;

    $hcId = insertHostCategories($ret);
    updateHostCategoriesHosts($hcId, $ret);
    $centreon->user->access->updateACL();
    return $hcId;
}

function updateHostCategoriesInDB($hcId = null)
{
    global $centreon;
    if (!$hcId) {
        return;
    }
    updateHostCategories($hcId);
    updateHostCategoriesHosts($hcId);
    $centreon->user->access->updateACL();
}

function insertHostCategories($ret = [])
{
    global $form, $pearDB, $centreon;

    if (!count($ret)) {
        $ret = $form->getSubmitValues();
    }

    $bindParams = [];
    foreach ($ret as $key => $value) {
        switch ($key) {
            case 'hc_name':
                $value = filter_var($value, FILTER_SANITIZE_STRING);
                $bindParams[':hc_name'] = [\PDO::PARAM_STR => $value];
                break;
            case 'hc_alias':
                $value = filter_var($value, FILTER_SANITIZE_STRING);
                $bindParams[':hc_alias'] = [\PDO::PARAM_STR => $value];
                break;
            case 'hc_severity_level':
                $value = filter_var($value, FILTER_VALIDATE_INT);
                $value
                    ? $bindParams[':level'] = [\PDO::PARAM_INT => $value]
                    : $bindParams[':level'] = [\PDO::PARAM_NULL => null];
                break;
            case 'hc_severity_icon':
                $value = filter_var($value, FILTER_VALIDATE_INT);
                $value
                    ? $bindParams[':icon_id'] = [\PDO::PARAM_INT => $value]
                    : $bindParams[':icon_id'] = [\PDO::PARAM_NULL => null];
                break;
            case 'hc_comment':
                $value = filter_var($value, FILTER_SANITIZE_STRING);
                $value
                    ? $bindParams[':hc_comment'] = [\PDO::PARAM_STR => $value]
                    : $bindParams[':hc_comment'] = [\PDO::PARAM_NULL => null];
                break;
            case 'hc_activate':
                $value = filter_var($value['hc_activate'], FILTER_VALIDATE_REGEXP, [
                    "options" => [
                        "regexp" => "/^0|1$/"
                    ]
                ]);
                $value
                    ? $bindParams[':hc_activate'] = [\PDO::PARAM_STR => $value]
                    : $bindParams[':hc_activate'] = [\PDO::PARAM_STR => "0"];
                break;
        }
    }

    $statement = $pearDB->prepare("
        INSERT INTO hostcategories
        (hc_name, hc_alias, level, icon_id, hc_comment, hc_activate)
        VALUES (:hc_name, :hc_alias, :level, :icon_id, :hc_comment, :hc_activate)
    ");
    foreach ($bindParams as $token => $bindValues) {
        foreach ($bindValues as $paramType => $value) {
            $statement->bindValue($token, $value, $paramType);
        }
    }
    $statement->execute();
    $dbResult = $pearDB->query("SELECT MAX(hc_id) FROM hostcategories");
    $hcId = $dbResult->fetch();

    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($ret);

    $centreon->CentreonLogAction->insertLog(
        "hostcategories",
        $hcId["MAX(hc_id)"],
        CentreonDB::escape($ret["hc_name"]),
        "a",
        $fields
    );
    return ($hcId["MAX(hc_id)"]);
}

function updateHostCategories($hcId)
{
    if (!$hcId) {
        return;
    }
    $hcId = filter_var($hcId, FILTER_VALIDATE_INT);
    global $form, $pearDB, $centreon;
    $ret = [];
    $ret = $form->getSubmitValues();
    $ret['hc_type']
        ? $scType = true
        : $scType = false;
    $bindParams = [];
    $bindParams[':hc_id'] = [\PDO::PARAM_INT => $hcId];
    foreach ($ret as $key => $value) {
        switch ($key) {
            case 'hc_name':
                $value = filter_var($value, FILTER_SANITIZE_STRING);
                $bindParams[':hc_name'] = [\PDO::PARAM_STR => $value];
                break;
            case 'hc_alias':
                $value = filter_var($value, FILTER_SANITIZE_STRING);
                $bindParams[':hc_alias'] = [\PDO::PARAM_STR => $value];
                break;
            case 'hc_severity_level':
                $value = filter_var($value, FILTER_VALIDATE_INT);
                ($value && $scType)
                    ? $bindParams[':level'] = [\PDO::PARAM_INT => $value]
                    : $bindParams[':level'] = [\PDO::PARAM_NULL => null];
                break;
            case 'hc_severity_icon':
                $value = filter_var($value, FILTER_VALIDATE_INT);
                ($value && $scType)
                    ? $bindParams[':icon_id'] = [\PDO::PARAM_INT => $value]
                    : $bindParams[':icon_id'] = [\PDO::PARAM_NULL => null];
                break;
            case 'hc_comment':
                $value = filter_var($value, FILTER_SANITIZE_STRING);
                $value
                    ? $bindParams[':hc_comment'] = [\PDO::PARAM_STR => $value]
                    : $bindParams[':hc_comment'] = [\PDO::PARAM_NULL => null];
                break;
            case 'hc_activate':
                $value = filter_var($value['hc_activate'], FILTER_VALIDATE_REGEXP, [
                    "options" => [
                        "regexp" => "/^0|1$/"
                    ]
                ]);
                $value
                    ? $bindParams[':hc_activate'] = [\PDO::PARAM_STR => $value]
                    : $bindParams[':hc_activate'] = [\PDO::PARAM_STR => "0"];
                break;
        }
    }

    $statement = $pearDB->prepare("
        UPDATE hostcategories SET
        hc_name = :hc_name,
        hc_alias = :hc_alias,
        level = :level,
        icon_id = :icon_id,
        hc_comment = :hc_comment,
        hc_activate = :hc_activate
        WHERE hc_id = :hc_id
    ");
    foreach ($bindParams as $token => $bindValues) {
        foreach ($bindValues as $paramType => $value) {
            $statement->bindValue($token, $value, $paramType);
        }
    }
    $statement->execute();
    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($ret);

    $centreon->CentreonLogAction->insertLog(
        "hostcategories",
        $hcId,
        CentreonDB::escape($ret["hc_name"]),
        "c",
        $fields
    );
}

function updateHostCategoriesHosts($hcId, $ret = [])
{
    global $form, $pearDB;

    if (!$hcId) {
        return;
    }
    $hcId = filter_var($hcId, FILTER_VALIDATE_INT);
    /*
     * Special Case, delete relation between host/service, when service
     * is linked to hostcategories in escalation, dependencies, osl
     *
     * Get initial Host list to make a diff after deletion
     */
    $hostsOLD = [];

    $statement = $pearDB->prepare("
        SELECT host_host_id FROM hostcategories_relation
        WHERE hostcategories_hc_id = :hc_id");
    $statement->bindValue(':hc_id', $hcId, \PDO::PARAM_INT);
    $statement->execute();
    while ($host = $statement->fetch()) {
        $hostsOLD[$host["host_host_id"]] = $host["host_host_id"];
    }
    $statement->closeCursor();

    // delete all previous relations before reinsert new relations
    $statement = $pearDB->prepare("DELETE FROM hostcategories_relation WHERE hostcategories_hc_id = :hc_id");
    $statement->bindValue(':hc_id', $hcId, \PDO::PARAM_INT);
    $statement->execute();
    // get host relations
    $linkedHosts = isset($ret["hc_hosts"]) ?
        $ret["hc_hosts"] :
        CentreonUtils::mergeWithInitialValues($form, 'hc_hosts');

    // get host template relations
    $linkedHostTemplates = isset($ret["hc_hostsTemplate"]) ?
        $ret["hc_hostsTemplate"] :
        CentreonUtils::mergeWithInitialValues($form, 'hc_hostsTemplate');

    // merge host and host template relations
    $linkedObjects = array_merge($linkedHosts, $linkedHostTemplates);

    // build query to insert all relations
    if (count($linkedObjects)) {
        $query = "INSERT INTO hostcategories_relation (hostcategories_hc_id, host_host_id) VALUES ";
        for ($i = 0; $i < count($linkedObjects); $i++) {
            if ($i != 0) {
                $query .= ", ";
            }
            $query .= " (:hc_id, '" . $linkedObjects[$i] . "')";
        }
        $statement = $pearDB->prepare($query);
        $statement->bindValue(':hc_id', $hcId, \PDO::PARAM_INT);
        $statement->execute();
    }
}
