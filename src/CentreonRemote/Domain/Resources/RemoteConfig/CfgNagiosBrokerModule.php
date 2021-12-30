<?php

namespace CentreonRemote\Domain\Resources\RemoteConfig;

/**
 * Get broker configuration template
 */
class CfgNagiosBrokerModule
{
    /**
     * Get template configuration
     * @todo move it as yml
     *
     * @param int $configID the broker config id
     * @param string $pollerName the poller name
     * @return array<int, array<string,string|int>> the configuration template
     */
    public static function getConfiguration(int $configID, string $pollerName): array
    {
        $pollerName = strtolower(str_replace(' ', '-', $pollerName));

        return [
            [
                'cfg_nagios_id' => $configID,
                'broker_module' => "/usr/lib64/nagios/cbmod.so /etc/centreon-broker/{$pollerName}-module.json",
            ],
            [
                'cfg_nagios_id' => $configID,
                'broker_module' => '/usr/lib64/centreon-engine/externalcmd.so',
            ],
        ];
    }
}
