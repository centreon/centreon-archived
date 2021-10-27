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

if (!isset($centreon)) {
    exit();
}

require_once _CENTREON_PATH_ . "/www/class/centreon-config/centreonMainCfg.class.php";

$objMain = new CentreonMainCfg();
$monitoring_engines = [];

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
$selectedAdditionnalRS = null;
$serverType = "poller";
$cfg_server = [];
if (($o == SERVER_MODIFY || $o == SERVER_WATCH) && $server_id) {
    $dbResult = $pearDB->query("SELECT * FROM `nagios_server` WHERE `id` = '$server_id' LIMIT 1");
    $cfg_server = array_map("myDecode", $dbResult->fetch());
    $dbResult->closeCursor();

    $query = 'SELECT ip FROM remote_servers';
    $dbResult = $pearDB->query($query);
    $remotesServerIPs = $dbResult->fetchAll(PDO::FETCH_COLUMN);
    $dbResult->closeCursor();

    if ($cfg_server['localhost']) {
        $serverType = "central";
    } elseif (in_array($cfg_server['ns_ip_address'], $remotesServerIPs)) {
        $serverType = "remote";
    }

    if ($serverType === "remote") {
        $statement = $pearDB->prepare(
            "SELECT http_method, http_port, no_check_certificate, no_proxy
            FROM `remote_servers`
            WHERE `ip` = :ns_ip_address LIMIT 1"
        );
        $statement->bindParam(':ns_ip_address', $cfg_server['ns_ip_address'], \PDO::PARAM_STR);
        $statement->execute();

        $cfg_server = array_merge($cfg_server, array_map("myDecode", $statement->fetch()));
        $statement->closeCursor();
    }

    if ($serverType === "poller") {
        // Select additional Remote Servers
        $statement = $pearDB->prepare(
            "SELECT remote_server_id, name
            FROM rs_poller_relation AS rspr
            LEFT JOIN nagios_server AS ns ON (rspr.remote_server_id = ns.id)
            WHERE poller_server_id = :poller_server_id"
        );
        $statement->bindParam(':poller_server_id', $cfg_server['id'], \PDO::PARAM_INT);
        $statement->execute();

        if ($statement->numRows() > 0) {
            while ($row = $statement->fetch()) {
                $selectedAdditionnalRS[] = array(
                    'id' => $row['remote_server_id'],
                    'text' => $row['name'],
                );
            }
        }
        $statement->closeCursor();
    }
}

/*
 * Preset values of misc commands
 */
$cdata = CentreonData::getInstance();
$cmdArray = $instanceObj->getCommandsFromPollerId($server_id ?? null);
$cdata->addJsData('clone-values-pollercmd', htmlspecialchars(
    json_encode($cmdArray),
    ENT_QUOTES
));
$cdata->addJsData('clone-count-pollercmd', count($cmdArray));

/*
 * nagios servers comes from DB
 */
$nagios_servers = array();
$dbResult = $pearDB->query("SELECT * FROM `nagios_server` ORDER BY name");
while ($nagios_server = $dbResult->fetch()) {
    $nagios_servers[$nagios_server["id"]] = $nagios_server["name"];
}
$dbResult->closeCursor();

$attrsText = array("size" => "30");
$attrsText2 = array("size" => "50");
$attrsText3 = array("size" => "5");
$attrsTextarea = array("rows" => "5", "cols" => "40");

/*
 * Include Poller api
 */
$attrPollers = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => './api/internal.php?object=centreon_configuration_poller&action=list&t=remote',
    'multiple' => false,
    'linkedObject' => 'centreonInstance'
);
$attrPoller1 = $attrPollers;
if (isset($cfg_server['remote_id'])) {
    $attrPoller1['defaultDatasetRoute'] =
        './api/internal.php?object=centreon_configuration_poller&action=defaultValues'
        . '&target=resources&field=instance_id&id=' . $cfg_server['remote_id'];
}
$attrPoller2 = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => './api/internal.php?object=centreon_configuration_poller&action=list&t=remote',
    'multiple' => true,
    'linkedObject' => 'centreonInstance'
);

/*
 * Form begin
 */
