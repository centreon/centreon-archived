<?php

/*
 * Copyright 2005-2019 Centreon
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

include "./include/common/autoNumLimit.php";

// Init GMT class
$centreonGMT = new CentreonGMT($pearDB);
$centreonGMT->getMyGMTFromSession(session_id(), $pearDB);


$search = filter_var(
    $_POST['searchP'] ?? $_GET['searchP'] ?? null,
    FILTER_SANITIZE_STRING
);

if (isset($_POST['searchP']) || isset($_GET['searchP'])) {
    //saving filters values
    $centreon->historySearch[$url] = array();
    $centreon->historySearch[$url]['search'] = $search;
} else {
    //restoring saved values
    $search = $centreon->historySearch[$url]['search'] ?? null;
}

$LCASearch = '';
if ($search) {
    $LCASearch .= " name LIKE '%" . htmlentities($search, ENT_QUOTES, "UTF-8") . "%'";
}

// Get Authorized Actions
if ($is_admin == 0) {
    if ($centreon->user->access->checkAction('generate_cfg')) {
        $can_generate = 1;
    } else {
        $can_generate = 0;
    }
} else {
    $can_generate = 0;
}

/*
 * nagios servers comes from DB
 */
$nagiosServers = array();
$nagiosRestart = [];
foreach ($serverResult as $nagiosServer) {
    $nagiosServers[$nagiosServer["id"]] = $nagiosServer["name"];
    $nagiosRestart[$nagiosServer["id"]] = $nagiosServer["last_restart"];
}

$pollerstring = implode(',', array_keys($nagiosServers));

/*
 * Get information info RTM
 */
$nagiosInfo = array();
$dbResult = $pearDBO->query(
    "SELECT start_time AS program_start_time, running AS is_currently_running, pid AS process_id, instance_id, " .
    "name AS instance_name , last_alive FROM instances WHERE deleted = 0"
);
while ($info = $dbResult->fetch()) {
    $nagiosInfo[$info["instance_id"]] = $info;
}
$dbResult->closeCursor();

/*
 * Get Scheduler version
 */
$dbResult = $pearDBO->query(
    "SELECT DISTINCT instance_id, version AS program_version, engine AS program_name, name AS instance_name " .
    "FROM instances WHERE deleted = 0 "
);
while ($info = $dbResult->fetch()) {
    if (isset($nagiosInfo[$info["instance_id"]])) {
        $nagiosInfo[$info["instance_id"]]["version"] = $info["program_name"] . " " . $info["program_version"];
    }
}
$dbResult->closeCursor();

$query = 'SELECT ip FROM remote_servers';
$dbResult = $pearDB->query($query);
$remotesServerIPs = $dbResult->fetchAll(PDO::FETCH_COLUMN);
$dbResult->closeCursor();

// Smarty template Init
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

// Access level
($centreon->user->access->page($p) == 1) ? $lvl_access = 'w' : $lvl_access = 'r';
$tpl->assign('mode_access', $lvl_access);

// start header menu
$tpl->assign("headerMenu_name", _("Name"));
$tpl->assign("headerMenu_ip_address", _("IP Address"));
$tpl->assign("headerMenu_type", _("Server type"));
$tpl->assign("headerMenu_is_running", _("Is running ?"));
$tpl->assign("headerMenu_hasChanged", _("Conf Changed"));
$tpl->assign("headerMenu_pid", _("PID"));
$tpl->assign("headerMenu_version", _("Version"));
$tpl->assign("headerMenu_uptime", _("Uptime"));
$tpl->assign("headerMenu_lastUpdateTime", _("Last Update"));
$tpl->assign("headerMenu_status", _("Status"));
$tpl->assign("headerMenu_default", _("Default"));
$tpl->assign("headerMenu_options", _("Options"));

/*
 * Poller list
 */
$ACLString = $centreon->user->access->queryBuilder('WHERE', 'id', $pollerstring);

$query = "SELECT SQL_CALC_FOUND_ROWS id, name, ns_activate, ns_ip_address, localhost, is_default " .
    ", gorgone_communication_type FROM `nagios_server` " . $ACLString . " " .
    ($LCASearch != '' ? ($ACLString != "" ? "AND " : "WHERE ") . $LCASearch : "") .
    " ORDER BY name LIMIT " . $num * $limit . ", " . $limit;
$dbResult = $pearDB->query($query);

$rows = $pearDB->query("SELECT FOUND_ROWS()")->fetchColumn();

$servers = [];
$changeStateServers = [];
while (($config = $dbResult->fetch())) {
    $servers[] = $config;
    if ($config["ns_activate"] && isset($nagiosRestart[$config['id']])) {
        $changeStateServers[$config['id']] = $nagiosRestart[$config['id']];
    }
}

include "./include/common/checkPagination.php";

