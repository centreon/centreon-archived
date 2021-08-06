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

namespace Centreon\Domain\PlatformTopology\Model;

use Centreon\Domain\PlatformTopology\Interfaces\PlatformInterface;

/**
 * Class designed to retrieve servers to be added using the wizard
 *
 */
class PlatformPending implements PlatformInterface
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
     * @var PlatformRelation|null Communication type between topology and parent
     */
    private $relation;

    /**
     * @var bool define if the platform is in a pending state or is already registered
     * By default PlatformPending entities are pending platforms
     */
    private $isPending = true;

    /**
     * @inheritDoc
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function setId(?int $id): PlatformInterface
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function setType(?string $type): PlatformInterface
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
     * @inheritDoc
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function setName(?string $name): PlatformInterface
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getHostname(): ?string
    {
        return $this->hostname;
    }

    /**
     * @inheritDoc
     */
    public function setHostname(?string $hostname): PlatformInterface
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
        $address = filter_var(gethostbyname($address), FILTER_VALIDATE_IP);
        if (false === $address) {
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
     * @inheritDoc
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @inheritDoc
     */
    public function setAddress(?string $address): PlatformInterface
    {
        $this->address = $this->checkIpAddress($address);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getParentAddress(): ?string
    {
        return $this->parentAddress;
    }

    /**
     * @inheritDoc
     */
    public function setParentAddress(?string $parentAddress): PlatformInterface
    {
        if (null !== $parentAddress && $this->getType() === static::TYPE_CENTRAL) {
            throw new \InvalidArgumentException(_("Cannot use parent address on a Central server type"));
        }
        $this->parentAddress = $this->checkIpAddress($parentAddress);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    /**
     * @inheritDoc
     */
    public function setParentId(?int $parentId): PlatformInterface
    {
        if (null !== $parentId && $this->getType() === static::TYPE_CENTRAL) {
            throw new \InvalidArgumentException(_("Cannot set parent id to a central server"));
        }
        $this->parentId = $parentId;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getServerId(): ?int
    {
        return $this->serverId;
    }

    /**
     * @inheritDoc
     */
    public function setServerId(?int $serverId): PlatformInterface
    {
        $this->serverId = $serverId;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isLinkedToAnotherServer(): bool
    {
        return $this->isLinkedToAnotherServer;
    }

    /**
     * @inheritDoc
     */
    public function setLinkedToAnotherServer(bool $isLinked): PlatformInterface
    {
        $this->isLinkedToAnotherServer = $isLinked;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRelation(): ?PlatformRelation
    {
        return $this->relation;
    }

    /**
     * @inheritDoc
     */
    public function setRelation(?string $relationType): PlatformInterface
    {
        if ($this->getParentId() !== null) {
            $this->relation = (new PlatformRelation())
                ->setSource($this->getId())
                ->setRelation($relationType)
                ->setTarget($this->getParentId());
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isPending(): bool
    {
        return $this->isPending;
    }

    /**
     * @inheritDoc
     */
    public function setPending(bool $isPending): PlatformInterface
    {
        $this->isPending = $isPending;
        return $this;
    }
}
