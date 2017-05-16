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

/*
 * XML tag
 */
stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml") ?
    header("Content-type: application/xhtml+xml") : header("Content-type: text/xml");
header('Content-Disposition: attachment; filename="eventLogs-' . time() . '.xml"');

/** ****************************
 * Include configurations files
 */
include_once "../../../../config/centreon.config.php";

/*
 * Require Classes
 */
require_once _CENTREON_PATH_ . "www/class/centreonDB.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonSession.class.php";
require_once _CENTREON_PATH_ . "www/class/centreon.class.php";

/**
 * Connect to DB
 */
$pearDB = new CentreonDB();
$pearDBO = new CentreonDB("centstorage");

/* Check Session */
CentreonSession::start();
if (!CentreonSession::checkSession(session_id(), $pearDB)) {
    print "Bad Session";
    exit();
}

$centreon = $_SESSION["centreon"];

/**
 * Language informations init
 */
$locale = $centreon->user->get_lang();
putenv("LANG=$locale");
setlocale(LC_ALL, $locale);
bindtextdomain("messages", _CENTREON_PATH_ . "/www/locale/");
bind_textdomain_codeset("messages", "UTF-8");
textdomain("messages");


define("STATUS_OK", 0);
define("STATUS_WARNING", 1);
define("STATUS_CRITICAL", 2);
define("STATUS_UNKNOWN", 3);
define("STATUS_PENDING", 4);
define("STATUS_UP", 0);
define("STATUS_DOWN", 1);
define("STATUS_UNREACHABLE", 2);
define("TYPE_SOFT", 0);
define("TYPE_HARD", 1);

/**
 * Include Access Class
 */
include_once _CENTREON_PATH_ . "www/class/centreonACL.class.php";
include_once _CENTREON_PATH_ . "www/class/centreonXML.class.php";
include_once _CENTREON_PATH_ . "www/class/centreonGMT.class.php";
include_once _CENTREON_PATH_ . "www/include/common/common-Func.php";

/**
 * Get input vars
 */
$inputArguments = array(
    'lang' => FILTER_SANITIZE_STRING,
    'id' => FILTER_SANITIZE_STRING,
    'num' => FILTER_SANITIZE_STRING,
    'limit' => FILTER_SANITIZE_STRING,
    'StartDate' => FILTER_SANITIZE_STRING,
    'EndDate' => FILTER_SANITIZE_STRING,
    'StartTime' => FILTER_SANITIZE_STRING,
    'EndTime' => FILTER_SANITIZE_STRING,
    'period' => FILTER_SANITIZE_STRING,
    'engine' => FILTER_SANITIZE_STRING,
    'up' => FILTER_SANITIZE_STRING,
    'down' => FILTER_SANITIZE_STRING,
    'unreachable' => FILTER_SANITIZE_STRING,
    'ok' => FILTER_SANITIZE_STRING,
    'warning' => FILTER_SANITIZE_STRING,
    'critical' => FILTER_SANITIZE_STRING,
    'unknown' => FILTER_SANITIZE_STRING,
    'notification' => FILTER_SANITIZE_STRING,
    'alert' => FILTER_SANITIZE_STRING,
    'oh' => FILTER_SANITIZE_STRING,
    'error' => FILTER_SANITIZE_STRING,
    'output' => FILTER_SANITIZE_STRING,
    'search_H' => FILTER_SANITIZE_STRING,
    'search_S' => FILTER_SANITIZE_STRING,
    'search_host' => FILTER_SANITIZE_STRING,
    'search_service' => FILTER_SANITIZE_STRING,
    'export' => FILTER_SANITIZE_STRING,
);
$inputGet = filter_input_array(
    INPUT_GET,
    $inputArguments
);
$inputPost = filter_input_array(
    INPUT_POST,
    $inputArguments
);

$inputs = array();
foreach ($inputArguments as $argumentName => $argumentValue) {
    if (!is_null($inputGet[$argumentName])) {
        $inputs[$argumentName] = $inputGet[$argumentName];
    } else {
        $inputs[$argumentName] = $inputPost[$argumentName];
    }
}

/*
 * Start XML document root
 */
$buffer = new CentreonXML();
$buffer->startElement("root");

/*
 * Security check
 */
