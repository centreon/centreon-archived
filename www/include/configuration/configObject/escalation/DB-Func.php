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

require_once _CENTREON_PATH_ . 'www/class/centreonLDAP.class.php';
require_once _CENTREON_PATH_ . 'www/class/centreonContactgroup.class.php';

function testExistence($name = null)
{
    global $pearDB;
    global $form;
    $id = null;
    if (isset($form)) {
        $id = $form->getSubmitValue('esc_id');
    }
    $DBRESULT = $pearDB->query("SELECT esc_name, esc_id FROM escalation WHERE esc_name = '".$name."'");
    $esc = $DBRESULT->fetchRow();
    #Modif case
    if ($DBRESULT->numRows() >= 1 && $esc["esc_id"] == $id) {
        return true;
    #Duplicate entry
    } elseif ($DBRESULT->numRows() >= 1 && $esc["esc_id"] != $id) {
        return false;
    } else {
        return true;
    }
}

function deleteEscalationInDB($escalations = array())
{
    global $pearDB, $centreon;

    foreach ($escalations as $key => $value) {
        $DBRESULT2 = $pearDB->query("SELECT esc_name FROM `escalation` WHERE `esc_id` = '".$key."' LIMIT 1");
        $row = $DBRESULT2->fetchRow();

        $DBRESULT = $pearDB->query("DELETE FROM escalation WHERE esc_id = '".$key."'");
        $centreon->CentreonLogAction->insertLog("escalation", $key, $row['esc_name'], "d");
    }
}

