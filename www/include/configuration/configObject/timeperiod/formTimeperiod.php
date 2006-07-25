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
	## Database retrieve information for Time Period
	#
	$tp = array();
	if (($o == "c" || $o == "w") && $tp_id)	{	
		$res =& $pearDB->query("SELECT * FROM timeperiod WHERE tp_id = '".$tp_id."' LIMIT 1");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		# Set base value
		$tp = array_map("myDecode", $res->fetchRow());
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

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', $lang["tp_add"]);
	else if ($o == "c")
		$form->addElement('header', 'title', $lang["tp_change"]);
	else if ($o == "w")
		$form->addElement('header', 'title', $lang["tp_view"]);

	#
	## Time Period basic information
	#
	$form->addElement('header', 'information', $lang['tp_infos']);
	$form->addElement('text', 'tp_name', $lang["tp_name"], $attrsText);
	$form->addElement('text', 'tp_alias', $lang["tp_alias"], $attrsText);
	
	##
	## Notification informations
	##
	$form->addElement('header', 'notification', $lang['tp_notif']);
	
	$form->addElement('text', 'tp_sunday', $lang["tp_sunday"], $attrsText);
	$form->addElement('text', 'tp_monday', $lang["tp_monday"], $attrsText);
	$form->addElement('text', 'tp_tuesday', $lang["tp_tuesday"], $attrsText);
	$form->addElement('text', 'tp_wednesday', $lang["tp_wednesday"], $attrsText);
	$form->addElement('text', 'tp_thursday', $lang["tp_thursday"], $attrsText);
	$form->addElement('text', 'tp_friday', $lang["tp_friday"], $attrsText);
	$form->addElement('text', 'tp_saturday', $lang["tp_saturday"], $attrsText);
	
	#
	## Further informations
	#
	
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionList'], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionForm'], '0');
	$form->addGroup($tab, 'action', $lang["action"], '&nbsp;');	
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
	$form->applyFilter('_ALL_', 'trim');
	$form->applyFilter('tp_name', 'myReplace');
	$form->addRule('tp_name', $lang['ErrName'], 'required');
	$form->addRule('tp_alias', $lang['ErrAlias'], 'required');
	$form->registerRule('exist', 'callback', 'testTPExistence');
	$form->addRule('tp_name', $lang['ErrAlreadyExist'], 'exist');
	$form->setRequiredNote($lang['requiredFields']);

	# 
	##End of form definition
	#
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	# Just watch a Time Period information
	if ($o == "w")	{
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&tp_id=".$tp_id."'"));
	    $form->setDefaults($tp);
		$form->freeze();
	}
	# Modify a Time Period information
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	    $form->setDefaults($tp);
	}
	# Add a Time Period information
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	}
	
	$valid = false;
	if ($form->validate())	{
		$tpObj =& $form->getElement('tp_id');
		if ($form->getSubmitValue("submitA"))
			$tpObj->setValue(insertTimeperiodInDB());
		else if ($form->getSubmitValue("submitC"))
			updateTimeperiodInDB($tpObj->getValue());
		$o = "w";		
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&tp_id=".$tpObj->getValue()."'"));
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