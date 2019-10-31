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

require_once realpath(__DIR__ . "/../../../../../../config/centreon.config.php");
include_once _CENTREON_PATH_ . "www/class/centreonUtils.class.php";
include_once _CENTREON_PATH_ . "www/class/centreonXMLBGRequest.class.php";
include_once _CENTREON_PATH_ . "www/include/monitoring/status/Common/common-Func.php";
include_once _CENTREON_PATH_ . "www/include/common/common-Func.php";
include_once _CENTREON_PATH_ . "www/class/centreonService.class.php";

// Create XML Request Objects
CentreonSession::start(1);
$obj = new CentreonXMLBGRequest(session_id(), 1, 1, 0, 1);
$svcObj = new CentreonService($obj->DB);

if (!isset($obj->session_id) || !CentreonSession::checkSession($obj->session_id, $obj->DB)) {
    print "Bad Session ID";
    exit();
}

// Set Default Poller
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
$sort_type = $obj->checkArgument("sort_type", $_GET, "host_name");

// values saved in the session
$instance = filter_var($obj->defaultPoller, FILTER_VALIDATE_INT);
$hostgroup = filter_var($obj->defaultHostgroups, FILTER_VALIDATE_INT);

$grouplistStr = $obj->access->getAccessGroupsString();

// Get Host status
$rq1 =  " SELECT SQL_CALC_FOUND_ROWS DISTINCT hg.name AS alias, h.host_id id, h.name as host_name, hgm.hostgroup_id, "
    . "h.state hs, h.icon_image FROM hostgroups hg, hosts_hostgroups hgm, hosts h ";
if (!$obj->is_admin) {
    $rq1 .= ", centreon_acl ";
}
$rq1 .= " WHERE h.host_id = hgm.host_id".
        " AND hgm.hostgroup_id = hg.hostgroup_id".
        " AND h.enabled = '1' ".
        " AND h.name not like '_Module_%'";
if (!$obj->is_admin) {
    $rq1 .= $obj->access->queryBuilder("AND", "h.host_id", "centreon_acl.host_id")
        . $obj->access->queryBuilder("AND", "group_id", $grouplistStr) . " "
        . $obj->access->queryBuilder("AND", "hg.name", $obj->access->getHostGroupsString("NAME"));
}
if ($instance != -1) {
    $rq1 .=     " AND h.instance_id = ". (int)$instance;
}
if ($o == "svcgrid_pb" || $o == "svcOVHG_pb") {
    $rq1 .= " AND h.host_id IN (" .
        " SELECT s.host_id FROM services s " .
        " WHERE s.state != 0 AND s.state != 4 AND s.enabled = 1)";
}
if ($o == "svcOVHG_ack_0") {
    $rq1 .=     " AND h.host_id IN (" .
        " SELECT s.host_id FROM services s " .
        " WHERE s.acknowledged = 0 AND s.state != 0 AND s.state != 4 AND s.enabled = 1)";
}
if ($o == "svcOVHG_ack_1") {
    $rq1 .=     " AND h.host_id IN (" .
        " SELECT s.host_id FROM services s " .
        " WHERE s.acknowledged = 1 AND s.state != 0 AND s.state != 4 AND s.enabled = 1)";
}
if ($search != "") {
    $rq1 .= " AND h.name like '%" . CentreonDB::escape($search) . "%' ";
}
if ($hostgroup) {
    $rq1 .= " AND hg.hostgroup_id IN (" . $hostgroup . ")";
}
$rq1 .= " AND h.enabled = 1 ";
$rq1 .= " ORDER BY " . CentreonDB::escape($sort_type) . ", hg.name " . CentreonDB::escape($order) . ", host_name ASC ";
$rq1 .= " LIMIT " . (int)($num * $limit) . ", " . (int)$limit;

$tabH = array();
$tabHG = array();
$tab_finalH = array();

$dbResult = $obj->DBC->query($rq1);
$numRows = $obj->DBC->numberRows();
while ($ndo = $dbResult->fetchRow()) {
    if (!isset($tab_finalH[$ndo["alias"]])) {
        $tab_finalH[$ndo["alias"]] = array($ndo["host_name"] => array());
    }
    $tab_finalH[$ndo["alias"]][$ndo["host_name"]]["cs"] = $ndo["hs"];
    $tab_finalH[$ndo["alias"]][$ndo["host_name"]]["icon"] = $ndo['icon_image'];
    $tab_finalH[$ndo["alias"]][$ndo["host_name"]]["tab_svc"] = array();
    $tabH[$ndo["host_name"]] = $ndo["id"];
    $tabHG[$ndo["alias"]] = $ndo["hostgroup_id"];
}
$dbResult->free();

// Get Services status
$rq1 =  " SELECT DISTINCT s.service_id, h.name AS host_name, s.description, s.state svcs," .
    " (CASE s.state WHEN 0 THEN 3 WHEN 2 THEN 0 WHEN 3 THEN 2 ELSE s.state END) AS tri" .
    " FROM services s, hosts h ";
if (!$obj->is_admin) {
    $rq1 .= ", centreon_acl ";
}
$rq1 .=  " WHERE h.host_id = s.host_id ".
    " AND h.name NOT LIKE '_Module_%' ".
    " AND h.enabled = '1' " .
    " AND s.enabled = '1' ";
$rq1 .= $obj->access->queryBuilder("AND", "h.host_id", "centreon_acl.host_id") .
    $obj->access->queryBuilder("AND", "s.service_id", "centreon_acl.service_id") .
    $obj->access->queryBuilder("AND", "group_id", $grouplistStr);
if ($o == "svcgrid_pb" || $o == "svcOVHG_pb" || $o == "svcgrid_ack_0" || $o == "svcOVHG_ack_0") {
    $rq1 .= " AND s.state != 0 AND s.state != 4 ";
}
if ($o == "svcgrid_ack_1" || $o == "svcOVHG_ack_1") {
    $rq1 .= "AND s.acknowledged = 1";
}
if ($o == "svcgrid_ack_0" || $o == "svcOVHG_ack_0") {
    $rq1 .= "AND s.acknowledged = 0";
}
if ($search != "") {
    $rq1 .= " AND h.name like '%" . CentreonDB::escape($search) . "%' ";
}
if ($instance != -1) {
    $rq1 .= " AND h.instance_id = " . (int)$instance;
}
$rq1 .= " ORDER BY tri ASC, s.description ASC";

$tabService = array();
$tabHost = array();
$dbResult = $obj->DBC->query($rq1);
while ($ndo = $dbResult->fetchRow()) {
    if (!isset($tabService[$ndo["host_name"]])) {
        $tabService[$ndo["host_name"]] = array();
    }
    if (!isset($tabService[$ndo["host_name"]])) {
        $tabService[$ndo["host_name"]] = array("tab_svc" => array());
    }
    $tabService[$ndo["host_name"]]["tab_svc"][$ndo["description"]] = $ndo["svcs"];
    $tabHost[$ndo["host_name"]] = $ndo["service_id"];
}
$dbResult->free();

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
                        $obj->XML->startElement("svc");
                        $obj->XML->writeElement("sn", CentreonUtils::escapeSecure($svc));
                        $obj->XML->writeElement("snl", CentreonUtils::escapeSecure(urlencode($svc)));
                        $obj->XML->writeElement("sc", $obj->colorService[$state]);
                        $obj->XML->writeElement("svc_id", $svcObj->getServiceId($svc, $host_name));
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
