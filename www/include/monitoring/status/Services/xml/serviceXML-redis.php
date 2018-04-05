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

/* Connection to Redis
 */
$redis = new Redis();
$redis->connect($conf_centreon['redisServer'], $conf_centreon['redisPort']);
$redis->auth($conf_centreon['redisPassword']);

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
$graphs = array();

/** * *************************************************
 * Get Service status
 */

$tabOrder = array();
//$tabOrder["criticality_id"] = " ORDER BY isnull $order, criticality $order, h.name, s.description ";
//$tabOrder["host_name"] = " ORDER BY h.name " . $order . ", s.description ";
//$tabOrder["service_description"] = " ORDER BY s.description " . $order . ", h.name";
//$tabOrder["current_state"] = " ORDER BY s.state " . $order . ", h.name, s.description";
//$tabOrder["last_state_change"] = " ORDER BY s.last_state_change " . $order . ", h.name, s.description";
//$tabOrder["last_hard_state_change"] = " ORDER by s.last_hard_state_change " . $order . ", h.name, s.description";
//$tabOrder["last_check"] = " ORDER BY s.last_check " . $order . ", h.name, s.description";
//$tabOrder["current_attempt"] = " ORDER BY s.check_attempt " . $order . ", h.name, s.description";
//$tabOrder['output'] = ' ORDER BY s.output ' . $order . ', h.name, s.description';
$tabOrder["default"] = $tabOrder['criticality_id'];

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

/** * **************************************************
 * Get Pagination Rows
 */
//$DBRESULT = $obj->DBC->query($request);
//$numRows = $obj->DBC->numberRows();

//$critRes = $obj->DBC->query(
//        "SELECT value, service_id FROM customvariables WHERE name = 'CRITICALITY_ID' AND service_id IS NOT NULL"
//        );
//$criticalityUsed = 0;
//$critCache = array();
//if ($critRes->numRows()) {
//    $criticalityUsed = 1;
//    while ($critRow = $critRes->fetchRow()) {
//        $critCache[$critRow['service_id']] = $critRow['value'];
//    }
//}

$hkey_prev = 0;
$hst = null;
$instance = null;
$ct = 0;
$flag = 0;
$isMeta = 0;

$svc_it = null;

$host = array();
$inst = array();
$hostGroup = array();
$serviceGroup = array();

$states = array( 'ok', 'warning', 'critical', 'unknown', 'pending' );
$rows = array();
$numRows = 0;

