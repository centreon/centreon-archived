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

if (!$centreon->user->admin && isset($nagios_id)
    && count($allowedMainConf) && !isset($allowedMainConf[$nagios_id])
) {
    $msg = new CentreonMsg();
    $msg->setImage("./img/icons/warning.png");
    $msg->setTextStyle("bold");
    $msg->setText(_('You are not allowed to access this object configuration'));
    return null;
}

require_once _CENTREON_PATH_ . "www/class/centreon-config/centreonMainCfg.class.php";
$objMain = new CentreonMainCfg();

/*
 * Database retrieve information for Nagios
 */
$nagios = array();
$nagios_d = array();

$defaultEventBrokerOptions['event_broker_options'][-1] = 1;

if (($o == "c" || $o == "w") && $nagios_id) {
    $dbResult = $pearDB->query("SELECT * FROM cfg_nagios WHERE nagios_id = '" . $nagios_id . "' LIMIT 1");
    // Set base value
    $nagios = array_map("myDecode", $dbResult->fetch());
    $dbResult->closeCursor();

    $tmp = explode(',', $nagios["debug_level_opt"]);
    foreach ($tmp as $key => $value) {
        $nagios_d["nagios_debug_level"][$value] = 1;
    }
    $defaultEventBrokerOptions['event_broker_options'] = $objMain->explodeEventBrokerOptions(
        (int)$nagios['event_broker_options']
    );
    unset($nagios['event_broker_options']);
}

/*
 * Preset values of broker directives
 */
$mainCfg = new CentreonConfigEngine($pearDB);
$cdata = CentreonData::getInstance();
if ($o != "a") {
    $dirArray = $mainCfg->getBrokerDirectives(isset($nagios_id) ? $nagios_id : null);
} else {
    $dirArray[0]['in_broker_#index#'] = "/usr/lib64/centreon-engine/externalcmd.so";
    $dirArray[1]['in_broker_#index#'] = "/usr/lib64/nagios/cbmod.so /etc/centreon-broker/poller-module.json";
}
$cdata->addJsData(
    'clone-values-broker',
    htmlspecialchars(
        json_encode($dirArray),
        ENT_QUOTES
    )
);

$cdata->addJsData(
    'clone-count-broker',
    count($dirArray)
);

/* Set the values for list of whitelist macros */
$macrosWhitelist = [];
if ($o != 'a') {
    $macrosWhitelist = array_map(
        function ($macro) {
            return [
                'macros_filter_#index#' => $macro
            ];
        },
        explode(',', $nagios['macros_filter'])
    );
    unset($nagios['macros_filter']);
}
$cdata->addJsData(
    'clone-values-macros_filter',
    htmlspecialchars(
        json_encode($macrosWhitelist),
        ENT_QUOTES
    )
);
$cdata->addJsData(
    'clone-count-macros_filter',
    count($macrosWhitelist)
);

/*
 * Database retrieve information for different elements list we need on the page
 *
 * Check commands comes from DB -> Store in $checkCmds Array
 *
 */
$checkCmds = array();
$dbResult = $pearDB->query("SELECT command_id, command_name FROM command ORDER BY command_name");
$checkCmds = array(null => null);
while ($checkCmd = $dbResult->fetch()) {
    $checkCmds[$checkCmd["command_id"]] = $checkCmd["command_name"];
}
$dbResult->closeCursor();

/*
 * Get all nagios servers
 */
$nagios_server = array(null => "");
$result = $oreon->user->access->getPollerAclConf(
    array(
        'fields' => array('name', 'id'),
        'keys' => array('id')
    )
);
foreach ($result as $ns) {
    $nagios_server[$ns["id"]] = $ns["name"];
}

/*
 * Get all broker module for this nagios config
 */
$nBk = 0;
$aBk = array();
$dbResult = $pearDB->query(
    "SELECT bk_mod_id, broker_module FROM cfg_nagios_broker_module WHERE cfg_nagios_id = '"
    . $nagios_id . "'"
);
while ($lineBk = $dbResult->fetch()) {
    $aBk[$nBk] = $lineBk;
    $nBk++;
}
$dbResult->closeCursor();
unset($lineBk);

$attrsText = array("size" => "30");
$attrsText2 = array("size" => "50");
$attrsText3 = array("size" => "10");
$attrsTextarea = array("rows" => "5", "cols" => "40");

/*
 * Form begin
 */
$form = new HTML_QuickFormCustom('Form', 'post', "?p=" . $p);
if ($o == "a") {
    $form->addElement('header', 'title', _("Add a Monitoring Engine Configuration File"));
} elseif ($o == "c") {
    $form->addElement('header', 'title', _("Modify a Monitoring Engine Configuration File"));
} elseif ($o == "w") {
    $form->addElement('header', 'title', _("View a Monitoring Engine Configuration File"));
}

/* *************
 * Nagios Configuration basic information
 */
$form->addElement('header', 'information', _("Information"));
$form->addElement('text', 'nagios_name', _("Configuration Name"), $attrsText);
$form->addElement('textarea', 'nagios_comment', _("Comments"), $attrsTextarea);
$nagTab = array();
$nagTab[] = $form->createElement('radio', 'nagios_activate', null, _("Enabled"), '1');
$nagTab[] = $form->createElement('radio', 'nagios_activate', null, _("Disabled"), '0');
$form->addGroup($nagTab, 'nagios_activate', _("Status"), '&nbsp;');

$form->addElement('select', 'nagios_server_id', _("Linked poller"), $nagios_server);

$attrTimezones = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => './include/common/webServices/rest/internal.php?' .
        'object=centreon_configuration_timezone&action=list',
    'multiple' => false,
    'linkedObject' => 'centreonGMT'
);
$form->addElement('select2', 'use_timezone', _("Timezone / Location"), array(), $attrTimezones);

