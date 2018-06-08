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

/*
 * Get session
 */
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
    print 'Bad Session ID';
    exit();
}

/*
 * Connection to Redis
 */
$redis = new Redis();
$redis->connect($conf_centreon['redisServer'], $conf_centreon['redisPort']);
$redis->auth($conf_centreon['redisPassword']);

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
    $pollerList .= "'" . $d['name'] . "'";
}

$DBRESULT->free();

/* *********************************************
 * Get Host stats
 */

$hst_up = $redis->sCard('stateh:0');
$hst_down = $redis->sCard('stateh:2');
$hst_unreachable = $redis->sCard('stateh:3');
$hst_pending = $redis->sCard('stateh:4');

//$rq1 =  " SELECT count(DISTINCT name), state " .
//        " FROM hosts ";
//if (!$obj->is_admin) {
//    $rq1 .= " , centreon_acl ";
//}
//$rq1 .= " WHERE name NOT LIKE '_Module_%' ";
//if (!$obj->is_admin) {
//    $rq1 .= " AND hosts.host_id = centreon_acl.host_id ";
//}
//$rq1 .= " AND hosts.enabled = 1 ";
//$rq1 .= $obj->access->queryBuilder("AND", "centreon_acl.group_id", $obj->grouplistStr);
//$rq1 .= " GROUP BY state";

$hostCounter = $hst_up + $hst_down + $hst_unreachable + $hst_pending;
$host_stat = array(0 => $hst_up, 1 => $hst_down, 2 => $hst_unreachable, 3 => $hst_pending, 4=> 0);
//$DBRESULT = $obj->DBC->query($rq1);
//while ($data = $DBRESULT->fetchRow()) {
//    $host_stat[$data["state"]] = $data["count(DISTINCT name)"];
//    $hostCounter += $host_stat[$data["state"]];
//}
//$DBRESULT->free();

/* *********************************************
 * Get Service stats
 */
//$query_svc_status = "SELECT " .
//    "SUM(CASE WHEN s.state = 0 THEN 1 ELSE 0 END) AS OK_TOTAL, " .
//    "SUM(CASE WHEN s.state = 1 THEN 1 ELSE 0 END) AS WARNING_TOTAL, " .
//    "SUM(CASE WHEN s.state = 1 AND (s.acknowledged = '1' OR s.scheduled_downtime_depth = '1') " .
//    "    THEN 1 ELSE 0 END) AS WARNING_ACK_DT, " .
//    "SUM(CASE WHEN s.state = 2 THEN 1 ELSE 0 END) AS CRITICAL_TOTAL, " .
//    "SUM(CASE WHEN s.state = 2 AND (s.acknowledged = '1' OR s.scheduled_downtime_depth = '1') " .
//    "    THEN 1 ELSE 0 END) AS CRITICAL_ACK_DT, " .
//    "SUM(CASE WHEN s.state = 3 THEN 1 ELSE 0 END) AS UNKNOWN_TOTAL, " .
//    "SUM(CASE WHEN s.state = 3 AND (s.acknowledged = '1' OR s.scheduled_downtime_depth = '1') " .
//    "    THEN 1 ELSE 0 END) AS UNKNOWN_ACK_DT, " .
//    "SUM(CASE WHEN s.state = 4 THEN 1 ELSE 0 END) AS PENDING_TOTAL " .
//    "FROM hosts h, services s, instances i " .
//    "WHERE i.deleted = 0 " .
//    "AND h.enabled = 1 " .
//    "AND s.enabled = 1 " .
//    "AND i.instance_id = h.instance_id " .
//    "AND h.host_id = s.host_id " .
//    "AND (h.name NOT LIKE '_Module_%' OR h.name LIKE '_Module_Meta%') ";
//if (!$obj->is_admin) {
//    $query_svc_status .=  "AND EXISTS (" .
//        "SELECT service_id " .
//        "FROM centreon_acl " .
//        "WHERE centreon_acl.host_id = h.host_id " .
//        "AND centreon_acl.service_id = s.service_id " .
//        "AND centreon_acl.group_id IN (" . $obj->grouplistStr . ")" .
//        ") ";
//}
//$DBRESULT = $obj->DBC->query($query_svc_status);
//$svc_stat = array_map("myDecode", $DBRESULT->fetchRow());
//$DBRESULT->free();

