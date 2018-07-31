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

require_once _CENTREON_PATH_ . "www/class/centreon-config/centreonMainCfg.class.php";

$objMain = new CentreonMainCfg();

if (!$centreon->user->admin && $server_id && count($serverResult)) {
    if (!isset($serverResult[$server_id])) {
        $msg = new CentreonMsg();
        $msg->setImage("./img/icons/warning.png");
        $msg->setTextStyle("bold");
        $msg->setText(_('You are not allowed to access this monitoring instance'));
        return null;
    }
}

/*
 * Database retrieve information for Nagios
 */
$nagios = array();
if (($o == "c" || $o == "w") && $server_id) {
    $DBRESULT = $pearDB->query("SELECT * FROM `nagios_server` WHERE `id` = '$server_id' LIMIT 1");
    $cfg_server = array_map("myDecode", $DBRESULT->fetchRow());
    $DBRESULT->free();
}

/*
 * Preset values of misc commands
 */
$cdata = CentreonData::getInstance();
$cmdArray = $instanceObj->getCommandsFromPollerId(isset($server_id) ? $server_id : null);
$cdata->addJsData('clone-values-pollercmd', htmlspecialchars(
    json_encode($cmdArray),
    ENT_QUOTES
));
$cdata->addJsData('clone-count-pollercmd', count($cmdArray));

/*
 * nagios servers comes from DB
 */
$nagios_servers = array();
$DBRESULT = $pearDB->query("SELECT * FROM `nagios_server` ORDER BY name");
while ($nagios_server = $DBRESULT->fetchRow()) {
    $nagios_servers[$nagios_server["id"]] = $nagios_server["name"];
}
$DBRESULT->free();

$attrsText = array("size" => "30");
$attrsText2 = array("size" => "50");
$attrsText3 = array("size" => "5");
$attrsTextarea = array("rows" => "5", "cols" => "40");

/*
 * Form begin
 */
$form = new HTML_QuickForm('Form', 'post', "?p=" . $p);
if ($o == "a") {
    $form->addElement('header', 'title', _("Add a poller"));
} elseif ($o == "c") {
    $form->addElement('header', 'title', _("Modify a poller Configuration"));
} elseif ($o == "w") {
    $form->addElement('header', 'title', _("View a poller Configuration"));
}

/*
 * Headers
 */
$form->addElement('header', 'Server_Informations', _("Server Information"));
$form->addElement('header', 'SSH_Informations', _("SSH Information"));
$form->addElement('header', 'Nagios_Informations', _("Monitoring Engine Information"));
$form->addElement('header', 'Misc', _("Miscelleneous"));
$form->addElement('header', 'Centreontrapd', _("Centreon Trap Collector"));

/*
 * Poller Configuration basic information
 */
$form->addElement('header', 'information', _("Satellite configuration"));
$form->addElement('text', 'name', _("Poller Name"), $attrsText);
$form->addElement('text', 'ns_ip_address', _("IP Address"), $attrsText);
$form->addElement('text', 'init_script', _("Monitoring Engine Init Script"), $attrsText2);

$form->addElement('text', 'nagios_bin', _("Monitoring Engine Binary"), $attrsText2);
$form->addElement('text', 'nagiostats_bin', _("Monitoring Engine Statistics Binary"), $attrsText2);
$form->addElement('text', 'nagios_perfdata', _("Perfdata file"), $attrsText2);

$form->addElement('text', 'ssh_port', _("SSH port"), $attrsText3);

$Tab = array();
$Tab[] = HTML_QuickForm::createElement('radio', 'localhost', null, _("Yes"), '1');
$Tab[] = HTML_QuickForm::createElement('radio', 'localhost', null, _("No"), '0');
$form->addGroup($Tab, 'localhost', _("Localhost ?"), '&nbsp;');

$Tab = array();
$Tab[] = HTML_QuickForm::createElement('radio', 'is_default', null, _("Yes"), '1');
$Tab[] = HTML_QuickForm::createElement('radio', 'is_default', null, _("No"), '0');
$form->addGroup($Tab, 'is_default', _("Is default poller ?"), '&nbsp;');

$Tab = array();
$Tab[] = HTML_QuickForm::createElement('radio', 'ns_activate', null, _("Enabled"), '1');
$Tab[] = HTML_QuickForm::createElement('radio', 'ns_activate', null, _("Disabled"), '0');
$form->addGroup($Tab, 'ns_activate', _("Status"), '&nbsp;');

/*
 * Extra commands
 */
