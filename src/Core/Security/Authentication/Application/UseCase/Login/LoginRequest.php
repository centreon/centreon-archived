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

class LoginRequest
{
    /**
     * @param string $providerName
     * @param string|null $clientIp
     * @param string|null $username
     * @param string|null $password
     * @param string|null $code
     */
    private function __construct(
        private string $providerName,
        private ?string $clientIp = null,
        private ?string $username = null,
        private ?string $password = null,
        private ?string $code = null)
    {
    }

    /**
     * @param string $providerName
     * @param string $username
     * @param string $password
     * @param string|null $clientIp
     * @return LoginRequest
     */
    public static function createForLocal(
        string $providerName,
        string $username,
        string $password,
        ?string $clientIp = null): self {

        return new self($providerName, $clientIp, $username, $password);
    }

    /**
     * @param string $providerName
     * @param string $clientIp
     * @param string $code
     * @return LoginRequest
     */
    public static function createForOpenId(string $providerName, string $clientIp, string $code): self {
        return new self($providerName, $clientIp, null, null, $code);
    }

    /**
     * @param string $providerName
     * @param string $clientIp
     * @return LoginRequest
     */
    public static function createForSSO(string $providerName, string $clientIp): self {
        return new self($providerName, $clientIp);
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getProviderName(): string
    {
        return $this->providerName;
    }

    /**
     * @return string
     */
    public function getClientIp(): string
    {
        return $this->clientIp;
    }

    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }
}