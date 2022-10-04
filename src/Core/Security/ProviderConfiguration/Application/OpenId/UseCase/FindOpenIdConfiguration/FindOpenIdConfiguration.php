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

namespace Core\Security\ProviderConfiguration\Application\OpenId\UseCase\FindOpenIdConfiguration;

use Centreon\Domain\Log\LoggerTrait;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Security\Authentication\Application\Provider\ProviderAuthenticationFactoryInterface;
use Core\Security\ProviderConfiguration\Domain\Model\Configuration;
use Core\Security\ProviderConfiguration\Domain\Model\Provider;
use Core\Security\ProviderConfiguration\Domain\OpenId\Model\CustomConfiguration;

class FindOpenIdConfiguration
{
    use LoggerTrait;

    /**
     * @param ProviderAuthenticationFactoryInterface $providerFactory
     */
    public function __construct(private ProviderAuthenticationFactoryInterface $providerFactory)
    {
    }

    /**
     * @param FindOpenIdConfigurationPresenterInterface $presenter
     */
    public function __invoke(FindOpenIdConfigurationPresenterInterface $presenter): void
    {
        try {
            $provider = $this->providerFactory->create(Provider::OPENID);
            $configuration = $provider->getConfiguration();
        } catch (\Throwable $ex) {
            $this->error($ex->getMessage(), ['trace' => $ex->getTraceAsString()]);
            $presenter->setResponseStatus(new ErrorResponse($ex->getMessage()));
            return;
        }

        $presenter->present($this->createResponse($configuration));
    }

    /**
     * @param Configuration $provider
     * @return FindOpenIdConfigurationResponse
     */
    private function createResponse(Configuration $provider): FindOpenIdConfigurationResponse
    {
        /** @var CustomConfiguration $customConfiguration */
        $customConfiguration = $provider->getCustomConfiguration();
        $findOpenIdConfigurationResponse = new FindOpenIdConfigurationResponse();
        $findOpenIdConfigurationResponse->isActive = $provider->isActive();
        $findOpenIdConfigurationResponse->isForced = $provider->isForced();
        $findOpenIdConfigurationResponse->baseUrl = $customConfiguration->getBaseUrl();
        $findOpenIdConfigurationResponse->authorizationEndpoint = $customConfiguration->getAuthorizationEndpoint();
        $findOpenIdConfigurationResponse->tokenEndpoint = $customConfiguration->getTokenEndpoint();
        $findOpenIdConfigurationResponse->introspectionTokenEndpoint =
            $customConfiguration->getIntrospectionTokenEndpoint();
        $findOpenIdConfigurationResponse->userInformationEndpoint = $customConfiguration->getUserInformationEndpoint();
        $findOpenIdConfigurationResponse->endSessionEndpoint = $customConfiguration->getEndSessionEndpoint();
        $findOpenIdConfigurationResponse->connectionScopes = $customConfiguration->getConnectionScopes();
        $findOpenIdConfigurationResponse->loginClaim = $customConfiguration->getLoginClaim();
        $findOpenIdConfigurationResponse->clientId = $customConfiguration->getClientId();
        $findOpenIdConfigurationResponse->clientSecret = $customConfiguration->getClientSecret();
        $findOpenIdConfigurationResponse->authenticationType = $customConfiguration->getAuthenticationType();
        $findOpenIdConfigurationResponse->verifyPeer = $customConfiguration->verifyPeer();
        $findOpenIdConfigurationResponse->isAutoImportEnabled = $customConfiguration->isAutoImportEnabled();
        $findOpenIdConfigurationResponse->contactTemplate = $customConfiguration->getContactTemplate() === null
            ? null
            : $findOpenIdConfigurationResponse::contactTemplateToArray($customConfiguration->getContactTemplate());
        $findOpenIdConfigurationResponse->emailBindAttribute = $customConfiguration->getEmailBindAttribute();
        $findOpenIdConfigurationResponse->userNameBindAttribute = $customConfiguration->getUserNameBindAttribute();
        $findOpenIdConfigurationResponse->aclConditions = FindOpenIdConfigurationResponse::aclConditionsToArray(
            $customConfiguration->getACLConditions()
        );
        $findOpenIdConfigurationResponse->authenticationConditions =
            $findOpenIdConfigurationResponse::authenticationConditionsToArray(
                $customConfiguration->getAuthenticationConditions()
            );
        $findOpenIdConfigurationResponse->groupsMapping = $findOpenIdConfigurationResponse::groupsMappingToArray(
            $customConfiguration->getGroupsMapping()
        );

        return $findOpenIdConfigurationResponse;
    }
}
