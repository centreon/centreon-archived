<?php

namespace CentreonRemote\Domain\Resources\RemoteConfig\BrokerInfo;

/**
 * Get broker configuration template
 */
class LoggerSql
{
    /**
     * Get template configuration
     * @todo move it as yml
     *
     * @return array<int, string[]> the configuration template
     */
    public static function getConfiguration(): array
    {
        return [
            [
                'config_key'      => 'name',
                'config_value'    => '/var/log/centreon-broker/broker-sql.log',
                'config_group'    => 'logger',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'config',
                'config_value'    => 'yes',
                'config_group'    => 'logger',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'debug',
                'config_value'    => 'no',
                'config_group'    => 'logger',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'error',
                'config_value'    => 'yes',
                'config_group'    => 'logger',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'info',
                'config_value'    => 'no',
                'config_group'    => 'logger',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'level',
                'config_value'    => 'low',
                'config_group'    => 'logger',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'max_size',
                'config_value'    => '',
                'config_group'    => 'logger',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'type',
                'config_value'    => 'file',
                'config_group'    => 'logger',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'blockId',
                'config_value'    => '3_17',
                'config_group'    => 'logger',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
        ];
    }
}
