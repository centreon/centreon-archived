
/* Remove old columns in engine parameters table */
ALTER TABLE cfg_nagios DROP COLUMN `nagios_user`;
ALTER TABLE cfg_nagios DROP COLUMN `nagios_group`;
ALTER TABLE cfg_nagios DROP COLUMN `downtime_file`;
ALTER TABLE cfg_nagios DROP COLUMN `comment_file`;
ALTER TABLE cfg_nagios DROP COLUMN `temp_file`;
ALTER TABLE cfg_nagios DROP COLUMN `lock_file`;
ALTER TABLE cfg_nagios DROP COLUMN `retained_contact_host_attribute_mask`;
ALTER TABLE cfg_nagios DROP COLUMN `retained_contact_service_attribute_mask`;
ALTER TABLE cfg_nagios DROP COLUMN `retained_process_host_attribute_mask`;
ALTER TABLE cfg_nagios DROP COLUMN `retained_process_service_attribute_mask`;
ALTER TABLE cfg_nagios DROP COLUMN `retained_host_attribute_mask`;
ALTER TABLE cfg_nagios DROP COLUMN `retained_service_attribute_mask`;
ALTER TABLE cfg_nagios DROP COLUMN `max_check_result_reaper_time`;
ALTER TABLE cfg_nagios DROP COLUMN `interval_length`;
ALTER TABLE cfg_nagios DROP COLUMN `perfdata_timeout`;
ALTER TABLE cfg_nagios DROP COLUMN `process_performance_data`;
ALTER TABLE cfg_nagios drop CONSTRAINT `cfg_nagios_ibfk_22`;
ALTER TABLE cfg_nagios DROP KEY `cmd5_index`;
ALTER TABLE cfg_nagios DROP COLUMN `host_perfdata_command`;
ALTER TABLE cfg_nagios drop CONSTRAINT `cfg_nagios_ibfk_15`;
ALTER TABLE cfg_nagios DROP KEY `cmd6_index`;
ALTER TABLE cfg_nagios DROP COLUMN `service_perfdata_command`;
ALTER TABLE cfg_nagios DROP COLUMN `host_perfdata_file`;
ALTER TABLE cfg_nagios DROP COLUMN `service_perfdata_file`;
ALTER TABLE cfg_nagios DROP COLUMN `host_perfdata_file_template`;
ALTER TABLE cfg_nagios DROP COLUMN `service_perfdata_file_template`;
ALTER TABLE cfg_nagios DROP COLUMN `host_perfdata_file_mode`;
ALTER TABLE cfg_nagios DROP COLUMN `service_perfdata_file_mode`;
ALTER TABLE cfg_nagios DROP COLUMN `host_perfdata_file_processing_interval`;
ALTER TABLE cfg_nagios DROP COLUMN `service_perfdata_file_processing_interval`;
ALTER TABLE cfg_nagios drop CONSTRAINT `cfg_nagios_ibfk_24`;
ALTER TABLE cfg_nagios DROP KEY `cmd7_index`;
ALTER TABLE cfg_nagios DROP COLUMN `host_perfdata_file_processing_command`;
ALTER TABLE cfg_nagios drop CONSTRAINT `cfg_nagios_ibfk_25`;
ALTER TABLE cfg_nagios DROP KEY `cmd8_index`;
ALTER TABLE cfg_nagios DROP COLUMN `service_perfdata_file_processing_command`;
ALTER TABLE cfg_nagios DROP COLUMN `use_large_installation_tweaks`;
ALTER TABLE cfg_nagios DROP COLUMN `use_setpgid`;
ALTER TABLE cfg_nagios DROP COLUMN `translate_passive_host_checks`;