/* *************
 * Part 1
 */
$form->addElement('text', 'status_file', _("Status file"), $attrsText2);
$form->addElement('text', 'status_update_interval', _("Status File Update Interval"), $attrsText3);
$form->addElement('text', 'log_file', _("Log file"), $attrsText2);
$form->addElement('text', 'cfg_dir', _("Object Configuration Directory"), $attrsText2);
$form->addElement('text', 'cfg_file', _("Object Configuration File"), $attrsText2);
$form->addElement('text', 'temp_file', _("Temp File"), $attrsText2);

/* *****************************************************
 * User / Groups
 */
$form->addElement('text', 'nagios_user', _("Monitoring system User"), $attrsText);
$form->addElement('text', 'nagios_group', _("Monitoring system Group"), $attrsText);

/* *****************************************************
 * Enable / Disable functionalities
 */
$nagTab = array();
$nagTab[] = $form->createElement('radio', 'postpone_notification_to_timeperiod', null, _("Yes"), '1');
$nagTab[] = $form->createElement('radio', 'postpone_notification_to_timeperiod', null, _("No"), '0');
$form->addGroup($nagTab, 'postpone_notification_to_timeperiod', _("Postpone Notification Option"), '&nbsp;');

$nagTab = array();
$nagTab[] = $form->createElement('radio', 'enable_notifications', null, _("Yes"), '1');
$nagTab[] = $form->createElement('radio', 'enable_notifications', null, _("No"), '0');
$nagTab[] = $form->createElement('radio', 'enable_notifications', null, _("Default"), '2');
$form->addGroup($nagTab, 'enable_notifications', _("Notification Option"), '&nbsp;');

$nagTab = array();
$nagTab[] = $form->createElement('radio', 'execute_service_checks', null, _("Yes"), '1');
$nagTab[] = $form->createElement('radio', 'execute_service_checks', null, _("No"), '0');
$nagTab[] = $form->createElement('radio', 'execute_service_checks', null, _("Default"), '2');
$form->addGroup($nagTab, 'execute_service_checks', _("Service Check Execution Option"), '&nbsp;');

$nagTab = array();
$nagTab[] = $form->createElement('radio', 'accept_passive_service_checks', null, _("Yes"), '1');
$nagTab[] = $form->createElement('radio', 'accept_passive_service_checks', null, _("No"), '0');
$nagTab[] = $form->createElement('radio', 'accept_passive_service_checks', null, _("Default"), '2');
$form->addGroup($nagTab, 'accept_passive_service_checks', _("Passive Service Check Acceptance Option"), '&nbsp;');

$nagTab = array();
$nagTab[] = $form->createElement('radio', 'execute_host_checks', null, _("Yes"), '1');
$nagTab[] = $form->createElement('radio', 'execute_host_checks', null, _("No"), '0');
$nagTab[] = $form->createElement('radio', 'execute_host_checks', null, _("Default"), '2');
$form->addGroup($nagTab, 'execute_host_checks', _("Host Check Execution Option"), '&nbsp;');

$nagTab = array();
$nagTab[] = $form->createElement('radio', 'accept_passive_host_checks', null, _("Yes"), '1');
$nagTab[] = $form->createElement('radio', 'accept_passive_host_checks', null, _("No"), '0');
$nagTab[] = $form->createElement('radio', 'accept_passive_host_checks', null, _("Default"), '2');
$form->addGroup($nagTab, 'accept_passive_host_checks', _("Passive Host Check Acceptance Option"), '&nbsp;');

$nagTab = array();
$nagTab[] = $form->createElement('radio', 'enable_event_handlers', null, _("Yes"), '1');
$nagTab[] = $form->createElement('radio', 'enable_event_handlers', null, _("No"), '0');
$nagTab[] = $form->createElement('radio', 'enable_event_handlers', null, _("Default"), '2');
$form->addGroup($nagTab, 'enable_event_handlers', _("Event Handler Option"), '&nbsp;');

/* *****************************************************
 * Log Rotation Method
 */
$nagTab = array();
$nagTab[] = $form->createElement('radio', 'log_rotation_method', null, _("None"), 'n');
$nagTab[] = $form->createElement('radio', 'log_rotation_method', null, _("Hourly"), 'h');
$nagTab[] = $form->createElement('radio', 'log_rotation_method', null, _("Daily"), 'd');
$nagTab[] = $form->createElement('radio', 'log_rotation_method', null, _("Weekly"), 'w');
$nagTab[] = $form->createElement('radio', 'log_rotation_method', null, _("Monthly"), 'm');
$form->addGroup($nagTab, 'log_rotation_method', _("Log Rotation Method"), '&nbsp;&nbsp;');
$form->addElement('text', 'log_archive_path', _("Log Archive Path"), $attrsText2);

/* *****************************************************
 * External Commands
 */
$nagTab = array();
$nagTab[] = $form->createElement('radio', 'check_external_commands', null, _("Yes"), '1');
$nagTab[] = $form->createElement('radio', 'check_external_commands', null, _("No"), '0');
$nagTab[] = $form->createElement('radio', 'check_external_commands', null, _("Default"), '2');
$form->addGroup($nagTab, 'check_external_commands', _("External Command Check Option"), '&nbsp;');

$form->addElement('text', 'command_check_interval', _("External Command Check Interval"), $attrsText3);
$form->addElement('text', 'external_command_buffer_slots', _("External Command Buffer Slots"), $attrsText3);
$form->addElement('text', 'command_file', _("External Command File"), $attrsText2);

/* *****************************************************
 * Lock files
 */
$form->addElement('text', 'lock_file', _("Lock File"), $attrsText2);

/* *****************************************************
 * Retention
 */
