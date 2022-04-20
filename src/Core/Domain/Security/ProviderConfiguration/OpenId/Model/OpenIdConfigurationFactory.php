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

namespace Core\Domain\Security\ProviderConfiguration\OpenId\Model;

use Core\Application\Security\ProviderConfiguration\OpenId\UseCase\UpdateOpenIdConfiguration\{
    UpdateOpenIdConfigurationRequest
};
use Core\Domain\Security\ProviderConfiguration\OpenId\Exceptions\OpenIdConfigurationException;

class OpenIdConfigurationFactory
{
    /**
     * @param UpdateOpenIdConfigurationRequest $request
     * @return OpenIdConfiguration
     */
    public static function createFromRequest(UpdateOpenIdConfigurationRequest $request): OpenIdConfiguration
    {
        if ($request->userInformationEndpoint === null && $request->introspectionTokenEndpoint === null)
        {
            throw OpenIdConfigurationException::missingInformationEndpoint();
        }

        return new OpenIdConfiguration(
            $request->isActive,
            $request->isForced,
            $request->trustedClientAddresses,
            $request->blacklistClientAddresses,
            $request->baseUrl,
            $request->authorizationEndpoint,
            $request->tokenEndpoint,
            $request->introspectionTokenEndpoint,
            $request->userInformationEndpoint,
            $request->endSessionEndpoint,
            $request->connectionScopes,
            $request->loginClaim,
            $request->clientId,
            $request->clientSecret,
            $request->authenticationType,
            $request->verifyPeer
        );
    }
}
