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

namespace Core\Security\Application\ProviderConfiguration\OpenId\Builder;

use Core\Security\Application\ProviderConfiguration\OpenId\UseCase\{
    UpdateOpenIdConfiguration\UpdateOpenIdConfigurationRequest
};
use Core\Contact\Domain\Model\ContactGroup;
use Centreon\Domain\Common\Assertion\Assertion;
use Centreon\Domain\Common\Assertion\AssertionException;
use Core\Contact\Domain\Model\ContactTemplate;
use Core\Security\Domain\ProviderConfiguration\OpenId\Exceptions\OpenIdConfigurationException;
use Core\Security\Domain\ProviderConfiguration\OpenId\Model\{
    Configuration,
    AuthorizationRule
};

class ConfigurationBuilder
{
    /**
     * Create OpenId Configuration from Request DTO
     *
     * @param UpdateOpenIdConfigurationRequest $request
     * @param ContactTemplate|null $contactTemplate
     * @param ContactGroup|null $contactGroup
     * @param AuthorizationRule[] $authorizationRules
     * @return Configuration
     * @throws OpenIdConfigurationException|AssertionException
     */
    public static function create(
        UpdateOpenIdConfigurationRequest $request,
        ?ContactTemplate $contactTemplate,
        ?ContactGroup $contactGroup,
        array $authorizationRules
    ): Configuration {
        if ($request->isActive === true) {
            Assertion::notEmpty($request->clientId, "Configuration::clientId");
            Assertion::notEmpty($request->clientSecret, "Configuration::clientSecret");
            Assertion::notEmpty($request->baseUrl, "Configuration::baseUrl");
            Assertion::notEmpty($request->authorizationEndpoint, "Configuration::authorizationEndpoint");
            Assertion::notEmpty($request->tokenEndpoint, "Configuration::tokenEndpoint");
            Assertion::notNull($contactGroup, "Configuration::contactGroup");
            if (empty($request->introspectionTokenEndpoint) && empty($request->userInformationEndpoint)) {
                throw OpenIdConfigurationException::missingInformationEndpoint();
            }
            if ($request->isAutoImportEnabled === true) {
                self::validateParametersForAutoImport(
                    $contactTemplate,
                    $request->emailBindAttribute,
                    $request->userNameBindAttribute
                );
            }
        }

        return (new Configuration())
            ->setClientId($request->clientId)
            ->setForced($request->isForced)
            ->setActive($request->isActive)
            ->setTrustedClientAddresses($request->trustedClientAddresses)
            ->setBlacklistClientAddresses($request->blacklistClientAddresses)
            ->setEndSessionEndpoint($request->endSessionEndpoint)
            ->setConnectionScopes($request->connectionScopes)
            ->setLoginClaim($request->loginClaim)
            ->setAuthenticationType($request->authenticationType)
            ->setVerifyPeer($request->verifyPeer)
            ->setAuthorizationRules($authorizationRules)
            ->setAutoImportEnabled($request->isAutoImportEnabled)
            ->setClientSecret($request->clientSecret)
            ->setBaseUrl($request->baseUrl)
            ->setAuthorizationEndpoint($request->authorizationEndpoint)
            ->setTokenEndpoint($request->tokenEndpoint)
            ->setIntrospectionTokenEndpoint($request->introspectionTokenEndpoint)
            ->setUserInformationEndpoint($request->userInformationEndpoint)
            ->setContactTemplate($contactTemplate)
            ->setEmailBindAttribute($request->emailBindAttribute)
            ->setUserNameBindAttribute($request->userNameBindAttribute)
            ->setContactGroup($contactGroup)
            ->setClaimName($request->claimName);
    }

    /**
     * Validate mandatory parameters for auto import
     *
     * @param ContactTemplate|null $contactTemplate
     * @param string|null $emailBindAttribute
     * @param string|null $userNameBindAttribute
     * @throws OpenIdConfigurationException
     */
    private static function validateParametersForAutoImport(
        ?ContactTemplate $contactTemplate,
        ?string $emailBindAttribute,
        ?string $userNameBindAttribute
    ): void {
        $missingMandatoryParameters = [];
        if ($contactTemplate === null) {
            $missingMandatoryParameters[] = 'contact_template';
        }
        if (empty($emailBindAttribute)) {
            $missingMandatoryParameters[] = 'email_bind_attribute';
        }
        if (empty($userNameBindAttribute)) {
            $missingMandatoryParameters[] = 'fullname_bind_attribute';
        }
        if (! empty($missingMandatoryParameters)) {
            throw OpenIdConfigurationException::missingAutoImportMandatoryParameters(
                $missingMandatoryParameters
            );
        }
    }
}
