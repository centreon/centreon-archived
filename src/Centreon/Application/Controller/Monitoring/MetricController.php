<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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
 *Controller
 */
declare(strict_types=1);

namespace Centreon\Application\Controller\Monitoring;

use FOS\RestBundle\View\View;
use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Monitoring\Service;
use Centreon\Domain\Exception\EntityNotFoundException;
use Centreon\Application\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Centreon\Domain\Monitoring\Interfaces\MonitoringServiceInterface;
use Centreon\Domain\Monitoring\Metric\Interfaces\MetricServiceInterface;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;

/**
 * This class is design to manage all API REST about metric requests
 *
 * @package Centreon\Application\Controller\Metric\Controller
 */
class MetricController extends AbstractController
{
    /**
     * @var MetricServiceInterface
     */
    private $metricService;

    /**
     * @var MonitoringServiceInterface
     */
    private $monitoringService;

    /**
     * MetricController constructor.
     *
     * @param MetricServiceInterface $metricService
     * @param MonitoringServiceInterface $monitoringService
     */
    public function __construct(
        MetricServiceInterface $metricService,
        MonitoringServiceInterface $monitoringService
    ) {
        $this->metricService = $metricService;
        $this->monitoringService = $monitoringService;
    }

    /**
     * find a service from host id and service id
     *
     * @return Service if the service is found
     * @throws EntityNotFoundException if the host or service is not found
     */
    private function findService(int $hostId, int $serviceId): Service
    {
        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();
        $this->monitoringService->filterByContact($contact);

        $host = $this->monitoringService->findOneHost($hostId);
        if (is_null($host)) {
            throw new EntityNotFoundException(
                sprintf(_('Host %d not found'), $hostId)
            );
        }

        $service = $this->monitoringService->findOneService($hostId, $serviceId);
        if (is_null($service)) {
            throw new EntityNotFoundException(
                sprintf(_('Service %d not found'), $serviceId)
            );
        }
        $service->setHost($host);

        return $service;
    }

    /**
     * convert timestamp to DateTime
     *
     * @param integer $timestamp
     * @param \DateTimeZone $timezone
     * @return \DateTime
     */
    private function formatTimestampToDateTime(int $timestamp, \DateTimeZone $timezone): \DateTime
    {
        return (new \DateTime())
            ->setTimestamp($timestamp)
            ->setTimezone($timezone);
    }

    /**
     * Normalize dates (from timestamp to DateTime using timezone)
     *
     * @param array<string,mixed> $metrics
     * @return array<string,mixed> The normalized metrics
     */
    private function normalizePerformanceMetricsDates(array $metrics): array
    {
        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();
        $timezone = $contact->getTimezone();

        $metrics['global']['start'] = $this->formatTimestampToDateTime((int) $metrics['global']['start'], $timezone);

        $metrics['global']['end'] = $this->formatTimestampToDateTime((int) $metrics['global']['end'], $timezone);

        // Normalize ticks
        foreach ($metrics['times'] as $index => $timestamp) {
            $metrics['times'][$index] = $this->formatTimestampToDateTime((int) $timestamp, $timezone);
        }

        return $metrics;
    }

    /**
     * Validate and extract start/end dates from request parameters
     *
     * @param RequestParametersInterface $requestParameters
     * @return array<\DateTime>
     * @example [new \Datetime('yesterday'), new \Datetime('today')]
     * @throws NotFoundHttpException
     * @throws \LogicException
     */
    private function extractDatesFromRequestParameters(RequestParametersInterface $requestParameters): array
    {
        $start = $requestParameters->getExtraParameter('start') ?: '1 day ago';
        $end = $requestParameters->getExtraParameter('end') ?: 'now';

        foreach (['start' => $start, 'end' => $end] as $param => $value) {
            if (false === strtotime($value)) {
                throw new NotFoundHttpException(sprintf('Invalid date given for parameter "%s".', $param));
            }
        }

        $start = new \DateTime($start);
        $end = new \DateTime($end);

        if ($start >= $end) {
            throw new \RangeException('End date must be greater than start date.');
        }

        return [$start, $end];
    }

