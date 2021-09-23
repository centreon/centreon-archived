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

/**
 * Rule that checks whether severity data is set
 */
function checkSeverity($fields)
{
    $arr = [];
    if (isset($fields['sc_type']) && $fields['sc_severity_level'] == "") {
        $arr['sc_severity_level'] = "Severity level is required";
    }
    if (isset($fields['sc_type']) && $fields['sc_severity_icon'] == "") {
        $arr['sc_severity_icon'] = "Severity icon is required";
    }
    if (count($arr)) {
        return $arr;
    }
    return true;
}

function testServiceCategorieExistence($name = null)
{
    global $pearDB, $form;

    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $id = null;
    if (isset($form)) {
        $id = $form->getSubmitValue('sc_id');
    }
    $query = "SELECT `sc_name`, `sc_id` FROM `service_categories` WHERE `sc_name` = :sc_name";
    $statement = $pearDB->prepare($query);
    $statement->bindValue(':sc_name', $name, \PDO::PARAM_STR);
    $statement->execute();
    $sc = $statement->fetch();
    if ($statement->rowCount() >= 1 && $sc["sc_id"] != $id) {
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

function multipleServiceCategorieInDB($sc = [], $nbrDup = [])
{
    global $pearDB, $centreon;

    $scAcl = [];
    foreach ($sc as $key => $value) {
        $scId = filter_var($key, FILTER_VALIDATE_INT);
        $query = "SELECT * FROM `service_categories` WHERE `sc_id` = :sc_id LIMIT 1";
        $statement = $pearDB->prepare($query);
        $statement->bindValue(':sc_id', $scId, \PDO::PARAM_INT);
        $statement->execute();
        $row = $statement->fetch();
        for ($i = 1; $i <= $nbrDup[$scId]; $i++) {
            $val = null;
            $bindParams = [];
            foreach ($row as $key2 => $value2) {
                switch ($key2) {
                    case 'sc_name':
                        $value2 = filter_var($value2, FILTER_SANITIZE_STRING);
                        $sc_name = $value2 = $value2 . "_" . $i;
                        $bindParams[':sc_name'] = [
                            \PDO::PARAM_STR => $value2
                        ];
                        break;
                    case 'sc_description':
                        $value2 = filter_var($value2, FILTER_SANITIZE_STRING);
                        $bindParams[':sc_description'] = [
                            \PDO::PARAM_STR => $value2
                        ];
                        break;
                    case 'level':
                        $value2 = filter_var($value2, FILTER_VALIDATE_INT);
                        $value2
                            ? $bindParams[':sc_level'] = [\PDO::PARAM_INT => $value2]
                            : $bindParams[':sc_level'] = [\PDO::PARAM_NULL => "NULL"];
                        break;
                    case 'icon_id':
                        $value2 = filter_var($value2, FILTER_VALIDATE_INT);
                        $value2
                            ? $bindParams[':sc_icon_id'] = [\PDO::PARAM_INT => $value2]
                            : $bindParams[':sc_icon_id'] = [\PDO::PARAM_NULL => "NULL"];
                        break;
                    case 'sc_activate':
                        $value2 = filter_var($value2, FILTER_VALIDATE_INT);
                        $value2
                            ? $bindParams[':sc_activate'] = [\PDO::PARAM_STR => $value2]
                            : $bindParams[':sc_activate'] = [\PDO::PARAM_STR =>  "0"];
                        break;
                }
                $val
                    ? $val .= ($value2 != null ? (", '" . $value2 . "'") : ", NULL")
                    : $val .= ($value2 != null ? ("'" . $value2 . "'") : "NULL");
            }
            if (testServiceCategorieExistence($sc_name)) {
                $val
                    ? $query = "
                        INSERT INTO `service_categories`
                        VALUES (NULL, :sc_name, :sc_description, :sc_level, :sc_icon_id, :sc_activate)"
                    : $query = null;
                $statement = $pearDB->prepare($query);
                foreach ($bindParams as $token => $bindValues) {
                    foreach ($bindValues as $paramType => $value) {
                        $statement->bindValue($token, $value, $paramType);
                    }
                }
                $statement->execute();
                $statement = $pearDB->query("SELECT MAX(sc_id) as maxid FROM `service_categories`");
                $maxId = $statement->fetch();
                $scAcl[$maxId['maxid']] = $key;
                $query = "INSERT INTO service_categories_relation (service_service_id, sc_id) " .
                    "(SELECT service_service_id, " . $maxId['maxid'] .
                    " FROM service_categories_relation WHERE sc_id = :sc_id)";
                $statement = $pearDB->prepare($query);
                $statement->bindValue(':sc_id', $key, \PDO::PARAM_INT);
                $statement->execute();
            }
        }
    }
    CentreonACL::duplicateScAcl($scAcl);
    $centreon->user->access->updateACL();
}

function enableServiceCategorieInDB(?int $sc_id = null, $sc_arr = [])
{
    if (!$sc_id && !count($sc_arr)) {
        return;
    }
    global $pearDB;
    if ($sc_id) {
        $sc_arr = [$sc_id => "1"];
    }
    foreach (array_keys($sc_arr) as $key) {
        $query = "UPDATE service_categories SET sc_activate = '1' WHERE sc_id = :sc_id";
        $statement = $pearDB->prepare($query);
        $statement->bindValue(':sc_id', $key, \PDO::PARAM_INT);
        $statement->execute();
    }
}

function disableServiceCategorieInDB(?int $sc_id = null, $sc_arr = [])
{
    if (!$sc_id && !count($sc_arr)) {
        return;
    }
    global $pearDB;
    if ($sc_id) {
        $sc_arr = array($sc_id => "1");
    }
    foreach (array_keys($sc_arr) as $key) {
        $query = "UPDATE service_categories SET sc_activate = '0' WHERE sc_id = :sc_id";
        $statement = $pearDB->prepare($query);
        $statement->bindValue(':sc_id', $key, \PDO::PARAM_INT);
        $statement->execute();
    }
}

function insertServiceCategorieInDB()
{
    global $pearDB, $centreon;
    $scName = filter_var($_POST['sc_name'], FILTER_SANITIZE_STRING);
    $scDescription = filter_var($_POST['sc_description'], FILTER_SANITIZE_STRING);
    $scSeverityLevel = filter_var($_POST['sc_severity_level'], FILTER_VALIDATE_INT);
    $scType = filter_var($_POST['sc_type'] ?? false, FILTER_VALIDATE_INT);
    $scSeverityIconId = filter_var($_POST['sc_severity_icon'], FILTER_VALIDATE_INT);
    $scActivate = filter_var($_POST['sc_activate']['sc_activate'], FILTER_VALIDATE_INT);

    $bindParams = [];
    $bindParams[':sc_name'] = [
        \PDO::PARAM_STR => $scName
    ];
    $bindParams[':sc_description'] = [
        \PDO::PARAM_STR => $scDescription
    ];
    ($scSeverityLevel === false || $scType === false)
        ? $bindParams[':sc_severity_level'] = [\PDO::PARAM_NULL => "NULL"]
        : $bindParams[':sc_severity_level'] = [\PDO::PARAM_INT => $scSeverityLevel];

    ($scSeverityIconId === false || $scType === false)
        ? $bindParams[':sc_icon_id'] = [\PDO::PARAM_NULL => "NULL"]
        : $bindParams[':sc_icon_id'] = [\PDO::PARAM_INT => $scSeverityIconId];

    ($scActivate === false)
        ? $bindParams[':sc_activate'] = [\PDO::PARAM_STR => "0"]
        : $bindParams[':sc_activate'] = [\PDO::PARAM_STR => $scActivate];
    if (testServiceCategorieExistence($scName)) {
        $query = "
            INSERT INTO `service_categories` (`sc_name`, `sc_description`, `level`, `icon_id`, `sc_activate`)
            VALUES (:sc_name, :sc_description, :sc_severity_level, :sc_icon_id, :sc_activate)";
        $statement = $pearDB->prepare($query);

        foreach ($bindParams as $token => $bindValues) {
            foreach ($bindValues as $paramType => $value) {
                $statement->bindValue($token, $value, $paramType);
            }
        }
        $statement->execute();

        $query = "SELECT MAX(sc_id) FROM `service_categories` WHERE sc_name LIKE :sc_name";
        $statement = $pearDB->prepare($query);
        $statement->bindValue(':sc_name', $scName, \PDO::PARAM_STR);
        $statement->execute();
        $data = $statement->fetch();
    }
    updateServiceCategoriesServices($data["MAX(sc_id)"]);
    $centreon->user->access->updateACL();
}

function updateServiceCategorieInDB()
{
    global $pearDB, $centreon;

    $scId = filter_var($_POST['sc_id'], FILTER_VALIDATE_INT);
    $scName = filter_var($_POST['sc_name'], FILTER_SANITIZE_STRING);
    $scDescription = filter_var($_POST['sc_description'], FILTER_SANITIZE_STRING);
    $scSeverityLevel = filter_var($_POST['sc_severity_level'], FILTER_VALIDATE_INT);
    $scType = filter_var($_POST['sc_type'] ?? false, FILTER_VALIDATE_INT);
    $scSeverityIconId = filter_var($_POST['sc_severity_icon'], FILTER_VALIDATE_INT);
    $scActivate = filter_var($_POST['sc_activate']['sc_activate'], FILTER_VALIDATE_INT);

    $bindParams = [];
    $bindParams[':sc_id'] = [
        \PDO::PARAM_INT => $scId
    ];
    $bindParams[':sc_name'] = [
        \PDO::PARAM_STR => $scName
    ];
    $bindParams[':sc_description'] = [
        \PDO::PARAM_STR => $scDescription
    ];
    ($scSeverityLevel === false || $scType === false)
        ? $bindParams[':sc_severity_level'] = [\PDO::PARAM_NULL => "NULL"]
        : $bindParams[':sc_severity_level'] = [\PDO::PARAM_INT => $scSeverityLevel];

    ($scSeverityIconId === false || $scType === false)
        ? $bindParams[':sc_icon_id'] = [\PDO::PARAM_NULL => "NULL"]
        : $bindParams[':sc_icon_id'] = [\PDO::PARAM_INT => $scSeverityIconId];

    ($scActivate === false)
        ? $bindParams[':sc_activate'] = [\PDO::PARAM_STR => '0']
        : $bindParams[':sc_activate'] = [\PDO::PARAM_STR => $scActivate];

    $query = "
        UPDATE `service_categories`
        SET `sc_name` = :sc_name,
            `sc_description` = :sc_description,
            `level` = :sc_severity_level,
            `icon_id` = :sc_icon_id,
            `sc_activate` = :sc_activate
        WHERE `sc_id` = :sc_id";
    $statement = $pearDB->prepare($query);
    foreach ($bindParams as $token => $bindValues) {
        foreach ($bindValues as $paramType => $value) {
            $statement->bindValue($token, $value, $paramType);
        }
    }
    $statement->execute();

    updateServiceCategoriesServices($scId);
    $centreon->user->access->updateACL();
}

function deleteServiceCategorieInDB($ids = null)
{
    global $pearDB, $centreon;

    foreach (array_keys($ids) as $key) {
        $scId = filter_var($key, FILTER_VALIDATE_INT);
        $query = "DELETE FROM `service_categories` WHERE `sc_id` = :sc_id";
        $statement = $pearDB->prepare($query);
        $statement->bindValue(':sc_id', $scId, \PDO::PARAM_INT);
        $statement->execute();
    }
    $centreon->user->access->updateACL();
}

function updateServiceCategoriesServices(int $sc_id)
{
    global $pearDB, $form;

    if (!$sc_id) {
        return;
    }
    $query = "
        DELETE FROM service_categories_relation WHERE sc_id = :sc_id
        AND service_service_id IN (SELECT service_id FROM service WHERE service_register = '0')";
    $statement = $pearDB->prepare($query);
    $statement->bindValue(':sc_id', $sc_id, \PDO::PARAM_INT);
    $statement->execute();
    if (isset($_POST["sc_svcTpl"])) {
        foreach ($_POST["sc_svcTpl"] as $serviceId) {
            $serviceId = filter_var($serviceId, FILTER_VALIDATE_INT);
            $query = "
                INSERT INTO service_categories_relation (service_service_id, sc_id)
                VALUES (:service_id, :sc_id)";
            $statement = $pearDB->prepare($query);
            $statement->bindValue(':service_id', $serviceId, \PDO::PARAM_INT);
            $statement->bindValue(':sc_id', $sc_id, \PDO::PARAM_INT);
            $statement->execute();
        }
    }
}
