<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */
declare(strict_types=1);

namespace Centreon\Domain\Monitoring;

use Centreon\Domain\Entity\EntityValidator;
use Centreon\Domain\Exception\EntityNotFoundException;
use Centreon\Domain\Monitoring\ResourceGroup;
use Centreon\Domain\Monitoring\ResourceFilter;
use Centreon\Domain\Repository\RepositoryException;
use Centreon\Domain\Service\AbstractCentreonService;
use Centreon\Domain\Monitoring\Resource as ResourceEntity;
use Centreon\Domain\Monitoring\Exception\ResourceException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Centreon\Domain\Monitoring\Interfaces\ResourceServiceInterface;
use Centreon\Domain\Monitoring\Interfaces\ResourceRepositoryInterface;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;
use Centreon\Domain\MetaServiceConfiguration\Exception\MetaServiceConfigurationException;
use Centreon\Domain\HostConfiguration\Interfaces\HostMacro\HostMacroReadRepositoryInterface;
use Centreon\Domain\ServiceConfiguration\Interfaces\ServiceConfigurationRepositoryInterface;
use Centreon\Domain\MetaServiceConfiguration\Interfaces\MetaServiceConfigurationReadRepositoryInterface;

/**
 * Service manage the resources in real-time monitoring : hosts and services.
 *
 * @package Centreon\Domain\Monitoring
 */
class ResourceService extends AbstractCentreonService implements ResourceServiceInterface
{
    /**
     * @var ResourceRepositoryInterface
     */
    private $resourceRepository;

    /**
     * @var MonitoringRepositoryInterface
     */
    private $monitoringRepository;

    /**
     * @var AccessGroupRepositoryInterface
     */
    private $accessGroupRepository;

    /**
     * @var MetaServiceConfigurationReadRepositoryInterface
     */
    private $metaServiceConfigurationRepository;

    /**
     * @var HostMacroReadRepositoryInterface
     */
    private $hostMacroConfigurationRepository;

    /**
     * @var ServiceConfigurationRepositoryInterface
     */
    private $serviceMacroConfigurationRepository;

    /**
     * @param ResourceRepositoryInterface $resourceRepository
     * @param MonitoringRepositoryInterface $monitoringRepository
     * @param AccessGroupRepositoryInterface $accessGroupRepository
     * @param MetaServiceConfigurationReadRepositoryInterface $metaServiceConfigurationRepository
     * @param HostMacroReadRepositoryInterface $hostMacroConfigurationRepository
     * @param ServiceConfigurationRepositoryInterface $serviceMacroConfigurationRepository
     */
    public function __construct(
        ResourceRepositoryInterface $resourceRepository,
        MonitoringRepositoryInterface $monitoringRepository,
        AccessGroupRepositoryInterface $accessGroupRepository,
        MetaServiceConfigurationReadRepositoryInterface $metaServiceConfigurationRepository,
        HostMacroReadRepositoryInterface $hostMacroConfigurationRepository,
        ServiceConfigurationRepositoryInterface $serviceMacroConfigurationRepository
    ) {
        $this->resourceRepository = $resourceRepository;
        $this->monitoringRepository = $monitoringRepository;
        $this->accessGroupRepository = $accessGroupRepository;
        $this->metaServiceConfigurationRepository = $metaServiceConfigurationRepository;
        $this->hostMacroConfigurationRepository = $hostMacroConfigurationRepository;
        $this->serviceMacroConfigurationRepository = $serviceMacroConfigurationRepository;
    }

