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
     * @return array
     */
    public function findByMonitoringServerAndParameterName(
        int $monitoringServerId,
        string $config_Key
    ): array;
}
