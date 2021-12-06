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

namespace Centreon\Domain\Monitoring\ServiceGroup;

use Centreon\Domain\Service\AbstractCentreonService;
use Centreon\Domain\Monitoring\ServiceGroup\Interfaces\ServiceGroupServiceInterface;
use Centreon\Domain\Monitoring\ServiceGroup\Interfaces\ServiceGroupRepositoryInterface;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;

final class ServiceGroupService extends AbstractCentreonService implements ServiceGroupServiceInterface
{
    /**
     * @var ServiceGroupRepositoryInterface
     */
    private $serviceGroupRepository;

    /**
     * @var AccessGroupRepositoryInterface
     */
    private $accessGroupRepository;

    /**
     * @param ServiceGroupRepositoryInterface $serviceGroupRepository
     * @param AccessGroupRepositoryInterface $accessGroupRepository
     */
    public function __construct(
        ServiceGroupRepositoryInterface $serviceGroupRepository,
        AccessGroupRepositoryInterface $accessGroupRepository
    ) {
        $this->serviceGroupRepository = $serviceGroupRepository;
        $this->accessGroupRepository = $accessGroupRepository;
    }

    /**
     * @inheritDoc
     */
    public function filterByContact($contact): ServiceGroupServiceInterface
    {
        parent::filterByContact($contact);

        $this->serviceGroupRepository
            ->setContact($this->contact)
            ->filterByAccessGroups($this->accessGroupRepository->findByContact($this->contact));

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function findServiceGroupsByIds(array $serviceGroupIds): array
    {
        try {
            return $this->serviceGroupRepository->findServiceGroupsByIds($serviceGroupIds);
        } catch (\Throwable $e) {
            throw new ServiceGroupException(_('Error when searching servicegroups'), 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function findServiceGroupsByNames(array $serviceGroupNames): array
    {
        try {
            return $this->serviceGroupRepository->findServiceGroupsByNames($serviceGroupNames);
        } catch (\Throwable $e) {
            throw new ServiceGroupException(_('Error when searching servicegroups'), 0, $e);
        }
    }
}
