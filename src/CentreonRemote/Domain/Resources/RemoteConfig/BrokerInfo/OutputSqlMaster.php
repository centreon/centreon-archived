<?php

namespace CentreonRemote\Domain\Resources\RemoteConfig\BrokerInfo;

/**
 * Get broker configuration template
 */
class OutputSqlMaster
{
    /**
     * Get template configuration
     * @todo move it as yml
     *
     * @param string|null $dbUser the database user
     * @param string|null $dbPassword the database password
     * @return array<int, string[]> the configuration template
     */
    public static function getConfiguration($dbUser, $dbPassword): array
    {
        return [
            [
                'config_key'      => 'name',
                'config_value'    => 'central-broker-master-sql',
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'retry_interval',
                'config_value'    => '15',
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'buffering_timeout',
                'config_value'    => '0',
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'failover',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'db_type',
                'config_value'    => 'mysql',
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'db_host',
                'config_value'    => 'localhost',
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'db_port',
                'config_value'    => '3306',
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'db_user',
                'config_value'    => $dbUser,
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'db_password',
                'config_value'    => $dbPassword,
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'db_name',
                'config_value'    => 'centreon_storage',
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'queries_per_transaction',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'read_timeout',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'check_replication',
                'config_value'    => 'no',
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'cleanup_check_interval',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'instance_timeout',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'type',
                'config_value'    => 'sql',
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'blockId',
                'config_value'    => '1_16',
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
        ];
    }
}
