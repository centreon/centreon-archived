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

namespace Centreon\Application\Controller;

use FOS\RestBundle\View\View;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use Centreon\Domain\MetaServiceConfiguration\Exception\MetaServiceConfigurationException;
use Centreon\Domain\MetaServiceConfiguration\UseCase\V21\FindMetaServicesConfigurations;
use Centreon\Domain\MetaServiceConfiguration\UseCase\V21\FindOneMetaServiceConfiguration;
use Centreon\Infrastructure\MetaServiceConfiguration\API\Model\MetaServiceConfigurationV21Factory;

/**
 * This class is designed to provide APIs for the context of meta service configuration.
 *
 * @package Centreon\Application\Controller
 */
class MetaServiceConfigurationController extends AbstractController
{
    /**
     * @param RequestParametersInterface $requestParameters
     * @param FindOneMetaServiceConfiguration $findMetaServiceConfiguration
     * @param int $metaId
     * @return View
     * @throws MetaServiceConfigurationException
     */
    public function findOneMetaServiceConfiguration(
        RequestParametersInterface $requestParameters,
        FindOneMetaServiceConfiguration $findMetaServiceConfiguration,
        int $metaId
    ): View {
        $this->denyAccessUnlessGrantedForApiConfiguration();
        $response = $findMetaServiceConfiguration->execute($metaId);
        return $this->view(
            [
                'result' => MetaServiceConfigurationV21Factory::createOneFromResponse($response),
                'meta' => $requestParameters->toArray()
            ]
        );
    }

    /**
     * @param RequestParametersInterface $requestParameters
     * @param FindMetaServicesConfigurations $findMetasServicesConfigurations
     * @return View
     * @throws MetaServiceConfigurationException
     */
    public function findMetaServicesConfigurations(
        RequestParametersInterface $requestParameters,
        FindMetaServicesConfigurations $findMetasServicesConfigurations
    ): View {
        $this->denyAccessUnlessGrantedForApiConfiguration();
        $response = $findMetasServicesConfigurations->execute();
        return $this->view(
            [
                'result' => MetaServiceConfigurationV21Factory::createAllFromResponse($response),
                'meta' => $requestParameters->toArray()
            ]
        );
    }
}
