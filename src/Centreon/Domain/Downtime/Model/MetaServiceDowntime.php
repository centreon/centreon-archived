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
 *
 */
declare(strict_types=1);

namespace Centreon\Domain\Downtime\Model;

use Centreon\Domain\Downtime\Downtime;
use Centreon\Domain\Exception\EntityNotFoundException;
use Centreon\Domain\Engine\Interfaces\EngineServiceInterface;
use Centreon\Domain\Downtime\Interfaces\ResourceDowntimeInterface;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;

class MetaServiceDowntime implements ResourceDowntimeInterface
{
    /**
     * @var EngineServiceInterface
     */
    private $engineService;

        /**
     * @var MonitoringRepositoryInterface
     */
    private $monitoringRepository;

    public function __construct(
        EngineServiceInterface $engineService,
        MonitoringRepositoryInterface $monitoringRepository
    ) {
        $this->monitoringRepository = $monitoringRepository;
        $this->engineService = $engineService;
    }

    /**
     * {@inheritDoc}
     */
    public function isForResource(string $resourceType): bool
    {
        return $resourceType === 'metaservice';
    }

    /**
     * {@inheritDoc}
     * @param int $resourceId
     * @param int|null $parentResourceId
     */
    public function addDowntime(Downtime $downtime, $resourceId, $parentResourceId): void
    {
        $service = $this->monitoringRepository->findOneServiceByDescription(
            'meta_' . $resourceId
        );
        if ($service === null) {
            throw new EntityNotFoundException(_('Service not found'));
        }

        $host = $this->monitoringRepository->findOneHost($service->getHost()->getId());
        if ($host === null) {
            throw new EntityNotFoundException(_('Host not found'));
        }
        $service->setHost($host);
        $this->engineService->addServiceDowntime($downtime, $service);
    }
}
