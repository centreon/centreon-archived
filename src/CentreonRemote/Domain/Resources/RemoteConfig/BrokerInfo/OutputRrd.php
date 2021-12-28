<?php

namespace CentreonRemote\Domain\Resources\RemoteConfig\BrokerInfo;

/**
 * Get broker configuration template
 */
class OutputRrd
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
                'config_key'      => 'name',
                'config_value'    => 'central-rrd-master-output',
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'metrics_path',
                'config_value'    => '/var/lib/centreon/metrics/',
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
                'config_key'      => 'status_path',
                'config_value'    => '/var/lib/centreon/status/',
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
                'config_key'      => 'path',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'port',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'write_metrics',
                'config_value'    => 'yes',
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'write_status',
                'config_value'    => 'yes',
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'store_in_data_bin',
                'config_value'    => 'yes',
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'insert_in_index_data',
                'config_value'    => '1',
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'type',
                'config_value'    => 'rrd',
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'blockId',
                'config_value'    => '1_13',
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
        ];
    }
}
