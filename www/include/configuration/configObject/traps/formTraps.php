<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
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
	## Database retrieve information for Trap
	#
	
	function myDecodeTrap($arg)	{
		$arg = html_entity_decode($arg, ENT_QUOTES);
		return($arg);
	}

	$trap = array();
	$mnftr = array(NULL=>NULL);
	$mnftr_id = -1;
	if (($o == "c" || $o == "w") && $traps_id)	{		
		$DBRESULT =& $pearDB->query("SELECT * FROM traps WHERE traps_id = '".$traps_id."' LIMIT 1");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		# Set base value
		$trap = array_map("myDecodeTrap", $DBRESULT->fetchRow());
		$DBRESULT->free();
	}
	$DBRESULT =& $pearDB->query("SELECT id, alias FROM traps_vendor");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	while($DBRESULT->fetchInto($rmnftr)){
		$mnftr[$rmnftr["id"]] = $rmnftr["alias"];
	}
	$DBRESULT->free();
	
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
		$form->addElement('header', 'title', $lang["m_traps_add"]);
	else if ($o == "c")
		$form->addElement('header', 'title', $lang["m_traps_change"]);
	else if ($o == "w")
		$form->addElement('header', 'title', $lang["m_traps_view"]);

	#
	## Command information
	#
	$form->addElement('text', 'traps_name', $lang["m_traps_name"], $attrsText);
	$form->addElement('text', 'traps_oid', $lang["m_traps_oid"], $attrsText);
	$form->addElement('text', 'traps_args', $lang["m_traps_args"], $attrsText);
	$form->addElement('select', 'traps_status', $lang["m_traps_status"], array(0=>$lang['m_mon_ok'], 1=>$lang['m_mon_warning'], 2=>$lang['m_mon_critical'], 3=>$lang['m_mon_unknown']));
	$form->addElement('select', 'manufacturer_id', $lang["m_traps_manufacturer"], $mnftr);
	$form->addElement('textarea', 'traps_comments', $lang["m_traps_comments"], $attrsTextarea);

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionList'], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionForm'], '0');
	$form->addGroup($tab, 'action', $lang["action"], '&nbsp;');
	$form->setDefaults(array('action'=>'1'));

	#
	## Further informations
	#
	$form->addElement('hidden', 'traps_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	#
	## Form Rules
	#
	function myReplace()	{
		global $form;
		return (str_replace(" ", "_", $form->getSubmitValue("traps_name")));
	}
	$form->applyFilter('__ALL__', 'myTrim');
	$form->applyFilter('traps_name', 'myReplace');
	$form->addRule('traps_name', $lang['ErrName'], 'required');
	$form->addRule('traps_oid', $lang['ErrName'], 'required');
	$form->addRule('manufacturer_id', $lang['ErrName'], 'required');
	$form->addRule('traps_args', $lang['ErrName'], 'required');
	$form->registerRule('exist', 'callback', 'testTrapExistence');
	$form->addRule('traps_name', $lang['ErrAlreadyExist'], 'exist');
	$form->setRequiredNote($lang['requiredFields']);

	#
	##End of form definition
	#

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	# Just watch a Command information
	if ($o == "w")	{
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&traps_id=".$traps_id."'"));
	    $form->setDefaults($trap);
		$form->freeze();
	}
	# Modify a Command information
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	    $form->setDefaults($trap);
	}
	# Add a Command information
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	}

	$valid = false;
	if ($form->validate())	{
		$trapObj =& $form->getElement('traps_id');
		if ($form->getSubmitValue("submitA")) 
			$trapObj->setValue(insertTrapInDB());
		else if ($form->getSubmitValue("submitC"))
			updateTrapInDB($trapObj->getValue());
		$o = NULL;
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&traps_id=".$trapObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action =& $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once($path."listTraps.php");
	else	{
		##Apply a template definition
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		$tpl->display("formTraps.ihtml");
	}
?>