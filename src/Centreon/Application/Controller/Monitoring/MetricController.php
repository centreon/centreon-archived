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
 *Controller
 */
declare(strict_types=1);

namespace Centreon\Application\Controller\Monitoring;

use Centreon\Application\Controller\AbstractController;
use Centreon\Domain\Monitoring\Metric\Interfaces\MetricServiceInterface;
use Centreon\Domain\Monitoring\Interfaces\MonitoringServiceInterface;
use FOS\RestBundle\View\View;
use Centreon\Domain\Exception\EntityNotFoundException;

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
     */
    public function __construct(
        MetricServiceInterface $metricService,
        MonitoringServiceInterface $monitoringService
    ) {
        $this->metricService = $metricService;
        $this->monitoringService = $monitoringService;
    }

    /**
     * Entry point to get service metrics
     *
     * @param int $hostId
     * @param int $serviceId
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
         * @var $contact Contact
         */
        $contact = $this->getUser();
        $this->monitoringService->filterByContact($contact);

        $host = $this->monitoringService->findOneHost($hostId);
        if (is_null($host)) {
            throw new EntityNotFoundException('Host not found');
        }

        $service = $this->monitoringService->findOneService($hostId, $serviceId);
        if (is_null($service)) {
            throw new EntityNotFoundException('Service not found');
        }
        $service->setHost($host);

        $metrics = $this->metricService
            ->filterByContact($contact)
            ->findMetricsByService($service, $start, $end);

        return $this->view($metrics);
    }
}
