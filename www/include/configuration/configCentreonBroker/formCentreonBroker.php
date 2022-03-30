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

$id = filter_var(
    $_REQUEST['id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['default' => 0]]
);

if (
    !$centreon->user->admin
    && $id !== 0
    && count($allowedBrokerConf)
    && !isset($allowedBrokerConf[$id])
) {
    $msg = new CentreonMsg();
    $msg->setImage("./img/icons/warning.png");
    $msg->setTextStyle("bold");
    $msg->setText(_('You are not allowed to access this object configuration'));
    return null;
}

$cbObj = new CentreonConfigCentreonBroker($pearDB);

/**
 * @param array<string,mixed> $data
 * @return array<string,mixed>
 */
function htmlEncodeBrokerInformation(array $data): array
{
    $data['name'] = htmlentities($data['name']);
    $data['filename'] = htmlentities($data['filename']);
    $data['cache_directory'] = htmlentities($data['cache_directory']);
    $data['log_directory'] = htmlentities($data['log_directory']);
    $data['log_filename'] = htmlentities($data['log_filename']);
    $data['bbdo_version'] = htmlentities($data['bbdo_version']);
    $data['command_file'] = htmlentities($data['command_file']);

    return $data;
}

/**
 * @param array<string,mixed> $data
 * @return array<string,mixed>
 */
function htmlDecodeBrokerInformation(array $data): array
{
    $data['name'] = html_entity_decode($data['name']);
    $data['filename'] = html_entity_decode($data['filename']);
    $data['cache_directory'] = html_entity_decode($data['cache_directory']);
    $data['log_directory'] = html_entity_decode($data['log_directory']);
    $data['log_filename'] = html_entity_decode($data['log_filename']);
    $data['bbdo_version'] = html_entity_decode($data['bbdo_version']);
    $data['command_file'] = html_entity_decode($data['command_file']);

    return $data;
}

/*
 * nagios servers comes from DB
 */
$nagios_servers = array();
$serverAcl = "";
if (!$centreon->user->admin && $serverString != "''") {
    $serverAcl = " WHERE id IN ($serverString) ";
}
$DBRESULT = $pearDB->query("SELECT * FROM nagios_server $serverAcl ORDER BY name");
while ($nagios_server = $DBRESULT->fetchRow()) {
    $nagios_servers[$nagios_server["id"]] = $nagios_server["name"];
}
$DBRESULT->closeCursor();

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
$form = new HTML_QuickFormCustom('Form', 'post', "?p=".$p, '', array('onsubmit' => 'return formValidate()'));
if ($o == "a") {
    $form->addElement('header', 'title', _("Add a Centreon-Broker Configuration"));
} elseif ($o == "c") {
    $form->addElement('header', 'title', _("Modify a Centreon-Broker Configuration"));
} elseif ($o == "w") {
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
$tpl->assign('centreonbroker_main_options', _("Main options"));
$tpl->assign('centreonbroker_log_options', _("Log options"));
$tpl->assign('centreonbroker_advanced_options', _("Advanced options"));

$form->addElement('header', 'information', _("Centreon Broker configuration"));
$form->addElement('text', 'name', _("Name"), $attrsText);
$form->addElement('text', 'filename', _("Config file name"), $attrsText);
$form->addElement('select', 'ns_nagios_server', _("Requester"), $nagios_servers);
$form->addElement('text', 'cache_directory', _("Cache directory"), $attrsText);

$form->addElement('text', 'event_queue_max_size', _('Event queue max size'), $attrsText);
$command = $form->addElement('text', 'command_file', _('Command file'), $attrsText);

$form->addElement('text', 'pool_size', _('Pool size'), $attrsText);

//logger
$form->addElement('text', 'log_directory', _('Log directory'), $attrsText);
$form->addRule('log_directory', _("Mandatory directory"), 'required');
$form->addElement('text', 'log_filename', _('Log filename'), $attrsText);
$form->addElement('text', 'log_max_size', _('Maximum files size (in bytes)'), $attrsText2);

$logs = $cbObj->getLogsOption();
$logsLevel = $cbObj->getLogsLevel();
$smartyLogs = [];
$defaultLog = [];

foreach ($logs as $log) {
    array_push($smartyLogs, 'log_' . $log);
    $flippedLevel = array_flip($logsLevel);
    if ($log === 'core') {
        $defaultLog['log_' . $log] = $flippedLevel['info'];
    } else {
        $defaultLog['log_' . $log] = $flippedLevel['error'];
    }
    $form->addElement('select', 'log_' . $log, _($log), $logsLevel);
}

$timestamp = array();
$timestamp[] = $form->createElement('radio', 'write_timestamp', null, _("Yes"), 1);
$timestamp[] = $form->createElement('radio', 'write_timestamp', null, _("No"), 0);
$form->addGroup($timestamp, 'write_timestamp', _("Write timestamp (deprecated)"), '&nbsp;');

$thread_id = array();
$thread_id[] = $form->createElement('radio', 'write_thread_id', null, _("Yes"), 1);
$thread_id[] = $form->createElement('radio', 'write_thread_id', null, _("No"), 0);
$form->addGroup($thread_id, 'write_thread_id', _("Write thread id (deprecated)"), '&nbsp;');
//end logger

$status = array();
$status[] = $form->createElement('radio', 'activate', null, _("Enabled"), 1);
$status[] = $form->createElement('radio', 'activate', null, _("Disabled"), 0);
$form->addGroup($status, 'activate', _("Status"), '&nbsp;');

$centreonbroker = array();
$centreonbroker[] = $form->createElement('radio', 'activate_watchdog', null, _("Yes"), 1);
$centreonbroker[] = $form->createElement('radio', 'activate_watchdog', null, _("No"), 0);
$form->addGroup($centreonbroker, 'activate_watchdog', _("Link to cbd service"), '&nbsp;');

$stats_activate = array();
$stats_activate[] = $form->createElement('radio', 'stats_activate', null, _("Yes"), 1);
$stats_activate[] = $form->createElement('radio', 'stats_activate', null, _("No"), 0);
$form->addGroup($stats_activate, 'stats_activate', _("Statistics"), '&nbsp;');

$bbdo_versions = [ '2.0.0' => 'v.2.0.0 (old protocol)', '3.0.0' => 'v.3.0.0 (with protobuf)'];
$form->addElement('select', 'bbdo_version', _("BBDO version"), $bbdo_versions);

$tags = $cbObj->getTags();

$tabs = array();
foreach ($tags as $tagId => $tag) {
    $tabs[] = array(
        'id' => $tag,
        'name' => _("Centreon-Broker " . ucfirst($tag)),
        'link' => _("Add"),
        'nb' => 0,
        'blocks' => $cbObj->getListConfigBlock($tagId),
        'forms' => array()
    );
}

/**
 * Default values
 */
if (isset($_GET["o"]) && $_GET["o"] == 'a') {
    $result = array_merge(
        array(
            "name" => '',
            "cache_directory" => '/var/lib/centreon-broker/',
            "log_directory" => '/var/log/centreon-broker/',
            "write_timestamp" => '1',
            "write_thread_id" => '1',
            "stats_activate" => '1',
            "activate" => '1',
            "activate_watchdog" => '1',
            "bbdo_version" => '3.0.0',
        ),
        $defaultLog
    );
    $form->setDefaults($result);
    $tpl->assign('config_id', 0);
} elseif ($id !== 0) {
    $tpl->assign('config_id', $id);
    $defaultBrokerInformation = getCentreonBrokerInformation($id);
    $defaultBrokerInformation = htmlEncodeBrokerInformation($defaultBrokerInformation);
    if (!isset($defaultBrokerInformation['log_core'])) {
        $defaultBrokerInformation = array_merge(
            $defaultBrokerInformation,
            $defaultLog
        );
    }
    $form->setDefaults($defaultBrokerInformation);
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
$form->addElement('hidden', 'id');
$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);

/**
 * Form Rules
 */
$form->registerRule('exist', 'callback', 'testExistence');
$form->registerRule('isPositiveNumeric', 'callback', 'isPositiveNumeric');
$form->addRule('name', _("Mandatory name"), 'required');
$form->addRule('name', _("Name is already in use"), 'exist');
$form->addRule('filename', _("Mandatory filename"), 'required');
$form->addRule('cache_directory', _("Mandatory cache directory"), 'required');
$form->addRule('event_queue_max_size', _('Value must be numeric'), 'numeric');
$form->addRule('pool_size', _('Value must be a positive numeric'), 'isPositiveNumeric');

if ($o == "w") {
    if ($centreon->user->access->page($p) != 2) {
        $form->addElement(
            "button",
            "change",
            _("Modify"),
            array("onClick" => "javascript:window.location.href='?p=" . $p . "&o=c&id=" . $ndo2db_id . "'")
        );
    }
    $form->freeze();
} elseif ($o == "c") {
    /*
     * Modify a Centreon Broker information
     */
    $subC = $form->addElement('submit', 'submitC', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
} elseif ($o == "a") {
    /*
     * Add a nagios information
     */
    $subA = $form->addElement('submit', 'submitA', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
}

$valid = false;
if ($form->validate()) {
    $nagiosObj = $form->getElement('id');
    $data = htmlDecodeBrokerInformation($_POST);
    if ($form->getSubmitValue("submitA")) {
        $cbObj->insertConfig($data);
    } elseif ($form->getSubmitValue("submitC")) {
        $cbObj->updateConfig($data['id'], $data);
    }
    $o = null;
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
    $tpl->assign('smartyLogs', $smartyLogs);
    $tpl->display("formCentreonBroker.ihtml");
}
