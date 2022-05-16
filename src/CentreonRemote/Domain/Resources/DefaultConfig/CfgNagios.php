<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */

namespace CentreonRemote\Domain\Resources\DefaultConfig;

/**
 * Get broker configuration template
 */
class CfgNagios
{
    /**
     * Get template configuration
     * @todo move it as yml
     *
     * @return array<string, int|string|null> the configuration template
     */
    public static function getConfiguration(): array
    {
        return [
            'nagios_id'                                   => 1,
            'nagios_name'                                 => 'Centreon Engine Central',
            'log_file'                                    => '@monitoring_varlog@/centengine.log',
            'cfg_dir'                                     => '@monitoring_etc@',
            'temp_file'                                   => '@monitoring_varlog@/centengine.tmp',
            'status_file'                                 => '@monitoring_varlog@/status.dat',
            'status_update_interval'                      => null,
            'nagios_user'                                 => '@monitoring_user@',
            'nagios_group'                                => '@monitoring_group@',
            'enable_notifications'                        => '1',
            'execute_service_checks'                      => '1',
            'accept_passive_service_checks'               => '1',
            'execute_host_checks'                         => '2',
            'accept_passive_host_checks'                  => '2',
            'enable_event_handlers'                       => '1',
            'check_external_commands'                     => '1',
            'external_command_buffer_slots'               => null,
            'command_check_interval'                      => '1s',
            'command_file'                                => '@monitoring_var_lib@/rw/centengine.cmd',
            'downtime_file'                               => null,
            'comment_file'                                => null,
            'lock_file'                                   => '/var/lock/subsys/centengine.lock',
            'retain_state_information'                    => '1',
            'state_retention_file'                        => '@monitoring_varlog@/retention.dat',
            'retention_update_interval'                   => 60,
            'use_retained_program_state'                  => '1',
            'use_retained_scheduling_info'                => '1',
            'retained_contact_host_attribute_mask'        => null,
            'retained_contact_service_attribute_mask'     => null,
            'retained_process_host_attribute_mask'        => null,
            'retained_process_service_attribute_mask'     => null,
            'retained_host_attribute_mask'                => null,
            'retained_service_attribute_mask'             => null,
            'use_syslog'                                  => '0',
            'log_notifications'                           => '1',
            'log_service_retries'                         => '1',
            'log_host_retries'                            => '1',
            'log_event_handlers'                          => '1',
            'log_external_commands'                       => '1',
            'log_passive_checks'                          => '2',
            'global_host_event_handler'                   => null,
            'global_service_event_handler'                => null,
            'sleep_time'                                  => '1',
            'service_inter_check_delay_method'            => 's',
            'host_inter_check_delay_method'               => null,
            'service_interleave_factor'                   => 's',
            'max_concurrent_checks'                       => 0,
            'max_service_check_spread'                    => 15,
            'max_host_check_spread'                       => 15,
            'check_result_reaper_frequency'               => 5,
            'max_check_result_reaper_time'                => null,
            'interval_length'                             => 60,
            'auto_reschedule_checks'                      => '2',
            'auto_rescheduling_interval'                  => null,
            'auto_rescheduling_window'                    => null,
            'enable_flap_detection'                       => '0',
            'low_service_flap_threshold'                  => '25.0',
            'high_service_flap_threshold'                 => '50.0',
            'low_host_flap_threshold'                     => '25.0',
            'high_host_flap_threshold'                    => '50.0',
            'soft_state_dependencies'                     => '0',
            'service_check_timeout'                       => 60,
            'host_check_timeout'                          => 12,
            'event_handler_timeout'                       => 30,
            'notification_timeout'                        => 30,
            'ocsp_timeout'                                => 5,
            'ochp_timeout'                                => 5,
            'perfdata_timeout'                            => 5,
            'obsess_over_services'                        => '0',
            'ocsp_command'                                => null,
            'obsess_over_hosts'                           => '2',
            'ochp_command'                                => null,
            'process_performance_data'                    => '0',
            'host_perfdata_command'                       => null,
            'service_perfdata_command'                    => null,
            'host_perfdata_file'                          => null,
            'service_perfdata_file'                       => null,
            'host_perfdata_file_template'                 => null,
            'service_perfdata_file_template'              => null,
            'host_perfdata_file_mode'                     => '2',
            'service_perfdata_file_mode'                  => '2',
            'host_perfdata_file_processing_interval'      => null,
            'service_perfdata_file_processing_interval'   => null,
            'host_perfdata_file_processing_command'       => null,
            'service_perfdata_file_processing_command'    => null,
            'check_for_orphaned_services'                 => '0',
            'check_for_orphaned_hosts'                    => '0',
            'check_service_freshness'                     => '1',
            'service_freshness_check_interval'            => null,
            'freshness_check_interval'                    => null,
            'check_host_freshness'                        => '2',
            'host_freshness_check_interval'               => null,
            'date_format'                                 => 'euro',
            'illegal_object_name_chars'                   => '~!$%^&*"|\'<>?,()=',
            'illegal_macro_output_chars'                  => '`~$^&"|\'<>',
            'use_regexp_matching'                         => '2',
            'use_true_regexp_matching'                    => '2',
            'admin_email'                                 => 'admin@localhost',
            'admin_pager'                                 => 'admin',
            'nagios_comment'                              => 'Centreon Engine',
            'nagios_activate'                             => '1',
            'event_broker_options'                        => '-1',
            'translate_passive_host_checks'               => null,
            'nagios_server_id'                            => 1,
            'enable_predictive_host_dependency_checks'    => '2',
            'enable_predictive_service_dependency_checks' => '2',
            'cached_host_check_horizon'                   => 60,
            'cached_service_check_horizon'                => null,
            'passive_host_checks_are_soft'                => null,
            'use_large_installation_tweaks'               => '2',
            'enable_environment_macros'                   => '2',
            'additional_freshness_latency'                => null,
            'debug_file'                                  => '@monitoring_varlog@/centengine.debug',
            'debug_level'                                 => 0,
            'debug_level_opt'                             => '0',
            'debug_verbosity'                             => '2',
            'max_debug_file_size'                         => null,
            'cfg_file'                                    => 'centengine.cfg',
            'logger_version'                              => 'log_v2_enabled',
        ];
    }
}
