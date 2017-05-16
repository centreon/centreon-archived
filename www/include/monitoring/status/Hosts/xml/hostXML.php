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

require_once realpath(dirname(__FILE__) . "/../../../../../../config/centreon.config.php");
include_once _CENTREON_PATH_ . "www/class/centreonXMLBGRequest.class.php";
include_once _CENTREON_PATH_ . "www/class/centreonInstance.class.php";
include_once _CENTREON_PATH_ . "www/class/centreonCriticality.class.php";
include_once _CENTREON_PATH_ . "www/class/centreonMedia.class.php";
include_once _CENTREON_PATH_ . "www/include/common/common-Func.php";
include_once _CENTREON_PATH_ . "www/class/centreonUtils.class.php";

/*
 * Create XML Request Objects
 */
CentreonSession::start(1);
$obj = new CentreonXMLBGRequest(session_id(), 1, 1, 0, 1);

if (isset($_SESSION['centreon'])) {
    $centreon = $_SESSION['centreon'];
} else {
    exit;
}
$criticality = new CentreonCriticality($obj->DB);
$instanceObj = new CentreonInstance($obj->DB);
$media = new CentreonMedia($obj->DB);

if (isset($obj->session_id) && CentreonSession::checkSession($obj->session_id, $obj->DB)) {
    ;
} else {
    print "Bad Session ID";
    exit();
}

/*
 * Set Default Poller
 */
$obj->getDefaultFilters();

/*
 *  Check Arguments from GET
 */
$o          = $obj->checkArgument("o", $_GET, "h");
$p          = $obj->checkArgument("p", $_GET, "2");
$num        = $obj->checkArgument("num", $_GET, 0);
$limit      = $obj->checkArgument("limit", $_GET, 20);
$instance   = $obj->checkArgument("instance", $_GET, $obj->defaultPoller);
$hostgroups = $obj->checkArgument("hostgroups", $_GET, $obj->defaultHostgroups);
$search     = $obj->checkArgument("search", $_GET, "");
$order      = $obj->checkArgument("order", $_GET, "ASC");
$dateFormat = $obj->checkArgument("date_time_format_status", $_GET, "Y/m/d H:i:s");

$statusHost = $obj->checkArgument("statusHost", $_GET, "");
$statusFilter = $obj->checkArgument("statusFilter", $_GET, "");

if (isset($_GET['sort_type']) && $_GET['sort_type'] == "host_name") {
    $sort_type = "name";
} else {
    if ($o == "hpb" || $o == "h_unhandled") {
        $sort_type  = $obj->checkArgument("sort_type", $_GET, "");
    } else {
        $sort_type  = $obj->checkArgument("sort_type", $_GET, "host_name");
    }
}
$criticality_id = $obj->checkArgument('criticality', $_GET, $obj->defaultCriticality);

/* Store in session the last type of call */
if (isset($_GET['sSetOrderInMemory']) && $_GET['sSetOrderInMemory'] == "1") {
    $_SESSION['monitoring_host_status'] = $statusHost;
    $_SESSION['monitoring_host_status_filter'] = $statusFilter;
}
  
/*
 * Backup poller selection
 */
$obj->setInstanceHistory($instance);
$obj->setHostGroupsHistory($hostgroups);
$obj->setCriticality($criticality_id);

/*
 * Get Host status
 */
$rq1 =  " SELECT SQL_CALC_FOUND_ROWS DISTINCT h.state," .
    " h.acknowledged, " .
    " h.passive_checks," .
    " h.active_checks," .
    " h.notify," .
    " h.last_state_change," .
    " h.last_hard_state_change," .
    " h.output," .
    " h.last_check, " .
    " h.address," .
    " h.name," .
    " h.alias," .
    " h.action_url," .
    " h.notes_url," .
    " h.notes," .
    " h.icon_image," .
    " h.icon_image_alt," .
    " h.max_check_attempts," .
    " h.state_type," .
    " h.check_attempt, " .
    " h.scheduled_downtime_depth, " .
    " h.host_id, " .
    " h.flapping, " .
    " hph.parent_id as is_parent, " .
    " i.name as instance_name, " .
    " cv.value as criticality, ".
    " cv.value IS NULL as isnull ";
