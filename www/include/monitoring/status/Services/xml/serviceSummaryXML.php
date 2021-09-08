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

// Create XML Request Objects
CentreonSession::start(1);
$obj = new CentreonXMLBGRequest($dependencyInjector, session_id(), 1, 1, 0, 1);


if (!isset($obj->session_id) || !CentreonSession::checkSession($obj->session_id, $obj->DB)) {
    print "Bad Session ID";
    exit();
}

// Set Default Poller
$obj->getDefaultFilters();

$centreon = $_SESSION['centreon'];

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
$hostgroups = filter_var($obj->defaultHostgroups ?? 0, FILTER_VALIDATE_INT);
$search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING, ['options' => ['default' => '']]);
$sortType = filter_input(INPUT_GET, 'sort_type', FILTER_SANITIZE_STRING, ['options' => ['default' => 'host_name']]);
$order = isset($_GET['order']) && $_GET['order'] === "DESC" ? "DESC" : "ASC";

// Backup poller selection
$obj->setInstanceHistory($instance);

$kernel = \App\Kernel::createForWeb();
$resourceController = $kernel->getContainer()->get(
    \Centreon\Application\Controller\MonitoringResourceController::class
);

$service = [];
$host_status = [];
$service_status = [];
$host_services = [];
$metaService_status = [];
$tab_host_service = [];
$tabIcone = [];
//saving bound values
$queryValues = [];

/**
 * Get status
 */
$rq1 = "SELECT SQL_CALC_FOUND_ROWS DISTINCT hosts.name, hosts.state, hosts.icon_image, hosts.host_id FROM hosts ";
if ($hostgroups) {
    $rq1 .= ", hosts_hostgroups hg, hostgroups hg2 ";
}

if (!$obj->is_admin) {
    $rq1 .= ", centreon_acl ";
}

$rq1 .= "WHERE hosts.name NOT LIKE '_Module_%' AND hosts.enabled = 1 "
    . $obj->access->queryBuilder("AND", "hosts.host_id", "centreon_acl.host_id") . " "
    . $obj->access->queryBuilder("AND", "group_id", $obj->grouplistStr) . " ";

if (substr($o, -3) === '_pb' || substr($o, -6) === '_ack_0') {
    $rq1 .= "AND hosts.host_id IN ( "
        . "SELECT s.host_id FROM services s "
        . "WHERE s.state != 0 "
        . "AND s.state != 4 "
        . "AND s.enabled = 1) ";
} elseif (substr($o, -6) === '_ack_1') {
    $rq1 .= "AND hosts.host_id IN ( "
        . "SELECT s.host_id FROM services s "
        . "WHERE s.acknowledged = '1' "
        . "AND s.enabled = 1) ";
}

if ($search != "") {
    $rq1 .= "AND hosts.name like :search ";
    $queryValues['search'] = [\PDO::PARAM_STR => '%' . $search . '%'];
}

if ($instance != -1) {
    $rq1 .= "AND hosts.instance_id = :instance ";
    $queryValues['instance'] = [\PDO::PARAM_INT => $instance];
}

if ($hostgroups) {
    $rq1 .= " AND hosts.host_id = hg.host_id
        AND hg.hostgroup_id = :hostGroup
        AND hg.hostgroup_id = hg2.hostgroup_id ";
    $queryValues['hostGroup'] = [\PDO::PARAM_INT => $hostgroups];
}

// Sort order
switch ($sortType) {
    case 'current_state':
        $rq1 .= "ORDER BY hosts.state " . $order . ",hosts.name ";
        break;
    default:
        $rq1 .= "ORDER BY hosts.name " . $order . " ";
        break;
}

// Limit
$rq1 .= " LIMIT :numLimit, :limit";
$queryValues['numLimit'] = [\PDO::PARAM_INT => ($num * $limit)];
$queryValues['limit'] = [\PDO::PARAM_INT => $limit];

$dbResult = $obj->DBC->prepare($rq1);
foreach ($queryValues as $bindId => $bindData) {
    foreach ($bindData as $bindType => $bindValue) {
        $dbResult->bindValue($bindId, $bindValue, $bindType);
    }
}
$dbResult->execute();

$numRows = $obj->DBC->numberRows();

// Info / Pagination
$obj->XML->startElement("reponse");
$obj->XML->startElement("i");
$obj->XML->writeElement("numrows", $numRows);
$obj->XML->writeElement("num", $num);
$obj->XML->writeElement("limit", $limit);
$obj->XML->writeElement("p", $p);
$obj->XML->endElement();

$buildParameter = function (string $id, string $name) {
    return [
        'id' => $id,
        'name' => $name,
    ];
};

$buildServicesUri = function (string $hostname, array $statuses) use ($resourceController, $buildParameter) {
    return $resourceController->buildListingUri([
        'filter' => json_encode([
            'criterias' => [
                'search' => 'h.name:^' . $hostname . '$',
                'resourceTypes' => [$buildParameter('service', 'Service')],
                'statuses' => $statuses,
            ],
        ]),
    ]);
};

