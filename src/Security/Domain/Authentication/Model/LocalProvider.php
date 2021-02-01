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

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Security\Domain\Authentication\Interfaces\ProviderInterface;

/**
 * @package Security\Authentication\Model
 */
class LocalProvider implements ProviderInterface
{
    public const NAME = 'local';

    private $loginUrl;

    /**
     * LocalProvider constructor.
     *
     * @param string $loginUrl
     */
    public function __construct(string $loginUrl)
    {
        $this->loginUrl = $loginUrl;
    }

    /**
     * @inheritDoc
     */
    public function authenticate(array $data): void
    {
    }

    /**
     * @inheritDoc
     */
    public function canCreateUser(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function refreshToken(AuthenticationTokens $refreshToken): ?AuthenticationTokens
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getAuthenticationUri(): string
    {
        return $this->loginUrl;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return self::NAME;
    }

    public function getUser(): ?ContactInterface
    {
        return (new Contact())->setId(1);
    }

    /**
     * @inheritDoc
     */
    public function exportConfiguration(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function importConfiguration(array $configuration): void
    {
    }

    /**
     * @inheritDoc
     */
    public function canRefreshToken(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function isForced(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isAuthenticated(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getProviderRefreshToken(): ?ProviderToken
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getProviderToken(): ?ProviderToken
    {
        return null;
    }
}