$form = new HTML_QuickFormCustom('Form', 'post', "?p=" . $p);
if ($o == SERVER_ADD) {
    $form->addElement('header', 'title', _("Add a poller"));
} elseif ($o == SERVER_MODIFY) {
    $form->addElement('header', 'title', _("Modify a poller Configuration"));
} elseif ($o == SERVER_WATCH) {
    $form->addElement('header', 'title', _("View a poller Configuration"));
}

/*
 * Headers
 */
$form->addElement('header', 'Server_Informations', _("Server Information"));
$form->addElement('header', 'gorgone_Informations', _("Gorgone Information"));
$form->addElement('header', 'Nagios_Informations', _("Monitoring Engine Information"));
$form->addElement('header', 'Misc', _("Miscellaneous"));
$form->addElement('header', 'Centreontrapd', _("Centreon Trap Collector"));

/*
 * form for Remote Server
 */
if (strcmp($serverType, 'remote') == 0) {
    $form->addElement('header', 'Remote_Configuration', _("Remote Server Configuration"));
    $aMethod = array(
        'http' => 'http',
        'https' => 'https'
    );
    $form->addElement('select', 'http_method', _("HTTP Method"), $aMethod);
    $form->addElement('text', 'http_port', _("HTTP Port"), $attrsText3);
    $tab = array();
    $tab[] = $form->createElement('radio', 'no_check_certificate', null, _("Yes"), '1');
    $tab[] = $form->createElement('radio', 'no_check_certificate', null, _("No"), '0');
    $form->addGroup($tab, 'no_check_certificate', _("Do not check SSL certificate validation"), '&nbsp;');
    $tab = array();
    $tab[] = $form->createElement('radio', 'no_proxy', null, _("Yes"), '1');
    $tab[] = $form->createElement('radio', 'no_proxy', null, _("No"), '0');
    $form->addGroup($tab, 'no_proxy', _("Do not use proxy defined in global configuration"), '&nbsp;');
}

/*
 * Poller Configuration basic information
 */
$form->addElement('header', 'information', _("Satellite configuration"));
$form->addElement('text', 'name', _("Poller Name"), $attrsText);
$form->addElement('text', 'ns_ip_address', _("IP Address"), $attrsText);
$form->addElement('text', 'engine_start_command', _("Monitoring Engine start command"), $attrsText2);
$form->addElement('text', 'engine_stop_command', _("Monitoring Engine stop command"), $attrsText2);
$form->addElement('text', 'engine_restart_command', _("Monitoring Engine restart command"), $attrsText2);
$form->addElement('text', 'engine_reload_command', _("Monitoring Engine reload command"), $attrsText2);
if (strcmp($serverType, 'poller') == 0) {
    $form->addElement(
        'select2',
        'remote_id',
        _('Attach to Master Remote Server'),
        array(),
        $attrPoller1
    );
    $form->addElement('select2', 'remote_additional_id', _('Attach additional Remote Servers'), array(), $attrPoller2);
    $tab = [];
    $tab[] = $form->createElement('radio', 'remote_server_use_as_proxy', null, _("Enable"), '1');
    $tab[] = $form->createElement('radio', 'remote_server_use_as_proxy', null, _("Disable"), '0');
    $form->addGroup($tab, 'remote_server_use_as_proxy', _("Use the Remote Server as a proxy"), '&nbsp;');
}
$form->addElement('text', 'nagios_bin', _("Monitoring Engine Binary"), $attrsText2);
$form->addElement('text', 'nagiostats_bin', _("Monitoring Engine Statistics Binary"), $attrsText2);
$form->addElement('text', 'nagios_perfdata', _("Perfdata file"), $attrsText2);

$tab = array();
if ($serverType !== "central") {
    $form->addElement('text', 'ssh_port', _("SSH Legacy port"), $attrsText3);
}

$tab[] = $form->createElement('radio', 'gorgone_communication_type', null, _("ZMQ"), ZMQ);
$tab[] = $form->createElement('radio', 'gorgone_communication_type', null, _("SSH"), SSH);
$form->addGroup($tab, 'gorgone_communication_type', _("Gorgone connection protocol"), '&nbsp;');
$form->addElement('text', 'gorgone_port', _("Gorgone connection port"), $attrsText3);

$tab = array();
$tab[] = $form->createElement(
    'radio',
    'localhost',
    null,
    _("Yes"),
    '1',
    array('onclick' => "displayGorgoneParam(false);")
);
$tab[] = $form->createElement(
    'radio',
    'localhost',
    null,
    _("No"),
    '0',
    array('onclick' => "displayGorgoneParam(true);")
);
$form->addGroup($tab, 'localhost', _("Localhost ?"), '&nbsp;');

