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

namespace Centreon\Domain\Monitoring\MonitoringResource\UseCase\v2110\FindMonitoringResources;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Log\LoggerTrait;
use Centreon\Domain\Monitoring\MonitoringResource\Interfaces\MonitoringResourceServiceInterface;
use Centreon\Domain\Monitoring\ResourceFilter;

/**
 * This class is designed to represent a use case to find all monitoring resources.
 *
 * @package Centreon\Domain\Monitoring\MonitoringResource\UseCase\v2110\FindMonitoringResources
 */
class FindMonitoringResources
{
    use LoggerTrait;

    /**
     * @var MonitoringResourceServiceInterface
     */
    private $monitoringResourceService;

    /**
     * @var ContactInterface
     */
    private $contact;

    /**
     * FindMonitoringResources constructor.
     *
     * @param MonitoringResourceServiceInterface $monitoringResourceService
     * @param ContactInterface $contact
     */
    public function __construct(
        MonitoringResourceServiceInterface $monitoringResourceService,
        ContactInterface $contact
    ) {
        $this->monitoringResourceService = $monitoringResourceService;
        $this->contact = $contact;
    }

    /**
     * Execute the use case for which this class was designed.
     *
     * @return FindMonitoringResourcesResponse
     * @throws \Centreon\Domain\Monitoring\MonitoringResource\Exception\MonitoringResourceException
     */
    public function execute(ResourceFilter $filter): FindMonitoringResourcesResponse
    {
        $response = new FindMonitoringResourcesResponse();
        $this->debug(
            '[RESOURCES] Retrieving monitoring resources using filter',
            [
                'monitoring_resource_types' => $filter->getTypes(),
                'monitoring_resource_statuses' => $filter->getStatuses(),
                'monitoring_resource_states' => $filter->getStates(),
                'monitoring_resource_monitoring_servers_ids' => $filter->getMonitoringServerIds()
            ]
        );
        $monitoringResources = ($this->contact->isAdmin())
            ? $this->monitoringResourceService->findAllWithoutAcl($filter)
            : $this->monitoringResourceService->findAllWithAcl($filter, $this->contact);
        $response->setMonitoringResources($monitoringResources);
        return $response;
    }
}
