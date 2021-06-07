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

namespace Security\Domain\Authentication\Model;

use Centreon\Domain\Common\Assertion\Assertion;

/**
 * @package Security\Authentication\Model
 */
class AuthenticationTokens
{
    private const SESSION_TOKEN_MIN_LENGTH = 1;

    /**
     * @var string
     */
    private $sessionToken;

    /**
     * @var ProviderToken
     */
    private $providerToken;

    /**
     * @var null|ProviderToken
     */
    private $providerRefreshToken;

    /**
     * @var int
     */
    private $userId;

    /**
     * @var int
     */
    private $configurationProviderId;

    /**
     * @param int $userId
     * @param int $configurationProviderId
     * @param string $sessionToken
     * @param ProviderToken $providerToken
     * @param ProviderToken|null $providerRefreshToken
     * @throws \Assert\AssertionFailedException
     */
    public function __construct(
        int $userId,
        int $configurationProviderId,
        string $sessionToken,
        ProviderToken $providerToken,
        ?ProviderToken $providerRefreshToken
    ) {
        Assertion::minLength($sessionToken, self::SESSION_TOKEN_MIN_LENGTH, 'AuthenticationToken::sessionToken');
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
     * @return ProviderToken
     */
    public function getProviderToken(): ProviderToken
    {
        return $this->providerToken;
    }

    /**
     * @return ProviderToken|null
     */
    public function getProviderRefreshToken(): ?ProviderToken
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