function multipleEscalationInDB($escalations = array(), $nbrDup = array())
{
    foreach ($escalations as $key => $value) {
        global $pearDB, $centreon;
        $DBRESULT = $pearDB->query("SELECT * FROM escalation WHERE esc_id = '".$key."' LIMIT 1");
        $row = $DBRESULT->fetchRow();
        $row["esc_id"] = '';
        for ($i = 1; $i <= $nbrDup[$key]; $i++) {
            $val = null;
            foreach ($row as $key2 => $value2) {
                $key2 == "esc_name" ? ($esc_name = $value2 = $value2 . " " . $i) : null;
                $val ? $val .= ($value2 != null ? (", '" . $value2 . "'") : ", NULL")
                    : $val .= ($value2 != null ? ("'" . $value2 . "'") : "NULL");
                if ($key2 != "esc_id") {
                    $fields[$key2] = $value2;
                }
                if (isset($esc_name)) {
                    $fields["esc_name"] = $esc_name;
                }
            }
            if (isset($esc_name) && testExistence($esc_name)) {
                $val ? $rq = "INSERT INTO escalation VALUES (".$val.")" : $rq = null;
                $DBRESULT = $pearDB->query($rq);
                $DBRESULT = $pearDB->query("SELECT MAX(esc_id) FROM escalation");
                $maxId = $DBRESULT->fetchRow();
                if (isset($maxId["MAX(esc_id)"])) {
                    $DBRESULT = $pearDB->query(
                        "SELECT DISTINCT contactgroup_cg_id "
                        . "FROM escalation_contactgroup_relation "
                        . "WHERE escalation_esc_id = '" . $key . "'"
                    );
                    $fields["esc_cgs"] = "";
                    while ($cg = $DBRESULT->fetchRow()) {
                        $DBRESULT2 = $pearDB->query(
                            "INSERT INTO escalation_contactgroup_relation "
                            . "VALUES ('', '" . $maxId["MAX(esc_id)"]."', '"
                            . $cg["contactgroup_cg_id"]."')"
                        );
                        $fields["esc_cgs"] .= $cg["contactgroup_cg_id"] . ",";
                    }
                    $fields["esc_cgs"] = trim($fields["esc_cgs"], ",");
                    $DBRESULT = $pearDB->query(
                        "SELECT DISTINCT host_host_id "
                        . "FROM escalation_host_relation "
                        . "WHERE escalation_esc_id = '".$key."'"
                    );
                    $fields["esc_hosts"] = "";
                    while ($host = $DBRESULT->fetchRow()) {
                        $DBRESULT2 = $pearDB->query(
                            "INSERT INTO escalation_host_relation "
                            . "VALUES ('', '" . $maxId["MAX(esc_id)"]."', '"
                            . $host["host_host_id"]."')"
                        );
                        $fields["esc_hosts"] .= $host["host_host_id"] . ",";
                    }
                    $fields["esc_hosts"] = trim($fields["esc_hosts"], ",");
                    $DBRESULT = $pearDB->query(
                        "SELECT DISTINCT hostgroup_hg_id "
                        . "FROM escalation_hostgroup_relation "
                        . "WHERE escalation_esc_id = '".$key."'"
                    );
                    $fields["esc_hgs"] = "";
                    while ($hg = $DBRESULT->fetchRow()) {
                        $DBRESULT2 = $pearDB->query(
                            "INSERT INTO escalation_hostgroup_relation "
                            . "VALUES ('', '" . $maxId["MAX(esc_id)"]."', '"
                            . $hg["hostgroup_hg_id"]."')"
                        );
                        $fields["esc_hgs"] .= $hg["hostgroup_hg_id"] . ",";
                    }
                    $fields["esc_hgs"] = trim($fields["esc_hgs"], ",");
                    $DBRESULT = $pearDB->query(
                        "SELECT DISTINCT servicegroup_sg_id "
                        . "FROM escalation_servicegroup_relation "
                        . "WHERE escalation_esc_id = '" . $key . "'"
                    );
                    $fields["esc_sgs"] = "";
                    while ($sg = $DBRESULT->fetchRow()) {
                        $DBRESULT2 = $pearDB->query(
                            "INSERT INTO escalation_servicegroup_relation "
                            . "VALUES ('', '" . $maxId["MAX(esc_id)"] . "', '"
                            . $sg["servicegroup_sg_id"] . "')"
                        );
                        $fields["esc_sgs"] .= $sg["servicegroup_sg_id"] . ",";
                    }
                    $fields["esc_sgs"] = trim($fields["esc_sgs"], ",");
                    $DBRESULT = $pearDB->query(
                        "SELECT * FROM escalation_service_relation "
                        . "WHERE escalation_esc_id = '" . $key . "'"
                    );
                    $fields["esc_hServices"] = "";
                    while ($sv = $DBRESULT->fetchRow()) {
                        $DBRESULT2 = $pearDB->query(
                            "INSERT INTO escalation_service_relation "
                            . "VALUES ('', '" . $maxId["MAX(esc_id)"] . "', '"
                            . $sv["service_service_id"] . "', '"
                            . $sv["host_host_id"]."')"
                        );
                        $fields["esc_hServices"] .= $sv["service_service_id"] . ",";
                    }
                    $fields["esc_hServices"] = trim($fields["esc_hServices"], ",");
                    $DBRESULT = $pearDB->query(
                        "SELECT DISTINCT meta_service_meta_id "
                        . "FROM escalation_meta_service_relation "
                        . "WHERE escalation_esc_id = '" . $key . "'"
                    );
                    $fields["esc_metas"] = "";
                    while ($sv = $DBRESULT->fetchRow()) {
                        $DBRESULT2 = $pearDB->query(
                            "INSERT INTO escalation_meta_service_relation "
                            . "VALUES ('', '" . $maxId["MAX(esc_id)"] . "', '"
                            . $sv["meta_service_meta_id"] . "')"
                        );
                        $fields["esc_metas"] .= $sv["meta_service_meta_id"]  . ",";
                    }
                    $fields["esc_metas"] = trim($fields["esc_metas"], ",");
                    $centreon->CentreonLogAction->insertLog(
                        "escalation",
                        $maxId["MAX(esc_id)"],
                        $esc_name,
                        "a"
                    );
                }
            }
        }
    }
}

function updateEscalationInDB($esc_id = null)
{
    if (!$esc_id) {
        exit();
    }
    updateEscalation($esc_id);
    updateEscalationContactGroups($esc_id);
    updateEscalationHosts($esc_id);
    updateEscalationHostGroups($esc_id);
    updateEscalationServices($esc_id);
    updateEscalationMetaServices($esc_id);
    updateEscalationServiceGroups($esc_id);
}

function insertEscalationInDB()
{
    $esc_id = insertEscalation();
    updateEscalationContactGroups($esc_id);
    updateEscalationHosts($esc_id);
    updateEscalationHostGroups($esc_id);
    updateEscalationServices($esc_id);
    updateEscalationMetaServices($esc_id);
    updateEscalationServiceGroups($esc_id);
    return ($esc_id);
}

