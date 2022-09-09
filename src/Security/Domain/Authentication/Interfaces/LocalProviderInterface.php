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

namespace Security\Domain\Authentication\Interfaces;

use Core\Security\Authentication\Domain\Model\NewProviderToken;
use Core\Security\Authentication\Domain\Model\ProviderToken;
use Core\Security\ProviderConfiguration\Domain\Model\Configuration;
use Throwable;

/**
 * @package Security\Authentication\Interfaces
 */
interface LocalProviderInterface extends ProviderInterface
{
    /**
     * @param array<string, mixed> $data
     * @throws Throwable
     */
    public function authenticateOrFail(array $data): void;

    /**
     * Return the provider token
     *
     * @param string $token
     * @return NewProviderToken
     */
    public function getProviderToken(string $token): NewProviderToken;

    /**
     * Return the provider refresh token.
     *
     * @param string $token
     * @return NewProviderToken|null
     */
    public function getProviderRefreshToken(string $token): ?NewProviderToken;

    /**
     * Get the provider's configuration (ex: client_id, client_secret, grant_type, ...).
     *
     * @return Configuration
     */
    public function getConfiguration(): Configuration;
}
