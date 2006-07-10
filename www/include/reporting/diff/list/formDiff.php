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
	## Database retrieve information for Contact
	#
	if ($o == "c" || $o == "w")	{	
		function myDecode($arg)	{
			return html_entity_decode($arg, ENT_QUOTES);
		}
		$res =& $pearDB->query("SELECT * FROM reporting_diff_list WHERE rtdl_id = '".$rtdl_id."' LIMIT 1");
		# Set base value
		$list = array_map("myDecode", $res->fetchRow());
		# Set Mails List
		$res =& $pearDB->query("SELECT DISTINCT rtde_id FROM reporting_email_list_relation WHERE rtdl_id = '".$rtdl_id."' AND oreon_contact = '0'");
		for($i = 0; $res->fetchInto($mail); $i++)
			$list["list_mails"][$i] = $mail["rtde_id"];
		$res->free();
		# Set Oreon Mails List
		$res =& $pearDB->query("SELECT DISTINCT rtde_id FROM reporting_email_list_relation WHERE rtdl_id = '".$rtdl_id."' AND oreon_contact = '1'");
		for($i = 0; $res->fetchInto($mail); $i++)
			$list["list_oreonMails"][$i] = $mail["rtde_id"];
		$res->free();
	}
	#
	## Database retrieve information for differents elements list we need on the page
	#
	# Mail List comes from DB -> Store in $mails Array
	$mails = array();
	$res =& $pearDB->query("SELECT rtde_id, email FROM reporting_diff_email ORDER BY email");
	while($res->fetchInto($email))
		$mails[$email["rtde_id"]] = $email["email"];
	$res->free();
	# Oreon Mails List comes from DB -> Store in $oreonMails Array
	$oreonMails = array();
	$res =& $pearDB->query("SELECT contact_id, contact_email FROM contact ORDER BY contact_email");
	while($res->fetchInto($email))
		$oreonMails[$email["contact_id"]] = $email["contact_email"];
	$res->free();
	# Timeperiods comes from DB -> Store in $notifsTps Array
	$notifTps = array();
	$res =& $pearDB->query("SELECT tp_id, tp_name FROM timeperiod ORDER BY tp_name");
	while($res->fetchInto($notifTp))
		$notifTps[$notifTp["tp_id"]] = $notifTp["tp_name"];
	$res->free();
	#
	# End of "database-retrieved" information
	##########################################################
	##########################################################
	# Var information to format the element
	#
	$attrsText 		= array("size"=>"30");
	$attrsAdvSelect = array("style" => "width: 200px; height: 200px;");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"40");
	$template 		= "<table><tr><td>{unselected}</td><td align='center'>{add}<br><br><br>{remove}</td><td>{selected}</td></tr></table>";

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', $lang["list_add"]);
	else if ($o == "c")
		$form->addElement('header', 'title', $lang["list_change"]);
	else if ($o == "w")
		$form->addElement('header', 'title', $lang["list_view"]);

	#
	## Contact basic information
	#
	$form->addElement('header', 'information', $lang['list_infos']);
	$form->addElement('text', 'name', $lang["list_name"], $attrsText);
	$form->addElement('text', 'description', $lang["list_description"], $attrsText);
    $form->addElement('select', 'tp_id', $lang["list_period"], $notifTps);
	
    $ams3 =& $form->addElement('advmultiselect', 'list_mails', $lang["list_mails"], $mails, $attrsAdvSelect);
	$ams3->setButtonAttributes('add', array('value' =>  $lang['add']));
	$ams3->setButtonAttributes('remove', array('value' => $lang['delete']));
	$ams3->setElementTemplate($template);
	echo $ams3->getElementJs(false);
	
    $ams3 =& $form->addElement('advmultiselect', 'list_oreonMails', $lang["list_oreonMails"], $oreonMails, $attrsAdvSelect);
	$ams3->setButtonAttributes('add', array('value' =>  $lang['add']));
	$ams3->setButtonAttributes('remove', array('value' => $lang['delete']));
	$ams3->setElementTemplate($template);
	echo $ams3->getElementJs(false);
	
	#
	## Further informations
	#
	$form->addElement('header', 'furtherInfos', $lang['further_infos']);
	$listActivation[] = &HTML_QuickForm::createElement('radio', 'activate', null, $lang["enable"], '1');
	$listActivation[] = &HTML_QuickForm::createElement('radio', 'activate', null, $lang["disable"], '0');
	$form->addGroup($listActivation, 'activate', $lang["status"], '&nbsp;');
	$form->setDefaults(array('activate' => '1', "action"=>'1'));
	
	$form->addElement('textarea', 'comment', $lang["cmt_comment"], $attrsTextarea);
	
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionList'], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionForm'], '0');
	$form->addGroup($tab, 'action', $lang["action"], '&nbsp;');
	$form->setDefaults(array('action'=>'1'));
	
	$form->addElement('hidden', 'rtdl_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	
	#
	## Form Rules
	#
	$form->applyFilter('_ALL_', 'trim');
	$form->addRule('name', $lang['ErrName'], 'required');
	$form->registerRule('exist', 'callback', 'testExistence');
	$form->addRule('name', $lang['ErrAlreadyExist'], 'exist');
	$form->setRequiredNote($lang['requiredFields']);
	
	# 
	##End of form definition
	#
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	# Just watch a contact information
	if ($o == "w")	{		
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&rtdl_id=".$rtdl_id."'"));
	    $form->setDefaults($list);
		$form->freeze();
	}
	# Modify a contact information
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	    $form->setDefaults($list);
	}
	# Add a contact information
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	}
	
	$valid = false;
	if ($form->validate())	{
		$listObj =& $form->getElement('rtdl_id');
		if ($form->getSubmitValue("submitA"))
			$listObj->setValue(insertListInDB());
		else if ($form->getSubmitValue("submitC"))
			updateListInDB($listObj->getValue());
		$o = "w";
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&rtdl_id=".$listObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once($path."listDiff.php");
	else	{
		#Apply a template definition	
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);	
		$tpl->assign('form', $renderer->toArray());	
		$tpl->assign('o', $o);		
		$tpl->display("formDiff.ihtml");
	}
?>