$rq1 .= " FROM instances i, ";
if (!$obj->is_admin) {
    $rq1 .= " centreon_acl, ";
}
if ($hostgroups) {
    $rq1 .= " hosts_hostgroups hhg, hostgroups hg, ";
}
if ($criticality_id) {
    $rq1 .= "customvariables cvs, ";
}
$rq1 .= " `hosts` h ";
$rq1 .= " LEFT JOIN hosts_hosts_parents hph ";
$rq1 .= " ON hph.parent_id = h.host_id ";

$rq1 .= " LEFT JOIN `customvariables` cv ";
$rq1 .= " ON (cv.host_id = h.host_id AND cv.service_id IS NULL AND cv.name = 'CRITICALITY_LEVEL') ";

$rq1 .= " WHERE h.name NOT LIKE '_Module_%'";
$rq1 .= " AND h.instance_id = i.instance_id ";

if ($criticality_id) {
    $rq1 .= " AND h.host_id = cvs.host_id
              AND cvs.name = 'CRITICALITY_ID'
              AND cvs.service_id IS NULL
              AND cvs.value = '".$obj->DBC->escape($criticality_id)."' ";
}

if (!$obj->is_admin) {
    $rq1 .= " AND h.host_id = centreon_acl.host_id " . $obj->access->queryBuilder("AND", "centreon_acl.group_id", $obj->grouplistStr);
}
if ($search != "") {
    $rq1 .= " AND (h.name LIKE '%" . CentreonDB::escape($search) . "%' OR h.alias LIKE '%" . CentreonDB::escape($search) . "%' OR h.address LIKE '%" . CentreonDB::escape($search) . "%') ";
}

if ($statusHost == "h_unhandled") {
    $rq1 .= " AND h.state = 1 ";
    $rq1 .= " AND h.state_type = '1'";
    $rq1 .= " AND h.acknowledged = 0";
    $rq1 .= " AND h.scheduled_downtime_depth = 0";
} elseif ($statusHost == "hpb") {
     $rq1 .= " AND (h.state != 0 AND h.state != 4) ";
}

if ($statusFilter == "up") {
    $rq1 .= " AND h.state = 0 ";
} elseif ($statusFilter == "down") {
    $rq1 .= " AND h.state = 1 ";
} elseif ($statusFilter == "unreachable") {
    $rq1 .= " AND h.state = 2 ";
} elseif ($statusFilter == "pending") {
    $rq1 .= " AND h.state = 4 ";
}

if ($hostgroups) {
    $rq1 .= " AND h.host_id = hhg.host_id AND hg.hostgroup_id IN ($hostgroups) AND hhg.hostgroup_id = hg.hostgroup_id";
}

if ($instance != -1 && !empty($instance)) {
    $rq1 .= " AND h.instance_id = ".$instance;
}
$rq1 .= " AND h.enabled = 1 ";
switch ($sort_type) {
    case 'name':
        $rq1 .= " ORDER BY h.name ". $order;
        break;
    case 'current_state':
        $rq1 .= " ORDER BY h.state ". $order.",h.name ";
        break;
    case 'last_state_change':
        $rq1 .= " ORDER BY h.last_state_change ". $order.",h.name ";
        break;
    case 'last_hard_state_change':
        $rq1 .= " ORDER BY h.last_hard_state_change ". $order.",h.name ";
        break;
    case 'last_check':
        $rq1 .= " ORDER BY h.last_check ". $order.",h.name ";
        break;
    case 'current_check_attempt':
        $rq1 .= " ORDER BY h.check_attempt ". $order.",h.name ";
        break;
    case 'ip':
        # Not SQL portable
        $rq1 .= " ORDER BY IFNULL(inet_aton(h.address), h.address) ". $order.",h.name ";
        break;
    case 'plugin_output':
        $rq1 .= " ORDER BY h.output ". $order.",h.name ";
        break;
    case 'criticality_id':
        $rq1 .= " ORDER BY isnull $order, criticality $order, h.name ";
        break;
    default:
        $rq1 .= " ORDER BY isnull $order, criticality $order, h.name ";
        break;
}
$rq1 .= " LIMIT ".($num * $limit).",".$limit;

$ct = 0;
$flag = 0;
$DBRESULT = $obj->DBC->query($rq1);
$numRows = $obj->DBC->numberRows();

