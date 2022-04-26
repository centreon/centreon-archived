<?php

namespace CentreonRemote\Domain\Resources\DefaultConfig;

/**
 * Get broker configuration template
 */
class CfgNagiosLogger
{
    /**
     * Get template configuration
     *
     * @return array<string,string|int>
     */
    public static function getConfiguration(): array
    {
        return [
            'cfg_nagios_id' => 1,
            'log_v2_logger' => 'file',
            'log_level_functions' => 'error',
            'log_level_config' => 'info',
            'log_level_events' => 'info',
            'log_level_checks' => 'info',
            'log_level_notifications' => 'error',
            'log_level_eventbroker' => 'error',
            'log_level_external_command' => 'error',
            'log_level_commands' => 'error',
            'log_level_downtimes' => 'error',
            'log_level_comments' => 'error',
            'log_level_macros' => 'error',
            'log_level_process' => 'info',
            'log_level_runtime' => 'error',
        ];
    }
}
