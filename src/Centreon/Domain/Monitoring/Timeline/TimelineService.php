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

namespace Centreon\Domain\Monitoring\Timeline;

use Centreon\Domain\Monitoring\Timeline\Interfaces\TimelineServiceInterface;
use Centreon\Domain\Monitoring\Timeline\Interfaces\TimelineRepositoryInterface;
use Core\Security\AccessGroup\Application\Repository\ReadAccessGroupRepositoryInterface;
use Centreon\Domain\Service\AbstractCentreonService;
use Centreon\Domain\Monitoring\Host;
use Centreon\Domain\Monitoring\Service;

/**
 * Monitoring class used to manage the real time services and hosts
 *
 * @package Centreon\Domain\Monitoring\Timeline
 */
class TimelineService extends AbstractCentreonService implements TimelineServiceInterface
{
    /**
     * @var TimelineRepositoryInterface
     */
    private $timelineRepository;

    /**
     * @var ReadAccessGroupRepositoryInterface
     */
    private $accessGroupRepository;

    /**
     * @param TimelineRepositoryInterface $timelineRepository
     * @param ReadAccessGroupRepositoryInterface $accessGroupRepository
     */
    public function __construct(
        TimelineRepositoryInterface $timelineRepository,
        ReadAccessGroupRepositoryInterface $accessGroupRepository
    ) {
        $this->timelineRepository = $timelineRepository;
        $this->accessGroupRepository = $accessGroupRepository;
    }

    /**
     * @inheritDoc
     */
    public function filterByContact($contact)
    {
        parent::filterByContact($contact);

        $this->timelineRepository
            ->setContact($this->contact)
            ->filterByAccessGroups($this->accessGroupRepository->findByContact($contact));

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function findTimelineEventsByHost(Host $host): array
    {
        return $this->timelineRepository->findTimelineEventsByHost($host);
    }

    /**
     * @inheritDoc
     */
    public function findTimelineEventsByService(Service $service): array
    {
        return $this->timelineRepository->findTimelineEventsByService($service);
    }
}
