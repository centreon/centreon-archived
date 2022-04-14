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
     * @throws OpenIdConfigurationException
     */
    public static function createFromRequest(UpdateOpenIdConfigurationRequest $request): OpenIdConfiguration
    {
        if ($request->isAutoImportEnabled === true) {
            $missingParameters = [];
            if ($request->contactTemplate === null) {
                $missingParameters[] = 'contact_template';
            }
            if ($request->emailBindAttribute === null) {
                $missingParameters[] = 'email_bind_attribute';
            }
            if ($request->userAliasBindAttribute === null) {
                $missingParameters[] = 'alias_bind_attribute';
            }
            if ($request->userNameBindAttribute === null) {
                $missingParameters[] = 'fullname_bind_attribute';
            }
            if (!empty($missingParameters)) {
                $parameters = implode(", ", $missingParameters);
                throw OpenIdConfigurationException::missingMandatoryParametersForAutoImport($parameters);
            }
        }
        $contactTemplate =  null;
        if($request->contactTemplate !== null) {
            $contactTemplate = new ContactTemplate($request->contactTemplate['id'], $request->contactTemplate['name']);
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
            $request->verifyPeer,
            $contactTemplate,
            $request->isAutoImportEnabled,
            $request->emailBindAttribute,
            $request->userAliasBindAttribute,
            $request->userNameBindAttribute
        );
    }
}
