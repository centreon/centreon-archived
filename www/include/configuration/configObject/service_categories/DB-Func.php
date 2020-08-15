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
    $arr = array();
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
    $id = null;
    if (isset($form)) {
        $id = $form->getSubmitValue('sc_id');
    }
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $statement = $pearDB->prepare("SELECT `sc_name`, `sc_id` FROM `service_categories` WHERE `sc_name` = ?");
    $result = $pearDB->execute($statement, $name);
    $sc = $result->fetchRow();
    if ($result->numRows() >= 1 && $sc["sc_id"] != $id) {
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

function multipleServiceCategorieInDB($sc = array(), $nbrDup = array())
{
    global $pearDB, $centreon;

    $scAcl = array();
    foreach ($sc as $key => $value) {
        $scId = filter_var($key, FILTER_VALIDATE_INT);
        $statement = $pearDB->prepare("SELECT * FROM `service_categories` WHERE `sc_id` = ? LIMIT 1");
        $result = $pearDB->execute($statement, array($scId));
        $row = $result->fetchRow();
        $row["sc_id"] = '';
        for ($i = 1; $i <= $nbrDup[$key]; $i++) {
            $val = null;
            $params = array();
            foreach ($row as $key2 => $value2) {
                switch ($key2) {
                    case 'sc_name':
                        $value2 = filter_var($value2, FILTER_SANITIZE_STRING);
                        $sc_name = $value2 = $value2 . "_" . $i;
                        $params[] = $value2;
                        break;
                    case 'sc_description':
                        $value2 = filter_var($value2, FILTER_SANITIZE_STRING);
                        $params[] = $value2;
                        break;
                    case 'level':
                    case 'icon_id':
                        $value2 = filter_var($value2, FILTER_VALIDATE_INT);
                        $value2
                            ? $params[] = $value2
                            : $params[] = null;
                        break;
                    case 'sc_activate':
                        $value2 = filter_var(
                            $value2,
                            FILTER_VALIDATE_REGEXP,
                            array(
                                "options" => array(
                                    "regexp" => "/^0|1$/"
                                )
                            )
                        );
                        $value2
                            ? $params[] = $value2
                            : $params[] = "0";
                        break;
                }
                $val
                    ? $val .= ", ?"
                    : $val .= "NULL";
            }
            if (testServiceCategorieExistence($sc_name)) {
                $val ? $rq = "INSERT INTO `service_categories` VALUES (" . $val . ")" : $rq = null;
                $statement =  $pearDB->prepare($rq);
                $pearDB->execute($statement, $params);
                $result = $pearDB->query("SELECT MAX(sc_id) as maxid FROM `service_categories`");
                $maxId = $result->fetchRow();
                $scAcl[$maxId['MAX(sc_id)']] = $scId;
                $query = "INSERT INTO service_categories_relation (service_service_id, sc_id)
                (SELECT service_service_id, " . $maxId['maxid'] . "
                FROM service_categories_relation WHERE sc_id = ?)";
                $statement = $pearDB->prepare($query);
                $pearDB->execute($statement, array($scId));
            }
        }
    }
    CentreonACL::duplicateScAcl($scAcl);
    $centreon->user->access->updateACL();
}

function enableServiceCategorieInDB($sc_id = null, $sc_arr = array())
{
    if (!$sc_id && !count($sc_arr)) {
        return;
    }
    global $pearDB;
    if ($sc_id) {
        $sc_arr = array($sc_id => "1");
    }
    foreach ($sc_arr as $key => $value) {
        $scId = filter_var($key, FILTER_VALIDATE_INT);
        $statement = $pearDB->prepare("UPDATE service_categories SET sc_activate = '1' WHERE sc_id = ?");
        $pearDB->execute($statement, array($scId));
    }
}

function disableServiceCategorieInDB($sc_id = null, $sc_arr = array())
{
    if (!$sc_id && !count($sc_arr)) {
        return;
    }
    global $pearDB;
    if ($sc_id) {
        $sc_arr = array($sc_id => "1");
    }
    foreach ($sc_arr as $key => $value) {
        $scId = filter_var($key, FILTER_VALIDATE_INT);
        $statement = $pearDB->prepare("UPDATE service_categories SET sc_activate = '0' WHERE sc_id = ?");
        $pearDB->execute($statement, array($scId));
    }
}

function insertServiceCategorieInDB()
{
    global $pearDB, $centreon;
    $scName = filter_var($_POST['sc_name'], FILTER_SANITIZE_STRING);
    $scDescription = filter_var($_POST['sc_description'], FILTER_SANITIZE_STRING);
    isset($_POST['sc_severity_level']) && $_POST['sc_type']
        ? $scSeverityLevel = filter_var($_POST['sc_severity_level'], FILTER_VALIDATE_INT)
        : $scSeverityLevel = null;
    isset($_POST['sc_severity_icon']) && $_POST['sc_type']
        ? $scSeverityIconId = filter_var($_POST['sc_severity_icon'], FILTER_VALIDATE_INT)
        : $scSeverityIconId = null;
    $scActivate = filter_var(
        $_POST['sc_activate']['sc_activate'],
        FILTER_VALIDATE_REGEXP,
        array(
            "options" => array(
                "regexp" => "/^0|1$/"
            )
        )
    );
    $scActivate
        ? $scActivate = $scActivate
        : $scActivate = "0";
    $params = array($scName, $scDescription, $scSeverityLevel, $scSeverityIconId, $scActivate);
    if (testServiceCategorieExistence($scName)) {
        $query = "INSERT INTO `service_categories` (`sc_name`, `sc_description`, `level`, `icon_id`, `sc_activate` )
            VALUES (?, ?, ?, ?, ?)";
        $statement = $pearDB->prepare($query);
        $pearDB->execute($statement, $params);
        $statement = $pearDB->prepare("SELECT MAX(sc_id) FROM `service_categories` WHERE sc_name LIKE ?");
        $result = $pearDB->execute($statement, $scName);
        $data = $result->fetchRow();
    }
    updateServiceCategoriesServices($data["MAX(sc_id)"]);
    $centreon->user->access->updateACL();
}

function updateServiceCategorieInDB()
{
    global $pearDB, $centreon;

    $scId = filter_var($_POST["sc_id"], FILTER_VALIDATE_INT);
    $scName = filter_var($_POST['sc_name'], FILTER_SANITIZE_STRING);
    $scDescription = filter_var($_POST['sc_description'], FILTER_SANITIZE_STRING);
    isset($_POST['sc_severity_level']) && $_POST['sc_type']
        ? $scSeverityLevel = filter_var($_POST['sc_severity_level'], FILTER_VALIDATE_INT)
        : $scSeverityLevel = null;
    isset($_POST['sc_severity_icon']) && $_POST['sc_type']
        ? $scSeverityIconId = filter_var($_POST['sc_severity_icon'], FILTER_VALIDATE_INT)
        : $scSeverityIconId = null;
    $scActivate = filter_var(
        $_POST['sc_activate']['sc_activate'],
        FILTER_VALIDATE_REGEXP,
        array(
            "options" => array(
                "regexp" => "/^0|1$/"
            )
        )
    );
    $scActivate
        ? $scActivate = $scActivate
        : $scActivate = "0";
    $params = array($scName, $scDescription, $scSeverityLevel, $scSeverityIconId, $scActivate, $scId);
    $statement = $pearDB->prepare("UPDATE `service_categories` SET
        `sc_name` = ?,
        `sc_description` = ?,
        `level` = ?,
        `icon_id` = ?,
        `sc_activate` = ?
        WHERE `sc_id` = ? ");
    $pearDB->execute($statement, $params);
    updateServiceCategoriesServices($scId);
    $centreon->user->access->updateACL();
}

function deleteServiceCategorieInDB($ids = null)
{
    global $pearDB, $centreon;
    foreach ($ids as $key => $value) {
        $scId = filter_var($key, FILTER_VALIDATE_INT);
        $statement = $pearDB->prepare("DELETE FROM `service_categories` WHERE `sc_id` = ?");
        $pearDB->execute($statement, $scId);
    }
    $centreon->user->access->updateACL();
}

function updateServiceCategoriesServices($sc_id)
{
    global $pearDB;

    if (!$sc_id) {
        return;
    }

    $statement = $pearDB->prepare("DELETE FROM service_categories_relation
        WHERE sc_id = ?
        AND service_service_id
            IN (SELECT service_id FROM service WHERE service_register = '0')");
    $pearDB->execute($statement, $sc_id);
    if (isset($_POST["sc_svcTpl"])) {
        foreach ($_POST["sc_svcTpl"] as $key) {
            $key = filter_var($key, FILTER_VALIDATE_INT);
            $rq = "INSERT INTO service_categories_relation (service_service_id, sc_id) VALUES (?, ?)";
            $statement = $pearDB->prepare($rq);
            $pearDB->execute($statement, array($key, $sc_id));
        }
    }
}
