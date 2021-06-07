<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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

namespace Centreon\Domain\Authentication\UseCase;

class AuthenticateRequest
{
    /**
     * @var array<string,mixed>
     */
    private $credentials;

    /**
     * @var string
     */
    private $providerConfigurationName;

    /**
     * @var string
     */
    private $centreonBaseUri;

    /**
     * @param array<string,mixed> $credentials
     * @param string $providerConfigurationName
     */
    public function __construct(array $credentials, string $providerConfigurationName, string $centreonBaseUri)
    {
        if (empty($credentials)) {
            throw new \InvalidArgumentException(_('Missing credentials arguments'));
        }

        $this->credentials = $credentials;
        $this->providerConfigurationName = $providerConfigurationName;
        $this->centreonBaseUri = $centreonBaseUri;
    }

    /**
     * Get credentials
     *
     * @return array<string,mixed>
     */
    public function getCredentials(): array
    {
        return $this->credentials;
    }

    /**
     * Get provider configuration name
     *
     * @return string
     */
    public function getProviderConfigurationName(): string
    {
        return $this->providerConfigurationName;
    }

    /**
     * Get redirection uri
     *
     * @return string
     */
    public function getCentreonBaseUri(): string
    {
        return $this->centreonBaseUri;
    }
}
