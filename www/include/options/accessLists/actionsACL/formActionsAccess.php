<?php

/*
 * Copyright 2005-2021 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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

$informationsService = $dependencyInjector['centreon_remote.informations_service'];
$serverIsMaster = $informationsService->serverIsMaster();

/*
 * Database retrieve information for Modify a present "Action Access"
 */
if (($o === "c") && $aclActionId) {
    // 1. Get "Actions Rule" id selected by user
    $statement = $pearDB->prepare(
        "SELECT * FROM acl_actions WHERE acl_action_id = :aclActionId LIMIT 1"
    );
    $statement->bindValue(':aclActionId', $aclActionId, \PDO::PARAM_INT);
    $statement->execute();
    $action_infos = array();
    $action_infos = array_map("myDecode", $statement->fetch());

    // 2. Get "Groups" id linked with the selected Rule in order to initialize the form
    $statement = $pearDB->prepare(
        "SELECT DISTINCT acl_group_id FROM acl_group_actions_relations " .
        "WHERE acl_action_id = :aclActionId"
    );
    $statement->bindValue(':aclActionId', $aclActionId, \PDO::PARAM_INT);
    $statement->execute();
    $selected = array();
    while ($contacts = $statement->fetch()) {
        $selected[] = $contacts["acl_group_id"];
    }
    $action_infos["acl_groups"] = $selected;
    $statement = $pearDB->prepare(
        "SELECT acl_action_name FROM `acl_actions_rules` " .
        "WHERE `acl_action_rule_id` = :aclActionId"
    );
    $statement->bindValue(':aclActionId', $aclActionId, \PDO::PARAM_INT);
    $statement->execute();
    $selected_actions = array();
    while ($act = $statement->fetch()) {
        $selected_actions[$act["acl_action_name"]] = 1;
    }

    $statement->closeCursor();
}

// Database retrieve information for differents elements list we need on the page
// Groups list comes from Database and stores in $groups Array
$groups = array();
$DBRESULT = $pearDB->query("SELECT acl_group_id,acl_group_name FROM acl_groups ORDER BY acl_group_name");

while ($group = $DBRESULT->fetchRow()) {
    $groups[$group["acl_group_id"]] = CentreonUtils::escapeSecure(
        $group["acl_group_name"],
        CentreonUtils::ESCAPE_ALL
    );
}
$DBRESULT->closeCursor();

// Var information to format the element
$attrsText = array("size" => "30");
$attrsAdvSelect = array("style" => "width: 300px; height: 220px;");
$attrsTextarea = array("rows" => "5", "cols" => "60");
$eTemplate = "<table style='border:0px;'><tr><td>{unselected}</td><td align='center'>{add}<br /><br /><br />" .
    "{remove}</td><td>{selected}</td></tr></table>";

// Form begin
$form = new HTML_QuickFormCustom('Form', 'post', "?p=" . $p);
if ($o == "a") {
    $form->addElement('header', 'title', _("Add an Action"));
} elseif ($o == "c") {
    $form->addElement('header', 'title', _("Modify an Action"));
} elseif ($o == "w") {
    $form->addElement('header', 'title', _("View an Action"));
}

// Basic information
$form->addElement('header', 'information', _("General Information"));
$form->addElement('text', 'acl_action_name', _("Action Name"), $attrsText);
$form->addElement('text', 'acl_action_description', _("Description"), $attrsText);

