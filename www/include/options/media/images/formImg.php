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
	## Database retrieve information
	#
	$img = array("img_path"=>NULL);
	if ($o == "c" || $o == "w")	{
		$res =& $pearDB->query("SELECT * FROM view_img WHERE img_id = '".$img_id."' LIMIT 1");
		# Set base value
		$img = array_map("myDecode", $res->fetchRow());
		
		# Set Directories
		$DBRESULT =& $pearDB->query("SELECT DISTINCT dir_dir_parent_id FROM view_img_dir_relation WHERE img_img_id = '".$img_id."'");
		$dir =& $DBRESULT->fetchRow();
		$img["directories"] = $dir["dir_dir_parent_id"];
		$DBRESULT->free();
	}
	#
	## Database retrieve information for differents elements list we need on the page
	#
	# Directories comes from DB -> Store in $dirs Array
	$dirs = array();
	$DBRESULT =& $pearDB->query("SELECT dir_id, dir_name FROM view_img_dir ORDER BY dir_name");
	while ($dir =& $DBRESULT->fetchRow())
		$dirs[$dir["dir_id"]] = $dir["dir_name"];
	$DBRESULT->free();
	#
	# End of "database-retrieved" information
	##########################################################
	##########################################################
	# Var information to format the element
	#
	$attrsText 		= array("size"=>"35");
	$attrsAdvSelect = array("style" => "width: 200px; height: 100px;");
	$attrsTextarea 	= array("rows"=>"10", "cols"=>"40");
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
	## Basic information
	#
	$form->addElement('header', 'information', _("General Information"));	
	$form->addElement('select', 'directories', _("Directory"), $dirs);

	if ($o == "c" || $o == "w")
		$form->addElement('text', 'img_name', _("Image Name"), $attrsText);
 	$file =& $form->addElement('file', 'filename', _("Image"));
	$file1 =& $form->addElement('file', 'filename1', _("Image"));
	$file2 =& $form->addElement('file', 'filename2', _("Image"));
	$file3 =& $form->addElement('file', 'filename3', _("Image"));
	$file4 =& $form->addElement('file', 'filename4', _("Image"));

	if ($o == "w")	{
		$DBRESULT =& $pearDB->query("SELECT dir_alias FROM view_img_dir WHERE dir_id = '".$img["directories"]."' LIMIT 1");
		$dir_alias =& $DBRESULT->fetchRow();
		$form->addElement('text', 'img_path', "./img/media/".$dir_alias["dir_alias"]."/".$img["img_path"], NULL);
	}
	
	$form->addElement('textarea', 'img_comment', _("Comments"), $attrsTextarea);	

	#
	## Further informations
	#
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Action"), '&nbsp;');
	$form->setDefaults(array('action'=>'1'));
	
	$form->addElement('hidden', 'img_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	
	#
	## Form Rules
	#
	$form->applyFilter('__ALL__', 'myTrim');
	if ($o == "c")
		$form->addRule('img_name', $lang['ErrName'], 'required');
	$form->addRule('directories', _("Required Field"), 'required');
	$form->setRequiredNote(_("Required Field"));

	# 
	##End of form definition
	#
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	# Just watch an information
	if ($o == "w")	{
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&img_id=".$img_id."'"));
	    $form->setDefaults($img);
		$form->freeze();
	}
	# Modify an information
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	    $form->setDefaults($img);
	}
	# Add an information
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	}
	
	$valid = false;
	if ($form->validate())	{
		$imgObj =& $form->getElement('img_id');		
		if ($form->getSubmitValue("submitA"))
			$imgObj->setValue(insertImgInDB($file, $file1, $file2, $file3, $file4, $path));
		else if ($form->getSubmitValue("submitC"))
			updateImgInDB($imgObj->getValue(), $file, $path);
		$o = NULL;	
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&img_id=".$imgObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once("listImg.php");
	else	{
		#Apply a template definition	
		$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);	
		$tpl->assign('form', $renderer->toArray());	
		$tpl->assign('o', $o);		
		$tpl->display("formImg.ihtml");
	}
?>