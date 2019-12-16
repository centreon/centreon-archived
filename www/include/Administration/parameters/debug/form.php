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

$DBRESULT = $pearDB->query("SELECT * FROM `options`");

while ($opt = $DBRESULT->fetchRow()) {
    $gopt[$opt["key"]] = myDecode($opt["value"]);
}
$DBRESULT->closeCursor();

$attrsText        = array("size"=>"40");
$attrsText2        = array("size"=>"5");
$attrsAdvSelect = null;

$form = new HTML_QuickFormCustom('Form', 'post', "?p=".$p);
$form->addElement('header', 'title', _("Modify General Options"));
$form->addElement('header', 'debug', _("Debug"));

$form->addElement('text', 'debug_path', _("Logs Directory"), $attrsText);

$form->addElement('checkbox', 'debug_auth', _("Authentication debug"));
$form->addElement('checkbox', 'debug_sql', _("SQL debug"));
$form->addElement('checkbox', 'debug_nagios_import', _("Monitoring Engine Import debug"));
$form->addElement('checkbox', 'debug_rrdtool', _("RRDTool debug"));
$form->addElement('checkbox', 'debug_ldap_import', _("LDAP User Import debug"));
$form->addElement('checkbox', 'debug_gorgone', _("Centreon Gorgone debug"));
$form->addElement('checkbox', 'debug_centreontrapd', _("Centreontrapd debug"));

$form->applyFilter('__ALL__', 'myTrim');
$form->applyFilter('debug_path', 'slash');

$form->registerRule('is_valid_path', 'callback', 'is_valid_path');
$form->registerRule('is_readable_path', 'callback', 'is_readable_path');
$form->registerRule('is_executable_binary', 'callback', 'is_executable_binary');
$form->registerRule('is_writable_path', 'callback', 'is_writable_path');
$form->registerRule('is_writable_file', 'callback', 'is_writable_file');
$form->registerRule('is_writable_file_if_exist', 'callback', 'is_writable_file_if_exist');

$form->addRule('debug_path', _("Can't write in directory"), 'is_writable_path');

$form->addElement('hidden', 'gopt_id');
$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path.'debug/', $tpl);

$form->setDefaults($gopt);

$subC = $form->addElement('submit', 'submitC', _("Save"), array("class" => "btc bt_success"));
$DBRESULT = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));

$valid = false;

if ($form->validate()) {
    /*
     * Update in DB
     */
    updateDebugConfigData($form->getSubmitValue("gopt_id"));
    /*
     * Update in Oreon Object
     */
    $oreon->initOptGen($pearDB);

    $o = null;
    $valid = true;
    $form->freeze();

    if (isset($_POST["debug_auth_clear"])) {
        @unlink($oreon->optGen["debug_path"]."auth.log");
    }

    if (isset($_POST["debug_nagios_import_clear"])) {
        @unlink($oreon->optGen["debug_path"]."cfgimport.log");
    }

    if (isset($_POST["debug_rrdtool_clear"])) {
        @unlink($oreon->optGen["debug_path"]."rrdtool.log");
    }

    if (isset($_POST["debug_ldap_import_clear"])) {
        @unlink($oreon->optGen["debug_path"]."ldapsearch.log");
    }

    if (isset($_POST["debug_inventory_clear"])) {
        @unlink($oreon->optGen["debug_path"]."inventory.log");
    }
}

if (!$form->validate() && isset($_POST["gopt_id"])) {
    print("<div class='msg' align='center'>"._("Impossible to validate, one or more field is incorrect")."</div>");
}

$form->addElement(
    "button",
    "change",
    _("Modify"),
    array("onClick"=>"javascript:window.location.href='?p=".$p."&o=debug'", 'class' => 'btc bt_info')
);

// prepare help texts
$helptext = "";
include_once("help.php");
foreach ($help as $key => $text) {
    $helptext .= '<span style="display:none" id="help:'.$key.'">'.$text.'</span>'."\n";
}
$tpl->assign("helptext", $helptext);

$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->assign('o', $o);
$tpl->assign("genOpt_debug_options", _("Debug Properties"));
$tpl->assign('valid', $valid);

$tpl->display("form.ihtml");
