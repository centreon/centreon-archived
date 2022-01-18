<?php

/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
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

namespace ConfigGenerateRemote;

use \Exception;
use \PDO;
use ConfigGenerateRemote\Abstracts\AbstractObject;

class Engine extends AbstractObject
{
    protected $engine = null;
    protected $table = 'cfg_nagios';
    protected $generateFilename = 'cfg_nagios.infile';

    //skipped nagios parameters : temp_file, nagios_user, nagios_group, log_rotation_method, log_archive_path,
    //lock_file, daemon_dumps_core
    protected $attributesSelect = '
        nagios_server_id,
        nagios_id,
        nagios_name,
        use_timezone,
        cfg_dir,
        cfg_file,
        log_file,
        log_archive_path,
        status_file,
        status_update_interval,
        external_command_buffer_slots,
        command_check_interval,
        command_file,
        state_retention_file,
        retention_update_interval,
        sleep_time,
        service_inter_check_delay_method,
        host_inter_check_delay_method,
        service_interleave_factor,
        max_concurrent_checks,
        max_service_check_spread,
        max_host_check_spread,
        check_result_reaper_frequency,
        auto_rescheduling_interval,
        auto_rescheduling_window,
        enable_flap_detection,
        low_service_flap_threshold,
        high_service_flap_threshold,
        low_host_flap_threshold,
        high_host_flap_threshold,
        service_check_timeout,
        host_check_timeout,
        event_handler_timeout,
        notification_timeout,
        service_freshness_check_interval,
        host_freshness_check_interval,
        date_format,
        illegal_object_name_chars,
        illegal_macro_output_chars,
        admin_email,
        admin_pager,
        event_broker_options,
        translate_passive_host_checks,
        cached_host_check_horizon,
        cached_service_check_horizon,
        passive_host_checks_are_soft,
        additional_freshness_latency,
        debug_file,
        debug_level,
        debug_level_opt,
        debug_verbosity,
        max_debug_file_size,
        log_pid,
        enable_notifications,
        execute_service_checks,
        accept_passive_service_checks,
        execute_host_checks,
        accept_passive_host_checks,
        enable_event_handlers,
        check_external_commands,
        use_retained_program_state,
        use_retained_scheduling_info,
        use_syslog,
        log_notifications,
        log_service_retries,
        log_host_retries,
        log_event_handlers,
        log_external_commands,
        log_passive_checks,
        auto_reschedule_checks,
        soft_state_dependencies,
        check_for_orphaned_services,
        check_for_orphaned_hosts,
        check_service_freshness,
        check_host_freshness,
        use_regexp_matching,
        use_true_regexp_matching,
        enable_predictive_host_dependency_checks,
        enable_predictive_service_dependency_checks,
        enable_environment_macros,
        enable_macros_filter,
        macros_filter,
        nagios_activate
    ';
    protected $attributesWrite = [
        'nagios_server_id',
        'nagios_id',
        'nagios_name',
        'use_timezone',
        'log_file',
        'log_archive_path',
        'status_file',
        'status_update_interval',
        'external_command_buffer_slots',
        'command_check_interval',
        'command_file',
        'state_retention_file',
        'retention_update_interval',
        'sleep_time',
        'service_inter_check_delay_method',
        'host_inter_check_delay_method',
        'service_interleave_factor',
        'max_concurrent_checks',
        'max_service_check_spread',
        'max_host_check_spread',
        'check_result_reaper_frequency',
        'auto_rescheduling_interval',
        'auto_rescheduling_window',
        'low_service_flap_threshold',
        'high_service_flap_threshold',
        'low_host_flap_threshold',
        'high_host_flap_threshold',
        'service_check_timeout',
        'host_check_timeout',
        'event_handler_timeout',
        'notification_timeout',
        'service_freshness_check_interval',
        'host_freshness_check_interval',
        'date_format',
        'illegal_object_name_chars',
        'illegal_macro_output_chars',
        'admin_email',
        'admin_pager',
        'event_broker_options',
        'translate_passive_host_checks',
        'cached_host_check_horizon',
        'cached_service_check_horizon',
        'passive_host_checks_are_soft',
        'additional_freshness_latency',
        'debug_file',
        'debug_level',
        'debug_verbosity',
        'max_debug_file_size',
        'log_pid', // centengine
        'macros_filter',
        'enable_macros_filter',
        'nagios_activate',
        'cfg_dir',
        'cfg_file'
    ];
    protected $stmtEngine = null;

    /**
     * Generate engine configuration from poller id
     *
     * @param int $poller
     * @return void
     */
    private function generate(int $pollerId)
    {
        if (is_null($this->stmtEngine)) {
            $this->stmtEngine = $this->backendInstance->db->prepare(
                "SELECT $this->attributesSelect FROM cfg_nagios " .
                "WHERE nagios_server_id = :poller_id AND nagios_activate = '1'"
            );
        }
        $this->stmtEngine->bindParam(':poller_id', $pollerId, PDO::PARAM_INT);
        $this->stmtEngine->execute();

        $result = $this->stmtEngine->fetchAll(PDO::FETCH_ASSOC);
        $this->engine = array_pop($result);
        if (is_null($this->engine)) {
            throw new Exception(
                "Cannot get engine configuration for poller id (maybe not activate) '" . $pollerId . "'"
            );
        }

        $this->generateObjectInFile(
            $this->engine,
            $pollerId
        );
    }

    /**
     * Generate engine configuration from poller
     *
     * @param array $poller
     * @return void
     */
    public function generateFromPoller(array $poller)
    {
        Resource::getInstance($this->dependencyInjector)->generateFromPollerId($poller['id']);
        $this->generate($poller['id']);
    }
}
