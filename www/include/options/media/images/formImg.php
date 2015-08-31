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

	if (!isset($centreon)) {
		exit();
	}

	/*
	 * Database retrieve information
	 */
	$img = array("img_path"=>NULL);
	if ($o == "ci" || $o == "w")	{
		$res = $pearDB->query("SELECT * FROM view_img WHERE img_id = '".$img_id."' LIMIT 1");
		# Set base value
		$img = array_map("myDecode", $res->fetchRow());

		# Set Directories
		$q =  "SELECT dir_id, dir_name, dir_alias, img_path FROM view_img";
		$q .= "  JOIN view_img_dir_relation ON img_id = view_img_dir_relation.img_img_id";
		$q .= "  JOIN view_img_dir ON dir_id = dir_dir_parent_id";
		$q .= "  WHERE img_id = '".$img_id."' LIMIT 1";
		$DBRESULT = $pearDB->query($q);
		$dir = $DBRESULT->fetchRow();
		$img_path = "./img/media/".$dir["dir_alias"]."/".$dir["img_path"];
		$img["directories"] = $dir["dir_name"];
		$DBRESULT->free();
	}
	
	
	/*
	 * Get Directories
	 */
	$dir_ids = getListDirectory();
	$dir_list_sel = $dir_ids;
	$dir_list_sel[0] = "";
	asort($dir_list_sel);
	
	/*
	 * Styles
	 */
	$attrsText 	= array("size"=>"35");
	$attrsAdvSelect = array("style" => "width: 200px; height: 100px;");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"80");

	/*
	 * Form begin
	 */
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a") {
		$form->addElement('header', 'title', _("Add Image(s)"));
		$form->addElement('autocomplete', 'directories', _("Existing or new directory"), $dir_ids, array('id' => 'directories'));
		$form->addElement('select', 'list_dir', "", $dir_list_sel, array('onchange' => 'document.getElementById("directories").value =  this.options[this.selectedIndex].text;'));
 		$file = $form->addElement('file', 'filename', _("Image or archive"));
		$subA = $form->addElement('submit', 'submitA', _("Save"));
	} else if ($o == "ci") {
		$form->addElement('header', 'title', _("Modify Image"));
		$form->addElement('text', 'img_name', _("Image Name"), $attrsText);
		$form->addElement('autocomplete', 'directories', _("Existing or new directory"), $dir_ids, array('id' => 'directories'));
		$list_dir = $form->addElement('select', 'list_dir', "", $dir_list_sel, array('onchange' => 'document.getElementById("directories").value =  this.options[this.selectedIndex].text;'));
		$list_dir->setSelected($dir['dir_id']);
 		$file = $form->addElement('file', 'filename', _("Image"));
		$subC = $form->addElement('submit', 'submitC', _("Save"));
		$form->setDefaults($img);
		$form->addRule('img_name', _("Compulsory image name"), 'required');
	} else if ($o == "w") {
		$form->addElement('header', 'title', _("View Image"));
		$form->addElement('text', 'img_name', _("Image Name"), $attrsText);
		$form->addElement('text', 'img_path', $img_path, NULL);
		$form->addElement('autocomplete', 'directories', _("Directory"), $dir_ids, array('id', 'directories'));
 		$file = $form->addElement('file', 'filename', _("Image"));
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=ci&img_id=".$img_id."'"));
		$form->setDefaults($img);
	}
	$form->addElement("button", "cancel", _("Cancel"), array("onClick"=>"javascript:window.location.href='?p=".$p."'"));

	$form->addElement('textarea', 'img_comment', _("Comments"), $attrsTextarea);

	$tab = array();
	$tab[] = HTML_QuickForm::createElement('radio', 'action', null, _("Return to list"), '1');
	$tab[] = HTML_QuickForm::createElement('radio', 'action', null, _("Review form after save"), '0');
	$form->addGroup($tab, 'action', _("Action"), '&nbsp;');
	$form->setDefaults(array('action'=>'1'));

	$form->addElement('hidden', 'img_id');
	$redirect = $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	/*
	 * Form Rules
	 */
	$form->applyFilter('__ALL__', 'myTrim');
	$form->addRule('directories', _("Required Field"), 'required');
	$form->setRequiredNote(_("Required Field"));

	/*
	 * watch/view
	 */
	if ($o == "w")	{
		$form->freeze(); 
	}

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	$tpl->assign("helpattr", 'TITLE, "'._("Help").'", CLOSEBTN, true, FIX, [this, 0, 5], BGCOLOR, "#ffff99", BORDERCOLOR, "orange", TITLEFONTCOLOR, "black", TITLEBGCOLOR, "orange", CLOSEBTNCOLORS, ["","black", "white", "red"], WIDTH, -300, SHADOW, true, TEXTALIGN, "justify"' );
	
	/*
	 * prepare help texts
	 */
	$helptext = "";
	include_once("help.php");
	foreach ($help as $key => $text) {
		$helptext .= '<span style="display:none" id="help:'.$key.'">'.$text.'</span>'."\n";
	}
	$tpl->assign("helptext", $helptext);

	$valid = false;
	if ($form->validate())	{
		$imgID = $form->getElement('img_id');
		$imgPath = $form->getElement('directories')->getValue();
		$imgComment = $form->getElement('img_comment')->getValue();
		if ($form->getSubmitValue("submitA")) {
			$valid = handleUpload($file, $imgPath, $imgComment);
        } else if ($form->getSubmitValue("submitC")) {
			$imgName = $form->getElement('img_name')->getValue();
			$valid = updateImg($imgID->getValue(), $file, $imgPath, $imgName, $imgComment);
		}
		$form->freeze();
        if (false === $valid) {
            $form->setElementError('filename', "An image is not uploaded.");
        }
	}
	$action = $form->getSubmitValue("action");
	
	if ($valid) {
		require_once("listImg.php");
	} else {
		
		/*
		 * Apply a template definition
		 */
		$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('max_uploader_file', ini_get("upload_max_filesize"));
		$tpl->assign('o', $o);
		$tpl->display("formImg.ihtml");
	}
?>