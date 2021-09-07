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

namespace Centreon\Domain\Downtime;

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Monitoring\Host;
use Centreon\Domain\Monitoring\Service;
use Centreon\Domain\Security\AccessGroup;
use Centreon\Domain\Entity\EntityValidator;
use Centreon\Domain\Service\AbstractCentreonService;
use Centreon\Domain\Exception\EntityNotFoundException;
use Centreon\Domain\Engine\Interfaces\EngineServiceInterface;
use Centreon\Domain\Downtime\Interfaces\DowntimeServiceInterface;
use Centreon\Domain\Downtime\Interfaces\ResourceDowntimeInterface;
use Centreon\Domain\Downtime\Interfaces\DowntimeRepositoryInterface;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;
use Centreon\Domain\Monitoring\MonitoringResource\Model\MonitoringResource;

/**
 * This class is designed to add/delete/find downtimes on hosts and services.
 *
 * @package Centreon\Domain\Downtime
 */
class DowntimeService extends AbstractCentreonService implements DowntimeServiceInterface
{
    public const VALIDATION_GROUPS_ADD_HOST_DOWNTIME = ['Default', 'downtime_host', 'downtime_host_add'];
    public const VALIDATION_GROUPS_ADD_SERVICE_DOWNTIME = ['Default', 'downtime_service'];

    /**
     * @var AccessGroupRepositoryInterface
     */
    private $accessGroupRepository;

    /**
     * @var EngineServiceInterface For all downtimes requests except reading
     */
    private $engineService;

    /**
     * @var DowntimeRepositoryInterface
     */
    private $downtimeRepository;

    /**
     * @var AccessGroup[] Access groups of contact
     */
    private $accessGroups;

    /**
     * @var MonitoringRepositoryInterface
     */
    private $monitoringRepository;

    /**
     * @var ResourceDowntimeInterface[]
     */
    private $resourcesDowntime;

    /**
     * DowntimeService constructor.
     *
     * @param AccessGroupRepositoryInterface $accessGroupRepository
     * @param EngineServiceInterface $engineService
     * @param DowntimeRepositoryInterface $downtimeRepository
     * @param MonitoringRepositoryInterface $monitoringRepository
     */
    public function __construct(
        AccessGroupRepositoryInterface $accessGroupRepository,
        EngineServiceInterface $engineService,
        DowntimeRepositoryInterface $downtimeRepository,
        MonitoringRepositoryInterface $monitoringRepository
    ) {
        $this->accessGroupRepository = $accessGroupRepository;
        $this->engineService = $engineService;
        $this->downtimeRepository = $downtimeRepository;
        $this->monitoringRepository = $monitoringRepository;
    }

    /**
     * @param iterable<ResourceDowntimeInterface> $resources
     * @throws \InvalidArgumentException
     */
    public function setResourcesDowntime(iterable $resourcesDowntime): void
    {
        /**
         * @var ResourceDowntimeInterface[] $resourcesDowntime
         */
        $resourcesDowntime = $resourcesDowntime instanceof \Traversable
            ? iterator_to_array($resourcesDowntime)
            : $resourcesDowntime;

        if (count($resourcesDowntime) === 0) {
            throw new \InvalidArgumentException(
                _('You must at least add one resource downtime')
            );
        }

        $this->resourcesDowntime = $resourcesDowntime;
    }

