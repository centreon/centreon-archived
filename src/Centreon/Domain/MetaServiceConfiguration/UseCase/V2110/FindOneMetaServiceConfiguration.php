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

namespace Centreon\Domain\MetaServiceConfiguration\UseCase\V2110;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\MetaServiceConfiguration\Exception\MetaServiceConfigurationException;
use Centreon\Domain\MetaServiceConfiguration\Interfaces\MetaServiceConfigurationServiceInterface;
use Centreon\Domain\MetaServiceConfiguration\UseCase\V2110\FindOneMetaServiceConfigurationResponse;

/**
 * This class is designed to represent a use case to find all host categories.
 *
 * @package Centreon\Domain\MetaServiceConfiguration\UseCase\V2110
 */
class FindOneMetaServiceConfiguration
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
     * @param int $metaId
     * @return FindOneMetaServiceConfigurationResponse
     * @throws MetaServiceConfigurationException
     */
    public function execute(int $metaId): FindOneMetaServiceConfigurationResponse
    {
        $response = new FindOneMetaServiceConfigurationResponse();
        $metaServiceConfiguration = ($this->contact->isAdmin())
            ? $this->metaServiceConfigurationService->findWithoutAcl($metaId)
            : $this->metaServiceConfigurationService->findWithAcl($metaId);

        if (is_null($metaServiceConfiguration)) {
            throw MetaServiceConfigurationException::findOneMetaServiceConfigurationNotFound($metaId);
        }

        $response->setMetaServiceConfiguration($metaServiceConfiguration);
        return $response;
    }
}
