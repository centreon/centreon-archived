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
    $name = CentreonUtils::escapeSecure($name, 4);
    $DBRESULT = $pearDB->query("SELECT `sc_name`, `sc_id` FROM `service_categories` WHERE `sc_name` = '".$name."'");
    $sc = $DBRESULT->fetchRow();
    if ($DBRESULT->numRows() >= 1 && $sc["sc_id"] == $id) {
        return true;
    } elseif ($DBRESULT->numRows() >= 1 && $sc["sc_id"] != $id) {
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
        $scId = CentreonUtils::escapeSecure($key, 4);
        $DBRESULT = $pearDB->query("SELECT * FROM `service_categories` WHERE `sc_id` = '" . $scId . "' LIMIT 1");
        $row = $DBRESULT->fetchRow();
        $row["sc_id"] = '';
        for ($i = 1; $i <= $nbrDup[$key]; $i++) {
            $val = null;
            foreach ($row as $key2 => $value2) {
                $value2 = CentreonUtils::escapeSecure($value2, 4);
                $key2 == "sc_name" ? ($sc_name = $value2 = $value2."_".$i) : null;
                $key2 == "sc_description" ? ($sc_alias = $value2 = $value2) : null;
                $val
                    ? $val .= ($value2!=null ? (", '".$value2."'") : ", NULL")
                    : $val .= ($value2 != null?("'".$value2."'"):"NULL");
            }
            if (testServiceCategorieExistence($sc_name)) {
                $val ? $rq = "INSERT INTO `service_categories` VALUES (".$val.")" : $rq = null;
                $DBRESULT = $pearDB->query($rq);
                $DBRESULT = $pearDB->query("SELECT MAX(sc_id) as maxid FROM `service_categories`");
                $maxId = $DBRESULT->fetchRow();
                $scAcl[$maxId['MAX(sc_id)']] = $scId;
                $query = "INSERT INTO service_categories_relation (service_service_id, sc_id)
                (SELECT service_service_id, ".$maxId['maxid']."
                FROM service_categories_relation WHERE sc_id = " . $scId . ")";
                $pearDB->query($query);
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
        $sc_arr = array($sc_id=>"1");
    }
    foreach ($sc_arr as $key => $value) {
        $scId = CentreonUtils::escapeSecure($key, 4);
        $DBRESULT = $pearDB->query("UPDATE service_categories SET sc_activate = '1' WHERE sc_id = '".$scId."'");
    }
}

function disableServiceCategorieInDB($sc_id = null, $sc_arr = array())
{
    if (!$sc_id && !count($sc_arr)) {
        return;
    }
    global $pearDB;
    if ($sc_id) {
        $sc_arr = array($sc_id=>"1");
    }
    foreach ($sc_arr as $key => $value) {
        $scId = CentreonUtils::escapeSecure($key, 4);
        $DBRESULT = $pearDB->query("UPDATE service_categories SET sc_activate = '0' WHERE sc_id = '".$scId."'");
    }
}

function insertServiceCategorieInDB()
{
    global $pearDB, $centreon;

    $scName = CentreonUtils::escapeSecure($_POST['sc_name'], 4);
    $scDescription = CentreonUtils::escapeSecure($_POST['sc_description'], 4);
    isset($_POST['sc_severity_level']) && $_POST['sc_type']
        ? $scSeverityLevel = CentreonUtils::escapeSecure($_POST['sc_severity_level'], 4)
        : $scSeverityLevel = "NULL";
    isset($_POST['sc_severity_icon']) && $_POST['sc_type']
        ? $scSeverityIconId = CentreonUtils::escapeSecure($_POST['sc_severity_icon'], 4)
        : $scSeverityIconId = "NULL";
    $scActivate = CentreonUtils::escapeSecure($_POST['sc_activate']['sc_activate'], 4);

    if (isset($_POST['sc_type'])) {
        $scType = CentreonUtils::escapeSecure($_POST['sc_type'], 4);
    }
    if (testServiceCategorieExistence($scName)) {
            $DBRESULT = $pearDB->query("INSERT INTO `service_categories` (`sc_name`, `sc_description`, `level`, `icon_id`, `sc_activate` )
                VALUES ('" . $scName . "', '" . $scDescription . "', '" . $scSeverityLevel . "', '" . $scSeverityIconId . "', '" . $scActivate . "')");
            $DBRESULT = $pearDB->query("SELECT MAX(sc_id) FROM `service_categories` WHERE sc_name LIKE '".$scName."'");
            $data = $DBRESULT->fetchRow();
    }
    updateServiceCategoriesServices($data["MAX(sc_id)"]);
    $centreon->user->access->updateACL();
}

function updateServiceCategorieInDB()
{
    global $pearDB, $centreon;

    $scId = CentreonUtils::escapeSecure($_POST["sc_id"], 4);
    $scName = CentreonUtils::escapeSecure($_POST['sc_name'], 4);
    $scDescription = CentreonUtils::escapeSecure($_POST['sc_description'], 4);
    isset($_POST['sc_severity_level']) && $_POST['sc_type']
        ? $scSeverityLevel = CentreonUtils::escapeSecure($_POST['sc_severity_level'], 4)
        : $scSeverityLevel = "NULL";
    isset($_POST['sc_severity_icon']) && $_POST['sc_type']
        ? $scSeverityIconId = CentreonUtils::escapeSecure($_POST['sc_severity_icon'], 4)
        : $scSeverityIconId = "NULL";
    $scActivate = CentreonUtils::escapeSecure($_POST['sc_activate']['sc_activate'], 4);
    var_dump($scSeverityLevel, $scSeverityIconId);
    $DBRESULT = $pearDB->query("UPDATE `service_categories` SET
        `sc_name` = '" . $scName . "' ,
        `sc_description` = '" . $scDescription . "' ,
        `level` = " . $scSeverityLevel . ",
        `icon_id` = " . $scSeverityIconId . ",
        `sc_activate` = '" . $scActivate . "'
        WHERE `sc_id` = '" . $scId . "'");
    updateServiceCategoriesServices($scId);
    $centreon->user->access->updateACL();
}

function deleteServiceCategorieInDB($ids = null)
{
    global $pearDB, $centreon;
    foreach ($ids as $key => $value) {
        $DBRESULT = $pearDB->query("DELETE FROM `service_categories` WHERE `sc_id` = '".$key."'");
    }
    $centreon->user->access->updateACL();
}

function updateServiceCategoriesServices($sc_id)
{
    global $pearDB, $form;

    if (!$sc_id) {
        return;
    }

    $DBRESULT = $pearDB->query("DELETE FROM service_categories_relation WHERE sc_id = '".$sc_id."' AND service_service_id IN (SELECT service_id FROM service WHERE service_register = '0')");
    if (isset($_POST["sc_svcTpl"])) {
        foreach ($_POST["sc_svcTpl"] as $key) {
            $key = CentreonUtils::escapeSecure($key, 4);
            $rq = "INSERT INTO service_categories_relation (service_service_id, sc_id) VALUES ('".$key."', '".$sc_id."')";
            $DBRESULT = $pearDB->query($rq);
        }
    }
}
