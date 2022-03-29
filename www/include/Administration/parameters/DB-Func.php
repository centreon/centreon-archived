<?php

/*
 * Copyright 2005-2020 Centreon
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

 /**
 * Used to update fields in the 'centreon.options' table
 *
 * @param \CentreonDB $pearDB : database connection
 * @param string $key : name of the row
 * @param string $value : value of the row
 */
function updateOption($pearDB, $key, $value)
{
    $stmt = $pearDB->prepare("DELETE FROM `options` WHERE `key` = :key");
    $stmt->bindValue(':key', $key, \PDO::PARAM_STR);
    $stmt->execute();

    $stmt = $pearDB->prepare("INSERT INTO `options` (`key`, `value`) VALUES (:key, :value)");
    $stmt->bindValue(':key', $key, \PDO::PARAM_STR);
    $stmt->bindValue(':value', $value, \PDO::PARAM_STR);
    $stmt->execute();
}

/**
 * Used to update fields in the 'centreon.informations' table
 *
 * @param object $pearDB : database connection
 * @param string $key : name of the row
 * @param string $value : value of the row
 */
function updateInformations($pearDB, string $key, string $value): void
{
    $stmt = $pearDB->prepare("DELETE FROM `informations` WHERE `key` = :key");
    $stmt->bindValue(':key', $key, \PDO::PARAM_STR);
    $stmt->execute();

    $stmt = $pearDB->prepare("INSERT INTO `informations` (`key`, `value`) VALUES (:key, :value)");
    $stmt->bindValue(':key', $key, \PDO::PARAM_STR);
    $stmt->bindValue(':value', $value, \PDO::PARAM_STR);
    $stmt->execute();
}

function is_valid_path_images($path)
{
    if (trim($path) == '') {
        return true;
    }
    if (is_dir($path)) {
        return true;
    }
    return false;
}

function is_valid_path($path)
{
    if (is_dir($path)) {
        return true;
    } else {
        return false;
    }
}

function is_readable_path($path)
{
    if (is_dir($path) && is_readable($path)) {
        return true;
    }
    return false;
}

function is_executable_binary($path)
{
    if (is_file($path) && is_executable($path)) {
        return true;
    }
    return false;
}

function is_writable_path($path)
{
    if (is_dir($path) && is_writable($path)) {
        return true;
    }
    return false;
}

function is_writable_file($path)
{
    if (is_file($path) && is_writable($path)) {
        return true;
    }
    return false;
}

function is_writable_file_if_exist($path = null)
{
    if (!$path) {
        return true;
    }
    if (is_file($path) && is_writable($path)) {
        return true;
    }
    return false;
}

/**
 * rule to check the session duration value chosen by the user
 * @param int $value
 * @param int $valueMax
 *
 * @return bool
 */
function isSessionDurationValid(int $value = null)
{
    return ($value > 0 && $value <= SESSION_DURATION_LIMIT);
}

function updateGeneralOptInDB($gopt_id = null)
{
    if (!$gopt_id) {
        return;
    }
    updateGeneralOpt($gopt_id);
}

