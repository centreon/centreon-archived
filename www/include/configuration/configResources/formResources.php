<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

Adapted to Pear library by Merethis company, under direction of Cedrick Facon, Romain Le Merlus, Julien Mathis

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
	## Database retrieve information for Resources CFG
	#
	if (($o == "c" || $o == "w") && $resource_id)	{	
		$res =& $pearDB->query("SELECT * FROM cfg_resource WHERE resource_id = '".$resource_id."' LIMIT 1");
		# Set base value
		$rs = array_map("myDecode", $res->fetchRow());
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
		$form->addElement('header', 'title', $lang["rs_add"]);
	else if ($o == "c")
		$form->addElement('header', 'title', $lang["rs_change"]);
	else if ($o == "w")
		$form->addElement('header', 'title', $lang["rs_view"]);

	#
	## Resources CFG basic information
	#
	$form->addElement('header', 'information', $lang['rs_infos']);
	$form->addElement('text', 'resource_name', $lang["rs_name"], $attrsText);
	$form->addElement('text', 'resource_line', $lang["rs_line"], $attrsText);
	
	#
	## Further informations
	#
	$form->addElement('header', 'furtherInfos', $lang['further_infos']);
	$rsActivation[] = &HTML_QuickForm::createElement('radio', 'resource_activate', null, $lang["enable"], '1');
	$rsActivation[] = &HTML_QuickForm::createElement('radio', 'resource_activate', null, $lang["disable"], '0');
	$form->addGroup($rsActivation, 'resource_activate', $lang["status"], '&nbsp;');
	$form->setDefaults(array('resource_activate' => '1'));
	$form->addElement('textarea', 'resource_comment', $lang["cmt_comment"], $attrsTextarea);
		
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionList'], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionForm'], '0');
	$form->addGroup($tab, 'action', $lang["action"], '&nbsp;');
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
	$form->applyFilter('_ALL_', 'trim');
	$form->applyFilter('resource_name', 'myReplace');
	$form->addRule('resource_name', $lang['ErrName'], 'required');
	$form->addRule('resource_line', $lang['ErrAlias'], 'required');
	$form->registerRule('exist', 'callback', 'testExistence');
	$form->addRule('resource_name', $lang['ErrAlreadyExist'], 'exist');
	$form->setRequiredNote($lang['requiredFields']);

	# 
	##End of form definition
	#
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	# Just watch a Resources CFG information
	if ($o == "w")	{
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&resource_id=".$resource_id."'"));
	    $form->setDefaults($rs);
		$form->freeze();
	}
	# Modify a Resources CFG information
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	    $form->setDefaults($rs);
	}
	# Add a Resources CFG information
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	}
	
	$valid = false;
	if ($form->validate())	{
		$rsObj =& $form->getElement('resource_id');
		if ($form->getSubmitValue("submitA"))
			$rsObj->setValue(insertResourceInDB());
		else if ($form->getSubmitValue("submitC"))
			updateResourceInDB($rsObj->getValue());
		$o = "w";
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&resource_id=".$rsObj->getValue()."'"));
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