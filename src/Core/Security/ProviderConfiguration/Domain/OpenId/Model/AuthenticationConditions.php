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

namespace Core\Security\ProviderConfiguration\Domain\OpenId\Model;

use Centreon\Domain\Common\Assertion\AssertionException;
use Core\Security\ProviderConfiguration\Domain\OpenId\Exceptions\OpenIdConfigurationException;

/**
 * This class is designed to represent the Authentication Conditions to be able to connect with OpenID Provider
 * Conditions are gathered from the Response (attribute path) of an endpoint defined by the user.
 * e.g : "http://myprovider.com/my_authorizations" will return a response:
 *
 * {
 *   "infos": {
 *      "groups": [
 *          "groupA",
 *          "groupB"
 *      ]
 *   }
 * }
 *
 * If we want to allow access for user with groupA, we should set attributePath to "infos.groups", and authorizedValues
 * with ["groupA"]
 */
class AuthenticationConditions
{
    /**
     * @var string[]
     */
    private array $trustedClientAddresses = [];

    /**
     * @var string[]
     */
    private array $blacklistClientAddresses = [];

    /**
     * @param boolean $isEnabled
     * @param string $attributePath
     * @param Endpoint $endpoint
     * @param string[] $authorizedValues
     * @throws OpenIdConfigurationException
     */
    public function __construct(
        private bool $isEnabled,
        private string $attributePath,
        private Endpoint $endpoint,
        private array $authorizedValues
    ) {
        $this->validateMandatoryParametersForEnabledCondition(
            $isEnabled,
            $attributePath,
            $authorizedValues
        );
    }

    /**
     * @return boolean
     */
    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    /**
     * @return string
     */
    public function getAttributePath(): string
    {
        return $this->attributePath;
    }

    /**
     * @return Endpoint
     */
    public function getEndpoint(): Endpoint
    {
        return $this->endpoint;
    }

    /**
     * @return string[]
     */
    public function getAuthorizedValues(): array
    {
        return $this->authorizedValues;
    }

    /**
     * @return string[]
     */
    public function getTrustedClientAddresses(): array
    {
        return $this->trustedClientAddresses;
    }

    /**
     * @return string[]
     */
    public function getBlacklistClientAddresses(): array
    {
        return $this->blacklistClientAddresses;
    }

    /**
     * @param string[] $trustedClientAddresses
     * @return self
     * @throws AssertionException
     */
    public function setTrustedClientAddresses(array $trustedClientAddresses): self
    {
        $this->trustedClientAddresses = [];
        foreach ($trustedClientAddresses as $trustedClientAddress) {
            $this->addTrustedClientAddress($trustedClientAddress);
        }

        return $this;
    }

    /**
     * @param string $trustedClientAddress
     * @return self
     * @throws AssertionException
     */
    public function addTrustedClientAddress(string $trustedClientAddress): self
    {
        $this->validateClientAddressOrFail($trustedClientAddress, 'trustedClientAddresses');
        $this->trustedClientAddresses[] = $trustedClientAddress;

        return $this;
    }

    /**
     * @param string[] $blacklistClientAddresses
     * @return self
     * @throws AssertionException
     */
    public function setBlacklistClientAddresses(array $blacklistClientAddresses): self
    {
        $this->blacklistClientAddresses = [];
        foreach ($blacklistClientAddresses as $blacklistClientAddress) {
            $this->addBlacklistClientAddress($blacklistClientAddress);
        }

        return $this;
    }

    /**
     * @param string $blacklistClientAddress
     * @return self
     * @throws AssertionException
     */
    public function addBlacklistClientAddress(string $blacklistClientAddress): self
    {
        $this->validateClientAddressOrFail($blacklistClientAddress, 'blacklistClientAddresses');
        $this->blacklistClientAddresses[] = $blacklistClientAddress;

        return $this;
    }

    /**
     * @param string $clientAddress
     * @param string $fieldName
     * @throws AssertionException
     */
    private function validateClientAddressOrFail(string $clientAddress, string $fieldName): void
    {
        if (
            filter_var($clientAddress, FILTER_VALIDATE_IP) === false
            && filter_var($clientAddress, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) === false
        ) {
            throw AssertionException::ipOrDomain(
                $clientAddress,
                'AuthenticationConditions::' . $fieldName
            );
        }
    }

    /**
     * Validate that all mandatory parameters are correctly set when conditions are enabled
     *
     * @param boolean $isEnabled
     * @param string $attributePath
     * @param string[] $authorizedValues
     * @throws OpenIdConfigurationException
     */
    private function validateMandatoryParametersForEnabledCondition(
        bool $isEnabled,
        string $attributePath,
        array $authorizedValues
    ): void {
        if ($isEnabled) {
            $mandatoryParameters = [];
            if (empty($attributePath)) {
                $mandatoryParameters[] = "attribute_path";
            }
            if (empty($authorizedValues)) {
                $mandatoryParameters[] = "authorized_values";
            }
            if (! empty($mandatoryParameters)) {
                throw OpenIdConfigurationException::missingMandatoryParameters($mandatoryParameters);
            }
        }
    }
}
