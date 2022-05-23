INSERT INTO `nagios_server` (`id`, `name`, `localhost`, `is_default`, `last_restart`, `ns_ip_address`, `ns_activate`, `ns_status`, `engine_start_command`, `engine_stop_command`, `engine_restart_command`, `engine_reload_command`, `broker_reload_command`, `nagios_bin`, `nagiostats_bin`, `nagios_perfdata`, `centreonbroker_cfg_path`, `centreonbroker_module_path`, `centreonconnector_path`, `ssh_port`, `gorgone_communication_type`, `gorgone_port`, `init_script_centreontrapd`, `snmp_trapd_path_conf`) VALUES
(1, 'Central', '1', 1, 0, '127.0.0.1', '1', '0', 'service centengine start', 'service centengine stop', 'service centengine restart', 'service centengine reload', 'service cbd reload', '@monitoring_binary@', '@centreon_engine_stats_binary@', '@monitoring_varlog@/service-perfdata', '', NULL, '@centreon_engine_connectors@', 22, '1',  5556, 'centreontrapd', '/etc/snmp/centreon_traps/');

INSERT INTO `cfg_nagios` (`nagios_id`, `nagios_name`) VALUES (1, 'Centreon Engine Central');
UPDATE `cfg_nagios` SET `log_file` = '@monitoring_varlog@/centengine.log';
UPDATE `cfg_nagios` SET `cfg_dir` = '@monitoring_etc@';
UPDATE `cfg_nagios` SET `status_file` = '@monitoring_varlog@/status.dat';
UPDATE `cfg_nagios` SET `status_update_interval` = '60';
UPDATE `cfg_nagios` SET `enable_notifications` = '1';
UPDATE `cfg_nagios` SET `execute_service_checks` = '1';
UPDATE `cfg_nagios` SET `accept_passive_service_checks` = '1';
UPDATE `cfg_nagios` SET `execute_host_checks` = '1';
UPDATE `cfg_nagios` SET `accept_passive_host_checks` = '1';
UPDATE `cfg_nagios` SET `enable_event_handlers` = '1';
UPDATE `cfg_nagios` SET `check_external_commands` = '1';
UPDATE `cfg_nagios` SET `external_command_buffer_slots` = '4096';
UPDATE `cfg_nagios` SET `command_check_interval` = '1s';
UPDATE `cfg_nagios` SET `command_file` = '@monitoring_var_lib@/rw/centengine.cmd';
UPDATE `cfg_nagios` SET `retain_state_information` = '1';
UPDATE `cfg_nagios` SET `state_retention_file` = '@monitoring_varlog@/retention.dat';
UPDATE `cfg_nagios` SET `retention_update_interval` = '60';
UPDATE `cfg_nagios` SET `use_retained_program_state` = '1';
UPDATE `cfg_nagios` SET `use_retained_scheduling_info` = '1';
UPDATE `cfg_nagios` SET `use_syslog` = '0';
UPDATE `cfg_nagios` SET `log_notifications` = '1';
UPDATE `cfg_nagios` SET `log_service_retries` = '1';
UPDATE `cfg_nagios` SET `log_host_retries` = '1';
UPDATE `cfg_nagios` SET `log_event_handlers` = '1';
UPDATE `cfg_nagios` SET `log_notifications` = '1';
UPDATE `cfg_nagios` SET `log_service_retries` = '1';
UPDATE `cfg_nagios` SET `log_host_retries` = '1';
UPDATE `cfg_nagios` SET `log_event_handlers` = '1';
UPDATE `cfg_nagios` SET `log_external_commands` = '1';
UPDATE `cfg_nagios` SET `log_passive_checks` = '1';
UPDATE `cfg_nagios` SET `service_inter_check_delay_method` = 's';
UPDATE `cfg_nagios` SET `host_inter_check_delay_method` = 's';
UPDATE `cfg_nagios` SET `service_interleave_factor` = 's';
UPDATE `cfg_nagios` SET `max_concurrent_checks` = '0';
UPDATE `cfg_nagios` SET `max_service_check_spread` = '15';
UPDATE `cfg_nagios` SET `max_host_check_spread` = '15';
UPDATE `cfg_nagios` SET `check_result_reaper_frequency` = '5';
UPDATE `cfg_nagios` SET `auto_reschedule_checks` = '0';
UPDATE `cfg_nagios` SET `auto_rescheduling_interval` = NULL;
UPDATE `cfg_nagios` SET `auto_rescheduling_window` = NULL;
UPDATE `cfg_nagios` SET `enable_flap_detection` = '0';
UPDATE `cfg_nagios` SET `low_service_flap_threshold` = '25.0';
UPDATE `cfg_nagios` SET `high_service_flap_threshold` = '50.0';
UPDATE `cfg_nagios` SET `low_host_flap_threshold` = '25.0';
UPDATE `cfg_nagios` SET `high_host_flap_threshold` = '50.0';
UPDATE `cfg_nagios` SET `soft_state_dependencies` = '0';
UPDATE `cfg_nagios` SET `service_check_timeout` = '60';
UPDATE `cfg_nagios` SET `host_check_timeout` = '12';
UPDATE `cfg_nagios` SET `event_handler_timeout` = '30';
UPDATE `cfg_nagios` SET `notification_timeout` = '30';
UPDATE `cfg_nagios` SET `check_for_orphaned_services` = '1';
UPDATE `cfg_nagios` SET `check_for_orphaned_hosts` = '1';
UPDATE `cfg_nagios` SET `check_service_freshness` = '0';
UPDATE `cfg_nagios` SET `service_freshness_check_interval` = NULL;
UPDATE `cfg_nagios` SET `freshness_check_interval` = NULL;
UPDATE `cfg_nagios` SET `check_host_freshness` = '0';
UPDATE `cfg_nagios` SET `host_freshness_check_interval` = NULL;
UPDATE `cfg_nagios` SET `date_format` = 'euro';
UPDATE `cfg_nagios` SET `illegal_object_name_chars` = '~!$%^&*"|\'<>?,()=';
UPDATE `cfg_nagios` SET `illegal_macro_output_chars` = '`~$^&"|\'<>';
UPDATE `cfg_nagios` SET `use_regexp_matching` = '0';
UPDATE `cfg_nagios` SET `use_true_regexp_matching` = '0';
UPDATE `cfg_nagios` SET `admin_email` = 'admin@localhost';
UPDATE `cfg_nagios` SET `admin_pager` = 'admin@localhost';
UPDATE `cfg_nagios` SET `nagios_comment` = 'Centreon Engine configuration file for a central instance';
UPDATE `cfg_nagios` SET `nagios_activate` = '1';
UPDATE `cfg_nagios` SET `event_broker_options` = '-1';
UPDATE `cfg_nagios` SET `nagios_server_id` = '1';
UPDATE `cfg_nagios` SET `enable_predictive_host_dependency_checks` = NULL;
UPDATE `cfg_nagios` SET `enable_predictive_service_dependency_checks` = NULL;
UPDATE `cfg_nagios` SET `cached_host_check_horizon` = '60';
UPDATE `cfg_nagios` SET `cached_service_check_horizon` = NULL;
UPDATE `cfg_nagios` SET `enable_environment_macros` = '0';
UPDATE `cfg_nagios` SET `additional_freshness_latency` = '15';
UPDATE `cfg_nagios` SET `debug_file` = '@monitoring_varlog@/centengine.debug';
UPDATE `cfg_nagios` SET `debug_level` = '0';
UPDATE `cfg_nagios` SET `debug_level_opt` = '0';
UPDATE `cfg_nagios` SET `debug_verbosity` = '1';
UPDATE `cfg_nagios` SET `max_debug_file_size` = '1000000000';
UPDATE `cfg_nagios` SET `log_pid` = '1';
UPDATE `cfg_nagios` SET `cfg_file` = 'centengine.cfg';
UPDTAE `cfg_nagios` SET `logger_version` = 'log_v2_enabled';

