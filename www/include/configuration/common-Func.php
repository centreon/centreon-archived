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
 *
 */

$aDefaultBrokerDirective = array(
    'ui' => '/usr/lib64/centreon-engine/externalcmd.so',
    'wizard' => '/usr/lib64/nagios/cbmod.so /etc/centreon-broker/poller-module.xml'
);

$aInstanceDefaultValues = array(
    'log_file' => '/var/log/centreon-engine/centengine.log',
    'cfg_dir' => '/etc/centreon-engine/',
    'object_cache_file' => '/var/log/centreon-engine/objects.cache',
    'temp_file' => '/var/log/centreon-engine/centengine.tmp',
    'status_file' => '/var/log/centreon-engine/status.dat',
    'status_update_interval' => '30',
    'nagios_user' => 'centreon-engine',
    'nagios_group' => 'centreon-engine',
    'enable_notifications' => '1',
    'execute_service_checks' => '1',
    'accept_passive_service_checks' => '1',
    'execute_host_checks' => '1',
    'accept_passive_host_checks' => '1',
    'enable_event_handlers' => '1',
    'log_rotation_method' => 'd',
    'log_archive_path' => '/var/log/centreon-engine/archives/',
    'check_external_commands' => '1',
    'external_command_buffer_slots' => '4096',
    'command_check_interval' => '1s',
    'command_file' => '/var/lib/centreon-engine/rw/centengine.cmd',
    'lock_file' => '/var/lock/subsys/centengine.lock',
    'retain_state_information' => '1',
    'state_retention_file' => '/var/log/centreon-engine/retention.dat',
    'retention_update_interval' => '60',
    'use_retained_program_state' => '1',
    'use_retained_scheduling_info' => '1',
    'use_syslog' => '0',
    'log_notifications' => '1',
    'log_service_retries' => '1',
    'log_host_retries' => '1',
    'log_event_handlers' => '1',
    'log_initial_states' => '1',
    'log_external_commands' => '1',
    'log_passive_checks' => '0',
    'sleep_time' => '0.2',
    'service_inter_check_delay_method' => 's',
    'host_inter_check_delay_method' => 's',
    'service_interleave_factor' => 's',
    'max_concurrent_checks' => '400',
    'max_service_check_spread' => '5',
    'max_host_check_spread' => '5',
    'check_result_reaper_frequency' => '5',
    'max_check_result_reaper_time' => '10',
    'interval_length' => '60',
    'auto_reschedule_checks' => '0',
    'use_aggressive_host_checking' => '0',
    'enable_flap_detection' => '0',
    'low_service_flap_threshold' => '25.0',
    'high_service_flap_threshold' => '50.0',
    'low_host_flap_threshold' => '25.0',
    'high_host_flap_threshold' => '50.0',
    'soft_state_dependencies' => '0',
    'service_check_timeout' => '60',
    'host_check_timeout' => '10',
    'event_handler_timeout' => '30',
    'notification_timeout' => '30',
    'ocsp_timeout' => '5',
    'ochp_timeout' => '5',
    'perfdata_timeout' => '5',
    'obsess_over_services' => '0',
    'obsess_over_hosts' => '0',
    'process_performance_data' => '0',
    'host_perfdata_file_mode' => '2',
    'service_perfdata_file_mode' => '2',
    'check_for_orphaned_services' => '0',
    'check_for_orphaned_hosts' => '0',
    'check_service_freshness' => '2',
    'check_host_freshness' => '2',
    'date_format' => 'euro',
    'illegal_object_name_chars' => "~!$%^&*\"|'<>?,()=",
    'illegal_macro_output_chars' => "`~$^&\"|'<>",
    'use_regexp_matching' => '2',
    'use_true_regexp_matching' => '2',
    'admin_email' => 'admin@localhost',
    'admin_pager' => 'admin',
    'nagios_comment' => 'Centreon Engine configuration file',
    'nagios_activate' => '1',
    'event_broker_options' => '-1',
    'translate_passive_host_checks' => '2',
    'nagios_server_id' => '1',
    'enable_predictive_host_dependency_checks' => '0',
    'enable_predictive_service_dependency_checks' => '0',
    'passive_host_checks_are_soft' => '2',
    'use_large_installation_tweaks' => '1',
    'enable_environment_macros' => '2',
    'use_setpgid' => '2',
    'debug_file' => '/var/log/centreon-engine/centengine.debug',
    'debug_level' => '0',
    'debug_level_opt' => '0',
    'debug_verbosity' => '0',
    'max_debug_file_size' => '1000000000',
    'daemon_dumps_core' => '0',
    'cfg_file' => 'centengine.cfg',
    'use_check_result_path' => '0',
    'cached_host_check_horizon' => '60'
);