(isset($inputs["lang"])) ?
    $lang_ = htmlentities($inputs["lang"], ENT_QUOTES, "UTF-8") : $lang_ = "-1";
(isset($inputs["id"])) ?
    $openid = htmlentities($inputs["id"], ENT_QUOTES, "UTF-8") : $openid = "-1";
$sid = session_id();
(isset($sid)) ? $sid = $sid : $sid = "-1";

/*
 * Init GMT class
 */
$centreonGMT = new CentreonGMT($pearDB);
$centreonGMT->getMyGMTFromSession($sid, $pearDB);

/*
 * Check Session
 */
$contact_id = check_session($sid, $pearDB);

$is_admin = isUserAdmin($sid);
if (isset($sid) && $sid) {
    $access = new CentreonAcl($contact_id, $is_admin);
    $lca = array("LcaHost" => $access->getHostsServices($pearDBO, 1), "LcaHostGroup" => $access->getHostGroups(), "LcaSG" => $access->getServiceGroups());
}

$num = isset($inputs["num"]) ? htmlentities($inputs["num"]) : "0";
$limit = isset($inputs["limit"]) ? htmlentities($inputs["limit"]) : "30";
$StartDate = isset($inputs["StartDate"]) ? htmlentities($inputs["StartDate"]) : "";
$EndDate = isset($inputs["EndDate"]) ? $EndDate = htmlentities($inputs["EndDate"]) : "";
$StartTime = isset($inputs["StartTime"]) ? $StartTime = htmlentities($inputs["StartTime"]) : "";
$EndTime = isset($inputs["EndTime"]) ? $EndTime = htmlentities($inputs["EndTime"]) : "";
$auto_period = isset($inputs["period"]) ? $auto_period = htmlentities($inputs["period"]) : "-1";
$engine = isset($inputs["engine"]) ? $engine = htmlentities($inputs["engine"]) : "false";
$up = isset($inputs["up"]) ? htmlentities($inputs["up"]) : "true";
$down = isset($inputs["down"]) ? htmlentities($inputs["down"]) : "true";
$unreachable = isset($inputs["unreachable"]) ? htmlentities($inputs["unreachable"]) : "true";
$ok = isset($inputs["ok"]) ? htmlentities($inputs["ok"]) : "true";
$warning = isset($inputs["warning"]) ? htmlentities($inputs["warning"]) : "true";
$critical = isset($inputs["critical"]) ? htmlentities($inputs["critical"]) : "true";
$unknown = isset($inputs["unknown"]) ? htmlentities($inputs["unknown"]) : "true";
$notification = isset($inputs["notification"]) ? htmlentities($inputs["notification"]) : "false";
$alert = isset($inputs["alert"]) ? htmlentities($inputs["alert"]) : "true";
$oh = isset($inputs["oh"]) ? htmlentities($inputs["oh"]) : "false";
$error = isset($inputs["error"]) ? htmlentities($inputs["error"]) : "false";

$output = isset($inputs["output"]) ? urldecode($inputs["output"]) : $output = "";
$search_H = isset($inputs["search_H"]) ? htmlentities($inputs["search_H"]) : "VIDE";
$search_S = isset($inputs["search_S"]) ? htmlentities($inputs["search_S"]) : "VIDE";
$search_host = isset($inputs["search_host"]) ? htmlentities($inputs["search_host"], ENT_QUOTES, "UTF-8") : "";
$search_service = isset($inputs["search_service"]) ? htmlentities($inputs["search_service"], ENT_QUOTES, "UTF-8") : "";
$export = isset($inputs["export"]) ? htmlentities($inputs["export"], ENT_QUOTES, "UTF-8") : 0;

$start = 0;
$end = 0;

if ($engine == "true") {
    $ok = "false";
    $up = "false";
    $unknown = "false";
    $unreachable = "false";
    $down = "false";
    $warning = "false";
    $critical = "false";
    $oh = "false";
    $notification = "false";
    $alert = "false";
}

if ($StartDate != "" && $StartTime == "") {
    $StartTime = "00:00";
}

if ($EndDate != "" && $EndTime == "") {
    $EndTime = "00:00";
}

