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

use Security\Domain\Authentication\Model\AuthenticationTokens;

/**
 * @package Security\Authentication\Interfaces
 */
interface ProviderInterface
{
    /**
     * Indicates whether or not the provider has a mechanism to refresh the token.
     *
     * @return bool
     */
    public function canRefreshToken(): bool;

    /**
     * @param AuthenticationTokens $authenticationToken
     * @return AuthenticationTokens|null
     */
    public function refreshToken(AuthenticationTokens $authenticationToken): ?AuthenticationTokens;

    /**
     * Return the provider's name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Set the provider's configuration to initialize it (ex: client_id, client_secret, grant_type, ...).
     *
     * @param ProviderConfiguration $configuration
     */
    public function setConfiguration(ProviderConfigurationInterface $configuration): void;
}
