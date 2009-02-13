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
	 * Form Rules
	 */
	function myReplace()	{
		global $form;
		$ret = $form->getSubmitValues();
		return (str_replace(" ", "_", $ret["cg_name"]));
	}	

	/*
	 * Database retrieve information for Contact
	 */
	$cg = array();
	if (($o == "c" || $o == "w") && $cg_id)	{
		/*
		 * Get host Group information
		 */
		$DBRESULT =& $pearDB->query("SELECT * FROM `contactgroup` WHERE `cg_id` = '".$cg_id."' LIMIT 1");
		
		/*
		 * Set base value
		 */	
		$cg = array_map("myDecode", $DBRESULT->fetchRow());
		
		/*
		 * Set Contact Childs
		 */
		$DBRESULT =& $pearDB->query("SELECT DISTINCT `contact_contact_id` FROM `contactgroup_contact_relation` WHERE `contactgroup_cg_id` = '".$cg_id."'");
		for ($i = 0; $contacts =& $DBRESULT->fetchRow(); $i++)
			$cg["cg_contacts"][$i] = $contacts["contact_contact_id"];
		$DBRESULT->free();
	}

	/*
	 * Contacts comes from DB -> Store in $contacts Array
	 */
	$contacts = array();
	$DBRESULT =& $pearDB->query("SELECT `contact_id`, `contact_name` FROM `contact` ORDER BY `contact_name`");
	while ($contact =& $DBRESULT->fetchRow())
		$contacts[$contact["contact_id"]] = $contact["contact_name"];
	unset($contact);
	$DBRESULT->free();
	
	$attrsText 		= array("size"=>"30");
	$attrsAdvSelect = array("style" => "width: 250px; height: 100px;");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"60");
	$template 		= "<table style='border:0px;'><tr><td>{unselected}</td><td align='center'>{add}<br /><br /><br />{remove}</td><td>{selected}</td></tr></table>";

	/*
	 * form begin
	 */
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', _("Add a Contact Group"));
	else if ($o == "c")
		$form->addElement('header', 'title', _("Modify a Contact Group"));
	else if ($o == "w")
		$form->addElement('header', 'title', _("View a Contact Group"));

	/*
	 * Contact basic information
	 */
	$form->addElement('header', 'information', _("General Information"));
	$form->addElement('text', 'cg_name', _("Contact Group Name"), $attrsText);
	$form->addElement('text', 'cg_alias', _("Alias"), $attrsText);
	
	/*
	 * Contacts Selection
	 */
	$form->addElement('header', 'notification', _("Relations"));
	
    $ams1 =& $form->addElement('advmultiselect', 'cg_contacts', _("Linked Contacts"), $contacts, $attrsAdvSelect);
	$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams1->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);
	
	/*
	 * Further informations
	 */
	$form->addElement('header', 'furtherInfos', _("Additional Information"));
	$cgActivation[] = &HTML_QuickForm::createElement('radio', 'cg_activate', null, _("Enabled"), '1');
	$cgActivation[] = &HTML_QuickForm::createElement('radio', 'cg_activate', null, _("Disabled"), '0');
	$form->addGroup($cgActivation, 'cg_activate', _("Status"), '&nbsp;');
	$form->setDefaults(array('cg_activate' => '1'));
	$form->addElement('textarea', 'cg_comment', _("Comments"), $attrsTextarea);
	
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');	
	$form->setDefaults(array('action' => '1'));
	
	$form->addElement('hidden', 'cg_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	
	/*
	 * Set rules
	 */
	$form->applyFilter('__ALL__', 'myTrim');
	$form->applyFilter('cg_name', 'myReplace');
	$form->addRule('cg_name', _("Compulsory Name"), 'required');
	$form->addRule('cg_alias', _("Compulsory Alias"), 'required');
	$form->registerRule('exist', 'callback', 'testContactGroupExistence');
	$form->addRule('cg_name', _("Name is already in use"), 'exist');
	$form->setRequiredNote("<font style='color: red;'>*</font>". _(" Required fields"));

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	if ($o == "w")	{
		/*
		 * Just watch a Contact Group information
		 */
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&cg_id=".$cg_id."'"));
	    $form->setDefaults($cg);
		$form->freeze();
	} else if ($o == "c")	{
		/*
		 * Modify a Contact Group information
		 */
		$subC =& $form->addElement('submit', 'submitC', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	    $form->setDefaults($cg);
	} else if ($o == "a")	{
		/*
		 * Add a Contact Group information
		 */
		$subA =& $form->addElement('submit', 'submitA', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	}
	
	$valid = false;
	if ($form->validate())	{
		
		$cgObj =& $form->getElement('cg_id');
		
		if ($form->getSubmitValue("submitA"))
			$cgObj->setValue(insertContactGroupInDB());
		else if ($form->getSubmitValue("submitC"))
			updateContactGroupInDB($cgObj->getValue());
		
		$o = NULL;
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&cg_id=".$cgObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"]) { 
		require_once($path."listContactGroup.php");
	} else {
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);	
		$tpl->assign('form', $renderer->toArray());	
		$tpl->assign('o', $o);		
		
		$tpl->display("formContactGroup.ihtml");
	}
?>