function insertBrokerDefaultDirectives($iId, $source)
{
    global $pearDB, $aDefaultBrokerDirective;
    
    if (empty($iId) || !in_array($source, array('ui', 'wizard'))) {
        return;
    }
    
    $value = $aDefaultBrokerDirective[$source];

    $DBRESULT = $pearDB->query("SELECT bk_mod_id FROM `cfg_nagios_broker_module` WHERE cfg_nagios_id = '".$iId."'");
    if ($DBRESULT->numRows() == 0) {
        $sQuery = "INSERT INTO cfg_nagios_broker_module (`broker_module`, `cfg_nagios_id`) VALUES ('". $value ."', ". $iId .")";
        $res = $pearDB->query($sQuery);

        if (PEAR::isError($res)) {
            return false;
        }
    }
}

/**
 * Insert the instance in cfg_nagios
 *
 * @param string $sName
 * @param int $iId
 */
function insertServerInCfgNagios($iId, $sName)
{
    global $pearDB, $aInstanceDefaultValues;
    if (empty($sName)) {
        $sName = 'poller';
    }
    if (!isset($aInstanceDefaultValues) || !isset($iId)) {
        return;
    }
    $DBRESULT = $pearDB->query("SELECT nagios_id FROM `cfg_nagios` WHERE  nagios_server_id = '".$iId."'");
    
    if ($DBRESULT->numRows() == 0) {
        $rq = "INSERT INTO `cfg_nagios` (`nagios_name`, `nagios_server_id`, `log_file`, `cfg_dir`, `object_cache_file`, `temp_file`, `status_file`, 
        `status_update_interval`, `nagios_user`, `nagios_group`, `enable_notifications`, `execute_service_checks`, `accept_passive_service_checks`, `execute_host_checks`, 
        `accept_passive_host_checks`, `enable_event_handlers`, `log_rotation_method`, `log_archive_path`, `check_external_commands`, `external_command_buffer_slots`, 
        `command_check_interval`, `command_file`, `lock_file`, `retain_state_information`, `state_retention_file`,`retention_update_interval`, `use_retained_program_state`, 
        `use_retained_scheduling_info`, `use_syslog`, `log_notifications`, `log_service_retries`, `log_host_retries`, `log_event_handlers`, `log_initial_states`, 
        `log_external_commands`, `log_passive_checks`, `sleep_time`, `service_inter_check_delay_method`, `host_inter_check_delay_method`, `service_interleave_factor`, 
        `max_concurrent_checks`, `max_service_check_spread`, `max_host_check_spread`, `check_result_reaper_frequency`, `max_check_result_reaper_time`, `interval_length`, 
        `auto_reschedule_checks`, `use_aggressive_host_checking`, `enable_flap_detection`, `low_service_flap_threshold`, `high_service_flap_threshold`, `low_host_flap_threshold`, 
        `high_host_flap_threshold`, `soft_state_dependencies`, `service_check_timeout`, `host_check_timeout`, `event_handler_timeout`, `notification_timeout`, `ocsp_timeout`, 
        `ochp_timeout`, `perfdata_timeout`, `obsess_over_services`, `obsess_over_hosts`, `process_performance_data`, `host_perfdata_file_mode`, `service_perfdata_file_mode`, 
        `check_for_orphaned_services`, `check_for_orphaned_hosts`, `check_service_freshness`, `check_host_freshness`, `date_format`, `illegal_object_name_chars`, 
        `illegal_macro_output_chars`, `use_regexp_matching`, `use_true_regexp_matching`, `admin_email`, `admin_pager`, `nagios_comment`, `nagios_activate`, 
        `event_broker_options`, `translate_passive_host_checks`, `enable_predictive_host_dependency_checks`, `enable_predictive_service_dependency_checks`, `passive_host_checks_are_soft`, 
        `use_large_installation_tweaks`, `enable_environment_macros`, `use_setpgid`,
        `debug_file`, `debug_level`, `debug_level_opt`, `debug_verbosity`, `max_debug_file_size`, `daemon_dumps_core`, `cfg_file`, `use_check_result_path`) ";
        $rq .= "VALUES (";

        $rq .= "'".$sName."', '". $iId. "', '".$aInstanceDefaultValues['log_file'] ."', '" .
        $aInstanceDefaultValues['cfg_dir'] ."', '" .
        $aInstanceDefaultValues['object_cache_file'] ."', '" .
        $aInstanceDefaultValues['temp_file'] ."', '" .
        $aInstanceDefaultValues['status_file'] ."', '" .
        $aInstanceDefaultValues['status_update_interval'] ."', '" .
        $aInstanceDefaultValues['nagios_user'] ."', '" .
        $aInstanceDefaultValues['nagios_group'] ."', '" .
        $aInstanceDefaultValues['enable_notifications'] ."', '" .
        $aInstanceDefaultValues['execute_service_checks'] ."', '" .
        $aInstanceDefaultValues['accept_passive_service_checks'] ."', '" .
        $aInstanceDefaultValues['execute_host_checks'] ."', '" .
        $aInstanceDefaultValues['accept_passive_host_checks'] ."', '" .
        $aInstanceDefaultValues['enable_event_handlers'] ."', '" .
        $aInstanceDefaultValues['log_rotation_method'] ."', '" .
        $aInstanceDefaultValues['log_archive_path'] ."', '" .
        $aInstanceDefaultValues['check_external_commands'] ."', '" .
        $aInstanceDefaultValues['external_command_buffer_slots'] ."', '" .
        $aInstanceDefaultValues['command_check_interval'] ."', '" .
        $aInstanceDefaultValues['command_file'] ."', '" .
        $aInstanceDefaultValues['lock_file'] ."', '" .
        $aInstanceDefaultValues['retain_state_information'] ."', '" .
        $aInstanceDefaultValues['state_retention_file' ] ."', '" .
        $aInstanceDefaultValues['retention_update_interval'] ."', '" .
        $aInstanceDefaultValues['use_retained_program_state'] ."', '" .
        $aInstanceDefaultValues['use_retained_scheduling_info'] ."', '" .
        $aInstanceDefaultValues['use_syslog'] ."', '" .
        $aInstanceDefaultValues['log_notifications'] ."', '" .
        $aInstanceDefaultValues['log_service_retries'] ."', '" .
        $aInstanceDefaultValues['log_host_retries'] ."', '" .
        $aInstanceDefaultValues['log_event_handlers'] ."', '" .
        $aInstanceDefaultValues['log_initial_states'] ."', '" .
        $aInstanceDefaultValues['log_external_commands'] ."', '" .
        $aInstanceDefaultValues['log_passive_checks'] ."', '" .
        $aInstanceDefaultValues['sleep_time'] ."', '" .
        $aInstanceDefaultValues['service_inter_check_delay_method'] ."', '" .
        $aInstanceDefaultValues['host_inter_check_delay_method'] ."', '" .
        $aInstanceDefaultValues['service_interleave_factor'] ."', '" .
        $aInstanceDefaultValues['max_concurrent_checks'] ."', '" .
        $aInstanceDefaultValues['max_service_check_spread'] ."', '" .
        $aInstanceDefaultValues['max_host_check_spread'] ."', '" .
        $aInstanceDefaultValues['check_result_reaper_frequency'] ."', '" .
        $aInstanceDefaultValues['max_check_result_reaper_time'] ."', '" .
        $aInstanceDefaultValues['interval_length'] ."', '" .
        $aInstanceDefaultValues['auto_reschedule_checks'] ."', '" .
        $aInstanceDefaultValues['use_aggressive_host_checking'] ."', '" .
        $aInstanceDefaultValues['enable_flap_detection'] ."', '" .
        $aInstanceDefaultValues['low_service_flap_threshold'] ."', '" .
        $aInstanceDefaultValues['high_service_flap_threshold'] ."', '" .
        $aInstanceDefaultValues['low_host_flap_threshold'] ."', '" .
        $aInstanceDefaultValues['high_host_flap_threshold'] ."', '" .
        $aInstanceDefaultValues['soft_state_dependencies'] ."', '" .
        $aInstanceDefaultValues['service_check_timeout'] ."', '" .
        $aInstanceDefaultValues['host_check_timeout'] ."', '" .
        $aInstanceDefaultValues['event_handler_timeout'] ."', '" .
        $aInstanceDefaultValues['notification_timeout'] ."', '" .
        $aInstanceDefaultValues['ocsp_timeout'] ."', '" .
        $aInstanceDefaultValues['ochp_timeout'] ."', '" .
        $aInstanceDefaultValues['perfdata_timeout'] ."', '" .
        $aInstanceDefaultValues['obsess_over_services'] ."', '" .
        $aInstanceDefaultValues['obsess_over_hosts'] ."', '" .
        $aInstanceDefaultValues['process_performance_data'] ."', '" .
        $aInstanceDefaultValues['host_perfdata_file_mode'] ."', '" .
        $aInstanceDefaultValues['service_perfdata_file_mode'] ."', '" .
        $aInstanceDefaultValues['check_for_orphaned_services'] ."', '" .
        $aInstanceDefaultValues['check_for_orphaned_hosts'] ."', '" .
        $aInstanceDefaultValues['check_service_freshness'] ."', '" .
        $aInstanceDefaultValues['check_host_freshness'] ."', '" .
        $aInstanceDefaultValues['date_format'] ."', '" .
        htmlentities($aInstanceDefaultValues['illegal_object_name_chars'], ENT_QUOTES, "UTF-8") ."', '" .
        htmlentities($aInstanceDefaultValues['illegal_macro_output_chars'], ENT_QUOTES, "UTF-8") ."', '" .
        $aInstanceDefaultValues['use_regexp_matching'] ."', '" .
        $aInstanceDefaultValues['use_true_regexp_matching'] ."', '" .
        $aInstanceDefaultValues['admin_email'] ."', '" .
        $aInstanceDefaultValues['admin_pager'] ."', '" .
        $aInstanceDefaultValues['nagios_comment'] ."', '" .
        $aInstanceDefaultValues['nagios_activate'] ."', '" .
        $aInstanceDefaultValues['event_broker_options'] ."', '" .
        $aInstanceDefaultValues['translate_passive_host_checks'] ."', '" .
        $aInstanceDefaultValues['enable_predictive_host_dependency_checks'] ."', '" .
        $aInstanceDefaultValues['enable_predictive_service_dependency_checks'] ."', '" .
        $aInstanceDefaultValues['passive_host_checks_are_soft'] ."', '" .
        $aInstanceDefaultValues['use_large_installation_tweaks'] ."', '" .
        $aInstanceDefaultValues['enable_environment_macros'] ."', '" .
        $aInstanceDefaultValues['use_setpgid'] ."', '" .
        $aInstanceDefaultValues['debug_file'] ."', '" .
        $aInstanceDefaultValues['debug_level'] ."', '" .
        $aInstanceDefaultValues['debug_level_opt'] ."', '" .
        $aInstanceDefaultValues['debug_verbosity'] ."', '" .
        $aInstanceDefaultValues['max_debug_file_size'] ."', '" .
        $aInstanceDefaultValues['daemon_dumps_core'] ."', '" .
        $aInstanceDefaultValues['cfg_file'] ."', '" .
        $aInstanceDefaultValues['use_check_result_path'] ."'";
        $rq .= ")";

        $res = $pearDB->query($rq);

        if (PEAR::isError($res)) {
            return;
        }
        $res1 = $pearDB->query("SELECT MAX(nagios_id) as last_id FROM `cfg_nagios`");
        $nagios = $res1->fetchRow();
        $iIdNagios = $nagios["last_id"];
    } else {
        $aNagios = $DBRESULT->fetchRow();
        $iIdNagios = $aNagios["nagios_id"];
    }
    
    
    return $iIdNagios;
}
