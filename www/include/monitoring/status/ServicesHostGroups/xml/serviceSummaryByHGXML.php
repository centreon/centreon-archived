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

/***************************************************
 * Check Arguments From GET tab
 */
$o = $obj->checkArgument("o", $_GET, "h");
$p = $obj->checkArgument("p", $_GET, "2");
$hg = $obj->checkArgument("hg", $_GET, "");
$num = $obj->checkArgument("num", $_GET, 0);
$limit = $obj->checkArgument("limit", $_GET, 20);
$instance = $obj->checkArgument("instance", $_GET, $obj->defaultPoller);
$hostgroups = $obj->checkArgument("hostgroups", $_GET, $obj->defaultHostgroups);
$search = $obj->checkArgument("search", $_GET, "");
$sort_type = $obj->checkArgument("sort_type", $_GET, "alias");
$order = $obj->checkArgument("order", $_GET, "ASC");
$dateFormat = $obj->checkArgument("date_time_format_status", $_GET, "Y/m/d H:i:s");
$grouplistStr = $obj->access->getAccessGroupsString();


/****************************************
 * Get Host status
 *
 */
$rq1 = "SELECT SQL_CALC_FOUND_ROWS DISTINCT h.name as host_name, hg.name as hgname, hgm.hostgroup_id, h.host_id, h.state, h.icon_image "
        . "FROM hostgroups hg, hosts_hostgroups hgm, hosts h ";

if (!$obj->is_admin) {
    $rq1 .= ", centreon_acl ";
}

$rq1 .= "WHERE h.host_id = hgm.host_id "
        . "AND hgm.hostgroup_id = hg.hostgroup_id "
        . "AND h.enabled = '1' "
        . "AND h.name not like '_Module_%' ";

if (!$obj->is_admin) {
    $rq1 .= $obj->access->queryBuilder("AND", "h.host_id", "centreon_acl.host_id") . " "
            . $obj->access->queryBuilder("AND", "group_id", $grouplistStr) . " "
            . $obj->access->queryBuilder("AND", "hg.hostgroup_id", $obj->access->getHostGroupsString("ID")) . " ";
}

if ($instance != -1) {
    $rq1 .= "AND h.instance_id = " . $instance . " ";
}

if ($o == "svcgridHG_pb" || $o == "svcSumHG_pb") {
    $rq1 .= " AND h.host_id IN ( "
            . "SELECT s.host_id FROM services s "
            . "WHERE s.state != 0 AND s.state != 4 AND s.enabled = 1) ";
}

if ($o == "svcSumHG_ack_0") {
    $rq1 .= "AND h.host_id IN ( "
            . "SELECT s.host_id FROM services s "
            . "WHERE s.acknowledged = 0 AND s.state != 0 AND s.state != 4 AND s.enabled = 1) ";
}

if ($o == "svcSumHG_ack_1") {
    $rq1 .= "AND h.host_id IN ( "
            . "SELECT s.host_id FROM services s "
            . "WHERE s.acknowledged = 1 AND s.state != 0 AND s.state != 4 AND s.enabled = 1) ";
}

if ($search != "") {
    $rq1 .= "AND h.name like '%" . $search . "%' ";
}

if ($hostgroups) {
    $rq1 .= "AND hg.hostgroup_id IN (" . $hostgroups . ") ";
}

$rq1 .= "AND h.enabled = 1 "
        . "ORDER BY " . $sort_type . ", h.name " . $order . " "
        . "LIMIT " . ($num * $limit) . "," . $limit . " ";

$obj->XML = new CentreonXML();
$obj->XML->startElement("reponse");

$class = "list_one";
$ct = 0;

$tab_final = array();
$tabHGUrl = array();
$DBRESULT = $obj->DBC->query($rq1);
$numRows = $obj->DBC->numberRows();

$obj->XML->startElement("i");
$obj->XML->writeElement("numrows", $numRows);
$obj->XML->writeElement("num", $num);
$obj->XML->writeElement("limit", $limit);
$obj->XML->writeElement("p", $p);
$obj->XML->writeElement("s", "1");
$obj->XML->endElement();