UPDATE cfg_nagios set `auto_reschedule_checks` = '0' WHERE `auto_reschedule_checks` = '2';
UPDATE cfg_nagios set `enable_environment_macros` = '0' WHERE `enable_environment_macros` = '2';
UPDATE cfg_nagios set `use_regexp_matching` = '0' WHERE `use_regexp_matching` = '2';
UPDATE cfg_nagios set `use_true_regexp_matching` = '0' WHERE `use_regexp_matching` = '2';
UPDATE cfg_nagios set `use_syslog` = '0' WHERE `use_syslog` = '2';
UPDATE cfg_nagios set `check_for_orphaned_hosts` = '1' WHERE `check_for_orphaned_hosts` = '2';
UPDATE cfg_nagios set `check_for_orphaned_services` = '1' WHERE `check_for_orphaned_services` = '2';
UPDATE cfg_nagios set `soft_state_dependencies` = '0' WHERE `soft_state_dependencies` = '2';
UPDATE cfg_nagios set `check_host_freshness` = '0' WHERE `check_host_freshness` = '2';
UPDATE cfg_nagios set `check_service_freshness` = '0' WHERE `check_service_freshness` = '2';
UPDATE cfg_nagios set `enable_flap_detection` = '0' WHERE `enable_flap_detection` = '2';
UPDATE cfg_nagios set `enable_notifications` = '1' WHERE `enable_notifications` = '2';
UPDATE cfg_nagios set `execute_service_checks` = '1' WHERE `execute_service_checks` = '2';
UPDATE cfg_nagios set `accept_passive_service_checks` = '1' WHERE `accept_passive_service_checks` = '2';
UPDATE cfg_nagios set `execute_host_checks` = '1' WHERE `execute_host_checks` = '2';
UPDATE cfg_nagios set `accept_passive_host_checks` = '1' WHERE `accept_passive_host_checks` = '2';
UPDATE cfg_nagios set `enable_event_handlers` = '1' WHERE `enable_event_handlers` = '2';
UPDATE cfg_nagios set `check_external_commands` = '1' WHERE `check_external_commands` = '2';
UPDATE cfg_nagios set `retain_state_information` = '1' WHERE `retain_state_information` = '2';
UPDATE cfg_nagios set `use_retained_program_state` = '1' WHERE `use_retained_program_state` = '2';
UPDATE cfg_nagios set `use_retained_scheduling_info` = '1' WHERE `use_retained_scheduling_info` = '2';
UPDATE cfg_nagios set `log_notifications` = '1' WHERE `log_notifications` = '2';
UPDATE cfg_nagios set `log_service_retries` = '1' WHERE `log_service_retries` = '2';
UPDATE cfg_nagios set `log_host_retries` = '1' WHERE `log_host_retries` = '2';
UPDATE cfg_nagios set `log_event_handlers` = '1' WHERE `log_event_handlers` = '2';
UPDATE cfg_nagios set `log_initial_states` = '1' WHERE `log_initial_states` = '2';
UPDATE cfg_nagios set `log_initial_states` = '1' WHERE `log_initial_states` = '2';
UPDATE cfg_nagios set `log_external_commands` = '1' WHERE `log_external_commands` = '2';
UPDATE cfg_nagios set `log_passive_checks` = '1' WHERE `log_passive_checks` = '2';
UPDATE cfg_nagios set `enable_predictive_host_dependency_checks` = '1' WHERE `enable_predictive_host_dependency_checks` = '2';
UPDATE cfg_nagios set `enable_predictive_service_dependency_checks` = '1' WHERE `enable_predictive_service_dependency_checks` = '2';
UPDATE cfg_nagios set `debug_verbosity` = '1' WHERE `debug_verbosity` = '2';

ALTER TABLE cfg_nagios MODIFY `auto_reschedule_checks` enum('0','1');
ALTER TABLE cfg_nagios MODIFY `enable_environment_macros` enum('0','1');
ALTER TABLE cfg_nagios MODIFY `use_regexp_matching` enum('0','1');
ALTER TABLE cfg_nagios MODIFY `use_true_regexp_matching` enum('0','1');
ALTER TABLE cfg_nagios MODIFY `use_syslog` enum('0','1');
ALTER TABLE cfg_nagios MODIFY `check_for_orphaned_hosts` enum('0','1');
ALTER TABLE cfg_nagios MODIFY `check_for_orphaned_services` enum('0','1');
ALTER TABLE cfg_nagios MODIFY `soft_state_dependencies` enum('0','1');
ALTER TABLE cfg_nagios MODIFY `check_host_freshness` enum('0','1');
ALTER TABLE cfg_nagios MODIFY `check_service_freshness` enum('0','1');
ALTER TABLE cfg_nagios MODIFY `enable_flap_detection` enum('0','1');
ALTER TABLE cfg_nagios MODIFY `enable_notifications` enum('0','1');
ALTER TABLE cfg_nagios MODIFY `execute_service_checks` enum('0','1');
ALTER TABLE cfg_nagios MODIFY `accept_passive_service_checks` enum('0','1');
ALTER TABLE cfg_nagios MODIFY `execute_host_checks` enum('0','1');
ALTER TABLE cfg_nagios MODIFY `accept_passive_host_checks` enum('0','1');
ALTER TABLE cfg_nagios MODIFY `enable_event_handlers` enum('0','1');
ALTER TABLE cfg_nagios MODIFY `check_external_commands` enum('0','1');
ALTER TABLE cfg_nagios MODIFY `retain_state_information` enum('0','1');
ALTER TABLE cfg_nagios MODIFY `use_retained_program_state` enum('0','1');
ALTER TABLE cfg_nagios MODIFY `use_retained_scheduling_info` enum('0','1');
ALTER TABLE cfg_nagios MODIFY `log_notifications` enum('0','1');
ALTER TABLE cfg_nagios MODIFY `log_service_retries` enum('0','1');
ALTER TABLE cfg_nagios MODIFY `log_host_retries` enum('0','1');
ALTER TABLE cfg_nagios MODIFY `log_event_handlers` enum('0','1');
ALTER TABLE cfg_nagios MODIFY `log_initial_states` enum('0','1');
ALTER TABLE cfg_nagios MODIFY `log_initial_states` enum('0','1');
ALTER TABLE cfg_nagios MODIFY `log_external_commands` enum('0','1');
ALTER TABLE cfg_nagios MODIFY `log_passive_checks` enum('0','1');
ALTER TABLE cfg_nagios MODIFY `enable_predictive_host_dependency_checks` enum('0','1');
ALTER TABLE cfg_nagios MODIFY `enable_predictive_service_dependency_checks` enum('0','1');
ALTER TABLE cfg_nagios MODIFY `debug_verbosity` enum('0','1');
