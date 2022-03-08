<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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

namespace Core\Application\Security\UseCase\FindProviderConfigurations;

use Core\Application\Common\UseCase\ErrorResponse;
use Core\Application\Common\UseCase\NotFoundResponse;
use Core\Domain\Security\ProviderConfiguration\Local\Model\Configuration as LocalConfiguration;
use Core\Domain\Security\ProviderConfiguration\OpenId\Model\OpenIdConfiguration;
use Core\Application\Security\ProviderConfiguration\Local\Repository\ReadConfigurationRepositoryInterface
    as ReadLocalConfigurationRepositoryInterface;
use Core\Application\Security\ProviderConfiguration\OpenId\Repository\ReadOpenIdConfigurationRepositoryInterface;

class FindProviderConfigurations
{
    /**
     * @param ReadLocalConfigurationRepositoryInterface $readLocalRepository
     * @param ReadOpenIdConfigurationRepositoryInterface $readOpenIdRepository
     */
    public function __construct(
        private ReadLocalConfigurationRepositoryInterface $readLocalRepository,
        private ReadOpenIdConfigurationRepositoryInterface $readOpenIdRepository,
    ) {
    }

    /**
     * @param FindProviderConfigurationsPresenterInterface $presenter
     */
    public function __invoke(FindProviderConfigurationsPresenterInterface $presenter): void
    {
        try {
            $localConfiguration = $this->readLocalRepository->findConfiguration();
            $openIdConfiguration = $this->readOpenIdRepository->findConfiguration();
        } catch (\Throwable $ex) {
            $presenter->setResponseStatus(new ErrorResponse($ex->getMessage()));
            return;
        }

        if ($localConfiguration === null) {
            $presenter->setResponseStatus(new NotFoundResponse('local'));
            return;
        }

        if ($openIdConfiguration !== null && ! $openIdConfiguration->isActive()) {
            $openIdConfiguration = null;
        }

        // @todo
        $presenter->present([
            $this->createLocalResponse($localConfiguration),
            $this->createOpenIdResponse($openIdConfiguration),
        ]);
    }

    /**
     * @param LocalConfiguration $localConfiguration
     * @return FindLocalProviderConfigurationResponse
     */
    private function createLocalResponse(
        LocalConfiguration $localConfiguration,
    ): FindLocalProviderConfigurationResponse {
        $findProviderConfigurationsResponse = new FindLocalProviderConfigurationResponse();
        $findProviderConfigurationsResponse->id = 1;
        $findProviderConfigurationsResponse->type = 'local';
        $findProviderConfigurationsResponse->name = 'local';
        $findProviderConfigurationsResponse->isActive = true;
        $findProviderConfigurationsResponse->isForced = false;
        $findProviderConfigurationsResponse->authenticationUri = '/authentication/providers/configurations/local';

        return $findProviderConfigurationsResponse;
    }

    /**
     * @param OpenIdConfiguration|null $openIdConfiguration
     * @return FindopenIdProviderConfigurationResponse
     */
    private function createOpenIdResponse(
        ?OpenIdConfiguration $openIdConfiguration,
    ): FindopenIdProviderConfigurationResponse {
        $findProviderConfigurationsResponse = new FindOpenIdProviderConfigurationResponse();
        $findProviderConfigurationsResponse->isActive = $openIdConfiguration->isActive();
        $findProviderConfigurationsResponse->isForced = $openIdConfiguration->isForced();
        $findProviderConfigurationsResponse->baseUrl = $openIdConfiguration->getBaseUrl();
        $findProviderConfigurationsResponse->authorizationEndpoint =
            $openIdConfiguration->getAuthorizationEndpoint();
        $findProviderConfigurationsResponse->clientId = $openIdConfiguration->getClientId();
        $findProviderConfigurationsResponse->id = $openIdConfiguration->getId();

        return $findProviderConfigurationsResponse;
    }
}
