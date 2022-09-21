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

ini_set("display_errors", "Off");

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

/**
 * @var Centreon $centreon
 */
$centreon = $_SESSION["centreon"];

/**
 * true: URIs will correspond to deprecated pages
 * false: URIs will correspond to new page (Resource Status)
 */
$useDeprecatedPages = $centreon->user->doesShowDeprecatedPages();

// Check Arguments From GET tab
$o = filter_input(INPUT_GET, 'o', FILTER_SANITIZE_STRING, ['options' => ['default' => 'h']]);
$p = filter_input(INPUT_GET, 'p', FILTER_VALIDATE_INT, ['options' => ['default' => 2]]);
$num = filter_input(INPUT_GET, 'num', FILTER_VALIDATE_INT, ['options' => ['default' => 0]]);
$limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT, ['options' => ['default' => 20]]);
//if instance value is not set, displaying all active pollers linked resources
$instance = filter_var($obj->defaultPoller ?? -1, FILTER_VALIDATE_INT);
$hSearch = filter_input(INPUT_GET, 'host_search', FILTER_SANITIZE_STRING, ['options' => ['default' => '']]);
$sgSearch = filter_input(INPUT_GET, 'sg_search', FILTER_SANITIZE_STRING, ['options' => ['default' => '']]);
$sort_type = filter_input(INPUT_GET, 'sort_type', FILTER_SANITIZE_STRING, ['options' => ['default' => 'host_name']]);
$order = isset($_GET['order']) && $_GET['order'] === "DESC" ? "DESC" : "ASC";

$kernel = \App\Kernel::createForWeb();
$resourceController = $kernel->getContainer()->get(
    \Centreon\Application\Controller\MonitoringResourceController::class
);

//saving bound values
$queryValues = [];
$queryValues2 = [];

// Backup poller selection
$obj->setInstanceHistory($instance);

$_SESSION['monitoring_service_groups'] = $sgSearch;

// Filter on state
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
$query = "SELECT SQL_CALC_FOUND_ROWS DISTINCT sg.servicegroup_id, h.host_id
    FROM servicegroups sg, services_servicegroups sgm, hosts h, services s ";

if (!$obj->is_admin) {
    $query .= ", centreon_acl ";
}

$query .= "WHERE sgm.servicegroup_id = sg.servicegroup_id
    AND sgm.host_id = h.host_id
    AND h.host_id = s.host_id
    AND sgm.service_id = s.service_id ";

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
    $query .= " AND sg.name = :sgSearch ";
    $queryValues['sgSearch'] = [
        \PDO::PARAM_STR => $sgSearch
    ];
}

// Host search
$h_search = '';
if ($hSearch != "") {
    $h_search .= " AND h.name LIKE :hSearch ";
    // as this partial request is used in two queries, we need to bound it two times using two arrays
    // to avoid incoherent number of bound variables in the second query
    $queryValues['hSearch'] = $queryValues2['hSearch'] = [
        \PDO::PARAM_STR => "%" . $hSearch . "%"
    ];
}
$query .= $h_search . $s_search;

// Poller search
if ($instance != -1) {
    $query .= " AND h.instance_id = :instance ";
    $queryValues['instance'] = [
        \PDO::PARAM_INT => $instance
    ];
}
$query .= " ORDER BY sg.name " . $order . " LIMIT :numLimit, :limit";
$queryValues['numLimit'] = [
    \PDO::PARAM_INT => (int)($num * $limit)
];
$queryValues['limit'] = [
    \PDO::PARAM_INT => (int)$limit
];

