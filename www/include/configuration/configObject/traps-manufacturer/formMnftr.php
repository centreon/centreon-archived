<?php

/*
 * Copyright 2005-2021 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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
 */

#
## Database retrieve information for Manufacturer
#

function myDecodeMnftr($arg)
{
    $arg = html_entity_decode($arg, ENT_QUOTES, "UTF-8");
    return ($arg);
}

$mnftr = array();
if (($o === "c" || $o === "w") && $id) {
    $statement = $pearDB->prepare("SELECT * FROM traps_vendor WHERE id = :id LIMIT 1");
    # Set base value
    $statement->bindValue(':id', $id, \PDO::PARAM_INT);
    $statement->execute();
    $mnftr = array_map("myDecodeMnftr", $statement->fetchRow());
    $statement->closeCursor();
}

##########################################################
# Var information to format the element
#
$attrsText = array("size" => "50");
$attrsTextarea = array("rows" => "5", "cols" => "40");
#
## Form begin
#
$form = new HTML_QuickFormCustom('Form', 'post', "?p=" . $p);
if ($o == "a") {
    $form->addElement('header', 'title', _("Add Vendor"));
} elseif ($o == "c") {
    $form->addElement('header', 'title', _("Modify Vendor"));
} elseif ($o == "w") {
    $form->addElement('header', 'title', _("View Vendor"));
}

#
## Manufacturer information
#
$form->addElement('text', 'name', _("Vendor Name"), $attrsText);
$form->addElement('text', 'alias', _("Alias"), $attrsText);
$form->addElement('textarea', 'description', _("Description"), $attrsTextarea);

#
## Further informations
#
$form->addElement('hidden', 'id');
$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);

#
## Form Rules
#
function myReplace()
{
    global $form;
    return (str_replace(" ", "_", $form->getSubmitValue("name")));
}

$form->applyFilter('__ALL__', 'myTrim');
$form->applyFilter('name', 'myReplace');
$form->addRule('name', _("Compulsory Name"), 'required');
$form->addRule('alias', _("Compulsory Name"), 'required');
$form->registerRule('exist', 'callback', 'testMnftrExistence');
$form->addRule('name', _("Name is already in use"), 'exist');
$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;" . _("Required fields"));

#
##End of form definition
#

# Smarty template Init
$tpl = new Smarty();
$tpl = initSmartyTpl(__DIR__, $tpl);
$tpl->assign(
    "helpattr",
    'TITLE, "' . _("Help") . '", CLOSEBTN, true, FIX, [this, 0, 5], BGCOLOR, "#ffff99", BORDERCOLOR, "orange", ' .
    'TITLEFONTCOLOR, "black", TITLEBGCOLOR, "orange", CLOSEBTNCOLORS, ["","black", "white", "red"], WIDTH, -300, ' .
    'SHADOW, true, TEXTALIGN, "justify"'
);

# prepare help texts
$helptext = "";
include_once("help.php");
foreach ($help as $key => $text) {
    $helptext .= '<span style="display:none" id="help:' . $key . '">' . $text . '</span>' . "\n";
}
$tpl->assign("helptext", $helptext);

# Just watch a Command information
if ($o == "w") {
    if ($centreon->user->access->page($p) != 2) {
        $form->addElement(
            "button",
            "change",
            _("Modify"),
            array("onClick" => "javascript:window.location.href='?p=" . $p . "&o=c&id=" . $id . "'")
        );
    }
    $form->setDefaults($mnftr);
    $form->freeze();
} # Modify a Command information
elseif ($o == "c") {
    $subC = $form->addElement('submit', 'submitC', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
    $form->setDefaults($mnftr);
} # Add a Command information
elseif ($o == "a") {
    $subA = $form->addElement('submit', 'submitA', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
}

$valid = false;
if ($form->validate()) {
    $mnftrObj = $form->getElement('id');
    if ($form->getSubmitValue("submitA")) {
        $mnftrObj->setValue(insertMnftrInDB());
    } elseif ($form->getSubmitValue("submitC")) {
        updateMnftrInDB($mnftrObj->getValue());
    }
    $o = null;
    $valid = true;
}

if ($valid) {
    require_once(__DIR__ . "/listMnftr.php");
} else {
    ##Apply a template definition
    $renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
    $renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
    $renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
    $form->accept($renderer);
    $tpl->assign('form', $renderer->toArray());
    $tpl->assign('o', $o);
    $tpl->display("formMnftr.ihtml");
}