if ($StartDate != "") {
    preg_match("/^([0-9]*)\/([0-9]*)\/([0-9]*)/", $StartDate, $matchesD);
    preg_match("/^([0-9]*):([0-9]*)/", $StartTime, $matchesT);
    $start = mktime($matchesT[1], $matchesT[2], "0", $matchesD[1], $matchesD[2], $matchesD[3], -1) ;
}
if ($EndDate !=  "") {
    preg_match("/^([0-9]*)\/([0-9]*)\/([0-9]*)/", $EndDate, $matchesD);
    preg_match("/^([0-9]*):([0-9]*)/", $EndTime, $matchesT);
    $end = mktime($matchesT[1], $matchesT[2], "0", $matchesD[1], $matchesD[2], $matchesD[3], -1) ;
}

$period = 86400;
if ($auto_period > 0) {
    $period = $auto_period;
    $start = time() - ($period);
    $end = time();
}

$general_opt = getStatusColor($pearDB);

$tab_color_service  = array(STATUS_OK => 'service_ok', STATUS_WARNING => 'service_warning', STATUS_CRITICAL => 'service_critical', STATUS_UNKNOWN => 'service_unknown', STATUS_PENDING => 'pending');
$tab_color_host         = array(STATUS_UP => 'host_up', STATUS_DOWN => 'host_down', STATUS_UNREACHABLE => 'host_unreachable');

$tab_type = array("1" => "HARD", "0" => "SOFT");
$tab_class = array("0" => "list_one", "1" => "list_two");
$tab_status_host = array("0" => "UP", "1" => "DOWN", "2" => "UNREACHABLE");
$tab_status_service = array("0" => "OK", "1" => "WARNING", "2" => "CRITICAL", "3" => "UNKNOWN");

/*
 * Create IP Cache
 */
if ($export) {
    $HostCache = array();
    $DBRESULT = $pearDB->query("SELECT host_name, host_address FROM host WHERE host_register = '1'");
    while ($h = $DBRESULT->fetchRow()) {
        $HostCache[$h["host_name"]] = $h["host_address"];
    }
    $DBRESULT->free();
}


$logs = array();

/*
 * Print infos..
 */
$buffer->startElement("infos");
$buffer->writeElement("sid", $sid);
$buffer->writeElement("opid", $openid);
$buffer->writeElement("start", $start);
$buffer->writeElement("end", $end);
$buffer->writeElement("notification", $notification);
$buffer->writeElement("alert", $alert);
$buffer->writeElement("error", $error);
$buffer->writeElement("up", $up);
$buffer->writeElement("down", $down);
$buffer->writeElement("unreachable", $unreachable);
$buffer->writeElement("ok", $ok);
$buffer->writeElement("warning", $warning);
$buffer->writeElement("critical", $critical);
$buffer->writeElement("unknown", $unknown);
$buffer->writeElement("oh", $oh);
$buffer->writeElement("search_H", $search_H);
$buffer->writeElement("search_S", $search_S);
$buffer->endElement();

$msg_type_set = array ();
if ($alert == 'true') {
    array_push($msg_type_set, "'0'");
}
if ($alert == 'true') {
    array_push($msg_type_set, "'1'");
}
if ($notification == 'true') {
    array_push($msg_type_set, "'2'");
}
if ($notification== 'true') {
    array_push($msg_type_set, "'3'");
}
if ($error == 'true') {
    array_push($msg_type_set, "'4'");
}

$msg_req = '';
$suffix_order = " ORDER BY ctime DESC ";

$host_msg_status_set = array();
if ($up == 'true') {
    array_push($host_msg_status_set, "'".STATUS_UP."'");
}
if ($down == 'true') {
    array_push($host_msg_status_set, "'".STATUS_DOWN."'");
}
if ($unreachable == 'true') {
    array_push($host_msg_status_set, "'".STATUS_UNREACHABLE."'");
}

$svc_msg_status_set = array();
if ($ok == 'true') {
    array_push($svc_msg_status_set, "'".STATUS_OK."'");
}
if ($warning == 'true') {
    array_push($svc_msg_status_set, "'".STATUS_WARNING."'");
}
if ($critical == 'true') {
    array_push($svc_msg_status_set, "'".STATUS_CRITICAL."'");
}
if ($unknown == 'true') {
    array_push($svc_msg_status_set, "'".STATUS_UNKNOWN."'");
}

