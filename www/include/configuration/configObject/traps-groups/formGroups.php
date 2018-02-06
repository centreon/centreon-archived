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
 */

#
## Database retrieve information for Manufacturer
#

function myDecodeGroup($arg)
{
    $arg = html_entity_decode($arg, ENT_QUOTES, "UTF-8");
    return($arg);
}

$group = array();
if (($o == "c" || $o == "w") && $id) {
    $DBRESULT = $pearDB->query("SELECT traps_group_name as name, traps_group_id as id FROM traps_group WHERE traps_group_id = '" . $pearDB->escape($id) . "' LIMIT 1");
    # Set base value
    $group = array_map("myDecodeGroup", $DBRESULT->fetchRow());
    $DBRESULT->free();
}

##########################################################
# Var information to format the element
#
$attrsText      = array("size"=>"50");
$attrsTextarea  = array("rows"=>"5", "cols"=>"40");
#
## Form begin
#
$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
if ($o == "a") {
    $form->addElement('header', 'title', _("Add Group"));
} elseif ($o == "c") {
    $form->addElement('header', 'title', _("Modify Group"));
} elseif ($o == "w") {
    $form->addElement('header', 'title', _("View Group"));
}

#
## Group information
#
$form->addElement('text', 'name', _("Name"), $attrsText);

$attrTraps = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_trap&action=list',
    'multiple' => true,
    'linkedObject' => 'centreonTraps',
    'defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_trap&action=defaultValues&target=Traps&field=groups&id=' . $id,
);
$form->addElement('select2', 'traps', _("Traps"), array(), $attrTraps);

#
## Further informations
#
$form->addElement('hidden', 'id');
$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);

#
## Form Rules
#
$form->applyFilter('__ALL__', 'myTrim');
$form->addRule('name', _("Compulsory Name"), 'required');
$form->registerRule('exist', 'callback', 'testTrapGroupExistence');
$form->addRule('name', _("Name is already in use"), 'exist');
$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;". _("Required fields"));

#
##End of form definition
#

# Smarty template Init
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);
$tpl->assign("helpattr", 'TITLE, "'._("Help").'", CLOSEBTN, true, FIX, [this, 0, 5], BGCOLOR, "#ffff99", BORDERCOLOR, "orange", TITLEFONTCOLOR, "black", TITLEBGCOLOR, "orange", CLOSEBTNCOLORS, ["","black", "white", "red"], WIDTH, -300, SHADOW, true, TEXTALIGN, "justify"');

# prepare help texts
$helptext = "";
include_once("help.php");
foreach ($help as $key => $text) {
    $helptext .= '<span style="display:none" id="help:'.$key.'">'.$text.'</span>'."\n";
}
$tpl->assign("helptext", $helptext);

# Just watch a Trap Group information
if ($o == "w") {
    if ($centreon->user->access->page($p) != 2) {
        $form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&id=".$id."'"));
    }
    $form->setDefaults($group);
    $form->freeze();
} # Modify a Trap Group information
elseif ($o == "c") {
    $subC = $form->addElement('submit', 'submitC', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
    $form->setDefaults($group);
} # Add a Trap Group information
elseif ($o == "a") {
    $subA = $form->addElement('submit', 'submitA', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
}

$valid = false;
if ($form->validate()) {
    $trapGroupObj = $form->getElement('id');
    if ($form->getSubmitValue("submitA")) {
        $trapGroupObj->setValue(insertTrapGroupInDB());
    } elseif ($form->getSubmitValue("submitC")) {
        updateTrapGroupInDB($trapGroupObj->getValue());
    }
    $o = null;
    $valid = true;
}

if ($valid) {
    require_once($path."listGroups.php");
} else {
    ##Apply a template definition
    $renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
    $renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
    $renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
    $form->accept($renderer);
    $tpl->assign('form', $renderer->toArray());
    $tpl->assign('o', $o);
    $tpl->display("formGroups.ihtml");
}
