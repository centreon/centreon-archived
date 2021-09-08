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
CentreonSession::start(1);
$obj = new CentreonXMLBGRequest($dependencyInjector, session_id(), 1, 1, 0, 1);
$svcObj = new CentreonService($obj->DB);

if (!isset($obj->session_id) || !CentreonSession::checkSession($obj->session_id, $obj->DB)) {
    print "Bad Session ID";
    exit();
}

$centreon = $_SESSION['centreon'];

/**
 * true: URIs will correspond to deprecated pages
 * false: URIs will correspond to new page (Resource Status)
 */
$useDeprecatedPages = $centreon->user->doesShowDeprecatedPages();

// Set Default Poller
$obj->getDefaultFilters();

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

//saving bound values
$queryValues = [];

/**
 * Get Host status
 */
$rq1 = " SELECT SQL_CALC_FOUND_ROWS DISTINCT hosts.name, hosts.state, hosts.icon_image, hosts.host_id FROM hosts ";
if ($hostgroups) {
    $rq1 .= ", hosts_hostgroups hg, hostgroups hg2 ";
}
if (!$obj->is_admin) {
    $rq1 .= ", centreon_acl ";
}
$rq1 .= " WHERE hosts.name NOT LIKE '_Module_%' ";
if (!$obj->is_admin) {
    $rq1 .= " AND hosts.host_id = centreon_acl.host_id " .
        $obj->access->queryBuilder("AND", "group_id", $obj->grouplistStr);
}
if ($o == "svcgrid_pb" || $o == "svcOV_pb" || $o == "svcgrid_ack_0" || $o == "svcOV_ack_0") {
    $rq1 .= " AND hosts.host_id IN (" .
        " SELECT s.host_id FROM services s " .
        " WHERE s.state != 0 AND s.state != 4 AND s.enabled = 1)";
}
if ($o == "svcgrid_ack_1" || $o == "svcOV_ack_1") {
    $rq1 .= " AND hosts.host_id IN (" .
        " SELECT s.host_id FROM services s " .
        " WHERE s.acknowledged = '1' AND s.enabled = 1)";
}
if ($search != "") {
    $rq1 .= " AND hosts.name like :search ";
    $queryValues['search'] = [\PDO::PARAM_STR => '%' . $search . '%'];
}
if ($instance != -1) {
    $rq1 .= " AND hosts.instance_id = :instance ";
    $queryValues['instance'] = [\PDO::PARAM_INT =>  $instance];
}
if ($hostgroups) {
    $rq1 .= " AND hosts.host_id = hg.host_id
        AND hg.hostgroup_id = :hostgroup
        AND hg.hostgroup_id = hg2.hostgroup_id ";
    // only one value is returned from the current "select" filter
    $queryValues['hostgroup'] = [\PDO::PARAM_INT =>  $hostgroups];
}
$rq1 .= " AND hosts.enabled = 1 ";

switch ($sortType) {
    case 'current_state':
        $rq1 .= " ORDER BY hosts.state " . $order . ",hosts.name ";
        break;
    default:
        $rq1 .= " ORDER BY hosts.name " . $order;
        break;
}
$rq1 .= " LIMIT :numLimit, :limit";
$queryValues['numLimit'] = [\PDO::PARAM_INT => ($num * $limit)];
$queryValues['limit'] = [\PDO::PARAM_INT => $limit];

// Execute request
$dbResult = $obj->DBC->prepare($rq1);
foreach ($queryValues as $bindId => $bindData) {
    foreach ($bindData as $bindType => $bindValue) {
        $dbResult->bindValue($bindId, $bindValue, $bindType);
    }
}
$dbResult->execute();

$numRows = $obj->DBC->numberRows();

$obj->XML->startElement("reponse");
$obj->XML->startElement("i");
$obj->XML->writeElement("numrows", $numRows);
$obj->XML->writeElement("num", $num);
$obj->XML->writeElement("limit", $limit);
$obj->XML->writeElement("p", $p);

preg_match("/svcOV/", $_GET["o"], $matches) ? $obj->XML->writeElement("s", "1") : $obj->XML->writeElement("s", "0");
$obj->XML->endElement();

$tab_final = [];
$str = "";
while ($ndo = $dbResult->fetch()) {
    if ($str != "") {
        $str .= ",";
    }
    $str .= "'" . $ndo["name"] . "'";
    $tab_final[$ndo["name"]] = array("cs" => $ndo["state"], "hid" => $ndo["host_id"]);
    if ($ndo["icon_image"] != "") {
        $tabIcone[$ndo["name"]] = $ndo["icon_image"];
    } else {
        $tabIcone[$ndo["name"]] = "none";
    }
}
$dbResult->closeCursor();

// Get Service status
$tab_svc = $obj->monObj->getServiceStatus($str, $obj, $o, $instance, $hostgroups);
if (isset($tab_svc)) {
    foreach ($tab_svc as $host_name => $tab) {
        if (count($tab)) {
            $tab_final[$host_name]["tab_svc"] = $tab;
        }
    }
}

$ct = 0;
if (isset($tab_svc)) {
    foreach ($tab_final as $host_name => $tab) {
        $obj->XML->startElement("l");
        $obj->XML->writeAttribute("class", $obj->getNextLineClass());
        if (isset($tab["tab_svc"])) {
            foreach ($tab["tab_svc"] as $svc => $state) {
                $serviceId = $svcObj->getServiceId($svc, $host_name);
                $obj->XML->startElement("svc");
                $obj->XML->writeElement("sn", CentreonUtils::escapeSecure($svc), false);
                $obj->XML->writeElement("snl", CentreonUtils::escapeSecure(urlencode($svc)));
                $obj->XML->writeElement("sc", $obj->colorService[$state]);
                $obj->XML->writeElement("svc_id", $serviceId);
                $obj->XML->writeElement(
                    "s_details_uri",
                    $useDeprecatedPages
                        ? 'main.php?o=svcd&p=202&host_name=' . $host_name . '&service_description=' . $svc
                        : $resourceController->buildServiceDetailsUri($tab["hid"], $serviceId)
                );
                $obj->XML->endElement();
            }
        }
        $obj->XML->writeElement("o", $ct++);
        $obj->XML->writeElement("ico", $tabIcone[$host_name]);
        $obj->XML->writeElement("hn", $host_name, false);
        $obj->XML->writeElement("hid", $tab["hid"], false);
        $obj->XML->writeElement("hnl", CentreonUtils::escapeSecure(urlencode($host_name)));
        $obj->XML->writeElement("hs", _($obj->statusHost[$tab["cs"]]), false);
        $obj->XML->writeElement("hc", $obj->colorHost[$tab["cs"]]);
        $obj->XML->writeElement(
            "h_details_uri",
            $useDeprecatedPages
                ? 'main.php?p=20202&o=hd&host_name=' . $host_name
                : $resourceController->buildHostDetailsUri($tab["hid"])
        );
        $obj->XML->writeElement(
            "s_listing_uri",
            $useDeprecatedPages
                ? 'main.php?o=svc&p=20201&statusFilter=;host_search=' . $host_name
                : $resourceController->buildListingUri([
                    'filter' => json_encode([
                        'criterias' => [
                            'search' => 'h.name:^' . $host_name . '$',
                        ],
                    ]),
                ])
        );
        $obj->XML->endElement();
    }
}

if (!$ct) {
    $obj->XML->writeElement("infos", "none");
}
$obj->XML->endElement();

// Send Header
$obj->header();

// Send XML
$obj->XML->output();
