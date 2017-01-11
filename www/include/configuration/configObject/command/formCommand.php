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

if (!isset($centreon)) {
    exit();
}

include_once $path . "commandType.php";

/*
 * Form Rules
 */

function myReplace()
{
    global $form;
    $ret = $form->getSubmitValues();
    return (str_replace(" ", "_", $ret["command_name"]));
}

require_once _CENTREON_PATH_ . "www/include/configuration/configObject/command/javascript/commandJs.php";

/*
 * Database retrieve information for Command
 */
$plugins_list = return_plugin($oreon->optGen["nagios_path_plugins"]);
$cmd = array();

$nbRow = "10";
$strArgDesc = "";

if (($o == "c" || $o == "w") && $command_id) {
    if (isset($lockedElements[$command_id])) {
        $o = "w";
    }
    $DBRESULT = $pearDB->query("SELECT * FROM `command` WHERE `command_id` = '".$command_id."' LIMIT 1");

    # Set base value
    $cmd = array_map("myDecodeCommand", $DBRESULT->fetchRow());

    $DBRESULT = $pearDB->query("SELECT * FROM `command_arg_description` WHERE `cmd_id` = '".$command_id."'");
    $strArgDesc = "";
    $nbRow = 0;
    while ($row = $DBRESULT->fetchRow()) {
        $strArgDesc .= $row['macro_name'] . " : " . html_entity_decode($row['macro_description']) . "\n";
        $nbRow++;
    }
}

$oCommande = new CentreonCommand($pearDB);
$aMacroDescription = $oCommande->getMacroDescription($command_id);
$sStrMcro = "";
$nbRowMacro = 0;

if (count($aMacroDescription) > 0) {
    foreach ($aMacroDescription as $macro) {
        $sStrMcro .= "MACRO (".$oCommande->aTypeMacro[$macro['type']] . ") ". $macro['name'] ." : ". $macro['description'] . "\n";
        $nbRowMacro++;
    }
} else {
    $macrosHostDesc = $oCommande->matchObject($command_id, $cmd['command_line'], '1');
    $macrosServiceDesc = $oCommande->matchObject($command_id, $cmd['command_line'], '2');
   
    $aMacroDescription = array_merge($macrosServiceDesc, $macrosHostDesc);

    foreach ($aMacroDescription as $macro) {
        $sStrMcro .= "MACRO (".$oCommande->aTypeMacro[$macro['type']] . ") ". $macro['name'] ." : ". $macro['description'] . "\n";
        $nbRowMacro++;
    }
}

/*
 * Resource Macro
 */
$resource = array();
$DBRESULT = $pearDB->query("SELECT DISTINCT `resource_name`, `resource_comment` FROM `cfg_resource` ORDER BY `resource_line`");
while ($row = $DBRESULT->fetchRow()) {
    $resource[$row["resource_name"]] = $row["resource_name"];
    if (isset($row["resource_comment"]) && $row["resource_comment"] != "") {
        $resource[$row["resource_name"]] .= " (".$row["resource_comment"].")";
    }
}
unset($row);
$DBRESULT->free();

/*
 * Connectors
 */
$connectors = array();
$DBRESULT = $pearDB->query("SELECT `id`, `name` FROM `connector` WHERE `enabled` = '1' ORDER BY `name`");
while ($row = $DBRESULT->fetchRow()) {
    $connectors[$row["id"]] = $row["name"];
}
unset($row);
$DBRESULT->free();

/*
 * Graphs Template comes from DB -> Store in $graphTpls Array
 */
$graphTpls = array(null=>null);
$DBRESULT = $pearDB->query("SELECT `graph_id`, `name` FROM `giv_graphs_template` ORDER BY `name`");
while ($graphTpl = $DBRESULT->fetchRow()) {
    $graphTpls[$graphTpl["graph_id"]] = $graphTpl["name"];
}
unset($graphTpl);
$DBRESULT->free();

/*
 * Nagios Macro
 */
