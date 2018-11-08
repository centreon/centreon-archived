<?php
namespace CentreonRemote\Domain\Resources\DefaultConfig;

class CfgNagiosBrokerModule
{
    public static function getConfiguration()
    {
        return [
            [
                'cfg_nagios_id' => 1,
                'broker_module' => '@centreon_engine_lib@/externalcmd.so',
            ],
            [
                'cfg_nagios_id' => 1,
                'broker_module' => '@centreonbroker_cbmod@ @centreonbroker_etc@/central-module.xml',
            ],
        ];
    }
}