function updateNagiosConfigData($gopt_id = null)
{
    global $form, $pearDB, $centreon;

    $ret = array();
    $ret = $form->getSubmitValues();

    updateOption(
        $pearDB,
        "nagios_path_img",
        isset($ret["nagios_path_img"]) && $ret["nagios_path_img"] != null
            ? $pearDB->escape($ret["nagios_path_img"]) : "NULL"
    );
    updateOption(
        $pearDB,
        "nagios_path_plugins",
        isset($ret["nagios_path_plugins"]) && $ret["nagios_path_plugins"] != null
            ? $pearDB->escape($ret["nagios_path_plugins"]) : "NULL"
    );
    updateOption(
        $pearDB,
        "mailer_path_bin",
        isset($ret["mailer_path_bin"]) && $ret["mailer_path_bin"] != null
            ? $pearDB->escape($ret["mailer_path_bin"]) : "NULL"
    );
    updateOption(
        $pearDB,
        "interval_length",
        isset($ret["interval_length"]) && $ret["interval_length"] != null
            ? $pearDB->escape($ret["interval_length"]) : "NULL"
    );
    updateOption(
        $pearDB,
        "broker",
        isset($ret["broker"]) && $ret["broker"] != null
            ? $pearDB->escape($ret['broker']) : "broker"
    );
    $pearDB->query("UPDATE acl_resources SET changed = 1");

    /*
     * Tactical Overview part
     */
    updateOption($pearDB, "tactical_host_limit", $ret['tactical_host_limit']);
    updateOption($pearDB, "tactical_service_limit", $ret['tactical_service_limit']);
    updateOption($pearDB, "tactical_refresh_interval", $ret['tactical_refresh_interval']);

    /*
     * Acknowledgement part
     */
    updateOption(
        $pearDB,
        "monitoring_ack_sticky",
        isset($ret["monitoring_ack_sticky"]) && $ret['monitoring_ack_sticky'] ? 1 : 0
    );
    updateOption(
        $pearDB,
        "monitoring_ack_notify",
        isset($ret["monitoring_ack_notify"]) && $ret['monitoring_ack_notify'] ? 1 : 0
    );
    updateOption(
        $pearDB,
        "monitoring_ack_persistent",
        isset($ret["monitoring_ack_persistent"]) && $ret['monitoring_ack_persistent'] ? 1 : 0
    );
    updateOption(
        $pearDB,
        "monitoring_ack_active_checks",
        isset($ret["monitoring_ack_active_checks"]) && $ret['monitoring_ack_active_checks'] ? 1 : 0
    );
    updateOption(
        $pearDB,
        "monitoring_ack_svc",
        isset($ret["monitoring_ack_svc"]) && $ret['monitoring_ack_svc'] ? 1 : 0
    );

    /*
     * Downtime part
     */
    updateOption(
        $pearDB,
        "monitoring_dwt_duration",
        isset($ret["monitoring_dwt_duration"]) && $ret['monitoring_dwt_duration']
            ? $pearDB->escape($ret['monitoring_dwt_duration']) : 3600
    );
    updateOption(
        $pearDB,
        "monitoring_dwt_duration_scale",
        isset($ret["monitoring_dwt_duration_scale"]) && $ret['monitoring_dwt_duration_scale']
            ? $pearDB->escape($ret['monitoring_dwt_duration_scale']) : 's'
    );
    updateOption(
        $pearDB,
        "monitoring_dwt_fixed",
        isset($ret["monitoring_dwt_fixed"]) && $ret['monitoring_dwt_fixed'] ? 1 : 0
    );
    updateOption(
        $pearDB,
        "monitoring_dwt_svc",
        isset($ret["monitoring_dwt_svc"]) && $ret['monitoring_dwt_svc'] ? 1 : 0
    );

    /*
     * Misc
     */
    updateOption(
        $pearDB,
        'monitoring_console_notification',
        isset($ret["monitoring_console_notification"]) && $ret['monitoring_console_notification'] ? 1 : 0
    );
    updateOption(
        $pearDB,
        'monitoring_host_notification_0',
        isset($ret["monitoring_host_notification_0"]) && $ret['monitoring_host_notification_0'] ? 1 : 0
    );
    updateOption(
        $pearDB,
        'monitoring_host_notification_1',
        isset($ret["monitoring_host_notification_1"]) && $ret['monitoring_host_notification_1'] ? 1 : 0
    );
    updateOption(
        $pearDB,
        'monitoring_host_notification_2',
        isset($ret["monitoring_host_notification_2"]) && $ret['monitoring_host_notification_2'] ? 1 : 0
    );
    updateOption(
        $pearDB,
        'monitoring_svc_notification_0',
        isset($ret["monitoring_svc_notification_0"]) && $ret['monitoring_svc_notification_0'] ? 1 : 0
    );
    updateOption(
        $pearDB,
        'monitoring_svc_notification_1',
        isset($ret["monitoring_svc_notification_1"]) && $ret['monitoring_svc_notification_1'] ? 1 : 0
    );
    updateOption(
        $pearDB,
        'monitoring_svc_notification_2',
        isset($ret["monitoring_svc_notification_2"]) && $ret['monitoring_svc_notification_2'] ? 1 : 0
    );
    updateOption(
        $pearDB,
        'monitoring_svc_notification_3',
        isset($ret["monitoring_svc_notification_3"]) && $ret['monitoring_svc_notification_3'] ? 1 : 0
    );

    $centreon->initOptGen($pearDB);
}