$form = new HTML_QuickFormCustom('select_form', 'POST', "?p=" . $p);

$changeStateServers = getChangeState($changeStateServers);

// Fill a tab with a multidimensional Array we put in $tpl
$elemArr = [];
$i = -1;
$centreonToken = createCSRFToken();

foreach ($servers as $config) {
    $i++;
    $moptions = "";
    $selectedElements = $form->addElement(
        'checkbox',
        "select[" . $config['id'] . "]",
        null,
        '',
        array('id' => 'poller_' . $config['id'], 'onClick' => 'hasPollersSelected();')
    );
    if (!$isRemote) {
        if ($config["ns_activate"]) {
            $moptions .= "<a href='main.php?p=" . $p . "&server_id=" . $config['id'] . "&o=u&limit=" . $limit .
                "&num=" . $num . "&search=" . $search . "&centreon_token=" . $centreonToken .
                "'><svg xmlns='http://www.w3.org/2000/svg' class='ico-14-disabled margin_right' viewBox='0 0 22 22' >
                <path d='M0 0h24v24H0z' fill='none'/>
        <path d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8 0-1.85.63-3.55 1.69-4.9L16.9 18.31C15.55 19.37 13.85 20 12 20zm6.31-3.1L7.1 5.69C8.45 4.63 10.15 4 12 4c4.42 0 8 3.58 8 8 0 1.85-.63 3.55-1.69 4.9z'/>
        </svg></a>&nbsp;&nbsp;";
        } else {
            $moptions .= "<a href='main.php?p=" . $p . "&server_id=" . $config['id'] . "&o=s&limit=" . $limit .
                "&num=" . $num . "&search=" . $search . "&centreon_token=" . $centreonToken .
                "'><svg xmlns='http://www.w3.org/2000/svg' class='ico-14-enabled margin_right' viewBox='0 0 24 24' >
    <path d='M0 0h24v24H0z' fill='none'/>
    <path d='M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z'/></svg></a>&nbsp;&nbsp;";
        }
    }
    $moptions .= "<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) " .
        "event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) " .
        "return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" " .
        "name='dupNbr[" . $config['id'] . "]' />";

    if (!isset($nagiosInfo[$config["id"]]["is_currently_running"])) {
        $nagiosInfo[$config["id"]]["is_currently_running"] = 0;
    }


    // Manage flag for changes
    $confChangedMessage = _("N/A");
    $hasChanged = false;
    if ($config["ns_activate"] && isset($nagiosRestart[$config['id']])) {
        $hasChanged = $changeStateServers[$config['id']];
        $confChangedMessage = $hasChanged ? _("Yes") : _("No");
    }

    // Manage flag for update time
    $lastUpdateTimeFlag = 0;
    if (!isset($nagiosInfo[$config["id"]]["last_alive"])) {
        $lastUpdateTimeFlag = 0;
    } elseif (time() - $nagiosInfo[$config["id"]]["last_alive"] > 10 * 60) {
        $lastUpdateTimeFlag = 1;
    }

    //Get cfg_id
    $dbResult2 = $pearDB->query(
        "SELECT nagios_id FROM cfg_nagios " .
        "WHERE nagios_server_id = " . (int) $config["id"] . " AND nagios_activate = '1'"
    );
    $cfg_id = $dbResult2->rowCount() ? $dbResult2->fetch() : -1;

    $uptime = '-';
    $isRunning = (isset($nagiosInfo[$config['id']]['is_currently_running']) &&
        $nagiosInfo[$config['id']]['is_currently_running'] == 1)
        ? true
        : false;
    $version = (isset($nagiosInfo[$config['id']]['version']))
        ? $nagiosInfo[$config['id']]['version']
        : _('N/A');
    $updateTime = (isset($nagiosInfo[$config['id']]['last_alive']) &&
        $nagiosInfo[$config['id']]['last_alive'])
        ? $nagiosInfo[$config['id']]['last_alive']
        : '-';
    $serverType = $config['localhost'] ? _('Central') : _('Distant Poller');
    $serverType = in_array($config['ns_ip_address'], $remotesServerIPs)
        ? _('Remote Server')
        : $serverType;

    if (
        isset($nagiosInfo[$config['id']]['is_currently_running'])
        && $nagiosInfo[$config['id']]['is_currently_running'] == 1
    ) {
        $now = new DateTime();
        $startDate = (new DateTime())->setTimestamp($nagiosInfo[$config['id']]['program_start_time']);
        $interval = date_diff($now, $startDate);
        if (intval($interval->format('%a')) >= 2) {
            $uptime = $interval->format('%a days');
        } elseif (intval($interval->format('%a')) == 1) {
            $uptime = $interval->format('%a days %i minutes');
        } elseif (intval($interval->format('%a')) < 1 && intval($interval->format('%h')) >= 1) {
            $uptime = $interval->format('%h hours %i minutes');
        } elseif (intval($interval->format('%h')) < 1) {
            $uptime = $interval->format('%i minutes %s seconds');
        } else {
            $uptime = $interval->format('%a days %h hours %i minutes %s seconds');
        }
    }

    $pollerProcessId = $isRunning
        ? $nagiosInfo[$config["id"]]["process_id"]
        : "-";

    // Manage different styles between each line
    $style = ($i % 2) ? "two" : "one";

    $serverLink = $isRemote
        ? "main.php?p={$p}&o=w&server_id={$config['id']}"
        : "main.php?p={$p}&o=c&server_id={$config['id']}";

    $elemArr[$i] = [
        'MenuClass' => "list_{$style}",
        'RowMenu_select' => $selectedElements->toHtml(),
        'RowMenu_name' => $config['name'],
        'RowMenu_ip_address' => $config['ns_ip_address'],
        'RowMenu_server_id' => $config['id'],
        'RowMenu_gorgone_protocol' => $config['gorgone_communication_type'],
        'RowMenu_link' => $serverLink,
        'RowMenu_type' => $serverType,
        'RowMenu_is_running' => $isRunning ? _('Yes') : _('No'),
        'RowMenu_is_runningFlag' => $nagiosInfo[$config['id']]['is_currently_running'],
        'RowMenu_is_default' => $config['is_default'] ? _('Yes') : _('No'),
        'RowMenu_hasChanged' => $confChangedMessage,
        "RowMenu_pid" => $pollerProcessId,
        'RowMenu_hasChangedFlag' => $hasChanged,
        'RowMenu_version' => $version,
        'RowMenu_uptime' => $uptime,
        'RowMenu_lastUpdateTime' => $updateTime,
        'RowMenu_lastUpdateTimeFlag' => $lastUpdateTimeFlag,
        'RowMenu_status' => $config['ns_activate'] ? _('Enabled') : _('Disabled'),
        'RowMenu_badge' => $config['ns_activate'] ? 'service_ok' : 'service_critical',
        'RowMenu_statusVal' => $config['ns_activate'],
        'RowMenu_cfg_id' => ($cfg_id == -1) ? '' : $cfg_id['nagios_id'],
        'RowMenu_options' => $moptions
    ];
}
$tpl->assign("elemArr", $elemArr);