$flag_begin = 0;

$whereOutput = "";
if (isset($output) && $output != "") {
    $whereOutput = " AND logs.output like '%".$pearDBO->escape($output)."%' ";
}

$innerJoinEngineLog = "";
if ($engine == "true" && isset($openid) && $openid != "") {
    $innerJoinEngineLog = " inner join instances i on i.name = logs.instance_name AND i.instance_id IN (" . $pearDBO->escape($openid) . ") ";
}

if ($notification == 'true') {
    if (count($host_msg_status_set)) {
        $msg_req .= "(";
        $flag_begin = 1;
        $msg_req .= " (`msg_type` = '3' ";
        $msg_req .= " AND `status` IN (" . implode(',', $host_msg_status_set)."))";
        $msg_req .= ") ";
    }
    if (count($svc_msg_status_set)) {
        if ($flag_begin == 0) {
            $msg_req .= "(";
        } else {
            $msg_req .= " OR ";
        }
        $msg_req .= " (`msg_type` = '2' ";
        $msg_req .= " AND `status` IN (" . implode(',', $svc_msg_status_set)."))";
        if ($flag_begin == 0) {
            $msg_req .= ") ";
        }
        $flag_begin = 1;
    }
}
if ($alert == 'true') {
    if (count($host_msg_status_set)) {
        if ($flag_begin) {
            $msg_req .= " OR ";
        }
        if ($oh == true) {
            $msg_req .= " ( ";
            $flag_oh = true;
        }
        $flag_begin = 1;
        $msg_req .= " ((`msg_type` IN ('1', '10', '11') ";
        $msg_req .= " AND `status` IN (" . implode(',', $host_msg_status_set).")) ";
        $msg_req .= ") ";
    }
    if (count($svc_msg_status_set)) {
        if ($flag_begin) {
            $msg_req .= " OR ";
        }
        if ($oh == true && !isset($flag_oh)) {
            $msg_req .= " ( ";
        }
        $flag_begin = 1;
        $msg_req .= " ((`msg_type` IN ('0', '10', '11') ";
        $msg_req .= " AND `status` IN (" . implode(',', $svc_msg_status_set).")) ";
        $msg_req .= ") ";
    }
    if ($flag_begin) {
        $msg_req .= ")";
    }
    if ((count($host_msg_status_set) || count($svc_msg_status_set)) && $oh == 'true') {
        $msg_req .= " AND ";
    }
    if ($oh == 'true') {
        $flag_begin = 1;
        $msg_req .= " `type` = '".TYPE_HARD."' ";
    }
}
// Error filter is only used in the engine log page.
if ($error == 'true') {
    if ($flag_begin == 0) {
        $msg_req .= "AND ";
    } else {
        $msg_req .= " OR ";
    }
    $msg_req .= " `msg_type` IN ('4','5') ";
}
if ($flag_begin) {
    $msg_req = " AND (".$msg_req.") ";
}

