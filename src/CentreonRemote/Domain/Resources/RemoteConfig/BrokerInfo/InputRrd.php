<?php

namespace CentreonRemote\Domain\Resources\RemoteConfig\BrokerInfo;

/**
 * Get broker configuration template
 */
class InputRrd
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
                'config_value'    => 'central-rrd-master-input',
                'config_group'    => 'input',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'port',
                'config_value'    => '5670',
                'config_group'    => 'input',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'host',
                'config_value'    => '',
                'config_group'    => 'input',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'failover',
                'config_value'    => '',
                'config_group'    => 'input',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'retry_interval',
                'config_value'    => '15',
                'config_group'    => 'input',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'buffering_timeout',
                'config_value'    => '0',
                'config_group'    => 'input',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'protocol',
                'config_value'    => 'bbdo',
                'config_group'    => 'input',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'tls',
                'config_value'    => 'auto',
                'config_group'    => 'input',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'private_key',
                'config_value'    => '',
                'config_group'    => 'input',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'public_cert',
                'config_value'    => '',
                'config_group'    => 'input',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'ca_certificate',
                'config_value'    => '',
                'config_group'    => 'input',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'negociation',
                'config_value'    => 'yes',
                'config_group'    => 'input',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'one_peer_retention_mode',
                'config_value'    => 'no',
                'config_group'    => 'input',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'compression',
                'config_value'    => 'auto',
                'config_group'    => 'input',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'compression_level',
                'config_value'    => '',
                'config_group'    => 'input',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'compression_buffer',
                'config_value'    => '',
                'config_group'    => 'input',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'type',
                'config_value'    => 'ipv4',
                'config_group'    => 'input',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'blockId',
                'config_value'    => '2_3',
                'config_group'    => 'input',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
        ];
    }
}
