<?php
/*
 * Copyright 2018 Centreon
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

/**
 * Require configuration.
 */
require_once realpath(dirname(__FILE__) . '/../../../../../../config/centreon.config.php');

include_once _CENTREON_PATH_ . 'www/class/centreonUtils.class.php';

/**
 * Require Specific XML / Ajax Class
 */
include_once _CENTREON_PATH_ . 'www/class/centreonXMLBGRequest.class.php';
include_once _CENTREON_PATH_ . 'www/class/centreonInstance.class.php';
include_once _CENTREON_PATH_ . 'www/class/centreonCriticality.class.php';
include_once _CENTREON_PATH_ . 'www/class/centreonMedia.class.php';

/**
 * Require common Files.
 */
include_once _CENTREON_PATH_ . 'www/include/monitoring/status/Common/common-Func.php';
include_once _CENTREON_PATH_ . 'www/include/common/common-Func.php';

/**
 * Create XML Request Objects
 */
CentreonSession::start();
$obj = new CentreonXMLBGRequest(session_id(), 1, 1, 0, 1);

/*
 * Get session
 */
if (isset($_SESSION['centreon'])) {
    $centreon = $_SESSION['centreon'];
} else {
    exit;
}

/*
 * Get language
 */
$locale = $centreon->user->get_lang();
putenv("LANG=$locale");
setlocale(LC_ALL, $locale);
bindtextdomain('messages', _CENTREON_PATH_ . 'www/locale/');
bind_textdomain_codeset('messages', 'UTF-8');
textdomain('messages');

$criticality = new CentreonCriticality($obj->DB);
$instanceObj = new CentreonInstance($obj->DB);
$media = new CentreonMedia($obj->DB);

if (isset($obj->session_id) && CentreonSession::checkSession($obj->session_id, $obj->DB)) {
    ;
} else {
    print 'Bad Session ID';
    exit();
}

/**
 * Set Default Poller
 */
$obj->getDefaultFilters();

/** * *************************************************
 * Check Arguments From GET tab
 */
$o = $obj->checkArgument('o', $_GET, 'h');
$p = $obj->checkArgument('p', $_GET, '2');
$nc = $obj->checkArgument('nc', $_GET, '0');
$num = $obj->checkArgument('num', $_GET, 0);
$limit = $obj->checkArgument('limit', $_GET, 20);
$instance = $obj->checkArgument('instance', $_GET, $obj->defaultPoller);
$hostgroups = $obj->checkArgument('hostgroups', $_GET, $obj->defaultHostgroups);
$servicegroups = $obj->checkArgument('servicegroups', $_GET, $obj->defaultServicegroups);
$search = $obj->checkArgument('search', $_GET, '');
$search_host = $obj->checkArgument('search_host', $_GET, '');
$search_output = $obj->checkArgument('search_output', $_GET, '');
$sort_type = $obj->checkArgument('sort_type', $_GET, 'host_name');
$order = $obj->checkArgument('order', $_GET, 'ASC');
$dateFormat = $obj->checkArgument('date_time_format_status', $_GET, 'Y/m/d H:i:s');
$search_type_host = $obj->checkArgument('search_type_host', $_GET, 1);
$search_type_service = $obj->checkArgument('search_type_service', $_GET, 1);
$criticality_id = $obj->checkArgument('criticality', $_GET, $obj->defaultCriticality);

$statusService = $obj->checkArgument('statusService', $_GET, '');
$statusFilter = $obj->checkArgument('statusFilter', $_GET, '');

CentreonDb::checkInjection($o);
CentreonDb::checkInjection($p);
CentreonDb::checkInjection($nc);
CentreonDb::checkInjection($num);
CentreonDb::checkInjection($limit);
CentreonDb::checkInjection($instance);
CentreonDb::checkInjection($hostgroups);
CentreonDb::checkInjection($servicegroups);
CentreonDb::checkInjection($search);
CentreonDb::checkInjection($search_host);
CentreonDb::checkInjection($search_output);
CentreonDb::checkInjection($sort_type);
CentreonDb::checkInjection($order);
CentreonDb::checkInjection($dateFormat);
CentreonDb::checkInjection($search_type_host);
CentreonDb::checkInjection($search_type_service);
CentreonDb::checkInjection($criticality_id);

/* Connection to Redis
 */
$redis = new Redis();
$redis->connect($conf_centreon['redisServer'], $conf_centreon['redisPort']);
$redis->auth($conf_centreon['redisPassword']);

/* Store in session the last type of call */
$_SESSION['monitoring_service_status'] = $statusService;
$_SESSION['monitoring_service_status_filter'] = $statusFilter;

