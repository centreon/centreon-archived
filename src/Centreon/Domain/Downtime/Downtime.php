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

namespace Centreon\Domain\Downtime;

use Centreon\Domain\Common\Assertion\Assertion;
use Centreon\Domain\Service\EntityDescriptorMetadataInterface;

/**
 * This class is designed to represent the downtime of a resource.
 *
 * @package Centreon\Domain\Downtime
 */
class Downtime implements EntityDescriptorMetadataInterface
{
    public const DOWNTIME_YEAR_MAX = 2038;
    // Groups for serialization
    public const SERIALIZER_GROUPS_MAIN = ['Default', 'downtime_host'];
    public const SERIALIZER_GROUPS_SERVICE = ['Default', 'downtime_service'];
    public const SERIALIZER_GROUPS_RESOURCE_DOWNTIME = ['resource_dt'];

    //Groups for validation
    public const VALIDATION_GROUP_DT_RESOURCE = ['resource_dt'];

    // Types
    public const TYPE_HOST_DOWNTIME = 0;
    public const TYPE_SERVICE_DOWNTIME = 1;

    /**
     * @var int|null Unique id
     */
    private $id;

    /**
     * @var \DateTime|null Creation date
     */
    private $entryTime;

    /**
     * @var int|null Author id who sent this downtime
     */
    private $authorId;

    /**
     * @var string|null Author name who sent this downtime
     */
    private $authorName;

    /**
     * @var int|null Host id linked to this downtime
     */
    private $hostId;

    /**
     * @var int|null Service id linked to this downtime
     */
    private $serviceId;

    /**
     * @var int Resource id
     */
    private $resourceId;

    /**
     * @var int|null Parent resource id
     */
    private $parentResourceId;

    /**
     * @var bool Indicates if this downtime have been cancelled
     */
    private $isCancelled;

    /**
     * @var string|null Comments
     */
    private $comment;

    /**
     * @var \DateTime|null Date when this downtime have been deleted
     */
    private $deletionTime;

    /**
     * @var int|null Duration of the downtime corresponding to endTime - startTime (in seconds)
     */
    private $duration;

    /**
     * @var \DateTime|null End date of the downtime
     */
    private $endTime;

    /**
     * @var int|null (used to cancel a downtime)
     */
    private $internalId;

    /**
     * @var bool Indicates either the downtime is fixed or not
     */
    private $isFixed;

    /**
     * @var int|null Poller id
     */
    private $pollerId;

    /**
     * @var \DateTime|null Start date of the downtime
     */
    private $startTime;

    /**
     * @var \DateTime|null Actual start date of the downtime
     */
    private $actualStartTime;

    /**
     * @var \DateTime|null Actual end date of the downtime
     */
    private $actualEndTime;

    /**
     * @var bool Indicates if this downtime have started
     */
    private $isStarted;

    /**
     * @var bool Indicates if this downtime should be applied to linked services
     */
    private $withServices = false;
    /**
     * @var \DateTime
     */
    private $maxDate;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->maxDate = (new \DateTime('', new \DateTimeZone("UTC")))
            ->setDate(self::DOWNTIME_YEAR_MAX, 1, 1)
            ->setTime(0, 0)
            ->modify('- 1 minute');
    }

    /**
     * {@inheritdoc}
     */
    public static function loadEntityDescriptorMetadata(): array
    {
        return [
            'author' => 'setAuthorName',
            'downtime_id' => 'setId',
            'cancelled' => 'setCancelled',
            'comment_data' => 'setComment',
            'fixed' => 'setFixed',
            'instance_id' => 'setPollerId',
            'started' => 'setStarted',
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
     * @throws \Centreon\Domain\Common\Assertion\AssertionException
     * @throws \Exception
     */
    public function setEntryTime(?\DateTime $entryTime): void
    {
        if ($entryTime !== null) {
            Assertion::maxDate($entryTime, $this->maxDate, 'Downtime::entryTime');
        }
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
     * @return string|null
     */
    public function getAuthorName(): ?string
    {
        return $this->authorName;
    }

    /**
     * @param string|null $authorName
     */
    public function setAuthorName(?string $authorName): void
    {
        $this->authorName = $authorName;
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
     * @return int
     */
    public function getResourceId(): int
    {
        return $this->resourceId;
    }

    /**
     * @param int $resourceId
     * @return Downtime
     */
    public function setResourceId(int $resourceId): Downtime
    {
        $this->resourceId = $resourceId;
        return $this;
    }

    /**
     * @return int
     */
    public function getParentResourceId(): ?int
    {
        return $this->parentResourceId;
    }

    /**
     * @param int|null $parentResourceId
     * @return Downtime
     */
    public function setParentResourceId(?int $parentResourceId): Downtime
    {
        $this->parentResourceId = $parentResourceId;
        return $this;
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
     * @throws \Centreon\Domain\Common\Assertion\AssertionException
     * @throws \Exception
     */
    public function setDeletionTime(?\DateTime $deletionTime): void
    {
        if ($deletionTime !== null) {
            Assertion::maxDate($deletionTime, $this->maxDate, 'Downtime::deletionTime');
        }
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
     * @throws \Centreon\Domain\Common\Assertion\AssertionException
     * @throws \Exception
     */
    public function setEndTime(?\DateTime $endTime): void
    {
        if ($endTime !== null) {
            Assertion::maxDate($endTime, $this->maxDate, 'Downtime::endTime');
        }
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
     * @throws \Centreon\Domain\Common\Assertion\AssertionException
     * @throws \Exception
     */
    public function setStartTime(?\DateTime $startTime): void
    {
        if ($startTime !== null) {
            Assertion::maxDate($startTime, $this->maxDate, 'Downtime::startTime');
        }
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
     * @throws \Centreon\Domain\Common\Assertion\AssertionException
     * @throws \Exception
     */
    public function setActualStartTime(?\DateTime $actualStartTime): void
    {
        if ($actualStartTime !== null) {
            Assertion::maxDate($actualStartTime, $this->maxDate, 'Downtime::actualStartTime');
        }
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
     * @throws \Centreon\Domain\Common\Assertion\AssertionException
     * @throws \Exception
     */
    public function setActualEndTime(?\DateTime $actualEndTime): void
    {
        if ($actualEndTime !== null) {
            Assertion::maxDate($actualEndTime, $this->maxDate, 'Downtime::actualEndTime');
        }
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

    /**
     * @return bool
     */
    public function isWithServices(): bool
    {
        return $this->withServices;
    }

    /**
     * @param bool $withServices
     */
    public function setWithServices(bool $withServices): void
    {
        $this->withServices = $withServices;
    }
}
