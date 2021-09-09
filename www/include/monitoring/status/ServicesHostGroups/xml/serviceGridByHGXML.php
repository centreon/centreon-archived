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

require_once realpath(__DIR__ . "/../../../../../../bootstrap.php");
include_once _CENTREON_PATH_ . "www/class/centreonUtils.class.php";
include_once _CENTREON_PATH_ . "www/class/centreonXMLBGRequest.class.php";
include_once _CENTREON_PATH_ . "www/include/monitoring/status/Common/common-Func.php";
include_once _CENTREON_PATH_ . "www/include/common/common-Func.php";
include_once _CENTREON_PATH_ . "www/class/centreonService.class.php";

// Create XML Request Objects
CentreonSession::start(1);
$obj = new CentreonXMLBGRequest($dependencyInjector, session_id(), 1, 1, 0, 1);
$svcObj = new CentreonService($obj->DB);

if (!isset($obj->session_id) || !CentreonSession::checkSession($obj->session_id, $obj->DB)) {
    print "Bad Session ID";
    exit();
}

$statusService = isset($statusService) ? $statusService : null;
$statusFilter = isset($statusFilter) ? $statusFilter : null;

// Store in session the last type of call
$_SESSION['monitoring_serviceByHg_status'] = $statusService;
$_SESSION['monitoring_serviceByHg_status_filter'] = $statusFilter;

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

/*
 * Check Arguments From GET request
 */
$o = filter_input(INPUT_GET, 'o', FILTER_SANITIZE_STRING, ['options' => ['default' => 'svcOVSG_pb']]);
$p = filter_input(INPUT_GET, 'p', FILTER_VALIDATE_INT, ['options' => ['default' => 2]]);
$num = filter_input(INPUT_GET, 'num', FILTER_VALIDATE_INT, ['options' => ['default' => 0]]);
$limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT, ['options' => ['default' => 30]]);
//if instance value is not set, displaying all active pollers' linked resources
$instance = filter_var($obj->defaultPoller ?? -1, FILTER_VALIDATE_INT);
$hostgroup = filter_input(INPUT_GET, 'hg_search', FILTER_SANITIZE_STRING, ['options' => ['default' => '']]);
$search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING, ['options' => ['default' => '']]);
$sort_type = filter_input(INPUT_GET, 'sort_type', FILTER_SANITIZE_STRING, ['options' => ['default' => 'host_name']]);
$order = isset($_GET['order']) && $_GET['order'] === "DESC" ? "DESC" : "ASC";

$grouplistStr = $obj->access->getAccessGroupsString();

$kernel = \App\Kernel::createForWeb();
$resourceController = $kernel->getContainer()->get(
    \Centreon\Application\Controller\MonitoringResourceController::class
);

//saving bound values
$queryValues = [];
$filterRq2 = '';

//Get Host status
$rq1 = "SELECT SQL_CALC_FOUND_ROWS DISTINCT
    hg.name AS alias, h.host_id id, h.name AS host_name, hgm.hostgroup_id, h.state hs, h.icon_image
    FROM hostgroups hg, hosts_hostgroups hgm, hosts h ";

if (!$obj->is_admin) {
    $rq1 .= ", centreon_acl ";
}
$rq1 .= "WHERE h.host_id = hgm.host_id " .
    "AND hgm.hostgroup_id = hg.hostgroup_id " .
    "AND h.enabled = '1' " .
    "AND h.name NOT LIKE '_Module_%' ";

