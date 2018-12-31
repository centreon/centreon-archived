<?php

namespace CentreonRemote\Domain\Resources\RemoteConfig\BrokerInfo;

/**
 * Get broker configuration template
 */
class OutputStatus
{
    /**
     * Get template configuration
     * @todo move it as yml
     *
     * @return array the configuration template
     */
    public static function getConfiguration(): array
    {
        return [
            [
                'config_key'      => 'name',
                'config_value'    => 'Status-Master',
                'config_group'    => 'output',
                'config_group_id' => '1',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'retry_interval',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => '1',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'buffering_timeout',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => '1',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'failover',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => '1',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'db_type',
                'config_value'    => 'mysql',
                'config_group'    => 'output',
                'config_group_id' => '1',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'db_host',
                'config_value'    => 'localhost',
                'config_group'    => 'output',
                'config_group_id' => '1',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'db_port',
                'config_value'    => '3306',
                'config_group'    => 'output',
                'config_group_id' => '1',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'db_user',
                'config_value'    => 'centreon',
                'config_group'    => 'output',
                'config_group_id' => '1',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'db_password',
                'config_value'    => 'FDuM1710',
                'config_group'    => 'output',
                'config_group_id' => '1',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'db_name',
                'config_value'    => 'centreon_storage',
                'config_group'    => 'output',
                'config_group_id' => '1',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'queries_per_transaction',
                'config_value'    => '400',
                'config_group'    => 'output',
                'config_group_id' => '1',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'read_timeout',
                'config_value'    => '5',
                'config_group'    => 'output',
                'config_group_id' => '1',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'check_replication',
                'config_value'    => 'no',
                'config_group'    => 'output',
                'config_group_id' => '1',
                'grp_level'       => '0',
            ],

            [
                'config_key'      => 'cleanup_check_interval',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => '1',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'instance_timeout',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => '1',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'filters',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => '1',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'category',
                'config_value'    => 'neb',
                'config_group'    => 'output',
                'config_group_id' => '1',
                'grp_level'       => '1',
            ],
            [
                'config_key'      => 'category',
                'config_value'    => 'correlation',
                'config_group'    => 'output',
                'config_group_id' => '1',
                'grp_level'       => '1',
            ],
            [
                'config_key'      => 'type',
                'config_value'    => 'sql',
                'config_group'    => 'output',
                'config_group_id' => '1',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'blockId',
                'config_value'    => '1_16',
                'config_group'    => 'output',
                'config_group_id' => '1',
                'grp_level'       => '0',
            ],
        ];
    }
}
