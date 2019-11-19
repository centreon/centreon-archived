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

// Require configuration.
require_once realpath(__DIR__ . "/../../../../../../bootstrap.php");
include_once _CENTREON_PATH_ . "www/class/centreonUtils.class.php";

// Require Specific XML / Ajax Class
include_once _CENTREON_PATH_ . "www/class/centreonXMLBGRequest.class.php";
include_once _CENTREON_PATH_ . "www/class/centreonInstance.class.php";
include_once _CENTREON_PATH_ . "www/class/centreonCriticality.class.php";
include_once _CENTREON_PATH_ . "www/class/centreonMedia.class.php";

// Require common Files.
include_once _CENTREON_PATH_ . "www/include/monitoring/status/Common/common-Func.php";
include_once _CENTREON_PATH_ . "www/include/common/common-Func.php";

// Create XML Request Objects
CentreonSession::start();
$obj = new CentreonXMLBGRequest($dependencyInjector, session_id(), 1, 1, 0, 1);

// Get session
if (isset($_SESSION['centreon'])) {
    $centreon = $_SESSION['centreon'];
} else {
    exit;
}

// Get language
$locale = $centreon->user->get_lang();
putenv("LANG=$locale");
setlocale(LC_ALL, $locale);
bindtextdomain("messages", _CENTREON_PATH_ . "www/locale/");
bind_textdomain_codeset("messages", "UTF-8");
textdomain("messages");

$criticality = new CentreonCriticality($obj->DB);
$instanceObj = new CentreonInstance($obj->DB);
$media = new CentreonMedia($obj->DB);

if (!isset($obj->session_id) || !CentreonSession::checkSession($obj->session_id, $obj->DB)) {
    print "Bad Session ID";
    exit();
}

// Set Default Poller
$obj->getDefaultFilters();

// Check Arguments From GET tab
$o = filter_input(INPUT_GET, 'o', FILTER_SANITIZE_STRING, ['options' => ['default' => 'h']]);
$p = filter_input(INPUT_GET, 'p', FILTER_VALIDATE_INT, ['options' => ['default' => 2]]);
$num = filter_input(INPUT_GET, 'num', FILTER_VALIDATE_INT, ['options' => ['default' => 0]]);
$limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT, ['options' => ['default' => 20]]);
$nc = filter_input(INPUT_GET, 'nc', FILTER_VALIDATE_INT, ['options' => ['default' => 0]]);
$criticalityId = filter_input(INPUT_GET, 'criticality', FILTER_VALIDATE_INT, ['options' => ['default' => 0]]);
$serviceToSearch = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING, ['options' => ['default' => '']]);
$hostToSearch = filter_input(INPUT_GET, 'search_host', FILTER_SANITIZE_STRING, ['options' => ['default' => '']]);
$outputToSearch = filter_input(INPUT_GET, 'search_output', FILTER_SANITIZE_STRING, ['options' => ['default' => '']]);
$sortType = filter_input(INPUT_GET, 'sort_type', FILTER_SANITIZE_STRING, ['options' => ['default' => 'host_name']]);
$order = isset($_GET['order']) && $_GET['order'] === "DESC" ? "DESC" : "ASC";
$statusService = filter_input(INPUT_GET, 'statusService', FILTER_SANITIZE_STRING, ['options' => ['default' => '']]);
$statusFilter = filter_input(INPUT_GET, 'statusFilter', FILTER_SANITIZE_STRING, ['options' => ['default' => '']]);
$dateFormat = "Y/m/d H:i:s";
//if instance, hostgroup or servicegroup values are not set, displaying each active linked resources
$instance = filter_var($obj->defaultPoller ?? -1, FILTER_VALIDATE_INT);
$hostgroups = filter_var($obj->defaultHostgroups ?? 0, FILTER_VALIDATE_INT);
$servicegroups = filter_var($obj->defaultServicegroups ?? 0, FILTER_VALIDATE_INT);

