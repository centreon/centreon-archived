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
		$form->addRule('img_name', _("Compulsory image name"), 'required');
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