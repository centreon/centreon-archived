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
	$mail = array();
	if ($o == "c" || $o == "w")	{	
		function myDecode($arg)	{
			return html_entity_decode($arg, ENT_QUOTES);
		}
		$res =& $pearDB->query("SELECT * FROM reporting_diff_email WHERE rtde_id = '".$rtde_id."' LIMIT 1");
		# Set base value
		$mail = array_map("myDecode", $res->fetchRow());
		# Set Diffusion Lists
		$res =& $pearDB->query("SELECT DISTINCT rtdl_id FROM reporting_email_list_relation WHERE rtde_id = '".$rtde_id."'");
		for($i = 0; $res->fetchInto($list); $i++)
			$mail["contact_lists"][$i] = $list["rtdl_id"];
		$res->free();
	}
	#
	## Database retrieve information for differents elements list we need on the page
	#
	# Diffusion List comes from DB -> Store in $difLists Array
	$diffLists = array();
	$res =& $pearDB->query("SELECT rtdl_id, name FROM reporting_diff_list ORDER BY name");
	while($res->fetchInto($list))
		$diffLists[$list["rtdl_id"]] = $list["name"];
	$res->free();
	#
	# End of "database-retrieved" information
	##########################################################
	##########################################################
	# Var information to format the element
	#
	$attrsText 		= array("size"=>"30");
	$attrsAdvSelect = array("style" => "width: 200px; height: 100px;");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"40");
	$template 		= "<table><tr><td>{unselected}</td><td align='center'>{add}<br><br><br>{remove}</td><td>{selected}</td></tr></table>";

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', $lang["mailDB_add"]);
	else if ($o == "c")
		$form->addElement('header', 'title', $lang["mailDB_change"]);
	else if ($o == "w")
		$form->addElement('header', 'title', $lang["mailDB_view"]);

	#
	## Contact basic information
	#
	$form->addElement('header', 'information', $lang['mailDB_infos']);
	if ($o == "a")	{
		$form->addElement('textarea', 'email', $lang["mailDB_mail"], $attrsTextarea);
		$form->addElement('header', 'emailInfos', $lang["mailDB_mailTxt"]);
	}
	else
		$form->addElement('text', 'email', $lang["mailDB_mail"], $attrsText);
	
	$tab = array();
    $tab[] = &HTML_QuickForm::createElement('radio', 'format', null, $lang["mailDB_htmlType"], '1');
    $tab[] = &HTML_QuickForm::createElement('radio', 'format', null, $lang["mailDB_textType"], '2');
    $form->addGroup($tab, 'format', $lang["mailDB_receiptType"], '&nbsp;');
    $form->setDefaults(array('format' => '1'));
	
    $ams3 =& $form->addElement('advmultiselect', 'contact_lists', $lang["mailDB_diffList"], $diffLists, $attrsAdvSelect);
	$ams3->setButtonAttributes('add', array('value' =>  $lang['add']));
	$ams3->setButtonAttributes('remove', array('value' => $lang['delete']));
	$ams3->setElementTemplate($template);
	echo $ams3->getElementJs(false);
	
	#
	## Further informations
	#
	$form->addElement('header', 'furtherInfos', $lang['further_infos']);
	$mailActivation[] = &HTML_QuickForm::createElement('radio', 'activate', null, $lang["enable"], '1');
	$mailActivation[] = &HTML_QuickForm::createElement('radio', 'activate', null, $lang["disable"], '0');
	$form->addGroup($mailActivation, 'activate', $lang["status"], '&nbsp;');
	$form->setDefaults(array('activate' => '1', "action"=>'1'));
	
	$form->addElement('textarea', 'comment', $lang["cmt_comment"], $attrsTextarea);
	
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionList'], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionForm'], '0');
	$form->addGroup($tab, 'action', $lang["action"], '&nbsp;');
	$form->setDefaults(array('action'=>'1'));
	
	$form->addElement('hidden', 'rtde_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	
	#
	## Form Rules
	#
	$form->applyFilter('_ALL_', 'trim');
	//$form->addRule('email', $lang['ErrEmail'], 'required');
	//$form->registerRule('exist', 'callback', 'testExistence');
	//$form->addRule('email', $lang['ErrAlreadyExist'], 'exist');
	$form->setRequiredNote($lang['requiredFields']);
	
	# 
	##End of form definition
	#
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	# Just watch a contact information
	if ($o == "w")	{		
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&rtde_id=".$rtde_id."'"));
	    $form->setDefaults($mail);
		$form->freeze();
	}
	# Modify a contact information
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	    $form->setDefaults($mail);
	}
	# Add a contact information
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	}
	
	$valid = false;
	if ($form->validate())	{
		$mailObj =& $form->getElement('rtde_id');
		if ($form->getSubmitValue("submitA"))
			$mailObj->setValue(insertMailInDB());
		else if ($form->getSubmitValue("submitC"))
			updateMailInDB($mailObj->getValue());
		$o = "w";
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&rtde_id=".$mailObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once($path."listMailDB.php");
	else	{
		#Apply a template definition	
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);	
		$tpl->assign('form', $renderer->toArray());	
		$tpl->assign('o', $o);		
		$tpl->display("formMailDB.ihtml");
	}
?>