    /**
     * {@inheritDoc}
     * @param Contact $contact
     * @return DowntimeServiceInterface
     */
    public function filterByContact($contact): DowntimeServiceInterface
    {
        parent::filterByContact($contact);
        $this->engineService->filterByContact($contact);

        $this->accessGroups = $this->accessGroupRepository->findByContact($contact);

        $this->monitoringRepository
            ->setContact($this->contact)
            ->filterByAccessGroups($this->accessGroups);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addHostDowntime(Downtime $downtime, Host $host): void
    {
        $this->engineService->addHostDowntime($downtime, $host);
    }

    /**
     * @inheritDoc
     */
    public function addServiceDowntime(Downtime $downtime, Service $service): void
    {
        $this->engineService->addServiceDowntime($downtime, $service);
    }

    /**
     * @inheritDoc
     */
    public function findHostDowntimes(): array
    {
        if ($this->contact->isAdmin()) {
            return $this->downtimeRepository->findHostDowntimesForAdminUser();
        } else {
            return $this->downtimeRepository
                ->forAccessGroups($this->accessGroups)
                ->findHostDowntimesForNonAdminUser();
        }
    }

    /**
     * @inheritDoc
     */
    public function findServicesDowntimes(): array
    {
        if ($this->contact->isAdmin()) {
            return $this->downtimeRepository->findServicesDowntimesForAdminUser();
        } else {
            return $this->downtimeRepository
                ->forAccessGroups($this->accessGroups)
                ->findServicesDowntimesForNonAdminUser();
        }
    }

    /**
     * @inheritDoc
     */
    public function findDowntimesByService(int $hostId, int $serviceId): array
    {
        if ($this->contact->isAdmin()) {
            return $this->downtimeRepository->findDowntimesByServiceForAdminUser($hostId, $serviceId);
        } else {
            return $this->downtimeRepository
                ->forAccessGroups($this->accessGroups)
                ->findDowntimesByServiceForNonAdminUser($hostId, $serviceId);
        }
    }

    /**
     * @inheritDoc
     */
    public function findDowntimesByMetaService(int $metaId): array
    {
        $service = $this->monitoringRepository->findOneServiceByDescription('meta_' . $metaId);
        if (is_null($service)) {
            throw new EntityNotFoundException(_('Meta service not found'));
        }
        if ($this->contact->isAdmin()) {
            return $this->downtimeRepository->findDowntimesByServiceForAdminUser(
                $service->getHost()->getId(),
                $service->getId()
            );
        } else {
            return $this->downtimeRepository
                ->forAccessGroups($this->accessGroups)
                ->findDowntimesByServiceForNonAdminUser(
                    $service->getHost()->getId(),
                    $service->getId()
                );
        }
    }

    /**
     * @inheritDoc
     */
    public function findDowntimesByHost(int $hostId, bool $withServices): array
    {
        if ($this->contact->isAdmin()) {
            return $this->downtimeRepository->findDowntimesByHostForAdminUser($hostId, $withServices);
        } else {
            return $this->downtimeRepository
                ->forAccessGroups($this->accessGroups)
                ->findDowntimesByHostForNonAdminUser($hostId, $withServices);
        }
    }

    /**
     * @inheritDoc
     */
    public function findDowntimes(): array
    {
        if ($this->contact->isAdmin()) {
            return $this->downtimeRepository->findDowntimesForAdminUser();
        } else {
            return $this->downtimeRepository
                ->forAccessGroups($this->accessGroups)
                ->findDowntimesForNonAdminUser();
        }
    }

    /**
     * @inheritDoc
     */
    public function findOneDowntime(int $downtimeId): ?Downtime
    {
        if ($this->contact->isAdmin()) {
            return $this->downtimeRepository->findOneDowntimeForAdminUser($downtimeId);
        } else {
            return $this->downtimeRepository
                ->forAccessGroups($this->accessGroups)
                ->findOneDowntimeForNonAdminUser($downtimeId);
        }
    }

    /**
     * @inheritDoc
     */
    public function cancelDowntime(int $downtimeId, Host $host): void
    {
        $downtime = null;
        if ($this->contact->isAdmin()) {
            $downtime = $this->downtimeRepository->findOneDowntimeForAdminUser($downtimeId);
        } else {
            $downtime = $this->downtimeRepository
                ->forAccessGroups($this->accessGroups)
                ->findOneDowntimeForNonAdminUser($downtimeId);
        }

        if ($downtime === null) {
            throw new EntityNotFoundException(_('Downtime not found'));
        }

        $downtimeType = (empty($downtime->getServiceId())) ? 'host' : 'service';

        if (!is_null($downtime->getDeletionTime())) {
            throw new DowntimeException(
                sprintf(_('Downtime already cancelled for this %s'), $downtimeType)
            );
        }

        $this->engineService->cancelDowntime($downtime, $host);
    }

    /**
     * @inheritDoc
     */
    public function addResourceDowntime(MonitoringResource $monitoringResource, Downtime $downtime): void
    {
        foreach ($this->resourcesDowntime as $resource) {
            if ($resource->isForResource($monitoringResource->getType())) {
                $resource->addDowntime(
                    $downtime,
                    $monitoringResource->getId(),
                    $monitoringResource->getParent() !== null ? $monitoringResource->getParent()->getId() : null
                );
            }
        }
    }
}
