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

namespace Centreon\Domain\PlatformInformation\Model;

require_once __DIR__ . '/../../../../../www/class/HtmlAnalyzer.php';

use Centreon\Domain\PlatformInformation\Exception\PlatformInformationException;

/**
 * Class designed to retrieve servers' specific information
 *
 */
class PlatformInformation
{
    /**
     * @var bool platform type
     */
    private $isRemote;

    /**
     * @var string|null
     */
    private $platformName;

    /**
     * @var string server address
     */
    private string $address = '127.0.0.1';

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
     * @var bool SSL peer validation
     */
    private $apiPeerValidation = false;

    public function __construct(bool $isRemote)
    {
        $this->isRemote = $isRemote;
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
    public function setRemote(bool $isRemote): self
    {
        $this->isRemote = $isRemote;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPlatformName(): ?string
    {
        return $this->platformName;
    }

    /**
     * @param string|null $name
     * @return self
     */
    public function setPlatformName(?string $name): self
    {
        $this->platformName = \HtmlAnalyzer::sanitizeAndRemoveTags($name);
        if (empty($this->platformName)) {
            throw new \InvalidArgumentException(_("Platform name can't be empty"));
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @param string $address
     * @return $this
     */
    public function setAddress(string $address): self
    {
        $this->address = $address;

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
     * @param int $port
     * @return int
     * @throws PlatformInformationException
     */
    private function checkPortConsistency(int $port): int
    {
        if (1 > $port || $port > 65535) {
            throw PlatformInformationException::inconsistentDataException();
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
     * @throws PlatformInformationException
     */
    public function setApiPort(?int $port): self
    {
        if ($port !== null) {
            $this->apiPort = $this->checkPortConsistency($port);
        } else {
            $this->apiPort = $port;
        }

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
     * @throws PlatformInformationException
     */
    public function setApiPath(?string $path): self
    {
        if ($path !== null) {
            $path = trim(\HtmlAnalyzer::sanitizeAndRemoveTags($path), '/');
            if (empty($path)) {
                throw PlatformInformationException::inconsistentDataException();
            }
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
     * @param bool $status
     * @return $this
     */
    public function setApiPeerValidation(bool $status): self
    {
        $this->apiPeerValidation = $status;
        return $this;
    }
}
