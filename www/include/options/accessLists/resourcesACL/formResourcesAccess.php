<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
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
	if (PEAR::isError($DBRESULT)) 
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
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
	$attrsAdvSelect = array("style" => "width: 200px; height: 100px;");
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
	$form->addElement('header', 'services', _("Services Categories Shared"));
	$form->addElement('text',	'acl_res_name', _("ACL Definition"), $attrsText);
	$form->addElement('text', 	'acl_res_alias', _("Alias"), $attrsText);

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'acl_res_activate', null, _("Enabled"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'acl_res_activate', null, _("Disabled"), '0');
	$form->addGroup($tab, 'acl_res_activate', _("Status"), '&nbsp;');
	$form->setDefaults(array('acl_res_activate' => '1'));


    $ams1 =& $form->addElement('advmultiselect', 'acl_groups', _("Linked Groups"), $groups, $attrsAdvSelect);
	$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams1->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);

	$form->addElement('header', 'Host_infos', _("Shared Hosts Informations"));
	
	$ams2 =& $form->addElement('advmultiselect', 'acl_hosts', _("Hosts Access"), $hosts, $attrsAdvSelect);
	$ams2->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams2->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams2->setElementTemplate($template);
	echo $ams2->getElementJs(false);
	
	$ams2 =& $form->addElement('advmultiselect', 'acl_hostexclude', _("Exclude hosts on hosts groups"), $hosttoexcludes, $attrsAdvSelect);
	$ams2->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams2->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams2->setElementTemplate($template);
	echo $ams2->getElementJs(false);
	
	$ams2 =& $form->addElement('advmultiselect', 'acl_hostgroup', _("Hosts Groups Access"), $hostgroups, $attrsAdvSelect);
	$ams2->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams2->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams2->setElementTemplate($template);
	echo $ams2->getElementJs(false);
	
	$ams2 =& $form->addElement('advmultiselect', 'acl_sc', _("Services Categories Access"), $service_categories, $attrsAdvSelect);
	$ams2->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams2->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams2->setElementTemplate($template);
	echo $ams2->getElementJs(false);

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

	/*
	 * Just watch a LCA information
	 */
	if ($o == "w")	{
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&acl_id=".$acl_id."'"));
	    $form->setDefaults($acl);
		$form->freeze();
	} else if ($o == "c"){ # Modify a LCA information
		$subC =& $form->addElement('submit', 'submitC', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Delete"));
	    $form->setDefaults($acl);
	} else if ($o == "a"){	# Add a LCA information
		$subA =& $form->addElement('submit', 'submitA', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Delete"));
	}
	$tpl->assign('msg', array ("changeL"=>"?p=".$p."&o=c&lca_id=".$acl_id, "changeT"=>_("Modify")));

	$tpl->assign("sort1", _("General Information"));
	$tpl->assign("sort2", _("Resources"));
	$tpl->assign("sort3", _("Topology"));

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
			#Apply a template definition
			$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
			$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
			$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
			$form->accept($renderer);
			$tpl->assign('form', $renderer->toArray());
			$tpl->assign('o', $o);
			$tpl->display("formResourcesAccess.ihtml");
		}
	}
?>