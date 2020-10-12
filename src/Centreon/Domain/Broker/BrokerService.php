<?php

namespace Centreon\Domain\Broker;

use Centreon\Domain\Broker\BrokerServiceInterface;
use Centreon\Domain\Broker\Broker;

class BrokerService implements BrokerServiceInterface
{
    /**
     * @var BrokerRepositoryInterface
     */
    private $brokerRepository;

    public function __construct(BrokerRepositoryInterface $brokerRepository)
    {
        $this->brokerRepository = $brokerRepository;
    }

    /**
     * @inheritDoc
     */
    public function findConfigurationByMonitoringServer(int $monitoringServerId, string $configKey): ?Broker
    {
        $broker = $this->brokerRepository->findConfigurationByMonitoringServer($monitoringServerId, $configKey);
        if($broker === null) {
            throw new BrokerException('Your Broker Configuration is empty');
        }
        return $broker;
    }
}