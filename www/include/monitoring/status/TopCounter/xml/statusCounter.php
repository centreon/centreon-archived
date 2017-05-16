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

$debug = 0;

require_once realpath(dirname(__FILE__) . "/../../../../../../config/centreon.config.php");

require_once _CENTREON_PATH_ . "www/class/centreonXMLBGRequest.class.php";
require_once _CENTREON_PATH_ . 'www/class/centreonLang.class.php';
include_once _CENTREON_PATH_ . "www/include/common/common-Func.php";

session_start();
session_write_close();

if (!isset($_SESSION['centreon'])) {
    exit();
}
$centreon = $_SESSION['centreon'];

$centreonLang = new CentreonLang(_CENTREON_PATH_, $centreon);
$centreonLang->bindLang();

/*
 * Create XML Request Objects
 */
$obj = new CentreonXMLBGRequest(session_id(), 1, 1, 0, $debug, 1, 0);

if (isset($obj->session_id) && CentreonSession::checkSession($obj->session_id, $obj->DB)) {
    $obj->reloadSession();
} else {
    print "Bad Session ID";
    exit();
}

/* *********************************************
* Get active poller only
*/
$pollerList = "";
$request = "SELECT name FROM nagios_server WHERE ns_activate = '1'";
$DBRESULT = $obj->DB->query($request);
while ($d = $DBRESULT->fetchRow()) {
    if ($pollerList != "") {
        $pollerList .= ", ";
    }
    $pollerList .= "'".$d["name"]."'";
}

$DBRESULT->free();

/* *********************************************
 * Get Host stats
 */
$rq1 =  " SELECT count(DISTINCT name), state " .
        " FROM hosts ";
if (!$obj->is_admin) {
    $rq1 .= " , centreon_acl ";
}
$rq1 .= " WHERE name NOT LIKE '_Module_%' ";
if (!$obj->is_admin) {
    $rq1 .= " AND hosts.host_id = centreon_acl.host_id ";
}
$rq1 .= " AND hosts.enabled = 1 ";
$rq1 .= $obj->access->queryBuilder("AND", "centreon_acl.group_id", $obj->grouplistStr);
$rq1 .= " GROUP BY state";

$hostCounter = 0;
$host_stat = array(0 => 0, 1 => 0, 2 => 0, 3 => 0, 4=> 0);
$DBRESULT = $obj->DBC->query($rq1);
while ($data = $DBRESULT->fetchRow()) {
    $host_stat[$data["state"]] = $data["count(DISTINCT name)"];
    $hostCounter += $host_stat[$data["state"]];
}
$DBRESULT->free();

/* *********************************************
 * Get Service stats
 */
if (!$obj->is_admin) {
    $rq2 =  " SELECT COUNT(DISTINCT CONCAT(hosts.host_id,';', services.service_id)) as number, services.state" .
            " FROM hosts, services, instances, centreon_acl " .
            " WHERE instances.instance_id = hosts.instance_id ". 
            " AND instances.deleted = 0 ".
            " AND services.host_id = centreon_acl.host_id ".
            " AND services.service_id = centreon_acl.service_id " .
            " AND centreon_acl.group_id IN (".$obj->grouplistStr.") ".
            " AND hosts.host_id = services.host_id".
            " AND hosts.enabled = 1 " .
            " AND services.enabled = 1 " .
            " AND (hosts.name NOT LIKE '_Module_%' OR hosts.name LIKE '_Module_meta%')".
            " GROUP BY services.state";
} else {
    $rq2 =  " SELECT count(services.state) AS number, services.state" .
            " FROM hosts, services, instances" .
            " WHERE instances.deleted = 0 ".
            " AND hosts.enabled = 1 " .
            " AND services.enabled = 1 " .
            " AND instances.instance_id = hosts.instance_id ". 
            " AND hosts.host_id = services.host_id".
            " AND (hosts.name NOT LIKE '_Module_%' OR hosts.name LIKE '_Module_meta%') ".
            " GROUP BY services.state";
}
$serviceCounter = 0;
$svc_stat = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0, 6=>0, 7=>0, 8=>0);
$DBRESULT = $obj->DBC->query($rq2);
while ($data = $DBRESULT->fetchRow()) {
    $svc_stat[$data["state"]] = $data["number"];
    $serviceCounter += $svc_stat[$data["state"]];
}
$DBRESULT->free();

