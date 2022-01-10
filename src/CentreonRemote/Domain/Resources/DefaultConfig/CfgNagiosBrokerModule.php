<?php
namespace CentreonRemote\Domain\Resources\DefaultConfig;

/**
 * Get broker configuration template
 */
class CfgNagiosBrokerModule
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
                'cfg_nagios_id' => 1,
                'broker_module' => '@centreon_engine_lib@/externalcmd.so',
            ],
            [
                'cfg_nagios_id' => 1,
                'broker_module' => '@centreonbroker_cbmod@ @centreonbroker_etc@/central-module.json',
            ],
        ];
    }
}
