<?php

namespace Centreon\Domain\Service;

use Centreon\Domain\Repository\Interfaces\CfgCentreonBrokerInfoInterface;

/**
 * Service to manage broker flows configuration
 */
class BrokerConfigurationService
{
    /**
     * @var CfgCentreonBrokerInfoInterface
     */
    private $brokerInfoRepository;

    /**
     * Set broker infos repository to manage flows (input, output, log...)
     *
     * @param CfgCentreonBrokerInfoInterface $cfgCentreonBrokerInfo the broker info repository
     */
    public function setBrokerInfoRepository(CfgCentreonBrokerInfoInterface $cfgCentreonBrokerInfo)
    {
        $this->brokerInfoRepository = $cfgCentreonBrokerInfo;
    }

    /**
     * Add flow (input, output, log...)
     *
     * @param int $configId the config id to update
     * @param string $configGroup the config group to add (input, output...)
     * @param \Centreon\Domain\Entity\CfgCentreonBrokerInfo[] $brokerInfoEntities the flow parameters to insert
     */
    public function addFlow(int $configId, string $configGroup, array $brokerInfoEntities): void
    {
        // get new input config group id on central broker configuration
        // to add new IPv4 input
        $configGroupId = $this->brokerInfoRepository->getNewConfigGroupId($configId, $configGroup);

        // insert each line of configuration in database thanks to BrokerInfoEntity
        foreach ($brokerInfoEntities as $brokerInfoEntity) {
            $brokerInfoEntity->setConfigId($configId);
            $brokerInfoEntity->setConfigGroup($configGroup);
            $brokerInfoEntity->setConfigGroupId($configGroupId);
            $this->brokerInfoRepository->add($brokerInfoEntity);
        }
    }
}
