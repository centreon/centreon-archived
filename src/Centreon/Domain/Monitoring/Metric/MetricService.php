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

namespace Centreon\Domain\Monitoring\Metric;

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Monitoring\Service;
use Centreon\Domain\Monitoring\Metric\Interfaces\MetricServiceInterface;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;
use Centreon\Domain\Monitoring\Metric\Interfaces\MetricRepositoryInterface;
use Core\Security\AccessGroup\Application\Repository\ReadAccessGroupRepositoryInterface;
use Centreon\Domain\Service\AbstractCentreonService;

/**
 * Monitoring class used to manage the real time services and hosts
 *
 * @package Centreon\Domain\Monitoring
 */
class MetricService extends AbstractCentreonService implements MetricServiceInterface
{
    /**
     * @var MonitoringRepositoryInterface
     */
    private $monitoringRepository;

    /**
     * @var MetricRepositoryInterface
     */
    private $metricRepository;

    /**
     * @var ReadAccessGroupRepositoryInterface
     */
    private $accessGroupRepository;

    /**
     * @param MonitoringRepositoryInterface $monitoringRepository
     * @param MetricRepositoryInterface $metricRepository
     * @param ReadAccessGroupRepositoryInterface $accessGroupRepository
     */
    public function __construct(
        MonitoringRepositoryInterface $monitoringRepository,
        MetricRepositoryInterface $metricRepository,
        ReadAccessGroupRepositoryInterface $accessGroupRepository
    ) {
        $this->monitoringRepository = $monitoringRepository;
        $this->metricRepository = $metricRepository;
        $this->accessGroupRepository = $accessGroupRepository;
    }

    /**
     * {@inheritDoc}
     * @param Contact $contact
     * @return self
     */
    public function filterByContact($contact): self
    {
        parent::filterByContact($contact);

        $accessGroups = $this->accessGroupRepository->findByContact($contact);

        $this->monitoringRepository
            ->setContact($this->contact)
            ->filterByAccessGroups($accessGroups);

        $this->metricRepository->setContact($this->contact);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function findMetricsByService(Service $service, \DateTime $start, \DateTime $end): array
    {
        return $this->metricRepository->findMetricsByService($service, $start, $end);
    }

    /**
     * @inheritDoc
     */
    public function findStatusByService(Service $service, \DateTime $start, \DateTime $end): array
    {
        return $this->metricRepository->findStatusByService($service, $start, $end);
    }
}
