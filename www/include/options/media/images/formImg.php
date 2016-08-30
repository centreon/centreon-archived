<?php
/*
 * Copyright 2005-2015 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
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

if (!isset($centreon)) {
    exit();
}

/*
 * Database retrieve information
 */
$img = array("img_path"=>null);
if ($o == "ci" || $o == "w") {
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
$attrsText    = array("size"=>"35");
$attrsAdvSelect = array("style" => "width: 200px; height: 100px;");
$attrsTextarea    = array("rows"=>"5", "cols"=>"80");

/*
 * Form begin
 */
$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
if ($o == "a") {
    $form->addElement('header', 'title', _("Add Image(s)"));
    $form->addElement(
        'autocomplete',
        'directories',
        _("Existing or new directory"),
        $dir_ids,
        array('id' => 'directories')
    );
    $form->addElement(
        'select',
        'list_dir',
        "",
        $dir_list_sel,
        array('onchange' => 'document.getElementById("directories").value =  this.options[this.selectedIndex].text;')
    );
    $file = $form->addElement('file', 'filename', _("Image or archive"));
    $subA = $form->addElement('submit', 'submitA', _("Save"), array("class" => "btc bt_success"));
} elseif ($o == "ci") {
    $form->addElement('header', 'title', _("Modify Image"));
    $form->addElement('text', 'img_name', _("Image Name"), $attrsText);
    $form->addElement(
        'autocomplete',
        'directories',
        _("Existing or new directory"),
        $dir_ids,
        array('id' => 'directories')
    );
    $list_dir = $form->addElement(
        'select',
        'list_dir',
        "",
        $dir_list_sel,
        array('onchange' => 'document.getElementById("directories").value =  this.options[this.selectedIndex].text;')
    );
    $list_dir->setSelected($dir['dir_id']);
    $file = $form->addElement('file', 'filename', _("Image"));
    $subC = $form->addElement('submit', 'submitC', _("Save"), array("class" => "btc bt_success"));
    $form->setDefaults($img);
    $form->addRule('img_name', _("Compulsory image name"), 'required');
} elseif ($o == "w") {
    $form->addElement('header', 'title', _("View Image"));
    $form->addElement('text', 'img_name', _("Image Name"), $attrsText);
    $form->addElement('text', 'img_path', $img_path, null);
    $form->addElement('autocomplete', 'directories', _("Directory"), $dir_ids, array('id', 'directories'));
    $file = $form->addElement('file', 'filename', _("Image"));
    $form->addElement(
        "button",
        "change",
        _("Modify"),
        array(
            "onClick" => "javascript:window.location.href='?p=" . $p . "&o=ci&img_id=" . $img_id . "'"
        ),
        array("class" => "btc bt_success")
    );
    $form->setDefaults($img);
}
$form->addElement(
    "button",
    "cancel",
    _("Cancel"),
    array(
        "onClick" => "javascript:window.location.href='?p=" . $p . "'"
    ),
    array("class" => "btc bt_default")
);

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
if ($o == "w") {
    $form->freeze();
}

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

$tpl->assign(
    "helpattr",
    'TITLE, "' . _("Help") . '", CLOSEBTN, true, FIX, [this, 0, 5], '
    . 'BGCOLOR, "#ffff99", BORDERCOLOR, "orange", TITLEFONTCOLOR, '
    . '"black", TITLEBGCOLOR, "orange", CLOSEBTNCOLORS, ["","black", '
    . '"white", "red"], WIDTH, -300, SHADOW, true, TEXTALIGN, "justify"'
);

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
if ($form->validate()) {
    $imgID = $form->getElement('img_id');
    $imgPath = $form->getElement('directories')->getValue();
    $imgComment = $form->getElement('img_comment')->getValue();
    if ($form->getSubmitValue("submitA")) {
        $valid = handleUpload($file, $imgPath, $imgComment);
    } elseif ($form->getSubmitValue("submitC")) {
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