function updateGorgoneConfigData($db, $form, $centreon)
{
    $ret = $form->getSubmitValues();
    updateOption($db, "enable_broker_stats", isset($ret["enable_broker_stats"]) && $ret['enable_broker_stats'] ? 1 : 0);
    updateOption(
        $db,
        'gorgone_cmd_timeout',
        $ret['gorgone_cmd_timeout'] ?? 0
    );
    updateOption(
        $db,
        'gorgone_illegal_characters',
        $ret['gorgone_illegal_characters'] ?? ''
    );
    //API
    updateOption(
        $db,
        'gorgone_api_address',
        $ret['gorgone_api_address'] ?? '127.0.0.1'
    );
    updateOption(
        $db,
        'gorgone_api_port',
        $ret['gorgone_api_port'] ?? '8085'
    );
    updateOption(
        $db,
        'gorgone_api_username',
        $ret['gorgone_api_username'] ?? ''
    );
    updateOption(
        $db,
        'gorgone_api_password',
        $ret['gorgone_api_password'] ?? ''
    );
    updateOption(
        $db,
        'gorgone_api_ssl',
        $ret['gorgone_api_ssl'] ?? '0'
    );
    updateOption(
        $db,
        'gorgone_api_allow_self_signed',
        $ret['gorgone_api_allow_self_signed'] ?? '1'
    );
    $centreon->initOptGen($db);
}

function updateSNMPConfigData($gopt_id = null)
{
    global $form, $pearDB, $centreon;

    $ret = array();
    $ret = $form->getSubmitValues();

    updateOption(
        $pearDB,
        "snmp_community",
        isset($ret["snmp_community"]) && $ret["snmp_community"] != null ? $ret["snmp_community"] : "NULL"
    );
    updateOption(
        $pearDB,
        "snmp_version",
        isset($ret["snmp_version"]) && $ret["snmp_version"] != null ? $ret["snmp_version"] : "NULL"
    );
    updateOption(
        $pearDB,
        "snmp_trapd_path_conf",
        isset($ret["snmp_trapd_path_conf"]) && $ret["snmp_trapd_path_conf"] != null
            ? $ret["snmp_trapd_path_conf"] : "NULL"
    );
    updateOption(
        $pearDB,
        "snmptt_unknowntrap_log_file",
        isset($ret["snmptt_unknowntrap_log_file"]) && $ret["snmptt_unknowntrap_log_file"] != null
            ? $ret["snmptt_unknowntrap_log_file"] : "NULL"
    );
    updateOption(
        $pearDB,
        "snmpttconvertmib_path_bin",
        isset($ret["snmpttconvertmib_path_bin"]) && $ret["snmpttconvertmib_path_bin"] != null
            ? $ret["snmpttconvertmib_path_bin"] : "NULL"
    );
    updateOption(
        $pearDB,
        "perl_library_path",
        isset($ret["perl_library_path"]) && $ret["perl_library_path"] != null
            ? $ret["perl_library_path"] : "NULL"
    );

    $centreon->initOptGen($pearDB);
}

function updateDebugConfigData($gopt_id = null)
{
    global $form, $pearDB, $centreon;

    $ret = array();
    $ret = $form->getSubmitValues();

    updateOption(
        $pearDB,
        "debug_path",
        isset($ret["debug_path"]) && $ret["debug_path"] != null ? $ret["debug_path"] : "NULL"
    );
    updateOption($pearDB, "debug_auth", isset($ret["debug_auth"]) && $ret['debug_auth'] ? 1 : 0);
    updateOption(
        $pearDB,
        "debug_nagios_import",
        isset($ret["debug_nagios_import"]) && $ret['debug_nagios_import'] ? 1 : 0
    );
    updateOption($pearDB, "debug_rrdtool", isset($ret["debug_rrdtool"]) && $ret['debug_rrdtool'] ? 1 : 0);
    updateOption($pearDB, "debug_ldap_import", isset($ret["debug_ldap_import"]) && $ret['debug_ldap_import'] ? 1 : 0);
    updateOption($pearDB, "debug_sql", isset($ret["debug_sql"]) && $ret['debug_sql'] ? 1 : 0);
    updateOption($pearDB, "debug_gorgone", isset($ret["debug_gorgone"]) && $ret['debug_gorgone'] ? 1 : 0);
    updateOption($pearDB, "debug_centstorage", isset($ret["debug_centstorage"]) && $ret['debug_centstorage'] ? 1 : 0);
    updateOption(
        $pearDB,
        "debug_centreontrapd",
        isset($ret["debug_centreontrapd"]) && $ret['debug_centreontrapd'] ? 1 : 0
    );

    $centreon->initOptGen($pearDB);
}

