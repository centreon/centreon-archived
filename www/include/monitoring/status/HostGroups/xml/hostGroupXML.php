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
include_once _CENTREON_PATH_ . "www/include/common/common-Func.php";

/*
 * Create XML Request Objects
 */
CentreonSession::start();
$obj = new CentreonXMLBGRequest($dependencyInjector, session_id(), 1, 1, 0, 1);

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

$kernel = \App\Kernel::createForWeb();
$resourceController = $kernel->getContainer()->get(
    \Centreon\Application\Controller\MonitoringResourceController::class
);

// Alias / Name conversion table
$convertTable = [];
$convertID = [];
$dbResult = $obj->DBC->query("SELECT hostgroup_id, name FROM hostgroups");
while ($hg = $dbResult->fetch()) {
    $convertTable[$hg["name"]] = $hg["name"];
    $convertID[$hg["name"]] = $hg["hostgroup_id"];
}
$dbResult->closeCursor();

// Check Arguments From GET tab
$o = filter_input(INPUT_GET, 'o', FILTER_SANITIZE_STRING, ['options' => ['default' => 'h']]);
$p = filter_input(INPUT_GET, 'p', FILTER_VALIDATE_INT, ['options' => ['default' => 2]]);
$num = filter_input(INPUT_GET, 'num', FILTER_VALIDATE_INT, ['options' => ['default' => 0]]);
$limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT, ['options' => ['default' => 20]]);
//if instance value is not set, displaying all active pollers linked resources
$instance = filter_var($obj->defaultPoller ?? -1, FILTER_VALIDATE_INT);

$search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING, ['options' => ['default' => '']]);
$order = isset($_GET['order']) && $_GET['order'] === "DESC" ? "DESC" : "ASC";

//saving bound values
$queryValues = [];

$groupStr = $obj->access->getAccessGroupsString();

// Backup poller selection
$obj->setInstanceHistory($instance);

// Search string
$searchStr = " ";
if ($search != "") {
    $searchStr = " AND hg.name LIKE :search ";
    $queryValues['search'] = [
        \PDO::PARAM_STR => '%' . $search . '%'
    ];
}

/*
 * Host state
 */
if ($obj->is_admin) {
    $rq1 = "SELECT hg.name as alias, h.state, COUNT(h.host_id) AS nb
        FROM hosts_hostgroups hhg, hosts h, hostgroups hg
        WHERE hg.hostgroup_id = hhg.hostgroup_id
        AND hhg.host_id = h.host_id
        AND h.enabled = 1 ";
    if (isset($instance) && $instance > 0) {
        $rq1 .= "AND h.instance_id = :instance";
        $queryValues['instance'] = [
            \PDO::PARAM_INT => $instance
        ];
    }
    $rq1 .= $searchStr . "GROUP BY hg.name " . $order . ", h.state";
} else {
    $rq1 = "SELECT hg.name as alias, h.state, COUNT(DISTINCT h.host_id) AS nb
        FROM centreon_acl acl, hosts_hostgroups hhg, hosts h, hostgroups hg
        WHERE hg.hostgroup_id = hhg.hostgroup_id
        AND hhg.host_id = h.host_id
        AND h.enabled = 1 ";
    if (isset($instance) && $instance > 0) {
        $rq1 .= "AND h.instance_id = :instance";
        $queryValues['instance'] = [
            \PDO::PARAM_INT => $instance
        ];
    }
    $rq1 .= $searchStr . $obj->access->queryBuilder("AND", "hg.name", $obj->access->getHostGroupsString("NAME")) .
        "AND h.host_id = acl.host_id
        AND acl.group_id in (" . $groupStr . ")
        GROUP BY hg.name " . $order . ", h.state";
}
$dbResult = $obj->DBC->prepare($rq1);
foreach ($queryValues as $bindId => $bindData) {
    foreach ($bindData as $bindType => $bindValue) {
        $dbResult->bindValue($bindId, $bindValue, $bindType);
    }
}
$dbResult->execute();

while ($data = $dbResult->fetch()) {
    if (!isset($stats[$data["alias"]])) {
        $stats[$data["alias"]] = array(
            "h" => array(0 => 0, 1 => 0, 2 => 0, 3 => 0),
            "s" => array(0 => 0, 1 => 0, 2 => 0, 3 => 0, 3 => 0, 4 => 0)
        );
    }
    $stats[$data["alias"]]["h"][$data["state"]] = $data["nb"];
}
$dbResult->closeCursor();

/*
 * Get Services request
 */
