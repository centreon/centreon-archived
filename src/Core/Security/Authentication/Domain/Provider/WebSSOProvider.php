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

namespace Core\Security\Authentication\Domain\Provider;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Core\Security\Authentication\Domain\Model\AuthenticationTokens;
use Core\Security\ProviderConfiguration\Domain\Model\Configuration;
use Security\Domain\Authentication\Interfaces\WebSSOProviderInterface;

class WebSSOProvider implements WebSSOProviderInterface
{
    public const NAME = 'web-sso';
    public const TYPE = 'web-sso';

    /**
     * @var \Centreon
     */
    private \Centreon $legacySession;

    /**
     * @var Configuration
     */
    private Configuration $configuration;

    /**
     * @inheritDoc
     */
    public function getLegacySession(): \Centreon
    {
        return $this->legacySession;
    }

    /**
     * @inheritDoc
     */
    public function setLegacySession(\Centreon $legacySession): void
    {
        $this->legacySession = $legacySession;
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
    public function canRefreshToken(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @inheritDoc
     */
    public function getUser(): ?ContactInterface
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function setConfiguration(Configuration $configuration): void
    {
        $this->configuration = $configuration;
    }

    /**
     * @inheritDoc
     */
    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    /**
     * @inheritDoc
     */
    public function refreshToken(AuthenticationTokens $authenticationTokens): ?AuthenticationTokens
    {
        return null;
    }
}
