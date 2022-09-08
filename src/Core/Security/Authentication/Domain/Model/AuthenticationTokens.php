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

namespace Core\Security\Authentication\Domain\Model;

class AuthenticationTokens
{
    /**
     * @var string
     */
    private string $sessionToken;

    /**
     * @var NewProviderToken
     */
    private NewProviderToken $providerToken;

    /**
     * @var null|NewProviderToken
     */
    private ?NewProviderToken $providerRefreshToken;

    /**
     * @var int
     */
    private int $userId;

    /**
     * @var int
     */
    private int $configurationProviderId;

    /**
     * @param int $userId
     * @param int $configurationProviderId
     * @param string $sessionToken
     * @param NewProviderToken $providerToken
     * @param NewProviderToken|null $providerRefreshToken
     */
    public function __construct(
        int $userId,
        int $configurationProviderId,
        string $sessionToken,
        NewProviderToken $providerToken,
        ?NewProviderToken $providerRefreshToken
    ) {
        $this->userId = $userId;
        $this->configurationProviderId = $configurationProviderId;
        $this->sessionToken = $sessionToken;
        $this->providerToken = $providerToken;
        $this->providerRefreshToken = $providerRefreshToken;
    }

    /**
     * @return string
     */
    public function getSessionToken(): string
    {
        return $this->sessionToken;
    }

    /**
     * @return NewProviderToken
     */
    public function getProviderToken(): NewProviderToken
    {
        return $this->providerToken;
    }

    /**
     * @return NewProviderToken|null
     */
    public function getProviderRefreshToken(): ?NewProviderToken
    {
        return $this->providerRefreshToken;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return int
     */
    public function getConfigurationProviderId(): int
    {
        return $this->configurationProviderId;
    }
}