$nagTab = array();
$nagTab[] = $form->createElement('radio', 'retain_state_information', null, _("Yes"), '1');
$nagTab[] = $form->createElement('radio', 'retain_state_information', null, _("No"), '0');
$nagTab[] = $form->createElement('radio', 'retain_state_information', null, _("Default"), '2');
$form->addGroup($nagTab, 'retain_state_information', _("State Retention Option"), '&nbsp;');
$form->addElement('text', 'state_retention_file', _("State Retention File"), $attrsText2);
$form->addElement('text', 'retention_update_interval', _("Automatic State Retention Update Interval"), $attrsText3);

$nagTab = array();
$nagTab[] = $form->createElement('radio', 'use_retained_program_state', null, _("Yes"), '1');
$nagTab[] = $form->createElement('radio', 'use_retained_program_state', null, _("No"), '0');
$nagTab[] = $form->createElement('radio', 'use_retained_program_state', null, _("Default"), '2');
$form->addGroup($nagTab, 'use_retained_program_state', _("Use Retained Program State Option"), '&nbsp;');

$nagTab = array();
$nagTab[] = $form->createElement('radio', 'use_retained_scheduling_info', null, _("Yes"), '1');
$nagTab[] = $form->createElement('radio', 'use_retained_scheduling_info', null, _("No"), '0');
$nagTab[] = $form->createElement('radio', 'use_retained_scheduling_info', null, _("Default"), '2');
$form->addGroup(
    $nagTab,
    'use_retained_scheduling_info',
    _("Use Retained Scheduling Info Option"),
    '&nbsp;'
);

/**
 * Retention masks
 */
$form->addElement(
    'text',
    'retained_contact_host_attribute_mask',
    _("Retained Contact Host Attribute Mask"),
    $attrsText3
);
$form->addElement(
    'text',
    'retained_contact_service_attribute_mask',
    _("Retained Contact Service Attribute Mask"),
    $attrsText3
);
$form->addElement(
    'text',
    'retained_process_host_attribute_mask',
    _("Retained Process Host Attribute Mask"),
    $attrsText3
);
$form->addElement(
    'text',
    'retained_process_service_attribute_mask',
    _("Retained Process Service Attribute Mask"),
    $attrsText3
);
$form->addElement(
    'text',
    'retained_host_attribute_mask',
    _("Retained Host Attribute Mask"),
    $attrsText3
);
$form->addElement(
    'text',
    'retained_service_attribute_mask',
    _("Retained Service Attribute Mask"),
    $attrsText3
);

/* *****************************************************
 * logging options
 */
$nagTab = array();
$nagTab[] = $form->createElement('radio', 'use_syslog', null, _("Yes"), '1');
$nagTab[] = $form->createElement('radio', 'use_syslog', null, _("No"), '0');
$nagTab[] = $form->createElement('radio', 'use_syslog', null, _("Default"), '2');
$form->addGroup($nagTab, 'use_syslog', _("Syslog Logging Option"), '&nbsp;');

$nagTab = array();
$nagTab[] = $form->createElement('radio', 'log_notifications', null, _("Yes"), '1');
$nagTab[] = $form->createElement('radio', 'log_notifications', null, _("No"), '0');
$nagTab[] = $form->createElement('radio', 'log_notifications', null, _("Default"), '2');
$form->addGroup($nagTab, 'log_notifications', _("Notification Logging Option"), '&nbsp;');

$nagTab = array();
$nagTab[] = $form->createElement('radio', 'log_service_retries', null, _("Yes"), '1');
$nagTab[] = $form->createElement('radio', 'log_service_retries', null, _("No"), '0');
$nagTab[] = $form->createElement('radio', 'log_service_retries', null, _("Default"), '2');
$form->addGroup($nagTab, 'log_service_retries', _("Service Check Retry Logging Option"), '&nbsp;');

$nagTab = array();
$nagTab[] = $form->createElement('radio', 'log_host_retries', null, _("Yes"), '1');
$nagTab[] = $form->createElement('radio', 'log_host_retries', null, _("No"), '0');
$nagTab[] = $form->createElement('radio', 'log_host_retries', null, _("Default"), '2');
$form->addGroup($nagTab, 'log_host_retries', _("Host Retry Logging Option"), '&nbsp;');

$nagTab = array();
$nagTab[] = $form->createElement('radio', 'log_event_handlers', null, _("Yes"), '1');
$nagTab[] = $form->createElement('radio', 'log_event_handlers', null, _("No"), '0');
$nagTab[] = $form->createElement('radio', 'log_event_handlers', null, _("Default"), '2');
$form->addGroup($nagTab, 'log_event_handlers', _("Event Handler Logging Option"), '&nbsp;');

$nagTab = array();
$nagTab[] = $form->createElement('radio', 'log_external_commands', null, _("Yes"), '1');
$nagTab[] = $form->createElement('radio', 'log_external_commands', null, _("No"), '0');
$nagTab[] = $form->createElement('radio', 'log_external_commands', null, _("Default"), '2');
$form->addGroup($nagTab, 'log_external_commands', _("External Command Logging Option"), '&nbsp;');

$nagTab = array();
$nagTab[] = $form->createElement('radio', 'log_passive_checks', null, _("Yes"), '1');
$nagTab[] = $form->createElement('radio', 'log_passive_checks', null, _("No"), '0');
$nagTab[] = $form->createElement('radio', 'log_passive_checks', null, _("Default"), '2');
$form->addGroup($nagTab, 'log_passive_checks', _("Passive Check Logging Option"), '&nbsp;');

$nagTab = array();
$nagTab[] = $form->createElement('radio', 'log_pid', null, _("Yes"), '1');
$nagTab[] = $form->createElement('radio', 'log_pid', null, _("No"), '0');
$form->addGroup($nagTab, 'log_pid', _("Enable logging pid information"), '&nbsp;');

/* *****************************************************
 * Event handler
 */
