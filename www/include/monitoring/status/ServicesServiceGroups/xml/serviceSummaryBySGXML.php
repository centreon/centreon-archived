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

/*
 * Create XML Request Objects
 */
CentreonSession::start(1);
$obj = new CentreonXMLBGRequest($dependencyInjector, session_id(), 1, 1, 0, 1);
$svcObj = new CentreonService($obj->DB);

if (!isset($obj->session_id) || !CentreonSession::checkSession($obj->session_id, $obj->DB)) {
    print "Bad Session ID";
    exit();
}

/*
 * Set Default Poller
 */
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
 * Check Arguments From GET tab
 */
$o = isset($_GET['o']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['o']) : 'h';
$p = filter_input(INPUT_GET, 'p', FILTER_VALIDATE_INT, ['options' => ['default' => 2]]);
$num = filter_input(INPUT_GET, 'num', FILTER_VALIDATE_INT, ['options' => ['default' => 0]]);
$limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT, ['options' => ['default' => 20]]);
//if instance value is not set, displaying all active pollers linked resources
$instance = filter_var($obj->defaultPoller ?? -1, FILTER_VALIDATE_INT);
$hSearch = isset($_GET['host_search']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['host_search']) : '';
$sgSearch = isset($_GET['sg_search']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['sg_search']) : '';
$sort_type = isset($_GET['sort_type']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['sort_type']) : 'host_name';
$order = isset($_GET['order']) && $_GET['order'] === "DESC" ? "DESC" : "ASC";

$kernel = \App\Kernel::createForWeb();
$resourceController = $kernel->getContainer()->get(
    \Centreon\Application\Controller\MonitoringResourceController::class
);

//saving bound values
$queryValues = [];
$queryValues2 = [];

/*
 * Backup poller selection
 */
$obj->setInstanceHistory($instance);

/**
 * Prepare pagination
 */
$s_search = "";
// Display service problems
if (substr($o, -3) === '_pb') {
    $s_search .= " AND s.state != 0 AND s.state != 4 ";
}
// Display acknowledged services
if (substr($o, -6) === '_ack_1') {
    $s_search .= " AND s.acknowledged = '1' ";
} elseif (substr($o, -6) === '_ack_0') {
// Display not acknowledged services
    $s_search .= " AND s.state != 0 AND s.state != 4 AND s.acknowledged = 0 ";
}

$query = "SELECT SQL_CALC_FOUND_ROWS DISTINCT sg.servicegroup_id, h.host_id
    FROM servicegroups sg
    INNER JOIN services_servicegroups sgm ON sg.servicegroup_id = sgm.servicegroup_id
    INNER JOIN services s ON s.service_id = sgm.service_id
    INNER JOIN  hosts h ON sgm.host_id = h.host_id AND h.host_id = s.host_id "
    . $obj->access->getACLHostsTableJoin($obj->DBC, "h.host_id")
    . $obj->access->getACLServicesTableJoin($obj->DBC, "s.service_id")
    . " WHERE 1 = 1  ";

// Servicegroup ACL
$query .= $obj->access->queryBuilder("AND", "sg.servicegroup_id", $obj->access->getServiceGroupsString("ID"));

