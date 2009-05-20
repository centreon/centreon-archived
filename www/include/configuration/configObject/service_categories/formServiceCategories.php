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
	#
	## Database retrieve information for Contact
	#
	$cct = array();
	if (($o == "c" || $o == "w") && $sc_id)	{
		$DBRESULT =& $pearDB->query("SELECT * FROM `service_categories` WHERE `sc_id` = '".$sc_id."' LIMIT 1");
		# Set base value
		$sc = array_map("myDecode", $DBRESULT->fetchRow());
		$DBRESULT->free();
	}
	
	#
	# End of "database-retrieved" information
	##########################################################
	##########################################################
	# Var information to format the element
	#
	$attrsText 		= array("size"=>"30");
	$attrsText2 	= array("size"=>"60");
	$attrsAdvSelect = array("style" => "width: 200px; height: 100px;");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"40");
	$template 		= "<table><tr><td>{unselected}</td><td align='center'>{add}<br /><br /><br />{remove}</td><td>{selected}</td></tr></table>";

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', _("Add a Service Category"));
	else if ($o == "c")
		$form->addElement('header', 'title', _("Modify a Service Category"));
	else if ($o == "w")
		$form->addElement('header', 'title', _("View a Service Category"));
	
	# Contact basic information
	$form->addElement('header', 'information', _("Information"));

	# No possibility to change name and alias, because there's no interest
	$form->addElement('text', 'sc_name', _("Name"), $attrsText);
	$form->addElement('text', 'sc_description', _("Description"), $attrsText);

	$sc_activate[] = &HTML_QuickForm::createElement('radio', 'sc_activate', null, _("Enabled"), '1');
	$sc_activate[] = &HTML_QuickForm::createElement('radio', 'sc_activate', null, _("Disabled"), '0');
	$form->addGroup($sc_activate, 'sc_activate', _("Status"), '&nbsp;');
	$form->setDefaults(array('sc_activate' => '1'));

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');
	$form->setDefaults(array('action'=>'1'));

	$form->addElement('hidden', 'sc_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	if (is_array($select))	{
		$select_str = NULL;
		foreach ($select as $key => $value)
			$select_str .= $key.",";
		$select_pear =& $form->addElement('hidden', 'select');
		$select_pear->setValue($select_str);
	}
	
	# Form Rules
	
	function myReplace()	{
		global $form;
		$ret = $form->getSubmitValues();
		return (str_replace(" ", "_", $ret["contact_name"]));
	}

	$form->applyFilter('__ALL__', 'myTrim');
	$form->applyFilter('contact_name', 'myReplace');
	$from_list_menu = false;
	
	$form->addRule('sc_name', _("Compulsory Name"), 'required');
	$form->addRule('sc_description', _("Compulsory Alias"), 'required');
	
	$form->registerRule('existName', 'callback', 'testServiceCategorieExistence');
	$form->addRule('sc_name', _("Name is already in use"), 'existName');
	
	
	$form->setRequiredNote("<font style='color: red;'>*</font>". _(" Required fields"));

	# End of form definition

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	# Just watch a service_categories information
	if ($o == "w")	{
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&sc_id=".$sc_id."'"));
	    $form->setDefaults($cct);
		$form->freeze();
	} else if ($o == "c")	{
		# Modify a service_categories information
		$subC =& $form->addElement('submit', 'submitC', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	    $form->setDefaults($sc);
	} else if ($o == "a")	{
		# Add a service_categories information
		$subA =& $form->addElement('submit', 'submitA', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	}

	$valid = false;
	if ($form->validate() && $from_list_menu == false)	{
		$cctObj =& $form->getElement('sc_id');
		if ($form->getSubmitValue("submitA"))
			$cctObj->setValue(insertServiceCategorieInDB());
		else if ($form->getSubmitValue("submitC"))
			updateServiceCategorieInDB($cctObj->getValue());
		$o = NULL;
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&sc_id=".$cctObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once($path."listServiceCategories.php");
	else	{
		#Apply a template definition
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		$tpl->assign('p', $p);
		$tpl->display("formServiceCategories.ihtml");
	}
?>