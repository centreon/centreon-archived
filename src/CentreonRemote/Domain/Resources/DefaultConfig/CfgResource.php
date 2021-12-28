<?php

namespace CentreonRemote\Domain\Resources\DefaultConfig;

/**
 * Get broker configuration template
 */
class CfgResource
{
    /**
     * Get template configuration
     * @todo move it as yml
     *
     * @return array<int, string[]> the configuration template
     */
    public static function getConfiguration()
    {
        return [
            [
                'resource_name'     => '$USER1$',
                'resource_line'     => '@plugin_dir@',
                'resource_comment'  => 'Nagios Plugins Path',
                'resource_activate' => '1',
            ],
            [
                'resource_name'     => '$CENTREONPLUGINS$',
                'resource_line'     => '@centreonplugins@',
                'resource_comment'  => 'Centreon Plugins Path',
                'resource_activate' => '1'
            ]
        ];
    }
}