// Store in session the last type of call
$_SESSION['monitoring_service_status'] = $statusService;
$_SESSION['monitoring_service_status_filter'] = $statusFilter;

// Backup poller selection
$obj->setInstanceHistory($instance);

// Backup criticality id
$obj->setCriticality($criticalityId);

// Saving bound values
$queryValues = [];

// Graphs Tables
$graphs = [];

// Get Service status
$instance_filter = " ";
if (!empty($instance) && $instance != -1) {
    $instance_filter = " AND h.instance_id = :instance";
    $queryValues['instance'] = [\PDO::PARAM_INT => $instance];
}

$searchHost = " ";
if ($hostToSearch) {
    $searchHost = " AND (h.name LIKE :hostToSearch
        OR h.alias LIKE :hostToSearch
        OR h.address LIKE :hostToSearch) ";
    $queryValues['hostToSearch'] = [\PDO::PARAM_STR => '%' . $hostToSearch . '%'];
}

$searchService = " ";
if ($serviceToSearch) {
    $searchService = " AND (s.description LIKE :serviceToSearch OR s.display_name LIKE :serviceToSearch) ";
    $queryValues['serviceToSearch'] = [\PDO::PARAM_STR => '%' . $serviceToSearch . '%'];
}

$searchOutput = " ";
if ($outputToSearch) {
    $searchOutput = " AND s.output LIKE :outputToSearch ";
    $queryValues['outputToSearch'] = [\PDO::PARAM_STR => '%' . $outputToSearch . '%'];
}

$tabOrder = [];
$tabOrder["criticality_id"] = " ORDER BY isnull " .$order . ", criticality " . $order . ", h.name, s.description ";
$tabOrder["host_name"] = " ORDER BY h.name " . $order . ", s.description ";
$tabOrder["service_description"] = " ORDER BY s.description " . $order . ", h.name";
$tabOrder["current_state"] = " ORDER BY s.state " . $order . ", h.name, s.description";
$tabOrder["last_state_change"] = " ORDER BY s.last_state_change " . $order . ", h.name, s.description";
$tabOrder["last_hard_state_change"] = " ORDER by s.last_hard_state_change " . $order . ", h.name, s.description";
$tabOrder["last_check"] = " ORDER BY s.last_check " . $order . ", h.name, s.description";
$tabOrder["current_attempt"] = " ORDER BY s.check_attempt " . $order . ", h.name, s.description";
$tabOrder["output"] = " ORDER BY s.output " . $order . ", h.name, s.description";
$tabOrder["default"] = $tabOrder['criticality_id'];

$request = "SELECT SQL_CALC_FOUND_ROWS DISTINCT h.name, h.alias, h.address, h.host_id, s.description,
    s.service_id, s.notes, s.notes_url, s.action_url, s.max_check_attempts,
    s.icon_image, s.display_name, s.state, s.output as plugin_output,
    s.state_type, s.check_attempt as current_attempt, s.last_update as status_update_time, s.last_state_change,
    s.last_hard_state_change, s.last_check, s.next_check,
    s.notify, s.acknowledged, s.passive_checks, s.active_checks, s.event_handler_enabled, s.flapping,
    s.scheduled_downtime_depth, s.flap_detection, h.state as host_state, h.acknowledged AS h_acknowledged,
    h.scheduled_downtime_depth AS h_scheduled_downtime_depth,
    h.icon_image AS h_icon_images, h.display_name AS h_display_name, h.action_url AS h_action_url,
    h.notes_url AS h_notes_url, h.notes AS h_notes, h.address,
    h.passive_checks AS h_passive_checks, h.active_checks AS h_active_checks,
    i.name as instance_name, cv.value as criticality, cv.value IS NULL as isnull
    FROM hosts h, instances i ";
