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
     * @param array $json
     */
    public function __construct(private array $json)
    {
        $this->guard();
    }

    /**
     * @return array<string>
     */
    public function getTrustedClientAddresses(): array
    {
        return $this->json['trusted_client_addresses'] ?? [];
    }

    /**
     * @return array<string>
     */
    public function getBlackListClientAddresses(): array
    {
        return $this->json['blacklist_client_addresses'] ?? [];
    }

    /**
     * @return string|null
     */
    public function getLoginHeaderAttribute(): ?string
    {
        return $this->json['login_header_attribute'] ?? null;
    }

    /**
     * @return string|null
     */
    public function getPatternMatchingLogin(): ?string
    {
        return $this->json['pattern_matching_login'] ?? null;
    }

    /**
     * @return string|null
     */
    public function getPatternReplaceLogin(): ?string
    {
        return $this->json['pattern_replace_login'];
    }


    /**
     * @return void
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
