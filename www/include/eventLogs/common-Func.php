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

function get_user_param($user_id, $pearDB)
{
    $list_param = array('log_filter_host', 'log_filter_svc', 'log_filter_host_down',
        'log_filter_host_up', 'log_filter_host_unreachable', 'log_filter_svc_ok',
        'log_filter_svc_warning', 'log_filter_svc_critical', 'log_filter_svc_unknown',
        'log_filter_notif', 'log_filter_error', 'log_filter_alert', 'log_filter_oh',
        'search_H', 'search_S', 'log_filter_period');
    $tab_row = array();
    foreach ($list_param as $param) {
        if (isset($_SESSION[$param])) {
            $tab_row[$param] = $_SESSION[$param];
        } else {
            if (!isset($cache)) {
                $cache = array();
                $query = "SELECT cp_key, cp_value FROM contact_param
						WHERE cp_key in ('" . join("', '", $list_param) . "') AND cp_contact_id = " . CentreonDB::escape($user_id);
                $DBRESULT = $pearDB->query($query);
                while ($row = $DBRESULT->fetchRow()) {
                    $cache[$row['cp_key']] = $row['cp_value'];
                    $SESSION[$row['cp_key']] = $row['cp_value'];
                }
            }
            if (isset($cache[$param])) {
                $tab_row[$param] = $cache[$param];
            }
        }
    }
    return $tab_row;
}

function set_user_param($user_id, $pearDB, $key, $value)
{
    $_SESSION[$key] = $value;
    $list_param = array('log_filter_host', 'log_filter_svc', 'log_filter_host_down',
        'log_filter_host_up', 'log_filter_host_unreachable', 'log_filter_svc_ok',
        'log_filter_svc_warning', 'log_filter_svc_critical', 'log_filter_svc_unknown',
        'log_filter_notif', 'log_filter_error', 'log_filter_alert', 'log_filter_oh', 'log_filter_period');
    if (in_array($key, $list_param)) {
        $queryDel = "DELETE FROM contact_param WHERE cp_key = '" . CentreonDB::escape($key) . "' AND cp_contact_id = '" . CentreonDB::escape($user_id) . "'";
        $queryIns = "INSERT INTO contact_param (cp_key, cp_value, cp_contact_id) " .
                "VALUES ('" . CentreonDB::escape($key) . "', '" . CentreonDB::escape($value) . "', " . CentreonDB::escape($user_id) . ")";
        $pearDB->query($queryDel);
        $pearDB->query($queryIns);
    }
}
