<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 */
	#
	## Database retrieve information for Directory
	#
	$dir = array();
	if (($o == "c" || $o == "w") && $dir_id)	{	
		$DBRESULT = $pearDB->query("SELECT * FROM view_img_dir WHERE dir_id = '".$dir_id."' LIMIT 1");
		# Set base value
		$dir = array_map("myDecode", $DBRESULT->fetchRow());
		# Set Childs elements
		$DBRESULT = $pearDB->query("SELECT DISTINCT img_img_id FROM view_img_dir_relation WHERE dir_dir_parent_id = '".$dir_id."'");
		for($i = 0; $imgs =& $DBRESULT->fetchRow(); $i++)
			$dir["dir_imgs"][$i] = $imgs["img_img_id"];
		$DBRESULT->free();
	}
	#
	## Database retrieve information for differents elements list we need on the page
	#
	# Images comes from DB -> Store in $imgs Array
	$imgs = array();
	$DBRESULT = $pearDB->query("SELECT img_id, img_name FROM view_img ORDER BY img_name");
	while ($img =& $DBRESULT->fetchRow())
		$imgs[$img["img_id"]] = $img["img_name"];
	$DBRESULT->free();
	
	##########################################################
	# Var information to format the element
	#
	$attrsText 		= array("size"=>"30");
	$attrsAdvSelect = array("style" => "width: 200px; height: 250px;");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"40");
	$template 		= "<table><tr><td>{unselected}</td><td align='center'>{add}<br><br><br>{remove}</td><td>{selected}</td></tr></table>";

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', _("Add"));
	else if ($o == "c")
		$form->addElement('header', 'title', _("Modify"));
	else if ($o == "w")
		$form->addElement('header', 'title', _("View"));

	#
	## basic information
	#
	$form->addElement('header', 'information', _("General Information"));
	$form->addElement('text', 'dir_name', _("Name"), $attrsText);
	$form->addElement('text', 'dir_alias', _("Directory name on system"), $attrsText);
		
    $ams1 = $form->addElement('advmultiselect', 'dir_imgs', _("Images"), $imgs, $attrsAdvSelect);
	$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams1->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);
	
	#
	## Further informations
	#
	$form->addElement('header', 'furtherInfos', _("Additional information"));
	$form->addElement('textarea', 'dir_comment', _("Comments"), $attrsTextarea);
	
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Action"), '&nbsp;');	
	$form->setDefaults(array('action' => '1'));
	
	$form->addElement('hidden', 'dir_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	
	#
	## Form Rules
	#
	$form->applyFilter('__ALL__', 'myTrim');
	$form->addRule('dir_name', _("Compulsory Name"), 'required');
	$form->addRule('dir_alias', _("Compulsory Alias"), 'required');
	$form->registerRule('exist', 'callback', 'testDirectoryExistence');
	$form->addRule('dir_name', _("Name is already in use"), 'exist');
	$form->setRequiredNote(_("Required Field"));

	# 
	##End of form definition
	#
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	# Just watch a Contact Group information
	if ($o == "w")	{
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&dir_id=".$dir_id."'"));
	    $form->setDefaults($dir);
		$form->freeze();
	}
	# Modify a Contact Group information
	else if ($o == "c")	{
		$subC = $form->addElement('submit', 'submitC', _("Save"));
		$res = $form->addElement('reset', 'reset', _("Reset"));
	    $form->setDefaults($dir);
	}
	# Add a Contact Group information
	else if ($o == "a")	{
		$subA = $form->addElement('submit', 'submitA', _("Save"));
		$res = $form->addElement('reset', 'reset', _("Reset"));
	}
	
	$valid = false;
	if ($form->validate())	{
		$dirObj = $form->getElement('dir_id');
		if ($form->getSubmitValue("submitA"))
			$dirObj->setValue(insertDirectoryInDB());
		else if ($form->getSubmitValue("submitC"))
			updateDirectoryInDB($dirObj->getValue());
		$o = NULL;
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&dir_id=".$dirObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once($path."listDirectory.php");
	else	{
		#Apply a template definition
		$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);	
		$tpl->assign('form', $renderer->toArray());	
		$tpl->assign('o', $o);		
		$tpl->display("formDirectory.ihtml");
	}
?>