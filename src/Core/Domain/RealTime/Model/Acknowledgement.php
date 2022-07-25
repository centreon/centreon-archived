<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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
    final public const TYPE_HOST_ACKNOWLEDGEMENT = 0;
    final public const TYPE_SERVICE_ACKNOWLEDGEMENT = 1;

    public function __construct(
        private readonly int $id,
        private readonly int $hostId,
        private readonly int $serviceId,
        private readonly \DateTime $entryTime
    ) {
    }

    private ?int $instanceId = null;

    private ?int $authorId = null;

    private ?string $authorName = null;

    private ?string $comment = null;

    private ?\DateTime $deletionTime = null;

    private bool $isNotifyContacts = true;

    private bool $isPersistentComment = true;

    private bool $isSticky = true;

    private ?int $state = null;

    private ?int $type = null;

    private bool $withServices = false;

    public function getId(): int
    {
        return $this->id;
    }

    public function getInstanceId(): ?int
    {
        return $this->instanceId;
    }

    public function setInstanceId(int $instanceId): self
    {
        $this->instanceId = $instanceId;
        return $this;
    }

    public function getHostId(): int
    {
        return $this->hostId;
    }

    public function getServiceId(): int
    {
        return $this->serviceId;
    }

    public function getAuthorId(): ?int
    {
        return $this->authorId;
    }

    public function setAuthorId(?int $authorId): self
    {
        $this->authorId = $authorId;
        return $this;
    }

    public function getAuthorName(): ?string
    {
        return $this->authorName;
    }

    public function setAuthorName(?string $authorName): self
    {
        $this->authorName = $authorName;
        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    public function getDeletionTime(): ?\DateTime
    {
        return $this->deletionTime;
    }

    public function setDeletionTime(?\DateTime $deletionTime): self
    {
        $this->deletionTime = $deletionTime;
        return $this;
    }

    public function getEntryTime(): \DateTime
    {
        return $this->entryTime;
    }

    public function isNotifyContacts(): bool
    {
        return $this->isNotifyContacts;
    }

    public function setNotifyContacts(bool $isNotifyContacts): self
    {
        $this->isNotifyContacts = $isNotifyContacts;
        return $this;
    }

    public function isPersistentComment(): bool
    {
        return $this->isPersistentComment;
    }

    public function setPersistentComment(bool $isPersistentComment): self
    {
        $this->isPersistentComment = $isPersistentComment;
        return $this;
    }

    public function isSticky(): bool
    {
        return $this->isSticky;
    }

    public function setSticky(bool $isSticky): self
    {
        $this->isSticky = $isSticky;
        return $this;
    }

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(?int $state): self
    {
        $this->state = $state;
        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function isWithServices(): bool
    {
        return $this->withServices;
    }

    public function setWithServices(bool $withServices): self
    {
        $this->withServices = $withServices;
        return $this;
    }
}
