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

include_once _CENTREON_PATH_ . "www/class/centreonUtils.class.php";

include_once _CENTREON_PATH_ . "www/class/centreonXMLBGRequest.class.php";
include_once _CENTREON_PATH_ . "www/include/monitoring/status/Common/common-Func.php";
include_once _CENTREON_PATH_ . "www/include/common/common-Func.php";
include_once _CENTREON_PATH_ . "www/class/centreonService.class.php";

/*
 * Create XML Request Objects
 */
CentreonSession::start(1);
$obj = new CentreonXMLBGRequest(session_id(), 1, 1, 0, 1);
$svcObj = new CentreonService($obj->DB);

if (!isset($obj->session_id) || !CentreonSession::checkSession($obj->session_id, $obj->DB)) {
    print "Bad Session ID";
    exit();
}

/*
 * Set Default Poller
 */
$obj->getDefaultFilters();

/*
 *  Check Arguments from GET and session
 */
// integer values from $_GET
$p = filter_input(INPUT_GET, 'p', FILTER_VALIDATE_INT, array('options' => array('default' => 2)));
$num = filter_input(INPUT_GET, 'num', FILTER_VALIDATE_INT, array('options' => array('default' => 0)));
$limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT, array('options' => array('default' => 20)));

$order = filter_input(
    INPUT_GET,
    'order',
    FILTER_VALIDATE_REGEXP,
    array(
        'options' => array(
            'default' => "ASC",
            'regexp' => '/^(ASC|DESC)$/'
        )
    )
);

// string values from the $_GET sanitized using the checkArgument() which call CentreonDB::escape() method
$o = $obj->checkArgument("o", $_GET, "h");
$search = $obj->checkArgument("search", $_GET, "");
$hSearch = $obj->checkArgument("host_search", $_GET, "");
$sgSearch = $obj->checkArgument("sg_search", $_GET, "");
$sort_type = $obj->checkArgument("sort_type", $_GET, "host_name");

// values saved in the session
$instance = $obj->defaultPoller;
$dateFormat = "Y/m/d H:i:s";

/*
 * Backup poller selection
 */
$obj->setInstanceHistory($instance);

/**
 * Prepare pagination
 */
// Service search
$s_search = "";
// Display service problems
if ($o == "svcgridSG_pb" || $o == "svcOVSG_pb") {
    $s_search .= " AND s.state != 0 AND s.state != 4 " ;
}

// Display acknowledged services
if ($o == "svcgridSG_ack_1" || $o == "svcOVSG_ack_1") {
    $s_search .= " AND s.acknowledged = '1' ";
}

// Display not acknowledged services
if ($o == "svcgridSG_ack_0" || $o == "svcOVSG_ack_0") {
    $s_search .= " AND s.state != 0 AND s.state != 4 AND s.acknowledged = 0 " ;
}

$query = "SELECT SQL_CALC_FOUND_ROWS DISTINCT sg.servicegroup_id, h.host_id
    FROM servicegroups sg
    INNER JOIN services_servicegroups sgm ON sg.servicegroup_id = sgm.servicegroup_id
    INNER JOIN services s ON s.service_id = sgm.service_id
    INNER JOIN  hosts h ON sgm.host_id = h.host_id AND h.host_id = s.host_id "
    . $obj->access->getACLHostsTableJoin($obj->DBC, "h.host_id")
    . $obj->access->getACLServicesTableJoin($obj->DBC, "s.service_id") .
    " WHERE 1 = 1  ";

// Servicegroup ACL
$query .= $obj->access->queryBuilder("AND", "sg.servicegroup_id", $obj->access->getServiceGroupsString("ID"));

// Servicegroup search
if ($sgSearch != "") {
    $query .= "AND sg.name = '" . CentreonDB::escape($sgSearch) . "' ";
}

// Host search
$h_search = '';
if ($hSearch != "") {
    $h_search .= "AND h.name like '%" . CentreonDB::escape($hSearch) . "%' ";
}

$query .= $h_search . $s_search;

// Poller search
if (!empty($instance) && $instance !== -1) {
    $query .= " AND h.instance_id = " . (int) $instance . " ";
}

$query .= "ORDER BY sg.name " . $order . " LIMIT " . ((int) $num * (int) $limit) . "," . (int) $limit;

$dbResult = $obj->DBC->query($query);

$numRows = $obj->DBC->numberRows();


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

// Construct query for servicegroups search
$sg_search = "";
if ($numRows > 0) {
    $sg_search .= "AND (";
    $servicegroups = array();
    while ($row = $dbResult->fetchRow()) {
        $servicesgroups[$row['servicegroup_id']][] = $row['host_id'];
    }
    $servicegroupsSql1 = array();
    foreach ($servicesgroups as $key => $value) {
        $hostsSql = array();
        foreach ($value as $hostId) {
            $hostsSql[] = (int) $hostId;
        }
        $servicegroupsSql1[] = "(sg.servicegroup_id = " . (int) $key .
            " AND h.host_id IN (" . implode(',', $hostsSql) . ")) ";
    }
    $sg_search .= implode(" OR ", $servicegroupsSql1);
    $sg_search .= ") ";
    if ($sgSearch != "") {
        $sg_search .= "AND sg.name = '" . CentreonDB::escape($sgSearch) . "' ";
    }

    $query2 = "SELECT SQL_CALC_FOUND_ROWS COUNT(s.state) AS count_state, sg.name AS sg_name, h.name AS host_name,
        h.state AS host_state, h.icon_image, h.host_id, s.state,
        (CASE s.state WHEN 0 THEN 3 WHEN 2 THEN 0 WHEN 3 THEN 2 ELSE s.state END) AS tri
        FROM servicegroups sg, services_servicegroups sgm, services s, hosts h
        WHERE h.host_id = s.host_id AND s.host_id = sgm.host_id
        AND s.service_id=sgm.service_id AND sg.servicegroup_id=sgm.servicegroup_id "
        . $s_search
        . $sg_search
        . $h_search
        . $obj->access->queryBuilder("AND", "sg.servicegroup_id", $obj->access->getServiceGroupsString("ID"))
        . $obj->access->queryBuilder("AND", "s.service_id", $obj->access->getServicesString("ID", $obj->DBC)) .
        " GROUP BY sg_name,host_name,host_state,icon_image,host_id, s.state ORDER BY tri ASC";
    
    $dbResult = $obj->DBC->query($query2);

    $states = array(
        0 => 'sk',
        1 => 'sw',
        2 => 'sc',
        3 => 'su',
        4 => 'sp'
    );

    $sg_list = array();
    while ($tab = $dbResult->fetchRow()) {
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

            foreach ($hostInfos['states'] as $state => $count) {
                $obj->XML->writeElement($state, $count);
            }
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