function updateLdapConfigData($gopt_id = null)
{
    global $form, $pearDB, $centreon;

    $ret = array();
    $ret = $form->getSubmitValues();

    updateOption(
        $pearDB,
        "ldap_host",
        isset($ret["ldap_host"]) && $ret["ldap_host"] != null
            ? htmlentities($ret["ldap_host"], ENT_QUOTES, "UTF-8") : "NULL"
    );
    updateOption(
        $pearDB,
        "ldap_port",
        isset($ret["ldap_port"]) && $ret["ldap_port"] != null
            ? htmlentities($ret["ldap_port"], ENT_QUOTES, "UTF-8") : "NULL"
    );
    updateOption(
        $pearDB,
        "ldap_base_dn",
        isset($ret["ldap_base_dn"]) && $ret["ldap_base_dn"] != null
            ? htmlentities($ret["ldap_base_dn"], ENT_QUOTES, "UTF-8") : "NULL"
    );
    updateOption(
        $pearDB,
        "ldap_login_attrib",
        isset($ret["ldap_login_attrib"]) && $ret["ldap_login_attrib"] != null
            ? htmlentities($ret["ldap_login_attrib"], ENT_QUOTES, "UTF-8") : ""
    );
    updateOption(
        $pearDB,
        "ldap_ssl",
        isset($ret["ldap_ssl"]["ldap_ssl"]) && $ret["ldap_ssl"]["ldap_ssl"] != null
            ? htmlentities($ret["ldap_ssl"]["ldap_ssl"], ENT_QUOTES, "UTF-8") : "NULL"
    );
    updateOption(
        $pearDB,
        "ldap_auth_enable",
        isset($ret["ldap_auth_enable"]["ldap_auth_enable"]) && $ret["ldap_auth_enable"]["ldap_auth_enable"] != null
            ? htmlentities($ret["ldap_auth_enable"]["ldap_auth_enable"], ENT_QUOTES, "UTF-8") : "NULL"
    );
    updateOption(
        $pearDB,
        "ldap_search_user",
        isset($ret["ldap_search_user"]) && $ret["ldap_search_user"] != null
            ? htmlentities($ret["ldap_search_user"], ENT_QUOTES, "UTF-8") : ""
    );
    updateOption(
        $pearDB,
        "ldap_search_user_pwd",
        isset($ret["ldap_search_user_pwd"]) && $ret["ldap_search_user_pwd"] != null
            ? htmlentities($ret["ldap_search_user_pwd"], ENT_QUOTES, "UTF-8") : ""
    );
    updateOption(
        $pearDB,
        "ldap_search_filter",
        isset($ret["ldap_search_filter"]) && $ret["ldap_search_filter"] != null
            ? htmlentities($ret["ldap_search_filter"], ENT_QUOTES, "UTF-8") : "NULL"
    );
    updateOption(
        $pearDB,
        "ldap_search_timeout",
        isset($ret["ldap_search_timeout"]) && $ret["ldap_search_timeout"] != null
            ? htmlentities($ret["ldap_search_timeout"], ENT_QUOTES, "UTF-8") : "NULL"
    );
    updateOption(
        $pearDB,
        "ldap_search_limit",
        isset($ret["ldap_search_limit"]) && $ret["ldap_search_limit"] != null
            ? htmlentities($ret["ldap_search_limit"], ENT_QUOTES, "UTF-8") : "NULL"
    );
    updateOption(
        $pearDB,
        "ldap_protocol_version",
        isset($ret["ldap_protocol_version"]) && $ret["ldap_protocol_version"] != null
            ? htmlentities($ret["ldap_protocol_version"], ENT_QUOTES, "UTF-8") : "NULL"
    );

    $centreon->initOptGen($pearDB);
}

