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

ini_set("display_errors", "Off");

require_once realpath(__DIR__ . "/../../../../../../config/centreon.config.php");
require_once realpath(__DIR__ . "/../../../../../../bootstrap.php");

include_once _CENTREON_PATH_ . "www/class/centreonUtils.class.php";

include_once _CENTREON_PATH_ . "www/class/centreonXMLBGRequest.class.php";
include_once _CENTREON_PATH_ . "www/include/monitoring/status/Common/common-Func.php";
include_once _CENTREON_PATH_ . "www/include/common/common-Func.php";
include_once _CENTREON_PATH_ . "www/class/centreonService.class.php";

// Create XML Request Objects
 CentreonSession::start();
$obj = new CentreonXMLBGRequest($dependencyInjector, session_id(), 1, 1, 0, 1);
$svcObj = new CentreonService($obj->DB);

if (!isset($obj->session_id) || !CentreonSession::checkSession($obj->session_id, $obj->DB)) {
    print "Bad Session ID";
    exit();
}

// Set Default Poller
$obj->getDefaultFilters();

// Check Arguments From GET tab
$o = $obj->checkArgument("o", $_GET, "h");
$p = $obj->checkArgument("p", $_GET, "2");
$nc = $obj->checkArgument("nc", $_GET, "0");
$num = $obj->checkArgument("num", $_GET, 0);
$limit = $obj->checkArgument("limit", $_GET, 20);
$instance = $obj->checkArgument("instance", $_GET, $obj->defaultPoller);
$hostgroups = $obj->checkArgument("hostgroups", $_GET, $obj->defaultHostgroups);
$hSearch = $obj->checkArgument("host_search", $_GET, "");
$sgSearch = $obj->checkArgument("sg_search", $_GET, "");
$sort_type = $obj->checkArgument("sort_type", $_GET, "host_name");
$order = $obj->checkArgument("order", $_GET, "ASC");
$dateFormat = $obj->checkArgument("date_time_format_status", $_GET, "Y/m/d H:i:s");
$queryValues = array();

// Backup poller selection
$obj->setInstanceHistory($instance);

$_SESSION['monitoring_service_groups'] = $sgSearch;

// Prepare pagination
$s_search = "";

// Display service problems
if ($o == "svcgridSG_pb" || $o == "svcOVSG_pb") {
    $s_search .= " AND s.state != 0 AND s.state != 4 ";
}

// Display acknowledged services
if ($o == "svcgridSG_ack_1" || $o == "svcOVSG_ack_1") {
    $s_search .= " AND s.acknowledged = '1' ";
}

// Display not acknowledged services
if ($o == "svcgridSG_ack_0" || $o == "svcOVSG_ack_0") {
    $s_search .= " AND s.state != 0 AND s.state != 4 AND s.acknowledged = 0 ";
}

// this query allows to manage pagination
$query = "SELECT SQL_CALC_FOUND_ROWS DISTINCT sg.servicegroup_id, h.host_id "
    . "FROM servicegroups sg, services_servicegroups sgm, hosts h, services s ";

if (!$obj->is_admin) {
    $query .= ", centreon_acl ";
}

$query .= "WHERE sgm.servicegroup_id = sg.servicegroup_id "
    . "AND sgm.host_id = h.host_id "
    . "AND sgm.service_id = s.service_id ";

// filter elements with acl (host, service, servicegroup)
if (!$obj->is_admin) {
    $query .= $obj->access->queryBuilder("AND", "h.host_id", "centreon_acl.host_id")
        . $obj->access->queryBuilder("AND", "h.host_id", "centreon_acl.host_id")
        . $obj->access->queryBuilder("AND", "s.service_id", "centreon_acl.service_id")
        . $obj->access->queryBuilder("AND", "group_id", $obj->access->getAccessGroupsString()) . " "
        . $obj->access->queryBuilder("AND", "sg.servicegroup_id", $obj->access->getServiceGroupsString("ID")) . " ";
}

// Servicegroup search
if ($sgSearch != "") {
    $query .= "AND sg.name = :sgSearch ";
    $queryValues[':sgSearch'] = [
        PDO::PARAM_STR => $sgSearch
    ];
}

// Host search
$h_search = '';
if ($hSearch != "") {
    $h_search .= "AND h.name like :hSearch ";
    $queryValues[':hSearch'] = [
        PDO::PARAM_STR => "%" . $hSearch . "%"
    ];
}
$query .= $h_search;

// Service search
$query .= $s_search;

// Poller search
if ($instance != -1) {
    $query .= " AND h.instance_id = :instance ";
    $queryValues[':instance'] = [
        PDO::PARAM_INT => $instance
    ];
}
$query .= "ORDER BY sg.name :order "
    . "LIMIT :numLimit, :limit";
$queryValues[':order'] = [
    PDO::PARAM_INT => $instance
];
$queryValues[':numLimit'] = [
    PDO::PARAM_INT => (int) ($num * $limit)
];
$queryValues[':limit'] = [
    PDO::PARAM_INT => (int) $limit
];

$DBRESULT = $obj->DBC->prepare($query);
foreach ($queryValues as $bindId => $bindData) {
    foreach ($bindData as $bindType => $bindValue) {
        $DBRESULT->bindValue($bindId, $bindValue, $bindType);
    }
}
$DBRESULT->execute();
$numRows = $DBRESULT->rowCount();

// Create XML Flow
$obj->XML = new CentreonXML();
$obj->XML->startElement("reponse");
$obj->XML->startElement("i");
$obj->XML->writeElement("numrows", $numRows);
$obj->XML->writeElement("num", $num);
$obj->XML->writeElement("limit", $limit);
$obj->XML->writeElement("host_name", _("Hosts"), 0);
$obj->XML->writeElement("services", _("Services"), 0);
$obj->XML->writeElement("p", $p);
$obj->XML->writeElement("s", "1");
$obj->XML->endElement();