if (isset($hostgroups) && $hostgroups != 0) {
    $request .= ", hosts_hostgroups hg, hostgroups hg2";
}
if (isset($servicegroups) && $servicegroups != 0) {
    $request .= ", services_servicegroups ssg, servicegroups sg";
}
if ($criticalityId) {
    $request .= ", customvariables cvs ";
}
if (!$obj->is_admin) {
    $request .= ", centreon_acl ";
}
$request .= ", services s LEFT JOIN customvariables cv ON (s.service_id = cv.service_id
    AND cv.host_id = s.host_id AND cv.name = 'CRITICALITY_LEVEL')
    WHERE h.host_id = s.host_id
    AND s.enabled = 1
    AND h.enabled = 1
    AND h.instance_id = i.instance_id ";
if ($criticalityId) {
    $request .= " AND s.service_id = cvs. service_id
        AND cvs.host_id = h.host_id
        AND cvs.name = 'CRITICALITY_ID'
        AND cvs.value = :criticalityValue";
    // the variable bounded to criticalityValue must be an integer. But is inserted in a DB's varchar column
    $queryValues['criticalityValue'] = [\PDO::PARAM_STR => $criticalityId];
}
$request .= " AND h.name NOT LIKE '_Module_BAM%' "
    . $searchHost
    . $searchService
    . $searchOutput
    . $instance_filter;

if ($statusService == 'svc_unhandled') {
    $request .= " AND s.state_type = 1
        AND s.acknowledged = 0
        AND s.scheduled_downtime_depth = 0
        AND h.acknowledged = 0 AND h.scheduled_downtime_depth = 0 ";
}

if ($statusService === 'svc_unhandled' || $statusService === 'svcpb') {
    switch ($statusFilter) {
        case 'warning':
            $request .= " AND s.state = 1 ";
            break;
        case 'critical':
            $request .= " AND s.state = 2 ";
            break;
        case 'unknown':
            $request .= " AND s.state = 3 ";
            break;
        default:
            $request .= " AND s.state != 0 AND s.state != 4 ";
    }
} elseif ($statusService === 'svc') {
    switch ($statusFilter) {
        case 'ok':
            $request .= " AND s.state = 0";
            break;
        case 'warning':
            $request .= " AND s.state = 1 ";
            break;
        case 'critical':
            $request .= " AND s.state = 2 ";
            break;
        case 'unknown':
            $request .= " AND s.state = 3 ";
            break;
        case 'pending':
            $request .= " AND s.state = 4 ";
            break;
    }
}

// HostGroup Filter
if (isset($hostgroups) && $hostgroups != 0) {
    $request .= " AND hg.hostgroup_id = hg2.hostgroup_id
        AND hg.host_id = h.host_id AND hg.hostgroup_id = :hostGroup ";
    $queryValues['hostGroup'] = [\PDO::PARAM_INT => $hostgroups];
}

// ServiceGroup Filter
if (isset($servicegroups) && $servicegroups != 0) {
    $request .= " AND ssg.servicegroup_id = sg.servicegroup_id
        AND ssg.service_id = s.service_id AND ssg.servicegroup_id = :serviceGroup ";
    $queryValues['serviceGroup'] = [\PDO::PARAM_INT => $servicegroups];
}

// ACL activation
if (!$obj->is_admin) {
    $request .= " AND h.host_id = centreon_acl.host_id
        AND s.service_id = centreon_acl.service_id AND group_id IN (" . $obj->grouplistStr . ") ";
}

// Sort order by
$request .= isset($tabOrder[$sortType])
    ? $tabOrder[$sortType]
    : $tabOrder["default"];

$request .= " LIMIT :numLimit, :limit";
$queryValues['numLimit'] = [\PDO::PARAM_INT => ($num * $limit)];
$queryValues['limit'] = [\PDO::PARAM_INT => $limit];

// Get Pagination Rows
$sqlError = false;
try {
    $dbResult = $obj->DBC->prepare($request);
    foreach ($queryValues as $bindId => $bindData) {
        foreach ($bindData as $bindType => $bindValue) {
            $dbResult->bindValue($bindId, $bindValue, $bindType);
        }
    }
    $dbResult->execute();

    $numRows = $obj->DBC->numberRows();
} catch (\PDOException $e) {
    $sqlError = true;
    $numRows = 0;
}