function updateGeneralConfigData($gopt_id = null)
{
    global $form, $pearDB, $centreon;

    $ret = array();
    $ret = $form->getSubmitValues();

    if (!isset($ret['AjaxTimeReloadStatistic'])) {
        throw new \InvalidArgumentException('Missing submitted values');
    }

    if (!isset($ret["session_expire"]) || $ret["session_expire"] == 0) {
        $ret["session_expire"] = 2;
    }

    updateOption(
        $pearDB,
        "oreon_path",
        isset($ret["oreon_path"]) && $ret["oreon_path"] != null
            ? htmlentities($ret["oreon_path"], ENT_QUOTES, "UTF-8") : null
    );
    updateOption(
        $pearDB,
        "oreon_web_path",
        isset($ret["oreon_web_path"]) && $ret["oreon_web_path"] != null
            ? htmlentities($ret["oreon_web_path"], ENT_QUOTES, "UTF-8") : null
    );
    updateOption(
        $pearDB,
        "oreon_refresh",
        isset($ret["oreon_refresh"]) && $ret["oreon_refresh"] != null
            ? htmlentities($ret["oreon_refresh"], ENT_QUOTES, "UTF-8") : null
    );
    updateOption(
        $pearDB,
        "inheritance_mode",
        !empty($ret["inheritance_mode"]["inheritance_mode"])
            ? (int)$ret["inheritance_mode"]["inheritance_mode"]
            : 3 //default cumulative inheritance
    );
    updateOption(
        $pearDB,
        "session_expire",
        isset($ret["session_expire"]) && $ret["session_expire"] != null
            ? htmlentities($ret["session_expire"], ENT_QUOTES, "UTF-8") : null
    );
    updateOption(
        $pearDB,
        "maxViewMonitoring",
        isset($ret["maxViewMonitoring"]) && $ret["maxViewMonitoring"] != null
            ? htmlentities($ret["maxViewMonitoring"], ENT_QUOTES, "UTF-8") : null
    );
    updateOption(
        $pearDB,
        "maxViewConfiguration",
        isset($ret["maxViewConfiguration"]) && $ret["maxViewConfiguration"] != null
            ? htmlentities($ret["maxViewConfiguration"], ENT_QUOTES, "UTF-8") : null
    );
    updateOption(
        $pearDB,
        "maxGraphPerformances",
        isset($ret["maxGraphPerformances"]) && $ret["maxGraphPerformances"] != null
            ? htmlentities($ret["maxGraphPerformances"], ENT_QUOTES, "UTF-8") : null
    );
    updateOption(
        $pearDB,
        "selectPaginationSize",
        isset($ret["selectPaginationSize"]) && $ret["selectPaginationSize"] != null
            ? htmlentities($ret["selectPaginationSize"], ENT_QUOTES, "UTF-8") : null
    );
    updateOption(
        $pearDB,
        "AjaxTimeReloadMonitoring",
        isset($ret["AjaxTimeReloadMonitoring"]) && $ret["AjaxTimeReloadMonitoring"] != null
            ? htmlentities($ret["AjaxTimeReloadMonitoring"], ENT_QUOTES, "UTF-8") : null
    );
    updateOption(
        $pearDB,
        "AjaxTimeReloadStatistic",
        isset($ret["AjaxTimeReloadStatistic"]) && $ret["AjaxTimeReloadStatistic"] != null
            ? htmlentities($ret["AjaxTimeReloadStatistic"], ENT_QUOTES, "UTF-8") : null
    );
    updateOption(
        $pearDB,
        "enable_gmt",
        isset($ret["enable_gmt"]["yes"]) && $ret["enable_gmt"]["yes"] != null
            ? htmlentities($ret["enable_gmt"]["yes"], ENT_QUOTES, "UTF-8") : "0"
    );
    updateOption(
        $pearDB,
        "gmt",
        isset($ret["gmt"]) && $ret["gmt"] != null ? htmlentities($ret["gmt"], ENT_QUOTES, "UTF-8") : null
    );
    updateOption(
        $pearDB,
        "global_sort_type",
        isset($ret["global_sort_type"]) && $ret["global_sort_type"] != null
            ? htmlentities($ret["global_sort_type"], ENT_QUOTES, "UTF-8") : null
    );
    updateOption(
        $pearDB,
        "global_sort_order",
        isset($ret["global_sort_order"]) && $ret["global_sort_order"] != null
            ? htmlentities($ret["global_sort_order"], ENT_QUOTES, "UTF-8") : null
    );
    updateOption(
        $pearDB,
        "problem_sort_type",
        isset($ret["problem_sort_type"]) && $ret["problem_sort_type"] != null
            ? htmlentities($ret["problem_sort_type"], ENT_QUOTES, "UTF-8") : null
    );
    updateOption(
        $pearDB,
        "problem_sort_order",
        isset($ret["problem_sort_order"]) && $ret["problem_sort_order"] != null
            ? htmlentities($ret["problem_sort_order"], ENT_QUOTES, "UTF-8") : null
    );
    updateOption(
        $pearDB,
        'proxy_url',
        isset($ret["proxy_url"]) && $ret["proxy_url"] != null
            ? htmlentities($ret["proxy_url"], ENT_QUOTES, "UTF-8") : null
    );
    updateOption(
        $pearDB,
        'proxy_port',
        isset($ret["proxy_port"]) && $ret["proxy_port"] != null
            ? htmlentities($ret["proxy_port"], ENT_QUOTES, "UTF-8") : null
    );
    updateOption(
        $pearDB,
        'proxy_user',
        isset($ret["proxy_user"]) && $ret["proxy_user"] != null
            ? htmlentities($ret["proxy_user"], ENT_QUOTES, "UTF-8") : null
    );
    if (isset($ret["proxy_password"]) && $ret['proxy_password'] != CentreonAuth::PWS_OCCULTATION) {
        updateOption(
            $pearDB,
            'proxy_password',
            $ret["proxy_password"] != null ? htmlentities($ret["proxy_password"], ENT_QUOTES, "UTF-8") : null
        );
    }
    updateOption(
        $pearDB,
        'display_downtime_chart',
        isset($ret["display_downtime_chart"]["yes"]) && $ret["display_downtime_chart"]["yes"] != null
            ? htmlentities($ret["display_downtime_chart"]["yes"], ENT_QUOTES, "UTF-8") : "0"
    );
    updateOption(
        $pearDB,
        'display_comment_chart',
        isset($ret["display_comment_chart"]["yes"]) && $ret["display_comment_chart"]["yes"] != null
            ? htmlentities($ret["display_comment_chart"]["yes"], ENT_QUOTES, "UTF-8") : "0"
    );
    updateOption(
        $pearDB,
        "enable_autologin",
        isset($ret["enable_autologin"]["yes"]) && $ret["enable_autologin"]["yes"] != null
            ? htmlentities($ret["enable_autologin"]["yes"], ENT_QUOTES, "UTF-8") : "0"
    );
    updateOption(
        $pearDB,
        "display_autologin_shortcut",
        isset($ret["display_autologin_shortcut"]["yes"]) && $ret["display_autologin_shortcut"]["yes"] != null
            ? htmlentities($ret["display_autologin_shortcut"]["yes"], ENT_QUOTES, "UTF-8") : "0"
    );
    updateOption(
        $pearDB,
        "sso_enable",
        isset($ret["sso_enable"]["yes"]) && $ret["sso_enable"]["yes"] != null ? 1 : 0
    );
    updateOption(
        $pearDB,
        "sso_mode",
        isset($ret["sso_mode"]["sso_mode"]) && $ret["sso_mode"]["sso_mode"] != null
            ? $pearDB->escape($ret["sso_mode"]["sso_mode"]) : 1
    );
    updateOption(
        $pearDB,
        "sso_trusted_clients",
        isset($ret["sso_trusted_clients"]) && $ret["sso_trusted_clients"] != null
            ? $pearDB->escape($ret["sso_trusted_clients"]) : ""
    );
    updateOption(
        $pearDB,
        "sso_blacklist_clients",
        isset($ret["sso_blacklist_clients"]) && $ret["sso_blacklist_clients"] != null
            ? $pearDB->escape($ret["sso_blacklist_clients"]) : ""
    );
    updateOption(
        $pearDB,
        "sso_header_username",
        isset($ret["sso_header_username"]) && $ret["sso_header_username"] != null
            ? $pearDB->escape($ret["sso_header_username"]) : ""
    );
    updateOption(
        $pearDB,
        "sso_username_pattern",
        isset($ret["sso_username_pattern"]) && $ret["sso_username_pattern"] != null
            ? $pearDB->escape($ret["sso_username_pattern"]) : ""
    );
    updateOption(
        $pearDB,
        "sso_username_replace",
        isset($ret["sso_username_replace"]) && $ret["sso_username_replace"] != null
            ? $pearDB->escape($ret["sso_username_replace"]) : ""
    );
    updateOption(
        $pearDB,
        "centreon_support_email",
        isset($ret["centreon_support_email"]) && $ret["centreon_support_email"] != null
            ? htmlentities($ret["centreon_support_email"], ENT_QUOTES, "UTF-8") : null
    );
    updateOption(
        $pearDB,
        "send_statistics",
        isset($ret["send_statistics"]["yes"]) && $ret["send_statistics"]["yes"] != null
            ? htmlentities($ret["send_statistics"]["yes"], ENT_QUOTES, "UTF-8") : "0"
    );

    $centreon->initOptGen($pearDB);
}