do {
    $services = $redis->scan($svc_it, 's:*');

    if ($services !== false) {
        foreach ($services as $k) {
            $hst_id = explode(':', $k);
            $svc_id = $hst_id[2];
            $hst_id = $hst_id[1];

            error_log('Service ' . $k);

            if (! isset($host[$hst_id])) {
                $tmp_hst = $redis->hgetall("h:$hst_id");
                if ($tmp_hst !== false) {
                    if ($search_host
                            && strpos($tmp_hst['name'], $search_host) === false
                            && strpos($tmp_hst['alias'], $search_host) === false
                            && strpos($tmp_hst['address'], $search_host) === false) {
                        $host[$hst_id] = false;
                    }
                    else {
                        $host[$hst_id] = $tmp_hst;
                    }
                }
            }

            $hst = $host[$hst_id];
            $svc = $redis->hgetall($k);

            /* Is there a filter on the host ? */
            if ($hst === false) {
                continue;
            }
            /* Is there a filter on host groups ? */
            if (isset($hostgroups) && $hostgroups != 0) {
                if (! isset($hostGroup[$hst_id])) {
                    $hostGroup[$hst_id] = $redis->sIsMember("hgm:$hst_id", $hostgroups);
                }
                if ($hostGroup[$hst_id] === false)
                    continue;
            }
            /* Is there a filter on an instance ? */
            if ($instance != -1 && !empty($instance) && $hst['poller_id'] != $instance) {
                continue;
            }
            /* Is there a filter on the service ? */
            if ($search && strpos($svc['description'], $search) === false
                    && strpos($svc['display_name'], $search) === false) {
                continue;
            }
            /* Is there a filter on service groups ? */
            if (isset($servicegroups) && $servicegroups != 0) {
                if (! isset($serviceGroup[$k])) {
                    $serviceGroup[$k] = $redis->sIsMember("sgm:$hst_id:$svc_id", $servicegroups);
                }
                if ($serviceGroup[$k] === false)
                    continue;
            }
            /* Is there a filter on one service status ? */
            if ($statusFilter && $states[$svc['state']] != $statusFilter) {
                continue;
            }
            /* Is there a ALL/UNHANDLED/PROBLEMS filter on services ? */
            if (
                    $statusService == 'svc_unhandled' &&
                    (
                     $svc['state'] == 0 || $svc['state'] == 4 ||
                     $svc['state_type'] != 1 ||
                     $svc['acknowledged'] != 0 ||
                     $svc['scheduled_downtime_depth'] != 0 ||
                     $hst['acknowledged'] != 0 ||
                     $hst['scheduled_downtime_depth'] != 0
                    )
               ) {
                continue;
            } elseif ($statusService == 'svcpb' && ($svc['state'] == 0 || $svc['state'] == 4)) {
                continue;
            }
            /* Is there a filter on the output ? */
            if ($search_output && strpos($svc['plugin_output'], $search_output) === false) {
                continue;
            }

            /* Is there a filter on criticalities ? */
            if ($criticality_id && (! isset($svc['criticality_id']) || $svc['criticality_id'] != $criticality_id)) {
                continue;
            }

            $row = array();
            if (isset($svc['criticality_id'])) {
                $row['criticality_id'] = $svc['criticality_id'];
            } else {
                $row['criticality_id'] = 0;
            }

            $inst_id = $hst['poller_id'];
            if (! isset($inst[$inst_id])) {
                $tmp_inst = $redis->hgetall('i:' . $inst_id);
                if ($tmp_inst !== false) {
                    $inst[$inst_id] = $tmp_inst;
                }
            }
            $curInst = $inst[$inst_id];

            $passive = 0;
            $active = 1;

            $class = null;
            if ($svc['scheduled_downtime_depth'] > 0) {
                $class = 'line_downtime';
            } elseif ($svc['state'] == 2) {
                $svc['acknowledged'] == 1 ? $class = 'line_ack' : $class = 'list_down';
            } elseif ($svc['acknowledged'] == 1) {
                $class = 'line_ack';
            }

            $isMeta = 0;
            $svc['host_display_name'] = $hst['name'];
            if (! strncmp($hst['name'], '_Module_Meta', strlen('_Module_Meta'))) {
                $isMeta = 1;
                $svc['host_display_name'] = 'Meta';
                $svc['host_state'] = '0';
            }

            /* Split the plugin_output */
            if (isset($svc['plugin_output'])) {
                $row['output'] = $svc['plugin_output'];
                $outputLines = explode("\n", $svc['plugin_output']);
                $pluginShortOuput = $outputLines[0];
            }
            else {
                $row['output'] = '';
                $outputLines = array('');
                $pluginShortOutput = '';
            }

            $row['o'] = $ct++;
            $row['hid'] = $hst_id;
            $row['host_name'] = $hst['name'];
            $row['hdn'] = $svc['host_display_name'];
            $row['isMeta'] = $isMeta;
            $row['last_state_change'] = $svc['last_state_change'];
            $row['last_hard_state_change'] = $svc['last_hard_state_change'];

            if ($hst['scheduled_downtime_depth'] == 0) {
                $row['hc'] = $obj->colorHostInService[$hst['state']];
            } else {
                $row['hc'] = $obj->general_opt['color_downtime'];
            }

            /*
             * Add possibility to use display name
             */
            if ($isMeta) {
                $row['sdn'] = CentreonUtils::escapeSecure($svc['display_name']);
            } else {
                $row['sdn'] = CentreonUtils::escapeSecure($svc['description']);
            }
            $row['service_description'] = $svc['description'];
            $row['sico'] = $svc['icon_image'];

            $row['sdl'] = CentreonUtils::escapeSecure(urlencode($svc['description']));
            $row['svc_id'] = $svc_id;
            $row['current_state'] = $svc['state'];
            $row['po'] = CentreonUtils::escapeSecure($pluginShortOuput);
            $row['current_attempt'] = $svc['current_check_attempt'];
            $row['ca'] = $svc['current_check_attempt'] . '/' . $svc['max_check_attempts'] . ' (' . $obj->stateType[$svc['state_type']] . ')';

            $row['ne'] = $svc['notify'];
            $row['pa'] = $svc['acknowledged'];
            $row['pc'] = $svc['passive_checks'];

            $row['ac'] = $svc['active_checks'];
            $row['eh'] = $svc['event_handler_enabled'];
            $row['is'] = $svc['flapping'];
            $row['dtm'] = $svc['scheduled_downtime_depth'];

//                if ($svc['notes'] != '') {
//                        $svc['notes'] = str_replace('$SERVICEDESC$', $svc['description'], $svc['notes']);
//                        $svc['notes'] = str_replace('$HOSTNAME$', $hst['name'], $svc['notes']);
//                        if (isset($hst['alias']) && $hst['alias']) {
//                            $svc['notes'] = str_replace('$HOSTALIAS$', $hst['alias'], $svc['notes']);
//                        }
//                        if (isset($hst['address']) && $hst['address']) {
//                            $svc['notes'] = str_replace('$HOSTADDRESS$', $hst['address'], $svc['notes']);
//                        }
//                        if (isset($curInst['name']) && $curInst['name']) {
//                            $svc['notes'] = str_replace('$INSTANCENAME$', $curInst['name'], $svc['notes']);
//                            $svc['notes'] = str_replace(
//                                    '$INSTANCEADDRESS$',
//                                    $instanceObj->getParam($curInst['name'], 'ns_ip_address'),
//                                    $svc['notes']
//                                    );
//                        }
//                        $obj->XML->writeElement('snn', CentreonUtils::escapeSecure($svc['notes']));
//                    } else {
//                        $obj->XML->writeElement('snn', 'none');
//                    }
//
//                    if ($svc['notes_url'] != '') {
//                        $svc['notes_url'] = str_replace('$SERVICEDESC$', $svc['description'], $svc['notes_url']);
//                        $svc['notes_url'] = str_replace('$SERVICESTATEID$', $svc['state'], $svc['notes_url']);
//                        $svc['notes_url'] = str_replace(
//                                '$SERVICESTATE$',
//                                $obj->statusService[$svc['state']],
//                                $svc['notes_url']
//                                );
//                        $svc['notes_url'] = str_replace('$HOSTNAME$', $hst['name'], $svc['notes_url']);
//                        if (isset($hst['alias']) && $hst['alias']) {
//                            $svc['notes_url'] = str_replace('$HOSTALIAS$', $hst['alias'], $svc['notes_url']);
//                        }
//                        if (isset($hst['address']) && $hst['address']) {
//                            $svc['notes_url'] = str_replace('$HOSTADDRESS$', $hst['address'], $svc['notes_url']);
//                        }
//                        if (isset($curInst['name']) && $curInst['name']) {
//                            $svc['notes_url'] = str_replace('$INSTANCENAME$', $curInst['name'], $svc['notes_url']);
//                            $svc['notes_url'] = str_replace(
//                                    '$INSTANCEADDRESS$',
//                                    $instanceObj->getParam($curInst['name'], 'ns_ip_address'),
//                                    $svc['notes_url']
//                                    );
//                        }
//                        $obj->XML->writeElement(
//                                'snu',
//                                CentreonUtils::escapeSecure($obj->serviceObj->replaceMacroInString(
//                                        $svc_id,
//                                        $svc['notes_url']
//                                        ))
//                                );
//                    } else {
//                        $obj->XML->writeElement('snu', 'none');
//                    }
//
//                    if ($svc['action_url'] != '') {
//                        $svc['action_url'] = str_replace('$SERVICEDESC$', $svc['description'], $svc['action_url']);
//                        $svc['action_url'] = str_replace('$SERVICESTATEID$', $svc['state'], $svc['action_url']);
//                        $svc['action_url'] = str_replace('$SERVICESTATE$', $obj->statusService[$svc['state']], $svc['action_url']);
//                        $svc['action_url'] = str_replace('$HOSTNAME$', $hst['name'], $svc['action_url']);
//                        if (isset($hst['alias']) && $hst['alias']) {
//                            $svc['action_url'] = str_replace('$HOSTALIAS$', $hst['alias'], $svc['action_url']);
//                        }
//                        if (isset($hst['address']) && $hst['address']) {
//                            $svc['action_url'] = str_replace('$HOSTADDRESS$', $hst['address'], $svc['action_url']);
//                        }
//                        if (isset($curInst['name']) && $curInst['name']) {
//                            $svc['action_url'] = str_replace('$INSTANCENAME$', $curInst['name'], $svc['action_url']);
//                            $svc['action_url'] = str_replace(
//                                    '$INSTANCEADDRESS$',
//                                    $instanceObj->getParam($curInst['name'], 'ns_ip_address'),
//                                    $svc['action_url']
//                                    );
//                        }
//                        $obj->XML->writeElement(
//                                'sau',
//                                CentreonUtils::escapeSecure(
//                                    $obj->serviceObj->replaceMacroInString($svc_id, $svc['action_url'])
//                                    )
//                                );
//                    } else {
//                        $obj->XML->writeElement('sau', 'none');
//                    }
//
//                    if ($svc['notes'] != '') {
//                        $svc['notes'] = str_replace('$SERVICEDESC$', $svc['description'], $svc['notes']);
//                        $svc['notes'] = str_replace('$HOSTNAME$', $hst['name'], $svc['notes']);
//                        if (isset($hst['alias']) && $hst['alias']) {
//                            $svc['notes'] = str_replace('$HOSTALIAS$', $hst['alias'], $svc['notes']);
//                        }
//                        if (isset($hst['address']) && $hst['address']) {
//                            $svc['notes'] = str_replace('$HOSTADDRESS$', $hst['address'], $svc['notes']);
//                        }
//                        $obj->XML->writeElement('sn', CentreonUtils::escapeSecure($svc['notes']));
//                    } else {
//                        $obj->XML->writeElement('sn', 'none');
//                    }
//
//                    $obj->XML->writeElement('fd', $svc['flap_detection']);
//                    $obj->XML->writeElement('ha', $hst['acknowledged']);
//                    $obj->XML->writeElement('hae', $hst['active_checks']);
//                    $obj->XML->writeElement('hpe', $hst['passive_checks']);
//                    $obj->XML->writeElement('nc', $obj->GMT->getDate($dateFormat, $svc['next_check']));
            $row['last_check'] = $svc['last_check'];
//                    /**
//                     * Get Service Graph index
//                     */
//                    if (!isset($graphs[$hst_id]) || !isset($graphs[$hst_id][$svc_id])) {
//                        $request2 = "SELECT DISTINCT service_id, id "
//                            . "FROM index_data, metrics "
//                            . "WHERE metrics.index_id = index_data.id "
//                            . "AND host_id = " . $hst_id . " "
//                            . "AND service_id = " . $svc_id . " "
//                            . "AND index_data.hidden = '0' ";
//                        $DBRESULT2 = $obj->DBC->query($request2);
//                        while ($dataG = $DBRESULT2->fetchRow()) {
//                            if (!isset($graphs[$hst_id])) {
//                                $graphs[$hst_id] = array();
//                            }
//                            $graphs[$hst_id][$dataG["service_id"]] = $dataG["id"];
//                        }
//                        if (!isset($graphs[$hst_id])) {
//                            $graphs[$hst_id] = array();
//                        }
//                    }
//                    $obj->XML->writeElement(
//                            "svc_index",
//                            (isset($graphs[$hst_id][$svc_id]) ? $graphs[$hst_id][$svc_id] : 0)
//                            );
//                    $obj->XML->endElement();
            $rows[] = $row;
            $numRows++;
        }
    }
} while ($svc_it > 0);

