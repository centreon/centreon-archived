<?php
namespace CentreonRemote\Domain\Resources\DefaultConfig;

/**
 * Get broker configuration template
 */
class CfgCentreonBrokerInfo
{
    /**
     * Get template configuration
     * @todo move it as yml
     *
     * @return array<int, array<string,int|string>> the configuration template
     */
    public static function getConfiguration(): array
    {
        return [
            [
                'config_id'       => 1,
                'config_key'      => 'name',
                'config_value'    => 'central-broker-master-input',
                'config_group'    => 'input',
                'config_group_id' => 1,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'port',
                'config_value'    => '5669',
                'config_group'    => 'input',
                'config_group_id' => 1,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'buffering_timeout',
                'config_value'    => '0',
                'config_group'    => 'input',
                'config_group_id' => 1,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'host',
                'config_value'    => '',
                'config_group'    => 'input',
                'config_group_id' => 1,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'retry_interval',
                'config_value'    => '15',
                'config_group'    => 'input',
                'config_group_id' => 1,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'protocol',
                'config_value'    => 'bbdo',
                'config_group'    => 'input',
                'config_group_id' => 1,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'tls',
                'config_value'    => 'auto',
                'config_group'    => 'input',
                'config_group_id' => 1,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'private_key',
                'config_value'    => '',
                'config_group'    => 'input',
                'config_group_id' => 1,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'public_cert',
                'config_value'    => '',
                'config_group'    => 'input',
                'config_group_id' => 1,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'ca_certificate',
                'config_value'    => '',
                'config_group'    => 'input',
                'config_group_id' => 1,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'negotiation',
                'config_value'    => 'yes',
                'config_group'    => 'input',
                'config_group_id' => 1,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'one_peer_retention_mode',
                'config_value'    => 'no',
                'config_group'    => 'input',
                'config_group_id' => 1,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'compression',
                'config_value'    => 'auto',
                'config_group'    => 'input',
                'config_group_id' => 1,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'compression_level',
                'config_value'    => '',
                'config_group'    => 'input',
                'config_group_id' => 1,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'compression_buffer',
                'config_value'    => '',
                'config_group'    => 'input',
                'config_group_id' => 1,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'type',
                'config_value'    => 'ipv4',
                'config_group'    => 'input',
                'config_group_id' => 1,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'blockId',
                'config_value'    => '2_3',
                'config_group'    => 'input',
                'config_group_id' => 1,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'name',
                'config_value'    => '@centreonbroker_log@/central-broker-master.log',
                'config_group'    => 'logger',
                'config_group_id' => 1,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'config',
                'config_value'    => 'yes',
                'config_group'    => 'logger',
                'config_group_id' => 1,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'debug',
                'config_value'    => 'no',
                'config_group'    => 'logger',
                'config_group_id' => 1,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'error',
                'config_value'    => 'yes',
                'config_group'    => 'logger',
                'config_group_id' => 1,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'info',
                'config_value'    => 'no',
                'config_group'    => 'logger',
                'config_group_id' => 1,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'level',
                'config_value'    => 'low',
                'config_group'    => 'logger',
                'config_group_id' => 1,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'max_size',
                'config_value'    => '',
                'config_group'    => 'logger',
                'config_group_id' => 1,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'type',
                'config_value'    => 'file',
                'config_group'    => 'logger',
                'config_group_id' => 1,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'blockId',
                'config_value'    => '3_17',
                'config_group'    => 'logger',
                'config_group_id' => 1,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'name',
                'config_value'    => 'central-broker-master-sql',
                'config_group'    => 'output',
                'config_group_id' => 1,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'db_type',
                'config_value'    => 'mysql',
                'config_group'    => 'output',
                'config_group_id' => 1,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'retry_interval',
                'config_value'    => '15',
                'config_group'    => 'output',
                'config_group_id' => 1,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'buffering_timeout',
                'config_value'    => '0',
                'config_group'    => 'output',
                'config_group_id' => 1,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'db_host',
                'config_value'    => '@address@',
                'config_group'    => 'output',
                'config_group_id' => 1,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'db_port',
                'config_value'    => '@port@',
                'config_group'    => 'output',
                'config_group_id' => 1,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'db_user',
                'config_value'    => '@db_user@',
                'config_group'    => 'output',
                'config_group_id' => 1,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'db_password',
                'config_value'    => '@db_password@',
                'config_group'    => 'output',
                'config_group_id' => 1,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'db_name',
                'config_value'    => '@db_storage@',
                'config_group'    => 'output',
                'config_group_id' => 1,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'queries_per_transaction',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => 1,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'read_timeout',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => 1,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'type',
                'config_value'    => 'sql',
                'config_group'    => 'output',
                'config_group_id' => 1,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'blockId',
                'config_value'    => '1_16',
                'config_group'    => 'output',
                'config_group_id' => 1,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'name',
                'config_value'    => 'centreon-broker-master-rrd',
                'config_group'    => 'output',
                'config_group_id' => 2,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'port',
                'config_value'    => '5670',
                'config_group'    => 'output',
                'config_group_id' => 2,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'buffering_timeout',
                'config_value'    => '0',
                'config_group'    => 'output',
                'config_group_id' => 2,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'host',
                'config_value'    => 'localhost',
                'config_group'    => 'output',
                'config_group_id' => 2,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'retry_interval',
                'config_value'    => '15',
                'config_group'    => 'output',
                'config_group_id' => 2,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'protocol',
                'config_value'    => 'bbdo',
                'config_group'    => 'output',
                'config_group_id' => 2,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'tls',
                'config_value'    => 'no',
                'config_group'    => 'output',
                'config_group_id' => 2,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'private_key',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => 2,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'public_cert',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => 2,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'ca_certificate',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => 2,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'negotiation',
                'config_value'    => 'yes',
                'config_group'    => 'output',
                'config_group_id' => 2,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'one_peer_retention_mode',
                'config_value'    => 'no',
                'config_group'    => 'output',
                'config_group_id' => 2,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'compression',
                'config_value'    => 'no',
                'config_group'    => 'output',
                'config_group_id' => 2,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'compression_level',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => 2,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'compression_buffer',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => 2,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'type',
                'config_value'    => 'ipv4',
                'config_group'    => 'output',
                'config_group_id' => 2,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'blockId',
                'config_value'    => '1_3',
                'config_group'    => 'output',
                'config_group_id' => 2,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'name',
                'config_value'    => 'central-broker-master-perfdata',
                'config_group'    => 'output',
                'config_group_id' => 3,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'interval',
                'config_value'    => '60',
                'config_group'    => 'output',
                'config_group_id' => 3,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'retry_interval',
                'config_value'    => '15',
                'config_group'    => 'output',
                'config_group_id' => 3,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'buffering_timeout',
                'config_value'    => '0',
                'config_group'    => 'output',
                'config_group_id' => 3,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'length',
                'config_value'    => '15552000',
                'config_group'    => 'output',
                'config_group_id' => 3,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'db_type',
                'config_value'    => 'mysql',
                'config_group'    => 'output',
                'config_group_id' => 3,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'db_host',
                'config_value'    => '@address@',
                'config_group'    => 'output',
                'config_group_id' => 3,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'db_port',
                'config_value'    => '@port@',
                'config_group'    => 'output',
                'config_group_id' => 3,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'db_user',
                'config_value'    => '@db_user@',
                'config_group'    => 'output',
                'config_group_id' => 3,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'db_password',
                'config_value'    => '@db_password@',
                'config_group'    => 'output',
                'config_group_id' => 3,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'db_name',
                'config_value'    => '@db_storage@',
                'config_group'    => 'output',
                'config_group_id' => 3,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'queries_per_transaction',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => 3,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'read_timeout',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => 3,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'check_replication',
                'config_value'    => 'no',
                'config_group'    => 'output',
                'config_group_id' => 3,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'rebuild_check_interval',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => 3,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'store_in_data_bin',
                'config_value'    => 'yes',
                'config_group'    => 'output',
                'config_group_id' => 3,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'insert_in_index_data',
                'config_value'    => '1',
                'config_group'    => 'output',
                'config_group_id' => 3,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'type',
                'config_value'    => 'storage',
                'config_group'    => 'output',
                'config_group_id' => 3,
            ],
            [
                'config_id'       => 1,
                'config_key'      => 'blockId',
                'config_value'    => '1_14',
                'config_group'    => 'output',
                'config_group_id' => 3,
            ],

            /////////////////////////////////////////////////////////////////////////////////////
            /////////////////////////////////////////////////////////////////////////////////////
            /////////////////////////////////////////////////////////////////////////////////////
            /////////////////////////////////////////////////////////////////////////////////////
            /////////////////////////////////////////////////////////////////////////////////////

            [
                'config_id'       => 2,
                'config_key'      => 'name',
                'config_value'    => 'central-rrd-master-input',
                'config_group'    => 'input',
                'config_group_id' => 1
            ],
            [

                'config_id'       => 2,
                'config_key'      => 'port',
                'config_value'    => '5670',
                'config_group'    => 'input',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 2,
                'config_key'      => 'buffering_timeout',
                'config_value'    => '0',
                'config_group'    => 'input',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 2,
                'config_key'      => 'host',
                'config_value'    => '',
                'config_group'    => 'input',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 2,
                'config_key'      => 'retry_interval',
                'config_value'    => '15',
                'config_group'    => 'input',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 2,
                'config_key'      => 'protocol',
                'config_value'    => 'bbdo',
                'config_group'    => 'input',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 2,
                'config_key'      => 'tls',
                'config_value'    => 'auto',
                'config_group'    => 'input',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 2,
                'config_key'      => 'private_key',
                'config_value'    => '',
                'config_group'    => 'input',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 2,
                'config_key'      => 'public_cert',
                'config_value'    => '',
                'config_group'    => 'input',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 2,
                'config_key'      => 'ca_certificate',
                'config_value'    => '',
                'config_group'    => 'input',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 2,
                'config_key'      => 'negotiation',
                'config_value'    => 'yes',
                'config_group'    => 'input',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 2,
                'config_key'      => 'one_peer_retention_mode',
                'config_value'    => 'no',
                'config_group'    => 'input',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 2,
                'config_key'      => 'compression',
                'config_value'    => 'auto',
                'config_group'    => 'input',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 2,
                'config_key'      => 'compression_level',
                'config_value'    => '',
                'config_group'    => 'input',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 2,
                'config_key'      => 'compression_buffer',
                'config_value'    => '',
                'config_group'    => 'input',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 2,
                'config_key'      => 'type',
                'config_value'    => 'ipv4',
                'config_group'    => 'input',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 2,
                'config_key'      => 'blockId',
                'config_value'    => '2_3',
                'config_group'    => 'input',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 2,
                'config_key'      => 'name',
                'config_value'    => '@centreonbroker_log@/central-rrd-master.log',
                'config_group'    => 'logger',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 2,
                'config_key'      => 'config',
                'config_value'    => 'yes',
                'config_group'    => 'logger',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 2,
                'config_key'      => 'debug',
                'config_value'    => 'no',
                'config_group'    => 'logger',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 2,
                'config_key'      => 'error',
                'config_value'    => 'yes',
                'config_group'    => 'logger',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 2,
                'config_key'      => 'info',
                'config_value'    => 'no',
                'config_group'    => 'logger',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 2,
                'config_key'      => 'level',
                'config_value'    => 'low',
                'config_group'    => 'logger',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 2,
                'config_key'      => 'max_size',
                'config_value'    => '',
                'config_group'    => 'logger',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 2,
                'config_key'      => 'type',
                'config_value'    => 'file',
                'config_group'    => 'logger',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 2,
                'config_key'      => 'blockId',
                'config_value'    => '3_17',
                'config_group'    => 'logger',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 2,
                'config_key'      => 'name',
                'config_value'    => 'central-rrd-master-output',
                'config_group'    => 'output',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 2,
                'config_key'      => 'metrics_path',
                'config_value'    => '@centreon_varlib@/metrics',
                'config_group'    => 'output',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 2,
                'config_key'      => 'status_path',
                'config_value'    => '@centreon_varlib@/status',
                'config_group'    => 'output',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 2,
                'config_key'      => 'retry_interval',
                'config_value'    => '15',
                'config_group'    => 'output',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 2,
                'config_key'      => 'buffering_timeout',
                'config_value'    => '0',
                'config_group'    => 'output',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 2,
                'config_key'      => 'path',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 2,
                'config_key'      => 'port',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 2,
                'config_key'      => 'write_metrics',
                'config_value'    => 'yes',
                'config_group'    => 'output',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 2,
                'config_key'      => 'write_status',
                'config_value'    => 'yes',
                'config_group'    => 'output',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 2,
                'config_key'      => 'type',
                'config_value'    => 'rrd',
                'config_group'    => 'output',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 2,
                'config_key'      => 'blockId',
                'config_value'    => '1_13',
                'config_group'    => 'output',
                'config_group_id' => 1
            ],

            /////////////////////////////////////////////////////////////////////////////////////
            /////////////////////////////////////////////////////////////////////////////////////
            /////////////////////////////////////////////////////////////////////////////////////
            /////////////////////////////////////////////////////////////////////////////////////
            /////////////////////////////////////////////////////////////////////////////////////

            [
                'config_id'       => 3,
                'config_key'      => 'name',
                'config_value'    => '@centreonbroker_log@/central-module-master.log',
                'config_group'    => 'logger',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 3,
                'config_key'      => 'config',
                'config_value'    => 'yes',
                'config_group'    => 'logger',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 3,
                'config_key'      => 'debug',
                'config_value'    => 'no',
                'config_group'    => 'logger',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 3,
                'config_key'      => 'error',
                'config_value'    => 'yes',
                'config_group'    => 'logger',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 3,
                'config_key'      => 'info',
                'config_value'    => 'no',
                'config_group'    => 'logger',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 3,
                'config_key'      => 'level',
                'config_value'    => 'low',
                'config_group'    => 'logger',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 3,
                'config_key'      => 'max_size',
                'config_value'    => '',
                'config_group'    => 'logger',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 3,
                'config_key'      => 'type',
                'config_value'    => 'file',
                'config_group'    => 'logger',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 3,
                'config_key'      => 'blockId',
                'config_value'    => '3_17',
                'config_group'    => 'logger',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 3,
                'config_key'      => 'name',
                'config_value'    => 'central-module-master-output',
                'config_group'    => 'output',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 3,
                'config_key'      => 'port',
                'config_value'    => '5669',
                'config_group'    => 'output',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 3,
                'config_key'      => 'host',
                'config_value'    => 'localhost',
                'config_group'    => 'output',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 3,
                'config_key'      => 'retry_interval',
                'config_value'    => '15',
                'config_group'    => 'output',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 3,
                'config_key'      => 'buffering_timeout',
                'config_value'    => '0',
                'config_group'    => 'output',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 3,
                'config_key'      => 'protocol',
                'config_value'    => 'bbdo',
                'config_group'    => 'output',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 3,
                'config_key'      => 'tls',
                'config_value'    => 'no',
                'config_group'    => 'output',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 3,
                'config_key'      => 'private_key',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 3,
                'config_key'      => 'public_cert',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 3,
                'config_key'      => 'ca_certificate',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 3,
                'config_key'      => 'negotiation',
                'config_value'    => 'yes',
                'config_group'    => 'output',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 3,
                'config_key'      => 'one_peer_retention_mode',
                'config_value'    => 'no',
                'config_group'    => 'output',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 3,
                'config_key'      => 'compression',
                'config_value'    => 'no',
                'config_group'    => 'output',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 3,
                'config_key'      => 'compression_level',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 3,
                'config_key'      => 'compression_buffer',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 3,
                'config_key'      => 'type',
                'config_value'    => 'ipv4',
                'config_group'    => 'output',
                'config_group_id' => 1
            ],
            [
                'config_id'       => 3,
                'config_key'      => 'blockId',
                'config_value'    => '1_3',
                'config_group'    => 'output',
                'config_group_id' => 1
            ]
        ];
    }
}
