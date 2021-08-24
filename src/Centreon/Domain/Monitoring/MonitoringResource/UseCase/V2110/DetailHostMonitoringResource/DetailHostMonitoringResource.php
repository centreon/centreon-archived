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

namespace Centreon\Domain\Monitoring\MonitoringResource\UseCase\v2110\DetailHostMonitoringResource;

use Centreon\Domain\Monitoring\ResourceGroup;
use Centreon\Domain\Monitoring\ResourceFilter;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;
use Centreon\Domain\Monitoring\MonitoringResource\Interfaces\MonitoringResourceServiceInterface;
use Centreon\Domain\Monitoring\MonitoringResource\UseCase\v2110\DetailHostMonitoringResource as DetailHost;

/**
 * This class is designed to represent a use case to detail a host monitoring resource
 *
 * @package Centreon\Domain\Monitoring\MonitoringResource\UseCase\v2110\DetailHostMonitoringResource
 */
class DetailHostMonitoringResource
{
    /**
     * @var MonitoringResourceServiceInterface
     */
    private $monitoringResourceService;

    /**
     * @var ContactInterface
     */
    private $contact;

    /**
     * @var MonitoringRepositoryInterface
     */
    private $monitoringRepository;

    /**
     * DetailHostMonitoringResource constructor.
     *
     * @param MonitoringResourceServiceInterface $monitoringResourceService
     * @param ContactInterface $contact
     * @param MonitoringRepositoryInterface $monitoringRepository
     */
    public function __construct(
        MonitoringResourceServiceInterface $monitoringResourceService,
        ContactInterface $contact,
        MonitoringRepositoryInterface $monitoringRepository
    ) {
        $this->monitoringResourceService = $monitoringResourceService;
        $this->contact = $contact;
        $this->monitoringRepository = $monitoringRepository;
    }

    /**
     * Execute the use case for which this class was designed.
     *
     * @return DetailHost\DetailHostMonitoringResourceResponse
     * @throws \Centreon\Domain\Monitoring\MonitoringResource\Exception\MonitoringResourceException
     */
    public function execute(ResourceFilter $filter): DetailHost\DetailHostMonitoringResourceResponse
    {
        $response = new DetailHost\DetailHostMonitoringResourceResponse();
        $monitoringResource = ($this->contact->isAdmin())
            ? $this->monitoringResourceService->findAllWithoutAcl($filter)
            : $this->monitoringResourceService->findAllWithAcl($filter, $this->contact);

        // getting downtimes information
        $hostMonitoringResource = $monitoringResource[0];
        $downtimes = $this->monitoringRepository->findDowntimes($hostMonitoringResource->getId(), 0);
        $hostMonitoringResource->setDowntimes($downtimes);

        // getting acknowledgements information
        if ($hostMonitoringResource->getAcknowledged()) {
            $acknowledgements = $this->monitoringRepository->findAcknowledgements($hostMonitoringResource->getId(), 0);
            if (!empty($acknowledgements)) {
                $hostMonitoringResource->setAcknowledgement($acknowledgements[0]);
            }
        }

        // getting host groups belonging information
        $hostGroups = $this->monitoringRepository
            ->findHostGroups($hostMonitoringResource->getId());

        $hostMonitoringResourceGroups = [];
        foreach ($hostGroups as $hostGroup) {
            $hostMonitoringResourceGroups[] = new ResourceGroup($hostGroup->getId(), $hostGroup->getName());
        }

        $hostMonitoringResource->setGroups($hostMonitoringResourceGroups);

        $response->setHostMonitoringResourceDetail($hostMonitoringResource);
        return $response;
    }
}
