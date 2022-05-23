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
use Core\Contact\Domain\Model\ContactTemplate;
use Core\Domain\Security\ProviderConfiguration\OpenId\Exceptions\OpenIdConfigurationException;

class OpenIdConfigurationFactory
{
    /**
     * @param UpdateOpenIdConfigurationRequest $request
     * @return OpenIdConfiguration
     */
    public static function createFromRequest(UpdateOpenIdConfigurationRequest $request): OpenIdConfiguration
    {
        if ($request->userInformationEndpoint === null && $request->introspectionTokenEndpoint === null) {
            throw OpenIdConfigurationException::missingInformationEndpoint();
        }

        $contactTemplate = $request->contactTemplate !== null
            ? new ContactTemplate($request->contactTemplate['id'], $request->contactTemplate['name'])
            : null;

        $configuration = new OpenIdConfiguration(
            $request->isAutoImportEnabled,
            $contactTemplate,
            $request->emailBindAttribute,
            $request->userAliasBindAttribute,
            $request->userNameBindAttribute
        );

        $configuration
            ->setActive($request->isActive)
            ->setForced($request->isForced)
            ->setTrustedClientAddresses($request->trustedClientAddresses)
            ->setBlacklistClientAddresses($request->blacklistClientAddresses)
            ->setBaseUrl($request->baseUrl)
            ->setAuthorizationEndpoint($request->authorizationEndpoint)
            ->setTokenEndpoint($request->tokenEndpoint)
            ->setIntrospectionTokenEndpoint($request->introspectionTokenEndpoint)
            ->setUserInformationEndpoint($request->userInformationEndpoint)
            ->setEndSessionEndpoint($request->endSessionEndpoint)
            ->setConnectionScopes($request->connectionScopes)
            ->setLoginClaim($request->loginClaim)
            ->setClientId($request->clientId)
            ->setClientSecret($request->clientSecret)
            ->setAuthenticationType($request->authenticationType)
            ->setVerifyPeer($request->verifyPeer);

        return $configuration;
    }
}