$form->addElement('select', 'global_host_event_handler', _("Global Host Event Handler"), $checkCmds);
$form->addElement('select', 'global_service_event_handler', _("Global Service Event Handler"), $checkCmds);

/* *****************************************************
 * General Options
 */
$form->addElement('text', 'sleep_time', _("Inter-Check Sleep Time"), $attrsText3);
$form->addElement('text', 'max_concurrent_checks', _("Maximum Concurrent Service Checks"), $attrsText3);
$form->addElement('text', 'max_host_check_spread', _("Maximum Host Check Spread"), $attrsText3);
$form->addElement('text', 'max_service_check_spread', _("Maximum Service Check Spread"), $attrsText3);
$form->addElement('text', 'service_interleave_factor', _("Service Interleave Factor"), $attrsText3);

$form->addElement('text', 'host_inter_check_delay_method', _("Host Inter-Check Delay Method"), $attrsText3);
$form->addElement('text', 'service_inter_check_delay_method', _("Service Inter-Check Delay Method"), $attrsText3);

$form->addElement('text', 'check_result_reaper_frequency', _("Check Result Reaper Frequency"), $attrsText3);
$form->addElement('text', 'max_check_result_reaper_time', _("Maximum Check Result Reaper Time"), $attrsText3);

$nagTab = array();
$nagTab[] = $form->createElement('radio', 'translate_passive_host_checks', null, _("Yes"), '1');
$nagTab[] = $form->createElement('radio', 'translate_passive_host_checks', null, _("No"), '0');
$nagTab[] = $form->createElement('radio', 'translate_passive_host_checks', null, _("Default"), '2');
$form->addGroup($nagTab, 'translate_passive_host_checks', _("Translate Passive Host Checks Option"), '&nbsp;');

$nagTab = array();
$nagTab[] = $form->createElement('radio', 'passive_host_checks_are_soft', null, _("Yes"), '1');
$nagTab[] = $form->createElement('radio', 'passive_host_checks_are_soft', null, _("No"), '0');
$nagTab[] = $form->createElement('radio', 'passive_host_checks_are_soft', null, _("Default"), '2');
$form->addGroup($nagTab, 'passive_host_checks_are_soft', _("Passive Host Checks Are SOFT Option"), '&nbsp;');

/* *****************************************************
 * Auto Rescheduling Option
 */
$nagTab = array();
$nagTab[] = $form->createElement('radio', 'auto_reschedule_checks', null, _("Yes"), '1');
$nagTab[] = $form->createElement('radio', 'auto_reschedule_checks', null, _("No"), '0');
$nagTab[] = $form->createElement('radio', 'auto_reschedule_checks', null, _("Default"), '2');
$form->addGroup($nagTab, 'auto_reschedule_checks', _("Auto-Rescheduling Option"), '&nbsp;');

$form->addElement('text', 'auto_rescheduling_interval', _("Auto-Rescheduling Interval"), $attrsText3);
$form->addElement('text', 'auto_rescheduling_window', _("Auto-Rescheduling Window"), $attrsText3);

/* *****************************************************
 * Aggressive host checking
 */
$nagTab = array();
$nagTab[] = $form->createElement('radio', 'use_aggressive_host_checking', null, _("No"), '0');
$form->addGroup($nagTab, 'use_aggressive_host_checking', _("Aggressive Host Checks"), '&nbsp;');

/* *****************************************************
 * Flapping management.
 */
$nagTab = array();
$nagTab[] = $form->createElement('radio', 'enable_flap_detection', null, _("Yes"), '1');
$nagTab[] = $form->createElement('radio', 'enable_flap_detection', null, _("No"), '0');
$nagTab[] = $form->createElement('radio', 'enable_flap_detection', null, _("Default"), '2');
$form->addGroup($nagTab, 'enable_flap_detection', _("Flap Detection Option"), '&nbsp;');

$form->addElement('text', 'low_service_flap_threshold', _("Low Service Flap Threshold"), $attrsText3);
$form->addElement('text', 'high_service_flap_threshold', _("High Service Flap Threshold"), $attrsText3);
$form->addElement('text', 'low_host_flap_threshold', _("Low Host Flap Threshold"), $attrsText3);
$form->addElement('text', 'high_host_flap_threshold', _("High Host Flap Threshold"), $attrsText3);

/* *****************************************************
 * SOFT dependencies options
 */
$nagTab = array();
$nagTab[] = $form->createElement('radio', 'soft_state_dependencies', null, _("Yes"), '1');
$nagTab[] = $form->createElement('radio', 'soft_state_dependencies', null, _("No"), '0');
$nagTab[] = $form->createElement('radio', 'soft_state_dependencies', null, _("Default"), '2');
$form->addGroup($nagTab, 'soft_state_dependencies', _("Soft Service Dependencies Option"), '&nbsp;');

/* *****************************************************
 * Timeout.
 */
$form->addElement('text', 'service_check_timeout', _("Service Check Timeout"), $attrsText3);
$form->addElement('text', 'host_check_timeout', _("Host Check Timeout"), $attrsText3);
$form->addElement('text', 'event_handler_timeout', _("Event Handler Timeout"), $attrsText3);
$form->addElement('text', 'notification_timeout', _("Notification Timeout"), $attrsText3);
$form->addElement('text', 'ocsp_timeout', _("Obsessive Compulsive Service Processor Timeout"), $attrsText3);
$form->addElement('text', 'ochp_timeout', _("Obsessive Compulsive Host Processor Timeout"), $attrsText3);
$form->addElement('text', 'perfdata_timeout', _("Performance Data Processor Command Timeout"), $attrsText3);

/* *****************************************************
 * OCSP / OCHP
 */
