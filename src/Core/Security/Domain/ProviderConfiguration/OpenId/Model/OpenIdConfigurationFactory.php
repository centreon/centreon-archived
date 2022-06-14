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
use Core\Contact\Domain\Model\ContactGroup;
use Core\Security\Domain\ProviderConfiguration\OpenId\Model\AuthorizationRule;
use Core\Security\Domain\ProviderConfiguration\OpenId\Exceptions\OpenIdConfigurationException;

class OpenIdConfigurationFactory
{
    /**
     * @param UpdateOpenIdConfigurationRequest $request
     * @param ContactTemplate|null $contactTemplate
     * @param ContactGroup|null $contactGroup
     * @param AuthorizationRule[] $authorizationRules
     * @return AbstractConfiguration
     * @throws OpenIdConfigurationException
     */
    public static function create(
        UpdateOpenIdConfigurationRequest $request,
        ?ContactTemplate $contactTemplate = null,
        ?ContactGroup $contactGroup = null,
        array $authorizationRules = []
    ): AbstractConfiguration {
        if ($request->isActive === true) {
            $configuration = self::createActiveConfiguration(
                $request,
                $contactTemplate,
                $contactGroup,
                $authorizationRules
            );
        } else {
            $configuration = self::createNonActiveConfiguration(
                $request,
                $contactTemplate,
                $contactGroup,
                $authorizationRules
            );
        }

        return $configuration;
    }

    /**
     * Create Active Configuration
     *
     * @param UpdateOpenIdConfigurationRequest $request
     * @param ContactTemplate|null $contactTemplate
     * @param ContactGroup|null $contactGroup
     * @param AuthorizationRule[] $authorizationRules
     * @return ActiveConfiguration
     * @throws OpenIdConfigurationException
     */
    private static function createActiveConfiguration(
        UpdateOpenIdConfigurationRequest $request,
        ?ContactTemplate $contactTemplate,
        ?ContactGroup $contactGroup,
        array $authorizationRules
    ): ActiveConfiguration {
        $configuration = new ActiveConfiguration(
            $request->isAutoImportEnabled,
            $request->clientId,
            $request->clientSecret,
            $request->baseUrl,
            $request->authorizationEndpoint,
            $request->tokenEndpoint,
            $request->introspectionTokenEndpoint,
            $request->userInformationEndpoint,
            $contactGroup,
            $contactTemplate,
            $request->emailBindAttribute,
            $request->userAliasBindAttribute,
            $request->userNameBindAttribute,
        );
        if ($request->claimName !== null) {
            $configuration->setClaimName($request->claimName);
        }
        $configuration
            ->setForced($request->isForced)
            ->setTrustedClientAddresses($request->trustedClientAddresses)
            ->setBlacklistClientAddresses($request->blacklistClientAddresses)
            ->setEndSessionEndpoint($request->endSessionEndpoint)
            ->setConnectionScopes($request->connectionScopes)
            ->setLoginClaim($request->loginClaim)
            ->setAuthenticationType($request->authenticationType)
            ->setVerifyPeer($request->verifyPeer)
            ->setAuthorizationRules($authorizationRules);

        return $configuration;
    }

    /**
     * Create Non Active Configuration
     *
     * @param UpdateOpenIdConfigurationRequest $request
     * @param ContactTemplate|null $contactTemplate
     * @param ContactGroup|null $contactGroup
     * @param AuthorizationRule[] $authorizationRules
     * @return NonActiveConfiguration
     */
    private static function createNonActiveConfiguration(
        UpdateOpenIdConfigurationRequest $request,
        ?ContactTemplate $contactTemplate,
        ?ContactGroup $contactGroup,
        array $authorizationRules
    ): NonActiveConfiguration {
        return (new NonActiveConfiguration())
            ->setAutoImportEnabled($request->isAutoImportEnabled)
            ->setClientId($request->clientId)
            ->setClientSecret($request->clientSecret)
            ->setBaseUrl($request->baseUrl)
            ->setAuthorizationEndpoint($request->authorizationEndpoint)
            ->setTokenEndpoint($request->tokenEndpoint)
            ->setIntrospectionTokenEndpoint($request->introspectionTokenEndpoint)
            ->setUserInformationEndpoint($request->userInformationEndpoint)
            ->setContactGroup($contactGroup)
            ->setContactTemplate($contactTemplate)
            ->setEmailBindAttribute($request->emailBindAttribute)
            ->setUserAliasBindAttribute($request->userAliasBindAttribute)
            ->setUserNameBindAttribute($request->userNameBindAttribute)
            ->setClaimName($request->claimName)
            ->setForced($request->isForced)
            ->setTrustedClientAddresses($request->trustedClientAddresses)
            ->setBlacklistClientAddresses($request->blacklistClientAddresses)
            ->setEndSessionEndpoint($request->endSessionEndpoint)
            ->setConnectionScopes($request->connectionScopes)
            ->setLoginClaim($request->loginClaim)
            ->setAuthenticationType($request->authenticationType)
            ->setVerifyPeer($request->verifyPeer)
            ->setAuthorizationRules($authorizationRules);
    }
}
