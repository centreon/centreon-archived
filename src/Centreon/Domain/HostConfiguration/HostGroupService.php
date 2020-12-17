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

namespace Centreon\Domain\HostConfiguration;

use Centreon\Domain\ActionLog\ActionLog;
use Centreon\Domain\ActionLog\Interfaces\ActionLogServiceInterface;
use Centreon\Domain\HostConfiguration\Exception\HostGroupException;
use Centreon\Domain\HostConfiguration\Interfaces\HostGroupRepositoryInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostGroupServiceInterface;
use Centreon\Domain\HostConfiguration\Model\HostGroup;

class HostGroupService implements HostGroupServiceInterface
{
    /**
     * @var HostGroupRepositoryInterface
     */
    private $hostGroupRepository;

    /**
     * @var ActionLogServiceInterface
     */
    private $actionLogService;

    /**
     * @param HostGroupRepositoryInterface $hostGroupRepository
     * @param ActionLogServiceInterface $actionLogService
     */
    public function __construct(
        HostGroupRepositoryInterface $hostGroupRepository,
        ActionLogServiceInterface $actionLogService
    ) {
        $this->hostGroupRepository = $hostGroupRepository;
        $this->actionLogService = $actionLogService;
    }

    /**
     * @inheritDoc
     */
    public function findHostGroups(): array
    {
        try {
            return $this->hostGroupRepository->findHostGroups();
        } catch (\Throwable $ex) {
            throw HostGroupException::searchHostGroupsException($ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function getNumberOfHostGroups(): int
    {
        try {
            return $this->hostGroupRepository->getNumberOfHostGroups();
        } catch (\Throwable $ex) {
            throw HostGroupException::countHostGroupsException($ex);
        }
    }
}
