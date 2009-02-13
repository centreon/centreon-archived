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

	#
	## Database retrieve information for Resources CFG
	#
	if (($o == "c" || $o == "w") && $resource_id)	{	
		$DBRESULT =& $pearDB->query("SELECT * FROM cfg_resource WHERE resource_id = '".$resource_id."' LIMIT 1");
		# Set base value
		$rs = array_map("myDecode", $DBRESULT->fetchRow());
		$DBRESULT->free();
	}
	#
	## Database retrieve information for differents elements list we need on the page
	#
	
	#
	# End of "database-retrieved" information
	##########################################################
	##########################################################
	# Var information to format the element
	#
	$attrsText 		= array("size"=>"35");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"40");

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', _("Add a Resource"));
	else if ($o == "c")
		$form->addElement('header', 'title', _("Modify a Resource"));
	else if ($o == "w")
		$form->addElement('header', 'title', _("View Resource"));

	#
	## Resources CFG basic information
	#
	$form->addElement('header', 'information', _("General Information"));
	$form->addElement('text', 'resource_name', _("Resource Name"), $attrsText);
	$form->addElement('text', 'resource_line', _("MACRO Expression"), $attrsText);
	
	#
	## Further informations
	#
	$form->addElement('header', 'furtherInfos', _("Additional Information"));
	$rsActivation[] = &HTML_QuickForm::createElement('radio', 'resource_activate', null, _("Enabled"), '1');
	$rsActivation[] = &HTML_QuickForm::createElement('radio', 'resource_activate', null, _("Disabled"), '0');
	$form->addGroup($rsActivation, 'resource_activate', _("Status"), '&nbsp;');
	$form->setDefaults(array('resource_activate' => '1'));
	$form->addElement('textarea', 'resource_comment', _("Comment"), $attrsTextarea);
		
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');
	$form->setDefaults(array('action' => '1'));
	
	$form->addElement('hidden', 'resource_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	
	#
	## Form Rules
	#
	function myReplace()	{
		global $form;
		$ret = $form->getSubmitValues();
		return (str_replace(" ", "_", $ret["resource_name"]));
	}
	$form->applyFilter('__ALL__', 'myTrim');
	$form->applyFilter('resource_name', 'myReplace');
	$form->addRule('resource_name', _("Compulsory Name"), 'required');
	$form->addRule('resource_line', _("Compulsory Alias"), 'required');
	$form->registerRule('exist', 'callback', 'testExistence');
	$form->addRule('resource_name', _("Name is already in use"), 'exist');
	$form->setRequiredNote("<font style='color: red;'>*</font>" . _(" Required fields"));

	# 
	##End of form definition
	#
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	# Just watch a Resources CFG information
	if ($o == "w")	{
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&resource_id=".$resource_id."'"));
	    $form->setDefaults($rs);
		$form->freeze();
	}
	# Modify a Resources CFG information
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	    $form->setDefaults($rs);
	}
	# Add a Resources CFG information
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	}
	
	$valid = false;
	if ($form->validate())	{
		$rsObj =& $form->getElement('resource_id');
		if ($form->getSubmitValue("submitA"))
			$rsObj->setValue(insertResourceInDB());
		else if ($form->getSubmitValue("submitC"))
			updateResourceInDB($rsObj->getValue());
		$o = NULL;
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&resource_id=".$rsObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once($path."listResources.php");
	else	{
		#Apply a template definition
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);	
		$tpl->assign('form', $renderer->toArray());	
		$tpl->assign('o', $o);		
		$tpl->display("formResources.ihtml");
	}
?>