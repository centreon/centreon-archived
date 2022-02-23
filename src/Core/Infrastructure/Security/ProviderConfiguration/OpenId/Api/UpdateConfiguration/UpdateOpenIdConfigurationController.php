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

namespace Core\Infrastructure\Security\ProviderConfiguration\OpenId\Api\UpdateConfiguration;

use Symfony\Component\HttpFoundation\Request;
use Centreon\Application\Controller\AbstractController;
use Core\Application\Security\ProviderConfiguration\OpenId\UseCase\UpdateOpenIdConfiguration\{
    UpdateOpenIdConfiguration,
    UpdateOpenIdConfigurationPresenterInterface,
    UpdateOpenIdConfigurationRequest
};

class UpdateOpenIdConfigurationController extends AbstractController
{
    /**
     * @param UpdateOpenIdConfiguration $useCase
     * @param Request $request
     * @param UpdateOpenIdConfigurationPresenterInterface $presenter
     * @return object
     */
    public function __invoke(
        UpdateOpenIdConfiguration $useCase,
        Request $request,
        UpdateOpenIdConfigurationPresenterInterface $presenter
    ): object {
        $this->denyAccessUnlessGrantedForApiConfiguration();
        $this->validateDataSent($request, __DIR__ . '/UpdateOpenIdConfigurationSchema.json');
        $updateOpenIdConfigurationRequest = $this->createUpdateOpenIdConfigurationRequest($request);
        $useCase($presenter, $updateOpenIdConfigurationRequest);

        return $presenter->show();
    }

    /**
     * @param Request $request
     * @return UpdateOpenIdConfigurationRequest
     */
    private function createUpdateOpenIdConfigurationRequest(Request $request): UpdateOpenIdConfigurationRequest
    {
        $requestData  = json_decode((string) $request->getContent(), true);
        $updateOpenIdConfigurationRequest = new UpdateOpenIdConfigurationRequest();
        $updateOpenIdConfigurationRequest->isActive = $requestData['is_active'];
        $updateOpenIdConfigurationRequest->isForced = $requestData['is_forced'];
        $updateOpenIdConfigurationRequest->trustedClientAddresses = $requestData['trusted_client_addresses'] ?? [];
        $updateOpenIdConfigurationRequest->blacklistClientAddresses = $requestData['blacklist_client_addresses'] ?? [];
        $updateOpenIdConfigurationRequest->baseUrl = $requestData['base_url'];
        $updateOpenIdConfigurationRequest->authorizationEndpoint = $requestData['authorization_endpoint'];
        $updateOpenIdConfigurationRequest->tokenEndpoint = $requestData['token_endpoint'];
        $updateOpenIdConfigurationRequest->introspectionTokenEndpoint = $requestData['introspection_token_endpoint'];
        $updateOpenIdConfigurationRequest->userInformationsEndpoint = $requestData['userinfo_endpoint'];
        $updateOpenIdConfigurationRequest->endSessionEndpoint = $requestData['endsession_endpoint'];
        $updateOpenIdConfigurationRequest->connectionScope = $requestData['connection_scope'] ?? [];
        $updateOpenIdConfigurationRequest->loginClaim = $requestData['login_claim'];
        $updateOpenIdConfigurationRequest->clientId = $requestData['client_id'];
        $updateOpenIdConfigurationRequest->clientSecret = $requestData['client_secret'];
        $updateOpenIdConfigurationRequest->authenticationType = $requestData['authentication_type'];
        $updateOpenIdConfigurationRequest->verifyPeer = $requestData['verify_peer'];

        return $updateOpenIdConfigurationRequest;
    }
}
