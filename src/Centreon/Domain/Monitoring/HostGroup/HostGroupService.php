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

namespace Centreon\Domain\Monitoring\HostGroup;

use Centreon\Domain\Service\AbstractCentreonService;
use Centreon\Domain\Monitoring\HostGroup\Interfaces\HostGroupServiceInterface;
use Centreon\Domain\Monitoring\HostGroup\Interfaces\HostGroupRepositoryInterface;
use Core\Security\AccessGroup\Application\Repository\ReadAccessGroupRepositoryInterface;

final class HostGroupService extends AbstractCentreonService implements HostGroupServiceInterface
{
    /**
     * @var HostGroupRepositoryInterface
     */
    private $hostGroupRepository;

    /**
     * @var ReadAccessGroupRepositoryInterface
     */
    private $accessGroupRepository;

    /**
     * @param HostGroupRepositoryInterface $hostGroupRepository
     * @param ReadAccessGroupRepositoryInterface $accessGroupRepository
     */
    public function __construct(
        HostGroupRepositoryInterface $hostGroupRepository,
        ReadAccessGroupRepositoryInterface $accessGroupRepository
    ) {
        $this->hostGroupRepository = $hostGroupRepository;
        $this->accessGroupRepository = $accessGroupRepository;
    }

    /**
     * @inheritDoc
     */
    public function filterByContact($contact): HostGroupServiceInterface
    {
        parent::filterByContact($contact);

        $this->hostGroupRepository
            ->setContact($this->contact)
            ->filterByAccessGroups($this->accessGroupRepository->findByContact($this->contact));

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function findHostGroupsByIds(array $hostGroupIds): array
    {
        try {
            return $this->hostGroupRepository->findHostGroupsByIds($hostGroupIds);
        } catch (\Throwable $e) {
            throw new HostGroupException(_('Error when searching hostgroups'), 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function findHostGroupsByNames(array $hostGroupNames): array
    {
        try {
            return $this->hostGroupRepository->findHostGroupsByNames($hostGroupNames);
        } catch (\Throwable $e) {
            throw new HostGroupException(_('Error when searching hostgroups'), 0, $e);
        }
    }
}
