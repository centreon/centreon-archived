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
use Centreon\Domain\Monitoring\Interfaces\MonitoringServiceInterface;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Centreon\Domain\Service\AbstractCentreonService;

/**
 * Monitoring class used to manage the real time services and hosts
 *
 * @package Centreon\Domain\Monitoring
 */
class MonitoringService extends AbstractCentreonService implements MonitoringServiceInterface
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
     * {@inheritDoc}
     * @param Contact $contact
     * @return self
     */
    public function filterByContact($contact): self
    {
        parent::filterByContact($contact);

        $accessGroups = $this->accessGroupRepository->findByContact($contact);

        $this->monitoringRepository
            ->setContact($this->contact)
            ->filterByAccessGroups($accessGroups);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function findServices(): array
    {
        return $this->monitoringRepository->findServices();
    }

    /**
     * @inheritDoc
     */
    public function findServicesByHost(int $hostId): array
    {
        return $this->monitoringRepository->findServicesByHost($hostId);
    }

    /**
     * @inheritDoc
     */
    public function findHosts(bool $withServices = false): array
    {
        $hosts = $this->monitoringRepository->findHosts();
        if ($withServices && !empty($hosts)) {
            $hosts = $this->completeHostsWithTheirServices($hosts);
        }
        return $hosts;
    }

    /**
     * @inheritDoc
     */
    public function findHostGroups(bool $withHosts = false, bool $withServices = false): array
    {
        // Find hosts groups only
        $hostGroups = $this->monitoringRepository->findHostGroups();

        if (!empty($hostGroups)) {
            $hostIds = [];
            if ($withHosts || $withServices) {
                // We will find hosts linked to hosts groups found
                $hostGroupIds = [];
                foreach ($hostGroups as $hostGroup) {
                    $hostGroupIds[] = $hostGroup->getId();
                }

                if (!empty($hostGroupIds)) {
                    $hostsByHostsGroups = $this->monitoringRepository->findHostsByHostsGroups($hostGroupIds);

                    foreach ($hostGroups as $hostGroup) {
                        if (array_key_exists($hostGroup->getId(), $hostsByHostsGroups)) {
                            $hostGroup->setHosts($hostsByHostsGroups[$hostGroup->getId()]);
                            // We keep the host ids if we must to retrieve their services
                            if ($withServices && !empty($hostGroup->getHosts())) {
                                foreach ($hostGroup->getHosts() as $host) {
                                    if (!in_array($host->getId(), $hostIds)) {
                                        $hostIds[] = $host->getId();
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if ($withServices) {
                // We will find services linked to hosts linked to host groups
                $servicesByHost = $this->monitoringRepository->findServicesByHosts($hostIds);
                foreach ($hostGroups as $hostGroup) {
                    foreach ($hostGroup->getHosts() as $host) {
                        if (array_key_exists($host->getId(), $servicesByHost)) {
                            $host->setServices($servicesByHost[$host->getId()]);
                        }
                    }
                }
            }
        }

        return $hostGroups;
    }

    /**
     * @inheritDoc
     */
    public function findOneHost(int $hostId): ?Host
    {
        $host = $this->monitoringRepository->findOneHost($hostId);

        if (!empty($host)) {
            $host = $this->completeHostsWithTheirServices([$host])[0];
        }
        return $host;
    }

    /**
     * @inheritDoc
     */
    public function findOneService(int $hostId, int $serviceId): ?Service
    {
        return $this->monitoringRepository->findOneService($hostId, $serviceId);
    }

    /**
     * @inheritDoc
     */
    public function findServiceGroups(bool $withHosts = false, bool $withServices = false): array
    {
        // Find hosts groups only
        $serviceGroups = $this->monitoringRepository->findServiceGroups();

        if (!empty($serviceGroups) && ($withHosts || $withServices)) {
            // We will find hosts linked to hosts groups found
            $serviceGroupIds = [];
            foreach ($serviceGroups as $serviceGroup) {
                $serviceGroupIds[] = $serviceGroup->getId();
            }

            $hostsByServicesGroups = $this->monitoringRepository->findHostsByServiceGroups($serviceGroupIds);

            foreach ($serviceGroups as $serviceGroup) {
                if (array_key_exists($serviceGroup->getId(), $hostsByServicesGroups)) {
                    $serviceGroup->setHosts($hostsByServicesGroups[$serviceGroup->getId()]);
                }
            }

            if ($withServices) {
                // We will find services linked to hosts linked to service groups
                $servicesByServiceGroup = $this->monitoringRepository->findServicesByServiceGroups($serviceGroupIds);

                // First, we will sort services by service groups and hosts
                $servicesByServiceGroupAndHost = [];
                /**
                 * @var $services Service[]
                 */
                foreach ($servicesByServiceGroup as $serviceGroupId => $services) {
                    foreach ($services as $service) {
                        $hostId = $service->getHost()->getId();
                        $servicesByServiceGroupAndHost[$serviceGroupId][$hostId][] = $service;
                    }
                }

                // Next, we will linked services to host
                /**
                 * @var $serviceGroup ServiceGroup
                 */
                foreach ($serviceGroups as $serviceGroup) {
                    foreach ($serviceGroup->getHosts() as $host) {
                        if (array_key_exists($serviceGroup->getId(), $servicesByServiceGroupAndHost)
                            && array_key_exists($host->getId(), $servicesByServiceGroupAndHost[$serviceGroup->getId()])
                        ) {
                            $host->setServices(
                                $servicesByServiceGroupAndHost[$serviceGroup->getId()][$host->getId()]
                            );
                        }
                    }
                }
            }
        }

        return $serviceGroups;
    }

    /**
     * @inheritDoc
     */
    public function isHostExists(int $hostId): bool
    {
        return !is_null($this->findOneHost($hostId));
    }

    /**
     * Completes hosts with their services.
     *
     * @param array $hosts Host list for which we want to complete with their services
     * @return array Returns the host list with their services
     * @throws \Exception
     */
    private function completeHostsWithTheirServices(array $hosts): array
    {
        $hostIds = [];
        foreach ($hosts as $host) {
            $hostIds[] = $host->getId();
        }
        $services = $this->monitoringRepository->findServicesByHosts($hostIds);

        foreach ($hosts as $host) {
            if (array_key_exists($host->getId(), $services)) {
                $host->setServices($services[$host->getId()]);
            }
        }
        return $hosts;
    }
}