if ($obj->is_admin) {
    $rq2 = "SELECT hg.name AS alias, s.state, COUNT( s.service_id ) AS nb,
        (CASE s.state WHEN 0 THEN 3 WHEN 2 THEN 0 WHEN 3 THEN 2 ELSE s.state END) AS tri
        FROM hosts_hostgroups hhg, hosts h, hostgroups hg, services s
        WHERE hg.hostgroup_id = hhg.hostgroup_id
        AND hhg.host_id = h.host_id
        AND h.enabled = 1
        AND h.host_id = s.host_id
        AND s.enabled = 1 ";
    if (isset($instance) && $instance > 0) {
        $rq2 .= "AND h.instance_id = :instance";
    }
    $rq2 .= $searchStr . "GROUP BY hg.name, s.state ORDER BY tri ASC";
} else {
    $rq2 = "SELECT hg.name as alias, s.state, COUNT( s.service_id ) AS nb,
        (CASE s.state WHEN 0 THEN 3 WHEN 2 THEN 0 WHEN 3 THEN 2 ELSE s.state END) AS tri
        FROM centreon_acl acl, hosts_hostgroups hhg, hosts h, hostgroups hg, services s
        WHERE hg.hostgroup_id = hhg.hostgroup_id
        AND hhg.host_id = h.host_id
        AND h.enabled = 1
        AND h.host_id = s.host_id
        AND s.enabled = 1 ";
    if (isset($instance) && $instance > 0) {
        $rq2 .= "AND h.instance_id = :instance";
    }
    $rq2 .= $searchStr . $obj->access->queryBuilder("AND", "hg.name", $obj->access->getHostGroupsString("NAME")) .
        "AND h.host_id = acl.host_id
        AND s.service_id = acl.service_id
        AND acl.group_id IN (" . $groupStr . ")
        GROUP BY hg.name, s.state ORDER BY tri ASC";
}

$dbResult = $obj->DBC->prepare($rq2);
foreach ($queryValues as $bindId => $bindData) {
    foreach ($bindData as $bindType => $bindValue) {
        $dbResult->bindValue($bindId, $bindValue, $bindType);
    }
}
$dbResult->execute();

while ($data = $dbResult->fetch()) {
    if (!isset($stats[$data["alias"]])) {
        $stats[$data["alias"]] = array(
            "h" => array(0 => 0, 1 => 0, 2 => 0, 3 => 0),
            "s" => array(0 => 0, 1 => 0, 2 => 0, 3 => 0, 3 => 0, 4 => 0)
        );
    }
    if ($stats[$data["alias"]]) {
        $stats[$data["alias"]]["s"][$data["state"]] = $data["nb"];
    }
}

/*
 * Get Pagination Rows
 */
$stats = $stats ?? [];
$numRows = count($stats);

$obj->XML->startElement("reponse");
$obj->XML->startElement("i");
$obj->XML->writeElement("numrows", $numRows);
$obj->XML->writeElement("num", $num);
$obj->XML->writeElement("limit", $limit);
$obj->XML->writeElement("p", $p);
$obj->XML->endElement();

$buildParameter = function ($id, string $name) {
    return [
        'id' => $id,
        'name' => $name,
    ];
};

$buildHostgroupUri = function (array $hostgroups, array $types, array $statuses) use ($resourceController) {
    return $resourceController->buildListingUri([
        'filter' => json_encode([
            'criterias' => [
                'hostGroups' => $hostgroups,
                'resourceTypes' => $types,
                'statuses' => $statuses,
            ],
        ]),
    ]);
};

$hostType = $buildParameter('host', 'Host');
$serviceType = $buildParameter('service', 'Service');
$okStatus = $buildParameter('OK', 'Ok');
$warningStatus = $buildParameter('WARNING', 'Warning');
$criticalStatus = $buildParameter('CRITICAL', 'Critical');
$unknownStatus = $buildParameter('UNKNOWN', 'Unknown');
$pendingStatus = $buildParameter('PENDING', 'Pending');
$upStatus = $buildParameter('UP', 'Up');
$downStatus = $buildParameter('DOWN', 'Down');
$unreachableStatus = $buildParameter('UNREACHABLE', 'Unreachable');

$i = 0;
$ct = 0;

