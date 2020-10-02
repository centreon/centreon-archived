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

use Centreon\Domain\Annotation\EntityDescriptor;
use Security\Encryption;

/**
 * Class designed to retrieve servers' specific information
 *
 */
class PlatformInformation
{
    /**
     * @var string|null platform version
     * @EntityDescriptor(column="version", modifier="setVersion")
     */
    private $version;

    /**
     * @var string|null
     * @EntityDescriptor(column="appKey", modifier="setAppKey")
     */
    private $appKey;

    /**
     * @var bool platform type
     * @EntityDescriptor(column="isRemote", modifier="setIsRemote")
     */
    private $isRemote = false;

    /**
     * @var bool platform type
     * @EntityDescriptor(column="isCentral", modifier="setIsCentral")
     */
    private $isCentral = false;

    /**
     * @var string|null central's address
     * @EntityDescriptor(column="authorizedMaster", modifier="setAuthorizedMaster")
     */
    private $authorizedMaster;

    /**
     * @var string|null
     * @EntityDescriptor(column="apiUsername", modifier="setApiUsername")
     */
    private $apiUsername;

    /**
     * @var string|null
     * @EntityDescriptor(column="apiCredentials", modifier="setApiCredentials")
     */
    private $apiCredentials;

    /**
     * @var string|null
     * @EntityDescriptor(column="apiScheme", modifier="setApiScheme")
     */
    private $apiScheme;

    /**
     * @var int|null
     * @EntityDescriptor(column="apiPort", modifier="setApiPort")
     */
    private $apiPort;

    /**
     * @var string|null
     * @EntityDescriptor(column="apiPath", modifier="setApiPath")
     */
    private $apiPath;

    /**
     * @var bool SSL peer validation
     * @EntityDescriptor(column="apiPeerValidation", modifier="setApiPeerValidation")
     */
    private $apiPeerValidation = false;

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
     * @param string|null $isRemote
     * @return $this
     */
    public function setIsRemote(?string $isRemote): self
    {
        $this->isRemote = ('yes' === ($isRemote ?? null));
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
     * @param string|null $isCentral
     * @return $this
     */
    public function setIsCentral(?string $isCentral): self
    {
        $this->isCentral = ('yes' === ($isCentral ?? null));
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
     * @param string|null $address
     * @return $this
     */
    public function setAuthorizedMaster(?string $address): self
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
        // first key
        $path = __DIR__ . "/../../../../";
        require_once $path . "/src/Security/Encryption.php";
        if (file_exists($path . '/.env.local.php')) {
            $localEnv = @include $path . '/.env.local.php';
        }

        // second key
        if (empty($localEnv) || !isset($localEnv['APP_SECRET'])) {
            throw new \InvalidArgumentException(
                _("Unable to find the encryption key. Please check the '.env.local.php' file.")
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
                _("Central platform's API port are not consistent. Please check the 'Remote Access' form.")
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
     * @return bool
     */
    public function hasApiPeerValidation(): bool
    {
        return $this->apiPeerValidation;
    }

    /**
     * @param string|null $status
     * @return $this
     */
    public function setApiPeerValidation(?string $status): self
    {
        $this->apiPeerValidation = ('yes' === ($status ?? null));
        return $this;
    }
}
