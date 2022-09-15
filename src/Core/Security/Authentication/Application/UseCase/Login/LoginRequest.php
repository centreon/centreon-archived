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

namespace Core\Security\Authentication\Application\UseCase\Login;

use Core\Security\ProviderConfiguration\Domain\Model\Provider;

class LoginRequest
{
    /**
     * @param string $providerName
     * @param string|null $clientIp
     * @param string|null $username
     * @param string|null $password
     * @param string|null $code
     * @param string|null $refererQueryParameters
     */
    private function __construct(
        public string $providerName,
        public ?string $clientIp = null,
        public ?string $username = null,
        public ?string $password = null,
        public ?string $code = null,
        public ?string $refererQueryParameters = null
    ) {
    }

    /**
     * @param string $username
     * @param string $password
     * @param string|null $clientIp
     * @param string|null $refererQueryParameters
     * @return LoginRequest
     */
    public static function createForLocal(
        string $username,
        string $password,
        ?string $clientIp = null,
        ?string $refererQueryParameters = null
    ): self {

        return new self(
            Provider::LOCAL,
            $clientIp,
            $username,
            $password,
            null,
            $refererQueryParameters
        );
    }

    /**
     * @param string $clientIp
     * @param string $code
     * @return LoginRequest
     */
    public static function createForOpenId(string $clientIp, string $code): self
    {
        return new self(Provider::OPENID, $clientIp, null, null, $code);
    }

    /**
     * @param string $clientIp
     * @return LoginRequest
     */
    public static function createForSSO(string $clientIp): self
    {
        return new self(Provider::WEB_SSO, $clientIp);
    }
}
