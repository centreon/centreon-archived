<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
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

namespace Centreon\Domain\PlatformInformation;

use Security\Encryption;

/**
 * Class designed to retrieve servers' specific information
 *
 */
class PlatformInformation
{
    /**
     * @var bool Indicate if the server is behind another server (n-1)
     */
    private $isLinkedToAnotherServer = false;

    /**
     * @var bool platform type
     */
    private $isRemote = false;

    /**
     * @var string|null central's address
     */
    private $authorizedMaster;

    /**
     * @var string|null
     */
    private $apiUsername;

    /**
     * @var string|null
     */
    private $apiCredentials;

    /**
     * @var string|null
     */
    private $apiScheme;

    /**
     * @var int|null
     */
    private $apiPort;

    /**
     * @var string|null
     */
    private $apiPath;

    /**
     * @var bool SSL peer validation activated
     */
    private $sslPeerValidationRequired = false;

    /**
     * @return bool
     */
    public function isLinkedToAnotherServer(): bool
    {
        return $this->isLinkedToAnotherServer;
    }

    /**
     * @param bool $isLinked
     * @return $this
     */
    public function setLinkedToAnotherServer(bool $isLinked): self
    {
        $this->isLinkedToAnotherServer = $isLinked;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsRemote(): bool
    {
        return $this->isRemote;
    }

    /**
     * @param bool $type
     * @return $this
     */
    public function setIsRemote(bool $type): self
    {
        $this->isRemote = $type;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAuthorizedMaster(): ?string
    {
        return $this->authorizedMaster;
    }

    /**
     * @param string $address
     * @return $this
     */
    public function setAuthorizedMaster(string $address): self
    {
        $this->authorizedMaster = $address;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getApiUsername(): ?string
    {
        return $this->apiUsername;
    }

    /**
     * @param string $username
     * @return $this
     */
    public function setApiUsername(string $username): self
    {
        $this->apiUsername = $username;
        return $this;
    }

    /**
     * @param string|null $encryptedKey
     * @return string|null
     */
    private function decryptApiCredentials(?string $encryptedKey): ?string
    {
        if (empty($encryptedKey)) {
            return null;
        }
        // first key
        $path = __DIR__ . "/../../../../";
        require_once $path . "/src/Security/Encryption.php";
        if (file_exists($path . '/.env.local.php')) {
            $localEnv = @include $path . '/.env.local.php';
        }

        // second key
        if (empty($localEnv) || !isset($localEnv['APP_SECRET'])) {
            throw new \InvalidArgumentException(
                _("Unable to find the encryption key. Please check the '.env.local.php' file")
            );
        }
        $secondKey = base64_encode('api_remote_credentials');

        try {
            $centreonEncryption = new Encryption();
            $centreonEncryption->setFirstKey($localEnv['APP_SECRET'])->setSecondKey($secondKey);
            return $centreonEncryption->decrypt($encryptedKey);
        } catch (\throwable $e) {
            throw new \InvalidArgumentException(
                _("Unable to decipher central's credentials. Please check the credentials in the 'Remote Access' form")
            );
        }
    }

    /**
     * @return string|null
     */
    public function getApiCredentials(): ?string
    {
        return $this->apiCredentials;
    }

    /**
     * @param string|null $encryptedKey
     * @return $this
     */
    public function setApiCredentials(?string $encryptedKey): self
    {
        $this->apiCredentials = $this->decryptApiCredentials($encryptedKey);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getApiScheme(): ?string
    {
        return $this->apiScheme;
    }

    /**
     * @param string|null $schema
     * @return $this
     */
    public function setApiScheme(?string $schema): self
    {
        $schema = trim($schema, '/');
        $this->apiScheme = ($schema === 'https' ? 'https' : 'http');
        return $this;
    }

    /**
     * @param int|null $port
     * @return int
     */
    private function checkPortConsistency(?int $port): int
    {
        // checking
        $port = filter_var($port, FILTER_VALIDATE_INT);
        if (false === $port || 1 > $port || $port > 65536) {
            throw new \InvalidArgumentException(
                _("Central platform's data are not consistent. Please check the 'Remote Access' form")
            );
        }
        return $port;
    }

    /**
     * @return int|null
     */
    public function getApiPort(): ?int
    {
        return $this->apiPort;
    }

    /**
     * @param int|null $port
     * @return $this
     */
    public function setApiPort(?int $port): self
    {
        // auto resolving default scheme port
        if (null === $port && null !== $this->apiScheme) {
            if ('https' === $this->apiScheme) {
                $this->apiPort = 443;
                return $this;
            }
            if ('http' === $this->apiScheme) {
                $this->apiPort = 80;
                return $this;
            }
        }
        $this->apiPort = $this->checkPortConsistency($port);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getApiPath(): ?string
    {
        return $this->apiPath;
    }

    /**
     * @param string|null $path
     * @return $this
     */
    public function setApiPath(?string $path): self
    {
        $path = trim(filter_var($path, FILTER_SANITIZE_STRING), '/');
        if (empty($path)) {
            throw new \InvalidArgumentException(
                _("Central platform's data are not consistent. Please check the 'Remote Access' form")
            );
        }
        $this->apiPath = $path;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSslPeerValidationRequired(): bool
    {
        return $this->sslPeerValidationRequired;
    }

    /**
     * @param bool $status
     * @return $this
     */
    public function setSslPeerValidationRequired(bool $status): self
    {
        $this->sslPeerValidationRequired = $status;
        return $this;
    }
}