/* ********************************************
 *  Get Real non-ok Status
 */
if (!$obj->is_admin) {
    $rq3 =  " SELECT COUNT(DISTINCT CONCAT(s.service_id,';', s.host_id)) as number, s.state_type, s.acknowledged, s.scheduled_downtime_depth, s.state " .
            " FROM services s, hosts h, centreon_acl " .
            " WHERE centreon_acl.host_id IS NOT NULL " .
            "   AND s.host_id = centreon_acl.host_id ".
            "   AND s.service_id = centreon_acl.service_id " .
            " 	AND s.host_id = h.host_id " .
            "   AND s.state <> '0' " .
            "	AND s.scheduled_downtime_depth = '0' " .
            "	AND s.acknowledged = '0' " .
            "   AND h.state = '0' " .
            "   AND s.enabled = 1 " .
            "   AND h.enabled = 1 " .
            "   AND (h.name NOT LIKE '_Module_%' OR h.name LIKE '_Module_meta%') ".
            "   AND centreon_acl.group_id IN (".$obj->grouplistStr.") ".
            " GROUP BY s.state, s.acknowledged, s.scheduled_downtime_depth";
} else {
    $rq3 =  " SELECT COUNT(DISTINCT CONCAT(s.service_id,';', s.host_id)) as number, s.state_type, s.acknowledged, s.scheduled_downtime_depth, s.state " .
            " FROM services s, hosts h " .
            " WHERE s.host_id = h.host_id " .
            "   AND s.state <> '0' " .
            "	AND s.scheduled_downtime_depth = '0' " .
            "	AND s.acknowledged = '0' " .
            "	AND h.state = '0' " .
            "   AND s.enabled = 1 " .
            "   AND h.enabled = 1 " .
            "   AND (h.name NOT LIKE '_Module_%' OR h.name LIKE '_Module_meta%') ".
            " GROUP BY s.state, s.acknowledged, s.scheduled_downtime_depth";
}
$DBRESULT = $obj->DBC->query($rq3);
while ($data = $DBRESULT->fetchRow()) {
    $svc_stat[$data["state"] + 5] = $data["number"];
}
$DBRESULT->free();
unset($data);

/* ********************************************
 * Check Poller Status
 */
$status = 0;
$latency = 0;
$activity = 0;
$error = "";
$pollerListInError = "";
$pollersWithLatency = array();

$timeUnit = 300;

$inactivInstance = "";
$pollerInError = "";

if ($pollerList != "") {
    $request = "SELECT `last_alive` AS last_update, `running`, name, instance_id FROM instances WHERE deleted = 0 
                AND name IN ($pollerList)";
    $DBRESULT = $obj->DBC->query($request);
    while ($data = $DBRESULT->fetchRow()) {
        /* Get Instance ID */
        if ($pollerList != "") {
            $pollerList .= ", ";
        }
        $pollerList .= "'".$data["instance_id"]."'";

        /*
         * Running
         */
        if ($status != 2 && ($data["running"] == 0 || (time() - $data["last_update"] >= $timeUnit * 5))) {
            $status = 1;
            $pollerInError = $data["name"];
        }
        if ($data["running"] == 0 || (time() - $data["last_update"] >= $timeUnit * 10)) {
            $status = 2;
            $pollerInError = $data["name"];
        }
        if ($pollerListInError != "" && $pollerInError != "") {
            $pollerListInError .= ", ";
        }
        $pollerListInError .= $pollerInError;
        $pollerInError = '';

        /*
         * Activity
         */
        if ($activity != 2 && (time() - $data["last_update"] >= $timeUnit * 5)) {
            $activity = 2;
            if ($inactivInstance != "") {
                $inactivInstance .= ",";
            }
            $inactivInstance .= $data["name"]." [".(time() - $data["last_update"])."s / ".($timeUnit * 5)."s]";
        } elseif ((time() - $data["last_update"] >= $timeUnit * 10)) {
            $activity = 1;
            if ($inactivInstance != "") {
                $inactivInstance .= ",";
            }
            $inactivInstance .= $data["name"]." [".(time() - $data["last_update"])."s / ".($timeUnit * 10)."s]";
        }
    }
}
$DBRESULT->free();
if ($pollerListInError != '') {
    $error = "$pollerListInError not running";
}

