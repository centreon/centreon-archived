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

if (!isset($oreon))
    exit();

$DBRESULT = $pearDB->query("SELECT * FROM `options`");
while ($opt = $DBRESULT->fetchRow()) {
    $gopt[$opt["key"]] = myDecode($opt["value"]);
}
$DBRESULT->free();

$attrsText = array("size" => "40");
$attrsText2 = array("size" => "5");
$attrsAdvSelect = null;

/*
 * Form begin
 */
$form = new HTML_QuickForm('Form', 'post', "?p=" . $p);
$form->addElement('header', 'title', _("Modify Centcore options"));

/*
 * Centcore Options
 */
$form->addElement('checkbox', 'enable_perfdata_sync', _("Enable Perfdata Synchronisation"));
$form->addElement('checkbox', 'enable_logs_sync', _("Enable Logs Synchronisation"));
$form->addElement('checkbox', 'enable_broker_stats', _("Enable Broker Statistics Collection"));
$form->addElement('text', 'centcore_cmd_timeout', _("Timeout value for Centcore commands"), $attrsText2);
$form->addRule('centcore_cmd_timeout', _('Must be a number'), 'numeric');

$form->addElement('hidden', 'gopt_id');
$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);

$form->applyFilter('__ALL__', 'myTrim');

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path . "/centcore", $tpl);

$form->setDefaults($gopt);

$subC = $form->addElement('submit', 'submitC', _("Save"));
$DBRESULT = $form->addElement('reset', 'reset', _("Reset"));

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
    updateCentcoreConfigData($pearDB, $form, $oreon);

    $o = NULL;
    $valid = true;
    $form->freeze();
}
if (!$form->validate() && isset($_POST["gopt_id"])) {
    print("<div class='msg' align='center'>" . _("impossible to validate, one or more field is incorrect") . "</div>");
}

$form->addElement("button", "change", _("Modify"), array("onClick" => "javascript:window.location.href='?p=" . $p . "&o=centcore'"));

/*
 * Apply a template definition
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->assign('o', $o);
$tpl->assign("centcore_properties", _("Centcore properties"));
$tpl->assign("centcore_options", _("Centcore Options"));
$tpl->assign('valid', $valid);
$tpl->display("centcore.ihtml");
?>