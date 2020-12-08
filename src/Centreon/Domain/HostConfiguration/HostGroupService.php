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
use Centreon\Domain\HostConfiguration\Interfaces\HostGroupRepositoryInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostGroupServiceInterface;
use Centreon\Domain\HostConfiguration\Model\HostGroup;
use Centreon\Domain\Repository\RepositoryException;

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
    public function findHostGroup(int $hgId): array
    {
        try {
            return $this->hostGroupRepository->findHostGroup($hgId);
        } catch (\Throwable $ex) {
            throw new HostGroupException(_('Error while searching for the host group'), 0, $ex);
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
            throw new HostConfigurationException(_('Error while searching for the number of host group'), 0, $ex);
        }
    }
    
    /**
     * @inheritDoc
     */
    public function changeActivationStatus(HostGroup $hostGroup, bool $shouldBeActivated): void
    {
        try {
            if ($hostGroup->getId() === null) {
                throw new HostConfigurationException(_('Host id cannot be null'));
            }
            if ($hostGroup->getName() === null) {
                throw new HostConfigurationException(_('Host name cannot be null'));
            }
            $loadedHost = $this->findHostGroup($hostGroup->getId());
            if ($loadedHost === null) {
                throw new HostGroupException(sprintf(_('Host Group %d not found'), $hostGroup->getId()));
            }
            if ($loadedHost->getId() ===  null) {
                throw new HostConfigurationException(_('Host id cannot be null'));
            }
            $this->hostGroupRepository->changeActivationStatus($loadedHost->getId(), $shouldBeActivated);
            $this->actionLogService->addAction(
            // The userId is set to 0 because it is not yet possible to determine who initiated the action.
            // We will see later how to get it back.
                new ActionLog(
                    'hostGroup',
                    $hostGroup->getId(),
                    $hostGroup->getName(),
                    $shouldBeActivated ? ActionLog::ACTION_TYPE_ENABLE : ActionLog::ACTION_TYPE_DISABLE,
                    0
                )
            );
        } catch (HostGroupException $ex) {
            throw $ex;
        } catch (\Throwable $ex) {
            throw new HostGroupException(
                sprintf(
                    _('Error when changing host group status (%d to %s)'),
                    $hostGroup->getId(),
                    $shouldBeActivated ? 'true' : 'false'
                ),
                0,
                $ex
            );
        }
    }

    /**
    * @inheritDoc
    */
    public function findHostGroupNamesAlreadyUsed(array $namesToCheck): array
    {
        try {
            return $this->hostGroupRepository->findHostGroupNamesAlreadyUsed($namesToCheck);
        } catch (\Throwable $ex) {
            throw new HostGroupException(_('Error when searching for already used host names'));
        }
    }
}
