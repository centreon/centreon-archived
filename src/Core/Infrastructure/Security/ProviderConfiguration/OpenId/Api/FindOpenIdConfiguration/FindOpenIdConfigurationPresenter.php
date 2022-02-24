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

namespace Core\Infrastructure\Security\ProviderConfiguration\OpenId\Api\FindOpenIdConfiguration;

use Core\Application\Common\UseCase\AbstractPresenter;
use Core\Application\Security\ProviderConfiguration\OpenId\UseCase\FindOpenIdConfiguration\{
    FindOpenIdConfigurationPresenterInterface,
    FindOpenIdConfigurationResponse
};

class FindOpenIdConfigurationPresenter extends AbstractPresenter implements FindOpenIdConfigurationPresenterInterface
{
    /**
     * {@inheritDoc}
     * @param FindOpenIdConfigurationResponse $response
     */
    public function present(mixed $response): void
    {
        $presenterResponse = [
            'is_active' => $response->isActive,
            'is_forced' => $response->isForced,
            'trusted_client_addresses' => $response->trustedClientAddresses,
            'blacklist_client_addresses' => $response->blacklistClientAddresses,
            'base_url' => $response->baseUrl,
            'authorization_endpoint' => $response->authorizationEndpoint,
            'token_endpoint' => $response->tokenEndpoint,
            'introspection_token_endpoint' => $response->introspectionTokenEndpoint,
            'userinfo_endpoint' => $response->userInformationsEndpoint,
            'endsession_endpoint' => $response->endSessionEndpoint,
            'connection_scopes' => $response->connectionScopes,
            'login_claim' => $response->loginClaim,
            'client_id' => $response->clientId,
            'client_secret' => $response->clientSecret,
            'authentication_type' => $response->authenticationType,
            'verify_peer' => $response->verifyPeer
        ];

        $this->presenterFormatter->present($presenterResponse);
    }
}
