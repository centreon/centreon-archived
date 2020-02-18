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

namespace Centreon\Infrastructure\Monitoring\Metric;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Monitoring\Metric\Interfaces\MetricRepositoryInterface;
use Centreon\Domain\Monitoring\Service;
use Centreon\Infrastructure\DatabaseConnection;
use DateTime;

/**
 * Repository to get metrics data from legacy centreon classes
 *
 * @package Centreon\Infrastructure\Monitoring\Metric
 */
final class MetricRepositoryLegacy implements MetricRepositoryInterface
{
    /**
     * @var ContactInterface
     */
    private $contact;

    /**
     * @var DatabaseConnection
     */
    private $db;

    /**
     * MetricRepositoryLegacy constructor.
     *
     * @param DatabaseConnection $pdo
     */
    public function __construct(DatabaseConnection $db)
    {
        $this->db = $db;

        global $pearDB;
        $pearDB = new \CentreonDB('centreon', 3, true);
    }

    /**
     * @inheritDoc
     */
    public function setContact(ContactInterface $contact): MetricRepositoryInterface
    {
        $this->contact = $contact;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function findMetricsByService(Service $service, \DateTime $start, \DateTime $end): array
    {
        $graph = new \CentreonGraphNg($this->contact->getId());
        $graph->addServiceMetrics($service->getHost()->getId(), $service->getId());

        return $graph->getGraph($start->getTimestamp(), $end->getTimestamp());
    }

    /**
     * @inheritDoc
     */
    public function findStatusByService(Service $service, \DateTime $start, \DateTime $end): array
    {
        $dbStorage = new \CentreonDB('centstorage', 3, true);
        $indexData = \CentreonGraphStatus::getIndexId(
            $service->getHost()->getId(),
            $service->getId(),
            $dbStorage
        );
        $graph = new \CentreonGraphStatus($indexData, $start->getTimestamp(), $end->getTimestamp());

        return $graph->getData(200);
    }
}
