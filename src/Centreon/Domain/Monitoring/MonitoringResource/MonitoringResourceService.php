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

namespace Centreon\Domain\Monitoring\MonitoringResource;

use Centreon\Domain\Monitoring\ResourceFilter;
use Centreon\Domain\Service\AbstractCentreonService;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;
use Centreon\Domain\Monitoring\MonitoringResource\Model\MonitoringResource;
use Centreon\Domain\Monitoring\MonitoringResource\Exception\MonitoringResourceException;
use Centreon\Domain\Monitoring\MonitoringResource\Interfaces\MonitoringResourceServiceInterface;
use Centreon\Domain\Monitoring\MonitoringResource\Interfaces\MonitoringResourceRepositoryInterface;

/**
 * This class is designed to manage the monitoring resources.
 *
 * @package Centreon\Domain\Monitoring\MonitoringResource
 */
class MonitoringResourceService extends AbstractCentreonService implements MonitoringResourceServiceInterface
{
    /**
     * @var MonitoringResourceRepositoryInterface
     */
    private $monitoringResourceRepository;

    /**
     * @var ContactInterface
     */
    protected $contact;

    /**
    * @var AccessGroupRepositoryInterface
    */
    private $accessGroupRepository;

    /**
     * @var MonitoringRepositoryInterface
     */
    private $monitoringRepository;

    /**
     * @param MonitoringResourceRepositoryInterface $monitoringResourceRepository
     * @param ContactInterface $contact
     */
    public function __construct(
        MonitoringResourceRepositoryInterface $monitoringResourceRepository,
        ContactInterface $contact,
        AccessGroupRepositoryInterface $accessGroupRepository,
        MonitoringRepositoryInterface $monitoringRepository
    ) {
        $this->contact = $contact;
        $this->monitoringResourceRepository = $monitoringResourceRepository;
        $this->accessGroupRepository = $accessGroupRepository;
        $this->monitoringRepository = $monitoringRepository;
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
     * @inheritDoc
     */
    public function findAllWithAcl(ResourceFilter $filter, ContactInterface $contact): array
    {
        try {
            $accessGroups = $this->accessGroupRepository->findByContact($contact);
            return $this->monitoringResourceRepository->findAllByAccessGroups($filter, $accessGroups);
        } catch (\Throwable $ex) {
            throw MonitoringResourceException::findMonitoringResourcesException($ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findAllWithoutAcl(ResourceFilter $filter): array
    {
        try {
            return $this->monitoringResourceRepository->findAll($filter);
        } catch (\Throwable $ex) {
            throw MonitoringResourceException::findMonitoringResourcesException($ex);
        }
    }
}