$okStatus = $buildParameter('OK', 'Ok');
$warningStatus = $buildParameter('WARNING', 'Warning');
$criticalStatus = $buildParameter('CRITICAL', 'Critical');
$unknownStatus = $buildParameter('UNKNOWN', 'Unknown');
$pendingStatus = $buildParameter('PENDING', 'Pending');

$ct = 0;
$tabFinal = [];
while ($ndo = $dbResult->fetch()) {
    $tabFinal[$ndo["name"]]["nb_service_k"] = 0;
    $tabFinal[$ndo["name"]]["host_id"] = $ndo["host_id"];
    if (substr($o, 0, 6) !== 'svcSum') {
        $tabFinal[$ndo["name"]]["nb_service_k"] = $obj->monObj->getServiceStatusCount($ndo["name"], $obj, $o, 0, $obj);
    }
    $tabFinal[$ndo["name"]]["nb_service_w"] = 0 + $obj->monObj->getServiceStatusCount($ndo["name"], $obj, $o, 1, $obj);
    $tabFinal[$ndo["name"]]["nb_service_c"] = 0 + $obj->monObj->getServiceStatusCount($ndo["name"], $obj, $o, 2, $obj);
    $tabFinal[$ndo["name"]]["nb_service_u"] = 0 + $obj->monObj->getServiceStatusCount($ndo["name"], $obj, $o, 3, $obj);
    $tabFinal[$ndo["name"]]["nb_service_p"] = 0 + $obj->monObj->getServiceStatusCount($ndo["name"], $obj, $o, 4, $obj);
    $tabFinal[$ndo["name"]]["cs"] = $ndo["state"];

    if (isset($ndo["icon_image"]) && $ndo["icon_image"] != "") {
        $tabIcone[$ndo["name"]] = $ndo["icon_image"];
    } else {
        $tabIcone[$ndo["name"]] = "none";
    }
}

foreach ($tabFinal as $host_name => $tab) {
    $obj->XML->startElement("l");
    $obj->XML->writeAttribute("class", $obj->getNextLineClass());
    $obj->XML->writeElement("o", $ct++);
    $obj->XML->writeElement("hn", CentreonUtils::escapeSecure($host_name), false);
    $obj->XML->writeElement("hnl", CentreonUtils::escapeSecure(urlencode($host_name)));
    $obj->XML->writeElement("hid", $tab["host_id"], false);
    $obj->XML->writeElement(
        "h_details_uri",
        $useDeprecatedPages
            ? 'main.php?p=20202&o=hd&host_name=' . $host_name
            : $resourceController->buildHostDetailsUri($tab["host_id"])
    );
    $serviceListingDeprecatedUri = 'main.php?p=20201&o=svc&host_search=' . $host_name;
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
    $obj->XML->writeElement("ico", $tabIcone[$host_name]);
    $obj->XML->writeElement("hs", _($obj->statusHost[$tab["cs"]]), false);
    $obj->XML->writeElement("hc", $obj->colorHost[$tab["cs"]]);
    $obj->XML->writeElement("sc", $tab["nb_service_c"]);
    $obj->XML->writeElement("scc", $obj->colorService[2]);
    $obj->XML->writeElement("sw", $tab["nb_service_w"]);
    $obj->XML->writeElement("swc", $obj->colorService[1]);
    $obj->XML->writeElement("su", $tab["nb_service_u"]);
    $obj->XML->writeElement("suc", $obj->colorService[3]);
    $obj->XML->writeElement("sk", $tab["nb_service_k"]);
    $obj->XML->writeElement("skc", $obj->colorService[0]);
    $obj->XML->writeElement("sp", $tab["nb_service_p"]);
    $obj->XML->writeElement("spc", $obj->colorService[4]);
    $obj->XML->writeElement(
        "s_listing_ok",
        $useDeprecatedPages
            ? $serviceListingDeprecatedUri . '&statusFilter=ok'
            : $buildServicesUri($host_name, [$okStatus])
    );
    $obj->XML->writeElement(
        "s_listing_warning",
        $useDeprecatedPages
            ? $serviceListingDeprecatedUri . '&statusFilter=warning'
            : $buildServicesUri($host_name, [$warningStatus])
    );
    $obj->XML->writeElement(
        "s_listing_critical",
        $useDeprecatedPages
            ? $serviceListingDeprecatedUri . '&statusFilter=critical'
            : $buildServicesUri($host_name, [$criticalStatus])
    );
    $obj->XML->writeElement(
        "s_listing_unknown",
        $useDeprecatedPages
            ? $serviceListingDeprecatedUri . '&statusFilter=unknown'
            : $buildServicesUri($host_name, [$unknownStatus])
    );
    $obj->XML->writeElement(
        "s_listing_pending",
        $useDeprecatedPages
            ? $serviceListingDeprecatedUri . '&statusFilter=pending'
            : $buildServicesUri($host_name, [$pendingStatus])
    );
    $obj->XML->endElement();
}

if (!$ct) {
    $obj->XML->writeElement("infos", "none");
}
$obj->XML->endElement();

// Send Header
$obj->header();

// Send XML
$obj->XML->output();
