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

namespace Centreon\Domain\Monitoring\MonitoringResource\UseCase\v2110\DetailMetaServiceMonitoringResource;

use Centreon\Domain\Monitoring\ResourceFilter;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;
use Centreon\Domain\Monitoring\MonitoringResource\Interfaces\MonitoringResourceServiceInterface;
use Centreon\Domain\MetaServiceConfiguration\Interfaces\MetaServiceConfigurationReadRepositoryInterface;
use Centreon\Domain\Monitoring\MonitoringResource\UseCase\v2110\DetailMetaServiceMonitoringResource as DetailMeta;

/**
 * This class is designed to represent a use case to detail a service monitoring resource
 *
 * @package Centreon\Domain\Monitoring\MonitoringResource\UseCase\v2110\DetailMetaServiceMonitoringResource
 */
class DetailMetaServiceMonitoringResource
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
     * @var MetaServiceConfigurationReadRepositoryInterface
     */
    private $metaServiceConfigurationRepository;

    /**
     * DetailMetaServiceMonitoringResource constructor.
     *
     * @param MonitoringResourceServiceInterface $monitoringResourceService
     * @param ContactInterface $contact
     * @param MonitoringRepositoryInterface $monitoringRepository
     */
    public function __construct(
        MonitoringResourceServiceInterface $monitoringResourceService,
        ContactInterface $contact,
        MonitoringRepositoryInterface $monitoringRepository,
        MetaServiceConfigurationReadRepositoryInterface $metaServiceConfigurationRepository
    ) {
        $this->monitoringResourceService = $monitoringResourceService;
        $this->contact = $contact;
        $this->monitoringRepository = $monitoringRepository;
        $this->metaServiceConfigurationRepository = $metaServiceConfigurationRepository;
    }

    /**
     * Execute the use case for which this class was designed.
     *
     * @return DetailMeta\DetailMetaServiceMonitoringResourceResponse
     * @throws \Centreon\Domain\Monitoring\MonitoringResource\Exception\MonitoringResourceException
     */
    public function execute(ResourceFilter $filter): DetailMeta\DetailMetaServiceMonitoringResourceResponse
    {
        $response = new DetailMeta\DetailMetaServiceMonitoringResourceResponse();
        $monitoringResource = ($this->contact->isAdmin())
            ? $this->monitoringResourceService->findAllWithoutAcl($filter)
            : $this->monitoringResourceService->findAllWithAcl($filter, $this->contact);

        // getting downtimes information
        $metaServiceMonitoringResource = $monitoringResource[0];
        $downtimes = $this->monitoringRepository->findDowntimes(
            $metaServiceMonitoringResource->getHostId(),
            $metaServiceMonitoringResource->getServiceId()
        );
        $metaServiceMonitoringResource->setDowntimes($downtimes);

        // getting acknowledgements information
        if ($metaServiceMonitoringResource->getAcknowledged()) {
            $acknowledgements = $this->monitoringRepository->findAcknowledgements(
                $metaServiceMonitoringResource->getHostId(),
                $metaServiceMonitoringResource->getServiceId()
            );
            if (!empty($acknowledgements)) {
                $metaServiceMonitoringResource->setAcknowledgement($acknowledgements[0]);
            }
        }

        // get the meta service calculation type
        $metaConfiguration = $this->contact->isAdmin()
            ? $this->metaServiceConfigurationRepository->findById($metaServiceMonitoringResource->getId())
            : $this->metaServiceConfigurationRepository->findByIdAndContact(
                $metaServiceMonitoringResource->getId(),
                $this->contact
            );

        if (!is_null($metaConfiguration)) {
            $metaServiceMonitoringResource->setCalculationType($metaConfiguration->getCalculationType());
        }

        $response->setMetaServiceMonitoringResourceDetail($metaServiceMonitoringResource);

        return $response;
    }
}