/** * *************************************************
 * Backup poller selection
 */
$obj->setInstanceHistory($instance);

/** * *************************************************
 * Backup criticality id
 */
$obj->setCriticality($criticality_id);

/**
 * Graphs Tables
 */
//$graphs = array();

/** * *************************************************
 * Get Service status
 */

//$tabOrder = array();
//  SORTABLE $tabOrder["criticality_id"] = " ORDER BY isnull $order, criticality $order, h.name, s.description ";
//  SORTABLE $tabOrder["host_name"] = " ORDER BY h.name " . $order . ", s.description ";
//  SORTABLE $tabOrder["service_description"] = " ORDER BY s.description " . $order . ", h.name";
//  SORTABLE $tabOrder["current_state"] = " ORDER BY s.state " . $order . ", h.name, s.description";
//  SORTABLE $tabOrder["last_state_change"] = " ORDER BY s.last_state_change " . $order . ", h.name, s.description";
//  SORTABLE $tabOrder["last_hard_state_change"] = " ORDER by s.last_hard_state_change " . $order . ", h.name, s.description";
//  SORTABLE $tabOrder["last_check"] = " ORDER BY s.last_check " . $order . ", h.name, s.description";
//  SORTABLE (current_check_attempt) $tabOrder["current_attempt"] = " ORDER BY s.check_attempt " . $order . ", h.name, s.description";
//  SORTABLE (plugin_output) $tabOrder['output'] = ' ORDER BY s.output ' . $order . ', h.name, s.description';
//$tabOrder["default"] = $tabOrder['criticality_id'];

//$request = "SELECT SQL_CALC_FOUND_ROWS DISTINCT h.name, h.alias, h.address, h.host_id, s.description, "
//    . "s.service_id, s.notes, s.notes_url, s.action_url, s.max_check_attempts, "
//    . "s.icon_image, s.display_name, s.state, s.output as plugin_output, "
//    . "s.state_type, s.check_attempt as current_attempt, s.last_update as status_update_time, s.last_state_change, "
//    . "s.last_hard_state_change, s.last_check, s.next_check, "
//    . "s.notify, s.acknowledged, s.passive_checks, s.active_checks, s.event_handler_enabled, s.flapping, "
//    . "s.scheduled_downtime_depth, s.flap_detection, h.state as host_state, h.acknowledged AS h_acknowledged, "
//    . "h.scheduled_downtime_depth AS h_scheduled_downtime_depth, "
//    . "h.icon_image AS h_icon_images, h.display_name AS h_display_name, h.action_url AS h_action_url, "
//    . "h.notes_url AS h_notes_url, h.notes AS h_notes, h.address, "
//    . "h.passive_checks AS h_passive_checks, h.active_checks AS h_active_checks, "
//    . "i.name as instance_name, cv.value as criticality, cv.value IS NULL as isnull ";
//$request .= " FROM hosts h, instances i ";
//if ($criticality_id) {
//    $request .= ", customvariables cvs ";
//}
//if (!$obj->is_admin) {
//    $request .= ", centreon_acl ";
//}
//$request .= ", services s LEFT JOIN customvariables cv ON (s.service_id = cv.service_id "
//    . "AND cv.host_id = s.host_id AND cv.name = 'CRITICALITY_LEVEL') ";
//$request .= " WHERE h.host_id = s.host_id
//                AND s.enabled = 1
//                AND h.enabled = 1
//                AND h.instance_id = i.instance_id ";
//if ($criticality_id) {
//    $request .= " AND s.service_id = cvs. service_id
//                  AND cvs.host_id = h.host_id
//                  AND cvs.name = 'CRITICALITY_ID'
//                  AND cvs.value = '" . $obj->DBC->escape($criticality_id) . "' ";
//}
//$request .= " AND h.name NOT LIKE '_Module_BAM%' ";
//
///**
// * ACL activation
// */
//if (!$obj->is_admin) {
//    $request .= " AND h.host_id = centreon_acl.host_id "
//        . "AND s.service_id = centreon_acl.service_id AND group_id IN (" . $obj->grouplistStr . ") ";
//}
//
//(isset($tabOrder[$sort_type])) ? $request .= $tabOrder[$sort_type] : $request .= $tabOrder["default"];
//$request .= " LIMIT " . ($num * $limit) . "," . $limit;

