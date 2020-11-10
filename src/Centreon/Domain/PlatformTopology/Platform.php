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

use Centreon\Domain\PlatformTopology\PlatformRelation;

/**
 * Class designed to retrieve servers to be added using the wizard
 *
 */
class Platform
{
    public const TYPE_CENTRAL = 'central';
    public const TYPE_POLLER = 'poller';
    public const TYPE_REMOTE = 'remote';
    public const TYPE_MAP = 'map';
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
     * @var string|null chosen name : "virtual name"
     */
    private $name;

    /**
     * @var string|null  platform's real name : "physical name"
     */
    private $hostname;

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
     * @var bool
     */
    private $isLinkedToAnotherServer = false;

    /**
     * @var PlatformRelation Communication type between topology and parent
     */
    private $relation = null;

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
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type server type: central, poller, remote, map or mbi
     *
     * @return $this
     */
    public function setType(?string $type): self
    {
        if (null !== $type) {
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
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHostname(): ?string
    {
        return $this->hostname;
    }

    /**
     * @param string|null $hostname
     * @return $this
     */
    public function setHostname(?string $hostname): self
    {
        $this->hostname = $hostname;
        return $this;
    }

    /**
     * Validate address consistency
     *
     * @param string|null $address the address to be tested
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
                    _("The address '%s' of '%s' is not valid or not resolvable"),
                    $address,
                    $this->getName()
                )
            );
        }

        return $address;
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
     * @return Platform
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
     * @return PlatformRelation
     */
    public function getRelation(): ?PlatformRelation
    {
        return $this->relation;
    }

    /**
     * @param string|null $relationType
     * @return self
     */
    public function setRelation(?string $relationType): self
    {
        if ($this->getParentId() !== null) {
            $this->relation = (new PlatformRelation())
                ->setSource($this->getId())
                ->setRelation($relationType)
                ->setTarget($this->getParentId());
        }

        return $this;
    }
}
