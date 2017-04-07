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

ini_set("display_errors", "Off");

require_once realpath(dirname(__FILE__) . "/../../../../../../config/centreon.config.php");

include_once _CENTREON_PATH_ . "www/class/centreonUtils.class.php";
include_once _CENTREON_PATH_ . "www/class/centreonXMLBGRequest.class.php";
include_once _CENTREON_PATH_ . "www/include/monitoring/status/Common/common-Func.php";
include_once _CENTREON_PATH_ . "www/include/common/common-Func.php";

/*
 * Create XML Request Objects
 */
CentreonSession::start(1);
$obj = new CentreonXMLBGRequest(session_id(), 1, 1, 0, 1);


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

/* **************************************************
 * Check Arguments From GET tab
 */
$o          = $obj->checkArgument("o", $_GET, "h");
$p          = $obj->checkArgument("p", $_GET, "2");
$nc         = $obj->checkArgument("nc", $_GET, "0");
$num        = $obj->checkArgument("num", $_GET, 0);
$limit      = $obj->checkArgument("limit", $_GET, 20);
$instance   = $obj->checkArgument("instance", $_GET, $obj->defaultPoller);
$hostgroups = $obj->checkArgument("hostgroups", $_GET, $obj->defaultHostgroups);
$search     = $obj->checkArgument("search", $_GET, "");
$sort_type  = $obj->checkArgument("sort_type", $_GET, "host_name");
$order      = $obj->checkArgument("order", $_GET, "ASC");
$dateFormat = $obj->checkArgument("date_time_format_status", $_GET, "Y/m/d H:i:s");
/*
 * Backup poller selection
 */
$obj->setInstanceHistory($instance);

$service = array();
$host_status = array();
$service_status = array();
$host_services = array();
$metaService_status = array();
$tab_host_service = array();
$tabIcone = array();

/** *********************************************
 * Get status
 */
$rq1 = "SELECT SQL_CALC_FOUND_ROWS DISTINCT hosts.name, hosts.state, hosts.icon_image, hosts.host_id "
    . "FROM hosts ";
if ($hostgroups) {
    $rq1 .= ", hosts_hostgroups hg, hostgroups hg2 ";
}

if (!$obj->is_admin) {
    $rq1 .= ", centreon_acl ";
}

$rq1 .= "WHERE hosts.name NOT LIKE '_Module_%' "
    . "AND hosts.enabled = 1 "
    . $obj->access->queryBuilder("AND", "hosts.host_id", "centreon_acl.host_id") . " "
    . $obj->access->queryBuilder("AND", "group_id", $obj->grouplistStr) . " ";

if ($o == "svcgrid_pb" || $o == "svcOV_pb" || $o == "svcSum_pb" || $o == "svcgrid_ack_0" || $o == "svcOV_ack_0" || $o == "svcSum_ack_0") {
    $rq1 .= "AND hosts.host_id IN ( "
    . "SELECT s.host_id FROM services s "
    . "WHERE s.state != 0 "
    . "AND s.state != 4 "
    . "AND s.enabled = 1) ";
}

if ($o == "svcgrid_ack_1" || $o == "svcOV_ack_1" || $o == "svcSum_ack_1") {
    $rq1 .= "AND hosts.host_id IN ( "
        . "SELECT s.host_id FROM services s "
        . "WHERE s.acknowledged = '1' "
        . "AND s.enabled = 1) ";
}

if ($search != "") {
    $rq1 .= "AND hosts.name like '%" . $search . "%' ";
}

if ($instance != -1) {
    $rq1 .= "AND hosts.instance_id = " . $instance . " ";
}

if ($hostgroups) {
    $rq1 .= " AND hosts.host_id = hg.host_id "
        . "AND hg.hostgroup_id IN (".$hostgroups.") "
        . "AND hg.hostgroup_id = hg2.hostgroup_id ";
}

# ORDER BY
switch ($sort_type) {
    case 'current_state':
        $rq1 .= "ORDER BY hosts.state " . $order . ",hosts.name ";
        break;
    default:
        $rq1 .= "ORDER BY hosts.name " . $order . " ";
        break;
}

    # LIMIT
$rq1 .= "LIMIT " . ($num * $limit) . "," . $limit . " ";


/*
 * Execute request
 */
$DBRESULT = $obj->DBC->query($rq1);
$numRows = $obj->DBC->numberRows();

/*
 * Info / Pagination
 */
$obj->XML->startElement("reponse");
$obj->XML->startElement("i");
$obj->XML->writeElement("numrows", $numRows);
$obj->XML->writeElement("num", $num);
$obj->XML->writeElement("limit", $limit);
$obj->XML->writeElement("p", $p);
$obj->XML->endElement();

$ct = 0;
$tab_final = array();
$DBRESULT_NDO1 = $obj->DBC->query($rq1);
while ($ndo = $DBRESULT_NDO1->fetchRow()) {
    $tab_final[$ndo["name"]]["nb_service_k"] = 0;
    $tab_final[$ndo["name"]]["host_id"] = $ndo["host_id"];
    if ($o != "svcSum_pb" && $o != "svcSum_ack_1"  && $o !=  "svcSum_ack_0") {
        $tab_final[$ndo["name"]]["nb_service_k"] = $obj->monObj->getServiceStatusCount($ndo["name"], $obj, $o, 0, $obj);
    }
    $tab_final[$ndo["name"]]["nb_service_w"] = 0 + $obj->monObj->getServiceStatusCount($ndo["name"], $obj, $o, 1, $obj);
    $tab_final[$ndo["name"]]["nb_service_c"] = 0 + $obj->monObj->getServiceStatusCount($ndo["name"], $obj, $o, 2, $obj);
    $tab_final[$ndo["name"]]["nb_service_u"] = 0 + $obj->monObj->getServiceStatusCount($ndo["name"], $obj, $o, 3, $obj);
    $tab_final[$ndo["name"]]["nb_service_p"] = 0 + $obj->monObj->getServiceStatusCount($ndo["name"], $obj, $o, 4, $obj);
    $tab_final[$ndo["name"]]["cs"] = $ndo["state"];

    if (isset($ndo["icon_image"]) && $ndo["icon_image"] != "") {
        $tabIcone[$ndo["name"]] = $ndo["icon_image"];
    } else {
        $tabIcone[$ndo["name"]] = "none";
    }
}

foreach ($tab_final as $host_name => $tab) {
    $obj->XML->startElement("l");
    $obj->XML->writeAttribute("class", $obj->getNextLineClass());
    $obj->XML->writeElement("o", $ct++);
    $obj->XML->writeElement("hn", CentreonUtils::escapeSecure($host_name), false);
    $obj->XML->writeElement("hnl", CentreonUtils::escapeSecure(urlencode($host_name)));
    $obj->XML->writeElement("hid", $tab["host_id"], false);
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
    $obj->XML->endElement();
}

if (!$ct) {
    $obj->XML->writeElement("infos", "none");
}
$obj->XML->endElement();

/*
 * Send Header
 */
$obj->header();

/*
 * Send XML
 */
$obj->XML->output();
