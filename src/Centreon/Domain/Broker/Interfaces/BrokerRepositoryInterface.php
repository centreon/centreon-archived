<?php

namespace Centreon\Domain\Broker\Interfaces;

/**
 * @return array
 */
interface BrokerRepositoryInterface
{
    public function findByMonitoringServerAndParameterName(
        int $monitoringServerId,
        string $configKey
    ): array;
}
