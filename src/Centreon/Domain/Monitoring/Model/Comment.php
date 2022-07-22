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

namespace Centreon\Domain\Monitoring\Model;

class Comment
{
    private ?int $id = null;

    private ?\DateTime $entryTime = null;

    private ?int $hostId = null;

    private ?int $serviceId = null;

    private ?string $author = null;

    private ?string $data = null;

    private ?\DateTime $deletionTime = null;

    private ?int $entryType = null;

    private ?\DateTime $expireTime = null;

    private ?int $expires = null;

    private ?int $instanceId = null;

    private ?int $internalId = null;

    private ?int $persistent = null;

    private ?int $source = null;

    private ?int $type = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getEntryTime(): ?\DateTime
    {
        return $this->entryTime;
    }

    public function setEntryTime(?\DateTime $entryTime): void
    {
        $this->entryTime = $entryTime;
    }

    public function getHostId(): ?int
    {
        return $this->hostId;
    }

    public function setHostId(?int $hostId): void
    {
        $this->hostId = $hostId;
    }

    public function getServiceId(): ?int
    {
        return $this->serviceId;
    }

    public function setServiceId(?int $serviceId): void
    {
        $this->serviceId = $serviceId;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): void
    {
        $this->author = $author;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function setData(?string $data): void
    {
        $this->data = $data;
    }

    public function getDeletionTime(): ?\DateTime
    {
        return $this->deletionTime;
    }

    public function setDeletionTime(?\DateTime $deletionTime): void
    {
        $this->deletionTime = $deletionTime;
    }

    public function getEntryType(): ?int
    {
        return $this->entryType;
    }

    public function setEntryType(?int $entryType): void
    {
        $this->entryType = $entryType;
    }

    public function getExpireTime(): ?\DateTime
    {
        return $this->expireTime;
    }

    public function setExpireTime(?\DateTime $expireTime): void
    {
        $this->expireTime = $expireTime;
    }

    public function getExpires(): ?int
    {
        return $this->expires;
    }

    public function setExpires(?int $expires): void
    {
        $this->expires = $expires;
    }

    public function getInstanceId(): ?int
    {
        return $this->instanceId;
    }

    public function setInstanceId(?int $instanceId): void
    {
        $this->instanceId = $instanceId;
    }

    public function getInternalId(): ?int
    {
        return $this->internalId;
    }

    public function setInternalId(?int $internalId): void
    {
        $this->internalId = $internalId;
    }

    public function getPersistent(): ?int
    {
        return $this->persistent;
    }

    public function setPersistent(?int $persistent): void
    {
        $this->persistent = $persistent;
    }

    public function getSource(): ?int
    {
        return $this->source;
    }

    public function setSource(?int $source): void
    {
        $this->source = $source;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): void
    {
        $this->type = $type;
    }
}