$tab_id = preg_split("/\,/", $openid);
$tab_host_ids = array();
$tab_svc = array();
$filters = false;
foreach ($tab_id as $openid) {
    $tab_tmp = preg_split("/\_/", $openid);
    $id = "";
    $hostId = "";

    if (isset($tab_tmp[2])) {
        $hostId = $tab_tmp[1];
        $id = $tab_tmp[2];
    } elseif (isset($tab_tmp[1])) {
        $id = $tab_tmp[1];
    }

    if ($id == "") {
        continue;
    }
    
    $type = $tab_tmp[0];
    
    
    if ($type == "HG" && (isset($lca["LcaHostGroup"][$id]) || $is_admin)) {
        $filters = true;
        // Get hosts from hostgroups
        $hosts = getMyHostGroupHosts($id);
        if (count($hosts) == 0) {
            $tab_host_ids[] = "-1";
        } else {
            foreach ($hosts as $h_id) {
                if (isset($lca["LcaHost"][$h_id])) {
                    $tab_host_ids[] = $h_id;
                    $tab_svc[$h_id] = $lca["LcaHost"][$h_id];
                }
            }
        }
    } elseif ($type == 'SG' && (isset($lca["LcaSG"][$id]) || $is_admin)) {
        $filters = true;
        $services = getMyServiceGroupServices($id);
        if (count($services) == 0) {
            $tab_svc[] = "-1";
        } else {
            foreach ($services as $svc_id => $svc_name) {
                $tab_tmp = preg_split("/\_/", $svc_id);
                $tmp_host_id = $tab_tmp[0];
                $tmp_service_id = $tab_tmp[1];
                $tab = preg_split("/\:/", $svc_name);
                $host_name = $tab[3];
                if (isset($lca["LcaHost"][$tmp_host_id][$tmp_service_id])) {
                    $tab_svc[$tmp_host_id][$tmp_service_id] = $lca["LcaHost"][$tmp_host_id][$tmp_service_id];
                }
            }
        }
    } elseif ($type == "HH" && isset($lca["LcaHost"][$id])) {
        $filters = true;
        $tab_host_ids[] = $id;
        $tab_svc[$id] = $lca["LcaHost"][$id];
    } elseif ($type == "HS" && isset($lca["LcaHost"][$hostId][$id])) {
        $filters = true;
        $tab_svc[$hostId][$id] = $lca["LcaHost"][$hostId][$id];
    } elseif ($type == "MS") {
        $filters = true;
        $tab_svc["_Module_Meta"][$id] = "meta_".$id;
    }
}

// Build final request
$req = "SELECT SQL_CALC_FOUND_ROWS ".(!$is_admin ? "DISTINCT" : "")." 
        logs.ctime, 
        logs.host_id, 
        logs.host_name, 
        logs.service_id, 
        logs.service_description, 
        logs.msg_type, 
        logs.notification_cmd, 
        logs.notification_contact, 
        logs.output, 
        logs.retry, 
        logs.status, 
        logs.type, 
        logs.instance_name
        FROM logs ".$innerJoinEngineLog.
    ((!$is_admin) ?
    " inner join centreon_acl acl on ((logs.host_id = acl.host_id AND logs.service_id IS NULL) OR "
    . " (logs.host_id = acl.host_id AND acl.service_id = logs.service_id)) "
    . " WHERE acl.group_id IN (".$access->getAccessGroupsString().") AND " : "WHERE ")
    . " logs.ctime > '$start' AND logs.ctime <= '$end' $whereOutput $msg_req";

/*
 * Add Host
 */
$str_unitH = "";
$str_unitH_append = "";
$host_search_sql = "";
if (count($tab_host_ids) == 0 && count($tab_svc) == 0) {
    if ($engine == "false") {
        $req .= " AND `msg_type` NOT IN ('4','5') ";
        $req .= " AND logs.host_name NOT LIKE '_Module_BAM%' ";
    }
} else {
    foreach ($tab_host_ids as $host_id) {
        if ($host_id != "") {
            $str_unitH .= $str_unitH_append . "'$host_id'";
            $str_unitH_append = ", ";
        }
    }
    if ($str_unitH != "") {
        $str_unitH = "(logs.host_id IN ($str_unitH) AND logs.service_id IS NULL)";
        if (isset($search_host) && $search_host != "") {
            $host_search_sql = " AND logs.host_name LIKE '%".$pearDBO->escape($search_host)."%' ";
        }
    }
    
    /*
     * Add services
     */
    $flag = 0;
    $str_unitSVC = "";
    $service_search_sql = "";
    if ((count($tab_svc) || count($tab_host_ids)) && ($up == 'true' || $down == 'true' || $unreachable == 'true' || $ok == 'true' || $warning == 'true' || $critical == 'true' || $unknown == 'true')) {
        $req_append = "";
        foreach ($tab_svc as $host_id => $services) {
            $str = "";
            $str_append = "";
            foreach ($services as $svc_id => $svc_name) {
                if ($svc_id != "") {
                    $str .= $str_append . $svc_id;
                    $str_append = ", ";
                }
            }
            if ($str != "") {
                $str_unitSVC .= $req_append . " (logs.host_id = '".$host_id."' AND logs.service_id IN ($str)) ";
                $req_append = " OR";
            }
        }
        if (isset($search_service) && $search_service != "") {
            $service_search_sql = " AND logs.service_description LIKE '%".$pearDBO->escape($search_service)."%' ";
        }
        if ($str_unitH != "" && $str_unitSVC != "") {
            $str_unitSVC = " OR " . $str_unitSVC;
        }
        if ($str_unitH != "" || $str_unitSVC != "") {
            $req .= " AND (".$str_unitH.$str_unitSVC.")";
        }
    } else {
        $req .= "AND 0 ";
    }
    $req .= " AND logs.host_name NOT LIKE '_Module_BAM%' ";
    $req .= $host_search_sql . $service_search_sql;
}