function updateRRDToolConfigData($gopt_id = null)
{
    global $form, $pearDB, $centreon;

    $ret = array();
    $ret = $form->getSubmitValues();

    updateOption(
        $pearDB,
        "rrdtool_path_bin",
        isset($ret["rrdtool_path_bin"]) && $ret["rrdtool_path_bin"] != null
            ? htmlentities($ret["rrdtool_path_bin"], ENT_QUOTES, "UTF-8") : "NULL"
    );
    updateOption(
        $pearDB,
        "rrdtool_version",
        isset($ret["rrdtool_version"]) && $ret["rrdtool_version"] != null
            ? htmlentities($ret["rrdtool_version"], ENT_QUOTES, "UTF-8") : "NULL"
    );
    updateOption(
        $pearDB,
        "rrdcached_enable",
        isset($ret['rrdcached_enable']['rrdcached_enable']) ? $ret['rrdcached_enable']['rrdcached_enable'] : '0'
    );
    updateOption(
        $pearDB,
        "rrdcached_port",
        isset($ret['rrdcached_port']) ? $ret['rrdcached_port'] : ''
    );
    updateOption(
        $pearDB,
        "rrdcached_unix_path",
        isset($ret['rrdcached_unix_path']) ? htmlentities($ret['rrdcached_unix_path'], ENT_QUOTES, "UTF-8") : ''
    );

    $centreon->initOptGen($pearDB);
}