// Construct query for servicegroups search
$aTab = array();
$sg_search = "";
$aTab = array();
if ($numRows > 0) {
    $sg_search .= "AND (";
    $servicegroups = array();
    while ($row = $DBRESULT->fetch()) {
        $servicesgroups[$row['servicegroup_id']][] = $row['host_id'];
    }
    $servicegroupsSql1 = array();
    foreach ($servicesgroups as $key => $value) {
        $hostsSql = array();
        foreach ($value as $hostId) {
            $hostsSql[] = $hostId;
        }
        $servicegroupsSql1[] = "(sg.servicegroup_id = " . $key . " AND h.host_id IN (" .
            implode(',', $hostsSql) . ")) ";
    }
    $sg_search .= implode(" OR ", $servicegroupsSql1);
    $sg_search .= ") ";
    if ($sgSearch != "") {
        $sg_search .= "AND sg.name = '" . $sgSearch . "' ";
    }

    $query2 = "SELECT SQL_CALC_FOUND_ROWS DISTINCT sg.name AS sg_name, sg.name as alias, h.name as host_name, "
        . "h.state as host_state, h.icon_image, h.host_id, s.state, s.description, s.service_id, "
        . "(case s.state when 0 then 3 when 2 then 0 when 3 then 2 else s.state END) as tri "
        . "FROM servicegroups sg, services_servicegroups sgm, services s, hosts h ";

    if (!$obj->is_admin) {
        $query2 .= ", centreon_acl ";
    }

    $query2 .= "WHERE sgm.servicegroup_id = sg.servicegroup_id "
        . "AND sgm.host_id = h.host_id "
        . "AND sgm.service_id = s.service_id ";

    // filter elements with acl (host, service, servicegroup)
    if (!$obj->is_admin) {
        $query2 .= $obj->access->queryBuilder("AND", "h.host_id", "centreon_acl.host_id") .
            $obj->access->queryBuilder("AND", "h.host_id", "centreon_acl.host_id") .
            $obj->access->queryBuilder("AND", "s.service_id", "centreon_acl.service_id") .
            $obj->access->queryBuilder("AND", "group_id", $obj->access->getAccessGroupsString()) . " " .
            $obj->access->queryBuilder("AND", "sg.servicegroup_id", $obj->access->getServiceGroupsString("ID")) . " ";
    }
    $query2 .= $sg_search . $h_search . $s_search . " ORDER BY sg_name, tri ASC";

    $DBRESULT = $obj->DBC->query($query2);

    $ct = 0;
    $sg = "";
    $h = "";
    $flag = 0;
    $count = 0;

    while ($tab = $DBRESULT->fetch()) {
        if (!isset($aTab[$tab["sg_name"]])) {
            $aTab[$tab["sg_name"]] = array(
                'sgn' => CentreonUtils::escapeSecure($tab["sg_name"]),
                'o' => $ct,
                'host' => array()
            );
        }

        if (!isset($aTab[$tab["sg_name"]]['host'][$tab["host_name"]])) {
            $count++;
            if ($tab["icon_image"]) {
                $icone = $tab["icon_image"];
            } else {
                $icone = "none";
            }
            $aTab[$tab["sg_name"]]['host'][$tab["host_name"]] = array(
                'h' => $tab["host_name"],
                'hs' => $tab["host_state"],
                'hn' => CentreonUtils::escapeSecure($tab["host_name"]),
                'hico' => $icone,
                'hnl' => CentreonUtils::escapeSecure(urlencode($tab["host_name"])),
                'hid' => $tab["host_id"],
                "hcount" => $count,
                "hs" => _($obj->statusHost[$tab["host_state"]]),
                "hc" => $obj->colorHost[$tab["host_state"]],
                'service' => array()
            );
        }

        if (!isset($aTab[$tab["sg_name"]]['host'][$tab["host_name"]]['service'][$tab['description']])) {
            $aTab[$tab["sg_name"]]['host'][$tab["host_name"]]['service'][$tab['description']] = array(

                "sn" => CentreonUtils::escapeSecure($tab['description']),
                "snl" => CentreonUtils::escapeSecure(urlencode($tab['description'])),
                "sc" => $obj->colorService[$tab['state']],
                "svc_id" => $tab['service_id']
            );
        }
        $ct++;
    }
}

foreach ($aTab as $key => $element) {
    $obj->XML->startElement("sg");
    $obj->XML->writeElement("sgn", $element['sgn']);
    $obj->XML->writeElement("o", $element['o']);
    foreach ($element['host'] as $host) {
        $obj->XML->startElement("h");
        $obj->XML->writeAttribute("class", $obj->getNextLineClass());
        $obj->XML->writeElement("hn", $host['hn'], false);
        $obj->XML->writeElement("hico", $host['hico']);
        $obj->XML->writeElement("hnl", $host['hnl']);
        $obj->XML->writeElement("hid", $host['hid']);
        $obj->XML->writeElement("hcount", $host['hcount']);
        $obj->XML->writeElement("hs", $host['hs']);
        $obj->XML->writeElement("hc", $host['hc']);
        foreach ($host['service'] as $service) {
            $obj->XML->startElement("svc");
            $obj->XML->writeElement("sn", $service['sn']);
            $obj->XML->writeElement("snl", $service['snl']);
            $obj->XML->writeElement("sc", $service['sc']);
            $obj->XML->writeElement("svc_id", $service['svc_id']);
            $obj->XML->endElement();
        }
        $obj->XML->endElement();
        $count++;
    }

    $obj->XML->endElement();
}

$obj->XML->endElement();

// Send Header
$obj->header();

// Send XML
$obj->XML->output();
