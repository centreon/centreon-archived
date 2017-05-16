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

if (!isset($oreon)) {
    exit();
}

$DBRESULT = $pearDB->query("SELECT * FROM `options` WHERE options.key LIKE 'kb_%'");
while ($opt = $DBRESULT->fetchRow()) {
    $gopt[$opt["key"]] = myDecode($opt["value"]);
}
$DBRESULT->free();

$attrsAdvSelect = null;

/*
 * Form begin
 */
$form = new HTML_QuickForm('Form', 'post', "?p=" . $p);

/*
 * Knowledge base form
 */
$form->addElement('text', 'kb_db_name', _("Knowledge base database name"));
$form->addRule('kb_db_name', _("Mandatory field"), 'required');
$form->addElement('text', 'kb_db_user', _("Knowledge base database user"));
$form->addElement('password', 'kb_db_password', _("Knowledge base Database password"));
$form->addElement('text', 'kb_db_host', _("Knowledge base Database host"));
$form->addRule('kb_db_host', _("Mandatory field"), 'required');
$form->addElement(
    'button',
    'test_connection',
    _("Test DB connection"),
    array("class" => "btc bt_success", "onClick"=>"javascript:checkWikiConnection()")
);
$form->addElement('text', 'kb_db_prefix', _("Knowledge base Database prefix"));
$form->addElement('text', 'kb_wiki_url', _("Knowledge base url"));
$form->addRule('kb_wiki_url', _("Mandatory field"), 'required');
$form->addElement('text', 'kb_wiki_account', _("Knowledge wiki account (with delete right)"));
$form->addRule('kb_wiki_account', _("Mandatory field"), 'required');
$form->addElement('password', 'kb_wiki_password', _("Knowledge wiki account password"));
$form->addRule('kb_wiki_password', _("Mandatory field"), 'required');

$form->addElement('hidden', 'gopt_id');
$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);

$form->applyFilter('__ALL__', 'myTrim');

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path . "/knowledgeBase", $tpl);

$form->setDefaults($gopt);

$subC = $form->addElement('submit', 'submitC', _("Save"), array("class" => "btc bt_success"));
$DBRESULT = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));

// prepare help texts
$helptext = "";
include_once("help.php");
foreach ($help as $key => $text) {
    $helptext .= '<span style="display:none" id="help:' . $key . '">' . $text . '</span>' . "\n";
}
$tpl->assign("helptext", $helptext);

$valid = false;
if ($form->validate()) {
    /*
     * Update in DB
     */
    updateKnowledgeBaseData($pearDB, $form, $oreon);

    $o = null;
    $valid = true;

    $form->freeze();
}
if (!$form->validate() && isset($_POST["gopt_id"])) {
    print("<div class='msg' align='center'>" . _("impossible to validate, one or more field is incorrect") . "</div>");
}

$form->addElement(
    "button",
    "change",
    _("Modify"),
    array("onClick" => "javascript:window.location.href='?p=" . $p . "&o=knowledgeBase'", 'class' => 'btc bt_info')
);

/*
 * Apply a template definition
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->assign('o', $o);
$tpl->assign('valid', $valid);

$tpl->display("formKnowledgeBase.html"); 
