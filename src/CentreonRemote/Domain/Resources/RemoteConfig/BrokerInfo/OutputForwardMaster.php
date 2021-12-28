<?php

namespace CentreonRemote\Domain\Resources\RemoteConfig\BrokerInfo;

/**
 * Get broker configuration template
 */
class OutputForwardMaster
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
                'config_value'    => 'forward-to-master',
                'config_group'    => 'output',
                'config_group_id' => '3',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'port',
                'config_value'    => '5669',
                'config_group'    => 'output',
                'config_group_id' => '3',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'host',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => '3',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'failover',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => '3',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'retry_interval',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => '3',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'buffering_timeout',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => '3',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'protocol',
                'config_value'    => 'bbdo',
                'config_group'    => 'output',
                'config_group_id' => '3',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'tls',
                'config_value'    => 'no',
                'config_group'    => 'output',
                'config_group_id' => '3',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'private_key',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => '3',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'public_cert',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => '3',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'ca_certificate',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => '3',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'negociation',
                'config_value'    => 'yes',
                'config_group'    => 'output',
                'config_group_id' => '3',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'one_peer_retention_mode',
                'config_value'    => 'no',
                'config_group'    => 'output',
                'config_group_id' => '3',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'compression',
                'config_value'    => 'no',
                'config_group'    => 'output',
                'config_group_id' => '3',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'compression_level',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => '3',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'compression_buffer',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => '3',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'type',
                'config_value'    => 'ipv4',
                'config_group'    => 'output',
                'config_group_id' => '3',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'blockId',
                'config_value'    => '1_3',
                'config_group'    => 'output',
                'config_group_id' => '3',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'filters',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => '3',
                'grp_level'       => '0',
                'subgrp_id'       => '1',
            ],
            [
                'config_key'      => 'category',
                'config_value'    => 'neb',
                'config_group'    => 'output',
                'config_group_id' => '3',
                'grp_level'       => '1',
                'parent_grp_id'   => '1',
            ],
        ];
    }
}
