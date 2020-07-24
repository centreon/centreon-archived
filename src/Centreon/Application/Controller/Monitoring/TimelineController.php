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

namespace Centreon\Application\Controller\Monitoring;

use Centreon\Application\Controller\AbstractController;
use Centreon\Domain\Monitoring\Entity\AckEventObject;
use Centreon\Domain\Monitoring\Entity\CommentEventObject;
use Centreon\Domain\Monitoring\Entity\DowntimeEventObject;
use Centreon\Domain\Monitoring\Entity\LogEventObject;
use Centreon\Domain\Monitoring\Interfaces\MonitoringServiceInterface;
use Centreon\Domain\Monitoring\Timeline\Interfaces\TimelineServiceInterface;
use Centreon\Domain\Monitoring\Timeline\TimelineEvent;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Exception\EntityNotFoundException;

/**
 * @package Centreon\Application\Controller\Monitoring
 */
class TimelineController extends AbstractController
{
    /**
     * @var MonitoringServiceInterface
     */
    private $monitoringService;

    /**
     * @var TimelineServiceInterface
     */
    private $timelineService;

    /**
     * TimelineController constructor.
     *
     * @param MonitoringServiceInterface $monitoringService
     * @param TimelineServiceInterface $timelineService
     */
    public function __construct(
        MonitoringServiceInterface $monitoringService,
        TimelineServiceInterface $timelineService
    ) {
        $this->monitoringService = $monitoringService;
        $this->timelineService = $timelineService;
    }


    /**
     * Entry point to get timeline for a host
     *
     * @param int $hostId id of host
     * @param RequestParametersInterface $requestParameters Request parameters used to filter the request
     * @return View
     * @throws EntityNotFoundException
     */
    public function getHostTimeline(
        int $hostId,
        RequestParametersInterface $requestParameters
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();

        /**
         * @var Contact $user
         */
        $user = $this->getUser();
        $this->monitoringService->filterByContact($user);

        $host = $this->monitoringService->findOneHost($hostId);
        if ($host === null) {
            throw new EntityNotFoundException(
                sprintf(_('Host id %d not found'), $hostId)
            );
        }

        $timeline = $this->timelineService->findTimelineEventsByHost($host);

        return $this->view(
            [
                'result' => $timeline,
                'meta' => $requestParameters->toArray()
            ]
            );

        $context = (new Context())
            ->setGroups([
                LogEventObject::SERIALIZER_GROUP_LIST,
                CommentEventObject::SERIALIZER_GROUP_LIST,
                DowntimeEventObject::SERIALIZER_GROUP_LIST,
                AckEventObject::SERIALIZER_GROUP_LIST,
                TimelineEvent::SERIALIZER_GROUP_LIST,
            ])
            ->enableMaxDepth();

        return $this->view(
            [
                'result' => $timeline,
                'meta' => $requestParameters->toArray()
            ]
        )->setContext($context);
    }
}
