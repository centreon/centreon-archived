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

namespace Centreon\Domain\MetaServiceConfiguration\UseCase\V21;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\MetaServiceConfiguration\Interfaces\MetaServiceConfigurationServiceInterface;
use Centreon\Domain\MetaServiceConfiguration\UseCase\V21\FindMetaServicesConfigurationsResponse;
use Centreon\Domain\MetaServiceConfiguration\Exception\MetaServiceConfigurationException;

/**
 * This class is designed to represent a use case to find all host categories.
 *
 * @package Centreon\Domain\MetaServiceConfiguration\UseCase\V21
 */
class FindMetaServicesConfigurations
{
    /**
     * @var MetaServiceConfigurationServiceInterface
     */
    private $metaServiceConfigurationService;
    /**
     * @var ContactInterface
     */
    private $contact;

    /**
     * FindMetaServiceConfiguration constructor.
     *
     * @param MetaServiceConfigurationServiceInterface $metaServiceConfigurationService
     * @param ContactInterface $contact
     */
    public function __construct(
        MetaServiceConfigurationServiceInterface $metaServiceConfigurationService,
        ContactInterface $contact
    ) {
        $this->metaServiceConfigurationService = $metaServiceConfigurationService;
        $this->contact = $contact;
    }

    /**
     * Execute the use case for which this class was designed.
     *
     * @return FindMetaServicesConfigurationsResponse
     * @throws MetaServiceConfigurationException
     */
    public function execute(): FindMetaServicesConfigurationsResponse
    {
        $response = new FindMetaServicesConfigurationsResponse();
        $metaServicesConfigurations = ($this->contact->isAdmin())
            ? $this->metaServiceConfigurationService->findAllWithoutAcl()
            : $this->metaServiceConfigurationService->findAllWithAcl();
        $response->setMetaServicesConfigurations($metaServicesConfigurations);
        return $response;
    }
}
