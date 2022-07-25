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

namespace Core\Security\Application\ProviderConfiguration\OpenId\UseCase\FindOpenIdConfiguration;

use Core\Application\Common\UseCase\ErrorResponse;
use Core\Application\Common\UseCase\NotFoundResponse;
use Core\Security\Domain\ProviderConfiguration\OpenId\Model\Configuration;
use Core\Security\Application\ProviderConfiguration\OpenId\Repository\ReadOpenIdConfigurationRepositoryInterface;

class FindOpenIdConfiguration
{
    /**
     * @param ReadOpenIdConfigurationRepositoryInterface $repository
     */
    public function __construct(private readonly ReadOpenIdConfigurationRepositoryInterface $repository)
    {
    }

    /**
     * @param FindOpenIdConfigurationPresenterInterface $presenter
     */
    public function __invoke(FindOpenIdConfigurationPresenterInterface $presenter): void
    {
        try {
            $configuration = $this->repository->findConfiguration();
        } catch (\Throwable $ex) {
            $presenter->setResponseStatus(new ErrorResponse($ex->getMessage()));
            return;
        }

        if ($configuration === null) {
            $presenter->setResponseStatus(new NotFoundResponse('OpenIdConfiguration'));
            return;
        }

        $presenter->present($this->createResponse($configuration));
    }

    private function createResponse(Configuration $configuration): FindOpenIdConfigurationResponse
    {
        $findOpenIdConfigurationResponse = new FindOpenIdConfigurationResponse();
        $findOpenIdConfigurationResponse->isActive = $configuration->isActive();
        $findOpenIdConfigurationResponse->isForced = $configuration->isForced();
        $findOpenIdConfigurationResponse->trustedClientAddresses = $configuration->getTrustedClientAddresses();
        $findOpenIdConfigurationResponse->blacklistClientAddresses = $configuration->getBlacklistClientAddresses();
        $findOpenIdConfigurationResponse->baseUrl = $configuration->getBaseUrl();
        $findOpenIdConfigurationResponse->authorizationEndpoint = $configuration->getAuthorizationEndpoint();
        $findOpenIdConfigurationResponse->tokenEndpoint = $configuration->getTokenEndpoint();
        $findOpenIdConfigurationResponse->introspectionTokenEndpoint = $configuration->getIntrospectionTokenEndpoint();
        $findOpenIdConfigurationResponse->userInformationEndpoint = $configuration->getUserInformationEndpoint();
        $findOpenIdConfigurationResponse->endSessionEndpoint = $configuration->getEndSessionEndpoint();
        $findOpenIdConfigurationResponse->connectionScopes = $configuration->getConnectionScopes();
        $findOpenIdConfigurationResponse->loginClaim = $configuration->getLoginClaim();
        $findOpenIdConfigurationResponse->clientId = $configuration->getClientId();
        $findOpenIdConfigurationResponse->clientSecret = $configuration->getClientSecret();
        $findOpenIdConfigurationResponse->authenticationType = $configuration->getAuthenticationType();
        $findOpenIdConfigurationResponse->verifyPeer = $configuration->verifyPeer();
        $findOpenIdConfigurationResponse->isAutoImportEnabled = $configuration->isAutoImportEnabled();
        $findOpenIdConfigurationResponse->contactTemplate = $configuration->getContactTemplate() === null
            ? null
            : $findOpenIdConfigurationResponse::contactTemplateToArray($configuration->getContactTemplate());
        $findOpenIdConfigurationResponse->emailBindAttribute = $configuration->getEmailBindAttribute();
        $findOpenIdConfigurationResponse->userNameBindAttribute = $configuration->getUserNameBindAttribute();
        $findOpenIdConfigurationResponse->claimName = $configuration->getClaimName();
        $findOpenIdConfigurationResponse->contactGroup = $configuration->getContactGroup() === null
            ? null
            : $findOpenIdConfigurationResponse::contactGroupToArray($configuration->getContactGroup());
        $findOpenIdConfigurationResponse->authorizationRules = empty($configuration->getAuthorizationRules())
            ? []
            : $findOpenIdConfigurationResponse::authorizationRulesToArray($configuration->getAuthorizationRules());

        return $findOpenIdConfigurationResponse;
    }
}