// Services
if ($serverIsMaster) {
    $form->addElement('checkbox', 'service_checks', _("Enable/Disable Checks for a service"));
    $form->addElement('checkbox', 'service_notifications', _("Enable/Disable Notifications for a service"));
}
$form->addElement('checkbox', 'service_acknowledgement', _("Acknowledge a service"));
$form->addElement('checkbox', 'service_disacknowledgement', _("Disacknowledge a service"));
$form->addElement('checkbox', 'service_schedule_check', _("Re-schedule the next check for a service"));
$form->addElement('checkbox', 'service_schedule_forced_check', _("Re-schedule the next check for a service (Forced)"));
$form->addElement('checkbox', 'service_schedule_downtime', _("Schedule downtime for a service"));
$form->addElement('checkbox', 'service_comment', _("Add/Delete a comment for a service"));
if ($serverIsMaster) {
    $form->addElement('checkbox', 'service_event_handler', _("Enable/Disable Event Handler for a service"));
    $form->addElement('checkbox', 'service_flap_detection', _("Enable/Disable Flap Detection of a service"));
    $form->addElement('checkbox', 'service_passive_checks', _("Enable/Disable passive checks of a service"));
}
$form->addElement('checkbox', 'service_submit_result', _("Submit result for a service"));
$form->addElement('checkbox', 'service_display_command', _("Display executed command by monitoring engine"));

// Hosts
if ($serverIsMaster) {
    $form->addElement('checkbox', 'host_checks', _("Enable/Disable Checks for a host"));
    $form->addElement('checkbox', 'host_notifications', _("Enable/Disable Notifications for a host"));
}
$form->addElement('checkbox', 'host_acknowledgement', _("Acknowledge a host"));
$form->addElement('checkbox', 'host_disacknowledgement', _("Disaknowledge a host"));
$form->addElement('checkbox', 'host_schedule_check', _("Schedule the check for a host"));
$form->addElement('checkbox', 'host_schedule_forced_check', _("Schedule the check for a host (Forced)"));
$form->addElement('checkbox', 'host_schedule_downtime', _("Schedule downtime for a host"));
$form->addElement('checkbox', 'host_comment', _("Add/Delete a comment for a host"));
if ($serverIsMaster) {
    $form->addElement('checkbox', 'host_event_handler', _("Enable/Disable Event Handler for a host"));
    $form->addElement('checkbox', 'host_flap_detection', _("Enable/Disable Flap Detection for a host"));
    $form->addElement('checkbox', 'host_notifications_for_services', _("Enable/Disable Notifications services of a host"));
    $form->addElement('checkbox', 'host_checks_for_services', _("Enable/Disable Checks services of a host"));
}
$form->addElement('checkbox', 'host_submit_result', _("Submit result for a host"));

// Global Nagios External Commands
$form->addElement('checkbox', 'global_shutdown', _("Shutdown Monitoring Engine"));
$form->addElement('checkbox', 'global_restart', _("Restart Monitoring Engine"));
$form->addElement('checkbox', 'global_notifications', _("Enable/Disable notifications"));
$form->addElement('checkbox', 'global_service_checks', _("Enable/Disable service checks"));
$form->addElement('checkbox', 'global_service_passive_checks', _("Enable/Disable passive service checks"));
$form->addElement('checkbox', 'global_host_checks', _("Enable/Disable host checks"));
$form->addElement('checkbox', 'global_host_passive_checks', _("Enable/Disable passive host checks"));
$form->addElement('checkbox', 'global_event_handler', _("Enable/Disable Event Handlers"));
$form->addElement('checkbox', 'global_flap_detection', _("Enable/Disable Flap Detection"));
$form->addElement('checkbox', 'global_service_obsess', _("Enable/Disable Obsessive service checks"));
$form->addElement('checkbox', 'global_host_obsess', _("Enable/Disable Obsessive host checks"));
$form->addElement('checkbox', 'global_perf_data', _("Enable/Disable Performance Data"));

// Global Functionnalities
$form->addElement('checkbox', 'top_counter', _("Display Top Counter"));
$form->addElement('checkbox', 'poller_stats', _("Display Top Counter pollers statistics"));
$form->addElement('checkbox', 'poller_listing', _("Display Poller Listing"));

// Configuration Actions
$form->addElement('checkbox', 'generate_cfg', _("Generate Configuration Files"));
$form->addElement('checkbox', 'generate_trap', _("Generate SNMP Trap configuration"));

$form->addElement('checkbox', 'all_service', "");
$form->addElement('checkbox', 'all_host', "");
$form->addElement('checkbox', 'all_engine', "");