/*
 * calculate size before limit for pagination
 */
if (isset($req) && $req) {
    /*
     * Add Suffix for order
     */
    $req .= $suffix_order;
    if ($num < 0) {
        $num = 0;
    }

    $limitReq = "";
    $limitReq2 = "";
    if ($export !== "1") {
        $limitReq = " LIMIT " . $num * $limit . ", " . $limit;
    }
    $DBRESULT = $pearDBO->query($req .$limitReq);
    $rows = $pearDBO->numberRows();
    
    if (!($DBRESULT->numRows()) && ($num != 0)) {
        if ($export !== "1") {
            $limitReq2 =" LIMIT " . (floor($rows / $limit) * $limit) . ", " . $limit;
        }
        $DBRESULT = $pearDBO->query($req . $limitReq2);
    }

    $buffer->startElement("selectLimit");
    for ($i = 10; $i <= 100; $i = $i +10) {
        $buffer->writeElement("limitValue", $i);
    }
    $buffer->writeElement("limit", $limit);
    $buffer->endElement();
    
    require_once _CENTREON_PATH_ . "www/include/common/checkPagination.php";
    /*
     * pagination
     */
    
    $pageArr = array();
    $istart = 0;

    for ($i = 5, $istart = $num; $istart > 0 && $i > 0; $i--) {
        $istart--;
    }
    
    for ($i2 = 0, $iend = $num; ( $iend <  ($rows / $limit -1)) && ( $i2 < (5 + $i)); $i2++) {
        $iend++;
    }
    
    for ($i = $istart; $i <= $iend; $i++) {
        $pageArr[$i] = array("url_page"=>"&num=$i&limit=".$limit, "label_page"=>($i +1),"num"=> $i);
    }
    
    if ($i > 1) {
        foreach ($pageArr as $key => $tab) {
            $buffer->startElement("page");
            if ($tab["num"] == $num) {
                $buffer->writeElement("selected", "1");
            } else {
                $buffer->writeElement("selected", "0");
            }
            $buffer->writeElement("num", $tab["num"]);

            $buffer->writeElement("url_page", $tab["url_page"]);
            $buffer->writeElement("label_page", $tab["label_page"]);
            $buffer->endElement();
        }
    }
    
    $prev = $num - 1;
    $next = $num + 1;
    
    if ($num > 0) {
        $buffer->startElement("first");
        $buffer->writeAttribute("show", "true");
        $buffer->text("0");
        $buffer->endElement();
    } else {
        $buffer->startElement("first");
        $buffer->writeAttribute("show", "false");
        $buffer->text("none");
        $buffer->endElement();
    }
    
    if ($num > 1) {
        $buffer->startElement("prev");
        $buffer->writeAttribute("show", "true");
        $buffer->text($prev);
        $buffer->endElement();
    } else {
        $buffer->startElement("prev");
        $buffer->writeAttribute("show", "false");
        $buffer->text("none");
        $buffer->endElement();
    }
    
    if ($num < $page_max - 1) {
        $buffer->startElement("next");
        $buffer->writeAttribute("show", "true");
        $buffer->text($next);
        $buffer->endElement();
    } else {
        $buffer->startElement("next");
        $buffer->writeAttribute("show", "false");
        $buffer->text("none");
        $buffer->endElement();
    }
    
    $last = $page_max - 1;
    
    if ($num < $page_max-1) {
        $buffer->startElement("last");
        $buffer->writeAttribute("show", "true");
        $buffer->text($last);
        $buffer->endElement();
    } else {
        $buffer->startElement("last");
        $buffer->writeAttribute("show", "false");
        $buffer->text("none");
        $buffer->endElement();
    }
    
    /*
     * Full Request
     */
    $cpts = 0;
    while ($log = $DBRESULT->fetchRow()) {
        $buffer->startElement("line");
        $buffer->writeElement("msg_type", $log["msg_type"]);
        $displayType = $log['type'];
        if (isset($tab_type[$log['type']])) {
            $displayType = $tab_type[$log['type']];
        }
        $log["msg_type"] > 1 ? $buffer->writeElement("retry", "") : $buffer->writeElement("retry", $log["retry"]);
        $log["msg_type"] == 2 || $log["msg_type"] == 3 ? $buffer->writeElement("type", "NOTIF") : $buffer->writeElement("type", $displayType);
        
        /*
         * Color initialisation for services and hosts status
         */
        $color = '';
        if (isset($log["status"])) {
            if (isset($tab_color_service[$log["status"]]) && isset($log["service_description"]) && $log["service_description"] != "") {
                $color = $tab_color_service[$log["status"]];
            } elseif (isset($tab_color_host[$log["status"]])) {
                $color = $tab_color_host[$log["status"]];
            }
        }
        
        /*
         * Variable initialisation to color "INITIAL STATE" on envent logs
         */
        if ($log["output"] == "" && $log["status"] != "") {
            $log["output"] = "INITIAL STATE";
        }
        
        $buffer->startElement("status");
        $buffer->writeAttribute("color", $color);
        $displayStatus = $log["status"];
        if ($log['service_description'] && isset($tab_status_service[$log['status']])) {
            $displayStatus = $tab_status_service[$log['status']];
        } elseif (isset($tab_status_host[$log['status']])) {
            $displayStatus = $tab_status_host[$log['status']];
        }
        $buffer->text($displayStatus);
        $buffer->endElement();
        
        if (!strncmp($log["host_name"], "_Module_Meta", strlen("_Module_Meta"))) {
            preg_match('/meta_([0-9]*)/', $log["service_description"], $matches);
            $DBRESULT2 = $pearDB->query("SELECT meta_name FROM meta_service WHERE meta_id = '".$matches[1]."'");
            $meta = $DBRESULT2->fetchRow();
            $DBRESULT2->free();
            $buffer->writeElement("host_name", "Meta", false);
            $buffer->writeElement("real_service_name", $log["service_description"], false);
            $buffer->writeElement("service_description", $meta["meta_name"], false);
            unset($meta);
        } else {
            $buffer->writeElement("host_name", $log["host_name"], false);
            if ($export) {
                $buffer->writeElement("address", $HostCache[$log["host_name"]], false);
            }
            $buffer->writeElement("service_description", $log["service_description"], false);
            $buffer->writeElement("real_service_name", $log["service_description"], false);
        }
        $buffer->writeElement("real_name", $log["host_name"], false);
        $buffer->writeElement("class", $tab_class[$cpts % 2]);
        $buffer->writeElement("poller", $log["instance_name"]);
        $buffer->writeElement("date", $centreonGMT->getDate(_("Y/m/d"), $log["ctime"]));
        $buffer->writeElement("time", $centreonGMT->getDate(_("H:i:s"), $log["ctime"]));
        $buffer->writeElement("output", $log["output"]);
        $buffer->writeElement("contact", $log["notification_contact"], false);
        $buffer->writeElement("contact_cmd", $log["notification_cmd"], false);
        $buffer->endElement();
        $cpts++;
    }
} else {
    $buffer->startElement("page");
    $buffer->writeElement("limit", $limit);
    $buffer->writeElement("selected", "1");
    $buffer->writeElement("num", 0);
    $buffer->writeElement("url_page", "");
    $buffer->writeElement("label_page", "");
    $buffer->endElement();
}

/*
 * Translation for tables.
 */
$buffer->startElement("lang");
$buffer->writeElement("d", _("Day"), 0);
$buffer->writeElement("t", _("Time"), 0);
$buffer->writeElement("O", _("Object name"), 0);
$buffer->writeElement("T", _("Type"), 0);
$buffer->writeElement("R", _("Retry"), 0);
$buffer->writeElement("o", _("Output"), 0);
$buffer->writeElement("c", _("Contact"), 0);
$buffer->writeElement("C", _("Command"), 0);
$buffer->writeElement("P", _("Poller"), 0);

$buffer->endElement();
$buffer->endElement();
$buffer->output();
