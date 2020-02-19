<?php

/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
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

namespace Centreon\Domain\Acknowledgement;

use Centreon\Domain\Service\EntityDescriptorMetadataInterface;

class Acknowledgement implements EntityDescriptorMetadataInterface
{
    // Groups for serilizing
    public const SERIALIZER_GROUP_MAIN = 'ack_main';
    public const SERIALIZER_GROUP_SERVICE = 'ack_service';

    // Types
    public const TYPE_HOST_ACKNOWLEDGEMENT = 0;
    public const TYPE_SERVICE_ACKNOWLEDGEMENT = 1;

    /**
     * @var int
     */
    private $id;

    /**
     * @var int|null
     */
    private $authorId;

    /**
     * @var string|null
     */
    private $comment;

    /**
     * @var \DateTime|null
     */
    private $deletionTime;

    /**
     * @var \DateTime
     */
    private $entryTime;

    /**
     * @var int Host id
     */
    private $hostId;

    /**
     * @var int|null Service id
     */
    private $serviceId;

    /**
     * @var int|null Poller Id
     */
    private $pollerId;

    /**
     * @var bool Indicates if the contacts must to be notify
     */
    private $isNotifyContacts;

    /**
     * @var bool Indicates this acknowledgement will be maintained in the case of a restart of the scheduler
     */
    private $isPersistentComment;

    /**
     * @var bool
     */
    private $isSticky;

    /**
     * @var int State of this acknowledgement
     */
    private $state;

    /**
     * @var int Type of this acknowledgement
     */
    private $type;

    /**
     * {@inheritdoc}
     */
    public static function loadEntityDescriptorMetadata(): array
    {
        return [
            'acknowledgement_id' => 'setId',
            'comment_data' => 'setComment',
            'instance_id' => 'setPollerId',
            'notify_contacts' => 'setNotifyContacts',
            'persistent_comment' => 'setPersistentComment',
            'sticky' => 'setSticky',
        ];
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Acknowledgement
     */
    public function setId(int $id): Acknowledgement
    {
        $this->id = $id;
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
     * @return Acknowledgement
     */
    public function setAuthorId(?int $authorId): Acknowledgement
    {
        $this->authorId = $authorId;
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
     * @return Acknowledgement
     */
    public function setComment(?string $comment): Acknowledgement
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
     * @return Acknowledgement
     */
    public function setDeletionTime(?\DateTime $deletionTime): Acknowledgement
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
     * @param \DateTime $entryTime
     * @return Acknowledgement
     */
    public function setEntryTime(\DateTime $entryTime): Acknowledgement
    {
        $this->entryTime = $entryTime;
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
     * @return Acknowledgement
     */
    public function setHostId(int $hostId): Acknowledgement
    {
        $this->hostId = $hostId;
        return $this;
    }

    /**
     * @return int
     */
    public function getServiceId(): ?int
    {
        return $this->serviceId;
    }

    /**
     * @param int $serviceId|null
     * @return Acknowledgement
     */
    public function setServiceId(?int $serviceId): Acknowledgement
    {
        $this->serviceId = $serviceId;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPollerId(): ?int
    {
        return $this->pollerId;
    }

    /**
     * @param int|null $pollerId
     * @return Acknowledgement
     */
    public function setPollerId(?int $pollerId): Acknowledgement
    {
        $this->pollerId = $pollerId;
        return $this;
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
     * @return Acknowledgement
     */
    public function setNotifyContacts(bool $isNotifyContacts): Acknowledgement
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
     * @return Acknowledgement
     */
    public function setPersistentComment(bool $isPersistentComment): Acknowledgement
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
     * @return Acknowledgement
     */
    public function setSticky(bool $isSticky): Acknowledgement
    {
        $this->isSticky = $isSticky;
        return $this;
    }

    /**
     * @return int
     */
    public function getState(): int
    {
        return $this->state;
    }

    /**
     * @param int $state
     * @return Acknowledgement
     */
    public function setState(int $state): Acknowledgement
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return Acknowledgement
     */
    public function setType(int $type): Acknowledgement
    {
        $this->type = $type;
        return $this;
    }
}
