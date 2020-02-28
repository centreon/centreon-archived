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

use Centreon\Domain\Monitoring\Interfaces\ResourceServiceInterface;
use Centreon\Domain\Monitoring\Interfaces\ResourceRepositoryInterface;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Centreon\Domain\Service\AbstractCentreonService;

/**
 * Service manage the resources in real-time monitoring : hosts and services.
 *
 * @package Centreon\Domain\Monitoring
 */
class ResourceService extends AbstractCentreonService implements ResourceServiceInterface
{
    /**
     * @var \Centreon\Domain\Monitoring\Interfaces\MonitoringResourceRepositoryInterface
     */
    private $resourceRepository;

    /**
     * @var AccessGroupRepositoryInterface
     */
    private $accessGroupRepository;

    /**
     * @param ResourceRepositoryInterface $resourceRepository
     * @param AccessGroupRepositoryInterface $accessGroupRepository
     */
    public function __construct(
        ResourceRepositoryInterface $resourceRepository,
        AccessGroupRepositoryInterface $accessGroupRepository
    ) {
        $this->resourceRepository = $resourceRepository;
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

        $this->resourceRepository
            ->setContact($this->contact)
            ->filterByAccessGroups($this->accessGroupRepository->findByContact($contact));

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function findResources(?string $filterState): array
    {
        return $this->resourceRepository->findResources($filterState);
    }
}