function build_search_rows(&$result, $block) {
    $retval = array();
    $i = 0;
    $size = count($block);

    $tmp = array();
    foreach ($result as $d) {
        $tmp[$block[$i]] = $d;
        $i++;
        if ($i >= $size) {
            $retval[] = $tmp;
            $i = 0;
            $tmp = array();
        }
    }
    if (!empty($tmp))
        $retval[] = $tmp;
    return $retval;
}

$filter = array();
$src_array = array();

if ($statusService == 'svcpb' || $statusService == 'svc_unhandled') {
    if ($statusService == 'svcpb') {
        $filter[] = 'current_state';
        if (!empty($statusFilter)) {
            $s = array(
                    'ok' => 5,  // Do not show ok
                    'warning' => 1,
                    'critical' => 2,
                    'unknown' => 3,
                    'pending' => 5  // Do not show pending
                    );
            $filter[] = 'EQUAL';
            $filter[] = $s[$statusFilter];
        }
        else {
            $filter[] = 'MATCH';
            $filter[] = '[1-3]';
        }
    }
    else {
        $filter[] = 'current_state';
        if (!empty($statusFilter)) {
            $s = array(
                    'ok' => 5,  // Do not show ok
                    'warning' => 1,
                    'critical' => 2,
                    'unknown' => 3,
                    'pending' => 5  // Do not show pending
                    );
            $filter[] = 'EQUAL';
            $filter[] = $s[$statusFilter];
        }
        else {
            $filter[] = 'MATCH';
            $filter[] = '[1-3]';
        }
        $filter[] = 'state_type';
        $filter[] = 'EQUAL';
        $filter[] = '1';
        $filter[] = 'acknowledged';
        $filter[] = 'EQUAL';
        $filter[] = '0';
        $filter[] = 'scheduled_downtime_depth';
        $filter[] = 'EQUAL';
        $filter[] = '0';
    }
}
elseif (!empty($statusFilter)) {
    $s = array(
            'ok' => 0,
            'warning' => 1,
            'critical' => 2,
            'unknown' => 3,
            'pending' => 4
            );
    $filter[] = 'current_state';
    $filter[] = 'EQUAL';
    $filter[] = $s[$statusFilter];
}

if (isset($instance) && $instance > 0) {
    $filter[] = 'poller_id';
    $filter[] = 'EQUAL';
    $filter[] = $instance;
}

if (isset($hostgroups) && $hostgroups > 0) {
    $src_array[] = 'hgs:' . $hostgroups;
}

if (isset($servicegroups) && $servicegroups > 0) {
    $src_array[] = 'sg:' . $servicegroups;
}

if (!empty($search)) {
    $filter[] = 'display_name';
    $filter[] = 'MATCH';
    $filter[] = '*' . $search . '*';
}

if (!empty($search_host)) {
    $filter[] = 'host_name';
    $filter[] = 'MATCH';
    $filter[] = '*' . $search_host . '*';
}

if (!empty($search_output)) {
    $filter[] = 'plugin_output';
    $filter[] = 'MATCH';
    $filter[] = '*' . $search_output . '*';
}

//if (!$obj->is_admin) {
//    $filter .= ' @acl_groups:{' . implode('|', array_keys($obj->grouplist)) . '}';
//}

if (empty($src_array)) {
    $src = 'services';
}
else {
    $src = uniqid('services_src_');
    array_unshift($src_array, $src);
    call_user_func_array(array($redis, 'sUnionStore'), $src_array);
    $redis->setTimeout($src, 60);
}

$store = uniqid('services_');

$get_params = array(
        'TABULAR.GET',
        $src,
        $num * $limit,
        $num * $limit + $limit - 1,
        'STORE', $store,
);

if ($sort_type == 'host_name') {
    $t = ($order == 'ASC') ? 'ALPHA' : 'REVALPHA';
    $get_params = array_merge($get_params,
        array('SORT', 2, 'host_name', $t, 'service_description', 'ALPHA'));
}
else if ($sort_type == 'service_description') {
    $t = ($order == 'ASC') ? 'ALPHA' : 'REVALPHA';
    $get_params = array_merge($get_params,
        array('SORT', 2, 'service_description', $t, 'host_name', 'ALPHA'));
}
else {
    $ty = array(
        'current_state' => array(
            'current_state',
            'NUM',
            ),
        'last_state_change' => array(
            'last_state_change',
            'NUM',
            ),
        'last_check' => array(
            'last_check',
            'NUM',
            ),
        'current_attempt' => array(
            'current_check_attempt',
            'NUM',
            ),
        'plugin_output' => array(
            'plugin_output',
            'ALPHA',
            ),
    );
    $t = ($order == 'ASC') ? $ty[$sort_type][1] : 'REV' . $ty[$sort_type][1];
    $get_params = array_merge($get_params,
        array('SORT', 3, $ty[$sort_type][0], $t, 'host_name', 'ALPHA', 'service_description', 'ALPHA'));
}
if (!empty($filter)) {
    error_log('FILTER1: ' . print_r($filter, true));
    array_unshift($filter, 'FILTER', count($filter) / 3);
    error_log('FILTER2: ' . print_r($filter, true));
    $get_params = array_merge($get_params, $filter);
}
error_log('###########################################');
error_log(print_r($get_params, true));

