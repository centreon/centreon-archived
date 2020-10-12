<?php

namespace Centreon\Domain\Broker\Interfaces;

use Centreon\Domain\Broker\Broker;

interface BrokerRepositoryInterface
{
    public function findConfigurationByMonitoringServerAndConfigKey(int $monitoringServerId, string $configKey): ?Broker;
}