<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */
	#
	## Database retrieve information for Directory
	#
	$dir = array();
	$list = array();
	$selected = array();
	/*
	 * Change Directory
	 */
	if ($o == "cd" && $dir_id) {
		$DBRESULT = $pearDB->query("SELECT * FROM view_img_dir WHERE dir_id = '".$dir_id."' LIMIT 1");
		$dir = array_map("myDecode", $DBRESULT->fetchRow());
		# Set Child elements
		$DBRESULT = $pearDB->query("SELECT DISTINCT img_img_id FROM view_img_dir_relation WHERE dir_dir_parent_id = '".$dir_id."'");
		for($i = 0; $imgs = $DBRESULT->fetchRow(); $i++) {
			$dir["dir_imgs"][$i] = $imgs["img_img_id"];
		}
		$DBRESULT->free();
	}
	else if ($o == "m") {
	    $selected = array();
	    if (isset($select) && $select)
		$list = $select;
	    else if (isset($dir_imgs) && $dir_imgs)
		$list = $dir_imgs;
	
	    foreach($list as $selector=>$status) {
		$ids = explode('-',$selector);
		if (count($ids)!=2) continue;
		$selected[$ids[1]] = $ids[1];
	    }
	}
	
	#
	## Database retrieve information for differents elements list we need on the page
	#
	# Images comes from DB -> Store in $imgs Array
	$imgs = array();
	$rq = "SELECT `img_id`,`dir_alias`,`img_name` FROM view_img ";
	$rq .= " JOIN view_img_dir_relation ON img_img_id = img_id ";
	$rq .= " JOIN view_img_dir ON dir_id = dir_dir_parent_id ";
	if ($o == "m" && count($selected) > 0)
	    $rq .= " WHERE `img_id` IN (".implode(",",$selected).") ";
	$rq .= " ORDER BY dir_alias, img_name";
	$DBRESULT = $pearDB->query($rq);
	while ($img = $DBRESULT->fetchRow()) {
		$imgs[$img["img_id"]] = $img["dir_alias"]."/".$img["img_name"];
	}
	$DBRESULT->free();

	$directories = array();
	$DBRESULT = $pearDB->query("SELECT dir_id, dir_name, dir_comment FROM view_img_dir ORDER BY dir_name");
	while ($row = $DBRESULT->fetchRow()) {
    	    $directories[$row["dir_id"]] = $row["dir_name"];
	}
					        

	##########################################################
	# Var information to format the element
	#
	$attrsText	= array("size"=>"30");
	$attrsSelect	= array("size"=>"5", "multiple"=>"1", "cols"=>"40");
	$attrsAdvSelect	= array("style" => "width: 250px; height: 250px;");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"40");

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "cd") { 
		$form->addElement('header', 'title', _("Modify directory"));
		$form->addElement('autocomplete', 'dir_name', _("Directory name"), $directories);
		$form->addElement('textarea', 'dir_comment', _("Comments"), $attrsTextarea);
		$form->setDefaults($dir);
	} else if ($o == "m") {
		$form->addElement('header', 'title', _("Move files to directory"));
		$form->addElement('autocomplete', 'dir_name', _("Destination directory"), $directories);
		$form->addElement('select', 'dir_imgs', _("Images"), $imgs, $attrsSelect);
	}
	
	$tab = array();
	$tab[] = HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Action"), '&nbsp;');	
	$form->setDefaults(array('action' => '1'));
	
	$form->addElement('hidden', 'dir_id');
	$form->addElement('hidden', 'select');
	$redirect = $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	
	#
	## Form Rules
	#
	$form->applyFilter('__ALL__', 'myTrim');
	if ( $o == "cd" && $dir_id ) {
	    $form->addRule('dir_name', _("Compulsory Name"), 'required');
//	    $form->addRule('dir_alias', _("Compulsory Alias"), 'required');
	    $form->setRequiredNote(_("Required Field"));
	}

	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
        // prepare help texts
	$helptext = "";
	include_once("help.php");
	foreach ($help as $key => $text) {
		$helptext .= '<span style="display:none" id="help:'.$key.'">'.$text.'</span>'."\n";
	}
	$tpl->assign("helptext", $helptext);
        
	# move files to a directory
	if ($o == "m")	{
		$subM = $form->addElement('submit', 'submitM', _("Apply"));
		$res = $form->addElement("button", "cancel", _("Cancel"), array("onClick"=>"javascript:window.location.href='?p=".$p."'"));
	}
	# Modify a directory
	else if ($o == "cd")	{
		if (isset($dir['dir_imgs']))
		    $confirm = implode(',',$dir['dir_imgs']);
		else
		    $confirm = "";
		$subC = $form->addElement('submit', 'submitC', _("Save"));
		$res = $form->addElement("button", "cancel", _("Cancel"), array("onClick"=>"javascript:window.location.href='?p=".$p."'"));
		$form->setDefaults($dir);
	}

	$valid = false;
	if ($form->validate())	{
		$dir_id = $form->getSubmitValue('dir_id');
		/* move images */
		if ($form->getSubmitValue("submitM")) {
			$dir_name = $form->getSubmitValue('dir_name');
			$imgs = $form->getSubmitValue('dir_imgs');
			moveMultImg($imgs, $dir_name);
		}
		/* modify dir */
		else if ($form->getSubmitValue("submitC")) {
			$dirName = $form->getSubmitValue('dir_name');
			$dirCmnt = $form->getSubmitValue('dir_comment');
			updateDirectory($dir_id, $dirName, $dirCmnt);
		}
		$o = NULL;
//		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&dir_id=".$dirObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	if ($valid) {
		require_once($path."listImg.php");
	} else	{
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