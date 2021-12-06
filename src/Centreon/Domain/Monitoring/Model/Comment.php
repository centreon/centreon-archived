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
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var \DateTime|null
     */
    private $entryTime;

    /**
     * @var int|null
     */
    private $hostId;

    /**
     * @var int|null
     */
    private $serviceId;

    /**
     * @var string|null
     */
    private $author;

    /**
     * @var string|null
     */
    private $data;

    /**
     * @var \DateTime|null
     */
    private $deletionTime;

    /**
     * @var int|null
     */
    private $entryType;

    /**
     * @var \DateTime|null
     */
    private $expireTime;

    /**
     * @var int|null
     */
    private $expires;

    /**
     * @var int|null
     */
    private $instanceId;

    /**
     * @var int|null
     */
    private $internalId;

    /**
     * @var int|null
     */
    private $persistent;

    /**
     * @var int|null
     */
    private $source;

    /**
     * @var int|null
     */
    private $type;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return \DateTime|null
     */
    public function getEntryTime(): ?\DateTime
    {
        return $this->entryTime;
    }

    /**
     * @param \DateTime|null $entryTime
     */
    public function setEntryTime(?\DateTime $entryTime): void
    {
        $this->entryTime = $entryTime;
    }

    /**
     * @return int|null
     */
    public function getHostId(): ?int
    {
        return $this->hostId;
    }

    /**
     * @param int|null $hostId
     */
    public function setHostId(?int $hostId): void
    {
        $this->hostId = $hostId;
    }

    /**
     * @return int|null
     */
    public function getServiceId(): ?int
    {
        return $this->serviceId;
    }

    /**
     * @param int|null $serviceId
     */
    public function setServiceId(?int $serviceId): void
    {
        $this->serviceId = $serviceId;
    }

    /**
     * @return null|string
     */
    public function getAuthor(): ?string
    {
        return $this->author;
    }

    /**
     * @param null|string $author
     */
    public function setAuthor(?string $author): void
    {
        $this->author = $author;
    }

    /**
     * @return null|string
     */
    public function getData(): ?string
    {
        return $this->data;
    }

    /**
     * @param null|string $data
     */
    public function setData(?string $data): void
    {
        $this->data = $data;
    }

    /**
     * @return \DateTime|null
     */
    public function getDeletionTime(): ?\DateTime
    {
        return $this->deletionTime;
    }

    /**
     * @param \DateTime|null $deletionTime
     */
    public function setDeletionTime(?\DateTime $deletionTime): void
    {
        $this->deletionTime = $deletionTime;
    }

    /**
     * @return int|null
     */
    public function getEntryType(): ?int
    {
        return $this->entryType;
    }

    /**
     * @param int|null $entryType
     */
    public function setEntryType(?int $entryType): void
    {
        $this->entryType = $entryType;
    }

    /**
     * @return \DateTime|null
     */
    public function getExpireTime(): ?\DateTime
    {
        return $this->expireTime;
    }

    /**
     * @param \DateTime|null $expireTime
     */
    public function setExpireTime(?\DateTime $expireTime): void
    {
        $this->expireTime = $expireTime;
    }

    /**
     * @return int|null
     */
    public function getExpires(): ?int
    {
        return $this->expires;
    }

    /**
     * @param int|null $expires
     */
    public function setExpires(?int $expires): void
    {
        $this->expires = $expires;
    }

    /**
     * @return int|null
     */
    public function getInstanceId(): ?int
    {
        return $this->instanceId;
    }

    /**
     * @param int|null $instanceId
     */
    public function setInstanceId(?int $instanceId): void
    {
        $this->instanceId = $instanceId;
    }

    /**
     * @return int|null
     */
    public function getInternalId(): ?int
    {
        return $this->internalId;
    }

    /**
     * @param int|null $internalId
     */
    public function setInternalId(?int $internalId): void
    {
        $this->internalId = $internalId;
    }

    /**
     * @return int|null
     */
    public function getPersistent(): ?int
    {
        return $this->persistent;
    }

    /**
     * @param int|null $persistent
     */
    public function setPersistent(?int $persistent): void
    {
        $this->persistent = $persistent;
    }

    /**
     * @return int|null
     */
    public function getSource(): ?int
    {
        return $this->source;
    }

    /**
     * @param int|null $source
     */
    public function setSource(?int $source): void
    {
        $this->source = $source;
    }

    /**
     * @return int|null
     */
    public function getType(): ?int
    {
        return $this->type;
    }

    /**
     * @param int|null $type
     */
    public function setType(?int $type): void
    {
        $this->type = $type;
    }
}
