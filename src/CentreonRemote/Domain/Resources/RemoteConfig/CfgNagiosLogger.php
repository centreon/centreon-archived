<?php

namespace CentreonRemote\Domain\Resources\RemoteConfig;

/**
 * Get broker configuration template
 */
class CfgNagiosLogger
{
    /**
     * Get template configuration
     *
     * @param int $nagiosId
     * @return array<string,string|int>
     */
    public static function getConfiguration(int $nagiosId): array
    {
        return [
            'cfg_nagios_id' => $nagiosId,
            'log_v2_logger' => 'syslog',
            'log_level_functions' => 'warning',
            'log_level_config' => 'warning',
            'log_level_events' => 'warning',
            'log_level_checks' => 'warning',
            'log_level_notifications' => 'warning',
            'log_level_eventbroker' => 'warning',
            'log_level_external_command' => 'warning',
            'log_level_commands' => 'warning',
            'log_level_downtimes' => 'warning',
            'log_level_comments' => 'warning',
            'log_level_macros' => 'warning',
            'log_level_process' => 'warning',
            'log_level_runtime' => 'warning',
        ];
    }
}