$form->setDefaults(array("hostComment" => 1));

// Contacts Selection
$form->addElement('header', 'notification', _("Relations"));
$form->addElement('header', 'service_actions', _("Services Actions Access"));
$form->addElement('header', 'host_actions', _("Hosts Actions Access"));
$form->addElement('header', 'global_actions', _("Global Monitoring Engine Actions (External Process Commands)"));
$form->addElement('header', 'global_access', _("Global Functionalities Access"));

$ams1 = $form->addElement('advmultiselect', 'acl_groups', _("Linked Groups"), $groups, $attrsAdvSelect, SORT_ASC);
$ams1->setButtonAttributes('add', array('value' => _("Add"), "class" => "btc bt_success"));
$ams1->setButtonAttributes('remove', array('value' => _("Remove"), "class" => "btc bt_danger"));
$ams1->setElementTemplate($eTemplate);
echo $ams1->getElementJs(false);

// Further informations
$form->addElement('header', 'furtherInfos', _("Additional Information"));
$groupActivation[] = $form->createElement('radio', 'acl_action_activate', null, _("Enabled"), '1');
$groupActivation[] = $form->createElement('radio', 'acl_action_activate', null, _("Disabled"), '0');
$form->addGroup($groupActivation, 'acl_action_activate', _("Status"), '&nbsp;');
$form->setDefaults(array('acl_action_activate' => '1'));

$form->addElement('hidden', 'acl_action_id');
$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);

// Form Rules
function myReplace()
{
    global $form;
    $ret = $form->getSubmitValues();
    return (str_replace(" ", "_", $ret["acl_action_name"]));
}

// Controls
$form->applyFilter('__ALL__', 'myTrim');
$form->applyFilter('acl_group_name', 'myReplace');
$form->addRule('acl_action_name', _("Compulsory Name"), 'required');
$form->addRule('acl_action_description', _("Compulsory Alias"), 'required');
$form->addRule('acl_groups', _("Compulsory Groups"), 'required');
$form->registerRule('exist', 'callback', 'testActionExistence');
$form->addRule('acl_action_name', _("Name is already in use"), 'exist');
$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;" . _("Required fields"));

// End of form definition

// Smarty template Init
$tpl = new Smarty();
$tpl = initSmartyTpl(__DIR__, $tpl);

// Modify an Action Group
if ($o == "c" && isset($selected_actions) && isset($action_infos)) {
    $form->setDefaults($selected_actions);
    $form->setDefaults($action_infos);
}
// Add an Action Group
if ($o == "a") {
    $subA = $form->addElement('submit', 'submitA', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
} else {
    $subC = $form->addElement('submit', 'submitC', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
}

$valid = false;
if ($form->validate()) {
    $groupObj = $form->getElement('acl_action_id');
    if ($form->getSubmitValue("submitA")) {
        $groupObj->setValue(insertActionInDB());
    } elseif ($form->getSubmitValue("submitC")) {
        updateActionInDB($groupObj->getValue());
        updateRulesActions($groupObj->getValue());
    }
    $o = null;
    $form->addElement(
        "button",
        "change",
        _("Modify"),
        array("onClick" => "javascript:window.location.href='?p=" . $p . "&o=c&c_id=" . $groupObj->getValue() . "'")
    );
    $form->freeze();
    $valid = true;
}

// prepare help texts
$helptext = "";
include_once("help.php");
foreach ($help as $key => $text) {
    $helptext .= '<span style="display:none" id="help:' . $key . '">' . $text . '</span>' . "\n";
}
$tpl->assign('helptext', $helptext);
$tpl->assign('serverIsMaster', $serverIsMaster);

$action = $form->getSubmitValue("action");
if ($valid) {
    require_once(__DIR__ . "/listsActionsAccess.php");
} else {
    // Apply a template definition
    $renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
    $renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
    $renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
    $form->accept($renderer);
    $tpl->assign('form', $renderer->toArray());
    $tpl->assign('o', $o);
    $tpl->display("formActionsAccess.ihtml");
}