$macros = array();
$DBRESULT = $pearDB->query("SELECT `macro_name` FROM `nagios_macro` ORDER BY `macro_name`");
while ($row = $DBRESULT->fetchRow()) {
    $macros[$row["macro_name"]] = $row["macro_name"];
}
unset($row);
$DBRESULT->free();

$attrsText      = array("size"=>"35");
$attrsTextarea  = array("rows"=>"9", "cols"=>"80", "id"=>"command_line");
$attrsTextarea2 = array("rows"=>"$nbRow", "cols"=>"100", "id"=>"listOfArg");
$attrsTextarea3 = array("rows"=>"5", "cols"=>"50", "id"=>"command_comment");
$attrsTextarea4 = array("rows"=>"$nbRowMacro", "cols"=>"100", "id"=>"listOfMacros");

/*
 * Form begin
 */
$form = new HTML_QuickForm('Form', 'post', "?p=".$p.'&type='.$type);
if ($o == "a") {
    $form->addElement('header', 'title', _("Add a Command"));
} elseif ($o == "c") {
    $form->addElement('header', 'title', _("Modify a Command"));
} elseif ($o == "w") {
    $form->addElement('header', 'title', _("View a Command"));
}

/*
 * Command information
 */
if (isset($tabCommandType[$type])) {
    $form->addElement('header', 'information', $tabCommandType[$type]);
} else {
    $form->addElement('header', 'information', _("Information"));
}

$form->addElement('header', 'furtherInfos', _("Additional Information"));

foreach ($tabCommandType as $id => $name) {
    $cmdType[] = HTML_QuickForm::createElement('radio', 'command_type', null, $name, $id, 'onChange=checkType(this.value);');
}

$form->addGroup($cmdType, 'command_type', _("Command Type"), '&nbsp;&nbsp;');

if (isset($type) && $type != "") {
    $form->setDefaults(array('command_type' => $type));
} else {
    $form->setDefaults(array('command_type' => '2'));
}

if (isset($cmd['connector_id']) && is_numeric($cmd['connector_id'])) {
    $form->setDefaults(array('connectors' => $cmd['connector_id']));
} else {
    $form->setDefaults(array('connectors' => ""));
}

$form->addElement('text', 'command_name', _("Command Name"), $attrsText);
$form->addElement('text', 'command_example', _("Argument Example"), $attrsText);
$form->addElement('text', 'command_hostaddress', _("\$HOSTADDRESS\$"), $attrsText);
$form->addElement('textarea', 'command_line', _("Command Line"), $attrsTextarea);
$form->addElement('checkbox', 'enable_shell', _("Enable shell"), null, $attrsText);

$form->addElement('textarea', 'listOfArg', _("Argument Descriptions"), $attrsTextarea2)->setAttribute("readonly");
$form->addElement('select', 'graph_id', _("Graph template"), $graphTpls);
$form->addElement('button', 'desc_arg', _("Describe arguments"), array("onClick"=>"goPopup();"));
$form->addElement('button', 'clear_arg', _("Clear arguments"), array("onClick"=>"clearArgs();"));
$form->addElement('textarea', 'command_comment', _("Comment"), $attrsTextarea2);
$form->addElement('button', 'desc_macro', _("Describe macros"), array("onClick"=>"manageMacros();"));
$form->addElement('textarea', 'listOfMacros', _("Macros Descriptions"), $attrsTextarea4)->setAttribute("readonly");

$cmdActivation[] = HTML_QuickForm::createElement('radio', 'command_activate', null, _("Enabled"), '1');
$cmdActivation[] = HTML_QuickForm::createElement('radio', 'command_activate', null, _("Disabled"), '0');
$form->addGroup($cmdActivation, 'command_activate', _("Status"), '&nbsp;');
$form->setDefaults(array('command_activate' => '1'));

$form->setDefaults(array("listOfArg" => $strArgDesc));
$form->setDefaults(array("listOfMacros" => $sStrMcro));

$connectors[null] = _("Select a connector...");
$form->addElement('select', 'resource', null, $resource);
$form->addElement('select', 'connectors', _("Connectors"), $connectors);
$form->addElement('select', 'macros', null, $macros);

