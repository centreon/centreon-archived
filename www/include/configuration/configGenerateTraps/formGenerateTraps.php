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

/*
 * Init Centcore Pipe
 */
if (defined('_CENTREON_VARLIB_')) {
    $centcore_pipe = _CENTREON_VARLIB_."/centcore.cmd";
} else {
    $centcore_pipe = "/var/lib/centreon/centcore.cmd";
}

/*
 *  Get Poller List
 */
$DBRESULT = $pearDB->query("SELECT * FROM `nagios_server` WHERE `ns_activate` = '1' ORDER BY name ASC");
$n = $DBRESULT->numRows();

/*
 * Display null option
 */
if ($n > 1) {
    $tab_nagios_server = array(-1 => "");
}

/*
 * Display all servers list
 */
for ($i = 0; $nagios = $DBRESULT->fetchRow(); $i++) {
    $tab_nagios_server[$nagios['id']] = $nagios['name'];
}
$DBRESULT->free();


/*
 * Display all server options
 */
if ($n > 1) {
    $tab_nagios_server[0] = _("All Pollers");
}

/*
 * Form begin
 */
$attrSelect = array("style" => "width: 220px;");

$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
/*
 * Init Header for tables in template
 */
$form->addElement('header', 'title', _("SNMP Trap Generation"));
$form->addElement('header', 'opt', _("Export Options"));
$form->addElement('header', 'result', _("Actions"));
$form->addElement('header', 'infos', _("Implied Server"));
$form->addElement('select', 'host', _("Poller"), $tab_nagios_server, $attrSelect);
    
/*
 * Add checkbox for enable restart
 */
$form->addElement('checkbox', 'generate', _("Generate trap database "));
$form->addElement('checkbox', 'apply', _("Apply configurations"));

$options = array(null => null,
                'RELOADCENTREONTRAPD' => _('Reload'),
                'RESTARTCENTREONTRAPD' => _('Restart')
                );
$form->addElement('select', 'signal', _('Send signal'), $options);
    
/*
 * Set checkbox checked.
 */
$form->setDefaults(array('generate' => '1', 'generate' => '1', 'opt' => '1'));

$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

$sub = $form->addElement('submit', 'submit', _("Generate"), array("class" => "btc bt_success"));
$msg = null;
$stdout = null;
$msg_generate = "";
$trapdPath = "/etc/snmp/centreon_traps/";

if ($form->validate()) {
    $ret = $form->getSubmitValues();
    $host_list = array();
    foreach ($tab_nagios_server as $key => $value) {
        if ($key && ($res["host"] == 0 || $res["host"] == $key)) {
            $host_list[$key] = $value;
        }
    }
    if ($ret["host"] == 0 || $ret["host"] != -1) {
        /*
         * Create Server List to snmptt generation file
         */
        $tab_server = array();
        $DBRESULT_Servers = $pearDB->query("SELECT `name`, `id`, `snmp_trapd_path_conf`, `localhost` FROM `nagios_server` WHERE `ns_activate` = '1' ORDER BY `localhost` DESC");
        while ($tab = $DBRESULT_Servers->fetchRow()) {
            if (isset($ret["host"]) && ($ret["host"] == 0 || $ret["host"] == $tab['id'])) {
                $tab_server[$tab["id"]] = array("id" => $tab["id"], "name" => $tab["name"], "localhost" => $tab["localhost"]);
            }
            if ($tab['localhost'] && $tab['snmp_trapd_path_conf']) {
                $trapdPath = $tab['snmp_trapd_path_conf'];
            }
        }
        if (isset($ret["generate"]) && $ret["generate"]) {
            $msg_generate .= sprintf("<strong>%s</strong><br/>", _('Database generation'));
            $stdout = "";
            foreach ($tab_server as $host) {
                if (!is_dir("{$trapdPath}/{$host['id']}")) {
                    mkdir("{$trapdPath}/{$host['id']}");
                }
                $filename = "{$trapdPath}/{$host['id']}/centreontrapd.sdb";
                $output = array();
                $returnVal = 0;
                exec(_CENTREON_PATH_."/bin/generateSqlLite '{$host['id']}' '{$filename}' 2>&1", $output, $returnVal);
                $stdout .= implode("<br/>", $output)."<br/>";
                if ($returnVal != 0) {
                    break;
                }
            }
            $msg_generate .= str_replace("\n", "<br/>", $stdout)."<br/>";
        }
        if (isset($ret["apply"]) && $ret["apply"] && $returnVal == 0) {
            $msg_generate .= sprintf("<strong>%s</strong><br/>", _('Centcore commands'));
            foreach ($tab_server as $host) {
                passthru("echo 'SYNCTRAP:".$host['id']."' >> $centcore_pipe", $return);
                if ($return) {
                    $msg_generate .= "Error while writing into $centcore_pipe<br/>";
                } else {
                    $msg_generate .= "Poller (id:{$host['id']}): SYNCTRAP sent to centcore.cmd<br/>";
                }
            }
        }
        if (isset($ret['signal']) && in_array($ret['signal'], array('RELOADCENTREONTRAPD', 'RESTARTCENTREONTRAPD'))) {
            foreach ($tab_server as $host) {
                passthru("echo '".$ret['signal'].":".$host['id']."' >> $centcore_pipe", $return);
                if ($return) {
                    $msg_generate .= "Error while writing into $centcore_pipe<br/>";
                } else {
                    $msg_generate .= "Poller (id:{$host['id']}): ".$ret['signal']." sent to centcore.cmd<br/>";
                }
            }
        }
    }
}

$form->addElement('header', 'status', _("Status"));
if (isset($msg) && $msg) {
    $tpl->assign('msg', $msg);
}
if (isset($msg_generate) && $msg_generate) {
    $tpl->assign('msg_generate', $msg_generate);
}
if (isset($tab_server) && $tab_server) {
    $tpl->assign('tab_server', $tab_server);
}
if (isset($host_list) && $host_list) {
    $tpl->assign('host_list', $host_list);
}

$tpl->assign("helpattr", 'TITLE, "'._("Help").'", CLOSEBTN, true, FIX, [this, 0, 5], BGCOLOR, "#ffff99", BORDERCOLOR, "orange", TITLEFONTCOLOR, "black", TITLEBGCOLOR, "orange", CLOSEBTNCOLORS, ["","black", "white", "red"], WIDTH, -300, SHADOW, true, TEXTALIGN, "justify"');
$helptext = "";

include_once("help.php");

foreach ($help as $key => $text) {
    $helptext .= '<span style="display:none" id="help:'.$key.'">'.$text.'</span>'."\n";
}
$tpl->assign("helptext", $helptext);

/*
 * Apply a template definition
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->assign('o', $o);
$tpl->display("formGenerateTraps.ihtml");
