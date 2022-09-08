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

namespace Core\Security\ProviderConfiguration\Domain\WebSSO\Model;

use Centreon\Domain\Common\Assertion\AssertionException;
use Core\Security\ProviderConfiguration\Domain\CustomConfigurationInterface;
use Core\Security\ProviderConfiguration\Domain\Local\Model\SecurityPolicy;
use Security\Domain\Authentication\Interfaces\ProviderConfigurationInterface;

final class CustomConfiguration implements CustomConfigurationInterface, ProviderConfigurationInterface
{
    /**
     * @param array<string> $trustedClientAddresses
     * @param array<string> $blacklistClientAddresses
     * @param string|null $loginHeaderAttribute
     * @param string|null $patternMatchingLogin
     * @param string|null $patternReplaceLogin
     */
    public function __construct(
        private array $trustedClientAddresses = [],
        private array $blacklistClientAddresses = [],
        private ?string $loginHeaderAttribute = null,
        private ?string $patternMatchingLogin = null,
        private ?string $patternReplaceLogin = null
    ) {
        $this->guard();
    }

    /**
     * @return array<string>
     */
    public function getTrustedClientAddresses(): array
    {
        return $this->trustedClientAddresses;
    }

    /**
     * @return array<string>
     */
    public function getBlackListClientAddresses(): array
    {
        return $this->blacklistClientAddresses;
    }

    /**
     * @return string|null
     */
    public function getLoginHeaderAttribute(): ?string
    {
        return $this->loginHeaderAttribute;
    }

    /**
     * @return string|null
     */
    public function getPatternMatchingLogin(): ?string
    {
        return $this->patternMatchingLogin;
    }

    /**
     * @return string|null
     */
    public function getPatternReplaceLogin(): ?string
    {
        return $this->patternReplaceLogin;
    }

    /**
     * Validate ips
     */
    private function guard(): void
    {
        foreach ($this->getTrustedClientAddresses() as $trustedClientAddress) {
            if (filter_var($trustedClientAddress, FILTER_VALIDATE_IP) === false) {
                throw AssertionException::ipAddressNotValid(
                    $trustedClientAddress,
                    'WebSSOConfiguration::trustedClientAddresses'
                );
            }
        }
        foreach ($this->getBlackListClientAddresses() as $blacklistClientAddress) {
            if (filter_var($blacklistClientAddress, FILTER_VALIDATE_IP) === false) {
                throw AssertionException::ipAddressNotValid(
                    $blacklistClientAddress,
                    'WebSSOConfiguration::blacklistClientAddresses'
                );
            }
        }
    }
}