ksort($plugins_list);
$form->addElement('select', 'plugins', null, $plugins_list);

/*
 * Further informations
 */
$form->addElement('hidden', 'command_id');
$redirectType = $form->addElement('hidden', 'type');
$redirectType->setValue($type);
$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);

$form->applyFilter('__ALL__', 'myTrim');
$form->applyFilter('command_name', 'myReplace');
$form->applyFilter('__ALL__', 'myTrim');
$form->addRule('command_name', _("Compulsory Name"), 'required');
$form->addRule('command_line', _("Compulsory Command Line"), 'required');
$form->registerRule('exist', 'callback', 'testCmdExistence');
$form->addRule('command_name', _("Name is already in use"), 'exist');
$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;". _("Required fields"));

/*
 * Smarty template Init
 */
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

/*
 * Just watch a Command information
 */
if ($o == "w") {
    if ($centreon->user->access->page($p) != 2 && !isset($lockedElements[$command_id])) {
        $form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&command_id=".$command_id."&type=".$type."'"));
    }
    $form->setDefaults($cmd);
    $form->freeze();
} elseif ($o == "c") {
    /*
     * Modify a Command information
     */
    $subC = $form->addElement('submit', 'submitC', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
    $form->setDefaults($cmd);
} elseif ($o == "a") {
    /*
     * Add a Command information
     */
    $subA = $form->addElement('submit', 'submitA', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
}

$tpl->assign('msg', array ("comment"=>_("Commands definitions can contain Macros but they have to be valid.")));
$tpl->assign('cmd_help', _("Plugin Help"));
$tpl->assign('cmd_play', _("Test the plugin"));

$valid = false;
if ($form->validate()) {
    $cmdObj = $form->getElement('command_id');
    if ($form->getSubmitValue("submitA")) {
        $cmdObj->setValue(insertCommandInDB());
    } elseif ($form->getSubmitValue("submitC")) {
        updateCommandInDB($cmdObj->getValue());
    }
    
    $o = null;
    $cmdObj = $form->getElement('command_id');
    $valid = true;
}

?><script type='text/javascript'>

function insertValueQuery(elem) {
    var myQuery = document.Form.command_line;
        if(elem == 1)   {
                var myListBox = document.Form.resource;
        } else if (elem == 2)   {
                var myListBox = document.Form.plugins;
        } else if (elem == 3)   {
                var myListBox = document.Form.macros;
        }
    if (myListBox.options.length > 0) {
        var chaineAj = '';
        var NbSelect = 0;
        for(var i=0; i<myListBox.options.length; i++) {
            if (myListBox.options[i].selected){
                NbSelect++;
                if (NbSelect > 1)
                    chaineAj += ', ';
                chaineAj += myListBox.options[i].value;
            }
        }

        if (document.selection) {
                // IE support
            myQuery.focus();
            sel = document.selection.createRange();
            sel.text = chaineAj;
            document.Form.insert.focus();
        } else if (document.Form.command_line.selectionStart || document.Form.command_line.selectionStart == '0') {
                // MOZILLA/NETSCAPE support
            var startPos = document.Form.command_line.selectionStart;
            var endPos = document.Form.command_line.selectionEnd;
            var chaineSql = document.Form.command_line.value;
            myQuery.value = chaineSql.substring(0, startPos) + chaineAj + chaineSql.substring(endPos, chaineSql.length);
        } else {
            myQuery.value += chaineAj;
        }
    }
}

</script><?php

if ($valid) {
    require_once($path."listCommand.php");
} else {
    /*
     * Apply a template definition
     */
    $renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
    $renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
    $renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
    $form->accept($renderer);
    $tpl->assign('form', $renderer->toArray());
    $tpl->assign('o', $o);
    $tpl->assign('arg_desc_label', _("Argument Descriptions"));
    $tpl->assign('macro_desc_label', _("Macros Descriptions"));
    $tpl->display("formCommand.ihtml");
}
