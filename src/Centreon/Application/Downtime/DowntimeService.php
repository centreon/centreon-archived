<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
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

namespace Centreon\Application\Downtime;

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Downtime\Downtime;
use Centreon\Domain\Downtime\Interfaces\DowntimeRepositoryInterface;
use Centreon\Domain\Downtime\Interfaces\DowntimeServiceInterface;
use Centreon\Domain\Engine\Interfaces\EngineServiceInterface;
use Centreon\Domain\Entity\EntityValidator;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;
use Centreon\Domain\Security\AccessGroup;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Centreon\Domain\Service\AbstractCentreonService;

class DowntimeService extends AbstractCentreonService implements DowntimeServiceInterface
{
    /**
     * @var AccessGroupRepositoryInterface
     */
    private $accessGroupRepository;
    /**
     * @var EngineServiceInterface All downtime requests except reading use Engine.
     */
    private $engineService;
    /**
     * @var EntityValidator
     */
    private $validator;
    /**
     * @var DowntimeRepositoryInterface
     */
    private $downtimeRepository;
    /**
     * @var MonitoringRepositoryInterface
     */
    private $monitoringRepository;
    /**
     * @var AccessGroup[]
     */
    private $accessGroups;

    public function __construct(
        AccessGroupRepositoryInterface $accessGroupRepository,
        EngineServiceInterface $engineService,
        EntityValidator $validator,
        DowntimeRepositoryInterface $downtimeRepository,
        MonitoringRepositoryInterface $monitoringRepository
    ) {
        $this->accessGroupRepository = $accessGroupRepository;
        $this->engineService = $engineService;
        $this->validator = $validator;
        $this->downtimeRepository = $downtimeRepository;
        $this->monitoringRepository = $monitoringRepository;
    }

    /**
     * {@inheritDoc}
     * @param Contact $contact
     * @return DowntimeServiceInterface
     */
    public function filterByContact($contact): self
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
    public function findHostDowntime(): array
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
     * @param int $hostId
     * @return array
     * @throws \Exception
     */
    public function findDowntimesByHost(int $hostId): array
    {
        if ($this->contact->isAdmin()) {
            return $this->downtimeRepository->findDowntimesByHostForAdminUser($hostId);
        } else {
            return $this->downtimeRepository
                ->forAccessGroups($this->accessGroups)
                ->findDowntimesByHostForNonAdminUser($hostId);
        }
    }

    /**
     * @inheritDoc
     */
    public function findServiceDowntime(): array
    {
        if ($this->contact->isAdmin()) {
            return $this->downtimeRepository->findServiceDowntimesForAdminUser();
        } else {
            return $this->downtimeRepository
                ->forAccessGroups($this->accessGroups)
                ->findServiceDowntimesForNonAdminUser();
        }
    }

    /**
     * @inheritDoc
     */
    public function findDowntime(): array
    {
        if ($this->contact->isAdmin()) {
            return $this->downtimeRepository->findDowntimeForAdminUser();
        } else {
            return $this->downtimeRepository
                ->forAccessGroups($this->accessGroups)
                ->findDowntimeForNonAdminUser();
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
}
