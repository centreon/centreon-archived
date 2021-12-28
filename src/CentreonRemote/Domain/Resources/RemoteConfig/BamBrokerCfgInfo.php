<?php

namespace CentreonRemote\Domain\Resources\RemoteConfig;

/**
 * Get broker configuration template
 */
class BamBrokerCfgInfo
{
    /**
     * Get template configuration
     * @todo move it as yml
     *
     * @param string $dbPassword the centreon database password
     * @return array<string, array<int, string[]>> the configuration template
     */
    public static function getConfiguration(string $dbPassword)
    {
        return [
            'monitoring' => [
                [
                    'config_key'      => 'name',
                    'config_value'    => 'poller-bam-monitoring',
                    'config_group'    => 'output',
                    'config_group_id' => '4',
                    'grp_level'       => '0',
                ],
                [
                    'config_key'      => 'cache',
                    'config_value'    => 'yes',
                    'config_group'    => 'output',
                    'config_group_id' => '4',
                    'grp_level'       => '0',
                ],
                [
                    'config_key'      => 'check_replication',
                    'config_value'    => 'no',
                    'config_group'    => 'output',
                    'config_group_id' => '4',
                    'grp_level'       => '0',
                ],
                [
                    'config_key'      => 'command_file',
                    'config_value'    => '/var/lib/centreon-engine/rw/centengine.cmd',
                    'config_group'    => 'output',
                    'config_group_id' => '4',
                    'grp_level'       => '0',
                ],
                [
                    'config_key'      => 'db_host',
                    'config_value'    => 'localhost',
                    'config_group'    => 'output',
                    'config_group_id' => '4',
                    'grp_level'       => '0',
                ],
                [
                    'config_key'      => 'db_name',
                    'config_value'    => 'centreon',
                    'config_group'    => 'output',
                    'config_group_id' => '4',
                    'grp_level'       => '0',
                ],
                [
                    'config_key'      => 'db_password',
                    'config_value'    => $dbPassword,
                    'config_group'    => 'output',
                    'config_group_id' => '4',
                    'grp_level'       => '0',
                ],
                [
                    'config_key'      => 'db_user',
                    'config_value'    => 'centreon',
                    'config_group'    => 'output',
                    'config_group_id' => '4',
                    'grp_level'       => '0',
                ],
                [
                    'config_key'      => 'db_port',
                    'config_value'    => '3306',
                    'config_group'    => 'output',
                    'config_group_id' => '4',
                    'grp_level'       => '0',
                ],
                [
                    'config_key'      => 'db_type',
                    'config_value'    => 'mysql',
                    'config_group'    => 'output',
                    'config_group_id' => '4',
                    'grp_level'       => '0',
                ],
                [
                    'config_key'      => 'queries_per_transaction',
                    'config_value'    => '0',
                    'config_group'    => 'output',
                    'config_group_id' => '4',
                    'grp_level'       => '0',
                ],
                [
                    'config_key'      => 'read_timeout',
                    'config_value'    => '',
                    'config_group'    => 'output',
                    'config_group_id' => '4',
                    'grp_level'       => '0',
                ],
                [
                    'config_key'      => 'retry_interval',
                    'config_value'    => '',
                    'config_group'    => 'output',
                    'config_group_id' => '4',
                    'grp_level'       => '0',
                ],
                [
                    'config_key'      => 'storage_db_name',
                    'config_value'    => '',
                    'config_group'    => 'output',
                    'config_group_id' => '4',
                    'grp_level'       => '0',
                ],
                [
                    'config_key'      => 'type',
                    'config_value'    => 'bam',
                    'config_group'    => 'output',
                    'config_group_id' => '4',
                    'grp_level'       => '0',
                ],
                [
                    'config_key'      => 'blockId',
                    'config_value'    => '1_34',
                    'config_group'    => 'output',
                    'config_group_id' => '4',
                    'grp_level'       => '0',
                ],
            ],
            'reporting' => [
                [
                    'config_key'      => 'name',
                    'config_value'    => 'poller-bam-reporting',
                    'config_group'    => 'output',
                    'config_group_id' => '5',
                    'grp_level'       => '0',
                ],
                [
                    'config_key'      => 'filters',
                    'config_value'    => '',
                    'config_group'    => 'output',
                    'config_group_id' => '5',
                    'grp_level'       => '0',
                ],
                [
                    'config_key'      => 'category',
                    'config_value'    => 'bam',
                    'config_group'    => 'output',
                    'config_group_id' => '5',
                    'grp_level'       => '0',
                ],
                [
                    'config_key'      => 'check_replication',
                    'config_value'    => 'no',
                    'config_group'    => 'output',
                    'config_group_id' => '5',
                    'grp_level'       => '0',
                ],
                [
                    'config_key'      => 'db_host',
                    'config_value'    => 'localhost',
                    'config_group'    => 'output',
                    'config_group_id' => '5',
                    'grp_level'       => '0',
                ],
                [
                    'config_key'      => 'db_name',
                    'config_value'    => 'centreon_storage',
                    'config_group'    => 'output',
                    'config_group_id' => '5',
                    'grp_level'       => '0',
                ],
                [
                    'config_key'      => 'db_password',
                    'config_value'    => $dbPassword,
                    'config_group'    => 'output',
                    'config_group_id' => '5',
                    'grp_level'       => '0',
                ],
                [
                    'config_key'      => 'db_user',
                    'config_value'    => 'centreon',
                    'config_group'    => 'output',
                    'config_group_id' => '5',
                    'grp_level'       => '0',
                ],
                [
                    'config_key'      => 'db_port',
                    'config_value'    => '3306',
                    'config_group'    => 'output',
                    'config_group_id' => '5',
                    'grp_level'       => '0',
                ],
                [
                    'config_key'      => 'db_type',
                    'config_value'    => 'mysql',
                    'config_group'    => 'output',
                    'config_group_id' => '5',
                    'grp_level'       => '0',
                ],
                [
                    'config_key'      => 'queries_per_transaction',
                    'config_value'    => '0',
                    'config_group'    => 'output',
                    'config_group_id' => '5',
                    'grp_level'       => '0',
                ],
                [
                    'config_key'      => 'read_timeout',
                    'config_value'    => '',
                    'config_group'    => 'output',
                    'config_group_id' => '5',
                    'grp_level'       => '0',
                ],
                [
                    'config_key'      => 'retry_interval',
                    'config_value'    => '',
                    'config_group'    => 'output',
                    'config_group_id' => '5',
                    'grp_level'       => '0',
                ],
                [
                    'config_key'      => 'type',
                    'config_value'    => 'bam_bi',
                    'config_group'    => 'output',
                    'config_group_id' => '5',
                    'grp_level'       => '0',
                ],
                [
                    'config_key'      => 'blockId',
                    'config_value'    => '1_35',
                    'config_group'    => 'output',
                    'config_group_id' => '5',
                    'grp_level'       => '0',
                ],
            ]
        ];
    }
}