$tpl->assign(
    "notice",
    _("Only services, servicegroups, hosts and hostgroups are taken in " .
        "account in order to calculate this status. If you modify a " .
        "template, it won't tell you the configuration had changed.")
);

// Action buttons
if (!$isRemote) {
    $tpl->assign(
        'wizardAddBtn',
        array(
            "link" => "./poller-wizard/1",
            "text" => _("Add"),
            "class" => "btc bt-poller-action bt_success",
            "iconClass" => "ui-icon-plus"
        )
    );

    $tpl->assign(
        'addBtn',
        array(
            "link" => "main.php?p=" . $p . "&o=a",
            "text" => _("Add (advanced)"),
            "class" => "btc bt-poller-action bt_success",
            "iconClass" => "ui-icon-plus"
        )
    );


    $tpl->assign(
        'duplicateBtn',
        array(
            "text" => _("Duplicate"),
            "class" => "btc bt-poller-action bt_success",
            "name" => "duplicate_action",
            "iconClass" => "ui-icon-copy",
            "onClickAction" => "javascript: " .
                " var bChecked = isChecked(); " .
                " if (!bChecked) { alert('" . _("Please select one or more items") . "'); return false;} " .
                " if (confirm('" . _("Do you confirm the duplication ?") . "')) { setO('m'); submit();} "
        )
    );

    $tpl->assign(
        'deleteBtn',
        array(
            "text" => _("Delete"),
            "class" => "btc bt-poller-action bt_danger",
            "name" => "delete_action",
            "iconClass" => "ui-icon-trash",
            "onClickAction" => "javascript: " .
                " var bChecked = isChecked(); " .
                " if (!bChecked) { alert('" . _("Please select one or more items") . "'); return false;} " .
                " if (confirm('" .
                _("You are about to delete one or more pollers.\\nThis action is IRREVERSIBLE.\\n" .
                "Do you confirm the deletion ?") .
                "')) { setO('d'); submit();} "
        )
    );

    $tpl->assign(
        'exportBtn',
        array(
            "text" => _("Export configuration"),
            "class" => "btc bt-poller-action bt_info",
            "name" => "apply_configuration",
            "iconClass" => "ui-icon-extlink",
            "onClickAction" => "applyConfiguration();"
        )
    );
}

$tpl->assign('limit', $limit);
$tpl->assign('searchP', $search);
$tpl->assign("can_generate", $can_generate);
$tpl->assign("is_admin", $is_admin);
$tpl->assign("isRemote", $isRemote);

// Apply a template definition
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->display("listServers.ihtml");
