<?php
/*
 * Copyright 2005-2010 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 * SVN : $URL: http://svn.centreon.com/trunk/centreon/www/include/options/accessLists/actionsACL/formActionsAccess.php $
 * SVN : $Id: formActionsAccess.php 10406 2010-05-01 14:08:12Z jmathis $
 *
 */

 	if (!isset($centreon))
 		exit();

	#
	## Database retrieve information for Modify a present "Action Access"
	#
	if (($o == "c") && $acl_action_id)	{

		# 1. Get "Actions Rule" id selected by user
		$DBRESULT =& $pearDB->query("SELECT * FROM acl_actions WHERE acl_action_id = '".$acl_action_id."' LIMIT 1");
		$action_infos = array();
		$action_infos = array_map("myDecode", $DBRESULT->fetchRow());

		# 2. Get "Groups" id linked with the selected Rule in order to initialize the form
		$DBRESULT =& $pearDB->query("SELECT DISTINCT acl_group_id FROM acl_group_actions_relations WHERE acl_action_id = '".$acl_action_id."'");

		$selected = array();
		for($i = 0; $contacts =& $DBRESULT->fetchRow(); $i++) {
			$selected[] = $contacts["acl_group_id"];
		}
		$action_infos["acl_groups"] = $selected;

		# 3. Range in a table variable, all Groups used in this "Actions Access"
		$DBRESULT =& $pearDB->query("SELECT acl_action_name FROM `acl_actions_rules` WHERE `acl_action_rule_id` = $acl_action_id");

		$selected_actions = array();
		for($i = 0; $act =& $DBRESULT->fetchRow(); $i++) {
			$selected_actions[$act["acl_action_name"]] = 1;
		}

		$DBRESULT->free();
	}

	#
	## Database retrieve information for differents elements list we need on the page
	#
	# Groups list comes from Database and stores in $groups Array
	$groups = array();
	$DBRESULT =& $pearDB->query("SELECT acl_group_id,acl_group_name FROM acl_groups ORDER BY acl_group_name");

	while ($group =& $DBRESULT->fetchRow()) {
		$groups[$group["acl_group_id"]] = $group["acl_group_name"];
	}
	$DBRESULT->free();

	##########################################################
	# Var information to format the element
	#
	$attrsText 		= array("size"=>"30");
	$attrsAdvSelect = array("style" => "width: 250px; height: 100px;");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"60");
	$template 		= "<table style='border:0px;'><tr><td>{unselected}</td><td align='center'>{add}<br /><br /><br />{remove}</td><td>{selected}</td></tr></table>";

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', _("Add an Action"));
	else if ($o == "c")
		$form->addElement('header', 'title', _("Modify an Action"));
	else if ($o == "w")
		$form->addElement('header', 'title', _("View an Action"));

	# Basic information
	$form->addElement('header', 'information', _("General Information"));
	$form->addElement('text', 'acl_action_name', _("Action Name"), $attrsText);
	$form->addElement('text', 'acl_action_description', _("Description"), $attrsText);

	# Services
	$form->addElement('checkbox', 'service_checks', _("Enable/Disable Checks for a service"));
	$form->addElement('checkbox', 'service_notifications', _("Enable/Disable Notifications for a service"));
	$form->addElement('checkbox', 'service_acknowledgement', _("Acknowledge/Disacknowledge a service"));
	$form->addElement('checkbox', 'service_schedule_check', _("Re-schedule the next check for a service"));
	$form->addElement('checkbox', 'service_schedule_forced_check', _("Re-schedule the next check for a service (Forced)"));
	$form->addElement('checkbox', 'service_schedule_downtime', _("Schedule downtime for a service"));
	$form->addElement('checkbox', 'service_comment', _("Add/Delete a comment for a service"));
	$form->addElement('checkbox', 'service_event_handler', _("Enable/Disable Event Handler for a service"));
	$form->addElement('checkbox', 'service_flap_detection', _("Enable/Disable Flap Detection of a service"));
	$form->addElement('checkbox', 'service_passive_checks', _("Enable/Disable passive checks of a service"));
	$form->addElement('checkbox', 'service_submit_result', _("Submit result for a service"));

	# Hosts
	$form->addElement('checkbox', 'host_checks', _("Enable/Disable Checks for a host"));
	$form->addElement('checkbox', 'host_notifications', _("Enable/Disable Notifications for a host"));
	$form->addElement('checkbox', 'host_acknowledgement', _("Acknowledge/Disaknowledge a host"));
	$form->addElement('checkbox', 'host_schedule_check', _("Schedule the check for a host"));
	$form->addElement('checkbox', 'host_schedule_forced_check', _("Schedule the check for a host (Forced)"));
	$form->addElement('checkbox', 'host_schedule_downtime', _("Schedule downtime for a host"));
	$form->addElement('checkbox', 'host_comment', _("Add/Delete a comment for a host"));
	$form->addElement('checkbox', 'host_event_handler', _("Enable/Disable Event Handler for a host"));
	$form->addElement('checkbox', 'host_flap_detection', _("Enable/Disable Flap Detection for a host"));
	$form->addElement('checkbox', 'host_notifications_for_services', _("Enable/Disable Notifications services of a host"));
	$form->addElement('checkbox', 'host_checks_for_services', _("Enable/Disable Checks services of a host"));


	# Global Nagios External Commands
	$form->addElement('checkbox', 'global_shutdown', _("Shutdown Nagios"));
	$form->addElement('checkbox', 'global_restart', _("Restart Nagios"));
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

	$form->setDefaults(array("hostComment" => 1 ));

	# Contacts Selection
	$form->addElement('header', 'notification', _("Relations"));
	$form->addElement('header', 'service_actions', _("Services Actions Access"));
	$form->addElement('header', 'host_actions', _("Hosts Actions Access"));
	$form->addElement('header', 'global_actions', _("Global Nagios Actions (External Process Commands)"));

    $ams1 =& $form->addElement('advmultiselect', 'acl_groups', _("Linked Groups"), $groups, $attrsAdvSelect, SORT_ASC);
	$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams1->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);

	# Further informations
	$form->addElement('header', 'furtherInfos', _("Additional Information"));
	$groupActivation[] = &HTML_QuickForm::createElement('radio', 'acl_action_activate', null, _("Enabled"), '1');
	$groupActivation[] = &HTML_QuickForm::createElement('radio', 'acl_action_activate', null, _("Disabled"), '0');
	$form->addGroup($groupActivation, 'acl_action_activate', _("Status"), '&nbsp;');
	$form->setDefaults(array('acl_action_activate' => '1'));

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');
	$form->setDefaults(array('action' => '1'));

	$form->addElement('hidden', 'acl_action_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	# Form Rules
	function myReplace()	{
		global $form;
		$ret = $form->getSubmitValues();
		return (str_replace(" ", "_", $ret["acl_action_name"]));
	}

	# Controls
	$form->applyFilter('__ALL__', 'myTrim');
	$form->applyFilter('acl_group_name', 'myReplace');
	$form->addRule('acl_action_name', _("Compulsory Name"), 'required');
	$form->addRule('acl_action_description', _("Compulsory Alias"), 'required');
	$form->addRule('acl_groups', _("Compulsory Groups"), 'required');
	$form->registerRule('exist', 'callback', 'testActionExistence');
	$form->addRule('acl_action_name', _("Name is already in use"), 'exist');
	$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;". _("Required fields"));

	# End of form definition

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	# Modify an Action Group
	if ($o == "c" && isset($selected_actions) && isset($action_infos))	{
		$form->setDefaults($selected_actions);
		$subC =& $form->addElement('submit', 'submitC', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	    $form->setDefaults($action_infos);
	}
	# Add an Action Group
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	}

	$valid = false;
	if ($form->validate())	{
		$groupObj =& $form->getElement('acl_action_id');
		if ($form->getSubmitValue("submitA"))
			$groupObj->setValue(insertActionInDB());
		else if ($form->getSubmitValue("submitC")){
			updateActionInDB($groupObj->getValue());
			updateRulesActions($groupObj->getValue());
		}
		$o = NULL;
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&c_id=".$groupObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once($path."listsActionsAccess.php");
	else	{
		#Apply a template definition
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		$tpl->display("formActionsAccess.ihtml");
	}
?>