// Get criticality ids
$critRes = $obj->DBC->query(
    "SELECT value, service_id FROM customvariables WHERE name = 'CRITICALITY_ID' AND service_id IS NOT NULL"
);
$criticalityUsed = 0;
$critCache = [];
if ($critRes->rowCount()) {
    $criticalityUsed = 1;
    while ($critRow = $critRes->fetch()) {
        $critCache[$critRow['service_id']] = $critRow['value'];
    }
}

$isHardStateDurationVisible =
    !($statusService === 'svc'
        && ($statusFilter === 'ok' || $statusFilter === 'pending')
    );

/**
 * Create Buffer
 */
$obj->XML->startElement("reponse");
$obj->XML->startElement("i");
$obj->XML->writeElement("numrows", $numRows);
$obj->XML->writeElement("num", $num);
$obj->XML->writeElement("limit", $limit);
$obj->XML->writeElement("p", $p);
$obj->XML->writeElement("nc", $nc);
$obj->XML->writeElement("o", $o);
$obj->XML->writeElement("hard_state_label", _("Hard State Duration"));
$obj->XML->writeElement("http_link", _("HTTP Link"));
$obj->XML->writeElement("http_action_link", _("HTTP Action Link"));
$obj->XML->writeElement("host_currently_downtime", _("Host is currently on downtime"));
$obj->XML->writeElement("problem_ack", _("Problem has been acknowledged"));
$obj->XML->writeElement("host_passive_mode", _("This host is only checked in passive mode"));
$obj->XML->writeElement("host_never_checked", _("This host is never checked"));
$obj->XML->writeElement("service_currently_downtime", _("Service is currently on Downtime"));
$obj->XML->writeElement("service_passive_mode", _("This service is only checked in passive mode"));
$obj->XML->writeElement("service_not_active_not_passive", _("This service is neither active nor passive"));
$obj->XML->writeElement("service_flapping", _("This Service is flapping"));
$obj->XML->writeElement("notif_disabled", _("Notification is disabled"));
$obj->XML->writeElement("use_criticality", $criticalityUsed);
$obj->XML->writeElement("use_hard_state_duration", $isHardStateDurationVisible ? 1 : 0);
$obj->XML->endElement();

$host_prev = "";
$ct = 0;
$flag = 0;

