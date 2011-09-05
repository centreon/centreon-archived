<?php
/*
 * Copyright 2005-2011 MERETHIS
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
 * SVN : $URL$
 * SVN : $Id$
 *
 */

 	if (!isset($centreon))
 		exit();

 	require_once $centreon_path . 'www/class/centreonLDAP.class.php';
 	require_once $centreon_path . 'www/class/centreonContactgroup.class.php';
 		
	/*
	 * Retreive information
	 */
	$group = array();
	if (($o == "c" || $o == "w") && $acl_group_id) {
		$DBRESULT = $pearDB->query("SELECT * FROM acl_groups WHERE acl_group_id = '".$acl_group_id."' LIMIT 1");
		/*
		 * Set base value
		 */
		$group = array_map("myDecode", $DBRESULT->fetchRow());

		/*
		 * Set Contact Childs
		 */
		$DBRESULT = $pearDB->query("SELECT DISTINCT contact_contact_id FROM acl_group_contacts_relations WHERE acl_group_id = '".$acl_group_id."'");
		for ($i = 0; $contacts = $DBRESULT->fetchRow(); $i++)
			$group["cg_contacts"][$i] = $contacts["contact_contact_id"];
		$DBRESULT->free();

		/*
		 * Set ContactGroup Childs
		 */
		$DBRESULT = $pearDB->query("SELECT DISTINCT cg_cg_id FROM acl_group_contactgroups_relations WHERE acl_group_id = '".$acl_group_id."'");
		for ($i = 0; $contactgroups = $DBRESULT->fetchRow(); $i++)
			$group["cg_contactGroups"][$i] = $contactgroups["cg_cg_id"];
		$DBRESULT->free();

		/*
		 * Set Menu link List
		 */
		$DBRESULT = $pearDB->query("SELECT DISTINCT acl_topology_id FROM acl_group_topology_relations WHERE acl_group_id = '".$acl_group_id."'");
		for ($i = 0; $data = $DBRESULT->fetchRow(); $i++)
			$group["menuAccess"][$i] = $data["acl_topology_id"];
		$DBRESULT->free();

		/*
		 * Set resources List
		 */
		$DBRESULT = $pearDB->query("SELECT DISTINCT acl_res_id FROM acl_res_group_relations WHERE acl_group_id = '".$acl_group_id."'");
		for ($i = 0; $data = $DBRESULT->fetchRow(); $i++)
			$group["resourceAccess"][$i] = $data["acl_res_id"];
		$DBRESULT->free();

		/*
		 * Set Action List
		 */
		$DBRESULT = $pearDB->query("SELECT DISTINCT acl_action_id FROM acl_group_actions_relations WHERE acl_group_id = '".$acl_group_id."'");
		for ($i = 0; $data = $DBRESULT->fetchRow(); $i++)
			$group["actionAccess"][$i] = $data["acl_action_id"];
		$DBRESULT->free();

	}

	/*
	 * Database retrieve information for differents elements list we need on the page
	 */
	# Contacts comes from DB -> Store in $contacts Array
	$contacts = array();
	$DBRESULT = $pearDB->query("SELECT contact_id, contact_name FROM contact WHERE contact_admin = '0' AND contact_register = 1 ORDER BY contact_name");
	while ($contact = $DBRESULT->fetchRow())
		$contacts[$contact["contact_id"]] = $contact["contact_name"];
	unset($contact);
	$DBRESULT->free();
	
	$cg = new CentreonContactgroup($pearDB);
	$contactGroups = $cg->getListContactgroup(true);
	
	# topology comes from DB -> Store in $contacts Array
	$menus = array();
	$DBRESULT = $pearDB->query("SELECT acl_topo_id, acl_topo_name FROM acl_topology ORDER BY acl_topo_name");
	while ($topo = $DBRESULT->fetchRow())
		$menus[$topo["acl_topo_id"]] = $topo["acl_topo_name"];
	unset($topo);
	$DBRESULT->free();

	# Action comes from DB -> Store in $contacts Array
	$action = array();
	$DBRESULT = $pearDB->query("SELECT acl_action_id, acl_action_name FROM acl_actions ORDER BY acl_action_name");
	while ($data = $DBRESULT->fetchRow())
		$action[$data["acl_action_id"]] = $data["acl_action_name"];
	unset($data);
	$DBRESULT->free();

	# Resources comes from DB -> Store in $contacts Array
	$resources = array();
	$DBRESULT = $pearDB->query("SELECT acl_res_id, acl_res_name FROM acl_resources ORDER BY acl_res_name");
	while ($res = $DBRESULT->fetchRow())
		$resources[$res["acl_res_id"]] = $res["acl_res_name"];
	unset($res);
	$DBRESULT->free();

	##########################################################
	# Var information to format the element
	#
	$attrsText 		= array("size"=>"30");
	$attrsAdvSelect = array("style" => "width: 300px; height: 130px;");
	$attrsTextarea 	= array("rows"=>"6", "cols"=>"150");
	$eTemplate	= '<table><tr><td><div class="ams">{label_2}</div>{unselected}</td><td align="center">{add}<br /><br /><br />{remove}</td><td><div class="ams">{label_3}</div>{selected}</td></tr></table>';

	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', _("Add a Group"));
	else if ($o == "c")
		$form->addElement('header', 'title', _("Modify a Group"));
	else if ($o == "w")
		$form->addElement('header', 'title', _("View a Group"));

	/*
	 * Contact basic information
	 */
	$form->addElement('header', 'information', _("General Information"));
	$form->addElement('text', 'acl_group_name', _("Group Name"), $attrsText);
	$form->addElement('text', 'acl_group_alias', _("Alias"), $attrsText);

	/*
	 * Contacts Selection
	 */
	$form->addElement('header', 'notification', _("Relations"));
	$form->addElement('header', 'menu', _("Menu access list link"));
	$form->addElement('header', 'resource', _("Resources access list link"));
	$form->addElement('header', 'actions', _("Action access list link"));

	$ams1 = $form->addElement('advmultiselect', 'cg_contacts', array(_("Linked Contacts"), _("Available"), _("Selected")), $contacts, $attrsAdvSelect, SORT_ASC);
	$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams1->setButtonAttributes('remove', array('value' => _("Remove")));
	$ams1->setElementTemplate($eTemplate);
	echo $ams1->getElementJs(false);

	$ams1 = $form->addElement('advmultiselect', 'cg_contactGroups', array(_("Linked Contact Groups"), _("Available"), _("Selected")), $contactGroups, $attrsAdvSelect, SORT_ASC);
	$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams1->setButtonAttributes('remove', array('value' => _("Remove")));
	$ams1->setElementTemplate($eTemplate);
	echo $ams1->getElementJs(false);

	$ams1 = $form->addElement('advmultiselect', 'menuAccess', array(_("Menu access"), _("Available"), _("Selected")), $menus, $attrsAdvSelect, SORT_ASC);
	$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams1->setButtonAttributes('remove', array('value' => _("Remove")));
	$ams1->setElementTemplate($eTemplate);
	echo $ams1->getElementJs(false);

	$ams1 = $form->addElement('advmultiselect', 'actionAccess', array(_("Actions access"), _("Available"), _("Selected")), $action, $attrsAdvSelect, SORT_ASC);
	$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams1->setButtonAttributes('remove', array('value' => _("Remove")));
	$ams1->setElementTemplate($eTemplate);
	echo $ams1->getElementJs(false);

	$ams1 = $form->addElement('advmultiselect', 'resourceAccess', array(_("Resources access"), _("Available"), _("Selected")), $resources, $attrsAdvSelect, SORT_ASC);
	$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams1->setButtonAttributes('remove', array('value' => _("Remove")));
	$ams1->setElementTemplate($eTemplate);
	echo $ams1->getElementJs(false);

	/*
	 * Further informations
	 */
	$form->addElement('header', 'furtherInfos', _("Additional Information"));
	$groupActivation[] = HTML_QuickForm::createElement('radio', 'acl_group_activate', null, _("Enabled"), '1');
	$groupActivation[] = HTML_QuickForm::createElement('radio', 'acl_group_activate', null, _("Disabled"), '0');
	$form->addGroup($groupActivation, 'acl_group_activate', _("Status"), '&nbsp;');
	$form->setDefaults(array('acl_group_activate' => '1'));

	$tab = array();
	$tab[] = HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');
	$form->setDefaults(array('action' => '1'));

	$form->addElement('hidden', 'acl_group_id');
	$redirect = $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	/*
	 * Form Rules
	 */
	function myReplace()	{
		global $form;
		$ret = $form->getSubmitValues();
		return (str_replace(" ", "_", $ret["acl_group_name"]));
	}

	$form->applyFilter('__ALL__', 'myTrim');
	$form->applyFilter('acl_group_name', 'myReplace');
	$form->addRule('acl_group_name', _("Compulsory Name"), 'required');
	$form->addRule('acl_group_alias', _("Compulsory Alias"), 'required');
	$form->registerRule('exist', 'callback', 'testGroupExistence');
	$form->addRule('acl_group_name', _("Name is already in use"), 'exist');
	$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;". _("Required fields"));

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	/*
	 * Define tab title
	 */
	$tpl->assign("sort1", "Group Information");
	$tpl->assign("sort2", "Authorizations information");

	/*
	 * Just watch a Contact Group information
	 */
	if ($o == "w") {
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&cg_id=".$group_id."'"));
	    $form->setDefaults($group);
		$form->freeze();
	} else if ($o == "c") {
		/*
		 * Modify a Contact Group information
		 */
		$subC = $form->addElement('submit', 'submitC', _("Save"));
		$res = $form->addElement('reset', 'reset', _("Reset"));
	    $form->setDefaults($group);
	} else if ($o == "a") {
		/*
		 * Add a Contact Group information
		 */
		$subA = $form->addElement('submit', 'submitA', _("Save"));
		$res = $form->addElement('reset', 'reset', _("Reset"));
	}

	$valid = false;
	if ($form->validate())	{
		$groupObj = $form->getElement('acl_group_id');
		if ($form->getSubmitValue("submitA"))
			$groupObj->setValue(insertGroupInDB());
		else if ($form->getSubmitValue("submitC"))
			updateGroupInDB($groupObj->getValue());
		$o = NULL;
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&cg_id=".$groupObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once($path."listGroupConfig.php");
	else {
		/*
		 * Apply a template definition
		 */
		$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl, true);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		$tpl->display("formGroupConfig.ihtml");
	}
?>
