<?php
/*
 * Centreon
 *
 * Source Copyright 2005-2019 Centreon
 *
 * Unauthorized reproduction, copy and distribution
 * are not allowed.
 *
 * For more informations : contact@centreon.com
 *
 */

namespace CentreonAutoDiscovery\Domain\Service;

use CentreonAutoDiscovery\Infrastructure\Repository\HostDiscoveryRuleRepositoryInterface;
use CentreonAutoDiscovery\Infrastructure\Repository\HostDiscoveryRepositoryInterface;

class HostDiscoveryRuleService
{
    /**
     * @var $hostDiscoveryRuleRepository HostDiscoveryRuleRepositoryInterface
     */
    protected $hostDiscoveryRuleRepository;

    /**
     * @var $hostDiscoveryRepository HostDiscoveryRepositoryInterface
     */
    protected $hostDiscoveryRepository;

    /**
     * discovery command
     *
     * @var \CentreonCommand $command
     */
    protected $command;

    /**
     * plugin pack id
     *
     * @var int $pluginPackId
     */
    protected $pluginPackId;

    /**
     * discovery rules
     *
     * @var array $rules
     */
    protected $rules;

    /**
     * additional params (commands)
     *
     * @var array $additionalParams
     */
    protected $additionalParams;

    /**
     * HostDiscoveryRuleService constructor.
     * @param HostDiscoveryRepositoryInterface $ruleRepository
     * @param HostDiscoveryRepositoryInterface $discoveryRepository
     * @param \CentreonCommand $command discovery command
     */
    public function __construct(
        HostDiscoveryRuleRepositoryInterface $ruleRepository,
        HostDiscoveryRepositoryInterface $discoveryRepository,
        \CentreonCommand $command
    ) {
        $this->hostDiscoveryRuleRepository = $ruleRepository;
        $this->hostDiscoveryRepository = $discoveryRepository;
        $this->command = $command;
    }

    /**
     * Set plugin pack id
     *
     * @param int $id
     * @return void
     */
    public function setPluginPackId(int $id)
    {
        $this->pluginPackId = $id;
    }

    /**
     * Set rules
     *
     * @param array $rules
     * @return void
     */
    public function setParams(array $rules)
    {
        $this->rules = $rules;
    }

    /**
     * Set additional params needed by host discovery (eg: commands)
     *
     * @param array $additionalParams
     * @return void
     */
    public function setAdditionalParams(array $additionalParams)
    {
        $this->additionalParams = $additionalParams;
    }

    /**
     * Install rules
     *
     * @return void
     */
    public function install()
    {
        foreach ($this->rules as $rule) {
            $this->installProvider($rule);
        }
    }

    /**
     * Update rules
     *
     * @return void
     */
    public function update()
    {
        foreach ($this->rules as $rule) {
            $this->installProvider($rule);
        }
    }

    /**
     * Install provider (general informations and mapping)
     *
     * @param array $rule
     * @return void
     */
    private function installProvider(array $rule)
    {
        $commandId = $this->command->getCommandIdByName($rule['command']);

        $this->hostDiscoveryRuleRepository->installProviderType($rule['type']);
        $providerTypeId = $this->hostDiscoveryRepository->findProviderTypeIdByName($rule['type']);

        $providerId = $this->hostDiscoveryRepository->findProviderIdByName($rule['name']);
        if ($providerId === null) {
            $providerId = $this->hostDiscoveryRuleRepository->installProvider(
                $this->pluginPackId,
                $providerTypeId,
                $rule,
                $commandId
            );
        } else {
            $this->hostDiscoveryRuleRepository->updateProvider(
                $providerId,
                $this->pluginPackId,
                $providerTypeId,
                $rule,
                $commandId
            );
        }

        foreach ($rule['mapping'] as $name => $parameters) {
            $mappingId = $this->hostDiscoveryRepository->findProviderMappingIdByName($name);
            if ($mappingId === null) {
                $this->hostDiscoveryRuleRepository->installProviderMapping($providerId, $name, $parameters);
            } else {
                $this->hostDiscoveryRuleRepository->updateProviderMapping($providerId, $name, $parameters);
            }
        }
    }
}
