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
 *
 * is this action rules already existing
 * @param $name
 */
function testActionExistence($name = null)
{
    global $pearDB, $form;

    $id = null;
    if (isset($form)) {
        $id = $form->getSubmitValue('acl_action_id');
    }
    $DBRESULT = $pearDB->query("SELECT acl_action_id, acl_action_name FROM acl_actions WHERE acl_action_name = '".htmlentities($name, ENT_QUOTES, "UTF-8")."'");
    $cg = $DBRESULT->fetchRow();
    #Modif case
    if ($DBRESULT->rowCount() >= 1 && $cg["acl_action_id"] == $id) {
        return true;
    } #Duplicate entry
    elseif ($DBRESULT->rowCount() >= 1 && $cg["acl_action_id"] != $id) {
        return false;
    } else {
        return true;
    }
}

/**
 *
 * enable an action rules
 * @param $acl_action_id
 */
function enableActionInDB($acl_action_id = null)
{
    if (!$acl_action_id) {
        return;
    }
    global $pearDB;
    $DBRESULT = $pearDB->query("UPDATE acl_actions SET acl_action_activate = '1' WHERE acl_action_id = '".$acl_action_id."'");
}

/**
 *
 * disable an action rules
 * @param $acl_action_id
 */
function disableActionInDB($acl_action_id = null)
{
    if (!$acl_action_id) {
        return;
    }
    global $pearDB;
    $DBRESULT = $pearDB->query("UPDATE acl_actions SET acl_action_activate = '0' WHERE acl_action_id = '".$acl_action_id."'");
}

/**
 *
 * delete an action rules
 * @param $Actions
 */
function deleteActionInDB($Actions = array())
{
    global $pearDB;
    foreach ($Actions as $key => $value) {
        $DBRESULT = $pearDB->query("DELETE FROM acl_actions WHERE acl_action_id = '".$key."'");
        $DBRESULT = $pearDB->query("DELETE FROM acl_actions_rules WHERE acl_action_rule_id = '".$key."'");
        $DBRESULT = $pearDB->query("DELETE FROM acl_group_actions_relations WHERE acl_action_id = '".$key."'");
    }
}

/**
 *
 * Duplicate an action rules
 * @param $Actions
 * @param $nbrDup
 */
function multipleActionInDB($Actions = array(), $nbrDup = array())
{
    foreach ($Actions as $key => $value) {
        global $pearDB;
        $DBRESULT = $pearDB->query("SELECT * FROM acl_actions WHERE acl_action_id = '".$key."' LIMIT 1");
        $row = $DBRESULT->fetchRow();
        $row["acl_action_id"] = '';
        for ($i = 1; $i <= $nbrDup[$key]; $i++) {
            $val = null;
            foreach ($row as $key2 => $value2) {
                $key2 == "acl_action_name" ? ($acl_action_name = $value2 = $value2."_".$i) : null;
                $val ? $val .= ", '".$value2."'" : $val .= "'".$value2."'";
            }
            if (testActionExistence($acl_action_name)) {
                $val ? $rq = "INSERT INTO acl_actions VALUES (".$val.")" : $rq = null;
                $DBRESULT = $pearDB->query($rq);
                $DBRESULT = $pearDB->query("SELECT MAX(acl_action_id) FROM acl_actions");
                $maxId = $DBRESULT->fetchRow();
                $DBRESULT->closeCursor();
                if (isset($maxId["MAX(acl_action_id)"])) {
                    $DBRESULT = $pearDB->query("SELECT DISTINCT acl_group_id,acl_action_id FROM acl_group_actions_relations WHERE acl_action_id = '".$key."'");
                    while ($cct = $DBRESULT->fetchRow()) {
                        $DBRESULT2 = $pearDB->query("INSERT INTO acl_group_actions_relations VALUES ('', '".$maxId["MAX(acl_action_id)"]."', '".$cct["acl_group_id"]."')");
                    }

                    # Duplicate Actions
                    $DBRESULT = $pearDB->query("SELECT acl_action_rule_id,acl_action_name FROM acl_actions_rules WHERE acl_action_rule_id = '".$key."'");
                    while ($acl = $DBRESULT->fetchRow()) {
                        $DBRESULT2 = $pearDB->query("INSERT INTO acl_actions_rules VALUES ('', '".$maxId["MAX(acl_action_id)"]."', '".$acl["acl_action_name"]."')");
                    }

                    $DBRESULT->closeCursor();
                }
            }
        }
    }
}

/**
 *
 * Insert all information in DB
 * @param $ret
 */
function insertActionInDB($ret = array())
{
    $acl_action_id = insertAction($ret);
    updateGroupActions($acl_action_id, $ret);
    updateRulesActions($acl_action_id, $ret);
    return $acl_action_id;
}

/**
 *
 * Insert actions
 * @param $ret
 */