if (!$obj->is_admin) {
    $rq1 .= $obj->access->queryBuilder("AND", "h.host_id", "centreon_acl.host_id") .
        $obj->access->queryBuilder("AND", "group_id", $grouplistStr) . " " .
        $obj->access->queryBuilder("AND", "hg.name", $obj->access->getHostGroupsString("NAME"));
}
if ($instance !== -1) {
    $rq1 .= " AND h.instance_id = :instance ";
    $queryValues['instance'] = [
        PDO::PARAM_INT => (int)$instance
    ];
}
if (substr($o, -3) === '_pb') {
    $rq1 .= " AND h.host_id IN (" .
        "SELECT s.host_id FROM services s " .
        "WHERE s.state != 0 AND s.state != 4 AND s.enabled = 1)";
    $filterRq2 = " AND s.state != 0 AND s.state != 4";
} elseif (substr($o, -6) === '_ack_0') {
    $rq1 .= " AND h.host_id IN (" .
        "SELECT s.host_id FROM services s " .
        "WHERE s.acknowledged = 0 AND s.state != 0 AND s.state != 4 AND s.enabled = 1)";
    $filterRq2 =  " AND s.state != 0 AND s.state != 4 AND s.acknowledged = 0";
} elseif (substr($o, -6) === '_ack_1') {
    $rq1 .= " AND h.host_id IN (" .
        "SELECT s.host_id FROM services s " .
        "WHERE s.acknowledged = 1 AND s.state != 0 AND s.state != 4 AND s.enabled = 1)";
    $filterRq2 = " AND s.acknowledged = 1";
}
if ($search != "") {
    $rq1 .= " AND h.name LIKE :search";
    $queryValues['search'] = [
        PDO::PARAM_STR => "%" . $search . "%"
    ];
}
if ($hostgroup !== "") {
    $rq1 .= " AND hg.name LIKE :hgName";
    $queryValues['hgName'] = [
        PDO::PARAM_STR => $hostgroup
    ];
}
$rq1 .= " AND h.enabled = 1 ORDER BY :sort_type, host_name " . $order;
$rq1 .= " LIMIT :numLimit, :limit";
$queryValues['sort_type'] = [
    PDO::PARAM_STR => $sort_type
];
$queryValues['numLimit'] = [
    PDO::PARAM_INT => (int)($num * $limit)
];
$queryValues['limit'] = [
    PDO::PARAM_INT => (int)$limit
];

$dbResult = $obj->DBC->prepare($rq1);
foreach ($queryValues as $bindId => $bindData) {
    foreach ($bindData as $bindType => $bindValue) {
        $dbResult->bindValue($bindId, $bindValue, $bindType);
    }
}
$dbResult->execute();

$tabH = [];
$tabHG = [];
$tab_finalH = [];
$numRows = $obj->DBC->query("SELECT FOUND_ROWS()")->fetchColumn();
while ($ndo = $dbResult->fetch()) {
    if (!isset($tab_finalH[$ndo["alias"]])) {
        $tab_finalH[$ndo["alias"]] = array($ndo["host_name"] => []);
    }
    $tab_finalH[$ndo["alias"]][$ndo["host_name"]]["cs"] = $ndo["hs"];
    $tab_finalH[$ndo["alias"]][$ndo["host_name"]]["icon"] = $ndo['icon_image'];
    $tab_finalH[$ndo["alias"]][$ndo["host_name"]]["tab_svc"] = [];
    $tabH[$ndo["host_name"]] = $ndo["id"];
    $tabHG[$ndo["alias"]] = $ndo["hostgroup_id"];
}
$dbResult->closeCursor();

// Resetting $queryValues
$queryValues = [];

// Get Services status
$rq2 = "SELECT DISTINCT s.service_id, h.name as host_name, s.description, s.state svcs, " .
    "(CASE s.state WHEN 0 THEN 3 WHEN 2 THEN 0 WHEN 3 THEN 2 ELSE s.state END) AS tri " .
    "FROM services s, hosts h ";
if (!$obj->is_admin) {
    $rq2 .= ", centreon_acl ";
}
$rq2 .= "WHERE h.host_id = s.host_id " .
    "AND h.name NOT LIKE '_Module_%' " .
    "AND h.enabled = '1' " .
    "AND s.enabled = '1' ";
$rq2 .= $obj->access->queryBuilder("AND", "h.host_id", "centreon_acl.host_id") .
    $obj->access->queryBuilder("AND", "s.service_id", "centreon_acl.service_id") .
    $obj->access->queryBuilder("AND", "group_id", $grouplistStr) .
    $filterRq2;

if ($search != "") {
    $rq2 .= " AND h.name LIKE :search";
    $queryValues[":search"] = [
        PDO::PARAM_STR => "%" . $search . "%"
    ];
}
if ($instance != -1) {
    $rq2 .= " AND h.instance_id = :instance ";
    $queryValues[":instance"] = [
        PDO::PARAM_INT => $instance
    ];
}
$rq2 .= " ORDER BY tri ASC, s.description ASC";

$tabService = [];
$tabHost = [];

