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

class Engine extends AbstractObject
{
    protected $engine = null;
    protected $table = 'cfg_nagios';
    protected $generate_filename = 'cfg_nagios.infile';

    # skipped nagios parameters : temp_file, nagios_user, nagios_group, log_rotation_method, log_archive_path,
    # lock_file, daemon_dumps_core
    protected $attributes_select = '
        nagios_server_id,
        nagios_id,
        use_timezone,
        cfg_dir,
        cfg_file as cfg_filename,
        log_file,
        status_file,
        check_result_path,
        use_check_result_path,
        max_check_result_file_age,
        status_update_interval,
        external_command_buffer_slots,
        command_check_interval,
        command_file,
        state_retention_file,
        retention_update_interval,
        retained_contact_host_attribute_mask,
        retained_contact_service_attribute_mask,
        retained_process_host_attribute_mask,
        retained_process_service_attribute_mask,
        retained_host_attribute_mask,
        retained_service_attribute_mask,
        sleep_time,
        service_inter_check_delay_method,
        host_inter_check_delay_method,
        service_interleave_factor,
        max_concurrent_checks,
        max_service_check_spread,
        max_host_check_spread,
        check_result_reaper_frequency,
        max_check_result_reaper_time,
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
        ocsp_timeout,
        ochp_timeout,
        perfdata_timeout,
        host_perfdata_file,
        service_perfdata_file,
        host_perfdata_file_template,
        service_perfdata_file_template,
        host_perfdata_file_processing_interval,
        service_perfdata_file_processing_interval,
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
        use_aggressive_host_checking,
        soft_state_dependencies,
        obsess_over_services,
        obsess_over_hosts,
        process_performance_data,
        host_perfdata_file_mode,
        service_perfdata_file_mode,
        check_for_orphaned_services,
        check_for_orphaned_hosts,
        check_service_freshness,
        check_host_freshness,
        use_regexp_matching,
        use_true_regexp_matching,
        enable_predictive_host_dependency_checks,
        enable_predictive_service_dependency_checks,
        use_large_installation_tweaks,
        enable_environment_macros,
        use_setpgid,
        enable_macros_filter,
        macros_filter,
        nagios_activate
    ';
    protected $attributes_write = array(
        'nagios_server_id',
        'use_timezone',
        'log_file',
        'status_file',
        'check_result_path',
        'use_check_result_path', //centengine
        'max_check_result_file_age',
        'status_update_interval',
        'external_command_buffer_slots',
        'command_check_interval',
        'command_file',
        'state_retention_file',
        'retention_update_interval',
        'retained_contact_host_attribute_mask',
        'retained_contact_service_attribute_mask',
        'retained_process_host_attribute_mask',
        'retained_process_service_attribute_mask',
        'retained_host_attribute_mask',
        'retained_service_attribute_mask',
        'sleep_time',
        'service_inter_check_delay_method',
        'host_inter_check_delay_method',
        'service_interleave_factor',
        'max_concurrent_checks',
        'max_service_check_spread',
        'max_host_check_spread',
        'check_result_reaper_frequency',
        'max_check_result_reaper_time',
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
        'ocsp_timeout',
        'ochp_timeout',
        'perfdata_timeout',
        'host_perfdata_file',
        'service_perfdata_file',
        'host_perfdata_file_template',
        'service_perfdata_file_template',
        'host_perfdata_file_processing_interval',
        'service_perfdata_file_processing_interval',
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
        'nagios_activate'
    );
    protected $stmt_engine = null;
    protected $stmt_broker = null;
    protected $stmt_interval_length = null;

    private function getBrokerModules()
    {
        if (is_null($this->stmt_broker)) {
            $this->stmt_broker = $this->backend_instance->db->prepare(
                "SELECT broker_module FROM cfg_nagios_broker_module " .
                "WHERE cfg_nagios_id = :id " .
                "ORDER BY bk_mod_id ASC"
            );
        }
        $this->stmt_broker->bindParam(':id', $this->engine['nagios_id'], PDO::PARAM_INT);
        $this->stmt_broker->execute();
        $this->engine['broker_module'] = $this->stmt_broker->fetchAll(PDO::FETCH_COLUMN);
    }

    private function getIntervalLength()
    {
        if (is_null($this->stmt_interval_length)) {
            $this->stmt_interval_length = $this->backend_instance->db->prepare(
                "SELECT `value` FROM options " .
                "WHERE `key` = 'interval_length'"
            );
        }
        $this->stmt_interval_length->execute();
        $this->engine['interval_length'] = $this->stmt_interval_length->fetchAll(PDO::FETCH_COLUMN);
    }

    private function generate($poller_id)
    {
        if (is_null($this->stmt_engine)) {
            $this->stmt_engine = $this->backend_instance->db->prepare(
                "SELECT $this->attributes_select FROM cfg_nagios " .
                "WHERE nagios_server_id = :poller_id AND nagios_activate = '1'"
            );
        }
        $this->stmt_engine->bindParam(':poller_id', $poller_id, PDO::PARAM_INT);
        $this->stmt_engine->execute();

        $result = $this->stmt_engine->fetchAll(PDO::FETCH_ASSOC);
        $this->engine = array_pop($result);
        if (is_null($this->engine)) {
            throw new Exception(
                "Cannot get engine configuration for poller id (maybe not activate) '" . $poller_id . "'"
            );
        }
        
        $this->generateObjectInFile(
            $this->engine,
            $poller_id
        );
    }

    public function generateFromPoller($poller)
    {
        Resource::getInstance($this->dependencyInjector)->generateFromPollerId($poller['id']);
        $this->generate($poller['id']);
    }

    public function reset($createfile=false)
    {
        parent::reset($createfile);
    }
}