$nagTab = array();
$nagTab[] = $form->createElement('radio', 'obsess_over_services', null, _("Yes"), '1');
$nagTab[] = $form->createElement('radio', 'obsess_over_services', null, _("No"), '0');
$nagTab[] = $form->createElement('radio', 'obsess_over_services', null, _("Default"), '2');
$form->addGroup($nagTab, 'obsess_over_services', _("Obsess Over Services Option"), '&nbsp;');
$form->addElement('select', 'ocsp_command', _("Obsessive Compulsive Service Processor Command"), $checkCmds);

$nagTab = array();
$nagTab[] = $form->createElement('radio', 'obsess_over_hosts', null, _("Yes"), '1');
$nagTab[] = $form->createElement('radio', 'obsess_over_hosts', null, _("No"), '0');
$nagTab[] = $form->createElement('radio', 'obsess_over_hosts', null, _("Default"), '2');
$form->addGroup($nagTab, 'obsess_over_hosts', _("Obsess Over Hosts Option"), '&nbsp;');
$form->addElement('select', 'ochp_command', _("Obsessive Compulsive Host Processor Command"), $checkCmds);

/* *****************************************************
 * Perfdata configuration parameters
 */
$nagTab = array();
$nagTab[] = $form->createElement('radio', 'process_performance_data', null, _("Yes"), '1');
$nagTab[] = $form->createElement('radio', 'process_performance_data', null, _("No"), '0');
$nagTab[] = $form->createElement('radio', 'process_performance_data', null, _("Default"), '2');
$form->addGroup($nagTab, 'process_performance_data', _("Performance Data Processing Option"), '&nbsp;');

$form->addElement('select', 'host_perfdata_command', _("Host Performance Data Processing Command"), $checkCmds);
$form->addElement('select', 'service_perfdata_command', _("Service Performance Data Processing Command"), $checkCmds);

$form->addElement('text', 'host_perfdata_file', _("Host Performance Data File"), $attrsText2);
$form->addElement('text', 'service_perfdata_file', _("Service Performance Data File"), $attrsText2);

$form->addElement(
    'textarea',
    'host_perfdata_file_template',
    _("Host Performance Data File Template"),
    $attrsTextarea
);
$form->addElement(
    'textarea',
    'service_perfdata_file_template',
    _("Service Performance Data File Template"),
    $attrsTextarea
);

$nagTab = array();
$nagTab[] = $form->createElement('radio', 'host_perfdata_file_mode', null, _("Append"), 'a');
$nagTab[] = $form->createElement('radio', 'host_perfdata_file_mode', null, _("Write"), 'w');
$nagTab[] = $form->createElement('radio', 'host_perfdata_file_mode', null, _("Default"), '2');
$form->addGroup($nagTab, 'host_perfdata_file_mode', _("Host Performance Data File Mode"), '&nbsp;');

$nagTab = array();
$nagTab[] = $form->createElement('radio', 'service_perfdata_file_mode', null, _("Append"), 'a');
$nagTab[] = $form->createElement('radio', 'service_perfdata_file_mode', null, _("Write"), 'w');
$nagTab[] = $form->createElement('radio', 'service_perfdata_file_mode', null, _("Default"), '2');
$form->addGroup($nagTab, 'service_perfdata_file_mode', _("Service Performance Data File Mode"), '&nbsp;');

$form->addElement(
    'text',
    'host_perfdata_file_processing_interval',
    _("Host Performance Data File Processing Interval"),
    $attrsText3
);
$form->addElement(
    'text',
    'service_perfdata_file_processing_interval',
    _("Service Performance Data File Processing Interval"),
    $attrsText3
);

$form->addElement(
    'select',
    'host_perfdata_file_processing_command',
    _("Host Performance Data File Processing Command"),
    $checkCmds
);
$form->addElement(
    'select',
    'service_perfdata_file_processing_command',
    _("Service Performance Data File Processing Command"),
    $checkCmds
);

/* *****************************************************
 * Check orphaned
 */
$nagTab = array();
$nagTab[] = $form->createElement('radio', 'check_for_orphaned_services', null, _("Yes"), '1');
$nagTab[] = $form->createElement('radio', 'check_for_orphaned_services', null, _("No"), '0');
$nagTab[] = $form->createElement('radio', 'check_for_orphaned_services', null, _("Default"), '2');
$form->addGroup($nagTab, 'check_for_orphaned_services', _("Orphaned Service Check Option"), '&nbsp;');

$nagTab = array();
$nagTab[] = $form->createElement('radio', 'check_for_orphaned_hosts', null, _("Yes"), '1');
$nagTab[] = $form->createElement('radio', 'check_for_orphaned_hosts', null, _("No"), '0');
$nagTab[] = $form->createElement('radio', 'check_for_orphaned_hosts', null, _("Default"), '2');
$form->addGroup($nagTab, 'check_for_orphaned_hosts', _("Orphaned Host Check Option"), '&nbsp;');

/* *****************************************************
 * Freshness
 */
$nagTab = array();
$nagTab[] = $form->createElement('radio', 'check_service_freshness', null, _("Yes"), '1');
$nagTab[] = $form->createElement('radio', 'check_service_freshness', null, _("No"), '0');
$nagTab[] = $form->createElement('radio', 'check_service_freshness', null, _("Default"), '2');
$form->addGroup($nagTab, 'check_service_freshness', _("Service Freshness Check Option"), '&nbsp;');
$form->addElement('text', 'service_freshness_check_interval', _("Service Freshness Check Interval"), $attrsText3);
$nagTab = array();
$nagTab[] = $form->createElement('radio', 'check_host_freshness', null, _("Yes"), '1');
$nagTab[] = $form->createElement('radio', 'check_host_freshness', null, _("No"), '0');
$nagTab[] = $form->createElement('radio', 'check_host_freshness', null, _("Default"), '2');
$form->addGroup($nagTab, 'check_host_freshness', _("Host Freshness Check Option"), '&nbsp;');
$form->addElement('text', 'host_freshness_check_interval', _("Host Freshness Check Interval"), $attrsText3);
$form->addElement('text', 'additional_freshness_latency', _("Additional freshness latency"), $attrsText3);

