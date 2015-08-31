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

if (!isset($oreon)) {
    exit();
}

if (!$oreon->user->admin && isset($_GET['id'])
    && count($allowedBrokerConf) && !isset($allowedBrokerConf[$_GET['id']])) {
    $msg = new CentreonMsg();
    $msg->setImage("./img/icones/16x16/warning.gif");
    $msg->setTextStyle("bold");
    $msg->setText(_('You are not allowed to access this object configuration'));
    return null;
}

$cbObj = new CentreonConfigCentreonBroker($pearDB);

/*
 * nagios servers comes from DB
 */
$nagios_servers = array();
$serverAcl = "";
if (!$oreon->user->admin && $serverString != "''") {
    $serverAcl = " WHERE id IN ($serverString) ";
}
$DBRESULT = $pearDB->query("SELECT * FROM nagios_server $serverAcl ORDER BY name");
while($nagios_server = $DBRESULT->fetchRow()) {
    $nagios_servers[$nagios_server["id"]] = $nagios_server["name"];
}
$DBRESULT->free();

/*
 * Var information to format the element
 */
$attrsText      = array("size"=>"120");
$attrsText2     = array("size"=>"50");
$attrsText3     = array("size"=>"10");
$attrsTextarea  = array("rows"=>"5", "cols"=>"40");

/*
 * Form begin
 */
$form = new HTML_QuickForm('Form', 'post', "?p=".$p, '', array('onsubmit' => 'return formValidate()'));
if ($o == "a") {
    $form->addElement('header', 'title', _("Add a Centreon-Broker Configuration"));
} else if ($o == "c") {
    $form->addElement('header', 'title', _("Modify a Centreon-Broker Configuration"));
} else if ($o == "w") {
    $form->addElement('header', 'title', _("View a Centreon-Broker Configuration"));
}

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

/*
 * TAB 1 - General informations
 */
$tpl->assign('centreonbroker_configuration', _("Centreon Broker information"));
$form->addElement('header', 'information', _("Centreon Broker configuration"));
$form->addElement('text', 'name', _("Name"), $attrsText);
$form->addElement('text', 'filename', _("Config file name"), $attrsText);
$form->addElement('select', 'ns_nagios_server', _("Requester"), $nagios_servers);

$form->addElement('text', 'event_queue_max_size', _('Event queue max size'), $attrsText);
$command = $form->addElement('text', 'command_file', _('Command file'), $attrText);
$command->freeze();

$timestamp = array();
$timestamp[] = HTML_QuickForm::createElement('radio', 'write_timestamp', null, _("Yes"), 1);
$timestamp[] = HTML_QuickForm::createElement('radio', 'write_timestamp', null, _("No"), 0);
$form->addGroup($timestamp, 'write_timestamp', _("Write timestamp"), '&nbsp;');

$thread_id = array();
$thread_id[] = HTML_QuickForm::createElement('radio', 'write_thread_id', null, _("Yes"), 1);
$thread_id[] = HTML_QuickForm::createElement('radio', 'write_thread_id', null, _("No"), 0);
$form->addGroup($thread_id, 'write_thread_id', _("Write thread id"), '&nbsp;');
    
$status = array();
$status[] = HTML_QuickForm::createElement('radio', 'activate', null, _("Enabled"), 1);
$status[] = HTML_QuickForm::createElement('radio', 'activate', null, _("Disabled"), 0);
$form->addGroup($status, 'activate', _("Status"), '&nbsp;');

$tags = $cbObj->getTags();

$tabs = array();
foreach ($tags as $tagId => $tag) {
    $tabs[] = array('id' => $tag,
                    'name' => _("Centreon-Broker " . ucfirst($tag)),
                    'link' => _("Add"),
                    'nb' => 0,
                    'blocks' => $cbObj->getListConfigBlock($tagId),
                    'forms' => array());
}

/*
 * Default values
 */
if (isset($_GET["o"]) && $_GET["o"] == 'a'){
    $form->setDefaults(array(
        "name" => '',
        "write_timestamp" => '1',
        "write_thread_id" => '1',
        "activate" => '1'
    ));
    $tpl->assign('config_id', 0);
} else {
    if (isset($_GET['id']) && $_GET['id'] != 0) {
        $id = $_GET['id'];
        $tpl->assign('config_id', $id);
        $form->setDefaults(getCentreonBrokerInformation($id));
        /*
         * Get informations for modify
         */
        textdomain("help");
        $nbTabs = count($tabs);
        for ($i = 0; $i < $nbTabs; $i++) {
            $tabs[$i]['forms'] = $cbObj->getForms($id, $tabs[$i]['id'], $p, $tpl);
            $tabs[$i]['helps'] = $cbObj->getHelps($id, $tabs[$i]['id']);
            $tabs[$i]['nb'] = count($tabs[$i]['forms']);
        }
        textdomain("messages");
    }
}
$form->addElement('hidden', 'id');
$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);

/*
 * Form Rules
 */
$form->registerRule('exist', 'callback', 'testExistence');
$form->addRule('name', _("Mandatory name"), 'required');
$form->addRule('name', _("Name is already in use"), 'exist');
$form->addRule('filename', _("Mandatory filename"), 'required');
$form->addRule('event_queue_max_size', _('Value must be numeric'), 'numeric');

if ($o == "w")  {
    if ($centreon->user->access->page($p) != 2) {
        $form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&id=".$ndo2db_id."'"));
    }
    $form->freeze();
 } else if ($o == "c")   {
    /*
     * Modify a Centreon Broker information
     */
    $subC = $form->addElement('submit', 'submitC', _("Save"));
    $res = $form->addElement('reset', 'reset', _("Reset"));
 } else if ($o == "a")   {
    /*
     * Add a nagios information
     */
    $subA = $form->addElement('submit', 'submitA', _("Save"));
    $res = $form->addElement('reset', 'reset', _("Reset"));
 }

$valid = false;
if ($form->validate())  {
    $nagiosObj = $form->getElement('id');
    if ($form->getSubmitValue("submitA")) {
        $cbObj->insertConfig($_POST);
    } else if ($form->getSubmitValue("submitC")) {
        $cbObj->updateConfig($_POST['id'], $_POST);
    }
    $o = NULL;
    $valid = true;
}
if ($valid) {
    require_once($path."listCentreonBroker.php");
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
    $tpl->assign('p', $p);
    $tpl->assign('sort1', _("General"));
    $tpl->assign('tabs', $tabs);
    $tpl->display("formCentreonBroker.ihtml");
}
?>
