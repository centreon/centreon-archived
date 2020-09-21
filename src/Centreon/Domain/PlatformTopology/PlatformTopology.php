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

use InvalidArgumentException;
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
    private const TYPE_MBI = 'mbi';

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
     * data recovered from 'informations' table
     * @var string|null platform type
     */
    private $isRemote;

    /**
     * data recovered from 'informations' table
     * @var string|null central's address
     */
    private $authorizedMaster;

    /**
     * data recovered from 'informations' table
     * @var string|null
     */
    private $apiUsername;

    /**
     * data recovered from 'informations' table
     * @var string|null
     */
    private $apiCredentials;

    /**
     * @return string|null
     */
    public function getIsRemote(): ?string
    {
        return $this->isRemote;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setIsRemote(string $type): self
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
     * @param string $encryptedKey
     * @return $this
     * @throws PlatformTopologyException
     * @throws InvalidArgumentException
     */
    public function setApiCredentials(string $encryptedKey): self
    {
        $this->apiCredentials = $this->decryptApiCredentials($encryptedKey);
        return $this;
    }

    /**
     * @param string $encryptedKey
     * @return string
     * @throws PlatformTopologyException
     * @throws InvalidArgumentException
     */
    public function decryptApiCredentials(string $encryptedKey): string
    {
        // first key
        $path = __DIR__ . "/../../../../";
        require_once $path . "/src/Security/Encryption.php";
        if (file_exists($path . '/.env.local.php')) {
            $localEnv = @include $path . '/.env.local.php';
        }

        // second key
        if (empty($localEnv) || !isset($localEnv['APP_SECRET'])) {
            throw new PlatformTopologyException(
                _("Unable to find the encryption key. Please check the '.env.local.php' file")
            );
        }
        $secondKey = base64_encode('api_remote_credentials');

        try {
            $centreonEncryption = new Encryption();
            $centreonEncryption->setFirstKey($localEnv['APP_SECRET'])->setSecondKey($secondKey);
            return $centreonEncryption->decrypt($encryptedKey);
        } catch (\throwable $e) {
            throw new InvalidArgumentException(
                _("Unable to decipher central's credentials. Please check the credentials in the remote access form")
            );
        }
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
     * @throws InvalidArgumentException
     */
    public function setParentAddress(?string $parentAddress): self
    {
        if (null !== $parentAddress && $this->getType() === static::TYPE_CENTRAL) {
            throw new InvalidArgumentException(_("Cannot use parent address on a Central server type"));
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
     * @throws InvalidArgumentException
     */
    public function setType(string $type): self
    {
        $type = strtolower($type);

        // Check if the server_type is available
        if (!in_array($type, static::AVAILABLE_TYPES)) {
            throw new InvalidArgumentException(
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
     * @throws InvalidArgumentException
     */
    public function setName(?string $name): self
    {
        $name = filter_var($name, FILTER_SANITIZE_STRING);
        if (empty($name)) {
            throw new InvalidArgumentException(
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
     * Validate address consistency
     *
     * @param string|null $address the address to be tested
     *
     * @return string|null
     * @throws InvalidArgumentException
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
            throw new InvalidArgumentException(
                sprintf(
                    _("The address of '%s' is not valid"),
                    $this->getName()
                )
            );
        }

        return $address;
    }

    /**
     * @return integer|null
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
            throw new InvalidArgumentException(_("Cannot set parent id to a central server"));
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
}