function insertEscalation()
{
    global $form, $pearDB, $centreon;
    $ret = array();
    $ret = $form->getSubmitValues();
    $rq = "INSERT INTO escalation ";
    $rq .= "(esc_name, esc_alias, first_notification, last_notification, "
        . "notification_interval, escalation_period, host_inheritance_to_services, "
        . "hostgroup_inheritance_to_services, escalation_options1, "
        . "escalation_options2, esc_comment) ";
    $rq .= "VALUES (";
    isset($ret["esc_name"]) && $ret["esc_name"] != null
        ? $rq .= "'" . htmlentities($ret["esc_name"], ENT_QUOTES, "UTF-8") . "', "
        : $rq .= "NULL, ";
    isset($ret["esc_alias"]) && $ret["esc_alias"] != null
        ? $rq .= "'" . htmlentities($ret["esc_alias"], ENT_QUOTES, "UTF-8") . "', "
        : $rq .= "NULL, ";
    isset($ret["first_notification"]) && $ret["first_notification"] != null
        ? $rq .= "'" . htmlentities($ret["first_notification"], ENT_QUOTES, "UTF-8") . "', "
        : $rq .= "NULL, ";
    isset($ret["last_notification"]) && $ret["last_notification"] != null
        ? $rq .= "'" . htmlentities($ret["last_notification"], ENT_QUOTES, "UTF-8") . "', "
        : $rq .= "NULL, ";
    isset($ret["notification_interval"]) && $ret["notification_interval"] != null
        ? $rq .= "'" . htmlentities($ret["notification_interval"], ENT_QUOTES, "UTF-8") . "', "
        : $rq .= "NULL, ";
    isset($ret["escalation_period"]) && $ret["escalation_period"] != null
        ? $rq .= "'" . htmlentities($ret["escalation_period"], ENT_QUOTES, "UTF-8") . "', "
        : $rq .= "NULL, ";
    $rq .= (isset($ret["host_inheritance_to_services"]) ? 1 : 0) . ', ';
    $rq .= (isset($ret["hostgroup_inheritance_to_services"]) ? 1 : 0) . ', ';
    isset($ret["escalation_options1"]) && $ret["escalation_options1"] != null
        ? $rq .= "'" . implode(",", array_keys($ret["escalation_options1"])) . "', "
        : $rq .= "NULL, ";
    isset($ret["escalation_options2"]) && $ret["escalation_options2"] != null
        ? $rq .= "'" . implode(",", array_keys($ret["escalation_options2"])) . "', "
        : $rq .= "NULL, ";
    isset($ret["esc_comment"]) && $ret["esc_comment"] != null
        ? $rq .= "'" . htmlentities($ret["esc_comment"], ENT_QUOTES, "UTF-8") . "' "
        : $rq .= "NULL ";
    $rq .= ")";
    $DBRESULT = $pearDB->query($rq);
    $DBRESULT = $pearDB->query("SELECT MAX(esc_id) FROM escalation");
    $esc_id = $DBRESULT->fetchRow();
    $fields["esc_name"] = htmlentities($ret["esc_name"], ENT_QUOTES, "UTF-8");
    $fields["esc_alias"] = htmlentities($ret["esc_alias"], ENT_QUOTES, "UTF-8");
    $fields["first_notification"] = htmlentities($ret["first_notification"], ENT_QUOTES, "UTF-8");
    $fields["last_notification"] = htmlentities($ret["last_notification"], ENT_QUOTES, "UTF-8");
    $fields["notification_interval"] = htmlentities($ret["notification_interval"], ENT_QUOTES, "UTF-8");
    $fields["escalation_period"] = htmlentities($ret["escalation_period"], ENT_QUOTES, "UTF-8");
    $fields["escalation_options1"] = "";
    if (isset($ret["escalation_options1"]) && $ret["escalation_options1"] != null) {
        $fields["escalation_options1"] = implode(",", array_keys($ret["escalation_options1"]));
    }
    $fields["escalation_options2"] = "";
    if (isset($ret["escalation_options2"]) && $ret["escalation_options2"] != null) {
        $fields["escalation_options2"] = implode(",", array_keys($ret["escalation_options2"]));
    }
    $fields["esc_comment"] = htmlentities($ret["esc_comment"], ENT_QUOTES, "UTF-8");
    $fields["esc_cgs"] = "";
    if (isset($ret["esc_cgs"])) {
        $fields["esc_cgs"] = implode(",", $ret["esc_cgs"]);
    }
    $fields["esc_hosts"] = "";
    if (isset($ret["host_inheritance_to_services"])) {
        $fields["host_inheritance_to_services"] = $ret["host_inheritance_to_services"];
    }
    if (isset($ret["hostgroup_inheritance_to_services"])) {
        $fields["hostgroup_inheritance_to_services"] = $ret["hostgroup_inheritance_to_services"];
    }
    if (isset($ret["esc_hosts"])) {
        $fields["esc_hosts"] = implode(",", $ret["esc_hosts"]);
    }
    $fields["esc_hgs"] = "";
    if (isset($ret["esc_hgs"])) {
        $fields["esc_hgs"] = implode(",", $ret["esc_hgs"]);
    }
    $fields["esc_sgs"] = "";
    if (isset($ret["esc_sgs"])) {
        $fields["esc_sgs"] = implode(",", $ret["esc_sgs"]);
    }
    $fields["esc_hServices"] = "";
    if (isset($ret["esc_hServices"])) {
        $fields["esc_hServices"] = implode(",", $ret["esc_hServices"]);
    }
    $fields["esc_metas"] = "";
    if (isset($ret["esc_metas"])) {
        $fields["esc_metas"] = implode(",", $ret["esc_metas"]);
    }
    $centreon->CentreonLogAction->insertLog(
        "escalation",
        $esc_id["MAX(esc_id)"],
        htmlentities($ret["esc_name"], ENT_QUOTES, "UTF-8"),
        "a",
        $fields
    );
    return ($esc_id["MAX(esc_id)"]);
}

