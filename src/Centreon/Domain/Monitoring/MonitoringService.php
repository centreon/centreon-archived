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
    public function filterByContact($contact)
    {
        parent::filterByContact($contact);

        $accessGroups = $this->accessGroupRepository->findByContact($contact);

        $this->monitoringRepository
            ->setAdmin($this->contact->isAdmin())
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
    public function findHosts(): array
    {
        $hosts = $this->monitoringRepository->findHosts();
        if (!empty($hosts)) {
            $hosts = $this->completeHostsWithTheirServices($hosts);
        }
        return $hosts;
    }

    /**
     * @inheritDoc
     */
    public function findHostGroups(): array
    {
        /**
         * @param array $hostGroups
         * @return HostGroup[]
         */
        $completeHostsOfHostGroupsWithTheirServices = function (array $hostGroups): array {
            $hostsById = [];
            // We create a unique hosts list indexed by their id
            foreach ($hostGroups as $hostGroup) {
                foreach ($hostGroup->getHosts() as $host) {
                    if (!array_key_exists($host->getId(), $hostsById)) {
                        $hostsById[$host->getId()] = $host;
                    }
                }
            }

            // We complete the hosts of host group by their services
            if (!empty($hostsById)) {
                $hosts = $this->completeHostsWithTheirServices(array_values($hostsById));
                $hostsById = [];
                foreach ($hosts as $host) {
                    $hostsById[$host->getId()] = $host;
                }

                foreach ($hostGroups as $hostGroup) {
                    foreach ($hostGroup->getHosts() as $host) {
                        if (array_key_exists($host->getId(), $hostsById)) {
                            $host->setServices($hostsById[$host->getId()]->getServices());
                        }
                    }
                }
            }

            return $hostGroups;
        };

        $hostGroups = $this->monitoringRepository->findHostGroups();

        return $completeHostsOfHostGroupsWithTheirServices($hostGroups);
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
    public function findServiceGroups(): array
    {
        return $this->monitoringRepository->findServiceGroups();
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
        $services = $this->monitoringRepository->findServicesOnMultipleHosts($hostIds);

        foreach ($hosts as $host) {
            if (array_key_exists($host->getId(), $services)) {
                $host->setServices($services[$host->getId()]);
            }
        }
        return $hosts;
    }
}