$svc_ok = $redis->sCard('states:0');
$svc_warning = $redis->sCard('states:1');
$svc_critical = $redis->sCard('states:2');
$svc_unknown = $redis->sCard('states:3');
$svc_pending = $redis->sCard('states:4');

$tmp = $redis->rawCommand('TABULAR.GET', 'states:1',
        0, 0, 'FILTER', 2, 'acknowledged', 'EQUAL', '0', 'scheduled_downtime_depth', 'EQUAL', '0');
$svc_warn_ack_dt = $svc_warning - $tmp[0];

$tmp = $redis->rawCommand('TABULAR.GET', 'states:2',
        0, 0, 'FILTER', 2, 'acknowledged', 'EQUAL', '0', 'scheduled_downtime_depth', 'EQUAL', '0');
$svc_crit_ack_dt = $svc_critical - $tmp[0];

$tmp = $redis->rawCommand('TABULAR.GET', 'states:3',
        0, 0, 'FILTER', 2, 'acknowledged', 'EQUAL', '0', 'scheduled_downtime_depth', 'EQUAL', '0');
$svc_unkn_ack_dt = $svc_unknown - $tmp[0];

$tmp = $redis->rawCommand('TABULAR.GET', 'states:4',
        0, 0, 'FILTER', 2, 'acknowledged', 'EQUAL', '0', 'scheduled_downtime_depth', 'EQUAL', '0');
$svc_pend_ack_dt = $svc_pending - $tmp[0];

$svc_stat = array(
        'OK_TOTAL' => $svc_ok,
        'WARNING_TOTAL' => $svc_warning,
        'WARNING_ACK_DT' => $svc_warn_ack_dt,
        'CRITICAL_TOTAL' => $svc_critical,
        'CRITICAL_ACK_DT' => $svc_crit_ack_dt,
        'UNKNOWN_TOTAL' => $svc_unknown,
        'UNKNOWN_ACK_DT' => $svc_unkn_ack_dt,
        'PENDING_TOTAL' => $svc_pending,
        'PENDING_ACK_DT' => $svc_pend_ack_dt);

$serviceCounter = $svc_stat["OK_TOTAL"] + $svc_stat["WARNING_TOTAL"]
    + $svc_stat["CRITICAL_TOTAL"] + $svc_stat["UNKNOWN_TOTAL"]
    + $svc_stat["PENDING_TOTAL"];

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
$obj->XML->writeElement("o", ($svc_stat["OK_TOTAL"] ? $svc_stat["OK_TOTAL"] : "0"));
$obj->XML->writeElement("w", ($svc_stat["WARNING_TOTAL"] ? $svc_stat["WARNING_TOTAL"] : "0"));
$obj->XML->writeElement("wU", ($svc_stat["WARNING_TOTAL"] - $svc_stat["WARNING_ACK_DT"]));
$obj->XML->writeElement("c", ($svc_stat["CRITICAL_TOTAL"] ? $svc_stat["CRITICAL_TOTAL"] : "0"));
$obj->XML->writeElement("cU", ($svc_stat["CRITICAL_TOTAL"] - $svc_stat["CRITICAL_ACK_DT"]));
$obj->XML->writeElement("un1", ($svc_stat["UNKNOWN_TOTAL"] ? $svc_stat["UNKNOWN_TOTAL"] : "0"));
$obj->XML->writeElement("un1U", ($svc_stat["UNKNOWN_TOTAL"] - $svc_stat["UNKNOWN_ACK_DT"]));
$obj->XML->writeElement("p1", ($svc_stat["PENDING_TOTAL"] ? $svc_stat["PENDING_TOTAL"] : "0"));
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