function updateEscalation($esc_id = null)
{
    if (!$esc_id) {
        exit();
    }
    global $form;
    global $pearDB, $centreon;
    $ret = array();
    $ret = $form->getSubmitValues();
    $rq = "UPDATE escalation SET ";
    $rq .= "esc_name = ";
    isset($ret["esc_name"]) && $ret["esc_name"] != null
        ? $rq .= "'" . htmlentities($ret["esc_name"], ENT_QUOTES, "UTF-8") . "', "
        : $rq .= "NULL, ";
    $rq .= "esc_alias = ";
    isset($ret["esc_alias"]) && $ret["esc_alias"] != null
        ? $rq .= "'" . htmlentities($ret["esc_alias"], ENT_QUOTES, "UTF-8") . "', "
        : $rq .= "NULL, ";
    $rq .= "first_notification = ";
    isset($ret["first_notification"]) && $ret["first_notification"] != null
        ? $rq .= "'" . htmlentities($ret["first_notification"], ENT_QUOTES, "UTF-8") . "', "
        : $rq .= "NULL, ";
    $rq .= "last_notification = ";
    isset($ret["last_notification"]) && $ret["last_notification"] != null
        ? $rq .= "'" . htmlentities($ret["last_notification"], ENT_QUOTES, "UTF-8") . "', "
        : $rq .= "NULL, ";
    $rq .= "notification_interval = ";
    isset($ret["notification_interval"]) && $ret["notification_interval"] != null
        ? $rq .= "'" . htmlentities($ret["notification_interval"], ENT_QUOTES, "UTF-8") . "', "
        : $rq .= "NULL, ";
    $rq .= "escalation_period = ";
    isset($ret["escalation_period"]) && $ret["escalation_period"] != null
        ? $rq .= "'" . htmlentities($ret["escalation_period"], ENT_QUOTES, "UTF-8") . "', "
        : $rq .= "NULL, ";
    $rq .= "host_inheritance_to_services = ";
    $rq .= (isset($ret['host_inheritance_to_services']) ? 1 : 0) . ', ';
    $rq .= "hostgroup_inheritance_to_services = ";
    $rq .= (isset($ret['hostgroup_inheritance_to_services']) ? 1 : 0) . ', ';
    $rq .= "escalation_options1 = ";
    isset($ret["escalation_options1"]) && $ret["escalation_options1"] != null
        ? $rq .= "'" . implode(",", array_keys($ret["escalation_options1"])) . "', "
        : $rq .= "NULL, ";
    $rq .= "escalation_options2 = ";
    isset($ret["escalation_options2"]) && $ret["escalation_options2"] != null
        ? $rq .= "'" . implode(",", array_keys($ret["escalation_options2"])) . "', "
        : $rq .= "NULL, ";
    $rq .= "esc_comment = ";
    isset($ret["esc_comment"]) && $ret["esc_comment"] != null
        ? $rq .= "'" . htmlentities($ret["esc_comment"], ENT_QUOTES, "UTF-8") . "' "
        : $rq .= "NULL ";
    $rq .= "WHERE esc_id = '".$esc_id."'";
    $DBRESULT = $pearDB->query($rq);
    $fields["esc_name"] = htmlentities($ret["esc_name"], ENT_QUOTES, "UTF-8");
    $fields["esc_alias"] = htmlentities($ret["esc_alias"], ENT_QUOTES, "UTF-8");
    $fields["first_notification"] = htmlentities($ret["first_notification"], ENT_QUOTES, "UTF-8");
    $fields["last_notification"] = htmlentities($ret["last_notification"], ENT_QUOTES, "UTF-8");
    $fields["notification_interval"] = htmlentities($ret["notification_interval"], ENT_QUOTES, "UTF-8");
    $fields["escalation_period"] = htmlentities($ret["escalation_period"], ENT_QUOTES, "UTF-8");
    if (isset($ret["host_inheritance_to_services"])) {
        $fields["host_inheritance_to_services"] = $ret["host_inheritance_to_services"];
    }
    if (isset($ret["hostgroup_inheritance_to_services"])) {
        $fields["hostgroup_inheritance_to_services"] = $ret["hostgroup_inheritance_to_services"];
    }
    $fields["esc_comment"] = htmlentities($ret["esc_comment"], ENT_QUOTES, "UTF-8");
    $fields["esc_cgs"] = "";
    if (isset($ret["esc_cgs"])) {
        $fields["esc_cgs"] = implode(",", $ret["esc_cgs"]);
    }
    $fields["esc_hosts"] = "";
    if (isset($ret["esc_hosts"])) {
        $fields["esc_hosts"] = implode(",", $ret["esc_hosts"]);
    }
    $fields["esc_hgs"] = "";
    if (isset($ret["esc_hgs"])) {
        $fields["esc_hgs"] = implode(",", $ret["esc_hgs"]);
    }
    $fields["esc_sgs"] = "";
    if (isset($ret["esc_sgs"])) {
        $fields["esc_sgs"] = implode(",", $ret["esc_sgs"]);
    }
    $fields["esc_hServices"] = "";
    if (isset($ret["esc_hServices"])) {
        $fields["esc_hServices"] = implode(",", $ret["esc_hServices"]);
    }
    $fields["esc_metas"] = "";
    if (isset($ret["esc_metas"])) {
        $fields["esc_metas"] = implode(",", $ret["esc_metas"]);
    }
    $centreon->CentreonLogAction->insertLog(
        "escalation",
        $esc_id["MAX(esc_id)"],
        htmlentities($ret["esc_name"], ENT_QUOTES, "UTF-8"),
        "c",
        $fields
    );
}

