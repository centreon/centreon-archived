<?php
/**
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/

	#
	## Database retrieve information for Contact
	#
	$group = array();
	if (($o == "c" || $o == "w") && $acl_group_id)	{	
		$DBRESULT =& $pearDB->query("SELECT * FROM acl_groups WHERE acl_group_id = '".$acl_group_id."' LIMIT 1");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		# Set base value
		$group = array_map("myDecode", $DBRESULT->fetchRow());
		# Set Contact Childs
		$DBRESULT =& $pearDB->query("SELECT DISTINCT contact_contact_id FROM acl_group_contacts_relations WHERE acl_group_id = '".$acl_group_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		for($i = 0; $contacts = $DBRESULT->fetchRow(); $i++)
			$group["cg_contacts"][$i] = $contacts["contact_contact_id"];
		$DBRESULT->free();
	}
	#
	## Database retrieve information for differents elements list we need on the page
	#
	# Contacts comes from DB -> Store in $contacts Array
	$contacts = array();
	$DBRESULT =& $pearDB->query("SELECT contact_id, contact_name FROM contact WHERE contact_admin = '0' ORDER BY contact_name");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	while($DBRESULT->fetchInto($contact))
		$contacts[$contact["contact_id"]] = $contact["contact_name"];
	unset($contact);
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
		$form->addElement('header', 'title', _("Add a Group"));
	else if ($o == "c")
		$form->addElement('header', 'title', _("Modify a Group"));
	else if ($o == "w")
		$form->addElement('header', 'title', _("View a Group"));

	# Contact basic information
	$form->addElement('header', 'information', _("General Information"));
	$form->addElement('text', 'acl_group_name', _("Group Name"), $attrsText);
	$form->addElement('text', 'acl_group_alias', _("Alias"), $attrsText);
	
	# Contacts Selection
	$form->addElement('header', 'notification', _("Relations"));
	
    $ams1 =& $form->addElement('advmultiselect', 'cg_contacts', _("Linked Contacts"), $contacts, $attrsAdvSelect);
	$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams1->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);
	
	# Further informations
	$form->addElement('header', 'furtherInfos', _("Additional Information"));
	$groupActivation[] = &HTML_QuickForm::createElement('radio', 'acl_group_activate', null, _("Enabled"), '1');
	$groupActivation[] = &HTML_QuickForm::createElement('radio', 'acl_group_activate', null, _("Disabled"), '0');
	$form->addGroup($groupActivation, 'acl_group_activate', _("Status"), '&nbsp;');
	$form->setDefaults(array('acl_group_activate' => '1'));

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');	
	$form->setDefaults(array('action' => '1'));
	
	$form->addElement('hidden', 'acl_group_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	
	# Form Rules
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
	$form->setRequiredNote("<font style='color: red;'>*</font>". _(" Required fields"));

	# End of form definition

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	# Just watch a Contact Group information
	if ($o == "w")	{
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&cg_id=".$group_id."'"));
	    $form->setDefaults($group);
		$form->freeze();
	}
	# Modify a Contact Group information
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	    $form->setDefaults($group);
	}
	# Add a Contact Group information
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	}
	
	$valid = false;
	if ($form->validate())	{
		$groupObj =& $form->getElement('acl_group_id');
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
	else	{
		#Apply a template definition
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);	
		$tpl->assign('form', $renderer->toArray());	
		$tpl->assign('o', $o);		
		$tpl->display("formGroupConfig.ihtml");
	}
?>