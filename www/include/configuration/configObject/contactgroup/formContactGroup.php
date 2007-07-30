<?
/**
Oreon is developped with GPL Licence 2.0 :
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
	$cg = array();
	if (($o == "c" || $o == "w") && $cg_id)	{	
		$DBRESULT =& $pearDB->query("SELECT * FROM contactgroup WHERE cg_id = '".$cg_id."' LIMIT 1");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		# Set base value
		$cg = array_map("myDecode", $DBRESULT->fetchRow());
		# Set Contact Childs
		$DBRESULT =& $pearDB->query("SELECT DISTINCT contact_contact_id FROM contactgroup_contact_relation WHERE contactgroup_cg_id = '".$cg_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		for($i = 0; $DBRESULT->fetchInto($contacts); $i++)
			$cg["cg_contacts"][$i] = $contacts["contact_contact_id"];
		$DBRESULT->free();
	}
	#
	## Database retrieve information for differents elements list we need on the page
	#
	# Contacts comes from DB -> Store in $contacts Array
	$contacts = array();
	$DBRESULT =& $pearDB->query("SELECT contact_id, contact_name FROM contact ORDER BY contact_name");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
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
	$template 		= "<table style='border:0px;'><tr><td>{unselected}</td><td align='center'>{add}<br><br><br>{remove}</td><td>{selected}</td></tr></table>";

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', $lang["cg_add"]);
	else if ($o == "c")
		$form->addElement('header', 'title', $lang["cg_change"]);
	else if ($o == "w")
		$form->addElement('header', 'title', $lang["cg_view"]);

	# Contact basic information
	$form->addElement('header', 'information', $lang['cg_infos']);
	$form->addElement('text', 'cg_name', $lang["cg_name"], $attrsText);
	$form->addElement('text', 'cg_alias', $lang["cg_alias"], $attrsText);
	
	# Contacts Selection
	$form->addElement('header', 'notification', $lang['hg_links']);
	
    $ams1 =& $form->addElement('advmultiselect', 'cg_contacts', $lang["cg_members"], $contacts, $attrsAdvSelect);
	$ams1->setButtonAttributes('add', array('value' =>  $lang['add']));
	$ams1->setButtonAttributes('remove', array('value' => $lang['delete']));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);
	
	# Further informations
	$form->addElement('header', 'furtherInfos', $lang['further_infos']);
	$cgActivation[] = &HTML_QuickForm::createElement('radio', 'cg_activate', null, $lang["enable"], '1');
	$cgActivation[] = &HTML_QuickForm::createElement('radio', 'cg_activate', null, $lang["disable"], '0');
	$form->addGroup($cgActivation, 'cg_activate', $lang["status"], '&nbsp;');
	$form->setDefaults(array('cg_activate' => '1'));
	$form->addElement('textarea', 'cg_comment', $lang["comment"], $attrsTextarea);
	
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionList'], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionForm'], '0');
	$form->addGroup($tab, 'action', $lang["action"], '&nbsp;');	
	$form->setDefaults(array('action' => '1'));
	
	$form->addElement('hidden', 'cg_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	
	# Form Rules
	function myReplace()	{
		global $form;
		$ret = $form->getSubmitValues();
		return (str_replace(" ", "_", $ret["cg_name"]));
	}

	$form->applyFilter('__ALL__', 'myTrim');
	$form->applyFilter('cg_name', 'myReplace');
	$form->addRule('cg_name', $lang['ErrName'], 'required');
	$form->addRule('cg_alias', $lang['ErrAlias'], 'required');
	$form->registerRule('exist', 'callback', 'testContactGroupExistence');
	$form->addRule('cg_name', $lang['ErrAlreadyExist'], 'exist');
	$form->setRequiredNote($lang['requiredFields']);

	# End of form definition

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	# Just watch a Contact Group information
	if ($o == "w")	{
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&cg_id=".$cg_id."'"));
	    $form->setDefaults($cg);
		$form->freeze();
	}
	# Modify a Contact Group information
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	    $form->setDefaults($cg);
	}
	# Add a Contact Group information
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	}
	
	$valid = false;
	if ($form->validate())	{
		$cgObj =& $form->getElement('cg_id');
		if ($form->getSubmitValue("submitA"))
			$cgObj->setValue(insertContactGroupInDB());
		else if ($form->getSubmitValue("submitC"))
			updateContactGroupInDB($cgObj->getValue());
		$o = NULL;
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&cg_id=".$cgObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once($path."listContactGroup.php");
	else	{
		#Apply a template definition
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);	
		$tpl->assign('form', $renderer->toArray());	
		$tpl->assign('o', $o);		
		$tpl->display("formContactGroup.ihtml");
	}
?>