function updateEscalationContactGroups($esc_id = null)
{
    if (!$esc_id) {
        exit();
    }
    global $form;
    global $pearDB;
    $rq = "DELETE FROM escalation_contactgroup_relation ";
    $rq .= "WHERE escalation_esc_id = '".$esc_id."'";
    $DBRESULT = $pearDB->query($rq);
    $ret = array();
    $ret = CentreonUtils::mergeWithInitialValues($form, 'esc_cgs');
    $cg = new CentreonContactgroup($pearDB);
    for ($i = 0; $i < count($ret); $i++) {
        if (!is_numeric($ret[$i])) {
            $res = $cg->insertLdapGroup($ret[$i]);
            if ($res != 0) {
                $ret[$i] = $res;
            } else {
                continue;
            }
        }
        $rq = "INSERT INTO escalation_contactgroup_relation ";
        $rq .= "(escalation_esc_id, contactgroup_cg_id) ";
        $rq .= "VALUES ";
        $rq .= "('".$esc_id."', '".$ret[$i]."')";
        $DBRESULT = $pearDB->query($rq);
    }
}

function updateEscalationHosts($esc_id = null)
{
    if (!$esc_id) {
        exit();
    }
    global $form;
    global $pearDB;
    $rq = "DELETE FROM escalation_host_relation ";
    $rq .= "WHERE escalation_esc_id = '".$esc_id."'";
    $DBRESULT = $pearDB->query($rq);
    $ret = array();
    $ret = CentreonUtils::mergeWithInitialValues($form, 'esc_hosts');
    for ($i = 0; $i < count($ret); $i++) {
        $rq = "INSERT INTO escalation_host_relation ";
        $rq .= "(escalation_esc_id, host_host_id) ";
        $rq .= "VALUES ";
        $rq .= "('".$esc_id."', '".$ret[$i]."')";
        $DBRESULT = $pearDB->query($rq);
    }
}