/* *****************************************************
 * General Informations
 */
$dateFormats = array(
    "euro" => "euro (30/06/2002 03:15:00)",
    "us" => "us (06/30/2002 03:15:00)",
    "iso8601" => "iso8601 (2002-06-30 03:15:00)",
    "strict-iso8601" => "strict-iso8601 (2002-06-30 03:15:00)"
);
$form->addElement('select', 'date_format', _("Date Format"), $dateFormats);
$form->addElement('text', 'admin_email', _("Administrator Email Address"), $attrsText);
$form->addElement('text', 'admin_pager', _("Administrator Pager"), $attrsText);
$form->addElement('text', 'illegal_object_name_chars', _("Illegal Object Name Characters"), $attrsText2);
$form->addElement('text', 'illegal_macro_output_chars', _("Illegal Macro Output Characters"), $attrsText2);

$nagTab = array();
$nagTab[] = $form->createElement('radio', 'use_regexp_matching', null, _("Yes"), '1');
$nagTab[] = $form->createElement('radio', 'use_regexp_matching', null, _("No"), '0');
$nagTab[] = $form->createElement('radio', 'use_regexp_matching', null, _("Default"), '2');
$form->addGroup($nagTab, 'use_regexp_matching', _("Regular Expression Matching Option"), '&nbsp;');

$nagTab = array();
$nagTab[] = $form->createElement('radio', 'use_true_regexp_matching', null, _("Yes"), '1');
$nagTab[] = $form->createElement('radio', 'use_true_regexp_matching', null, _("No"), '0');
$nagTab[] = $form->createElement('radio', 'use_true_regexp_matching', null, _("Default"), '2');
$form->addGroup($nagTab, 'use_true_regexp_matching', _("True Regular Expression Matching Option"), '&nbsp;');

/* *****************************************************
 * Event Broker Option
 */
$form->addElement('text', 'multiple_broker_module', _("Multiple Broker Module"), $attrsText2);
$form->addElement(
    'static',
    'bkTextMultiple',
    _("This directive can be used multiple times, see nagios documentation.")
);
$cloneSet = array();
$cloneSet[] = $form->addElement(
    'text',
    'in_broker[#index#]',
    _('Event broker directive'),
    array(
        'id' => 'in_broker_#index#',
        'size' => 100
    )
);

$eventBrokerOptionsData = [];
// Add checkbox for each of event broker options
foreach (CentreonMainCfg::EVENT_BROKER_OPTIONS as $bit => $label) {
    if ($bit === -1 || $bit === 0) {
        $onClick = 'unCheckOthers("event-broker-options", this.name);';
    } else {
        $onClick = 'unCheckAllAndNaught("event-broker-options");';
    }
    $eventBrokerOptionsData[] = $form->createElement(
        'checkbox',
        $bit,
        '',
        _($label),
        [
            'onClick' => $onClick,
            'class' => 'event-broker-options'
        ]
    );;
}
$form->addGroup($eventBrokerOptionsData, 'event_broker_options', _("Broker Module Options"), '&nbsp;');
 // New options for enable whitelist of macros sent to Centreon Broker
$enableMacrosFilter = [];
$enableMacrosFilter[] = $form->createElement('radio', 'enable_macros_filter', null, _("Yes"), 1);
$enableMacrosFilter[] = $form->createElement('radio', 'enable_macros_filter', null, _("No"), 0);
$form->addGroup($enableMacrosFilter, 'enable_macros_filter', _("Enable macro filtering"), '&nbsp;');
 // Dynamic field for macros whitelisted
$form->addElement(
    'static',
    'macros_filter',
    _('Macros whitelist')
);
$cloneSetMacrosFilter = array();
$cloneSetMacrosFilter[] = $form->addElement(
    'text',
    'macros_filter[#index#]',
    _('Macros whitelist'),
    [
        'id' => 'macros_filter_#index#',
        'size' => 100
    ]
);

$tab = array();
$tab[] = $form->createElement('radio', 'action', null, _("List"), '1');
$tab[] = $form->createElement('radio', 'action', null, _("Form"), '0');
$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');

/*
 * Predictive dependancy options
 */
$nagTab = array();
$nagTab[] = $form->createElement('radio', 'enable_predictive_host_dependency_checks', null, _("Yes"), '1');
$nagTab[] = $form->createElement('radio', 'enable_predictive_host_dependency_checks', null, _("No"), '0');
$nagTab[] = $form->createElement('radio', 'enable_predictive_host_dependency_checks', null, _("Default"), '2');
$form->addGroup($nagTab, 'enable_predictive_host_dependency_checks', _("Predictive Host Dependency Checks"), '&nbsp;');

$nagTab = array();
$nagTab[] = $form->createElement('radio', 'enable_predictive_service_dependency_checks', null, _("Yes"), '1');
$nagTab[] = $form->createElement('radio', 'enable_predictive_service_dependency_checks', null, _("No"), '0');
$nagTab[] = $form->createElement(
    'radio',
    'enable_predictive_service_dependency_checks',
    null,
    _("Default"),
    '2'
);
$form->addGroup(
    $nagTab,
    'enable_predictive_service_dependency_checks',
    _("Predictive Service Dependency Checks"),
    '&nbsp;'
);


/*
 * Cache check horizon.
 */
$form->addElement('text', 'cached_host_check_horizon', _("Cached Host Check"), $attrsText3);
$form->addElement('text', 'cached_service_check_horizon', _("Cached Service Check"), $attrsText3);

/*
 * Tunning
 */
