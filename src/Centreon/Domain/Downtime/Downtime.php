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

namespace Centreon\Domain\Downtime;

use JMS\Serializer\Annotation as Serializer;
use Centreon\Domain\Annotation\EntityDescriptor as Desc;

/**
 * Class Downtime
 * @package Centreon\Domain\Downtime
 */
class Downtime
{
    public const TYPE_HOST_DOWNTIME = 0;
    public const TYPE_SERVICE_DOWNTIME = 1;

    /**
     * @Serializer\Groups({"downtime_main"})
     * @Desc(column="downtime_id", modifier="setId")
     * @Serializer\Type("integer")
     * @var int|null Unique id
     */
    private $id;

    /**
     * @Serializer\Groups({"downtime_main"})
     * @var \DateTime|null Creation date
     */
    private $entryTime;

    /**
     * @Serializer\Groups({"downtime_main"})
     * @Serializer\Type("integer")
     * @var int|null Author id who sent this downtime
     */
    private $authorId;

    /**
     * @Serializer\Groups({"downtime_main"})
     * @Serializer\Type("integer")
     * @var int|null Host id linked to this downtime
     */
    private $hostId;

    /**
     * @Serializer\Groups({"downtime_service"})
     * @Serializer\Type("integer")
     * @var int|null Service id linked to this downtime
     */
    private $serviceId;

    /**
     * @Serializer\Groups({"downtime_main"})
     * @Desc(column="cancelled", modifier="setCancelled")
     * @Serializer\Type("boolean")
     * @var bool Indicates if this downtime have been cancelled
     */
    private $isCancelled;

    /**
     * @Serializer\Groups({"downtime_main"})
     * @Desc(column="comment_data", modifier="setComment")
     * @Serializer\Type("string")
     * @var string|null Comments
     */
    private $comment;

    /**
     * @Serializer\Groups({"downtime_main"})
     * @var \DateTime|null Date when this downtime have been deleted
     */
    private $deletionTime;

    /**
     * @Serializer\Groups({"downtime_main"})
     * @Serializer\Type("integer")
     * @var int|null Duration of the downtime corresponding to endTime - startTime (in seconds)
     */
    private $duration;

    /**
     * @Serializer\Groups({"downtime_main"})
     * @Serializer\Type("DateTime<'Y-m-d\TH:i:sP'>")
     * @var \DateTime|null End date of the downtime
     */
    private $endTime;

    /**
     * @Serializer\Type("integer")
     * @var int|null (used to cancel a downtime)
     */
    private $internalId;

    /**
     * @Serializer\Groups({"downtime_main"})
     * @Desc(column="fixed", modifier="setFixed")
     * @Serializer\Type("boolean")
     * @var boolean Indicates either the downtime is fixed or not
     */
    private $isFixed;

    /**
     * @Serializer\Groups({"downtime_main"})
     * @Desc(column="instance_id", modifier="setPollerId")
     * @Serializer\Type("integer")
     * @var int|null Poller id
     */
    private $pollerId;

    /**
     * @Serializer\Groups({"downtime_main"})
     * @Serializer\Type("DateTime<'Y-m-d\TH:i:sP'>")
     * @var \DateTime|null Start date of the downtime
     */
    private $startTime;

    /**
     * @Serializer\Groups({"downtime_main"})
     * @var \DateTime|null Actual start date of the downtime
     */
    private $actualStartTime;

    /**
     * @Serializer\Groups({"downtime_main"})
     * @var \DateTime|null Actual end date of the downtime
     */
    private $actualEndTime;

    /**
     * @Serializer\Groups({"downtime_main"})
     * @Desc(column="started", modifier="setStarted")
     * @Serializer\Type("boolean")
     * @var bool Indicates if this downtime have started
     */
    private $isStarted;

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
    public function getAuthorId(): ?int
    {
        return $this->authorId;
    }

    /**
     * @param int|null $authorId
     */
    public function setAuthorId(?int $authorId): void
    {
        $this->authorId = $authorId;
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
     * @return bool
     */
    public function isCancelled(): bool
    {
        return $this->isCancelled;
    }

    /**
     * @param bool $isCancelled
     */
    public function setCancelled(bool $isCancelled): void
    {
        $this->isCancelled = $isCancelled;
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
     */
    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
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
    public function getDuration(): ?int
    {
        return $this->duration;
    }

    /**
     * @param int|null $duration
     */
    public function setDuration(?int $duration): void
    {
        $this->duration = $duration;
    }

    /**
     * @return \DateTime|null
     */
    public function getEndTime(): ?\DateTime
    {
        return $this->endTime;
    }

    /**
     * @param \DateTime|null $endTime
     */
    public function setEndTime(?\DateTime $endTime): void
    {
        $this->endTime = $endTime;
    }

    /**
     * @return bool
     */
    public function isFixed(): bool
    {
        return $this->isFixed;
    }

    /**
     * @param bool $isFixed
     */
    public function setFixed(bool $isFixed): void
    {
        $this->isFixed = $isFixed;
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
    public function getPollerId(): ?int
    {
        return $this->pollerId;
    }

    /**
     * @param int|null $pollerId
     */
    public function setPollerId(?int $pollerId): void
    {
        $this->pollerId = $pollerId;
    }

    /**
     * @return \DateTime|null
     */
    public function getStartTime(): ?\DateTime
    {
        return $this->startTime;
    }

    /**
     * @param \DateTime|null $startTime
     */
    public function setStartTime(?\DateTime $startTime): void
    {
        $this->startTime = $startTime;
    }

    /**
     * @return \DateTime|null
     */
    public function getActualStartTime(): ?\DateTime
    {
        return $this->actualStartTime;
    }

    /**
     * @param \DateTime|null $actualStartTime
     */
    public function setActualStartTime(?\DateTime $actualStartTime): void
    {
        $this->actualStartTime = $actualStartTime;
    }

    /**
     * @return \DateTime|null
     */
    public function getActualEndTime(): ?\DateTime
    {
        return $this->actualEndTime;
    }

    /**
     * @param \DateTime|null $actualEndTime
     */
    public function setActualEndTime(?\DateTime $actualEndTime): void
    {
        $this->actualEndTime = $actualEndTime;
    }

    /**
     * @return bool
     */
    public function isStarted(): bool
    {
        return $this->isStarted;
    }

    /**
     * @param bool $isStarted
     */
    public function setStarted(bool $isStarted): void
    {
        $this->isStarted = $isStarted;
    }
}
