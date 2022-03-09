<?php

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
            'status_update_interval'                      => '60',
            'enable_notifications'                        => '1',
            'execute_service_checks'                      => '1',
            'accept_passive_service_checks'               => '1',
            'execute_host_checks'                         => '1',
            'accept_passive_host_checks'                  => '1',
            'enable_event_handlers'                       => '1',
            'log_archive_path'                            => '@monitoring_varlog@/archives/',
            'check_external_commands'                     => '1',
            'external_command_buffer_slots'               => '4096',
            'command_check_interval'                      => '1s',
            'command_file'                                => '@monitoring_var_lib@/rw/centengine.cmd',
            'retain_state_information'                    => '1',
            'state_retention_file'                        => '@monitoring_varlog@/retention.dat',
            'retention_update_interval'                   => 60,
            'use_retained_program_state'                  => '1',
            'use_retained_scheduling_info'                => '1',
            'use_syslog'                                  => '0',
            'log_notifications'                           => '1',
            'log_service_retries'                         => '1',
            'log_host_retries'                            => '1',
            'log_event_handlers'                          => '1',
            'log_external_commands'                       => '1',
            'log_passive_checks'                          => '1',
            'global_host_event_handler'                   => null,
            'global_service_event_handler'                => null,
            'sleep_time'                                  => '0.5',
            'service_inter_check_delay_method'            => 's',
            'host_inter_check_delay_method'               => 's',
            'service_interleave_factor'                   => 's',
            'max_concurrent_checks'                       => 0,
            'max_service_check_spread'                    => 15,
            'max_host_check_spread'                       => 15,
            'check_result_reaper_frequency'               => 5,
            'auto_reschedule_checks'                      => '0',
            'auto_rescheduling_interval'                  => '30',
            'auto_rescheduling_window'                    => '180',
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
            'check_for_orphaned_services'                 => '1',
            'check_for_orphaned_hosts'                    => '1',
            'check_service_freshness'                     => '0',
            'service_freshness_check_interval'            => null,
            'freshness_check_interval'                    => null,
            'check_host_freshness'                        => '0',
            'host_freshness_check_interval'               => null,
            'date_format'                                 => 'euro',
            'illegal_object_name_chars'                   => '~!$%^&*"|\'<>?,()=',
            'illegal_macro_output_chars'                  => '`~$^&"|\'<>',
            'use_regexp_matching'                         => '0',
            'use_true_regexp_matching'                    => '0',
            'admin_email'                                 => 'admin@localhost',
            'admin_pager'                                 => 'admin@localhost',
            'nagios_comment'                              => 'Centreon Engine config file for a central instance',
            'nagios_activate'                             => '1',
            'event_broker_options'                        => '-1',
            'nagios_server_id'                            => 1,
            'enable_predictive_host_dependency_checks'    => '1',
            'enable_predictive_service_dependency_checks' => '1',
            'cached_host_check_horizon'                   => 60,
            'cached_service_check_horizon'                => null,
            'enable_environment_macros'                   => '0',
            'additional_freshness_latency'                => '15',
            'debug_file'                                  => '@monitoring_varlog@/centengine.debug',
            'debug_level'                                 => 0,
            'debug_level_opt'                             => '0',
            'debug_verbosity'                             => '1',
            'max_debug_file_size'                         => '1000000000',
            'daemon_dumps_core'                           => '0',
            'cfg_file'                                    => 'centengine.cfg',
            'log_pid'                                     => '1',
        ];
    }
}