$nagTab = array();
$nagTab[] = $form->createElement('radio', 'use_large_installation_tweaks', null, _("Yes"), '1');
$nagTab[] = $form->createElement('radio', 'use_large_installation_tweaks', null, _("No"), '0');
$nagTab[] = $form->createElement('radio', 'use_large_installation_tweaks', null, _("Default"), '2');
$form->addGroup($nagTab, 'use_large_installation_tweaks', _("Use large installation tweaks"), '&nbsp;');

$nagTab = array();
$nagTab[] = $form->createElement('radio', 'enable_environment_macros', null, _("Yes"), '1');
$nagTab[] = $form->createElement('radio', 'enable_environment_macros', null, _("No"), '0');
$nagTab[] = $form->createElement('radio', 'enable_environment_macros', null, _("Default"), '2');
$form->addGroup($nagTab, 'enable_environment_macros', _("Enable environment macros"), '&nbsp;');

$nagTab = array();
$nagTab[] = $form->createElement('radio', 'use_setpgid', null, _("Yes"), '1');
$nagTab[] = $form->createElement('radio', 'use_setpgid', null, _("No"), '0');
$nagTab[] = $form->createElement('radio', 'use_setpgid', null, _("Default"), '2');
$form->addGroup($nagTab, 'use_setpgid', _("Use setpgid"), '&nbsp;');

/* ****************************************************
 * Debug
 */
$form->addElement('text', 'debug_file', _("Debug file (Directory + File)"), $attrsText);
$form->addElement('text', 'max_debug_file_size', _("Debug file Maximum Size"), $attrsText);

$nagTab = array();
$nagTab[] = $form->createElement('radio', 'daemon_dumps_core', null, _("Yes"), '1');
$nagTab[] = $form->createElement('radio', 'daemon_dumps_core', null, _("No"), '0');
$form->addGroup($nagTab, 'daemon_dumps_core', _('Daemon core dumps'), '&nbsp;');

$verboseOptions = array(
    '0' => _("Basic information"),
    '1' => _("More detailed information"),
    '2' => _("Highly detailed information")
);
$form->addElement('select', 'debug_verbosity', _("Debug Verbosity"), $verboseOptions);

$debugLevel = array();
$debugLevel["-1"] = _("Log everything");
$debugLevel["0"] = _("Log nothing (default)");
$debugLevel["1"] = _("Function enter/exit information");
$debugLevel["2"] = _("Config information");
$debugLevel["4"] = _("Process information");
$debugLevel["8"] = _("Scheduled event information");
$debugLevel["16"] = _("Host/service check information");
$debugLevel["32"] = _("Notification information");
$debugLevel["64"] = _("Event broker information");
$debugLevel["128"] = _("External Commands");
$debugLevel["256"] = _("Commands");
$debugLevel["512"] = _("Downtimes");
$debugLevel["1024"] = _("Comments");
$debugLevel["2048"] = _("Macros");
foreach ($debugLevel as $key => $val) {
    if ($key == "-1" || $key == "0") {
        $debugCheck[] = $form->createElement(
            'checkbox',
            $key,
            '&nbsp;',
            $val,
            array(
                "id" => "debug" . $key,
                "onClick" => "unCheckOthers('debug-level', this.name);",
                'class' => 'debug-level'
            )
        );
    } else {
        $debugCheck[] = $form->createElement(
            'checkbox',
            $key,
            '&nbsp;',
            $val,
            array(
                "id" => "debug" . $key,
                "onClick" => "unCheckAllAndNaught('debug-level');",
                'class' => 'debug-level'
            )
        );
    }
}
$form->addGroup($debugCheck, 'nagios_debug_level', _("Debug Level"), '<br/>');
$form->setDefaults($nagios_d);
$form->setDefaults($defaultEventBrokerOptions);

$form->setDefaults($objMain->getDefaultMainCfg());

$form->setDefaults(array('action' => '1'));

$form->addElement('hidden', 'nagios_id');
$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);


function isNum($value)
{
    return is_numeric($value);
}

$form->registerRule('exist', 'callback', 'testExistence');
$form->registerRule('isNum', 'callback', 'isNum');

/* Add validator for macro name format */
/**
 * Validate the macro name
 *
 * @param string $value Not used
 * @return bool If all name are valid
 */
function validMacroName($value)
{
    // Get the list of invalid characters
    $invalidCharacters = str_split($_REQUEST['illegal_macro_output_chars']);
    foreach ($_REQUEST['macros_filter'] as $name) {
        $parsed = str_replace($invalidCharacters, '', $name);
        // Contains one of invalid characters
        if ($parsed !== $name) {
            return false;
        }
    }
    return true;
}
$form->registerRule('validMacroName', 'callback', 'validMacroName');

$form->applyFilter('cfg_dir', 'slash');
$form->applyFilter('log_archive_path', 'slash');
$form->applyFilter('__ALL__', 'myTrim');

$form->addRule('nagios_name', _("Compulsory Name"), 'required');
$form->addRule('cfg_file', _("Required Field"), 'required');
$form->addRule('nagios_comment', _("Required Field"), 'required');
$form->addRule('nagios_name', _("Name is already in use"), 'exist');
// Add rule to field for whitelist macro
$form->addRule('macros_filter', _("A macro is malformated."), 'validMacroName');

/*
 * Get Values
 */
$ret = $form->getSubmitValues();

$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;" . _("Required fields"));

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