$RESULT = call_user_func_array(array($redis, 'rawCommand'), $get_params);

error_log(implode(' ', $get_params));

$field = array(
        'acknowledged',
        'active_checks',
        'current_check_attempt',
        'current_state',
        'display_name',
        'event_handler_enabled',
        'flap_detection',
        'flapping',
        'host_id',
        'host_name',
        'icon_image',
        'last_check',
        'last_hard_state_change',
        'last_state_change',
        'max_check_attempts',
        'next_check',
        'notify',
        'passive_checks',
        'plugin_output',
        'poller_id',
        'scheduled_downtime_depth',
        'service_description',
        'service_id',
        'state_type',
        );

$field1 = array('SORT', $store, 'BY', 'NOSORT');
foreach ($field as $f) {
    $field1[] = 'GET';
    $field1[] = '*->' . $f;
}
$RESULT = call_user_func_array(array($redis, 'rawCommand'), $field1);
error_log(implode(' ', $field1));

$redis->unlink($store);

$numRows = $redis->get($store . ':size');
error_log("NUM ROWS: " . $numRows);
$redis->unlink($store . ':size');

$rows = build_search_rows($RESULT, $field);

error_log('######################################################');
error_log(print_r($rows, true));

//$critRes = $obj->DBC->query(
//        "SELECT value, service_id FROM customvariables WHERE name = 'CRITICALITY_ID' AND service_id IS NOT NULL"
//        );
$criticalityUsed = 0;
foreach ($rows as $row) {
  if (isset($row['criticality_id'])) {
    $criticalityUsed = 1;
    break;
  }
}

//$critCache = array();
//if ($critRes->numRows()) {
//    $criticalityUsed = 1;
//    while ($critRow = $critRes->fetchRow()) {
//        $critCache[$critRow['service_id']] = $critRow['value'];
//    }
//}

/* * **************************************************
 * Create Buffer
 */
$obj->XML->startElement('reponse');
$obj->XML->startElement('i');
$obj->XML->writeElement('numrows', $numRows);
$obj->XML->writeElement('num', $num);
$obj->XML->writeElement('limit', $limit);
$obj->XML->writeElement('p', $p);
$obj->XML->writeElement('nc', $nc);
$obj->XML->writeElement('o', $o);
$obj->XML->writeElement('hard_state_label', _('Hard State Duration'));
$obj->XML->writeElement('http_link', _('HTTP Link'));
$obj->XML->writeElement('http_action_link', _('HTTP Action Link'));
$obj->XML->writeElement('host_currently_downtime', _('Host is currently on downtime'));
$obj->XML->writeElement('problem_ack', _('Problem has been acknowledged'));
$obj->XML->writeElement('host_passive_mode', _('This host is only checked in passive mode'));
$obj->XML->writeElement('host_never_checked', _('This host is never checked'));
$obj->XML->writeElement('service_currently_downtime', _('Service is currently on Downtime'));
$obj->XML->writeElement('service_passive_mode', _('This service is only checked in passive mode'));
$obj->XML->writeElement('service_not_active_not_passive', _('This service is neither active nor passive'));
$obj->XML->writeElement('service_flapping', _('This Service is flapping'));
$obj->XML->writeElement('notif_disabled', _('Notification is disabled'));
$obj->XML->writeElement('use_criticality', $criticalityUsed);
$obj->XML->endElement();

$host_prev = 0;
$ct = 0;
$flag = 0;

$hosts = array();
$instances = array();

function setSearchDefaultHostFields(&$row) {
    $defaultField = array(
        'acknowledged' => 0,
        'active_checks' => 1,
        'passive_checks' => 0,
        'current_check_attempt' => 1,
//        'current_state' => 4,
        'event_handler_enabled' => 1,
        'flapping' => 0,
        'flap_detection' => 1,
        'last_hard_state_change' => 0,
        'last_state_change' => 0,
        'max_check_attempts' => 1,
        'notify' => 1,
        'scheduled_downtime_depth' => 0,
        'state_type' => 1,
    );

    foreach ($defaultField as $k => $f) {
        if (!isset($row[$k]) || empty($row[$k])) {
            $row[$k] = $f;
        }
    }
}

