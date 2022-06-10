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

namespace Core\Security\Domain\ProviderConfiguration\OpenId\Model;

use Core\Security\Application\ProviderConfiguration\OpenId\UseCase\UpdateOpenIdConfiguration\{
    UpdateOpenIdConfigurationRequest
};
use Core\Contact\Domain\Model\ContactTemplate;
use Core\Security\Domain\ProviderConfiguration\OpenId\Exceptions\OpenIdConfigurationException;

class OpenIdConfigurationFactory
{
    /**
     * @param UpdateOpenIdConfigurationRequest $request
     * @param ContactTemplate|null $contactTemplate
     * @return Configuration
     * @throws OpenIdConfigurationException
     */
    public static function createFromRequest(
        UpdateOpenIdConfigurationRequest $request,
        ?ContactTemplate $contactTemplate = null
    ): Configuration {
        $configuration = new Configuration(
            $request->isActive,
            $request->isAutoImportEnabled,
            $request->clientId,
            $request->clientSecret,
            $request->baseUrl,
            $request->authorizationEndpoint,
            $request->tokenEndpoint,
            $request->introspectionTokenEndpoint,
            $request->userInformationEndpoint,
            $contactTemplate,
            $request->emailBindAttribute,
            $request->userAliasBindAttribute,
            $request->userNameBindAttribute
        );

        $configuration
            ->setForced($request->isForced)
            ->setTrustedClientAddresses($request->trustedClientAddresses)
            ->setBlacklistClientAddresses($request->blacklistClientAddresses)
            ->setEndSessionEndpoint($request->endSessionEndpoint)
            ->setConnectionScopes($request->connectionScopes)
            ->setLoginClaim($request->loginClaim)
            ->setAuthenticationType($request->authenticationType)
            ->setVerifyPeer($request->verifyPeer);

        return $configuration;
    }
}
