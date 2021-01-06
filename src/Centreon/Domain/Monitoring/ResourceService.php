<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
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
     * @param ResourceRepositoryInterface $resourceRepository
     * @param MonitoringRepositoryInterface $monitoringRepository,
     * @param AccessGroupRepositoryInterface $accessGroupRepository
     */
    public function __construct(
        ResourceRepositoryInterface $resourceRepository,
        MonitoringRepositoryInterface $monitoringRepository,
        AccessGroupRepositoryInterface $accessGroupRepository
    ) {
        $this->resourceRepository = $resourceRepository;
        $this->monitoringRepository = $monitoringRepository;
        $this->accessGroupRepository = $accessGroupRepository;
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
            throw new ResourceException(_('Error while searching for resources'), 0, $ex);
        }

        return $list;
    }

    /**
     * {@inheritDoc}
     */
    public function enrichHostWithDetails(ResourceEntity $resource): void
    {
        $host = $this->monitoringRepository->findOneHost($resource->getId());
        if ($host !== null) {
            $resource->setPollerName($host->getPollerName());
        }

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