function updatePartitioningConfigData($db, $form, $centreon)
{
    $ret = $form->getSubmitValues();

    foreach ($ret as $key => $value) {
        if (preg_match('/^partitioning_/', $key)) {
            updateOption($db, $key, $value);
        }
    }

    $centreon->initOptGen($db);
}

function updateODSConfigData()
{
    global $form, $pearDBO, $pearDB;

    $ret = array();
    $ret = $form->getSubmitValues();
    if (!isset($ret["audit_log_option"])) {
        $ret["audit_log_option"] = '0';
    }
    if (!isset($ret["len_storage_rrd"])) {
        $ret["len_storage_rrd"] = 1;
    }
    if (!isset($ret["len_storage_mysql"])) {
        $ret["len_storage_mysql"] = 1;
    }
    if (!isset($ret["autodelete_rrd_db"])) {
        $ret["autodelete_rrd_db"] = 0;
    }
    if (!isset($ret["purge_interval"]) || $ret["purge_interval"] <= 20) {
        $ret["purge_interval"] = 20;
    }
    if (!isset($ret["archive_log"])) {
        $ret["archive_log"] = "0";
    }
    if (!$ret["purge_interval"]) {
        $ret["purge_interval"] = 60;
    }
    if ($ret["RRDdatabase_path"][strlen($ret["RRDdatabase_path"]) - 1] != "/") {
        $ret["RRDdatabase_path"] .= "/";
    }

    if (!isset($ret["len_storage_downtimes"])) {
        $ret["len_storage_downtimes"] = 0;
    }
    if (!isset($ret["len_storage_comments"])) {
        $ret["len_storage_comments"] = 0;
    }
    if (!isset($ret["audit_log_retention"])) {
        $ret["audit_log_retention"] = 0;
    }

    $rq = "UPDATE `config` SET `RRDdatabase_path` = '" . $ret["RRDdatabase_path"] . "',
        `RRDdatabase_status_path` = '" . $ret["RRDdatabase_status_path"] . "',
        `RRDdatabase_nagios_stats_path` = '" . $ret["RRDdatabase_nagios_stats_path"] . "',
        `len_storage_rrd` = '" . $ret["len_storage_rrd"] . "',
        `len_storage_mysql` = '" . $ret["len_storage_mysql"] . "',
        `autodelete_rrd_db` = '" . $ret["autodelete_rrd_db"] . "',
        `purge_interval` = '" . $ret["purge_interval"] . "',
        `archive_log` = '" . $ret["archive_log"] . "',
        `archive_retention` = '" . $ret["archive_retention"] . "',
        `reporting_retention` = '" . $ret["reporting_retention"] . "',
        `audit_log_option` = '" . $ret["audit_log_option"] . "',
        `storage_type` = " . (isset($ret["storage_type"]) ? $ret["storage_type"] : 'NULL') . ",
        `len_storage_downtimes` = '" . $ret["len_storage_downtimes"] . "',
        `audit_log_retention` = '" . $ret["audit_log_retention"] . "',
        `len_storage_comments` = '" . $ret["len_storage_comments"] . "' "
        . " WHERE `id` = 1 LIMIT 1 ;";
    $DBRESULT = $pearDBO->query($rq);

    updateOption(
        $pearDB,
        "centstorage",
        isset($ret["enable_centstorage"]) && $ret["enable_centstorage"] != null
            ? htmlentities($ret["enable_centstorage"], ENT_QUOTES, "UTF-8") : "0"
    );
    updateOption($pearDB, "centstorage_auto_drop", isset($ret['centstorage_auto_drop']) ? '1' : '0');
    updateOption(
        $pearDB,
        "centstorage_drop_file",
        isset($ret['centstorage_drop_file']) ? $pearDB->escape($ret['centstorage_drop_file']) : ''
    );
}