    /**
     * Entry point to get service metrics
     *
     * @param int $hostId
     * @param int $serviceId
     * @param \DateTime $start
     * @param \DateTime $end
     * @return View
     * @throws \Exception
     */
    public function getServiceMetrics(
        int $hostId,
        int $serviceId,
        \DateTime $start,
        \DateTime $end
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();

        $service = $this->findService($hostId, $serviceId);

        $metrics = $this->metricService
            ->filterByContact($contact)
            ->findMetricsByService($service, $start, $end);

        return $this->view($metrics);
    }

    /**
     * Entry point to get service status
     *
     * @param int $hostId
     * @param int $serviceId
     * @param \DateTime $start
     * @param \DateTime $end
     * @return View
     * @throws \Exception
     */
    public function getServiceStatus(
        int $hostId,
        int $serviceId,
        \DateTime $start,
        \DateTime $end
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();

        $service = $this->findService($hostId, $serviceId);

        $status = $this->metricService
            ->filterByContact($contact)
            ->findStatusByService($service, $start, $end);

        return $this->view($status);
    }

    /**
     * Entry point to get service performance metrics
     *
     * @param int $hostId
     * @param int $serviceId
     * @return View
     * @throws \Exception
     */
    public function getServicePerformanceMetrics(
        RequestParametersInterface $requestParameters,
        int $hostId,
        int $serviceId
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();

        list($start, $end) = $this->extractDatesFromRequestParameters($requestParameters);

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();

        $service = $this->findService($hostId, $serviceId);

        $metrics = $this->metricService
            ->filterByContact($contact)
            ->findMetricsByService($service, $start, $end);

        $metrics = $this->normalizePerformanceMetricsDates($metrics);

        return $this->view($metrics);
    }

    /**
     * Entry point to get service status metrics
     *
     * @param int $hostId
     * @param int $serviceId
     * @return View
     * @throws \Exception
     */
    public function getServiceStatusMetrics(
        RequestParametersInterface $requestParameters,
        int $hostId,
        int $serviceId
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();

        list($start, $end) = $this->extractDatesFromRequestParameters($requestParameters);

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();

        $service = $this->findService($hostId, $serviceId);

        $status = $this->metricService
            ->filterByContact($contact)
            ->findStatusByService($service, $start, $end);

        return $this->view($status);
    }

    /**
     * Entry point to get meta service performance metrics
     *
     * @param int $metaId
     * @return View
     * @throws \Exception
     */
    public function getMetaServicePerformanceMetrics(
        RequestParametersInterface $requestParameters,
        int $metaId
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();

        list($start, $end) = $this->extractDatesFromRequestParameters($requestParameters);

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();

        $service = $this->monitoringService
            ->filterByContact($contact)
            ->findOneServiceByDescription('meta_' . $metaId);
        if ($service === null) {
            throw new EntityNotFoundException(
                sprintf(_('Meta Service linked to service %d not found'), $metaId)
            );
        }

        $metrics = $this->metricService
            ->filterByContact($contact)
            ->findMetricsByService($service, $start, $end);

        $metrics = $this->normalizePerformanceMetricsDates($metrics);

        return $this->view($metrics);
    }

    /**
     * Entry point to get metaservice status metrics
     *
     * @param int $metaId
     * @return View
     * @throws \Exception
     */
    public function getMetaServiceStatusMetrics(
        RequestParametersInterface $requestParameters,
        int $metaId
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();

        list($start, $end) = $this->extractDatesFromRequestParameters($requestParameters);

        /**
         * @var Contact $contact
         */
        $contact = $this->getUser();

        $service = $this->monitoringService
            ->filterByContact($contact)
            ->findOneServiceByDescription('meta_' . $metaId);
        if ($service === null) {
            throw new EntityNotFoundException(
                sprintf(_('Meta Service linked to service %d not found'), $metaId)
            );
        }

        $status = $this->metricService
            ->filterByContact($contact)
            ->findStatusByService($service, $start, $end);

        return $this->view($status);
    }
}
