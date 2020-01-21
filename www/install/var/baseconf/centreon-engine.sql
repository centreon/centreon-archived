INSERT INTO `nagios_server` (`id`, `name`, `localhost`, `is_default`, `last_restart`, `ns_ip_address`, `ns_activate`, `ns_status`, `engine_start_command`, `engine_stop_command`, `engine_restart_command`, `engine_reload_command`, `broker_reload_command`, `nagios_bin`, `nagiostats_bin`, `nagios_perfdata`, `centreonbroker_cfg_path`, `centreonbroker_module_path`, `centreonconnector_path`, `gorgone_communication_type`, `gorgone_port`, `init_script_centreontrapd`, `snmp_trapd_path_conf`) VALUES
(1, 'Central', '1', 1, 0, '127.0.0.1', '1', '0', 'service centengine start', 'service centengine stop', 'service centengine restart', 'service centengine reload', 'service cbd reload', '@monitoring_binary@', '@centreon_engine_stats_binary@', '@monitoring_varlog@/service-perfdata', '', NULL, '@centreon_engine_connectors@', '1',  5556, 'centreontrapd', '/etc/snmp/centreon_traps/');

INSERT INTO `cfg_nagios` (`nagios_id`, `nagios_name`, `log_file`, `cfg_dir`, `temp_file`, `status_file`, `check_result_path`, `use_check_result_path`, `max_check_result_file_age`, `status_update_interval`, `nagios_user`, `nagios_group`, `enable_notifications`, `execute_service_checks`, `accept_passive_service_checks`, `execute_host_checks`, `accept_passive_host_checks`, `enable_event_handlers`, `log_rotation_method`, `log_archive_path`, `check_external_commands`, `external_command_buffer_slots`, `command_check_interval`, `command_file`, `downtime_file`, `comment_file`, `lock_file`, `retain_state_information`, `state_retention_file`, `retention_update_interval`, `use_retained_program_state`, `use_retained_scheduling_info`, `retained_contact_host_attribute_mask`, `retained_contact_service_attribute_mask`, `retained_process_host_attribute_mask`, `retained_process_service_attribute_mask`, `retained_host_attribute_mask`, `retained_service_attribute_mask`, `use_syslog`, `log_notifications`, `log_service_retries`, `log_host_retries`, `log_event_handlers`, `log_external_commands`, `log_passive_checks`, `global_host_event_handler`, `global_service_event_handler`, `sleep_time`, `service_inter_check_delay_method`, `host_inter_check_delay_method`, `service_interleave_factor`, `max_concurrent_checks`, `max_service_check_spread`, `max_host_check_spread`, `check_result_reaper_frequency`, `max_check_result_reaper_time`, `interval_length`, `auto_reschedule_checks`, `auto_rescheduling_interval`, `auto_rescheduling_window`, `use_aggressive_host_checking`, `enable_flap_detection`, `low_service_flap_threshold`, `high_service_flap_threshold`, `low_host_flap_threshold`, `high_host_flap_threshold`, `soft_state_dependencies`, `service_check_timeout`, `host_check_timeout`, `event_handler_timeout`, `notification_timeout`, `ocsp_timeout`, `ochp_timeout`, `perfdata_timeout`, `obsess_over_services`, `ocsp_command`, `obsess_over_hosts`, `ochp_command`, `process_performance_data`, `host_perfdata_command`, `service_perfdata_command`, `host_perfdata_file`, `service_perfdata_file`, `host_perfdata_file_template`, `service_perfdata_file_template`, `host_perfdata_file_mode`, `service_perfdata_file_mode`, `host_perfdata_file_processing_interval`, `service_perfdata_file_processing_interval`, `host_perfdata_file_processing_command`, `service_perfdata_file_processing_command`, `check_for_orphaned_services`, `check_for_orphaned_hosts`, `check_service_freshness`, `service_freshness_check_interval`, `freshness_check_interval`, `check_host_freshness`, `host_freshness_check_interval`, `date_format`, `illegal_object_name_chars`, `illegal_macro_output_chars`, `use_regexp_matching`, `use_true_regexp_matching`, `admin_email`, `admin_pager`, `nagios_comment`, `nagios_activate`, `event_broker_options`, `translate_passive_host_checks`, `nagios_server_id`, `enable_predictive_host_dependency_checks`, `enable_predictive_service_dependency_checks`, `cached_host_check_horizon`, `cached_service_check_horizon`, `passive_host_checks_are_soft`, `use_large_installation_tweaks`, `enable_environment_macros`, `additional_freshness_latency`, `debug_file`, `debug_level`, `debug_level_opt`, `debug_verbosity`, `max_debug_file_size`, `cfg_file`) VALUES
(1, 'Centreon Engine Central', '@monitoring_varlog@/centengine.log', '@monitoring_etc@', '@monitoring_varlog@/centengine.tmp', '@monitoring_varlog@/status.dat', NULL, '0', NULL, NULL, '@monitoring_user@', '@monitoring_group@', '1', '1', '1', '2', '2', '1', 'd', '@monitoring_varlog@/archives/', '1', NULL, '1s', '@monitoring_var_lib@/rw/centengine.cmd', NULL, NULL, '/var/lock/subsys/centengine.lock', '1', '@monitoring_varlog@/retention.dat', 60, '1', '1', NULL, NULL, NULL, NULL, NULL, NULL, '0', '1', '1', '1', '1', '1', '2', NULL, NULL, '0.2', 's', NULL, 's', 400, 5, NULL, 5, NULL, 60, '2', NULL, NULL, '1', '0', '25.0', '50.0', '25.0', '50.0', '0', 60, 12, 30, 30, 5, 5, 5, '0', NULL, '2', NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, '2', '2', NULL, NULL, NULL, NULL, '0', '0', '1', NULL, NULL, '2', NULL, 'euro', '~!$%^&amp;*&quot;|&#039;&lt;&gt;?,()=', '`~$^&amp;&quot;|&#039;&lt;&gt;', '2', '2', 'admin@localhost', 'admin', 'Centreon Engine', '1', '-1', NULL, 1, '2', '2', 60, NULL, NULL, '2', '2', NULL, '@monitoring_varlog@/centengine.debug', 0, '0', '2', NULL, 'centengine.cfg');

INSERT INTO `cfg_nagios_broker_module` (`cfg_nagios_id`, `broker_module`) VALUES (1, '@centreon_engine_lib@/externalcmd.so');

INSERT INTO `cfg_resource` (`resource_id`, `resource_name`, `resource_line`, `resource_comment`, `resource_activate`) VALUES
(1, '$USER1$', '@plugin_dir@', 'Nagios Plugins Path', '1'),
(2, '$CENTREONPLUGINS$', '@centreonplugins@', 'Centreon Plugins Path', '1');

INSERT INTO `cfg_resource_instance_relations` (`resource_id`, `instance_id` ) VALUES (1, 1), (2, 1);
INSERT INTO `options` (`key`, `value`) VALUES ('cengine_path_connectors','@centreon_engine_connectors@/');