function updateCASConfigData($gopt_id = null)
{
    global $form, $pearDB, $centreon;

    $ret = array();
    $ret = $form->getSubmitValues();

    updateOption(
        $pearDB,
        "auth_cas_enable",
        isset($ret["auth_cas_enable"]["auth_cas_enable"]) && $ret["auth_cas_enable"]["auth_cas_enable"] != null
            ? $ret["auth_cas_enable"]["auth_cas_enable"] : "NULL"
    );
    updateOption(
        $pearDB,
        "cas_server",
        isset($ret["cas_server"]) && $ret["cas_server"] != null ? $ret["cas_server"] : "NULL"
    );
    updateOption($pearDB, "cas_port", isset($ret["cas_port"]) && $ret["cas_port"] != null ? $ret["cas_port"] : "NULL");
    updateOption($pearDB, "cas_url", isset($ret["cas_url"]) && $ret["cas_url"] != null ? $ret["cas_url"] : "NULL");
    updateOption(
        $pearDB,
        "cas_version",
        isset($ret["cas_version"]) && $ret["cas_version"] != null ? $ret["cas_version"] : "NULL"
    );

    $centreon->initOptGen($pearDB);
}

function updateBackupConfigData($db, $form, $centreon)
{
    $ret = $form->getSubmitValues();

    $radiobutton = array(
        'backup_enabled',
        'backup_database_type',
        'backup_export_scp_enabled'
    );
    foreach ($radiobutton as $value) {
        $ret[$value] = isset($ret[$value]) && isset($ret[$value][$value]) && $ret[$value][$value] ? 1 : 0;
    }

    $checkbox = array(
        'backup_configuration_files',
        'backup_database_centreon',
        'backup_database_centreon_storage'
    );
    foreach ($checkbox as $value) {
        $ret[$value] = isset($ret[$value]) && $ret[$value] ? 1 : 0;
    }

    $checkboxGroup = array(
        'backup_database_full',
        'backup_database_partial'
    );
    foreach ($checkboxGroup as $value) {
        if (isset($ret[$value]) && count($ret[$value])) {
            $valueKeys = array_keys($ret[$value]);
            $ret[$value] = implode(',', $valueKeys);
        } else {
            $ret[$value] = '';
        }
    }

    foreach ($ret as $key => $value) {
        if (preg_match('/^backup_/', $key)) {
            updateOption($db, $key, $value);
        }
    }

    $centreon->initOptGen($db);
}

function updateKnowledgeBaseData($db, $form, $centreon)
{
    $ret = $form->getSubmitValues();

    if (!isset($ret['kb_wiki_certificate']) || !filter_var($ret["kb_wiki_certificate"], FILTER_VALIDATE_INT)) {
        $ret['kb_wiki_certificate'] = 0;
    }

    if (isset($ret["kb_wiki_password"]) && $ret["kb_wiki_password"] === CentreonAuth::PWS_OCCULTATION) {
        unset($ret["kb_wiki_password"]);
    }

    if (isset($ret["kb_wiki_url"]) && !filter_var($ret["kb_wiki_url"], FILTER_VALIDATE_URL)) {
        unset($ret["kb_wiki_url"]);
    }

    foreach ($ret as $key => $value) {
        if (preg_match('/^kb_/', $key)) {
            updateOption($db, $key, $value);
        }
    }

    $centreon->initOptGen($db);
}

/**
 * Used to update Central's credentials on a remote server
 *
 * @param object $db : database connection
 * @param object $form : form data
 * @param \Security\Encryption $centreonEncryption : Encryption object
 */
function updateRemoteAccessCredentials($db, $form, $centreonEncryption): void
{
    $ret = $form->getSubmitValues();
    $passApi = $ret['apiCredentials'];

    //clean useless values
    unset($ret['submitC']);
    unset($ret['o']);
    unset($ret['centreon_token']);
    unset($ret['apiCredentials']);

    //convert values
    $ret['apiPeerValidation'] = (int) $ret['apiPeerValidation'] === 1 ? 'no' : 'yes';

    //update information
    foreach ($ret as $key => $value) {
        updateInformations($db, $key, $value);
    }

    if (CentreonAuth::PWS_OCCULTATION !== $passApi) {
        try {
            updateInformations(
                $db,
                'apiCredentials',
                $centreonEncryption->crypt($passApi)
            );
        } catch (Exception $e) {
            $errorMsg = _('The password cannot be crypted. Please re-submit the form');
            echo "<div class='msg' align='center'>" . $errorMsg . "</div>";
        }
    }
}