function updateEscalationHostGroups($esc_id = null)
{
    if (!$esc_id) {
        exit();
    }
    global $form;
    global $pearDB;
    $rq = "DELETE FROM escalation_hostgroup_relation ";
    $rq .= "WHERE escalation_esc_id = '".$esc_id."'";
    $DBRESULT = $pearDB->query($rq);
    $ret = array();
    $ret = CentreonUtils::mergeWithInitialValues($form, 'esc_hgs');
    for ($i = 0; $i < count($ret); $i++) {
        $rq = "INSERT INTO escalation_hostgroup_relation ";
        $rq .= "(escalation_esc_id, hostgroup_hg_id) ";
        $rq .= "VALUES ";
        $rq .= "('".$esc_id."', '".$ret[$i]."')";
        $DBRESULT = $pearDB->query($rq);
    }
}

function updateEscalationServiceGroups($esc_id = null)
{
    if (!$esc_id) {
        exit();
    }
    global $form;
    global $pearDB;
    $rq = "DELETE FROM escalation_servicegroup_relation ";
    $rq .= "WHERE escalation_esc_id = '".$esc_id."'";
    $DBRESULT = $pearDB->query($rq);
    $ret = array();
    $ret = CentreonUtils::mergeWithInitialValues($form, 'esc_sgs');
    for ($i = 0; $i < count($ret); $i++) {
        $rq = "INSERT INTO escalation_servicegroup_relation ";
        $rq .= "(escalation_esc_id, servicegroup_sg_id) ";
        $rq .= "VALUES ";
        $rq .= "('".$esc_id."', '".$ret[$i]."')";
        $DBRESULT = $pearDB->query($rq);
    }
}

function updateEscalationServices($esc_id = null)
{
    if (!$esc_id) {
        exit();
    }
    global $form;
    global $pearDB;
    $rq = "DELETE FROM escalation_service_relation ";
    $rq .= "WHERE escalation_esc_id = '".$esc_id."'";
    $DBRESULT = $pearDB->query($rq);
    $ret = array();
    $ret = CentreonUtils::mergeWithInitialValues($form, 'esc_hServices');
    for ($i = 0; $i < count($ret); $i++) {
        $exp = explode("-", $ret[$i]);
        if (count($exp) == 2) {
            $rq = "INSERT INTO escalation_service_relation ";
            $rq .= "(escalation_esc_id, service_service_id, host_host_id) ";
            $rq .= "VALUES ";
            $rq .= "('".$esc_id."', '".$exp[1]."', '".$exp[0]."')";
            $DBRESULT = $pearDB->query($rq);
        }
    }
}

function updateEscalationMetaServices($esc_id = null)
{
    if (!$esc_id) {
        exit();
    }
    global $form;
    global $pearDB;
    $rq = "DELETE FROM escalation_meta_service_relation ";
    $rq .= "WHERE escalation_esc_id = '".$esc_id."'";
    $DBRESULT = $pearDB->query($rq);
    $ret = array();
    $ret = CentreonUtils::mergeWithInitialValues($form, 'esc_metas');
    for ($i = 0; $i < count($ret); $i++) {
        $rq = "INSERT INTO escalation_meta_service_relation ";
        $rq .= "(escalation_esc_id, meta_service_meta_id) ";
        $rq .= "VALUES ";
        $rq .= "('".$esc_id."', '".$ret[$i]."')";
        $DBRESULT = $pearDB->query($rq);
    }
}