    /**
     * @inheritDoc
     */
    public function filterByContact($contact): self
    {
        parent::filterByContact($contact);

        $accessGroups = $this->accessGroupRepository->findByContact($contact);

        $this->resourceRepository
            ->setContact($this->contact)
            ->filterByAccessGroups($accessGroups);

        $this->monitoringRepository
            ->setContact($this->contact)
            ->filterByAccessGroups($accessGroups);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function extractResourcesWithGraphData(array $resources): array
    {
        return $this->resourceRepository->extractResourcesWithGraphData($resources);
    }

    /**
     * {@inheritDoc}
     */
    public function findResources(ResourceFilter $filter): array
    {
        // try to avoid exception from the regexp bad syntax in search criteria
        try {
            $list = $this->resourceRepository->findResources($filter);
        } catch (RepositoryException $ex) {
            throw new ResourceException($ex->getMessage(), 0, $ex);
        } catch (\Exception $ex) {
            throw new ResourceException($ex->getMessage(), 0, $ex);
        }

        return $list;
    }

    /**
     * {@inheritDoc}
     */
    public function enrichHostWithDetails(ResourceEntity $resource): void
    {
        $downtimes = $this->monitoringRepository->findDowntimes(
            $resource->getId(),
            0
        );
        $resource->setDowntimes($downtimes);

        if ($resource->getAcknowledged()) {
            $acknowledgements = $this->monitoringRepository->findAcknowledgements(
                $resource->getId(),
                0
            );
            if (!empty($acknowledgements)) {
                $resource->setAcknowledgement($acknowledgements[0]);
            }
        }

        /**
         * Get hostgroups on which the actual host belongs
         */
        $hostGroups = $this->monitoringRepository
            ->findHostGroups($resource->getId());


        $resourceGroups = [];

        foreach ($hostGroups as $hostGroup) {
            $resourceGroups[] = new ResourceGroup($hostGroup->getId(), $hostGroup->getName());
        }

        /**
         * Assign those resource groups to the actual resource
         */
        $resource->setGroups($resourceGroups);
    }

    /**
     * {@inheritDoc}
     */
    public function enrichServiceWithDetails(ResourceEntity $resource): void
    {
        if ($resource->getParent() === null) {
            throw new ResourceException(_('Parent of resource type service cannot be null'));
        }

        $downtimes = $this->monitoringRepository->findDowntimes(
            $resource->getParent()->getId(),
            $resource->getId()
        );
        $resource->setDowntimes($downtimes);

        if ($resource->getAcknowledged()) {
            $acknowledgements = $this->monitoringRepository->findAcknowledgements(
                $resource->getParent()->getId(),
                $resource->getId()
            );
            if (!empty($acknowledgements)) {
                $resource->setAcknowledgement($acknowledgements[0]);
            }
        }

        /**
         * Get servicegroups to which belongs the actual service resource.
         */
        $serviceGroups = $this->monitoringRepository
            ->findServiceGroupsByHostAndService($resource->getParent()->getId(), $resource->getId());

        $resourceGroups = [];

        foreach ($serviceGroups as $serviceGroup) {
            $resourceGroups[] = new ResourceGroup($serviceGroup->getId(), $serviceGroup->getName());
        }

        /**
         * Add those groups to the actual resource detailed.
         */
        $resource->setGroups($resourceGroups);
    }


    /**
     * {@inheritDoc}
     */
    public function enrichMetaServiceWithDetails(ResourceEntity $resource): void
    {
        $downtimes = $this->monitoringRepository->findDowntimes(
            $resource->getHostId(),
            $resource->getServiceId()
        );
        $resource->setDowntimes($downtimes);

        if ($resource->getAcknowledged()) {
            $acknowledgements = $this->monitoringRepository->findAcknowledgements(
                $resource->getHostId(),
                $resource->getServiceId()
            );
            if (!empty($acknowledgements)) {
                $resource->setAcknowledgement($acknowledgements[0]);
            }
        }
        /**
         * Specific to the Meta Service Resource Type
         * we need to add the Meta Service calculationType
         */
        $metaConfiguration = $this->contact->isAdmin()
            ? $this->metaServiceConfigurationRepository->findById($resource->getId())
            : $this->metaServiceConfigurationRepository->findByIdAndContact($resource->getId(), $this->contact);

        if (!is_null($metaConfiguration)) {
            $resource->setCalculationType($metaConfiguration->getCalculationType());
        }
    }

    /**
     * Find host id by resource
     * @param ResourceEntity $resource
     * @return int|null
     */
    public static function generateHostIdByResource(ResourceEntity $resource): ?int
    {
        $hostId = null;
        if ($resource->getType() === ResourceEntity::TYPE_HOST) {
            $hostId = (int) $resource->getId();
        } elseif ($resource->getType() === ResourceEntity::TYPE_SERVICE) {
            $hostId = (int) $resource->getParent()->getId();
        }

        return $hostId;
    }

    /**
     * Replace macros in the provided URL
     *
     * @param string $url
     * @param array<string, mixed> $macros
     * @return string
     */
    private function replaceMacrosByValues(string $url, array $macros): string
    {
        foreach ($macros as $name => $value) {
            $url = str_replace($name, (string) $value, $url);
        }
        return $url;
    }

    /**
     * Returns possible standard macros for Service
     *
     * @param Service $service
     * @return array<string, mixed>
     */
    private function getStandardMacrosForService(Service $service): array
    {
        $standardHostMacros = $this->getStandardMacrosForHost($service->getHost());
        $standardServiceMacros = [
            '$SERVICEDESC$' => $service->getDescription(),
            '$SERVICESTATE$' => $service->getStatus()->getName(),
            '$SERVICESTATEID$' => $service->getStatus()->getCode()
        ];

        return array_merge($standardHostMacros, $standardServiceMacros);
    }

    /**
     * Returns possible standard macros for Host
     *
     * @param Host $host
     * @return array<string, mixed>
     */
    private function getStandardMacrosForHost(Host $host): array
    {
        return [
            '$HOSTNAME$' => $host->getName(),
            '$HOSTSTATE$' => $host->getStatus()->getName(),
            '$HOSTSTATEID$' => $host->getStatus()->getCode(),
            '$HOSTALIAS$' => $host->getAlias(),
            '$INSTANCENAME$' => $host->getPollerName(),
            '$HOSTADDRESS$' => $host->getAddressIp()
        ];
    }

    /**
     * @inheritDoc
     */
    public function replaceMacrosInHostUrl(int $hostId, string $urlType): string
    {
        try {
            $host = $this->monitoringRepository->findOneHost($hostId);
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
        if ($host === null) {
            throw new EntityNotFoundException(_('Host not found'));
        }
        $url = ($urlType === 'action-url') ? $host->getActionUrl() : $host->getNotesUrl();

        $standardMacros = $this->getStandardMacrosForHost($host);
        $customMacros = $this->getCustomMacrosOutOfUrl($hostId, 0, $url);
        return $this->replaceMacrosByValues($url, array_merge($standardMacros, $customMacros));
    }

    /**
     * @inheritDoc
     */
    public function replaceMacrosInServiceUrl(int $hostId, int $serviceId, string $urlType): string
    {
        try {
            $host = $this->monitoringRepository->findOneHost($hostId);
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
        if ($host === null) {
            throw new EntityNotFoundException(_('Host not found'));
        }

        try {
            $service = $this->monitoringRepository->findOneService($hostId, $serviceId);
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
        if ($service === null) {
            throw new EntityNotFoundException(_('Service not found'));
        }

        $service->setHost($host);

        $url = ($urlType === 'action-url') ? $service->getActionUrl() : $service->getNotesUrl();

        $standardMacros = $this->getStandardMacrosForService($service);
        $customMacros = $this->getCustomMacrosOutOfUrl($hostId, $serviceId, $url);

        return $this->replaceMacrosByValues($url, array_merge($standardMacros, $customMacros));
    }

    /**
     * Finds all custom macros in the URL provided and get their values.
     *
     * @param int $hostId
     * @param int $serviceId
     * @param string $url
     * @return array<string, mixed>
     */
    private function getCustomMacrosOutOfUrl(int $hostId, int $serviceId, string $url): array
    {
        $foundMacros = [];
        $hasServiceMacros = false;
        $hasHostMacros = false;
        $customMacros = [];

        /**
         * Searching for custom macros potentially used in the URL provided
         */
        if (empty($url) === false) {
            /**
             * preg_match_all default flag PREG_PATTERN_ORDER ensures that matches
             * are in the same order regarding capturing groups
             * ex: $matches[0] = full match = $_SERVICEARG1$
             *     $matches[1] = suffix match = ARG1
             *
             * outcome $foundMacros = [
             *  '$_SERVICEARG1$' => 'ARG1',
             *  '$_HOSTARG1$' => 'ARG1'
             *  ...
             * ]
             */
            preg_match_all('/\$_SERVICE([0-9a-zA-Z\_\-]+)\$/', $url, $matches);
            foreach ($matches[0] as $key => $fullMatch) {
                $hasServiceMacros = true;
                $foundMacros[$fullMatch] = $matches[1][$key];
            }

            preg_match_all('/\$_HOST([0-9a-zA-Z\_\-]+)\$/', $url, $matches);
            foreach ($matches[0] as $key => $fullMatch) {
                $hasHostMacros = true;
                $foundMacros[$fullMatch] = $matches[1][$key];
            }
        }

        $hostRealtimeMacros = [];
        $hostConfigurationMacros = [];

        // Finding all HOST macros from configuration and realtime linked to the Resource
        if ($hasHostMacros) {
            $hostRealtimeMacros = $this->monitoringRepository->findCustomMacrosValues($hostId, 0);
            $hostConfigurationMacros = $this->hostMacroConfigurationRepository->findOnDemandHostMacros($hostId, true);
        }

        $serviceRealtimeMacros = [];
        $serviceConfigurationMacros = [];

        // Finding all SERVICE macros from configuration and realtime linked to the Resource
        if ($hasServiceMacros) {
            $serviceRealtimeMacros = $this->monitoringRepository->findCustomMacrosValues($hostId, $serviceId);
            $serviceConfigurationMacros = $this->serviceMacroConfigurationRepository->findOnDemandServiceMacros(
                $serviceId,
                true
            );
        }

        $realtimeMacros = array_merge($hostRealtimeMacros, $serviceRealtimeMacros);
        $configurationMacros = array_merge($hostConfigurationMacros, $serviceConfigurationMacros);

        foreach ($configurationMacros as $macro) {
            $macroName = $macro->getName();
            if (
                array_key_exists($macroName, $foundMacros)
                && array_key_exists($foundMacros[$macro->getName()], $realtimeMacros)
            ) {
                // hidding password type macros that should not be displayed in the realtime context
                $customMacros[$macroName] = $macro->isPassword() === false
                    ? $realtimeMacros[$foundMacros[$macroName]]
                    : '';
            }
        }

        return $customMacros;
    }

    /**
     * Validates input for resource based on groups
     * @param EntityValidator $validator
     * @param ResourceEntity $resource
     * @param array<string, mixed> $contextGroups
     * @return ConstraintViolationListInterface<mixed>
     */
    public static function validateResource(
        EntityValidator $validator,
        ResourceEntity $resource,
        array $contextGroups
    ): ConstraintViolationListInterface {
        return $validator->validate(
            $resource,
            null,
            $contextGroups
        );
    }
}
