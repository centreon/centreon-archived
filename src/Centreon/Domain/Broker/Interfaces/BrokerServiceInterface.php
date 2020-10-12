<?php

namespace Centreon\Domain\Broker;

use Centreon\Domain\Broker\Broker;

interface BrokerServiceInterface
{
    /**
     * Undocumented function
     *
     * @param integer $monitoringServerId
     * @param string $config_Key
     * @return Broker|null
     */
    public function findConfigurationByMonitoringServer(int $monitoringServerId, string $config_Key): ?Broker;
}