<?php

namespace Centreon\Domain\Broker;

interface BrokerRepositoryInterface
{
    public function findConfigurationByMonitoringServer(int $monitoringServerId, string $configKey): ?Broker;
}