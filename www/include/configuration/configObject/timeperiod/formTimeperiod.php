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
 

	#
	## Database retrieve information for Time Period
	#
	$tp = array();
	if (($o == "c" || $o == "w") && $tp_id)	{	
		$DBRESULT =& $pearDB->query("SELECT * FROM timeperiod WHERE tp_id = '".$tp_id."' LIMIT 1");
		# Set base value
		$tp = array_map("myDecode", $DBRESULT->fetchRow());
	}

	##########################################################
	# Var information to format the element
	#
	$attrsText 		= array("size"=>"35");

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', _("Add a Time Period"));
	else if ($o == "c")
		$form->addElement('header', 'title', _("Modify a Time Period"));
	else if ($o == "w")
		$form->addElement('header', 'title', _("View a Time Period"));

	#
	## Time Period basic information
	#
	$form->addElement('header', 'information', _("General Information"));
	$form->addElement('text', 'tp_name', _("Time Period Name"), $attrsText);
	$form->addElement('text', 'tp_alias', _("Alias"), $attrsText);
	
	##
	## Notification informations
	##
	$form->addElement('header', 'notification', _("Notification Time Range"));
	
	$form->addElement('text', 'tp_sunday', _("Sunday"), $attrsText);
	$form->addElement('text', 'tp_monday', _("Monday"), $attrsText);
	$form->addElement('text', 'tp_tuesday', _("Tuesday"), $attrsText);
	$form->addElement('text', 'tp_wednesday', _("Wednesday"), $attrsText);
	$form->addElement('text', 'tp_thursday', _("Thursday"), $attrsText);
	$form->addElement('text', 'tp_friday', _("Friday"), $attrsText);
	$form->addElement('text', 'tp_saturday', _("Saturday"), $attrsText);
	
	#
	## Further informations
	#
	
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');	
	$form->setDefaults(array('action' => '1'));
	
	$form->addElement('hidden', 'tp_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	
	#
	## Form Rules
	#
	function myReplace()	{
		global $form;
		$ret = $form->getSubmitValues();
		return (str_replace(" ", "_", $ret["tp_name"]));
	}
	$form->applyFilter('__ALL__', 'myTrim');
	$form->applyFilter('tp_name', 'myReplace');
	$form->addRule('tp_name', _("Compulsory Name"), 'required');
	$form->addRule('tp_alias', _("Compulsory Alias"), 'required');
	$form->registerRule('exist', 'callback', 'testTPExistence');
	$form->addRule('tp_name', _("Name is already in use"), 'exist');
	$form->setRequiredNote("<font style='color: red;'>*</font>". _(" Required fields"));

	# 
	##End of form definition
	#
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	# Just watch a Time Period information
	if ($o == "w")	{
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&tp_id=".$tp_id."'"));
	    $form->setDefaults($tp);
		$form->freeze();
	}
	# Modify a Time Period information
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	    $form->setDefaults($tp);
	}
	# Add a Time Period information
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	}
	
	$valid = false;
	if ($form->validate())	{
		$tpObj =& $form->getElement('tp_id');
		if ($form->getSubmitValue("submitA"))
			$tpObj->setValue(insertTimeperiodInDB());
		else if ($form->getSubmitValue("submitC"))
			updateTimeperiodInDB($tpObj->getValue());
		$o = NULL;		
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&tp_id=".$tpObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once($path."listTimeperiod.php");
	else	{
		#Apply a template definition
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);	
		$tpl->assign('form', $renderer->toArray());	
		$tpl->assign('o', $o);		
		$tpl->display("formTimeperiod.ihtml");
	}
?>