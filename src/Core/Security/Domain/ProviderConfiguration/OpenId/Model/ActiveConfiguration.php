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

use Core\Contact\Domain\Model\ContactGroup;
use Core\Contact\Domain\Model\ContactTemplate;
use Centreon\Domain\Common\Assertion\Assertion;
use Core\Security\Domain\ProviderConfiguration\OpenId\Exceptions\OpenIdConfigurationException;

/**
 * This class represent an active OpenId Configuration
 *
 * active configuration should have mandatory parameters to be able to authenticate using this configuration.
 */
class ActiveConfiguration extends AbstractConfiguration
{
    protected bool $isActive = true;

    /**
     * @param boolean $isAutoImportEnabled
     * @param string|null $clientId
     * @param string|null $clientSecret
     * @param string|null $baseUrl
     * @param string|null $authorizationEndpoint
     * @param string|null $tokenEndpoint
     * @param string|null $introspectionTokenEndpoint
     * @param string|null $userInformationEndpoint
     * @param ContactGroup|null $contactGroup
     * @param ContactTemplate|null $contactTemplate
     * @param string|null $emailBindAttribute
     * @param string|null $userAliasBindAttribute
     * @param string|null $userNameBindAttribute
     * @throws OpenIdConfigurationException
     */
    public function __construct(
        protected bool $isAutoImportEnabled,
        protected ?string $clientId,
        protected ?string $clientSecret,
        protected ?string $baseUrl,
        protected ?string $authorizationEndpoint,
        protected ?string $tokenEndpoint,
        protected ?string $introspectionTokenEndpoint,
        protected ?string $userInformationEndpoint,
        protected ?ContactGroup $contactGroup,
        protected ?ContactTemplate $contactTemplate = null,
        protected ?string $emailBindAttribute = null,
        protected ?string $userAliasBindAttribute = null,
        protected ?string $userNameBindAttribute = null,
    ) {
        $this->validateMandatoryParameters();
    }

    /**
     * Validate Mandatory Parameters
     *
     * @throws OpenIdConfigurationException
     */
    private function validateMandatoryParameters(): void
    {
        Assertion::notEmpty($this->clientId, "Configuration::clientId");
        Assertion::notEmpty($this->clientSecret, "Configuration::clientSecret");
        Assertion::notEmpty($this->baseUrl, "Configuration::baseUrl");
        Assertion::notEmpty($this->authorizationEndpoint, "Configuration::authorizationEndpoint");
        Assertion::notEmpty($this->tokenEndpoint, "Configuration::tokenEndpoint");
        Assertion::notNull($this->contactGroup, "Configuration::contactGroup");
        if (empty($this->introspectionTokenEndpoint) && empty($this->userInformationEndpoint)) {
            throw OpenIdConfigurationException::missingInformationEndpoint();
        }
        $this->validateParametersForAutoImport();
    }

    /**
     * Validate that parameters for auto import are filled
     *
     * @throws OpenIdConfigurationException
     */
    private function validateParametersForAutoImport(): void
    {
        if ($this->isAutoImportEnabled === true) {
            $missingMandatoryParameters = [];
            if ($this->contactTemplate === null) {
                $missingMandatoryParameters[] = 'contact_template';
            }
            if (empty($this->emailBindAttribute)) {
                $missingMandatoryParameters[] = 'email_bind_attribute';
            }
            if (empty($this->userAliasBindAttribute)) {
                $missingMandatoryParameters[] = 'alias_bind_attribute';
            }
            if (empty($this->userNameBindAttribute)) {
                $missingMandatoryParameters[] = 'fullname_bind_attribute';
            }
            if (! empty($missingMandatoryParameters)) {
                throw OpenIdConfigurationException::missingAutoImportMandatoryParameters(
                    $missingMandatoryParameters
                );
            }
        }
    }
}