while ($ndo = $DBRESULT->fetchRow()) {
    if (!isset($tab_final[$ndo["hgname"]])) {
        $tab_final[$ndo["hgname"]] = array();
    }
    if (!isset($tab_final[$ndo["hgname"]][$ndo["host_name"]])) {
        $tab_final[$ndo["hgname"]][$ndo["host_name"]] = array("0" => 0, "1" => 0, "2" => 0, "3" => 0, "4" => 0);
    }
    if ($o != "svcSum_pb" && $o != "svcSum_ack_1" && $o != "svcSum_ack_0") {
        $tab_final[$ndo["hgname"]][$ndo["host_name"]][0] = $obj->monObj->getServiceStatusCount($ndo["host_name"], $obj, $o, 0, $obj);
    }
    $tab_final[$ndo["hgname"]][$ndo["host_name"]][1] = 0 + $obj->monObj->getServiceStatusCount($ndo["host_name"], $obj, $o, 1, $obj);
    $tab_final[$ndo["hgname"]][$ndo["host_name"]][2] = 0 + $obj->monObj->getServiceStatusCount($ndo["host_name"], $obj, $o, 2, $obj);
    $tab_final[$ndo["hgname"]][$ndo["host_name"]][3] = 0 + $obj->monObj->getServiceStatusCount($ndo["host_name"], $obj, $o, 3, $obj);
    $tab_final[$ndo["hgname"]][$ndo["host_name"]][4] = 0 + $obj->monObj->getServiceStatusCount($ndo["host_name"], $obj, $o, 4, $obj);
    $tab_final[$ndo["hgname"]][$ndo["host_name"]]["cs"] = $ndo["state"];
    $tab_final[$ndo["hgname"]][$ndo["host_name"]]["hid"] = $ndo["host_id"];
    $tab_final[$ndo["hgname"]][$ndo["host_name"]]["icon"] = $ndo["icon_image"];
}
$DBRESULT->free();

$hg = "";
$count = 0;
if (isset($tab_final)) {
    foreach ($tab_final as $hg_name => $tab_host) {
        foreach ($tab_host as $host_name => $tab) {
            if (isset($hg_name) && $hg != $hg_name) {
                if ($hg != "") {
                    $obj->XML->endElement();
                }
                $hg = $hg_name;
                $obj->XML->startElement("hg");
                $obj->XML->writeElement("hgn", CentreonUtils::escapeSecure($hg_name));
            }
            $obj->XML->startElement("l");
            $obj->XML->writeAttribute("class", $obj->getNextLineClass());
            $obj->XML->writeElement("sc", $tab[2]);
            $obj->XML->writeElement("scc", $obj->colorService[2]);
            $obj->XML->writeElement("sw", $tab[1]);
            $obj->XML->writeElement("swc", $obj->colorService[1]);
            $obj->XML->writeElement("su", $tab[3]);
            $obj->XML->writeElement("suc", $obj->colorService[3]);
            $obj->XML->writeElement("sk", $tab[0]);
            $obj->XML->writeElement("skc", $obj->colorService[0]);
            $obj->XML->writeElement("sp", $tab[4]);
            $obj->XML->writeElement("spc", $obj->colorService[4]);
            $obj->XML->writeElement("o", $ct++);
            $obj->XML->writeElement("hn", CentreonUtils::escapeSecure($host_name), false);
            if (isset($tab["icon"]) && $tab["icon"]) {
                $obj->XML->writeElement("hico", $tab["icon"]);
            } else {
                $obj->XML->writeElement("hico", "none");
            }
            $obj->XML->writeElement("hnl", CentreonUtils::escapeSecure(urlencode($host_name)));
            $obj->XML->writeElement("hid", $tab["hid"]);
            $obj->XML->writeElement("hcount", $count);
            $obj->XML->writeElement("hs", $obj->statusHost[$tab["cs"]]);
            $obj->XML->writeElement("hc", $obj->colorHost[$tab["cs"]]);
            $obj->XML->endElement();
            $count++;
        }
    }
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