INSERT INTO `cfg_nagios_logger` (`cfg_nagios_id`, `log_v2_logger`, `log_level_functions`, `log_level_config`, `log_level_events`, `log_level_checks`, `log_level_notifications`, `log_level_eventbroker`, `log_level_external_command`, `log_level_commands`, `log_level_downtimes`, `log_level_comments`, `log_level_macros`, `log_level_process`, `log_level_runtime`) VALUES
(1, 'file', 'err', 'info', 'info', 'info', 'err', 'err', 'info', 'err', 'err', 'err', 'err', 'info', 'err');

INSERT INTO `cfg_nagios_broker_module` (`cfg_nagios_id`, `broker_module`) VALUES (1, '@centreon_engine_lib@/externalcmd.so');

INSERT INTO `cfg_resource` (`resource_id`, `resource_name`, `resource_line`, `resource_comment`, `resource_activate`) VALUES
(1, '$USER1$', '@plugin_dir@', 'Nagios Plugins Path', '1'),
(2, '$CENTREONPLUGINS$', '@centreonplugins@', 'Centreon Plugins Path', '1');

INSERT INTO `cfg_resource_instance_relations` (`resource_id`, `instance_id` ) VALUES (1, 1), (2, 1);
INSERT INTO `options` (`key`, `value`) VALUES ('cengine_path_connectors','@centreon_engine_connectors@/');