$dbResult = $obj->DBC->prepare($rq2);
foreach ($queryValues as $bindId => $bindData) {
    foreach ($bindData as $bindType => $bindValue) {
        $dbResult->bindValue($bindId, $bindValue, $bindType);
    }
}
$dbResult->execute();

while ($ndo = $dbResult->fetch()) {
    if (!isset($tabService[$ndo["host_name"]])) {
        $tabService[$ndo["host_name"]] = [];
    }
    if (!isset($tabService[$ndo["host_name"]])) {
        $tabService[$ndo["host_name"]] = array("tab_svc" => []);
    }
    $tabService[$ndo["host_name"]]["tab_svc"][$ndo["description"]] = $ndo["svcs"];
    $tabHost[$ndo["host_name"]] = $ndo["service_id"];
}
$dbResult->closeCursor();

// Begin XML Generation
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

$ct = 0;
$hg = "";
$count = 0;
if (isset($tab_finalH)) {
    foreach ($tab_finalH as $hg_name => $tab_host) {
        foreach ($tab_host as $host_name => $tab) {
            if (isset($tabService[$host_name]["tab_svc"]) && count($tabService[$host_name]["tab_svc"])) {
                if (isset($hg_name) && $hg != $hg_name) {
                    if ($hg != "") {
                        $obj->XML->endElement();
                    }
                    $hg = $hg_name;
                    $obj->XML->startElement("hg");
                    $obj->XML->writeElement("hgn", CentreonUtils::escapeSecure($hg_name));
                    $obj->XML->writeElement("hgid", CentreonUtils::escapeSecure($tabHG[$hg_name]));
                }
                $obj->XML->startElement("l");
                $obj->XML->writeAttribute("class", $obj->getNextLineClass());
                if (isset($tabService[$host_name]["tab_svc"])) {
                    foreach ($tabService[$host_name]["tab_svc"] as $svc => $state) {
                        $serviceId = $svcObj->getServiceId($svc, $host_name);
                        $obj->XML->startElement("svc");
                        $obj->XML->writeElement("sn", CentreonUtils::escapeSecure($svc));
                        $obj->XML->writeElement("snl", CentreonUtils::escapeSecure(urlencode($svc)));
                        $obj->XML->writeElement("sc", $obj->colorService[$state]);
                        $obj->XML->writeElement("svc_id", $serviceId);
                        $obj->XML->writeElement(
                            "s_details_uri",
                            $useDeprecatedPages
                                ? 'main.php?o=svcd&p=202&host_name=' . $host_name . '&service_description=' . $svc
                                : $resourceController->buildServiceDetailsUri($tabH[$host_name], $serviceId)
                        );
                        $obj->XML->endElement();
                    }
                }
                $obj->XML->writeElement("o", $ct);
                $obj->XML->writeElement("hn", CentreonUtils::escapeSecure($host_name), false);
                if (isset($tab["icon"]) && $tab["icon"]) {
                    $obj->XML->writeElement("hico", $tab["icon"]);
                } else {
                    $obj->XML->writeElement("hico", "none");
                }
                $obj->XML->writeElement("hnl", CentreonUtils::escapeSecure(urlencode($host_name)));
                $obj->XML->writeElement("hid", CentreonUtils::escapeSecure($tabH[$host_name]));
                $obj->XML->writeElement("hs", $obj->statusHost[$tab["cs"]]);
                $obj->XML->writeElement("hc", $obj->colorHost[$tab["cs"]]);
                $obj->XML->writeElement("hcount", $count);
                $obj->XML->writeElement(
                    "h_details_uri",
                    $useDeprecatedPages
                        ? 'main.php?p=20201&o=hd&host_name=' . $host_name
                        : $resourceController->buildHostDetailsUri($tabH[$host_name])
                );
                $obj->XML->writeElement(
                    "s_listing_uri",
                    $useDeprecatedPages
                        ? 'main.php?o=svc&p=20201&statusFilter=&host_search=' . $host_name
                        : $resourceController->buildListingUri([
                            'filter' => json_encode([
                                'criterias' => [
                                    'search' => 'h.name:^' . $host_name . '$',
                                ],
                            ]),
                        ])
                );
                $obj->XML->endElement();
                $count++;
            }
        }
        $ct++;
    }
}
$obj->XML->endElement();

// Send Header
$obj->header();

// Send XML
$obj->XML->output();
