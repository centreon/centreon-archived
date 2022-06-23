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
use Centreon\Domain\Monitoring\Interfaces\MonitoringServiceInterface;
use Centreon\Domain\Monitoring\ResourceStatus;
use Centreon\Domain\Monitoring\Timeline\Interfaces\TimelineServiceInterface;
use Centreon\Domain\Monitoring\Timeline\TimelineContact;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Exception\EntityNotFoundException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Centreon\Domain\Monitoring\Timeline\TimelineEvent;

/**
 * @package Centreon\Application\Controller\Monitoring
 */
class TimelineController extends AbstractController
{
    public const SERIALIZER_GROUPS_MAIN = ['timeline_main', 'contact_main', 'resource_status_main'];

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
        $this->timelineService->filterByContact($user);

        $host = $this->monitoringService->findOneHost($hostId);
        if ($host === null) {
            throw new EntityNotFoundException(
                sprintf(_('Host id %d not found'), $hostId)
            );
        }

        $timeline = $this->timelineService->findTimelineEventsByHost($host);

        $context = (new Context())
            ->setGroups(static::SERIALIZER_GROUPS_MAIN)
            ->enableMaxDepth();

        return $this->view([
            'result' => $timeline,
            'meta' => $requestParameters->toArray()
        ])->setContext($context);
    }

    /**
     * Entry point to get timeline for a service
     *
     * @param int $hostId id of host
     * @param int $serviceId id of service
     * @param RequestParametersInterface $requestParameters Request parameters used to filter the request
     * @return View
     * @throws EntityNotFoundException
     */
    public function getServiceTimeline(
        int $hostId,
        int $serviceId,
        RequestParametersInterface $requestParameters
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();

        $context = (new Context())
            ->setGroups(static::SERIALIZER_GROUPS_MAIN)
            ->enableMaxDepth();

        return $this->view(
            [
                'result' => $this->getTimelines($hostId, $serviceId),
                'meta' => $requestParameters->toArray()
            ]
        )->setContext($context);
    }

    public function downloadServiceTimeline(
        int $hostId,
        int $serviceId,
        RequestParametersInterface $requestParameters
    ): StreamedResponse {
        $this->denyAccessUnlessGrantedForApiRealtime();
        $requestParameters->setPage(1);
        $requestParameters->setLimit(1000000000);
        $timeLines = $this->formatTimeLinesForDownload($this->getTimelines($hostId, $serviceId));

        $response = new StreamedResponse();
        $response->setCallback(function () use ($timeLines) {
            $handle = fopen('php://output', 'r+');
            $header = ['type', 'date', 'content', 'contact', 'status', 'tries',];
            fputcsv($handle, $header, ';');

            foreach ($timeLines as $timeLine) {
                fputcsv($handle, $timeLine, ';');
            }

            fclose($handle);
        });
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename="export.csv"');

        return $response;
    }

    /**
     * Entry point to get timeline for a meta service
     *
     * @param int $metaId ID of the Meta
     * @param RequestParametersInterface $requestParameters Request parameters used to filter the request
     * @return View
     * @throws EntityNotFoundException
     */
    public function getMetaServiceTimeline(
        int $metaId,
        RequestParametersInterface $requestParameters
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();

        /**
         * @var Contact $user
         */
        $user = $this->getUser();
        $this->monitoringService->filterByContact($user);
        $this->timelineService->filterByContact($user);

        $service = $this->monitoringService->findOneServiceByDescription('meta_' . $metaId);

        if (is_null($service)) {
            throw new EntityNotFoundException(
                sprintf(
                    _('Meta service %d not found'),
                    $metaId
                )
            );
        }

        $host = $this->monitoringService->findOneHost($service->getHost()->getId());
        if (is_null($host)) {
            throw new EntityNotFoundException(
                sprintf(_('Host meta for meta service %d not found'), $metaId)
            );
        }

        $service->setHost($host);

        $timeline = $this->timelineService->findTimelineEventsByService($service);

        $context = (new Context())
            ->setGroups(static::SERIALIZER_GROUPS_MAIN)
            ->enableMaxDepth();

        return $this->view(
            [
                'result' => $timeline,
                'meta' => $requestParameters->toArray()
            ]
        )->setContext($context);
    }

    /**
     * @param int $hostId
     * @param int $serviceId
     * @return TimelineEvent[]
     * @throws EntityNotFoundException
     */
    private function getTimelines(int $hostId, int $serviceId): array
    {
        /**
         * @var Contact $user
         */
        $user = $this->getUser();
        $this->monitoringService->filterByContact($user);
        $this->timelineService->filterByContact($user);

        $host = $this->monitoringService->findOneHost($hostId);
        if ($host === null) {
            throw new EntityNotFoundException(
                sprintf(_('Host id %d not found'), $hostId)
            );
        }

        $service = $this->monitoringService->findOneService($hostId, $serviceId);
        if ($service === null) {
            throw new EntityNotFoundException(
                sprintf(
                    _('Service %d on host %d not found'),
                    $hostId,
                    $serviceId
                )
            );
        }
        $service->setHost($host);

        return $this->timelineService->findTimelineEventsByService($service);
    }

    /**
     * @param TimelineEvent[] $timeLines
     * @return iterable
     */
    private function formatTimeLinesForDownload(array $timeLines): iterable
    {
        foreach ($timeLines as $timeLine) {
            $date = $timeLine->getDate() instanceof \DateTime ? $timeLine->getDate()->format('c') : '';
            $contact = $timeLine->getContact() instanceof TimelineContact ? $timeLine->getContact()->getName() : '';
            $status = $timeLine->getStatus() instanceof ResourceStatus ? $timeLine->getStatus()->getName() : '';

            yield [
                'type' => $timeLine->getType() ?? '',
                'date' => $date,
                'content' => $timeLine->getContent(),
                'contact' => $contact,
                'status' => $status,
                'tries' => $timeLine->getTries(),
            ];
        }
    }
}
