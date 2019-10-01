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

namespace Centreon\Domain\Monitoring;

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Monitoring\Interfaces\MonitoringServiceInterface;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;

/**
 * Monitoring class used to manage the real time services and hosts
 *
 * @package Centreon\Domain\Monitoring
 */
class MonitoringService implements MonitoringServiceInterface
{
    /**
     * @var MonitoringRepositoryInterface
     */
    private $monitoringRepository;

    /**
     * @var AccessGroupRepositoryInterface
     */
    private $accessGroupRepository;

    /**
     * The contact will be use to filter services and hosts.
     * @var Contact
     */
    private $contact;

    /**
     * @param MonitoringRepositoryInterface $monitoringRepository
     * @param AccessGroupRepositoryInterface $accessGroupRepository
     */
    public function __construct(
        MonitoringRepositoryInterface $monitoringRepository,
        AccessGroupRepositoryInterface $accessGroupRepository
    ) {
        $this->monitoringRepository = $monitoringRepository;
        $this->accessGroupRepository = $accessGroupRepository;
    }

    /**
     * @inheritDoc
     */
    public function findServices(): array
    {
        if ($this->contact->isAdmin()) {
            return $this
                ->monitoringRepository
                ->filterByAccessGroups(null)
                ->findServices();
        } elseif (count($accessGroups = $this->accessGroupRepository->findByContact($this->contact)) > 0) {
            return $this
                ->monitoringRepository
                ->filterByAccessGroups($accessGroups)
                ->findServices();
        }
        return [];
    }

    /**
     * @inheritDoc
     */
    public function findServicesByHost(int $hostId): array
    {
        if ($this->contact->isAdmin()) {
            return $this
                ->monitoringRepository
                ->filterByAccessGroups(null)
                ->findServicesByHost($hostId);
        } elseif (count($accessGroups = $this->accessGroupRepository->findByContact($this->contact)) > 0) {
            return $this
                ->monitoringRepository
                ->filterByAccessGroups($accessGroups)
                ->findServicesByHost($hostId);
        }
        return [];
    }

    /**
     * @inheritDoc
     */
    public function findHosts(): array
    {
        if ($this->contact->isAdmin()) {
            return $this
                ->monitoringRepository
                ->filterByAccessGroups(null)
                ->findHosts();
        } elseif (count($accessGroups = $this->accessGroupRepository->findByContact($this->contact)) > 0) {
            return $this
                ->monitoringRepository
                ->filterByAccessGroups($accessGroups)
                ->findHosts();
        }
        return [];
    }

    /**
     * @inheritDoc
     */
    public function findHostGroups(): array
    {
        if ($this->contact->isAdmin()) {
            return $this
                ->monitoringRepository
                ->filterByAccessGroups(null)
                ->findHostGroups();
        } elseif (count($accessGroups = $this->accessGroupRepository->findByContact($this->contact)) > 0) {
            return $this
                ->monitoringRepository
                ->filterByAccessGroups($accessGroups)
                ->findHostGroups();
        }
        return [];
    }

    /**
     * @inheritDoc
     */
    public function filterByContact(ContactInterface $contact): MonitoringServiceInterface
    {
        $this->contact = $contact;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function findOneHost(int $hostId): ?Host
    {
        if ($this->contact->isAdmin()) {
            return $this
                ->monitoringRepository
                ->filterByAccessGroups(null)
                ->findOneHost($hostId);
        } elseif (count($accessGroups = $this->accessGroupRepository->findByContact($this->contact)) > 0) {
            return $this
                ->monitoringRepository
                ->filterByAccessGroups($accessGroups)
                ->findOneHost($hostId);
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function findOneService(int $hostId, int $serviceId): ?Service
    {
        if ($this->contact->isAdmin()) {
            return $this
                ->monitoringRepository
                ->filterByAccessGroups(null)
                ->findOneService($hostId, $serviceId);
        } elseif (count($accessGroups = $this->accessGroupRepository->findByContact($this->contact)) > 0) {
            return $this
                ->monitoringRepository
                ->filterByAccessGroups($accessGroups)
                ->findOneService($hostId, $serviceId);
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function findServiceGroups(): array
    {
        if ($this->contact->isAdmin()) {
            return $this
                ->monitoringRepository
                ->filterByAccessGroups(null)
                ->findServiceGroups();
        } elseif (count($accessGroups = $this->accessGroupRepository->findByContact($this->contact)) > 0) {
            return $this
                ->monitoringRepository
                ->filterByAccessGroups($accessGroups)
                ->findServiceGroups();
        }
        return [];
    }

    /**
     * @inheritDoc
     */
    public function isHostExists(int $hostId): bool
    {
        return !is_null($this->findOneHost($hostId));
    }
}
