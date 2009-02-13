<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus - Damien Duponchelle 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 */
 
 	if (!isset($oreon))
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
	$form->addElement('checkbox', 'service_notifications', _("Enable/Disabled Notifications for a service"));
	$form->addElement('checkbox', 'service_acknowledgement', _("Acknowledge/Disaknowledge a service"));
	$form->addElement('checkbox', 'service_schedule_check', _("Re-schedule the next check for a service"));
	$form->addElement('checkbox', 'service_schedule_downtime', _("Schedule downtime for a service"));
	$form->addElement('checkbox', 'service_comment', _("Add/Delete a comment for a service"));
	$form->addElement('checkbox', 'service_event_handler', _("Enable/Disable Event Handler for a service"));
	$form->addElement('checkbox', 'service_flap_detection', _("Enable/Disable Flap Detection for a service"));
	$form->addElement('checkbox', 'service_passive_checks', _("Enable/Disabled Accepting passive checks for a service"));
	$form->addElement('checkbox', 'service_submit_result', _("Submit result for a service"));	
	
	# Hosts
	$form->addElement('checkbox', 'host_checks', _("Enable/Disable Checks for a host"));
	$form->addElement('checkbox', 'host_notifications', _("Enable/Disabled Notifications for a host"));
	$form->addElement('checkbox', 'host_acknowledgement', _("Acknowledge/Disaknowledge a host"));
	$form->addElement('checkbox', 'host_schedule_check', _("Schedule the check for a host"));
	$form->addElement('checkbox', 'host_schedule_downtime', _("Schedule downtime for a host"));
	$form->addElement('checkbox', 'host_comment', _("Add/Delete a comment for a host"));
	$form->addElement('checkbox', 'host_event_handler', _("Enable/Disable Event Handler for a host"));
	$form->addElement('checkbox', 'host_flap_detection', _("Enable/Disable Flap Detection for a host"));
	$form->addElement('checkbox', 'host_notifications_for_services', _("Enable/Disabled Notifications for services for a host"));
	$form->addElement('checkbox', 'host_checks_for_services', _("Enable/Disabled Checks for services for a host"));
	
	$form->setDefaults(array("hostComment" => 1 ));
		
	# Contacts Selection
	$form->addElement('header', 'notification', _("Relations"));
	$form->addElement('header', 'service_actions', _("Services Actions Access"));
	$form->addElement('header', 'host_actions', _("Hosts Actions Access"));
		
    $ams1 =& $form->addElement('advmultiselect', 'acl_groups', _("Linked Groups"), $groups, $attrsAdvSelect);
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
	$form->setRequiredNote("<font style='color: red;'>*</font>". _(" Required fields"));

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