if (isset($stats)) {
    foreach ($stats as $name => $stat) {
        if (
            ($i < (($num + 1) * $limit) && $i >= (($num) * $limit))
            && ((isset($converTable[$name]) && isset($acl[$convertTable[$name]])) || (!isset($acl)))
            && $name != "meta_hostgroup"
        ) {
            $class = $obj->getNextLineClass();
            if (isset($stat["h"]) && count($stat["h"])) {
                $hostgroup = $buildParameter(
                    (int) $convertID[$convertTable[$name]],
                    $convertTable[$name]
                );
                $obj->XML->startElement("l");
                $obj->XML->writeAttribute("class", $class);
                $obj->XML->writeElement("o", $ct++);
                $obj->XML->writeElement(
                    "hn",
                    CentreonUtils::escapeSecure($convertTable[$name] . " (" . $name . ")"),
                    false
                );
                $obj->XML->writeElement("hu", $stat["h"][0]);
                $obj->XML->writeElement("huc", $obj->colorHost[0]);
                $obj->XML->writeElement("hd", $stat["h"][1]);
                $obj->XML->writeElement("hdc", $obj->colorHost[1]);
                $obj->XML->writeElement("hur", $stat["h"][2]);
                $obj->XML->writeElement("hurc", $obj->colorHost[2]);
                $obj->XML->writeElement("sc", $stat["s"][2]);
                $obj->XML->writeElement("scc", $obj->colorService[2]);
                $obj->XML->writeElement("sw", $stat["s"][1]);
                $obj->XML->writeElement("swc", $obj->colorService[1]);
                $obj->XML->writeElement("su", $stat["s"][3]);
                $obj->XML->writeElement("suc", $obj->colorService[3]);
                $obj->XML->writeElement("sk", $stat["s"][0]);
                $obj->XML->writeElement("skc", $obj->colorService[0]);
                $obj->XML->writeElement("sp", $stat["s"][4]);
                $obj->XML->writeElement("spc", $obj->colorService[4]);
                $hostgroupDeprecatedUri = CentreonUtils::escapeSecure("main.php?p=20201&o=svc&hg=" . $hostgroup['id']);
                $obj->XML->writeElement(
                    'hg_listing_uri',
                    $useDeprecatedPages ? $hostgroupDeprecatedUri : $buildHostgroupUri([$hostgroup], [], [])
                );
                $obj->XML->writeElement(
                    "hg_listing_h_up",
                    $useDeprecatedPages
                        ? $hostgroupDeprecatedUri . '&amp;o=h_up'
                        : $buildHostgroupUri([$hostgroup], [$hostType], [$upStatus])
                );
                $obj->XML->writeElement(
                    "hg_listing_h_down",
                    $useDeprecatedPages
                        ? $hostgroupDeprecatedUri . '&amp;o=h_down'
                        : $buildHostgroupUri([$hostgroup], [$hostType], [$downStatus])
                );
                $obj->XML->writeElement(
                    "hg_listing_h_unreachable",
                    $buildHostgroupUri([$hostgroup], [$hostType], [$unreachableStatus])
                );
                $obj->XML->writeElement(
                    "hg_listing_h_pending",
                    $useDeprecatedPages
                        ? $hostgroupDeprecatedUri . '&amp;o=h_pending'
                        : $buildHostgroupUri([$hostgroup], [$hostType], [$pendingStatus])
                );
                $obj->XML->writeElement(
                    "hg_listing_s_ok",
                    $useDeprecatedPages
                        ? $hostgroupDeprecatedUri . '&amp;o=svc&amp;statusFilter=ok'
                        : $buildHostgroupUri([$hostgroup], [$serviceType], [$okStatus])
                );
                $obj->XML->writeElement(
                    "hg_listing_s_warning",
                    $useDeprecatedPages
                        ? $hostgroupDeprecatedUri . '&amp;o=svc&amp;statusFilter=warning'
                        : $buildHostgroupUri([$hostgroup], [$serviceType], [$warningStatus])
                );
                $obj->XML->writeElement(
                    "hg_listing_s_critical",
                    $useDeprecatedPages
                        ? $hostgroupDeprecatedUri . '&amp;o=svc&amp;statusFilter=critical'
                        : $buildHostgroupUri([$hostgroup], [$serviceType], [$criticalStatus])
                );
                $obj->XML->writeElement(
                    "hg_listing_s_unknown",
                    $useDeprecatedPages
                        ? $hostgroupDeprecatedUri . '&amp;o=svc&amp;statusFilter=unknown'
                        : $buildHostgroupUri([$hostgroup], [$serviceType], [$unknownStatus])
                );
                $obj->XML->writeElement(
                    "hg_listing_s_pending",
                    $useDeprecatedPages
                        ? $hostgroupDeprecatedUri . '&amp;o=svc&amp;statusFilter=pending'
                        : $buildHostgroupUri([$hostgroup], [$serviceType], [$pendingStatus])
                );
                $obj->XML->endElement();
            }
        }
        $i++;
    }
}

if (!$ct) {
    $obj->XML->writeElement("infos", "none");
}
$obj->XML->endElement();

$obj->header();
$obj->XML->output();
