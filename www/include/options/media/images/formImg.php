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
	if ($o == "ci" || $o == "w")	{
		$res =& $pearDB->query("SELECT * FROM view_img WHERE img_id = '".$img_id."' LIMIT 1");
		# Set base value
		$img = array_map("myDecode", $res->fetchRow());
		
		# Set Directories
		$q =  "SELECT dir_name, dir_alias, img_path FROM view_img";
		$q .= "  JOIN view_img_dir_relation ON img_id = view_img_dir_relation.img_img_id";
		$q .= "  JOIN view_img_dir ON dir_id = dir_dir_parent_id";
		$q .= "  WHERE img_id = '".$img_id."' LIMIT 1";
		$DBRESULT =& $pearDB->query($q);
		$dir =& $DBRESULT->fetchRow();
		$img_path = "./img/media/".$dir["dir_alias"]."/".$dir["img_path"];
		$img["directories"] = $dir["dir_name"];
		$DBRESULT->free();
	}
	#
	## Database retrieve information for differents elements list we need on the page
	#
	$dir_ids = array();
	$DBRESULT =& $pearDB->query("SELECT dir_id, dir_name FROM view_img_dir ORDER BY dir_name");
	while ($dir =& $DBRESULT->fetchRow()) {
		$dir_ids[$dir["dir_id"]] = $dir["dir_name"];
	}
	$DBRESULT->free();
	#
	# End of "database-retrieved" information
	##########################################################
	##########################################################
	# Var information to format the element
	#
	$attrsText 	= array("size"=>"35");
	$attrsAdvSelect = array("style" => "width: 200px; height: 100px;");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"80");

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a") {
		$form->addElement('header', 'title', _("Add Image(s)"));
		$form->addElement('autocomplete', 'directories', _("Existing or new directory"), $dir_ids);
 		$file =& $form->addElement('file', 'filename', _("Image or archive (tar.gz)"));	
		$subA =& $form->addElement('submit', 'submitA', _("Save"));
	} else if ($o == "ci") {
		$form->addElement('header', 'title', _("Modify Image"));
		$form->addElement('text', 'img_name', _("Image Name"), $attrsText);
		$form->addElement('autocomplete', 'directories', _("Existing or new directory"), $dir_ids);
 		$file =& $form->addElement('file', 'filename', _("Image"));
		$subC =& $form->addElement('submit', 'submitC', _("Save"));
		$form->setDefaults($img);
		$form->addRule('img_name', _("Compulsory image name"), 'required');
	} else if ($o == "w") {
		$form->addElement('header', 'title', _("View Image"));
		$form->addElement('text', 'img_name', _("Image Name"), $attrsText);
		$form->addElement('text', 'img_path', $img_path, NULL);
		$form->addElement('autocomplete', 'directories', _("Directory"), $dir_ids);
 		$file =& $form->addElement('file', 'filename', _("Image"));	
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=ci&img_id=".$img_id."'"));
		$form->setDefaults($img);
	}
	$form->addElement("button", "cancel", _("Cancel"), array("onClick"=>"javascript:window.location.href='?p=".$p."'"));

	$form->addElement('textarea', 'img_comment', _("Comments"), $attrsTextarea);	

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("Return to list"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("Review form after save"), '0');
	$form->addGroup($tab, 'action', _("Action"), '&nbsp;');
	$form->setDefaults(array('action'=>'1'));
	
	$form->addElement('hidden', 'img_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	
	#
	## Form Rules
	#
	$form->applyFilter('__ALL__', 'myTrim');
	$form->addRule('directories', _("Required Field"), 'required');
	$form->setRequiredNote(_("Required Field"));
	
	# watch/view
	if ($o == "w")	{
		$form->freeze(); // modifications not allowed
	}

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	$valid = false;
	if ($form->validate())	{
		$imgID =& $form->getElement('img_id');	
		$imgPath = $form->getElement('directories')->getValue();
		$imgComment = $form->getElement('img_comment')->getValue();
		if ($form->getSubmitValue("submitA"))
			$imgID->setValue(insertImg($file, $imgPath, $imgComment));
		else if ($form->getSubmitValue("submitC")) {
			$imgName = $form->getElement('img_name')->getValue();
			updateImg($imgID->getValue(), $file, $imgPath, $imgName, $imgComment);
		}
		$o = NULL;	
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=ci&img_id=".$imgID->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid)
	// && $action["action"]["action"])
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