array_multisort(
        array_column($rows, $sort_type), $order == 'ASC' ? SORT_ASC:SORT_DESC,
        array_column($rows, 'host_name'), SORT_ASC,
        array_column($rows, 'service_description'), SORT_ASC,
        $rows
        );

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

$prev_hst_id = 0;
foreach ($rows as $row) {
    $obj->XML->startElement('l');
    $trClass = $obj->getNextLineClass();
    if (isset($class)) {
        $trClass = $class;
    }
    $obj->XML->writeAttribute('class', $trClass);
    $obj->XML->writeElement('o', $row['o']);
    if ($prev_hst_id == $row['hid']) {
        $obj->XML->writeElement('hc', 'transparent');
        $obj->XML->writeElement('isMeta', $row['isMeta']);
        $obj->XML->writeElement('hdn', $row['hdn']);
        $obj->XML->startElement('hn');
        $obj->XML->writeAttribute('none', '1');
        $obj->XML->text(CentreonUtils::escapeSecure(urlencode($row['host_name'])));
        $obj->XML->endElement();
        $obj->XML->writeElement('hnl', CentreonUtils::escapeSecure(urlencode($row['host_name'])));
        $obj->XML->writeElement('hid', $row['hid']);
    } else {
        $prev_hst_id = $row['hid'];
        $obj->XML->writeElement('hc', $row['hc']);
        $obj->XML->writeElement('isMeta', $row['isMeta']);
        $obj->XML->writeElement('hnl', CentreonUtils::escapeSecure(urlencode($row['host_name'])));
        $obj->XML->writeElement('hdn', $row['hdn']);
        $obj->XML->startElement('hn');
        $obj->XML->writeAttribute('none', '0');
        $obj->XML->text(CentreonUtils::escapeSecure(urlencode($row['host_name'])), true, false);
        $obj->XML->endElement();
    }

    $hst = $host[$row['hid']];
    $curInst = $inst[$hst['poller_id']];

    $hostNotesUrl = 'none';
    if ($hst['notes_url']) {
        $hostNotesUrl = str_replace('$HOSTNAME$', $hst['name'], $hst['notes_url']);
        $hostNotesUrl = str_replace('$HOSTALIAS$', $hst['alias'], $hostNotesUrl);
        $hostNotesUrl = str_replace('$HOSTADDRESS$', $hst['address'], $hostNotesUrl);
        $hostNotesUrl = str_replace('$INSTANCENAME$', $curInst['name'], $hostNotesUrl);
        $hostNotesUrl = str_replace('$HOSTSTATE$', $obj->statusHost[$hst['state']], $hostNotesUrl);
        $hostNotesUrl = str_replace('$HOSTSTATEID$', $hst['state'], $hostNotesUrl);
        $hostNotesUrl = str_replace(
                '$INSTANCEADDRESS$',
                $instanceObj->getParam($curInst['name'], 'ns_ip_address'),
                $hostNotesUrl
                );
    }
    $obj->XML->writeElement(
            'hnu',
            CentreonUtils::escapeSecure( $obj->hostObj->replaceMacroInString($hst['name'], $hostNotesUrl))
            );

    $hostActionUrl = 'none';
    if ($hst['action_url']) {
        $hostActionUrl = str_replace('$HOSTNAME$', $hst['name'], $hst['action_url']);
        $hostActionUrl = str_replace('$HOSTALIAS$', $hst['alias'], $hostActionUrl);
        $hostActionUrl = str_replace('$HOSTADDRESS$', $hst['address'], $hostActionUrl);
        $hostActionUrl = str_replace('$INSTANCENAME$', $curInst['name'], $hostActionUrl);
        $hostActionUrl = str_replace('$HOSTSTATE$', $obj->statusHost[$hst['state']], $hostActionUrl);
        $hostActionUrl = str_replace('$HOSTSTATEID$', $hst['state'], $hostActionUrl);
        $hostActionUrl = str_replace(
                '$INSTANCEADDRESS$',
                $instanceObj->getParam($curInst['name'], 'ns_ip_address'),
                $hostActionUrl
                );
    }
    $obj->XML->writeElement(
            'hau',
            CentreonUtils::escapeSecure($obj->hostObj->replaceMacroInString($hst['name'], $hostActionUrl))
            );

    $obj->XML->writeElement('hnn', CentreonUtils::escapeSecure($hst['notes']));

    $obj->XML->writeElement('hico', $hst['icon_image']);
    $obj->XML->writeElement('hip', CentreonUtils::escapeSecure($hst['address']));
    $obj->XML->writeElement('hdtm', $hst['scheduled_downtime_depth']);
    $obj->XML->writeElement(
            'hdtmXml',
            './include/monitoring/downtime/xml/broker/makeXMLForDowntime.php?hid=' . $row['hid']
            );
    $obj->XML->writeElement('hdtmXsl', './include/monitoring/downtime/xsl/popupForDowntime.xsl');
    $obj->XML->writeElement(
            'hackXml',
            './include/monitoring/acknowlegement/xml/broker/makeXMLForAck.php?hid=' . $row['hid']
            );
    $obj->XML->writeElement('hackXsl', './include/monitoring/acknowlegement/xsl/popupForAck.xsl');
    $obj->XML->writeElement('hid', $row['hid']);

    $obj->XML->writeElement('hs', $hst['state']);
    $obj->XML->writeElement('sdn', $row['sdn'], false);

    $obj->XML->writeElement('sd', CentreonUtils::escapeSecure($row['service_description']), false);
    $obj->XML->writeElement('sico', $row['sico']);
    $obj->XML->writeElement('sdl', $row['sdl']);
    $obj->XML->writeElement('svc_id', $row['svc_id']);

    if (! isset($row['current_state'])) {
        error_log('Service host: ' . $row['hid'] . ':' . $row['svc_id']);
    }
    $obj->XML->writeElement('sc', $obj->colorService[$row['current_state']]);
    $obj->XML->writeElement('cs', _($obj->statusService[$row['current_state']]), false);
    $obj->XML->writeElement('ssc', $row['current_state']);
    $obj->XML->writeElement('po', $row['po']);
    $obj->XML->writeElement('ca', $row['ca']);

    /* Criticality */
    if ($row['criticality_id']) {
        $obj->XML->writeElement("hci", 1); // has criticality
        //FIXME DBR
        $critData = $criticality->getData($row['criticality_id'], true);
        $obj->XML->writeElement("ci", $media->getFilename($critData['icon_id']));
        $obj->XML->writeElement("cih", CentreonUtils::escapeSecure($critData['name']));
    } else {
        $obj->XML->writeElement('hci', 0); // has no criticality
    }

    $obj->XML->writeElement('ne', $row['ne']);
    $obj->XML->writeElement('pa', $row['pa']);
    $obj->XML->writeElement('pc', $row['pc']);

    $obj->XML->writeElement('ac', $row['ac']);
    $obj->XML->writeElement('eh', $row['eh']);
    $obj->XML->writeElement('is', $row['is']);
    $obj->XML->writeElement('dtm', $row['dtm']);
    $obj->XML->writeElement('rd', (time() - $row['last_state_change']));

    $duration = ' ';
    if ($row['last_state_change'] > 0 && time() > $row['last_state_change']) {
        $duration = CentreonDuration::toString(time() - $row['last_state_change']);
    } elseif ($row['last_state_change'] > 0) {
        $duration = ' - ';
    }
    $obj->XML->writeElement('d', $duration);

    $hard_duration = ' N/S ';
    if ($row['last_hard_state_change'] > 0 && $row['last_hard_state_change'] >= $row['last_state_change']) {
        $hard_duration = CentreonDuration::toString(time() - $row['last_hard_state_change']);
    }
    $obj->XML->writeElement('last_hard_state_change', $hard_duration);

    if ($row['last_check'] != 0) {
        $obj->XML->writeElement('lc', CentreonDuration::toString(time() - $row['last_check']));
    } else {
        $obj->XML->writeElement('lc', 'N/A');
    }

    $obj->XML->writeElement(
            'dtmXml',
            './include/monitoring/downtime/xml/broker/makeXMLForDowntime.php?hid='
            . $row['hid'] . '&svc_id=' . $row['svc_id']
            );
    $obj->XML->writeElement('dtmXsl', './include/monitoring/downtime/xsl/popupForDowntime.xsl');
    $obj->XML->writeElement(
            'ackXml',
            './include/monitoring/acknowlegement/xml/broker/makeXMLForAck.php?hid='
            . $row['hid'] . '&svc_id=' . $row['svc_id']
            );
    $obj->XML->writeElement('ackXsl', './include/monitoring/acknowlegement/xsl/popupForAck.xsl');

    $obj->XML->endElement();
}

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
