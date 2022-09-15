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
use Centreon\Domain\Monitoring\Host;
use Centreon\Domain\Monitoring\Interfaces\MonitoringServiceInterface;
use Centreon\Domain\Monitoring\ResourceStatus;
use Centreon\Domain\Monitoring\Timeline\Interfaces\TimelineServiceInterface;
use Centreon\Domain\Monitoring\Timeline\TimelineContact;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
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

        $host = $this->getHostById($hostId);
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
                'result' => $this->getTimelinesByHostIdAndServiceId($hostId, $serviceId),
                'meta' => $requestParameters->toArray()
            ]
        )->setContext($context);
    }

    /**
     * @param int $hostId
     * @param RequestParametersInterface $requestParameters
     * @return StreamedResponse
     */
    public function downloadHostTimeline(int $hostId, RequestParametersInterface $requestParameters): StreamedResponse
    {
        $this->denyAccessUnlessGrantedForApiRealtime();
        $this->addDownloadParametersInRequestParameters($requestParameters);
        $timeLines = $this->formatTimeLinesForDownload($this->getTimelinesByHostId($hostId));

        return $this->streamTimeLines($timeLines);
    }

    /**
     * @param int $hostId
     * @param int $serviceId
     * @param RequestParametersInterface $requestParameters
     * @return StreamedResponse
     * @throws EntityNotFoundException
     */
    public function downloadServiceTimeline(
        int $hostId,
        int $serviceId,
        RequestParametersInterface $requestParameters
    ): StreamedResponse {
        $this->denyAccessUnlessGrantedForApiRealtime();
        $this->addDownloadParametersInRequestParameters($requestParameters);
        $timeLines = $this->formatTimeLinesForDownload($this->getTimelinesByHostIdAndServiceId($hostId, $serviceId));

        return $this->streamTimeLines($timeLines);
    }

    /**
     * @param RequestParametersInterface $requestParameters
     * @return void
     */
    private function addDownloadParametersInRequestParameters(RequestParametersInterface $requestParameters): void
    {
        $requestParameters->setPage(1);
        $requestParameters->setLimit(1000000000);
    }

    /**
     * @param \Iterator<String[]> $timeLines
     * @return StreamedResponse
     */
    private function streamTimeLines(iterable $timeLines): StreamedResponse
    {
        $response = new StreamedResponse();
        $response->setCallback(function () use ($timeLines) {
            $handle = fopen('php://output', 'r+');
            if ($handle === false) {
                throw new \RuntimeException('Unable to generate file');
            }
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
     * @return TimelineEvent[]
     */
    private function getMetaServiceTimelineById(int $metaId): array
    {
        $user = $this->getUser();
        $this->monitoringService->filterByContact($user);
        $this->timelineService->filterByContact($user);

        $service = $this->monitoringService->findOneServiceByDescription('meta_' . $metaId);

        if (is_null($service)) {
            $errorMsg = sprintf(_('Meta service %d not found'), $metaId);
            throw new EntityNotFoundException($errorMsg);
        }

        $serviceHost = $service->getHost();
        if (!$serviceHost instanceof Host) {
            throw new EntityNotFoundException(sprintf(_('Unable to find host by service id %d'), $service->getId()));
        }

        $serviceHostId = $serviceHost->getId() ?? 0;

        $host = $this->monitoringService->findOneHost($serviceHostId);
        if (is_null($host)) {
            throw new EntityNotFoundException(
                sprintf(_('Host meta for meta service %d not found'), $metaId)
            );
        }

        $service->setHost($host);

        return $this->timelineService->findTimelineEventsByService($service);
    }

    /**
     * Entry point to get timeline for a meta service
     *
     * @param int $metaId ID of the Meta
     * @param RequestParametersInterface $requestParameters Request parameters used to filter the request
     * @return View
     * @throws EntityNotFoundException
     */
    public function getMetaServiceTimeline(int $metaId, RequestParametersInterface $requestParameters): View
    {
        $this->denyAccessUnlessGrantedForApiRealtime();

        $context = (new Context())
            ->setGroups(static::SERIALIZER_GROUPS_MAIN)
            ->enableMaxDepth();

        return $this->view(
            [
                'result' => $this->getMetaServiceTimelineById($metaId),
                'meta' => $requestParameters->toArray()
            ]
        )->setContext($context);
    }

    /**
     * @param int $metaId
     * @param RequestParametersInterface $requestParameters
     * @return StreamedResponse
     * @throws EntityNotFoundException
     */
    public function downloadMetaserviceTimeline(
        int $metaId,
        RequestParametersInterface $requestParameters
    ): StreamedResponse {
        $this->denyAccessUnlessGrantedForApiRealtime();
        $this->addDownloadParametersInRequestParameters($requestParameters);

        $timeLines = $this->formatTimeLinesForDownload($this->getMetaServiceTimelineById($metaId));

        return $this->streamTimeLines($timeLines);
    }

    /**
     * @param int $hostId
     * @return TimelineEvent[]
     */
    private function getTimelinesByHostId(int $hostId): array
    {
        $host = $this->getHostById($hostId);

        return $this->timelineService->findTimelineEventsByHost($host);
    }

    /**
     * @param int $hostId
     * @param int $serviceId
     * @return TimelineEvent[]
     * @throws EntityNotFoundException
     */
    private function getTimelinesByHostIdAndServiceId(int $hostId, int $serviceId): array
    {
        $host = $this->getHostById($hostId);

        $service = $this->monitoringService->findOneService($hostId, $serviceId);
        if ($service === null) {
            $errorMsg = sprintf(_('Service %d on host %d not found'), $hostId, $serviceId);
            throw new EntityNotFoundException($errorMsg);
        }
        $service->setHost($host);

        return $this->timelineService->findTimelineEventsByService($service);
    }

    /**
     * @param int $hostId
     * @return Host
     * @throws EntityNotFoundException
     */
    private function getHostById(int $hostId): Host
    {
        $user = $this->getUser();
        $this->monitoringService->filterByContact($user);
        $this->timelineService->filterByContact($user);

        $host = $this->monitoringService->findOneHost($hostId);
        if ($host === null) {
            throw new EntityNotFoundException(sprintf(_('Host id %d not found'), $hostId));
        }

        return $host;
    }

    /**
     * @param TimelineEvent[] $timeLines
     * @return \Iterator
     */
    private function formatTimeLinesForDownload(array $timeLines): iterable
    {
        foreach ($timeLines as $timeLine) {
            $date = $timeLine->getDate() instanceof \DateTime ? $timeLine->getDate()->format('c') : '';
            $contact = $timeLine->getContact() instanceof TimelineContact ? $timeLine->getContact()->getName() : '';
            $status = $timeLine->getStatus() instanceof ResourceStatus ? $timeLine->getStatus()->getName() : '';
            $tries = $timeLine->getTries() ?? '';

            yield [
                'type' => $timeLine->getType() ?? '',
                'date' => $date,
                'content' => $timeLine->getContent(),
                'contact' => $contact,
                'status' => $status,
                'tries' => (string) $tries,
            ];
        }
    }
}
