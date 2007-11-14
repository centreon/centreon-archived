<?php
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Sugumaran Mathavarajan - Julien Mathis - Romain Le Merlus

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
	## Database retrieve information for Manufacturer
	#
	
	function myDecodeMnftr($arg)	{
		$arg = html_entity_decode($arg, ENT_QUOTES);
		return($arg);
	}

	$mnftr = array();
	if (($o == "c" || $o == "w") && $id)	{		
		$DBRESULT =& $pearDB->query("SELECT * FROM traps_vendor WHERE id = '".$id."' LIMIT 1");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
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
		$form->addElement('header', 'title', $lang["m_mnftr_add"]);
	else if ($o == "c")
		$form->addElement('header', 'title', $lang["m_mnftr_change"]);
	else if ($o == "w")
		$form->addElement('header', 'title', $lang["m_mnftr_view"]);

	#
	## Manufacturer information
	#
	$form->addElement('text', 'name', $lang["m_mnftr_name"], $attrsText);
	$form->addElement('text', 'alias', $lang["m_mnftr_alias"], $attrsText);
	$form->addElement('textarea', 'description', $lang["m_mnftr_desc"], $attrsTextarea);

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionList'], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionForm'], '0');
	$form->addGroup($tab, 'action', $lang["action"], '&nbsp;');
	$form->setDefaults(array('action'=>'1'));

	#
	## Further informations
	#
	$form->addElement('hidden', 'id');
	$redirect =& $form->addElement('hidden', 'o');
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
	$form->addRule('name', $lang['ErrName'], 'required');
	$form->addRule('alias', $lang['ErrName'], 'required');
	$form->registerRule('exist', 'callback', 'testMnftrExistence');
	$form->addRule('name', $lang['ErrAlreadyExist'], 'exist');
	$form->setRequiredNote($lang['requiredFields']);

	#
	##End of form definition
	#

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	# Just watch a Command information
	if ($o == "w")	{
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&id=".$id."'"));
	    $form->setDefaults($mnftr);
		$form->freeze();
	}
	# Modify a Command information
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	    $form->setDefaults($mnftr);
	}
	# Add a Command information
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	}

	$valid = false;
	if ($form->validate())	{
		$mnftrObj =& $form->getElement('id');
		if ($form->getSubmitValue("submitA")) 
			$mnftrObj->setValue(insertMnftrInDB());
		else if ($form->getSubmitValue("submitC"))
			updateMnftrInDB($mnftrObj->getValue());
		$o = NULL;
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&id=".$mnftrObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action =& $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once($path."listMnftr.php");
	else	{
		##Apply a template definition
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		$tpl->display("formMnftr.ihtml");
	}
?>