if (!$sqlError) {
    while ($data = $dbResult->fetch()) {
        $passive = 0;
        $active = 1;
        $last_check = " ";
        $duration = " ";

        // Split the plugin_output
        $outputLines = explode("\n", $data['plugin_output']);
        $pluginShortOuput = $outputLines[0];

        if ($data["last_state_change"] > 0 && time() > $data["last_state_change"]) {
            $duration = CentreonDuration::toString(time() - $data["last_state_change"]);
        } elseif ($data["last_state_change"] > 0) {
            $duration = " - ";
        }

        $hard_duration = " N/S ";
        if (($data["last_hard_state_change"] > 0) && ($data["last_hard_state_change"] >= $data["last_state_change"])) {
            $hard_duration = CentreonDuration::toString(time() - $data["last_hard_state_change"]);
        }

        $class = null;
        if ($data["scheduled_downtime_depth"] > 0) {
            $class = "line_downtime";
        } elseif ($data["state"] == 2) {
            $data["acknowledged"] == 1 ? $class = "line_ack" : $class = "list_down";
        } else {
            if ($data["acknowledged"] == 1) {
                $class = "line_ack";
            }
        }

        $obj->XML->startElement("l");
        $trClass = $obj->getNextLineClass();
        if (isset($class)) {
            $trClass = $class;
        }
        $obj->XML->writeAttribute("class", $trClass);
        $obj->XML->writeElement("o", $ct++);

        $isMeta = 0;
        $data["host_display_name"] = $data["name"];
        if (!strncmp($data["name"], "_Module_Meta", strlen("_Module_Meta"))) {
            $isMeta = 1;
            $data["host_display_name"] = "Meta";
            $data["host_state"] = "0";
        }

        if ($host_prev == $data["name"]) {
            $obj->XML->writeElement("hc", "transparent");
            $obj->XML->writeElement("isMeta", $isMeta);
            $obj->XML->writeElement("hdn", $data["host_display_name"]);
            $obj->XML->startElement("hn");
            $obj->XML->writeAttribute("none", "1");
            $obj->XML->text(CentreonUtils::escapeSecure(urlencode($data["name"])));
            $obj->XML->endElement();
            $obj->XML->writeElement("hnl", CentreonUtils::escapeSecure(urlencode($data["name"])));
            $obj->XML->writeElement("hid", $data["host_id"]);
        } else {
            $host_prev = $data["name"];
            if ($data["h_scheduled_downtime_depth"] == 0) {
                $obj->XML->writeElement("hc", $obj->colorHostInService[$data["host_state"]]);
            } else {
                $obj->XML->writeElement("hc", $obj->general_opt['color_downtime']);
            }
            $obj->XML->writeElement("isMeta", $isMeta);
            $obj->XML->writeElement("hnl", CentreonUtils::escapeSecure(urlencode($data["name"])));
            $obj->XML->writeElement("hdn", $data["host_display_name"]);
            $obj->XML->startElement("hn");
            $obj->XML->writeAttribute("none", "0");
            $obj->XML->text(CentreonUtils::escapeSecure(urlencode($data["name"])), true, false);
            $obj->XML->endElement();

            $hostNotesUrl = "none";
            if ($data["h_notes_url"]) {
                $hostNotesUrl = str_replace("\$HOSTNAME\$", $data["name"], $data["h_notes_url"]);
                $hostNotesUrl = str_replace("\$HOSTALIAS\$", $data["alias"], $hostNotesUrl);
                $hostNotesUrl = str_replace("\$HOSTADDRESS\$", $data["address"], $hostNotesUrl);
                $hostNotesUrl = str_replace("\$INSTANCENAME\$", $data["instance_name"], $hostNotesUrl);
                $hostNotesUrl = str_replace("\$HOSTSTATE\$", $obj->statusHost[$data["host_state"]], $hostNotesUrl);
                $hostNotesUrl = str_replace("\$HOSTSTATEID\$", $data["host_state"], $hostNotesUrl);
                $hostNotesUrl = str_replace(
                    "\$INSTANCEADDRESS\$",
                    $instanceObj->getParam($data["instance_name"], "ns_ip_address"),
                    $hostNotesUrl
                );
            }
            $obj->XML->writeElement(
                "hnu",
                CentreonUtils::escapeSecure($obj->hostObj->replaceMacroInString($data["name"], $hostNotesUrl))
            );

            $hostActionUrl = "none";
            if ($data["h_action_url"]) {
                $hostActionUrl = str_replace("\$HOSTNAME\$", $data["name"], $data["h_action_url"]);
                $hostActionUrl = str_replace("\$HOSTALIAS\$", $data["alias"], $hostActionUrl);
                $hostActionUrl = str_replace("\$HOSTADDRESS\$", $data["address"], $hostActionUrl);
                $hostActionUrl = str_replace("\$INSTANCENAME\$", $data["instance_name"], $hostActionUrl);
                $hostActionUrl = str_replace("\$HOSTSTATE\$", $obj->statusHost[$data["host_state"]], $hostActionUrl);
                $hostActionUrl = str_replace("\$HOSTSTATEID\$", $data["host_state"], $hostActionUrl);
                $hostActionUrl = str_replace(
                    "\$INSTANCEADDRESS\$",
                    $instanceObj->getParam($data["instance_name"], "ns_ip_address"),
                    $hostActionUrl
                );
            }
            $obj->XML->writeElement(
                "hau",
                CentreonUtils::escapeSecure($obj->hostObj->replaceMacroInString($data["name"], $hostActionUrl))
            );

            $obj->XML->writeElement("hnn", CentreonUtils::escapeSecure($data["h_notes"]));
            $obj->XML->writeElement("hico", $data["h_icon_images"]);
            $obj->XML->writeElement("hip", CentreonUtils::escapeSecure($data["address"]));
            $obj->XML->writeElement("hdtm", $data["h_scheduled_downtime_depth"]);
            $obj->XML->writeElement(
                "hdtmXml",
                "./include/monitoring/downtime/xml/broker/makeXMLForDowntime.php?hid=" . $data['host_id']
            );
            $obj->XML->writeElement("hdtmXsl", "./include/monitoring/downtime/xsl/popupForDowntime.xsl");
            $obj->XML->writeElement(
                "hackXml",
                "./include/monitoring/acknowlegement/xml/broker/makeXMLForAck.php?hid=" . $data['host_id']
            );
            $obj->XML->writeElement("hackXsl", "./include/monitoring/acknowlegement/xsl/popupForAck.xsl");
            $obj->XML->writeElement("hid", $data["host_id"]);
        }
        $obj->XML->writeElement("hs", $data["host_state"]);

        /*
         * Add possibility to use display name
         */
        if ($isMeta) {
            $obj->XML->writeElement("sdn", CentreonUtils::escapeSecure($data["display_name"]), false);
        } else {
            $obj->XML->writeElement("sdn", CentreonUtils::escapeSecure($data["description"]), false);
        }
        $obj->XML->writeElement("sd", CentreonUtils::escapeSecure($data["description"]), false);

        $obj->XML->writeElement("sico", $data["icon_image"]);
        $obj->XML->writeElement("sdl", CentreonUtils::escapeSecure(urlencode($data["description"])));
        $obj->XML->writeElement("svc_id", $data["service_id"]);
        $obj->XML->writeElement("sc", $obj->colorService[$data["state"]]);
        $obj->XML->writeElement("cs", _($obj->statusService[$data["state"]]), false);
        $obj->XML->writeElement("ssc", $data["state"]);
        $obj->XML->writeElement("po", CentreonUtils::escapeSecure($pluginShortOuput));
        $obj->XML->writeElement(
            "ca",
            $data["current_attempt"] . "/" . $data["max_check_attempts"]
            . " (" . $obj->stateType[$data["state_type"]] . ")"
        );
        if (isset($data['criticality']) && $data['criticality'] != '' && isset($critCache[$data['service_id']])) {
            $obj->XML->writeElement("hci", 1); // has criticality
            $critData = $criticality->getData($critCache[$data['service_id']], true);
            $obj->XML->writeElement("ci", $media->getFilename($critData['icon_id']));
            $obj->XML->writeElement("cih", CentreonUtils::escapeSecure($critData['name']));
        } else {
            $obj->XML->writeElement("hci", 0); // has no criticality
        }
        $obj->XML->writeElement("ne", $data["notify"]);
        $obj->XML->writeElement("pa", $data["acknowledged"]);
        $obj->XML->writeElement("pc", $data["passive_checks"]);
        $obj->XML->writeElement("ac", $data["active_checks"]);
        $obj->XML->writeElement("eh", $data["event_handler_enabled"]);
        $obj->XML->writeElement("is", $data["flapping"]);
        $obj->XML->writeElement("dtm", $data["scheduled_downtime_depth"]);
        $obj->XML->writeElement(
            "dtmXml",
            "./include/monitoring/downtime/xml/broker/makeXMLForDowntime.php?hid="
            . $data['host_id'] . "&svc_id=" . $data['service_id']
        );
        $obj->XML->writeElement("dtmXsl", "./include/monitoring/downtime/xsl/popupForDowntime.xsl");
        $obj->XML->writeElement(
            "ackXml",
            "./include/monitoring/acknowlegement/xml/broker/makeXMLForAck.php?hid="
            . $data['host_id'] . "&svc_id=" . $data['service_id']
        );
        $obj->XML->writeElement("ackXsl", "./include/monitoring/acknowlegement/xsl/popupForAck.xsl");

        if ($data["notes"] != "") {
            $data["notes"] = str_replace("\$SERVICEDESC\$", $data["description"], $data["notes"]);
            $data["notes"] = str_replace("\$HOSTNAME\$", $data["name"], $data["notes"]);
            if (isset($data["alias"]) && $data["alias"]) {
                $data["notes"] = str_replace("\$HOSTALIAS\$", $data["alias"], $data["notes"]);
            }
            if (isset($data['address']) && $data['address']) {
                $data["notes"] = str_replace("\$HOSTADDRESS\$", $data['address'], $data["notes"]);
            }
            if (isset($data['instance_name']) && $data['instance_name']) {
                $data["notes"] = str_replace("\$INSTANCENAME\$", $data['instance_name'], $data['notes']);
                $data["notes"] = str_replace(
                    "\$INSTANCEADDRESS\$",
                    $instanceObj->getParam($data['instance_name'], 'ns_ip_address'),
                    $data["notes"]
                );
            }
            $obj->XML->writeElement("snn", CentreonUtils::escapeSecure($data["notes"]));
        } else {
            $obj->XML->writeElement("snn", 'none');
        }

        if ($data["notes_url"] != "") {
            $data["notes_url"] = str_replace("\$SERVICEDESC\$", $data["description"], $data["notes_url"]);
            $data["notes_url"] = str_replace("\$SERVICESTATEID\$", $data["state"], $data["notes_url"]);
            $data["notes_url"] = str_replace(
                "\$SERVICESTATE\$",
                $obj->statusService[$data["state"]],
                $data["notes_url"]
            );
            $data["notes_url"] = str_replace("\$HOSTNAME\$", $data["name"], $data["notes_url"]);
            if (isset($data["alias"]) && $data["alias"]) {
                $data["notes_url"] = str_replace("\$HOSTALIAS\$", $data["alias"], $data["notes_url"]);
            }
            if (isset($data['address']) && $data['address']) {
                $data["notes_url"] = str_replace("\$HOSTADDRESS\$", $data['address'], $data["notes_url"]);
            }
            if (isset($data['instance_name']) && $data['instance_name']) {
                $data["notes_url"] = str_replace("\$INSTANCENAME\$", $data['instance_name'], $data['notes_url']);
                $data["notes_url"] = str_replace(
                    "\$INSTANCEADDRESS\$",
                    $instanceObj->getParam($data['instance_name'], 'ns_ip_address'),
                    $data["notes_url"]
                );
            }
            $obj->XML->writeElement(
                "snu",
                CentreonUtils::escapeSecure(
                    $obj->serviceObj->replaceMacroInString($data["service_id"], $data["notes_url"])
                )
            );
        } else {
            $obj->XML->writeElement("snu", 'none');
        }

        if ($data["action_url"] != "") {
            $data["action_url"] = str_replace("\$SERVICEDESC\$", $data["description"], $data["action_url"]);
            $data["action_url"] = str_replace("\$SERVICESTATEID\$", $data["state"], $data["action_url"]);
            $data["action_url"] = str_replace(
                "\$SERVICESTATE\$",
                $obj->statusService[$data["state"]],
                $data["action_url"]
            );
            $data["action_url"] = str_replace("\$HOSTNAME\$", $data["name"], $data["action_url"]);
            if (isset($data["alias"]) && $data["alias"]) {
                $data["action_url"] = str_replace("\$HOSTALIAS\$", $data["alias"], $data["action_url"]);
            }
            if (isset($data['address']) && $data['address']) {
                $data["action_url"] = str_replace("\$HOSTADDRESS\$", $data['address'], $data["action_url"]);
            }
            if (isset($data['instance_name']) && $data['instance_name']) {
                $data["action_url"] = str_replace("\$INSTANCENAME\$", $data['instance_name'], $data['action_url']);
                $data["action_url"] = str_replace(
                    "\$INSTANCEADDRESS\$",
                    $instanceObj->getParam($data['instance_name'], 'ns_ip_address'),
                    $data["action_url"]
                );
            }
            $obj->XML->writeElement(
                "sau",
                CentreonUtils::escapeSecure(
                    $obj->serviceObj->replaceMacroInString($data["service_id"], $data["action_url"])
                )
            );
        } else {
            $obj->XML->writeElement("sau", 'none');
        }

        if ($data["notes"] != "") {
            $data["notes"] = str_replace("\$SERVICEDESC\$", $data["description"], $data["notes"]);
            $data["notes"] = str_replace("\$HOSTNAME\$", $data["name"], $data["notes"]);
            if (isset($data["alias"]) && $data["alias"]) {
                $data["notes"] = str_replace("\$HOSTALIAS\$", $data["alias"], $data["notes"]);
            }
            if (isset($data['address']) && $data['address']) {
                $data["notes"] = str_replace("\$HOSTADDRESS\$", $data['address'], $data["notes"]);
            }
            $obj->XML->writeElement("sn", CentreonUtils::escapeSecure($data["notes"]));
        } else {
            $obj->XML->writeElement("sn", 'none');
        }

        $obj->XML->writeElement("fd", $data["flap_detection"]);
        $obj->XML->writeElement("ha", $data["h_acknowledged"]);
        $obj->XML->writeElement("hae", $data["h_active_checks"]);
        $obj->XML->writeElement("hpe", $data["h_passive_checks"]);
        $obj->XML->writeElement("nc", $obj->GMT->getDate($dateFormat, $data["next_check"]));
        if ($data["last_check"] != 0) {
            $obj->XML->writeElement("lc", CentreonDuration::toString(time() - $data["last_check"]));
        } else {
            $obj->XML->writeElement("lc", "N/A");
        }
        $obj->XML->writeElement("d", $duration);
        $obj->XML->writeElement("rd", (time() - $data["last_state_change"]));
        $obj->XML->writeElement("last_hard_state_change", $hard_duration);

        /**
         * Get Service Graph index
         */
        if (!isset($graphs[$data["host_id"]]) || !isset($graphs[$data["host_id"]][$data["service_id"]])) {
            $request2 = "SELECT DISTINCT service_id, id
                FROM index_data, metrics
                WHERE metrics.index_id = index_data.id
                AND host_id = :hostId
                AND service_id = :serviceId
                AND index_data.hidden = '0'";
            $dbResult2 = $obj->DBC->prepare($request2);
            $dbResult2->bindValue(':hostId', $data["host_id"], \PDO::PARAM_INT);
            $dbResult2->bindValue(':serviceId', $data["service_id"], \PDO::PARAM_INT);
            $dbResult2->execute();

            while ($dataG = $dbResult2->fetch()) {
                if (!isset($graphs[$data["host_id"]])) {
                    $graphs[$data["host_id"]] = [];
                }
                $graphs[$data["host_id"]][$dataG["service_id"]] = $dataG["id"];
            }
            if (!isset($graphs[$data["host_id"]])) {
                $graphs[$data["host_id"]] = [];
            }
        }
        $obj->XML->writeElement(
            "svc_index",
            (isset($graphs[$data["host_id"]][$data["service_id"]]) ? $graphs[$data["host_id"]][$data["service_id"]] : 0)
        );
        $obj->XML->endElement();
    }
    $dbResult->closeCursor();
}

unset($data);
unset($host_status);

if (!$ct) {
    $obj->XML->writeElement("infos", "none");
}

$obj->XML->writeElement("sid", $obj->session_id);
$obj->XML->endElement();

// Send Header
$obj->header();

// Send XML
$obj->XML->output();
