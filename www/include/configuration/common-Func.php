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


 $aInstanceDefaultValues = array(
	'log_file' => '/var/log/centreon-engine/centengine.log',
	'cfg_dir' => '/etc/centreon-engine/', 
	'object_cache_file' => '/var/log/centreon-engine/objects.cache', 
	'temp_file' => '/var/log/centreon-engine/centengine.tmp', 
	'temp_path' => '/tmp/', 
	'status_file' => '/var/log/centreon-engine/status.dat',
	'p1_file' => '/usr/sbin/p1.pl', 
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
	'free_child_process_memory' => '2',
	'child_processes_fork_twice' => '2',
	'enable_environment_macros' => '2',
        'use_setpgid' => '2',
	'enable_embedded_perl' => '2',
	'use_embedded_perl_implicitly' => '2',
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