// Servicegroup search
if ($sgSearch != "") {
    $query .= "AND sg.name = :sgSearch ";
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
$query .= $h_search;

// Service search
$query .= $s_search;

// Poller search
if ($instance != -1) {
    $query .= " AND h.instance_id = :instance ";
    $queryValues['instance'] = [
        \PDO::PARAM_INT => $instance
    ];
}

$query .= "ORDER BY sg.name " . $order . " LIMIT :numLimit, :limit";
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

/**
 * Create XML Flow
 */
$obj->XML = new CentreonXML();
$obj->XML->startElement("reponse");
$obj->XML->startElement("i");
$obj->XML->writeElement("numrows", $numRows);
$obj->XML->writeElement("num", $num);
$obj->XML->writeElement("limit", $limit);
$obj->XML->writeElement("host_name", _("Hosts"), 0);
$obj->XML->writeElement("services", _("Services"), 0);
$obj->XML->writeElement("p", $p);
$obj->XML->writeElement("sk", $obj->colorService[0]);
$obj->XML->writeElement("sw", $obj->colorService[1]);
$obj->XML->writeElement("sc", $obj->colorService[2]);
$obj->XML->writeElement("su", $obj->colorService[3]);
$obj->XML->writeElement("sp", $obj->colorService[4]);
$obj->XML->writeElement("s", "1");
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

// Construct query for servicegroups search
$sg_search = "";
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
        $servicegroupsSql1[] = "(sg.servicegroup_id = " . $key . " AND h.host_id IN (" .
            implode(',', $hostsSql) . ")) ";
    }
    $sg_search .= implode(" OR ", $servicegroupsSql1);
    $sg_search .= ") ";
    if ($sgSearch != "") {
        $sg_search .= "AND sg.name = :sgSearch";
        $queryValues2['sgSearch'] = [
            \PDO::PARAM_STR => $sgSearch
        ];
    }

    $query2 = "SELECT SQL_CALC_FOUND_ROWS count(s.state) as count_state,
        sg.name AS sg_name,
        h.name AS host_name,
        h.state AS host_state,
        h.icon_image, h.host_id, s.state,
        (CASE s.state WHEN 0 THEN 3 WHEN 2 THEN 0 WHEN 3 THEN 2 ELSE s.state END) AS tri
        FROM servicegroups sg, services_servicegroups sgm, services s, hosts h
        WHERE h.host_id = s.host_id AND s.host_id = sgm.host_id AND s.service_id=sgm.service_id
        AND sg.servicegroup_id=sgm.servicegroup_id "
        . $s_search
        . $sg_search
        . $h_search
        . $obj->access->queryBuilder("AND", "sg.servicegroup_id", $obj->access->getServiceGroupsString("ID"))
        . $obj->access->queryBuilder("AND", "s.service_id", $obj->access->getServicesString("ID", $obj->DBC))
        . " GROUP BY sg_name,host_name,host_state,icon_image,host_id, s.state ORDER BY tri ASC ";

    $dbResult = $obj->DBC->prepare($query2);
    foreach ($queryValues2 as $bindId => $bindData) {
        foreach ($bindData as $bindType => $bindValue) {
            $dbResult->bindValue($bindId, $bindValue, $bindType);
        }
    }
    $dbResult->execute();

    $states = array(
        0 => 'sk',
        1 => 'sw',
        2 => 'sc',
        3 => 'su',
        4 => 'sp'
    );

    $sg_list = [];
    while ($tab = $dbResult->fetch()) {
        $sg_list[$tab["sg_name"]][$tab["host_name"]]['host_id'] = $tab['host_id'];
        $sg_list[$tab["sg_name"]][$tab["host_name"]]['icon_image'] = $tab['icon_image'];
        $sg_list[$tab["sg_name"]][$tab["host_name"]]['host_state'] = $tab['host_state'];
        $sg_list[$tab["sg_name"]][$tab["host_name"]]['states'][$states[$tab['state']]] = $tab['count_state'];
    }

    $ct = 0;
    foreach ($sg_list as $sg => $h) {
        $count = 0;
        $ct++;
        $obj->XML->startElement("sg");
        $obj->XML->writeElement("sgn", CentreonUtils::escapeSecure($sg));
        $obj->XML->writeElement("o", $ct);

        foreach ($h as $hostName => $hostInfos) {
            $count++;
            $obj->XML->startElement("h");
            $obj->XML->writeAttribute("class", $obj->getNextLineClass());
            $obj->XML->writeElement("hn", CentreonUtils::escapeSecure($hostName), false);
            if ($hostInfos['icon_image']) {
                $obj->XML->writeElement("hico", $hostInfos['icon_image']);
            } else {
                $obj->XML->writeElement("hico", "none");
            }
            $obj->XML->writeElement("hnl", CentreonUtils::escapeSecure(urlencode($hostName)));
            $obj->XML->writeElement("hcount", $count);
            $obj->XML->writeElement("hid", $hostInfos['host_id']);
            $obj->XML->writeElement("hs", _($obj->statusHost[$hostInfos['host_state']]));
            $obj->XML->writeElement("hc", $obj->colorHost[$hostInfos['host_state']]);
            $obj->XML->writeElement(
                "h_details_uri",
                $useDeprecatedPages
                    ? 'main.php?p=20202&o=hd&host_name=' . $hostName
                    : $resourceController->buildHostDetailsUri($hostInfos['host_id'])
            );
            $serviceListingDeprecatedUri = 'main.php?p=20201&o=svc&host_search=' . $hostName;
            $obj->XML->writeElement(
                "s_listing_uri",
                $useDeprecatedPages
                    ? $serviceListingDeprecatedUri . '$statusFilter='
                    : $resourceController->buildListingUri([
                        'filter' => json_encode([
                            'criterias' => [
                                'search' => 'h.name:^' . $hostName . '$',
                            ],
                        ]),
                    ])
            );
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

            foreach ($hostInfos['states'] as $state => $count) {
                $obj->XML->writeElement($state, $count);
            }
            $obj->XML->writeElement("chartIcon", returnSvg("www/img/icons/chart.svg", "var(--icons-fill-color)", 18, 18));
            $obj->XML->writeElement("viewIcon", returnSvg("www/img/icons/view.svg", "var(--icons-fill-color)", 18, 18));
            $obj->XML->endElement();
        }
        $obj->XML->endElement();
    }
}

$obj->XML->endElement();

// Send Header
$obj->header();

// Send XML
$obj->XML->output();
