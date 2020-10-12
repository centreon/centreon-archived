<?php

namespace Centreon\Domain\Broker;

use Centreon\Domain\Broker\Broker;
use Centreon\Domain\Broker\Interfaces\BrokerServiceInterface;
use Centreon\Domain\Broker\Interfaces\BrokerRepositoryInterface;

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
    public function findConfigurationByMonitoringServerAndConfigKey(int $monitoringServerId, string $configKey): Broker
    {
        $broker = $this->brokerRepository->findConfigurationByMonitoringServerAndConfigKey(
            $monitoringServerId,
            $configKey
        );
        if($broker === null) {
            throw new BrokerException(sprintf(_('No entry for %s key in your Broker configuration'), $configKey));
        }

        foreach ($broker->getBrokerConfigurations() as $brokerConfiguration) {
            if(
                $brokerConfiguration->getConfigurationKey() === "one_peer_retention_mode"
                && $brokerConfiguration->getConfigurationValue() === "yes"
            ) {
                $broker->setIsPeerRetentionMode(true);
            }
        }
        return $broker;
    }
}