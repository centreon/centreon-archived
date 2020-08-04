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

use Symfony\Component\HttpFoundation\Response;

/**
 * Class designed to retrieve servers to be added using the wizard
 *
 */
class PlatformTopology
{
    /**
     * Available server types
     */
    private const SERVER_TYPE_CENTRAL = 0;
    private const SERVER_TYPE_POLLER = 1;
    private const SERVER_TYPE_REMOTE = 2;
    private const SERVER_TYPE_MAP = 3;
    private const SERVER_TYPE_MBI = 4;

    /**
     * Used to dynamically concatenate the thrown error when checking IP validity
     */
    private const KIND_SERVER = 'platform';
    private const KIND_PARENT = 'parent platform';

    /**
     * @var int Id of server
     */
    private $id;
    /**
     * @var string Server name
     */
    private $serverName;
    /**
     * @var int Server type
     */
    private $serverType;
    /**
     * @var string Server IP address
     */
    private $serverAddress;
    /**
     * @var string Server parent IP
     */
    private $serverParentAddress;
    /**
     * @var int Server parent id
     */
    private $serverParentId;

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
     * @return int
     */
    public function getServerType(): int
    {
        return $this->serverType;
    }

    /**
     * @param int $serverType server type
     *      SERVER_TYPE_CENTRAL = 0
     *      SERVER_TYPE_POLLER  = 1
     *      SERVER_TYPE_REMOTE  = 2
     *      SERVER_TYPE_MAP     = 3
     *      SERVER_TYPE_MBI     = 4
     *
     * @return $this
     * @throws PlatformTopologyException
     */
    public function setServerType(int $serverType): self
    {
        // The API should not be used to add a Central to another Central
        if (self::SERVER_TYPE_CENTRAL === $serverType) {
            throw new PlatformTopologyException(
                sprintf(
                    _("You cannot link the Central '%s'@'%s' to another Central"),
                    $this->getServerName(),
                    $this->getServerAddress()
                ),
                Response::HTTP_BAD_REQUEST
            );
        }

        // Check if the server_type is available
        $availableServerType = [
            self::SERVER_TYPE_POLLER,
            self::SERVER_TYPE_REMOTE,
            self::SERVER_TYPE_MAP,
            self::SERVER_TYPE_MBI
        ];
        if (!in_array($serverType, $availableServerType)) {
            throw new \InvalidArgumentException(
                sprintf(
                    _("The type of platform '%s'@'%s' is not consistent"),
                    $this->getServerName(),
                    $this->getServerAddress()
                ),
                Response::HTTP_BAD_REQUEST
            );
        }
        $this->serverType = $serverType;
        return $this;
    }

    /**
     * @return string
     */
    public function getServerName(): string
    {
        return $this->serverName;
    }

    /**
     * @param string $serverName
     * @return $this
     * @throws PlatformTopologyException
     */
    public function setServerName(string $serverName): self
    {
        $serverName = filter_var($serverName, FILTER_SANITIZE_STRING);
        if (empty($serverName)) {
            throw new PlatformTopologyException(
                _('The name of the platform is not consistent'),
                Response::HTTP_BAD_REQUEST
            );
        }
        $this->serverName = $serverName;
        return $this;
    }

    /**
     * Validate address consistency
     *
     * @param string $kind server or parent of the server
     * @param string|null $address the address to be tested
     *
     * @return string
     * @throws PlatformTopologyException
     */
    private function checkIpAddress(string $kind, ?string $address): string
    {
        // Server linked to the Central, may not send a parent address in the data
        if (!isset($address) && self::KIND_PARENT === $kind) {
            return $_SERVER['SERVER_ADDR'];
        }

        // Check for valid IPv4 or IPv6 IP
        if (false !== filter_var($address, FILTER_VALIDATE_IP)) {
            return $address;
        }

        // check for DNS to be resolved
        if (false === filter_var(gethostbyname($address), FILTER_VALIDATE_IP)) {
            throw new \InvalidArgumentException(
                sprintf(
                    _("The address of the " . $kind . " '%s' is not consistent"),
                    $this->getServerName()
                ),
                Response::HTTP_BAD_REQUEST
            );
        }

        return $address;
    }

    /**
     * @return string
     */
    public function getServerAddress(): string
    {
        return $this->serverAddress;
    }

    /**
     * @param string $serverAddress
     *
     * @return $this
     * @throws PlatformTopologyException
     */
    public function setServerAddress(string $serverAddress): self
    {
        $this->serverAddress = $this->checkIpAddress(self::KIND_SERVER, $serverAddress);
        return $this;
    }

    /**
     * @return string
     */
    public function getServerParentAddress(): string
    {
        return $this->serverParentAddress;
    }

    /**
     * @param string|null $serverParentAddress
     *
     * @return $this
     * @throws PlatformTopologyException
     */
    public function setServerParentAddress(?string $serverParentAddress): self
    {
        $this->serverParentAddress = $this->checkIpAddress(self::KIND_PARENT, $serverParentAddress);
        return $this;
    }

    public function getServerParentId(): int
    {
        return $this->serverParentId;
    }

    public function setServerParentId(int $parentId): self
    {
        $this->serverParentId = $parentId;
        return $this;
    }
}