$tab = array();
$tab[] = $form->createElement('radio', 'is_default', null, _("Yes"), '1');
$tab[] = $form->createElement('radio', 'is_default', null, _("No"), '0');
$form->addGroup($tab, 'is_default', _("Is default poller ?"), '&nbsp;');

$tab = array();
$tab[] = $form->createElement('radio', 'ns_activate', null, _("Enabled"), '1');
$tab[] = $form->createElement('radio', 'ns_activate', null, _("Disabled"), '0');
$form->addGroup($tab, 'ns_activate', _("Status"), '&nbsp;');

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
$form->addElement('text', 'broker_reload_command', _("Centreon Broker reload command"), $attrsText2);
$form->addElement('text', 'centreonbroker_cfg_path', _("Centreon Broker configuration path"), $attrsText2);
$form->addElement('text', 'centreonbroker_module_path', _("Centreon Broker modules path"), $attrsText2);
$form->addElement('text', 'centreonbroker_logs_path', _("Centreon Broker logs path"), $attrsText2);

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
if (isset($_GET["o"]) && $_GET["o"] == SERVER_ADD) {
    $monitoring_engines = [
        "nagios_bin" => "/usr/sbin/centengine",
        "nagiostats_bin" => "/usr/sbin/centenginestats",
        "engine_start_command" => "service centengine start",
        "engine_stop_command" => "service centengine stop",
        "engine_restart_command" => "service centengine restart",
        "engine_reload_command" => "service centengine reload",
        "nagios_perfdata" => "/var/log/centreon-engine/service-perfdata"
    ];

    $form->setDefaults(
        [
            "name" => '',
            "localhost" => '0',
            "ns_ip_address" => "127.0.0.1",
            "description" => "",
            "nagios_bin" => $monitoring_engines["nagios_bin"],
            "nagiostats_bin" => $monitoring_engines["nagiostats_bin"],
            "engine_start_command" => $monitoring_engines["engine_start_command"],
            "engine_stop_command" => $monitoring_engines["engine_stop_command"],
            "engine_restart_command" => $monitoring_engines["engine_restart_command"],
            "engine_reload_command" => $monitoring_engines["engine_reload_command"],
            "ns_activate" => '1',
            "is_default" => '0',
            "ssh_port" => 22,
            "gorgone_communication_type" => ZMQ,
            "gorgone_port" => 5556,
            "nagios_perfdata" => $monitoring_engines["nagios_perfdata"],
            "broker_reload_command" => "service cbd reload",
            "centreonbroker_cfg_path" => "/etc/centreon-broker",
            "centreonbroker_module_path" => "/usr/share/centreon/lib/centreon-broker",
            "centreonbroker_logs_path" => "/var/log/centreon-broker",
            "init_script_centreontrapd" => "centreontrapd",
            "snmp_trapd_path_conf" => "/etc/snmp/centreon_traps/",
            "remote_server_use_as_proxy" => '1'
        ]
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
$form->registerRule('testAdditionalRemoteServer', 'callback', 'testAdditionalRemoteServer');
$form->registerRule('isValidIpAddress', 'callback', 'isValidIpAddress');
$form->addRule('name', _("Name is already in use"), 'exist');
$form->addRule('name', _("The name of the poller is mandatory"), 'required');
$form->addRule('ns_ip_address', _("Compulsory Name"), 'required');
if ($serverType === 'poller') {
    $form->addRule(
        array('remote_additional_id', 'remote_id'),
        _('To use additional Remote Servers a Master Remote Server must be selected.'),
        'testAdditionalRemoteServer'
    );
}
$form->addRule('ns_ip_address', _("The IP address is incorrect"), 'isValidIpAddress');

$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;" . _("Required fields"));

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

if ($o == SERVER_WATCH) {
    /*
     * Just watch a nagios information
     */
    if ($centreon->user->access->page($p) != 2 && !$isRemote) {
        $form->addElement(
            "button",
            "change",
            _("Modify"),
            array("onClick" => "javascript:window.location.href='?p=" . $p . "&o=c&id=" . $server_id . "'")
        );
    }
    $form->setDefaults($nagios);
    $form->freeze();
} elseif ($o == SERVER_MODIFY) {
    /*
     * Modify a nagios information
     */
    $subC = $form->addElement('submit', 'submitC', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
    $form->registerRule('ipCanBeUpdated', 'callback', 'ipCanBeUpdated');
    $form->addRule(
        ['ns_ip_address', 'id'],
        _("The IP address is already registered on another poller"),
        'ipCanBeUpdated'
    );
    $form->setDefaults($nagios);
} elseif ($o == SERVER_ADD) {
    /*
     * Add a nagios information
     */
    $subA = $form->addElement('submit', 'submitA', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
    $form->registerRule('ipCanBeRegistered', 'callback', 'ipCanBeRegistered');
    $form->addRule('ns_ip_address', _("The IP address is already registered"), 'ipCanBeRegistered');
}

$valid = false;
if ($form->validate()) {
    $nagiosObj = $form->getElement('id');
    if ($form->getSubmitValue("submitA")) {
        insertServerInDB($form->getSubmitValues());
    } elseif ($form->getSubmitValue("submitC")) {
        updateServer(
            (int)$nagiosObj->getValue(),
            $form->getSubmitValues()
        );
    }
    $o = null;
    $valid = true;
}

if ($valid) {
    defineLocalPollerToDefault();
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
    $tpl->assign('isRemote', $isRemote);
    include_once("help.php");

    $helptext = "";
    foreach ($help as $key => $text) {
        $helptext .= '<span style="display:none" id="help:' . $key . '">' . $text . '</span>' . "\n";
    }
    $tpl->assign("helptext", $helptext);
    $tpl->display("formServers.ihtml");
}

?>
<script type='text/javascript'>
    // toggle gorgone port and communication mode fields
    function displayGorgoneParam(checkValue) {
        if (checkValue === true) {
            jQuery('#gorgoneData').fadeIn({duration: 0});
        } else {
            jQuery('#gorgoneData').fadeOut({duration: 0});
        }
    }

    // init current gorgone fields visibility
    displayGorgoneParam(<?= !isset($cfg_server['localhost']) || !$cfg_server['localhost'] ? "true" : "false" ?>)

    jQuery("#remote_additional_id").centreonSelect2({
        select2: {
            ajax: {
                url: './api/internal.php?object=centreon_configuration_poller&action=list&t=remote',
                cache: false
            },
            multiple: true,
        },
        allowClear: true,
        additionnalFilters: {
            e: '#remote_id'
        }
    });

    //check of gorgone_port type
    jQuery(function () {
        jQuery("input[name='gorgone_port']").change(function () {
            if (isNaN(this.value)) {
                const msg = "<span id='errMsg'><font style='color: red;'> Need to be a number</font></span>";
                jQuery(msg).insertAfter(this);
                jQuery("input[type='submit']").prop('disabled', true);
            } else {
                jQuery('#errMsg').remove();
                jQuery("input[type='submit']").prop('disabled', false);
            }
        });
    });

    jQuery(function () {
        jQuery("#remote_id").change(function () {
            var master_remote_id = jQuery("#remote_id").val();
            var remote_additional_id = jQuery("#remote_additional_id").val();

            jQuery.ajax({
                url: "./api/internal.php?object=centreon_configuration_poller&action=list&t=remote&e="
                    + master_remote_id,
                type: "GET",
                dataType: "json",
                success: function (json) {
                    jQuery('#remote_additional_id').val('');
                    json.items.forEach(function (elem) {
                        jQuery('#remote_additional_id').empty();
                        if (jQuery.inArray(elem.id, remote_additional_id) != -1
                            && elem.id != master_remote_id && elem.id) {
                            jQuery('#remote_additional_id').append(
                                '<option value="' + elem.id + '" selected>' + elem.text + '</option>'
                            );
                        }
                    });
                    jQuery('#remote_additional_id').trigger('change');
                }
            });
        });

        var initAdditionnalRS = '<?php echo json_encode($selectedAdditionnalRS); ?>';
        var pollers = JSON.parse(initAdditionnalRS);
        if (pollers) {
            for (var i = 0; i < pollers.length; i++) {
                if (pollers[i].text != null) {
                    jQuery('#remote_additional_id').append(
                        '<option value="' + pollers[i].id + '" selected>' + pollers[i].text + '</option>'
                    );
                }
            }
            jQuery('#remote_additional_id').trigger('change');
        }
    });
</script>