/**
 * Get criticality ids
 */
$critRes = $obj->DBC->query("SELECT value, host_id 
                                     FROM customvariables
                                     WHERE name = 'CRITICALITY_ID'
                                     AND service_id IS NULL");
$criticalityUsed = 0;
$critCache = array();
if ($critRes->numRows()) {
    $criticalityUsed = 1;
    while ($critRow = $critRes->fetchRow()) {
        $critCache[$critRow['host_id']] = $critRow['value'];
    }
}

$obj->XML->startElement("reponse");
$obj->XML->startElement("i");
$obj->XML->writeElement("numrows", $numRows);
$obj->XML->writeElement("num", $num);
$obj->XML->writeElement("limit", $limit);
$obj->XML->writeElement("p", $p);
$obj->XML->writeElement("o", $o);
$obj->XML->writeElement("sort_type", $sort_type);
$obj->XML->writeElement("hard_state_label", _("Hard State Duration"));
$obj->XML->writeElement("parent_host_label", _("Top Priority Hosts"));
$obj->XML->writeElement("regular_host_label", _("Secondary Priority Hosts"));
$obj->XML->writeElement("http_action_link", _("HTTP Action Link"));
$obj->XML->writeElement("notif_disabled", _("Notification is disabled"));
$obj->XML->writeElement("use_criticality", $criticalityUsed);
$obj->XML->endElement();

$delimInit = 0;
while ($data = $DBRESULT->fetchRow()) {
    if ($data["last_state_change"] > 0 && time() > $data["last_state_change"]) {
        $duration = CentreonDuration::toString(time() - $data["last_state_change"]);
    } else {
        $duration = "N/A";
    }

    if (($data["last_hard_state_change"] > 0) && ($data["last_hard_state_change"] >= $data["last_state_change"])) {
        $hard_duration = CentreonDuration::toString(time() - $data["last_hard_state_change"]);
    } elseif ($data["last_hard_state_change"] > 0) {
        $hard_duration = " N/A ";
    } else {
        $hard_duration = "N/A";
    }

    if ($data['is_parent']) {
        $delimInit = 1;
    }

    $class = null;
    if ($data["scheduled_downtime_depth"] > 0) {
        $class = "line_downtime";
    } elseif ($data["state"] == 1) {
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
    $obj->XML->writeElement("hc", $obj->colorHost[$data["state"]]);
    $obj->XML->writeElement("f", $flag);
    $obj->XML->writeElement("hid", $data["host_id"]);
    $obj->XML->writeElement("hn", CentreonUtils::escapeSecure($data["name"]), false);
    $obj->XML->writeElement("hnl", CentreonUtils::escapeSecure(urlencode($data["name"])));
    $obj->XML->writeElement("a", ($data["address"] ? CentreonUtils::escapeSecure($data["address"]) : "N/A"));
    $obj->XML->writeElement("ou", ($data["output"] ? CentreonUtils::escapeSecure($data["output"]) : "N/A"));
    $obj->XML->writeElement("lc", ($data["last_check"] != 0 ? CentreonDuration::toString(time() - $data["last_check"]) : "N/A"));
    $obj->XML->writeElement("cs", _($obj->statusHost[$data["state"]]), false);
    $obj->XML->writeElement("pha", $data["acknowledged"]);
    $obj->XML->writeElement("pce", $data["passive_checks"]);
    $obj->XML->writeElement("ace", $data["active_checks"]);
    $obj->XML->writeElement("lsc", ($duration ? $duration : "N/A"));
    $obj->XML->writeElement("lhs", ($hard_duration ? $hard_duration : "N/A"));
    $obj->XML->writeElement("ha", $data["acknowledged"]);
    $obj->XML->writeElement("hdtm", $data["scheduled_downtime_depth"]);
    $obj->XML->writeElement("hdtmXml", "./include/monitoring/downtime/xml/broker/makeXMLForDowntime.php?hid=".$data['host_id']);
    $obj->XML->writeElement("hdtmXsl", "./include/monitoring/downtime/xsl/popupForDowntime.xsl");
    $obj->XML->writeElement("hackXml", "./include/monitoring/acknowlegement/xml/broker/makeXMLForAck.php?hid=".$data['host_id']);
    $obj->XML->writeElement("hackXsl", "./include/monitoring/acknowlegement/xsl/popupForAck.xsl");
    $obj->XML->writeElement("hae", $data["active_checks"]);
    $obj->XML->writeElement("hpe", $data["passive_checks"]);
    $obj->XML->writeElement("ne", $data["notify"]);
    $obj->XML->writeElement("tr", $data["check_attempt"]."/".$data["max_check_attempts"]." (".$obj->stateType[$data["state_type"]].")");

    if (isset($data['criticality']) && $data['criticality'] != '' && isset($critCache[$data['host_id']])) {
        $obj->XML->writeElement("hci", 1); // has criticality
        $critData = $criticality->getData($critCache[$data['host_id']]);
        $obj->XML->writeElement("ci", $media->getFilename($critData['icon_id']));
        $obj->XML->writeElement("cih", CentreonUtils::escapeSecure($critData['name']));
    } else {
        $obj->XML->writeElement("hci", 0); // has no criticality
    }
    $obj->XML->writeElement("ico", $data["icon_image"]);
    $obj->XML->writeElement("isp", $data["is_parent"] ? 1 : 0);
    $obj->XML->writeElement("isf", $data["flapping"]);
    $parenth = 0;
    if ($ct === 1 && $data['is_parent']) {
        $parenth = 1;
    }
    if (!$sort_type && $delimInit && !$data['is_parent']) {
        $delim = 1;
        $delimInit = 0;
    } else {
        $delim = 0;
    }
    $obj->XML->writeElement("parenth", $parenth);
    $obj->XML->writeElement("delim", $delim);

    $hostObj = new CentreonHost($obj->DB);
    if ($data["notes"] != "") {
        $obj->XML->writeElement("hnn", CentreonUtils::escapeSecure($hostObj->replaceMacroInString($data["name"], str_replace("\$HOSTNAME\$", $data["name"], str_replace("\$HOSTADDRESS\$", $data["address"], $data["notes"])))));
    } else {
        $obj->XML->writeElement("hnn", "none");
    }

    if ($data["notes_url"] != "") {
        $str = $data['notes_url'];
        $str = str_replace("\$HOSTNAME\$", $data['name'], $str);
        $str = str_replace("\$HOSTALIAS\$", $data['alias'], $str);
        $str = str_replace("\$HOSTADDRESS\$", $data['address'], $str);
        $str = str_replace("\$HOSTNOTES\$", $data['notes'], $str);
        $str = str_replace("\$INSTANCENAME\$", $data['instance_name'], $str);

        $str = str_replace("\$HOSTSTATEID\$", $data['state'], $str);
        $str = str_replace("\$HOSTSTATE\$", $obj->statusHost[$data['state']], $str);

        $str = str_replace("\$INSTANCEADDRESS\$", $instanceObj->getParam($data['instance_name'], 'ns_ip_address'), $str);
        $obj->XML->writeElement("hnu", CentreonUtils::escapeSecure($hostObj->replaceMacroInString($data["notes_url"], $str)));
    } else {
        $obj->XML->writeElement("hnu", "none");
    }

    if ($data["action_url"] != "") {
        $str = $data['action_url'];
        $str = str_replace("\$HOSTNAME\$", $data['name'], $str);
        $str = str_replace("\$HOSTALIAS\$", $data['alias'], $str);
        $str = str_replace("\$HOSTADDRESS\$", $data['address'], $str);
        $str = str_replace("\$HOSTNOTES\$", $data['notes'], $str);
        $str = str_replace("\$INSTANCENAME\$", $data['instance_name'], $str);

        $str = str_replace("\$HOSTSTATEID\$", $data['state'], $str);
        $str = str_replace("\$HOSTSTATE\$", $obj->statusHost[$data['state']], $str);

        $str = str_replace("\$INSTANCEADDRESS\$", $instanceObj->getParam($data['instance_name'], 'ns_ip_address'), $str);
        $obj->XML->writeElement("hau", CentreonUtils::escapeSecure($hostObj->replaceMacroInString($data["name"], $str)));
    } else {
        $obj->XML->writeElement("hau", "none");
    }

    $obj->XML->endElement();
}
$DBRESULT->free();

if (!$ct) {
    $obj->XML->writeElement("infos", "none");
}
$obj->XML->endElement();

$obj->header();
$obj->XML->output();