if ($pollerList != "") {
    $request =  " SELECT stat_value, i.instance_id, name " .
                " FROM `nagios_stats` ns, instances i " .
                " WHERE ns.stat_label = 'Service Check Latency' " .
                "	AND ns.stat_key LIKE 'Average' " .
                "	AND ns.instance_id = i.instance_id" .
                "	AND i.deleted = 0" .
                "   AND i.instance_id IN ($pollerList)";
    $DBRESULT = $obj->DBC->query($request);
    while ($data = $DBRESULT->fetchRow()) {
        if (!$latency && $data["stat_value"] >= 60) {
            $latency = 1;
            $pollersWithLatency[$data['instance_id']] = $data['name'];
        }
        if ($data["stat_value"] >= 120) {
            $latency = 2;
            $pollersWithLatency[$data['instance_id']] = $data['name'];
        }
    }
    $DBRESULT->free();
    unset($data);
}

/* ********************************************
 * Error Messages
 */
if ($status != 0) {
    $errorPstt = "$error";
} else {
    $errorPstt = _("OK: all pollers are running");
}

if ($latency && count($pollersWithLatency)) {
        $errorLtc = sprintf(_("Latency detected on %s; check configuration for better optimisation"), implode(',', $pollersWithLatency));
} else {
        $errorLtc = _("OK: no latency detected on your platform");
}

if ($activity != 0) {
    $errorAct = _("Some database poller updates are not active; check your Monitoring platform");
} else {
    $errorAct = _("OK: all database poller updates are active");
}

/* *********************************************
 * Create Buffer
 */
$obj->XML = new CentreonXML();
$obj->XML->startElement("reponse");
$obj->XML->startElement("infos");
$obj->XML->writeElement("filetime", time());
$obj->XML->endElement();
$obj->XML->startElement("s");
$obj->XML->writeElement("th", $hostCounter);
$obj->XML->writeElement("ts", $serviceCounter);
$obj->XML->writeElement("o", $svc_stat["0"]);
$obj->XML->writeElement("w", $svc_stat["1"]);
$obj->XML->writeElement("wU", $svc_stat["6"]);
$obj->XML->writeElement("c", $svc_stat["2"]);
$obj->XML->writeElement("cU", $svc_stat["7"]);
$obj->XML->writeElement("un1", $svc_stat["3"]);
$obj->XML->writeElement("un1U", $svc_stat["8"]);
$obj->XML->writeElement("p1", $svc_stat["4"]);
$obj->XML->writeElement("up", $host_stat["0"]);
$obj->XML->writeElement("d", $host_stat["1"]);
$obj->XML->writeElement("un2", $host_stat["2"]);
$obj->XML->writeElement("p2", $host_stat["4"]);
$obj->XML->endElement();
$obj->XML->startElement("m");
$obj->XML->writeElement("pstt", $status);
$obj->XML->writeElement("ltc", $latency);
$obj->XML->writeElement("act", $activity);
$obj->XML->writeElement("errorPstt", $errorPstt);
$obj->XML->writeElement("errorLtc", $errorLtc);
$obj->XML->writeElement("errorAct", $errorAct);
$obj->XML->endElement();
$obj->XML->endElement();

/*
 * Send headers
 */
$obj->header();

/*
 * Display XML data
 */
$obj->XML->output();
