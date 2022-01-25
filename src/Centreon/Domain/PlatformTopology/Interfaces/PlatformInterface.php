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

namespace Centreon\Domain\PlatformTopology\Interfaces;

use Centreon\Domain\PlatformTopology\Model\PlatformRelation;

/**
 * Interface required to managed registered and pending platforms' entity
 * @package Centreon\Domain\PlatformTopology\Interfaces
 */
interface PlatformInterface
{
    /**
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * @param int|null $id
     * @return self
     */
    public function setId(?int $id): self;

    /**
     * @return string|null
     */
    public function getType(): ?string;

    /**
     * @param string|null $type server type: central, poller, remote, map or mbi
     *
     * @return self
     */
    public function setType(?string $type): self;

    /**
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * @param string|null $name
     * @return self
     */
    public function setName(?string $name): self;

    /**
     * @return string|null
     */
    public function getHostname(): ?string;

    /**
     * @param string|null $hostname
     * @return self
     */
    public function setHostname(?string $hostname): self;

    /**
     * @return string|null
     */
    public function getAddress(): ?string;

    /**
     * @param string|null $address
     *
     * @return self
     */
    public function setAddress(?string $address): self;

    /**
     * @return string|null
     */
    public function getParentAddress(): ?string;

    /**
     * @param string|null $parentAddress
     *
     * @return self
     */
    public function setParentAddress(?string $parentAddress): self;

    /**
     * @return int|null
     */
    public function getParentId(): ?int;

    /**
     * @param int|null $parentId
     *
     * @return self
     */
    public function setParentId(?int $parentId): self;

    /**
     * @return int|null
     */
    public function getServerId(): ?int;

    /**
     * @param int|null $serverId monitoring ID
     * @return self
     */
    public function setServerId(?int $serverId): self;

    /**
     * @return bool
     */
    public function isLinkedToAnotherServer(): bool;

    /**
     * @param bool $isLinked
     * @return self
     */
    public function setLinkedToAnotherServer(bool $isLinked): self;

    /**
     * @return PlatformRelation
     */
    public function getRelation(): ?PlatformRelation;

    /**
     * @param string|null $relationType
     * @return self
     */
    public function setRelation(?string $relationType): self;

    /**
     * @return bool
     */
    public function isPending(): bool;

    /**
     * @param bool $isPending
     * @return self
     */
    public function setPending(bool $isPending): self;
}
