<?php

namespace Centreon\Domain\Broker\Interfaces;

use Centreon\Domain\Broker\Broker;

interface BrokerServiceInterface
{
    /**
     * Undocumented function
     *
     * @param integer $monitoringServerId
     * @param string $config_Key
     * @throws BrokerException
     * @return Broker
     */
    public function findConfigurationByMonitoringServerAndConfigKey(
        int $monitoringServerId,
        string $config_Key
    ): Broker;
}