$cmdObj = new CentreonCommand($pearDB);
$cloneSetCmd = array();
$cloneSetCmd[] = $form->addElement(
    'select',
    'pollercmd[#index#]',
    _('Command'),
    (array(null => null) + $cmdObj->getMiscCommands()),
    array(
        'id' => 'pollercmd_#index#',
        'type' => 'select-one'
    )
);

/*
 * Centreon Broker
 */
$form->addElement('header', 'CentreonBroker', _("Centreon Broker"));
$form->addElement('text', 'centreonbroker_cfg_path', _("Centreon Broker configuration path"), $attrsText2);
$form->addElement('text', 'centreonbroker_module_path', _("Centreon Broker modules path"), $attrsText2);
$form->addElement('text', 'centreonbroker_logs_path', _("Centreon Broker logs"), $attrsText2);

/*
 * Centreon Connector
 */
$form->addElement('header', 'CentreonConnector', _("Centreon Connector"));
$form->addElement('text', 'centreonconnector_path', _("Centreon Connector path"), $attrsText2);

/*
 * Centreontrapd
 */
$form->addElement('text', 'init_script_centreontrapd', _("Centreontrapd init script path"), $attrsText2);
$form->addElement('text', 'snmp_trapd_path_conf', _('Directory of light database for traps'), $attrsText2);

/*
 * Set Default Values
 */
if (isset($_GET["o"]) && $_GET["o"] == 'a') {
    $monitoring_engines = array(
        "nagios_bin" => "/usr/sbin/centengine",
        "nagiostats_bin" => "/usr/sbin/centenginestats",
        "init_script" => "centengine",
        "nagios_perfdata" => "/var/log/centreon-engine/service-perfdata"
    );

    $form->setDefaults(
        array(
            "name" => '',
            "localhost" => '0',
            "ns_ip_address" => "127.0.0.1",
            "description" => "",
            "nagios_bin" => $monitoring_engines["nagios_bin"],
            "nagiostats_bin" => $monitoring_engines["nagiostats_bin"],
            "monitoring_engine"  => $centreon->optGen["monitoring_engine"],
            "init_script" => $monitoring_engines["init_script"],
            "ns_activate" => '1',
            "is_default"  =>  '0',
            "ssh_port"  =>  '22',
            "ssh_private_key"  =>  '~/.ssh/rsa.id',
            "nagios_perfdata"  => $monitoring_engines["nagios_perfdata"],
            "centreonbroker_cfg_path" => "/etc/centreon-broker",
            "centreonbroker_module_path" => "/usr/share/centreon/lib/centreon-broker",
            "centreonbroker_logs_path" => "/var/log/centreon-broker/watchdog.log",
            "init_script_centreontrapd" => "centreontrapd",
            "snmp_trapd_path_conf" => "/etc/snmp/centreon_traps/"
        )
    );
} else {
    if (isset($cfg_server)) {
        $form->setDefaults($cfg_server);
    }
}
$form->addElement('hidden', 'id');
$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);

/*
 * Form Rules
 */
$form->registerRule('exist', 'callback', 'testExistence');
$form->addRule('name', _("Name is already in use"), 'exist');
$form->addRule('name', _("The name of the poller is mandatory"), 'required');

$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;" . _("Required fields"));

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

if ($o == "w") {
    /*
     * Just watch a nagios information
     */
    if ($centreon->user->access->page($p) != 2) {
        $form->addElement(
            "button",
            "change",
            _("Modify"),
            array("onClick" => "javascript:window.location.href='?p=" . $p . "&o=c&id=" . $server_id . "'")
        );
    }
    $form->setDefaults($nagios);
    $form->freeze();
} elseif ($o == "c") {
    /*
     * Modify a nagios information
     */
    $subC = $form->addElement('submit', 'submitC', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
    $form->setDefaults($nagios);
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
    if ($form->getSubmitValue("submitA")) {
        insertServerInDB();
    } elseif ($form->getSubmitValue("submitC")) {
        updateServerInDB($nagiosObj->getValue());
    }
    $o = null;
    $valid = true;
}

if ($valid) {
    require_once($path . "listServers.php");
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
    $tpl->assign('engines', $monitoring_engines);
    $tpl->assign('cloneSetCmd', $cloneSetCmd);
    $tpl->assign('centreon_path', $centreon->optGen['oreon_path']);
    include_once("help.php");

    $helptext = "";
    foreach ($help as $key => $text) {
        $helptext .= '<span style="display:none" id="help:' . $key . '">' . $text . '</span>' . "\n";
    }
    $tpl->assign("helptext", $helptext);
    $tpl->display("formServers.ihtml");
}