function insertAction($ret)
{
    global $form, $pearDB;
    if (!count($ret)) {
        $ret = $form->getSubmitValues();
    }

        $rq = "INSERT INTO acl_actions ";
    $rq .= "(acl_action_name, acl_action_description, acl_action_activate) ";
    $rq .= "VALUES ";
    $rq .= "('".htmlentities($ret["acl_action_name"], ENT_QUOTES, "UTF-8")."', '".htmlentities($ret["acl_action_description"], ENT_QUOTES, "UTF-8")."', '".htmlentities($ret["acl_action_activate"]["acl_action_activate"], ENT_QUOTES, "UTF-8")."')";
    $DBRESULT = $pearDB->query($rq);
    $DBRESULT = $pearDB->query("SELECT MAX(acl_action_id) FROM acl_actions");
    $cg_id = $DBRESULT->fetchRow();
    return ($cg_id["MAX(acl_action_id)"]);
}

/**
 *
 * Summary function
 * @param $acl_action_id
 */
function updateActionInDB($acl_action_id = null)
{
    if (!$acl_action_id) {
        return;
    }
    updateAction($acl_action_id);
    updateGroupActions($acl_action_id);
}

/**
 *
 * Update all Actions
 * @param $acl_action_id
 */
function updateAction($acl_action_id = null)
{
    if (!$acl_action_id) {
        return;
    }
    global $form, $pearDB;

    $ret = array();
    $ret = $form->getSubmitValues();
    $rq = "UPDATE acl_actions ";
    $rq .= "SET acl_action_name = '".htmlentities($ret["acl_action_name"], ENT_QUOTES, "UTF-8")."', " .
            "acl_action_description = '".htmlentities($ret["acl_action_description"], ENT_QUOTES, "UTF-8")."', " .
            "acl_action_activate = '".htmlentities($ret["acl_action_activate"]["acl_action_activate"], ENT_QUOTES, "UTF-8")."' " .
            "WHERE acl_action_id = '".$acl_action_id."'";
    $DBRESULT = $pearDB->query($rq);
}

/**
 *
 * Update group action information in DB
 * @param $acl_action_id
 * @param $ret
 */
function updateGroupActions($acl_action_id, $ret = array())
{
    if (!$acl_action_id) {
        return;
    }
    global $form, $pearDB;

    $rq = "DELETE FROM acl_group_actions_relations WHERE acl_action_id = '".$acl_action_id."'";
    $DBRESULT = $pearDB->query($rq);
    if (isset($_POST["acl_groups"])) {
        foreach ($_POST["acl_groups"] as $id) {
            $rq = "INSERT INTO acl_group_actions_relations ";
            $rq .= "(acl_group_id, acl_action_id) ";
            $rq .= "VALUES ";
            $rq .= "('".$id."', '".$acl_action_id."')";
            $DBRESULT = $pearDB->query($rq);
        }
    }
}

/**
 *
 * update all Rules in DB
 * @param $acl_action_id
 * @param $ret
 */
function updateRulesActions($acl_action_id, $ret = array())
{
    global $form, $pearDB;

    if (!$acl_action_id) {
        return;
    }

    $rq = "DELETE FROM acl_actions_rules WHERE acl_action_rule_id = '".$acl_action_id."'";
    $DBRESULT = $pearDB->query($rq);

    $actions = array();
    $actions = listActions();

    foreach ($actions as $action) {
        if (isset($_POST[$action])) {
            $rq = "INSERT INTO acl_actions_rules ";
            $rq .= "(acl_action_rule_id, acl_action_name) ";
            $rq .= "VALUES ";
            $rq .= "('".$acl_action_id."', '".$action."')";
            $DBRESULT = $pearDB->query($rq);
        }
    }
}

/**
 *
 * list all actions
 */
function listActions()
{
    $actions = array();

    # Global Functionnality access
    $actions[] = "poller_listing";
    $actions[] = "poller_stats";
    $actions[] = "top_counter";

    # Services Actions
    $actions[] = "service_checks";
    $actions[] = "service_notifications";
    $actions[] = "service_acknowledgement";
    $actions[] = "service_disacknowledgement";
    $actions[] = "service_schedule_check";
    $actions[] = "service_schedule_forced_check";
    $actions[] = "service_schedule_downtime";
    $actions[] = "service_comment";
    $actions[] = "service_event_handler";
    $actions[] = "service_flap_detection";
    $actions[] = "service_passive_checks";
    $actions[] = "service_submit_result";
    $actions[] = "service_display_command";

    # Hosts Actions
    $actions[] = "host_checks";
    $actions[] = "host_notifications";
    $actions[] = "host_acknowledgement";
    $actions[] = "host_disacknowledgement";
    $actions[] = "host_schedule_check";
    $actions[] = "host_schedule_forced_check";
    $actions[] = "host_schedule_downtime";
    $actions[] = "host_comment";
    $actions[] = "host_event_handler";
    $actions[] = "host_flap_detection";
    $actions[] = "host_checks_for_services";
    $actions[] = "host_notifications_for_services";
    $actions[] = "host_submit_result";

    # Global Nagios External Commands
    $actions[] = "global_shutdown";
    $actions[] = "global_restart";
    $actions[] = "global_notifications";
    $actions[] = "global_service_checks";
    $actions[] = "global_service_passive_checks";
    $actions[] = "global_host_checks";
    $actions[] = "global_host_passive_checks";
    $actions[] = "global_event_handler";
    $actions[] = "global_flap_detection";
    $actions[] = "global_service_obsess";
    $actions[] = "global_host_obsess";
    $actions[] = "global_perf_data";

    $actions[] = "generate_cfg";
    $actions[] = "generate_trap";

    return $actions;
}
