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
use Centreon\Domain\Service\AbstractCentreonService;
use Centreon\Domain\Monitoring\Host;

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
     * @param TimelineRepositoryInterface $timelineRepository
     */
    public function __construct(TimelineRepositoryInterface $timelineRepository)
    {
        $this->timelineRepository = $timelineRepository;
    }

    /**
     * @inheritDoc
     */
    public function filterByContact($contact)
    {
    }

    /**
     * @inheritDoc
     */
    public function findTimelineEventsByHost(Host $host): array
    {
        return $this->timelineRepository->findTimelineEventsByHost($host);
    }
}
