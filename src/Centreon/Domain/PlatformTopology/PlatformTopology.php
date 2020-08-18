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

/**
 * Class designed to retrieve servers to be added using the wizard
 *
 */
class PlatformTopology
{
    private const TYPE_CENTRAL = 'central';
    private const TYPE_POLLER = 'poller';
    private const TYPE_REMOTE = 'remote';
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
     * @var int Id of server
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
     * @throws \InvalidArgumentException
     */
    public function setType(?string $type): self
    {
        $type = strtolower($type);

        // The API should not be used to add a Central to another Central
        if ('central' === $type) {
            throw new \InvalidArgumentException(
                sprintf(
                    _("You cannot link the Central '%s'@'%s' to another Central"),
                    $this->getName(),
                    $this->getAddress()
                )
            );
        }

        // Check if the server_type is available
        if (!in_array($type, static::AVAILABLE_TYPES)) {
            throw new \InvalidArgumentException(
                sprintf(
                    _("The type of platform '%s'@'%s' is not consistent"),
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
     * @throws \InvalidArgumentException
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
     * Validate address consistency
     *
     * @param string $address the address to be tested
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    private function checkIpAddress(string $address): string
    {
        // Check for valid IPv4, IPv6 or resolvable DNS
        if (
            false === filter_var($address, FILTER_VALIDATE_IP)
            && false === filter_var(gethostbyname($address), FILTER_VALIDATE_IP)
        ) {
            throw new \InvalidArgumentException(
                sprintf(
                    _("The address '%s' for '%s' is not valid"),
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
        if ($this->getType() === static::TYPE_CENTRAL && $parentAddress !== null) {
            throw new \InvalidArgumentException(_("Cannot set parent address to a central server"));
        }
        $this->parentAddress = $this->checkIpAddress($parentAddress);
        return $this;
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
        if ($this->getType() === static::TYPE_CENTRAL && $parentId !== null) {
            throw new \InvalidArgumentException(_("Cannot set parent id to a central server"));
        }
        $this->parentId = $parentId;
        return $this;
    }
}
