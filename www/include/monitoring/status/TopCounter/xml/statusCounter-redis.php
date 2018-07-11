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

if (!$obj->is_admin) {
    error_log("NOT ADMIN: $acl");
    $args = array_keys($obj->grouplist);
    sort($args);
    $acl = implode(':', $args);
    $svc_source = 'services:acl:' . $acl;
    if (!$redis->exists($svc_source)) {
        $args = array_map(function ($a) {
                    return 'services:acl:' . $a;
                }, $args);
        array_unshift($args, $svc_source);
        call_user_func_array(array($redis, 'sUnionStore'), $args);
    }
    $hst_source = 'hosts:acl:' . $acl;
    if (!$redis->exists($hst_source)) {
        $args = array_map($obj->grouplist, function ($a) {
                    return 'hosts:acl:' . $a;
                });
        array_unshift($args, $hst_source);
        call_user_func_array(array($redis, 'sUnionStore'), $args);
    }
}
else {
    error_log("ADMIN");
    $svc_source = 'services';
    $hst_source = 'hosts';
}

/* *********************************************
 * Get Host stats
 */

error_log("TABULAR.COUNT $hst_source FILTER 1 current_state MATCH [0-4]");
$tmp = $redis->rawCommand('TABULAR.COUNT', $hst_source,
        'FILTER', 1, 'current_state', 'MATCH', '[0-4]');

for ($i = 0; $i < count($tmp); $i += 6) {
    if ($tmp[$i + 1] == 0) {
        $hst_up = $tmp[$i + 3];
    }
    elseif ($tmp[$i + 1] == 2) {
        $hst_down = $tmp[$i + 3];
    }
    elseif ($tmp[$i + 1] == 3) {
        $hst_unreachable = $tmp[$i + 3];
    }
    elseif ($tmp[$i + 1] == 4) {
        $hst_pending = $tmp[$i + 3];
    }
}

if (!isset($hst_up)) {
    $hst_up = 0;
}

if (!isset($hst_down)) {
    $hst_down = 0;
}

if (!isset($hst_unreachable)) {
    $hst_unreachable = 0;
}

if (!isset($hst_pending)) {
    $hst_pending = 0;
}

$hostCounter = $hst_up + $hst_down + $hst_unreachable + $hst_pending;
$host_stat = array(0 => $hst_up, 1 => $hst_down, 2 => $hst_unreachable, 3 => $hst_pending, 4=> 0);

/* *********************************************
 * Get Service stats
 */

error_log("TABULAR.COUNT $svc_source FILTER 3 current_state MATCH [0-4] acknowledged EQUAL 0 scheduled_downtime_depth EQUAL 0");
$tmp = $redis->rawCommand('TABULAR.COUNT', $svc_source,
        'FILTER', 3, 'current_state', 'MATCH', '[0-4]',
        'acknowledged', 'EQUAL', '0',
        'scheduled_downtime_depth', 'EQUAL', '0');

$svc_ok = 0;
$svc_warning = 0;
$svc_warn_ack_dt = 0;
$svc_critical = 0;
$svc_crit_ack_dt = 0;
$svc_unknown = 0;
$svc_unkn_ack_dt = 0;
$svc_pending = 0;
$svc_pend_ack_dt = 0;

for ($i = 0; $i < count($tmp); $i += 6) {
    if ($tmp[$i + 1] == 0) {
        $svc_ok = $tmp[$i + 3];
    }
    elseif ($tmp[$i + 1] == 1) {
        $svc_warning = $tmp[$i + 3];
        $svc_warn_ack_dt = $tmp[$i + 5]->$tmp[5]->$tmp[1];
    }
    elseif ($tmp[$i + 1] == 2) {
        $svc_critical = $tmp[$i + 3];
        if (isset($tmp[$i + 5]) && isset($tmp[$i + 5]->$tmp[5])) {
            $svc_crit_ack_dt = $tmp[$i + 5]->$tmp[5]->$tmp[1];
        }
    }
    elseif ($tmp[$i + 1] == 3) {
        $svc_unknown = $tmp[$i + 3];
        if (isset($tmp[$i + 5]) && isset($tmp[$i + 5]->$tmp[5])) {
            $svc_unkn_ack_dt = $tmp[$i + 5]->$tmp[5]->$tmp[1];
        }
    }
    elseif ($tmp[$i + 1] == 4) {
        $svc_pending = $tmp[$i + 3];
        if (isset($tmp[$i + 5]) && isset($tmp[$i + 5]->$tmp[5])) {
            $svc_pend_ack_dt = $tmp[$i + 5]->$tmp[5]->$tmp[1];
        }
    }
}

$svc_stat = array(
        'OK_TOTAL' => isset($svc_ok) ? $svc_ok : 0,
        'WARNING_TOTAL' => isset($svc_warning) ? $svc_warning : 0,
        'WARNING_ACK_DT' => isset($svc_warn_ack_dt) ? $svc_warn_ack_dt : 0,
        'CRITICAL_TOTAL' => isset($svc_critical) ? $svc_critical : 0,
        'CRITICAL_ACK_DT' => $svc_crit_ack_dt,
        'UNKNOWN_TOTAL' => isset($svc_unknown) ? $svc_unknown : 0,
        'UNKNOWN_ACK_DT' => isset($svc_unkn_ack_dt) ? $svc_unkn_ack_dt : 0,
        'PENDING_TOTAL' => isset($svc_pending) ? $svc_pending : 0,
        'PENDING_ACK_DT' => isset($svc_pend_ack_dt) ? $svc_pend_ack_dt : 0);

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