$dbResult = $obj->DBC->prepare($query);
foreach ($queryValues as $bindId => $bindData) {
    foreach ($bindData as $bindType => $bindValue) {
        $dbResult->bindValue($bindId, $bindValue, $bindType);
    }
}
$dbResult->execute();
$numRows = $obj->DBC->query("SELECT FOUND_ROWS()")->fetchColumn();

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
$aTab = [];
$sg_search = "";
$aTab = [];
if ($numRows > 0) {
    $sg_search .= "AND (";
    $servicegroups = [];
    while ($row = $dbResult->fetch()) {
        $servicesgroups[$row['servicegroup_id']][] = $row['host_id'];
    }
    $servicegroupsSql1 = [];
    foreach ($servicesgroups as $key => $value) {
        $hostsSql = [];
        foreach ($value as $hostId) {
            $hostsSql[] = $hostId;
        }
        $servicegroupsSql1[] = "(sg.servicegroup_id = " . $key .
            " AND h.host_id IN (" . implode(',', $hostsSql) . ")) ";
    }
    $sg_search .= implode(" OR ", $servicegroupsSql1);
    $sg_search .= ") ";
    if ($sgSearch != "") {
        $sg_search .= "AND sg.name = :sgSearch";
        $queryValues2['sgSearch'] = [
            \PDO::PARAM_STR => $sgSearch
        ];
    }

    $query2 = "SELECT SQL_CALC_FOUND_ROWS DISTINCT sg.name AS sg_name,
        sg.name AS alias,
        h.name AS host_name,
        h.state AS host_state,
        h.icon_image, h.host_id, s.state, s.description, s.service_id,
        (CASE s.state WHEN 0 THEN 3 WHEN 2 THEN 0 WHEN 3 THEN 2 ELSE s.state END) AS tri
        FROM servicegroups sg, services_servicegroups sgm, services s, hosts h ";

    if (!$obj->is_admin) {
        $query2 .= ", centreon_acl ";
    }

    $query2 .= "WHERE sgm.servicegroup_id = sg.servicegroup_id
        AND sgm.host_id = h.host_id
        AND h.host_id = s.host_id
        AND sgm.service_id = s.service_id ";

    // filter elements with acl (host, service, servicegroup)
    if (!$obj->is_admin) {
        $query2 .= $obj->access->queryBuilder("AND", "h.host_id", "centreon_acl.host_id") .
            $obj->access->queryBuilder("AND", "h.host_id", "centreon_acl.host_id") .
            $obj->access->queryBuilder("AND", "s.service_id", "centreon_acl.service_id") .
            $obj->access->queryBuilder("AND", "group_id", $obj->access->getAccessGroupsString()) . " " .
            $obj->access->queryBuilder("AND", "sg.servicegroup_id", $obj->access->getServiceGroupsString("ID")) . " ";
    }
    $query2 .= $sg_search . $h_search . $s_search . " ORDER BY sg_name, tri ASC";

    $dbResult = $obj->DBC->prepare($query2);
    foreach ($queryValues2 as $bindId => $bindData) {
        foreach ($bindData as $bindType => $bindValue) {
            $dbResult->bindValue($bindId, $bindValue, $bindType);
        }
    }
    $dbResult->execute();

    $ct = 0;
    $sg = "";
    $h = "";
    $flag = 0;
    $count = 0;

    while ($tab = $dbResult->fetch()) {
        if (!isset($aTab[$tab["sg_name"]])) {
            $aTab[$tab["sg_name"]] = array(
                'sgn' => CentreonUtils::escapeSecure($tab["sg_name"]),
                'o' => $ct,
                'host' => []
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
                'hs' => _($obj->statusHost[$tab["host_state"]]),
                'hn' => CentreonUtils::escapeSecure($tab["host_name"]),
                'hico' => $icone,
                'hnl' => CentreonUtils::escapeSecure(urlencode($tab["host_name"])),
                'hid' => $tab["host_id"],
                "hcount" => $count,
                "hc" => $obj->colorHost[$tab["host_state"]],
                'service' => []
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
        $obj->XML->writeElement(
            "h_details_uri",
            $useDeprecatedPages
                ? 'main.php?p=20202&o=hd&host_name=' . $host['hn']
                : $resourceController->buildHostDetailsUri($host['hid'])
        );
        $obj->XML->writeElement(
            "s_listing_uri",
            $useDeprecatedPages
                ? 'main.php?o=svc&p=20201&statusFilter=&host_search=' . $host['hn']
                : $resourceController->buildListingUri([
                    'filter' => json_encode([
                        'criterias' => [
                            'search' => 'h.name:^' . $host['hn'] . '$',
                        ],
                    ]),
                ])
        );
        foreach ($host['service'] as $service) {
            $obj->XML->startElement("svc");
            $obj->XML->writeElement("sn", $service['sn']);
            $obj->XML->writeElement("snl", $service['snl']);
            $obj->XML->writeElement("sc", $service['sc']);
            $obj->XML->writeElement("svc_id", $service['svc_id']);
            $obj->XML->writeElement(
                "s_details_uri",
                $useDeprecatedPages
                    ? 'main.php?o=svcd&p=202&host_name='
                        . $host['hn']
                        . '&amp;service_description='
                        . $service['sn']
                    : $resourceController->buildServiceDetailsUri($host['hid'], $service['svc_id'])
            );
            $obj->XML->endElement();
        }
        $obj->XML->writeElement("chartIcon", returnSvg("www/img/icons/chart.svg", "var(--icons-fill-color)", 18, 18));
        $obj->XML->writeElement("viewIcon", returnSvg("www/img/icons/view.svg", "var(--icons-fill-color)", 18, 18));
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
