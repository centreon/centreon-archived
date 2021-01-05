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
     * Credentials encryption key
     */
    public const ENCRYPT_SECOND_KEY = 'api_remote_credentials';

    /**
     * @var string|null platform version
     */
    private $version;

    /**
     * @var string|null
     */
    private $appKey;

    /**
     * @var bool platform type
     */
    private $isRemote = false;

    /**
     * @var bool platform type
     */
    private $isCentral = false;

    /**
     * @var string|null central's address
     */
    private $centralServerAddress;

    /**
     * @var string|null
     */
    private $apiUsername;

    /**
     * @var string|null
     */
    private $encryptedApiCredentials;

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
     * @var bool|null SSL peer validation
     */
    private $apiPeerValidation;

    /**
     * @return string|null
     */
    public function getVersion(): ?string
    {
        return $this->version;
    }

    /**
     * @param string|null $version
     * @return $this
     */
    public function setVersion(?string $version): self
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAppKey(): ?string
    {
        return $this->appKey;
    }

    /**
     * @param string|null $value
     * @return $this
     */
    public function setAppKey(?string $value): self
    {
        $this->appKey = $value;
        return $this;
    }

    /**
     *
     * @return bool
     */
    public function isRemote(): bool
    {
        return $this->isRemote;
    }

    /**
     * @param bool $isRemote
     * @return $this
     */
    public function setIsRemote(bool $isRemote): self
    {
        $this->isRemote = $isRemote;
        $this->isCentral = !$isRemote;
        return $this;
    }

    /**
     * @return bool
     */
    public function isCentral(): bool
    {
        return $this->isCentral;
    }

    /**
     * @param bool $isCentral
     * @return $this
     */
    public function setIsCentral(bool $isCentral): self
    {
        $this->isCentral = $isCentral;
        $this->isRemote = !$isCentral;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCentralServerAddress(): ?string
    {
        return $this->centralServerAddress;
    }

    /**
     * @param string|null $address
     * @return $this
     */
    public function setCentralServerAddress(?string $address): self
    {
        $this->centralServerAddress = $address;
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
     * @param string|null $username
     * @return $this
     */
    public function setApiUsername(?string $username): self
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

        if (!isset($_ENV['APP_SECRET'])) {
            throw new \InvalidArgumentException(
                _("Unable to find the encryption key. Please check the '.env.local.php' file.")
            );
        }

        // second key
        $secondKey = base64_encode(self::ENCRYPT_SECOND_KEY);

        try {
            $centreonEncryption = new Encryption();
            $centreonEncryption->setFirstKey($_ENV['APP_SECRET'])->setSecondKey($secondKey);
            return $centreonEncryption->decrypt($encryptedKey);
        } catch (\throwable $e) {
            throw new \InvalidArgumentException(
                _("Unable to decipher central's credentials. Please check the credentials in the 'Remote Access' form")
            );
        }
    }

    /**
     * encrypt the Central API Password
     *
     * @param string $password
     * @return string
     */
    private function encryptApiCredentials(string $password): string
    {
        if (!isset($_ENV['APP_SECRET'])) {
            throw new \InvalidArgumentException(
                _("Unable to find the encryption key. Please check the '.env.local.php' file.")
            );
        }

        $secondKey = base64_encode(self::ENCRYPT_SECOND_KEY);
        $centreonEncryption = new Encryption();
        $centreonEncryption->setFirstKey($_ENV['APP_SECRET'])->setSecondKey($secondKey);
        return $centreonEncryption->crypt($password);
    }

    /**
     * @return string|null
     */
    public function getApiCredentials(): ?string
    {
        return $this->apiCredentials;
    }

    /**
     * @param string|null $apiCredentials
     * @return $this
     */
    public function setApiCredentials(?string $apiCredentials): self
    {
        $this->apiCredentials = $apiCredentials;
        if (null !== $apiCredentials) {
            $apiCredentials = $this->encryptApiCredentials($apiCredentials);
        }
        $this->encryptedApiCredentials = $apiCredentials;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEncryptedApiCredentials(): ?string
    {
        return $this->encryptedApiCredentials;
    }

    /**
     * Undocumented function
     *
     * @param string|null $encryptedKey
     * @return self
     */
    public function setEncryptedApiCredentials(?string $encryptedKey): self
    {
        $this->encryptedApiCredentials = $encryptedKey;
        if (null !== $encryptedKey) {
            $encryptedKey = $this->decryptApiCredentials($encryptedKey);
        }
        $this->apiCredentials = $encryptedKey;

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
        if (null !== $schema) {
            $schema = ('https' === trim($schema, '/') ? 'https' : 'http');
        }
        $this->apiScheme = $schema;
        return $this;
    }

    /**
     * @param int|null $port
     * @return int
     */
    private function checkPortConsistency(?int $port): int
    {
        if (null === $port || 1 > $port || $port > 65535) {
            throw new \InvalidArgumentException(
                _("Central platform's API port is not consistent. Please check the 'Remote Access' form.")
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
        $path = trim(filter_var($path, FILTER_SANITIZE_STRING, ['options' => ['default' => '']]), '/');
        if (empty($path)) {
            throw new \InvalidArgumentException(
                _("Central platform's data are not consistent. Please check the 'Remote Access' form")
            );
        }
        $this->apiPath = $path;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function hasApiPeerValidation(): ?bool
    {
        return $this->apiPeerValidation;
    }

    /**
     * @param bool|null $status
     * @return $this
     */
    public function setApiPeerValidation(?bool $status): self
    {
        $this->apiPeerValidation = $status;
        return $this;
    }
}
