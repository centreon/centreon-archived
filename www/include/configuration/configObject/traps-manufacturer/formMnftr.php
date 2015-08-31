<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

	#
	## Database retrieve information for Manufacturer
	#
	
	function myDecodeMnftr($arg)	{
		$arg = html_entity_decode($arg, ENT_QUOTES, "UTF-8");
		return($arg);
	}

	$mnftr = array();
	if (($o == "c" || $o == "w") && $id)	{		
		$DBRESULT = $pearDB->query("SELECT * FROM traps_vendor WHERE id = '".$id."' LIMIT 1");
		# Set base value
		$mnftr = array_map("myDecodeMnftr", $DBRESULT->fetchRow());
		$DBRESULT->free();
	}
	
	##########################################################
	# Var information to format the element
	#
	$attrsText 		= array("size"=>"50");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"40");
	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', _("Add Vendor"));
	else if ($o == "c")
		$form->addElement('header', 'title', _("Modify Vendor"));
	else if ($o == "w")
		$form->addElement('header', 'title', _("View Vendor"));

	#
	## Manufacturer information
	#
	$form->addElement('text', 'name', _("Vendor Name"), $attrsText);
	$form->addElement('text', 'alias', _("Alias"), $attrsText);
	$form->addElement('textarea', 'description', _("Description"), $attrsTextarea);

	$tab = array();
	$tab[] = HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');
	$form->setDefaults(array('action'=>'1'));

	#
	## Further informations
	#
	$form->addElement('hidden', 'id');
	$redirect = $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	#
	## Form Rules
	#
	function myReplace()	{
		global $form;
		return (str_replace(" ", "_", $form->getSubmitValue("name")));
	}
	$form->applyFilter('__ALL__', 'myTrim');
	$form->applyFilter('name', 'myReplace');
	$form->addRule('name', _("Compulsory Name"), 'required');
	$form->addRule('alias', _("Compulsory Name"), 'required');
	$form->registerRule('exist', 'callback', 'testMnftrExistence');
	$form->addRule('name', _("Name is already in use"), 'exist');
	$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;". _("Required fields"));

	#
	##End of form definition
	#

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	$tpl->assign("helpattr", 'TITLE, "'._("Help").'", CLOSEBTN, true, FIX, [this, 0, 5], BGCOLOR, "#ffff99", BORDERCOLOR, "orange", TITLEFONTCOLOR, "black", TITLEBGCOLOR, "orange", CLOSEBTNCOLORS, ["","black", "white", "red"], WIDTH, -300, SHADOW, true, TEXTALIGN, "justify"' );
	
	# prepare help texts
	$helptext = "";
	include_once("help.php");
	foreach ($help as $key => $text) { 
		$helptext .= '<span style="display:none" id="help:'.$key.'">'.$text.'</span>'."\n";
	}
	$tpl->assign("helptext", $helptext);

	# Just watch a Command information
	if ($o == "w")	{
		if ($centreon->user->access->page($p) != 2)
			$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&id=".$id."'"));
	    $form->setDefaults($mnftr);
		$form->freeze();
	}
	# Modify a Command information
	else if ($o == "c")	{
		$subC = $form->addElement('submit', 'submitC', _("Save"));
		$res = $form->addElement('reset', 'reset', _("Reset"));
	    $form->setDefaults($mnftr);
	}
	# Add a Command information
	else if ($o == "a")	{
		$subA = $form->addElement('submit', 'submitA', _("Save"));
		$res = $form->addElement('reset', 'reset', _("Reset"));
	}

	$valid = false;
	if ($form->validate())	{
		$mnftrObj = $form->getElement('id');
		if ($form->getSubmitValue("submitA")) 
			$mnftrObj->setValue(insertMnftrInDB());
		else if ($form->getSubmitValue("submitC"))
			updateMnftrInDB($mnftrObj->getValue());
		$o = NULL;
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&id=".$mnftrObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"])
		require_once($path."listMnftr.php");
	else	{
		##Apply a template definition
		$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		$tpl->display("formMnftr.ihtml");
	}
?>
