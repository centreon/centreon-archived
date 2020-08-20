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
    $arr = array();
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
    $id = null;
    if (isset($form)) {
        $id = $form->getSubmitValue('hc_id');
    }

    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $statement = $pearDB->prepare("SELECT hc_name, hc_id FROM hostcategories WHERE hc_name = ?");
    $result = $pearDB->execute($statement, array($name));
    $hc = $result->fetchRow();
    if ($result->numRows() >= 1 && $hc["hc_id"] != $id) {
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

function enableHostCategoriesInDB($hc_id = null, $hc_arr = array())
{
    global $pearDB, $centreon;

    if (!$hc_id && !count($hc_arr)) {
        return;
    }

    if ($hc_id) {
        $hc_arr = array($hc_id => "1");
    }

    foreach ($hc_arr as $key => $value) {
        $hcId = filter_var($key, FILTER_VALIDATE_INT);
        $statement = $pearDB->prepare("UPDATE hostcategories SET hc_activate = '1' WHERE hc_id = ? ");
        $pearDB->execute($statement, array($hcId));
        $statement2 = $pearDB->prepare("SELECT hc_name FROM `hostcategories` WHERE `hc_id` = ? LIMIT 1");
        $result = $pearDB->execute($statement2, array($hcId));
        $row = $result->fetchRow();
        $centreon->CentreonLogAction->insertLog("hostcategories", $hcId, $row['hc_name'], "enable");
    }
}

function disableHostCategoriesInDB($hc_id = null, $hc_arr = array())
{
    global $pearDB, $centreon;

    if (!$hc_id && !count($hc_arr)) {
        return;
    }
    if ($hc_id) {
        $hc_arr = array($hc_id => "1");
    }
    foreach ($hc_arr as $key => $value) {
        $hcId = filter_var($key, FILTER_VALIDATE_INT);
        $statement = $pearDB->prepare("UPDATE hostcategories SET hc_activate = '0' WHERE hc_id = ? ");
        $pearDB->execute($statement, array($hcId));
        $statement2 = $pearDB->prepare("SELECT hc_name FROM `hostcategories` WHERE `hc_id` = ? LIMIT 1");
        $result = $pearDB->execute($statement2, array($hcId));
        $row = $result->fetchRow();
        $centreon->CentreonLogAction->insertLog("hostcategories", $hcId, $row['hc_name'], "disable");
    }
}

function deleteHostCategoriesInDB($hostcategories = array())
{
    global $pearDB, $centreon;

    foreach ($hostcategories as $key => $value) {
        $hcId = filter_var($key, FILTER_VALIDATE_INT);
        $statement = $pearDB->prepare("SELECT hc_name FROM `hostcategories` WHERE `hc_id` = ? LIMIT 1");
        $result = $pearDB->execute($statement, array($hcId));
        $row = $result->fetchRow();
        $statement2 = $pearDB->prepare("DELETE FROM hostcategories WHERE hc_id = ? ");
        $pearDB->execute($statement2, array($hcId));
        $centreon->CentreonLogAction->insertLog("hostcategories", $hcId, $row['hc_name'], "d");
    }
    $centreon->user->access->updateACL();
}

function multipleHostCategoriesInDB($hostcategories = array(), $nbrDup = array())
{
    global $pearDB, $centreon;

    $hcAcl = array();
    foreach ($hostcategories as $key => $value) {
        $hcId = filter_var($key, FILTER_VALIDATE_INT);
        $statement = $pearDB->prepare("SELECT * FROM hostcategories WHERE hc_id = ? LIMIT 1");
        $result = $pearDB->execute($statement, array($hcId));
        $row = $result->fetchRow();
        $row["hc_id"] = '';
        for ($i = 1; $i <= $nbrDup[$hcId]; $i++) {
            $val = null;
            $rq = null;
            $level = false;
            $params = array();
            foreach ($row as $key2 => $value2) {
                switch ($key2) {
                    case 'hc_name':
                        $value2 = filter_var($value2, FILTER_SANITIZE_STRING);
                        $hc_name = $value2 = $value2 . "_" . $i;
                        $params[] = $value2;
                        break;
                    case 'hc_alias':
                    case 'hc_comment':
                        $value2 = filter_var($value2, FILTER_SANITIZE_STRING);
                        $params[] = $value2;
                        break;
                    case 'level':
                        $value2 = filter_var($value2, FILTER_VALIDATE_INT);
                        if ($value2) {
                            $params[] = $value2;
                            $level = true;
                        } else {
                            $params[] = null;
                        }
                        break;
                    case 'icon_id':
                        $value2 = filter_var($value2, FILTER_VALIDATE_INT);
                        $value2
                            ? $params[] = $value2
                            : $params[] = null;
                        break;
                    case 'hc_activate':
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
                if ($key2 != "hc_id") {
                    $fields[$key2] = $value2;
                }
            }
            $fields["hc_name"] = $hc_name;
            if (testHostCategorieExistence($hc_name)) {
                if ($val) {
                    $statement = $pearDB->prepare("INSERT INTO hostcategories VALUES (" . $val . ")");
                    $pearDB->execute($statement, $params);
                }
                $DBRESULT = $pearDB->query("SELECT MAX(hc_id) FROM hostcategories");
                $maxId = $DBRESULT->fetchRow();
                if (isset($maxId["MAX(hc_id)"]) && !$level) {
                    $hcAcl[$maxId["MAX(hc_id)"]] = $hcId;
                    $statement2 = $pearDB->prepare("SELECT DISTINCT hgr.host_host_id FROM hostcategories_relation hgr WHERE hgr.hostcategories_hc_id = ? ");
                    $result = $pearDB->execute($statement2, array($hcId));
                    $fields["hc_hosts"] = "";
                    while ($host = $result->fetchRow()) {
                        $pearDB->prepare("INSERT INTO hostcategories_relation VALUES ('', '" . $maxId["MAX(hc_id)"] . "', '" . $host["host_host_id"] . "')");
                        $fields["hc_hosts"] .= $host["host_host_id"] . ",";
                    }
                    $fields["hc_hosts"] = trim($fields["hc_hosts"], ",");
                    $centreon->CentreonLogAction->insertLog("hostcategories", $maxId["MAX(hc_id)"], $hc_name, "a", $fields);
                }
            }
        }
    }
    CentreonACL::duplicateHcAcl($hcAcl);
    $centreon->user->access->updateACL();
}

function insertHostCategoriesInDB($ret = array())
{
    global $centreon;

    $hc_id = insertHostCategories($ret);
    updateHostCategoriesHosts($hc_id, $ret);
    $centreon->user->access->updateACL();
    return $hc_id;
}

function updateHostCategoriesInDB($hc_id = null)
{
    global $centreon;
    if (!$hc_id) {
        return;
    }
    updateHostCategories($hc_id);
    updateHostCategoriesHosts($hc_id);
    $centreon->user->access->updateACL();
}

function insertHostCategories($ret = array())
{
    global $form, $pearDB, $centreon;

    if (!count($ret)) {
        $ret = $form->getSubmitValues();
    }
    $params = array();
    foreach ($ret as $key => $value) {
        switch ($key) {
            case 'hc_name':
            case 'hc_alias':
            case 'hc_comment':
                $value = filter_var($value, FILTER_SANITIZE_STRING);
                $params[] = $value;
                break;
            case 'hc_severity_level':
            case 'hc_severity_icon':
                $value = filter_var($value, FILTER_VALIDATE_INT);
                $value
                    ? $params[] = $value
                    : $params[] = null;
                break;
            case 'hc_activate':
                $value = filter_var(
                    $value['hc_activate'],
                    FILTER_VALIDATE_REGEXP,
                    array(
                        "options" => array(
                            "regexp" => "/^0|1$/"
                        )
                    )
                );
                $value
                    ? $params[] = $value
                    : $params[] = "0";
                break;
        }
    }
    $query = "
        INSERT INTO hostcategories
        (hc_name, hc_alias, level, icon_id, hc_activate, hc_comment)
        VALUES (?, ?, ?, ?, ?, ?)";
    $statement = $pearDB->prepare($query);
    $pearDB->execute($statement, $params);
    $DBRESULT = $pearDB->query("SELECT MAX(hc_id) FROM hostcategories");
    $hc_id = $DBRESULT->fetchRow();

    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($ret);

    $centreon->CentreonLogAction->insertLog("hostcategories", $hc_id["MAX(hc_id)"], CentreonDB::escape($ret["hc_name"]), "a", $fields);
    return ($hc_id["MAX(hc_id)"]);
}

function updateHostCategories($hc_id)
{

    $hcId = filter_var($hc_id, FILTER_VALIDATE_INT);
    if ($hcId === false) {
        return;
    }

    global $form, $pearDB, $centreon;
    $ret = array();
    $ret = $form->getSubmitValues();

    foreach ($ret as $key => $value) {
        switch ($key) {
            case 'hc_name':
                $value = filter_var($value, FILTER_SANITIZE_STRING);
                $params[] = $value;
                break;
            case 'hc_alias':
            case 'hc_comment':
                $value = filter_var($value, FILTER_SANITIZE_STRING);
                $params[] = $value;
                break;
            case 'hc_severity_level':
            case 'hc_severity_icon':
                $value = filter_var($value, FILTER_VALIDATE_INT);
                $value
                    ? $params[] = $value
                    : $params[] = null;
                break;
            case 'hc_activate':
                $value = filter_var(
                    $value['hc_activate'],
                    FILTER_VALIDATE_REGEXP,
                    array(
                        "options" => array(
                            "regexp" => "/^0|1$/"
                        )
                    )
                );
                $value
                    ? $params[] = $value
                    : $params[] = "0";
                break;
        }
    }
    $params[] = $hcId;

    $query = "
        UPDATE hostcategories SET
        hc_name = ?,
        hc_alias = ?,
        level = ?,
        icon_id = ?,
        hc_activate = ?,
        hc_comment = ?
        WHERE hc_id = ?";
    $statement = $pearDB->prepare($query);
    $pearDB->execute($statement, $params);

    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($ret);

    $centreon->CentreonLogAction->insertLog("hostcategories", $hc_id, CentreonDB::escape($ret["hc_name"]), "c", $fields);
}

function updateHostCategoriesHosts($hc_id, $ret = array())
{
    global $form, $pearDB;

    $hcId = filter_var($hc_id, FILTER_VALIDATE_INT);
    if ($hcId === false) {
        return;
    }

    /*
	 * Special Case, delete relation between host/service, when service
	 * is linked to hostcategories in escalation, dependencies, osl
	 *
	 * Get initial Host list to make a diff after deletion
	 */
    $hostsOLD = array();
    $statement = $pearDB->prepare("SELECT host_host_id FROM hostcategories_relation WHERE hostcategories_hc_id = ?");
    $result = $pearDB->execute($statement, array($hcId));
    while ($host = $result->fetchRow()) {

        $hostsOLD[$host["host_host_id"]] = $host["host_host_id"];
    }
    $result->free();

    /*
	 * Update Host HG relations
	 */
    $statement = $pearDB->prepare("DELETE FROM hostcategories_relation WHERE hostcategories_hc_id = ? ");
    $pearDB->execute($statement, array($hcId));
    $ret = isset($ret["hc_hosts"]) ? $ret["hc_hosts"] : CentreonUtils::mergeWithInitialValues($form, 'hc_hosts');
    $hgNEW = array();

    $rq = "INSERT INTO hostcategories_relation (hostcategories_hc_id, host_host_id) VALUES ";
    $params = array();
    for ($i = 0; $i < count($ret); $i++) {
        $params[] = $hcId;
        if ($i != 0) {
            $rq .= ", ";
        }
        $rq .= "( '?', '" . $ret[$i] . "')";

        $hostsNEW[$ret[$i]] = $ret[$i];
    }
    if ($i != 0) {
        $statement2 = $pearDB->prepare($rq);
        $pearDB->execute($statement2, $params);
    }
    isset($ret["hc_hostsTemplate"]) ? $ret = $ret["hc_hostsTemplate"] : $ret = $form->getSubmitValue("hc_hostsTemplate");
    $rq = "INSERT INTO hostcategories_relation (hostcategories_hc_id, host_host_id) VALUES ";
    $params = array();
    for ($i = 0; $i < count($ret); $i++) {
        $params[] = $hcId;
        if ($i != 0) {
            $rq .= ", ";
        }
        $rq .= " ( '?', '" . $ret[$i] . "')";

        $hostsNEW[$ret[$i]] = $ret[$i];
    }
    if ($i != 0) {
        $statement3 = $pearDB->prepare($rq);
        $pearDB->execute($statement3, $params);
    }
}
