<?php
/*
 * Copyright 2005-2009 MERETHIS
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
	
	if (!isset($oreon))
		exit();

	/*
	 * Database retrieve information for LCA
	 */
	 
	if ($o == "c" || $o == "w")	{
		$DBRESULT =& $pearDB->query("SELECT * FROM acl_resources WHERE acl_res_id = '".$acl_id."' LIMIT 1");
		
		# Set base value
		$acl = array_map("myDecode", $DBRESULT->fetchRow());
		
		# Set Hosts relations
		$hostnotexludes = array();
		$DBRESULT =& $pearDB->query("SELECT host_host_id FROM acl_resources_host_relations WHERE acl_res_id = '".$acl_id."'");
		for ($i = 0; $hosts_list =& $DBRESULT->fetchRow(); $i++) {
			$acl["acl_hosts"][$i] = $hosts_list["host_host_id"];
			$hostnotexludes[$hosts_list["host_host_id"]] = 1;
		}
		$DBRESULT->free();
		
		# Set Hosts exludes relations
		$DBRESULT =& $pearDB->query("SELECT host_host_id FROM acl_resources_hostex_relations WHERE acl_res_id = '".$acl_id."'");
		for ($i = 0; $hosts_list =& $DBRESULT->fetchRow(); $i++)
			$acl["acl_hostexclude"][$i] = $hosts_list["host_host_id"];		
		$DBRESULT->free();
		
		# Set Hosts Groups relations
		$DBRESULT =& $pearDB->query("SELECT hg_hg_id FROM acl_resources_hg_relations WHERE acl_res_id = '".$acl_id."'");
		for ($i = 0; $hg_list =& $DBRESULT->fetchRow(); $i++)
			$acl["acl_hostgroup"][$i] = $hg_list["hg_hg_id"];
		$DBRESULT->free();

		# Set Groups relations
		$DBRESULT =& $pearDB->query("SELECT DISTINCT acl_group_id FROM acl_res_group_relations WHERE acl_res_id = '".$acl_id."'");
		for ($i = 0; $groups =& $DBRESULT->fetchRow(); $i++)
			$acl["acl_groups"][$i] = $groups["acl_group_id"];
		$DBRESULT->free();
		
		# Set Service Categories relations
		$DBRESULT =& $pearDB->query("SELECT DISTINCT sc_id FROM acl_resources_sc_relations WHERE acl_res_id = '".$acl_id."'");
		if ($DBRESULT->numRows())
			for ($i = 0; $sc =& $DBRESULT->fetchRow(); $i++)
				$acl["acl_sc"][$i] = $sc["sc_id"];
		$DBRESULT->free();

		# Set Service Groups relations
		$DBRESULT =& $pearDB->query("SELECT DISTINCT sg_id FROM acl_resources_sg_relations WHERE acl_res_id = '".$acl_id."'");
		if ($DBRESULT->numRows())
			for ($i = 0; $sg =& $DBRESULT->fetchRow(); $i++)
				$acl["acl_sg"][$i] = $sg["sg_id"];
		$DBRESULT->free();

	}

	$groups = array();
	$DBRESULT =& $pearDB->query("SELECT acl_group_id, acl_group_name FROM acl_groups ORDER BY acl_group_name");
	while ($group =& $DBRESULT->fetchRow())
		$groups[$group["acl_group_id"]] = $group["acl_group_name"];
	$DBRESULT->free();
	
	$hosts = array();
	$DBRESULT =& $pearDB->query("SELECT host_id, host_name FROM host WHERE host_register = '1' ORDER BY host_name");
	while ($host =& $DBRESULT->fetchRow())
		$hosts[$host["host_id"]] = $host["host_name"];
	$DBRESULT->free();
	
	$hosttoexcludes = array();
	$DBRESULT =& $pearDB->query("SELECT host_id, host_name FROM host WHERE host_register = '1' ORDER BY host_name");
	while ($host =& $DBRESULT->fetchRow())
		$hosttoexcludes[$host["host_id"]] = $host["host_name"];
	$DBRESULT->free();
	
	$hostgroups = array();
	$DBRESULT =& $pearDB->query("SELECT hg_id, hg_name FROM hostgroup ORDER BY hg_name");
	while ($hg =& $DBRESULT->fetchRow())
		$hostgroups[$hg["hg_id"]] = $hg["hg_name"];
	$DBRESULT->free();
	
	$service_categories = array();
	$DBRESULT =& $pearDB->query("SELECT sc_id, sc_name FROM service_categories ORDER BY sc_name");
	while ($sc =& $DBRESULT->fetchRow())
		$service_categories[$sc["sc_id"]] = $sc["sc_name"];
	$DBRESULT->free();
	
	$service_groups = array();
	$DBRESULT =& $pearDB->query("SELECT sg_id, sg_name FROM servicegroup ORDER BY sg_name");
	while ($sg =& $DBRESULT->fetchRow())
		$service_groups[$sg["sg_id"]] = $sg["sg_name"];
	$DBRESULT->free();
	
	
	/*
	 * Var information to format the element
	 */
	
	$attrsText 		= array("size"=>"30");
	$attrsText2 	= array("size"=>"60");
	$attrsAdvSelect = array("style" => "width: 200px; height: 200px;");
	$attrsTextarea 	= array("rows"=>"3", "cols"=>"80");
	$template 		= "<table><tr><td>{unselected}</td><td align='center'>{add}<br /><br /><br />{remove}</td><td>{selected}</td></tr></table>";

	/*
	 * Form begin
	 */
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', _("Add an ACL"));
	else if ($o == "c")
		$form->addElement('header', 'title', _("Modify an ACL"));
	else if ($o == "w")
		$form->addElement('header', 'title', _("View an ACL"));

	/*
	 * LCA basic information
	 */
	$form->addElement('header', 'information', _("General Information"));
	$form->addElement('header', 'hostgroups', _("Hosts Groups Shared"));
	$form->addElement('header', 'services', _("Services Filters"));
	$form->addElement('text',	'acl_res_name', _("ACL Definition"), $attrsText);
	$form->addElement('text', 	'acl_res_alias', _("Alias"), $attrsText2);

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'acl_res_activate', null, _("Enabled"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'acl_res_activate', null, _("Disabled"), '0');
	$form->addGroup($tab, 'acl_res_activate', _("Status"), '&nbsp;');
	$form->setDefaults(array('acl_res_activate' => '1'));

	/*
	 * Contact implied
	 */
	$form->addElement('header', 'contacts_infos', _("People linked to this Access list"));
	
    $ams1 =& $form->addElement('advmultiselect', 'acl_groups', _("Linked Groups"), $groups, $attrsAdvSelect);
	$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams1->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);

	$form->addElement('header', 'Host_infos', _("Shared Resouces"));
	$form->addElement('header', 'help', _("Help"));
	$form->addElement('header', 'HSharedExplain', _("<b><i>Help :</i></b> In this tab, you will be able to select hosts and hostgroups that you want to shared to people present in group selected on the previous tab. You have also the possibilty to exclude host on selected hostgroup. You can also do filters on selected hosts services. If you select a service category, user will only see only services of the selected categories."));
	/*
	 * Hosts
	 */
	$ams2 =& $form->addElement('advmultiselect', 'acl_hosts', _("Hosts available"), $hosts, $attrsAdvSelect);
	$ams2->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams2->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams2->setElementTemplate($template);
	echo $ams2->getElementJs(false);
	
	/*
	 * Host Groups
	 */
	$ams2 =& $form->addElement('advmultiselect', 'acl_hostgroup', _("Hosts groups available"), $hostgroups, $attrsAdvSelect);
	$ams2->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams2->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams2->setElementTemplate($template);
	echo $ams2->getElementJs(false);
	
	$ams2 =& $form->addElement('advmultiselect', 'acl_hostexclude', _("Exclude hosts on selected hosts groups"), $hosttoexcludes, $attrsAdvSelect);
	$ams2->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams2->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams2->setElementTemplate($template);
	echo $ams2->getElementJs(false);
	
	/*
	 * Host Filters
	 */
	$ams2 =& $form->addElement('advmultiselect', 'acl_sc', _("Services Categories Access"), $service_categories, $attrsAdvSelect);
	$ams2->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams2->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams2->setElementTemplate($template);
	echo $ams2->getElementJs(false);

	/*
	 * Service Groups Add
	 */
	$form->addElement('header', 'SSharedExplain', "");
	
	$ams2 =& $form->addElement('advmultiselect', 'acl_sg', _("Services Groups"), $service_groups, $attrsAdvSelect);
	$ams2->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams2->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams2->setElementTemplate($template);
	echo $ams2->getElementJs(false);
		
	/*
	 * Further informations
	 */
	$form->addElement('header', 'furtherInfos', _("Additional Information"));
	$form->addElement('textarea', 'lca_comment', _("Comments"), $attrsTextarea);
	
	
	$form->addElement('hidden', 'acl_res_id');
	
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	/*
	 * Form Rules
	 */
	$form->applyFilter('__ALL__', 'myTrim');
	$form->addRule('lca_name', _("Required"), 'required');
	$form->registerRule('exist', 'callback', 'testExistence');
	$form->addRule('lca_name', _("Already exists"), 'exist');
	$form->setRequiredNote(_("Required field"));

	/*
	 * Smarty template Init
	 */ 
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	if ($o == "w") {
		/*
		 * Just watch a LCA information
		 */
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&acl_id=".$acl_id."'"));
	    $form->setDefaults($acl);
		$form->freeze();
	} else if ($o == "c"){ 
		/*
		 * Modify a LCA information
		 */
		$subC =& $form->addElement('submit', 'submitC', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Delete"));
	    $form->setDefaults($acl);
	} else if ($o == "a") {
		/*
		 *  Add a LCA information
		 */
		$subA =& $form->addElement('submit', 'submitA', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Delete"));
	}
	$tpl->assign('msg', array ("changeL"=>"?p=".$p."&o=c&lca_id=".$acl_id, "changeT"=>_("Modify")));

	$valid = false;
	if ($form->validate())	{
		$aclObj =& $form->getElement('acl_res_id');
		if ($form->getSubmitValue("submitA"))
			$aclObj->setValue(insertLCAInDB());
		else if ($form->getSubmitValue("submitC"))
			updateLCAInDB($aclObj->getValue());
		require_once("listsResourcesAccess.php");
	} else {
		$action = $form->getSubmitValue("action");
		if ($valid && $action["action"]["action"])
			require_once("listsResourcesAccess.php");
		else	{
			/*
			 * Apply a template definition
			 */
			$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
			$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
			$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
			$form->accept($renderer);
			$tpl->assign('form', $renderer->toArray());
			$tpl->assign('o', $o);
			$tpl->assign("sort1", _("General Information"));
			$tpl->assign("sort2", _("Hosts Resources"));
			$tpl->assign("sort3", _("Services Resources"));
			$tpl->display("formResourcesAccess.ihtml");
		}
	}
?>