if ($o == "w") {
    // Just watch a nagios information
    if ($centreon->user->access->page($p) != 2) {
        $form->addElement(
            "button",
            "change",
            _("Modify"),
            array("onClick" => "javascript:window.location.href='?p=" . $p . "&o=c&nagios_id=" . $nagios_id . "'")
        );
    }
    $form->setDefaults($nagios);
    $form->freeze();
} elseif ($o == "c") {
    // Modify nagios information
    $subC = $form->addElement('submit', 'submitC', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement(
        'reset',
        'reset',
        _("Reset"),
        array("onClick" => "javascript:resetBroker('" . $o . "')", "class" => "btc bt_default")
    );

    $form->setDefaults($nagios);
} elseif ($o == "a") {
    // Add nagios information
    $subA = $form->addElement('submit', 'submitA', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement(
        'reset',
        'reset',
        _("Reset"),
        array("onClick" => "javascript:resetBroker('" . $o . "')", "class" => "btc bt_default")
    );
}
$tpl->assign('msg', array("nagios" => $oreon->user->get_version()));

$tpl->assign(
    "helpattr",
    'TITLE, "' . _("Help")
    . '", CLOSEBTN, true, FIX, [this, 0, 5], BGCOLOR, "#ffff99", BORDERCOLOR, "orange", '
    . 'TITLEFONTCOLOR, "black", TITLEBGCOLOR, "orange", CLOSEBTNCOLORS, '
    . '["","black", "white", "red"], WIDTH, -300, SHADOW, true, TEXTALIGN, "justify"'
);

// prepare help texts
$helptext = "";
include_once("help.php");
foreach ($help as $key => $text) {
    $helptext .= '<span style="display:none" id="help:' . $key . '">' . $text . '</span>' . "\n";
}
$tpl->assign("helptext", $helptext);


$valid = false;
if ($form->validate()) {
    $nagiosObj = $form->getElement('nagios_id');
    if ($form->getSubmitValue("submitA")) {
        $nagiosObj->setValue(insertNagiosInDB());
    } elseif ($form->getSubmitValue("submitC")) {
        updateNagiosInDB($nagiosObj->getValue());
    }
    $o = "w";
    if ($centreon->user->access->page($p) != 2) {
        $form->addElement(
            "button",
            "change",
            _("Modify"),
            array(
                "onClick" => "javascript:window.location.href='?p=" . $p .
                    "&o=c&nagios_id=" . $nagiosObj->getValue() . "'"
            )
        );
    }
    $valid = true;
}

if ($valid) {
    require_once($path . "listNagios.php");
} else {
    /*
     * Apply a template definition
     */
    $renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
    $renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
    $renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
    $form->accept($renderer);
    $tpl->assign('form', $renderer->toArray());
    $tpl->assign('o', $o);
    $tpl->assign('sort1', _("Files"));
    $tpl->assign('sort2', _("Check Options"));
    $tpl->assign('sort3', _("Log Options"));
    $tpl->assign('sort4', _("Data"));
    $tpl->assign('sort5', _("Tuning"));
    $tpl->assign('sort6', _("Admin"));
    $tpl->assign('sort7', _("Debug"));
    $tpl->assign('Status', _("Status"));
    $tpl->assign('Folders', _("Folders"));
    $tpl->assign('Files', _("Files"));
    $tpl->assign('ExternalCommandes', _("External Commands"));
    $tpl->assign('HostCheckOptions', _("Host Check Options"));
    $tpl->assign('ServiceCheckOptions', _("Service Check Options"));
    $tpl->assign('EventHandler', _("Event Handler"));
    $tpl->assign('Freshness', _("Freshness"));
    $tpl->assign('FlappingOptions', _("Flapping Options"));
    $tpl->assign('PostCheck', _("Post Check"));
    $tpl->assign('CachedCheck', _("Cached Check"));
    $tpl->assign('MiscOptions', _("Misc Options"));
    $tpl->assign('PassivOptions', _("Passive host checking Options"));
    $tpl->assign('LoggingOptions', _("Logging Options"));
    $tpl->assign('Timouts', _("Timeouts"));
    $tpl->assign('Archives', _("Archives"));
    $tpl->assign('StatesRetention', _("States Retention"));
    $tpl->assign('BrokerModule', _("Broker Module"));
    $tpl->assign('Perfdata', _("Perfdata"));
    $tpl->assign('TimeUnit', _("Time Unit"));
    $tpl->assign('HostCheckSchedulingOptions', _("Host Check Scheduling Options"));
    $tpl->assign('ServiceCheckSchedulingOptions', _("Service Check Scheduling Options"));
    $tpl->assign('AutoRescheduling', _("Auto Rescheduling"));
    $tpl->assign('Optimization', _("Optimization"));
    $tpl->assign('Advanced', _("Advanced"));
    $tpl->assign('AdminInfo', _("Admin information"));
    $tpl->assign('DebugConfiguration', _("Debug Configuration"));
    $tpl->assign('Debug', _("Debug"));
    $tpl->assign("Seconds", _("seconds"));
    $tpl->assign("Minutes", _("minutes"));
    $tpl->assign("Bytes", _("bytes"));
    $tpl->assign(
        "BrokerOptionsWarning",
        _("Warning: this value can be dangerous, use -1 if you have any doubt.")
    );
    $tpl->assign('cloneSet', $cloneSet);
    $tpl->assign('cloneSetMacrosFilter', $cloneSetMacrosFilter);
    $tpl->assign('centreon_path', _CENTREON_PATH_);
    $tpl->assign("initial_state_warning", _("This option must be enabled for Centreon Dashboard module."));
    $tpl->assign("aggressive_host_warning", _("This option must be disable in order to avoid latency problem."));
    $tpl->display("formNagios.ihtml");
}
?>

<script type="text/javascript">

    function unCheckOthers(className, name) {
        var elements = document.querySelectorAll("." + className);
        elements.forEach(function (element) {
            if (element.name !== name) {
                element.checked = false;
            }
        })
    }

    function unCheckAllAndNaught(className) {
        var elements = document.querySelectorAll("." + className);
        elements.forEach(function (element) {
            if (element.name.match(/\[(0|-1)\]$/)) {
                element.checked = false;
            }
        })
    }

</script>
