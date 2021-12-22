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

use Centreon\Domain\Common\Assertion\Assertion;

class Downtime
{
    public const DOWNTIME_YEAR_MAX = 2100;
    public const HOST_TYPE_DOWNTIME = 0;
    public const SERVICE_TYPE_DOWNTIME = 1;

    /**
     * @var \DateTime|null
     */
    private $entryTime;

    /**
     * @var int|null
     */
    private $authorId;

    /**
     * @var string|null
     */
    private $authorName;

    /**
     * @var bool
     */
    private $isCancelled = false;

    /**
     * @var string|null
     */
    private $comment;

    /**
     * @var \DateTime|null
     */
    private $deletionTime;

    /**
     * @var int|null
     */
    private $duration;

    /**
     * @var \DateTime|null
     */
    private $endTime;

    /**
     * @var int|null
     */
    private $engineDowntimeId;

    /**
     * @var bool
     */
    private $isFixed = false;

    /**
     * @var int|null
     */
    private $instanceId;

    /**
     * @var \DateTime|null
     */
    private $startTime;

    /**
     * @var \DateTime|null
     */
    private $actualStartTime;

    /**
     * @var \DateTime|null
     */
    private $actualEndTime;

    /**
     * @var bool
     */
    private $isStarted = false;

    /**
     * @var bool
     */
    private $withServices = false;

    /**
     * @var \DateTime
     */
    private $maxDate;

    /**
     * @param int $id
     * @param int $hostId
     * @param int $serviceId
     * @throws \Exception
     */
    public function __construct(
        private int $id,
        private int $hostId,
        private int $serviceId
    ) {
        $this->maxDate = (new \DateTime('', new \DateTimeZone("UTC")))
            ->setDate(self::DOWNTIME_YEAR_MAX, 1, 1)
            ->setTime(0, 0)
            ->modify('- 1 minute');
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
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
     * @return self
     */
    public function setEntryTime(?\DateTime $entryTime): self
    {
        if ($entryTime !== null) {
            Assertion::maxDate($entryTime, $this->maxDate, 'Downtime::entryTime');
        }
        $this->entryTime = $entryTime;
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
     * @return int
     */
    public function getHostId(): int
    {
        return $this->hostId;
    }

    /**
     * @return int
     */
    public function getServiceId(): int
    {
        return $this->serviceId;
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
     * @return self
     */
    public function setCancelled(bool $isCancelled): self
    {
        $this->isCancelled = $isCancelled;
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
     * @throws \Centreon\Domain\Common\Assertion\AssertionException
     * @throws \Exception
     * @return self
     */
    public function setDeletionTime(?\DateTime $deletionTime): self
    {
        if ($deletionTime !== null) {
            Assertion::maxDate($deletionTime, $this->maxDate, 'Downtime::deletionTime');
        }
        $this->deletionTime = $deletionTime;
        return $this;
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
     * @return self
     */
    public function setDuration(?int $duration): self
    {
        $this->duration = $duration;
        return $this;
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
     * @return self
     */
    public function setEndTime(?\DateTime $endTime): self
    {
        if ($endTime !== null) {
            Assertion::maxDate($endTime, $this->maxDate, 'Downtime::endTime');
        }
        $this->endTime = $endTime;
        return $this;
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
     * @return self
     */
    public function setFixed(bool $isFixed): self
    {
        $this->isFixed = $isFixed;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getEngineDowntimeId(): ?int
    {
        return $this->engineDowntimeId;
    }

    /**
     * @param int|null $engineDowntimeId
     * @return self
     */
    public function setEngineDowntimeId(?int $engineDowntimeId): self
    {
        $this->engineDowntimeId = $engineDowntimeId;
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
     * @param int|null $instanceId
     * @return self
     */
    public function setInstanceId(?int $instanceId): self
    {
        $this->instanceId = $instanceId;
        return $this;
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
     * @return self
     */
    public function setStartTime(?\DateTime $startTime): self
    {
        if ($startTime !== null) {
            Assertion::maxDate($startTime, $this->maxDate, 'Downtime::startTime');
        }
        $this->startTime = $startTime;
        return $this;
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
     * @return self
     */
    public function setActualStartTime(?\DateTime $actualStartTime): self
    {
        if ($actualStartTime !== null) {
            Assertion::maxDate($actualStartTime, $this->maxDate, 'Downtime::actualStartTime');
        }
        $this->actualStartTime = $actualStartTime;
        return $this;
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
     * @return self
     */
    public function setActualEndTime(?\DateTime $actualEndTime): self
    {
        if ($actualEndTime !== null) {
            Assertion::maxDate($actualEndTime, $this->maxDate, 'Downtime::actualEndTime');
        }
        $this->actualEndTime = $actualEndTime;
        return $this;
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
     * @return self
     */
    public function setStarted(bool $isStarted): self
    {
        $this->isStarted = $isStarted;
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
