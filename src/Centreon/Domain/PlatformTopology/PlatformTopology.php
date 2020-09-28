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

namespace Centreon\Domain\PlatformTopology;

use Security\Encryption;

/**
 * Class designed to retrieve servers to be added using the wizard
 *
 */
class PlatformTopology
{
    public const TYPE_CENTRAL = 'central';
    public const TYPE_POLLER = 'poller';
    public const TYPE_REMOTE = 'remote';
    private const TYPE_MAP = 'map';
    public const TYPE_MBI = 'mbi';

    /**
     * Available server types
     */
    private const AVAILABLE_TYPES = [
        self::TYPE_CENTRAL,
        self::TYPE_POLLER,
        self::TYPE_REMOTE,
        self::TYPE_MAP,
        self::TYPE_MBI
    ];

    /**
     * @var int|null Id of server
     */
    private $id;

    /**
     * @var string|null name
     */
    private $name;

    /**
     * @var string|null Server type
     */
    private $type;

    /**
     * @var string|null Server address
     */
    private $address;

    /**
     * @var string|null Server parent address
     */
    private $parentAddress;

    /**
     * @var int|null Server parent id
     */
    private $parentId;

    /**
     * @var int|null Server nagios ID for Central only
     */
    private $serverId;

    /**
     * @var bool Indicate if the API need to be called on the parent of the parent server (n-1)
     */
    private $isLinkedToAnotherServer = false;

    /**
     * data retrieved from 'informations' table
     * @var bool platform type
     */
    private $isRemote = false;

    /**
     * data retrieved from 'informations' table
     * @var string|null central's address
     */
    private $authorizedMaster;

    /**
     * data retrieved from 'informations' table
     * @var string|null
     */
    private $apiUsername;

    /**
     * data retrieved from 'informations' table
     * @var string|null
     */
    private $apiCredentials;

    /**
     * data retrieved from 'informations' table
     * @var string|null
     */
    private $apiScheme;

    /**
     * data retrieved from 'informations' table
     * @var int|null
     */
    private $apiPort;

    /**
     * data retrieved from 'informations' table
     * @var string|null
     */
    private $apiPath;

    /**
     * data retrieved from 'informations' table
     * @var bool SSL peer validation activated
     */
    private $apiPeerValidationActivated = false;

    /**
     * data retrieved from 'options' table
     * @var string|null
     */
    private $proxyUrl;

    /**
     * data retrieved from 'options' table
     * @var int|null
     */
    private $proxyPort;

    /**
     * data retrieved from 'options' table
     * @var string|null
     */
    private $proxyUsername;

    /**
     * data retrieved from 'options' table
     * @var string|null
     */
    private $proxyCredentials;

    /**
     * Validate address consistency
     *
     * @param string|null $address the address to be tested
     *
     * @return string|null
     */
    private function checkIpAddress(?string $address): ?string
    {
        // Check for valid IPv4 or IPv6 IP
        // or not sent address (in the case of Central's "parent_address")
        if (null === $address || false !== filter_var($address, FILTER_VALIDATE_IP)) {
            return $address;
        }

        // check for DNS to be resolved
        if (false === filter_var(gethostbyname($address), FILTER_VALIDATE_IP)) {
            throw new \InvalidArgumentException(
                sprintf(
                    _("The address of '%s' is not valid"),
                    $this->getName()
                )
            );
        }

        return $address;
    }

    /**
     * @param int|null $port
     * @return int
     */
    public function checkPortConsistency(?int $port): int
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
     * @param string|null $encryptedKey
     * @return string|null
     */
    public function decryptApiCredentials(?string $encryptedKey): ?string
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
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return $this
     */
    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParentAddress(): ?string
    {
        return $this->parentAddress;
    }

    /**
     * @param string|null $parentAddress
     *
     * @return $this
     */
    public function setParentAddress(?string $parentAddress): self
    {
        if (null !== $parentAddress && $this->getType() === static::TYPE_CENTRAL) {
            throw new \InvalidArgumentException(_("Cannot use parent address on a Central server type"));
        }
        $this->parentAddress = $this->checkIpAddress($parentAddress);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type server type: central, poller, remote, map or mbi
     *
     * @return $this
     */
    public function setType(string $type): self
    {
        $type = strtolower($type);

        // Check if the server_type is available
        if (!in_array($type, static::AVAILABLE_TYPES)) {
            throw new \InvalidArgumentException(
                sprintf(
                    _("The platform type of '%s'@'%s' is not consistent"),
                    $this->getName(),
                    $this->getAddress()
                )
            );
        }
        $this->type = $type;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return $this
     */
    public function setName(?string $name): self
    {
        $name = filter_var($name, FILTER_SANITIZE_STRING);
        if (empty($name)) {
            throw new \InvalidArgumentException(
                _('The name of the platform is not consistent')
            );
        }
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @param string|null $address
     *
     * @return $this
     */
    public function setAddress(?string $address): self
    {
        $this->address = $this->checkIpAddress($address);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    /**
     * @param int|null $parentId
     *
     * @return $this
     */
    public function setParentId(?int $parentId): self
    {
        if (null !== $parentId && $this->getType() === static::TYPE_CENTRAL) {
            throw new \InvalidArgumentException(_("Cannot set parent id to a central server"));
        }
        $this->parentId = $parentId;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getServerId(): ?int
    {
        return $this->serverId;
    }

    /**
     * @param int|null $serverId nagios_server ID
     * @return PlatformTopology
     */
    public function setServerId(?int $serverId): self
    {
        $this->serverId = $serverId;
        return $this;
    }

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
        $this->apiScheme = ($schema === 'https' ? 'https' : 'http');
        return $this;
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
        $path = filter_var($path, FILTER_SANITIZE_STRING);
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
    public function getApiPeerValidationActivated(): bool
    {
        return $this->apiPeerValidationActivated;
    }

    /**
     * @param bool $status
     * @return $this
     */
    public function setApiPeerValidationActivated(bool $status): self
    {
        $this->apiPeerValidationActivated = $status;
        return $this;
    }

    public function getProxyUrl(): ?string
    {
        return $this->proxyUrl;
    }

    /**
     * @param string|null $url
     * @return $this
     */
    public function setProxyUrl(?string $url): self
    {
        $path = filter_var($url, FILTER_SANITIZE_STRING);
        if (empty($url)) {
            throw new \InvalidArgumentException(
                _("Central's platform path is not consistent. Please check the 'Remote Access' form")
            );
        }
        $this->proxyUrl = $url;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getProxyPort(): ?int
    {
        return $this->proxyPort;
    }

    /**
     * @param int $port
     * @return $this
     */
    public function setProxyPort(int $port): self
    {
        $this->proxyPort = $this->checkPortConsistency($port);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getProxyUsername(): ?string
    {
        return $this->proxyUsername;
    }

    /**
     * @param string|null $username
     * @return $this
     */
    public function setProxyUsername(?string $username): self
    {
        $username = filter_var($username, FILTER_SANITIZE_STRING);
        if (empty($username)) {
            throw new \InvalidArgumentException(
                _("Central platform's data are not consistent. Please check the 'Remote Access' form")
            );
        }
        $this->proxyUsername = $username;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getProxyCredentials(): ?string
    {
        return $this->proxyCredentials;
    }

    /**
     * @param string|null $credential
     * @return $this
     */
    public function setProxyCredentials(?string $credential): self
    {
        $this->apiCredentials = $credential;
        return $this;
    }
}
