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

namespace Centreon\Domain\Acknowledgement\Model;

use Centreon\Domain\Acknowledgement\Acknowledgement;
use Centreon\Domain\Acknowledgement\Interfaces\ResourceAcknowledgementInterface;
use Centreon\Domain\Exception\EntityNotFoundException;
use Centreon\Domain\Engine\Interfaces\EngineServiceInterface;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;

class HostAcknowledgement implements ResourceAcknowledgementInterface
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
        return $resourceType === 'host';
    }

    /**
     * {@inheritDoc}
     * @param int $resourceId
     * @param int|null $parentResourceId
     */
    public function addAcknowledgement(Acknowledgement $acknowledgement, $resourceId, $parentResourceId): void
    {
        $host = $this->monitoringRepository->findOneHost($resourceId);
        if ($host === null) {
            throw new EntityNotFoundException(_('Host not found'));
        }
        $this->engineService->addHostAcknowledgement($acknowledgement, $host);
        if ($acknowledgement->isWithServices()) {
            $services = $this->monitoringRepository->findServicesByHostWithoutRequestParameters($host->getId());
            foreach ($services as $service) {
                $service->setHost($host);
                $this->engineService->addServiceAcknowledgement($acknowledgement, $service);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function removeAcknowledgement(Acknowledgement $acknowledgement, $resourceId, $parentResourceId): void
    {
        $host = $this->monitoringRepository->findOneHost($resourceId);
        if ($host === null) {
            throw new EntityNotFoundException(_('Host not found'));
        }

        $this->engineService->disacknowledgeHost($host);

        if ($acknowledgement->isWithServices()) {
            $services = $this->monitoringRepository->findServicesByHostWithoutRequestParameters($host->getId());
            foreach ($services as $service) {
                $service->setHost($host);
                $this->engineService->disacknowledgeService($service);
            }
        }
    }
}
