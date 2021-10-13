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
#
## Database retrieve information for Directory
#
$dir = array();
$list = array();
$selected = array();
/*
 * Change Directory
 */
if ($o == IMAGE_MODIFY_DIRECTORY && $directoryId) {
    $DBRESULT = $pearDB->query(
        "SELECT * FROM view_img_dir WHERE dir_id = $directoryId LIMIT 1"
    );
    $dir = array_map("myDecode", $DBRESULT->fetchRow());
    # Set Child elements
    $DBRESULT = $pearDB->query(
        "SELECT DISTINCT img_img_id FROM view_img_dir_relation "
        . "WHERE dir_dir_parent_id = $directoryId"
    );
    for ($i = 0; $imgs = $DBRESULT->fetchRow(); $i++) {
        $dir["dir_imgs"][$i] = $imgs["img_img_id"];
    }
    $DBRESULT->closeCursor();
} elseif ($o == IMAGE_MOVE) {
    $selected = array();
    if (isset($selectIds) && $selectIds) {
        $list = $selectIds;
    } elseif (isset($dir_imgs) && $dir_imgs) {
        $list = $dir_imgs;
    }

    foreach ($list as $selector => $status) {
        $ids = explode('-', $selector);
        if (count($ids)!=2) {
            continue;
        }
        $selected[] = $ids[1];
    }
}

#
## Database retrieve information for differents elements list we need on the page
#
# Images comes from DB -> Store in $imgs Array
$imgs = array();
$rq = "SELECT `img_id`,`dir_alias`,`img_name` FROM view_img "
    . "JOIN view_img_dir_relation ON img_img_id = img_id "
    . "JOIN view_img_dir ON dir_id = dir_dir_parent_id ";
if ($o == IMAGE_MOVE && count($selected) > 0) {
    $rq .= " WHERE `img_id` IN (".implode(",", $selected).") ";
}
$rq .= " ORDER BY dir_alias, img_name";
$DBRESULT = $pearDB->query($rq);
while ($img = $DBRESULT->fetchRow()) {
    $imgs[$img["img_id"]] = htmlentities(
        $img["dir_alias"]."/".$img["img_name"],
        ENT_QUOTES,
        "utf-8"
    );
}
$DBRESULT->closeCursor();

$directories = array();
$DBRESULT = $pearDB->query(
    "SELECT dir_id, dir_name, dir_comment FROM view_img_dir ORDER BY dir_name"
);
while ($row = $DBRESULT->fetchRow()) {
    $directories[$row["dir_id"]] = htmlentities(
        $row["dir_name"],
        ENT_QUOTES,
        "utf-8"
    );
}


##########################################################
# Var information to format the element
#
$attrsText = array("size"=>"30");
$attrsSelect = array("size"=>"5", "multiple"=>"1", "cols"=>"40","required"=>"true");
$attrsAdvSelect = array("style" => "width: 250px; height: 250px;");
$attrsTextarea = array("rows"=>"5", "cols"=>"40");

#
## Form begin
#
$form = new HTML_QuickFormCustom('Form', 'post', "?p=".$p);
if ($o == IMAGE_MODIFY_DIRECTORY) {
    $form->addElement('header', 'title', _("Modify directory"));
    $form->addElement('autocomplete', 'dir_name', _("Directory name"), $directories);
    $form->addElement('textarea', 'dir_comment', _("Comments"), $attrsTextarea);
    $form->setDefaults($dir);
} elseif ($o == IMAGE_MOVE) {
    $form->addElement('header', 'title', _("Move files to directory"));
    $form->addElement('autocomplete', 'dir_name', _("Destination directory"), $directories);
    $form->addElement('select', 'dir_imgs', _("Images"), $imgs, $attrsSelect);
}

$tab = array();
$tab[] = $form->createElement('radio', 'action', null, _("List"), '1');
$tab[] = $form->createElement('radio', 'action', null, _("Form"), '0');
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
if ($o == IMAGE_MODIFY_DIRECTORY && $directoryId) {
    $form->addRule('dir_name', _("Compulsory Name"), 'required');
    $form->setRequiredNote(_("Required Field"));
}


# Smarty template Init
$tpl = initSmartyTpl($path, new Smarty());

// prepare help texts
$helptext = "";
include_once("help.php");
foreach ($help as $key => $text) {
    $helptext .= '<span style="display:none" id="help:'.$key.'">'.$text.'</span>'."\n";
}
$tpl->assign("helptext", $helptext);

if ($o == IMAGE_MOVE) {
    $subM = $form->addElement('submit', 'submitM', _("Apply"));
    $res = $form->addElement(
        "button",
        "cancel",
        _("Cancel"),
        array("onClick" => "javascript:window.location.href='?p=$p'")
    );
} elseif ($o == IMAGE_MODIFY_DIRECTORY) {
    if (isset($dir['dir_imgs'])) {
        $confirm = implode(',', $dir['dir_imgs']);
    } else {
        $confirm = "";
    }
    $subC = $form->addElement('submit', 'submitC', _("Save"));
    $res = $form->addElement(
        "button",
        "cancel",
        _("Cancel"),
        array("onClick" => "javascript:window.location.href='?p=$p'")
    );
    $form->setDefaults($dir);
}

$valid = false;
if ($form->validate()) {
    if ($form->getSubmitValue("submitM")) {
        /**
         * Move files to new directory
         */
        $dir_name = $form->getSubmitValue('dir_name');
        $imgs = $form->getSubmitValue('dir_imgs');
        moveMultImg($imgs, $dir_name);
        $valid = true;
    /* modify dir */
    } elseif ($form->getSubmitValue("submitC") &&
        ($directoryId = $form->getSubmitValue('dir_id'))
    ) {
        /**
         * Update directory name
         */
        $dirName = $form->getSubmitValue('dir_name');
        $dirCmnt = $form->getSubmitValue('dir_comment');
        updateDirectory($directoryId, $dirName, $dirCmnt);
        $valid = true;
    }
}
if ($valid) {
    $o = null;
    $form->freeze();
    require_once($path."listImg.php");
} else {
    #Apply a template definition
    $renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
    $renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
    $renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
    $form->accept($renderer);
    $tpl->assign('form', $renderer->toArray());
    $tpl->assign('o', $o);
    $tpl->display("formDirectory.ihtml");
}