function setSearchDefaultSvcFields(&$row) {
    $defaultField = array(
        'acknowledged' => 0,
        'active_checks' => 0,
        'passive_checks' => 1,
        'current_check_attempt' => 1,
        'current_state' => 4,
        'event_handler_enabled' => 1,
        'flapping' => 0,
        'flap_detection' => 1,
        'last_hard_state_change' => 0,
        'last_state_change' => 0,
        'max_check_attempts' => 1,
        'notify' => 0,
        'scheduled_downtime_depth' => 0,
        'state_type' => 1,
    );

    foreach ($defaultField as $k => $f) {
        if (!isset($row[$k])) {
            $row[$k] = $f;
        }
    }
}

foreach ($rows as $row) {
    assert(!empty($row));
    $duration = ' ';

    /* Split plugin_output */
    if (!isset($row['plugin_output'])) {
      $row['plugin_output'] = '';
    }
    $outputLines = explode("\n", $row['plugin_output']);
    $pluginShortOuput = $outputLines[0];

    if ($row['last_state_change'] > 0 && time() > $row['last_state_change']) {
        $duration = CentreonDuration::toString(time() - $row['last_state_change']);
    } elseif ($row['last_state_change'] > 0) {
        $duration = ' - ';
    }

    $hard_duration = ' N/S ';
    if ($row['last_hard_state_change'] > 0 && $row['last_hard_state_change'] >= $row['last_state_change']) {
        $hard_duration = CentreonDuration::toString(time() - $row['last_hard_state_change']);
    }

    $class = null;
    if ($row['scheduled_downtime_depth'] > 0) {
        $class = 'line_downtime';
    } elseif ($row['current_state'] == 2) {
        $row['acknowledged'] == 1 ? $class = 'line_ack' : $class = 'list_down';
    } else {
        if ($row['acknowledged'] == 1) {
            $class = 'line_ack';
        }
    }

    $obj->XML->startElement('l');
    $trClass = $obj->getNextLineClass();
    if (isset($class)) {
        $trClass = $class;
    }
    $obj->XML->writeAttribute('class', $trClass);
    $obj->XML->writeElement('o', $ct++);

    $isMeta = 0;
    $row['host_display_name'] = $row['host_name'];
    if (strpos($row['host_name'], '_Module_Meta') === 0) {
      $isMeta = 1;
      $row['host_display_name'] = 'Meta';
      $row['host_state'] = '0';
    }

    /* Let's get the associated host: in cache or from Redis */
    $hid = $row['host_id'];
    $host_key = 'h:' . $hid;
    if (!isset($hosts[$hid])) {
      $hosts[$hid] = $redis->hMGet(
          'h:' . $hid,
          array('current_state', 'notes', 'notes_url', 'action_url', 'scheduled_downtime_depth', 'icon_image', 'address')
      );
      setSearchDefaultHostFields($hosts[$hid]);
    }
    $hst = &$hosts[$hid];
    error_log('SET ROW '.$row['service_id'] . ': HOST PASSIVE CHECKS ' . $hst['passive_checks']);
    /* Let's get the instance name: in cache or from Redis */
    if (!isset($instances[$row['poller_id']])) {
      $instances[$row['poller_id']] = $redis->get('i:' . $row['poller_id']);
    }
    $inst = &$instances[$row['poller_id']];

    if ($host_prev == $hid) {
        $obj->XML->writeElement('hc', 'transparent');
        $obj->XML->writeElement('isMeta', $isMeta);
        $obj->XML->writeElement('hdn', $row['host_display_name']);
        $obj->XML->startElement('hn');
        $obj->XML->writeAttribute('none', '1');
        $obj->XML->text(CentreonUtils::escapeSecure(urlencode($row['host_name'])));
        $obj->XML->endElement();
        $obj->XML->writeElement('hnl', CentreonUtils::escapeSecure(urlencode($row['host_name'])));
        $obj->XML->writeElement('hid', $hid);
    } else {
        $host_prev = $hid;
        if ($hst['scheduled_downtime_depth'] == 0) {
          $obj->XML->writeElement('hc', $obj->colorHostInService[$hst['current_state']]);
        } else {
          $obj->XML->writeElement('hc', $obj->general_opt['color_downtime']);
        }
        $obj->XML->writeElement('isMeta', $isMeta);
        $obj->XML->writeElement('hnl', CentreonUtils::escapeSecure(urlencode($row['host_name'])));
        $obj->XML->writeElement('hdn', $row['host_display_name']);
        $obj->XML->startElement('hn');
        $obj->XML->writeAttribute('none', '0');
        $obj->XML->text(CentreonUtils::escapeSecure(urlencode($row['host_name'])), true, false);
        $obj->XML->endElement();

        $hostNotesUrl = 'none';
        if ($hst['notes_url']) {
            $hostNotesUrl = str_replace('$HOSTNAME$', $row['host_name'], $hst['notes_url']);
            $hostNotesUrl = str_replace('$HOSTALIAS$', $hst['alias'], $hostNotesUrl);
            $hostNotesUrl = str_replace('$HOSTADDRESS$', $hst['address'], $hostNotesUrl);
            $hostNotesUrl = str_replace('$INSTANCENAME$', $inst, $hostNotesUrl);
            $hostNotesUrl = str_replace('$HOSTSTATE$', $obj->statusHost[$hst['current_state']], $hostNotesUrl);
            $hostNotesUrl = str_replace('$HOSTSTATEID$', $hst['current_state'], $hostNotesUrl);
            $hostNotesUrl = str_replace(
                '$INSTANCEADDRESS$',
                $instanceObj->getParam($inst, 'ns_ip_address'),
                $hostNotesUrl
            );
        }
        $obj->XML->writeElement(
            'hnu',
            CentreonUtils::escapeSecure($obj->hostObj->replaceMacroInString($row['host_name'], $hostNotesUrl))
        );

        $hostActionUrl = 'none';
        if ($hst['action_url']) {
            $hostActionUrl = str_replace('$HOSTNAME$', $row['host_name'], $data["h_action_url"]);
            $hostActionUrl = str_replace('$HOSTALIAS$', $hst['alias'], $hostActionUrl);
            $hostActionUrl = str_replace('$HOSTADDRESS$', $hst['address'], $hostActionUrl);
            $hostActionUrl = str_replace('$INSTANCENAME$', $inst, $hostActionUrl);
            $hostActionUrl = str_replace('$HOSTSTATE$', $obj->statusHost[$hst['current_state']], $hostActionUrl);
            $hostActionUrl = str_replace('$HOSTSTATEID$', $hst['current_host'], $hostActionUrl);
            $hostActionUrl = str_replace(
                '$INSTANCEADDRESS$',
                $instanceObj->getParam($inst, 'ns_ip_address'),
                $hostActionUrl
            );
        }
        $obj->XML->writeElement(
            'hau',
            CentreonUtils::escapeSecure($obj->hostObj->replaceMacroInString($row['host_name'], $hostActionUrl))
        );

        $obj->XML->writeElement('hnn', CentreonUtils::escapeSecure($hst['notes']));
        $obj->XML->writeElement('hico', $hst['icon_image']);
        $obj->XML->writeElement('hip', CentreonUtils::escapeSecure($hst['address']));
        $obj->XML->writeElement('hdtm', $hst['scheduled_downtime_depth']);
        $obj->XML->writeElement(
            'hdtmXml',
            './include/monitoring/downtime/xml/broker/makeXMLForDowntime.php?hid=' . $hid
        );
        $obj->XML->writeElement('hdtmXsl', './include/monitoring/downtime/xsl/popupForDowntime.xsl');
        $obj->XML->writeElement(
            'hackXml',
            './include/monitoring/acknowlegement/xml/broker/makeXMLForAck.php?hid=' . $hid
        );
        $obj->XML->writeElement('hackXsl', './include/monitoring/acknowlegement/xsl/popupForAck.xsl');
        $obj->XML->writeElement('hid', $hid);
    }
    $obj->XML->writeElement('hs', $hst['current_state']);

    /*
     * Add possibility to use display name
     */
    if ($isMeta) {
        $obj->XML->writeElement('sdn', CentreonUtils::escapeSecure($row['display_name']), false);
    } else {
        $obj->XML->writeElement('sdn', CentreonUtils::escapeSecure($row['service_description']), false);
    }
    $obj->XML->writeElement('sd', CentreonUtils::escapeSecure($row['service_description']), false);

    $obj->XML->writeElement('sico', $row['icon_image']);
    $obj->XML->writeElement('sdl', CentreonUtils::escapeSecure(urlencode($row['service_description'])));
    $obj->XML->writeElement('svc_id', $row['service_id']);
    $obj->XML->writeElement('sc', $obj->colorService[$row['current_state']]);
    $obj->XML->writeElement('cs', _($obj->statusService[$row['current_state']]), false);
    $obj->XML->writeElement('ssc', $row['current_state']);
    $obj->XML->writeElement('po', CentreonUtils::escapeSecure($pluginShortOuput));
    $obj->XML->writeElement(
        'ca',
        $row['current_check_attempt'] . '/' . $row['max_check_attempts']
            . ' (' . $obj->stateType[$row['state_type']] . ')'
    );
    if (isset($row['criticality_id'])) {
        $obj->XML->writeElement('hci', 1); // has criticality
        $critData = $criticality->getData($row['criticality_id'], true);
        $obj->XML->writeElement('ci', $media->getFilename($critData['icon_id']));
        $obj->XML->writeElement('cih', CentreonUtils::escapeSecure($critData['name']));
    } else {
        $obj->XML->writeElement('hci', 0); // has no criticality
    }
    $obj->XML->writeElement('ne', $row['notify']);
    $obj->XML->writeElement('pa', $row['acknowledged']);
    $obj->XML->writeElement('pc', $row['passive_checks']);
    $obj->XML->writeElement('ac', $row['active_checks']);
    $obj->XML->writeElement('eh', $row['event_handler_enabled']);
    $obj->XML->writeElement('is', $row['flapping']);
    $obj->XML->writeElement('dtm', $row['scheduled_downtime_depth']);
    $obj->XML->writeElement(
        'dtmXml',
        './include/monitoring/downtime/xml/broker/makeXMLForDowntime.php?hid='
            . $hid . '&svc_id=' . $row['service_id']
    );
    $obj->XML->writeElement('dtmXsl', './include/monitoring/downtime/xsl/popupForDowntime.xsl');
    $obj->XML->writeElement(
        'ackXml',
        './include/monitoring/acknowlegement/xml/broker/makeXMLForAck.php?hid='
            . $hid . '&svc_id=' . $row['service_id']
    );
    $obj->XML->writeElement('ackXsl', './include/monitoring/acknowlegement/xsl/popupForAck.xsl');

    if (!empty($row['notes'])) {
        $row['notes'] = str_replace('$SERVICEDESC$', $row['service_description'], $row['notes']);
        $row['notes'] = str_replace('$HOSTNAME$', $row['host_name'], $row['notes']);
        if (isset($row['alias']) && $row['alias']) {
            $row['notes'] = str_replace('$HOSTALIAS$', $row['alias'], $row['notes']);
        }
        if (isset($row['address']) && $row['address']) {
            $row['notes'] = str_replace('$HOSTADDRESS$', $row['address'], $row['notes']);
        }
        if ($inst) {
            $row['notes'] = str_replace('$INSTANCENAME$', $inst, $row['notes']);
            $row['notes'] = str_replace(
                '$INSTANCEADDRESS$',
                $instanceObj->getParam($inst, 'ns_ip_address'),
                $row['notes']
            );
        }
        $obj->XML->writeElement('snn', CentreonUtils::escapeSecure($row['notes']));
    } else {
        $obj->XML->writeElement('snn', 'none');
    }

    if (!empty($row['notes_url'])) {
        $row['notes_url'] = str_replace('$SERVICEDESC$', $row['description'], $row['notes_url']);
        $row['notes_url'] = str_replace('$SERVICESTATEID$', $row['state'], $row['notes_url']);
        $row['notes_url'] = str_replace(
            '$SERVICESTATE$',
            $obj->statusService[$row['state']],
            $row['notes_url']
        );
        $row['notes_url'] = str_replace('$HOSTNAME$', $row['host_name'], $row['notes_url']);
        if (isset($row['alias']) && $row['alias']) {
            $row['notes_url'] = str_replace('$HOSTALIAS$', $row['alias'], $row['notes_url']);
        }
        if (isset($row['address']) && $row['address']) {
            $row['notes_url'] = str_replace('$HOSTADDRESS$', $row['address'], $row['notes_url']);
        }
        if (isset($row['instance_name']) && $row['instance_name']) {
            $row['notes_url'] = str_replace('$INSTANCENAME$', $row['instance_name'], $row['notes_url']);
            $row['notes_url'] = str_replace(
                '$INSTANCEADDRESS$',
                $instanceObj->getParam($row['instance_name'], 'ns_ip_address'),
                $row['notes_url']
            );
        }
        $obj->XML->writeElement('snu', CentreonUtils::escapeSecure($obj->serviceObj->replaceMacroInString($row['service_id'], $row['notes_url'])));
    } else {
        $obj->XML->writeElement('snu', 'none');
    }

    if (!empty($row['action_url'])) {
        $row['action_url'] = str_replace('$SERVICEDESC$', $row['description'], $row['action_url']);
        $row['action_url'] = str_replace('$SERVICESTATEID$', $row['state'], $row['action_url']);
        $row['action_url'] = str_replace('$SERVICESTATE$', $obj->statusService[$row['state']], $row['action_url']);
        $row['action_url'] = str_replace('$HOSTNAME$', $row['host_name'], $row['action_url']);
        if (isset($row['alias']) && $row['alias']) {
            $row['action_url'] = str_replace('$HOSTALIAS$', $row['alias'], $row['action_url']);
        }
        if (isset($row['address']) && $row['address']) {
            $row['action_url'] = str_replace('$HOSTADDRESS$', $row['address'], $row['action_url']);
        }
        if (isset($row['instance_name']) && $row['instance_name']) {
            $row['action_url'] = str_replace('$INSTANCENAME$', $row['instance_name'], $row['action_url']);
            $row['action_url'] = str_replace(
                '$INSTANCEADDRESS$',
                $instanceObj->getParam($row['instance_name'], 'ns_ip_address'),
                $row['action_url']
            );
        }
        $obj->XML->writeElement(
            'sau',
            CentreonUtils::escapeSecure(
                $obj->serviceObj->replaceMacroInString($row['service_id'], $row['action_url'])
            )
        );
    } else {
        $obj->XML->writeElement('sau', 'none');
    }

    if (!empty($row['notes'])) {
        $row['notes'] = str_replace('$SERVICEDESC$', $row['service_description'], $row['notes']);
        $row['notes'] = str_replace('$HOSTNAME$', $row['host_name'], $row['notes']);
        if (isset($row['alias']) && $row['alias']) {
            $row['notes'] = str_replace('$HOSTALIAS$', $row['alias'], $row['notes']);
        }
        if (isset($row['address']) && $row['address']) {
            $row['notes'] = str_replace('$HOSTADDRESS$', $row['address'], $row['notes']);
        }
        $obj->XML->writeElement('sn', CentreonUtils::escapeSecure($row['notes']));
    } else {
        $obj->XML->writeElement('sn', 'none');
    }

    error_log('ROW '.$row['service_id'] . ': FLAP DETECTION ' . $row['flap_detection']);
    $obj->XML->writeElement('fd', $row['flap_detection']);
    $obj->XML->writeElement('ha', $hst['acknowledged']);
    $obj->XML->writeElement('hae', $hst['active_checks']);
    error_log('ROW '.$row['service_id'] . ': HOST PASSIVE CHECKS ' . $hst['passive_checks']);
    $obj->XML->writeElement('hpe', $hst['passive_checks']);
    $obj->XML->writeElement('nc', $obj->GMT->getDate($dateFormat, $row['next_check']));
    if ($row['last_check'] != 0) {
        $obj->XML->writeElement('lc', CentreonDuration::toString(time() - $row['last_check']));
    } else {
        $obj->XML->writeElement('lc', 'N/A');
    }
    $obj->XML->writeElement('d', $duration);
    $obj->XML->writeElement('rd', (time() - $row['last_state_change']));
    $obj->XML->writeElement('last_hard_state_change', $hard_duration);

    /**
     * Get Service Graph index
     */
    if ($hid != '') {
        if ((!isset($graphs[$hid]) || !isset($graphs[$hid][$row['service_id']]))) {
            $request2 = 'SELECT DISTINCT service_id, id '
                . 'FROM index_data, metrics '
                . 'WHERE metrics.index_id = index_data.id '
                . 'AND host_id = ' . $hid . ' '
                . 'AND service_id = ' . $row['service_id'] . ' '
                . 'AND index_data.hidden = \'0\' ';
            $DBRESULT2 = $obj->DBC->query($request2);
            error_log($request2);
            while ($dataG = $DBRESULT2->fetchRow()) {
                if (!isset($graphs[$hid])) {
                    $graphs[$hid] = array();
                }
                $graphs[$hid][$dataG['service_id']] = $dataG['id'];
            }
            if (!isset($graphs[$hid])) {
                $graphs[$hid] = array();
            }
        }
        $obj->XML->writeElement(
            'svc_index',
            (isset($graphs[$hid][$row['service_id']]) ? $graphs[$hid][$row['service_id']] : 0)
        );
    }
    $obj->XML->endElement();
}

unset($rows);

if (!$ct) {
    $obj->XML->writeElement("infos", "none");
}

$obj->XML->writeElement("sid", $obj->session_id);
$obj->XML->endElement();

/*
 * Send Header
 */
$obj->header();

/*
 * Send XML
 */
$obj->XML->output();
