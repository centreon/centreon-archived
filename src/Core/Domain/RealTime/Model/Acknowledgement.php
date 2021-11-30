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

namespace Core\Domain\RealTime\Model;

class Acknowledgement
{
    // Types
    public const TYPE_HOST_ACKNOWLEDGEMENT = 0;
    public const TYPE_SERVICE_ACKNOWLEDGEMENT = 1;

    public function __construct(
        private int $id,
        private int $hostId,
        private int $serviceId,
        private \DateTime $entryTime
    ) {
    }

    /**
     * @var int
     */
    private $instanceId;

    /**
     * @var int|null
     */
    private $authorId;

    /**
     * @var string|null
     */
    private $authorName;

    /**
     * @var string|null
     */
    private $comment;

    /**
     * @var \DateTime|null
     */
    private $deletionTime;

    /**
     * @var bool
     */
    private $isNotifyContacts = true;

    /**
     * @var bool
     */
    private $isPersistentComment = true;

    /**
     * @var bool
     */
    private $isSticky = true;

    /**
     * @var int|null
     */
    private $state;

    /**
     * @var int|null
     */
    private $type;

    /**
     * @var bool
     */
    private $withServices = false;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return self
     */
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getInstanceId(): ?int
    {
        return $this->instanceId;
    }

    /**
     * @param int $instanceId
     * @return self
     */
    public function setInstanceId(int $instanceId): self
    {
        $this->instanceId = $instanceId;
        return $this;
    }

    /**
     * @return int
     */
    public function getHostId(): int
    {
        return $this->hostId;
    }

    /**
     * @param int $hostId
     * @return self
     */
    public function setHostId(int $hostId): self
    {
        $this->hostId = $hostId;
        return $this;
    }

    /**
     * @return int
     */
    public function getServiceId(): int
    {
        return $this->serviceId;
    }

    /**
     * @param int $serviceId
     * @return self
     */
    public function setServiceId(int $serviceId): self
    {
        $this->serviceId = $serviceId;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getAuthorId(): ?int
    {
        return $this->authorId;
    }

    /**
     * @param int|null $authorId
     * @return self
     */
    public function setAuthorId(?int $authorId): self
    {
        $this->authorId = $authorId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAuthorName(): ?string
    {
        return $this->authorName;
    }

    /**
     * @param string|null $authorName
     * @return self
     */
    public function setAuthorName(?string $authorName): self
    {
        $this->authorName = $authorName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @param string|null $comment
     * @return self
     */
    public function setComment(?string $comment): self
    {
        $this->comment = $comment;
        return $this;
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
     * @return self
     */
    public function setDeletionTime(?\DateTime $deletionTime): self
    {
        $this->deletionTime = $deletionTime;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEntryTime(): \DateTime
    {
        return $this->entryTime;
    }

    /**
     * @return bool
     */
    public function isNotifyContacts(): bool
    {
        return $this->isNotifyContacts;
    }

    /**
     * @param bool $isNotifyContacts
     * @return self
     */
    public function setNotifyContacts(bool $isNotifyContacts): self
    {
        $this->isNotifyContacts = $isNotifyContacts;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPersistentComment(): bool
    {
        return $this->isPersistentComment;
    }

    /**
     * @param bool $isPersistentComment
     * @return self
     */
    public function setPersistentComment(bool $isPersistentComment): self
    {
        $this->isPersistentComment = $isPersistentComment;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSticky(): bool
    {
        return $this->isSticky;
    }

    /**
     * @param bool $isSticky
     * @return self
     */
    public function setSticky(bool $isSticky): self
    {
        $this->isSticky = $isSticky;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getState(): ?int
    {
        return $this->state;
    }

    /**
     * @param int|null $state
     * @return self
     */
    public function setState(?int $state): self
    {
        $this->state = $state;
        return $this;
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
     * @return self
     */
    public function setType(?int $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return bool
     */
    public function isWithServices(): bool
    {
        return $this->withServices;
    }

    /**
     * @param bool $withServices
     * @return self
     */
    public function setWithServices(bool $withServices): self
    {
        $this->withServices = $withServices;
        return $this;
    }
}
