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

use Centreon\Domain\Common\Assertion\Assertion;

class Downtime
{
    final public const DOWNTIME_YEAR_MAX = 2100;
    final public const HOST_TYPE_DOWNTIME = 0;
    final public const SERVICE_TYPE_DOWNTIME = 1;

    private ?\DateTime $entryTime = null;

    private ?int $authorId = null;

    private ?string $authorName = null;

    private bool $isCancelled = false;

    private ?string $comment = null;

    private ?\DateTime $deletionTime = null;

    private ?int $duration = null;

    private ?\DateTime $endTime = null;

    private ?int $engineDowntimeId = null;

    private bool $isFixed = false;

    private ?int $instanceId = null;

    private ?\DateTime $startTime = null;

    private ?\DateTime $actualStartTime = null;

    private ?\DateTime $actualEndTime = null;

    private bool $isStarted = false;

    private bool $withServices = false;

    private readonly \DateTime $maxDate;

    /**
     * @param int $id
     * @param int $hostId
     * @param int $serviceId
     * @throws \Exception
     */
    public function __construct(
        private readonly int $id,
        private readonly int $hostId,
        private readonly int $serviceId
    ) {
        $this->maxDate = (new \DateTime('', new \DateTimeZone("UTC")))
            ->setDate(self::DOWNTIME_YEAR_MAX, 1, 1)
            ->setTime(0, 0)
            ->modify('- 1 minute');
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEntryTime(): ?\DateTime
    {
        return $this->entryTime;
    }

    /**
     * @throws \Centreon\Domain\Common\Assertion\AssertionException
     * @throws \Exception
     */
    public function setEntryTime(?\DateTime $entryTime): self
    {
        if ($entryTime !== null) {
            Assertion::maxDate($entryTime, $this->maxDate, 'Downtime::entryTime');
        }
        $this->entryTime = $entryTime;
        return $this;
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

    public function getHostId(): int
    {
        return $this->hostId;
    }

    public function getServiceId(): int
    {
        return $this->serviceId;
    }

    public function isCancelled(): bool
    {
        return $this->isCancelled;
    }

    public function setCancelled(bool $isCancelled): self
    {
        $this->isCancelled = $isCancelled;
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

    /**
     * @throws \Centreon\Domain\Common\Assertion\AssertionException
     * @throws \Exception
     */
    public function setDeletionTime(?\DateTime $deletionTime): self
    {
        if ($deletionTime !== null) {
            Assertion::maxDate($deletionTime, $this->maxDate, 'Downtime::deletionTime');
        }
        $this->deletionTime = $deletionTime;
        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): self
    {
        $this->duration = $duration;
        return $this;
    }

    public function getEndTime(): ?\DateTime
    {
        return $this->endTime;
    }

    /**
     * @throws \Centreon\Domain\Common\Assertion\AssertionException
     * @throws \Exception
     */
    public function setEndTime(?\DateTime $endTime): self
    {
        if ($endTime !== null) {
            Assertion::maxDate($endTime, $this->maxDate, 'Downtime::endTime');
        }
        $this->endTime = $endTime;
        return $this;
    }

    public function isFixed(): bool
    {
        return $this->isFixed;
    }

    public function setFixed(bool $isFixed): self
    {
        $this->isFixed = $isFixed;
        return $this;
    }

    public function getEngineDowntimeId(): ?int
    {
        return $this->engineDowntimeId;
    }

    public function setEngineDowntimeId(?int $engineDowntimeId): self
    {
        $this->engineDowntimeId = $engineDowntimeId;
        return $this;
    }

    public function getInstanceId(): ?int
    {
        return $this->instanceId;
    }

    public function setInstanceId(?int $instanceId): self
    {
        $this->instanceId = $instanceId;
        return $this;
    }

    public function getStartTime(): ?\DateTime
    {
        return $this->startTime;
    }

    /**
     * @throws \Centreon\Domain\Common\Assertion\AssertionException
     * @throws \Exception
     */
    public function setStartTime(?\DateTime $startTime): self
    {
        if ($startTime !== null) {
            Assertion::maxDate($startTime, $this->maxDate, 'Downtime::startTime');
        }
        $this->startTime = $startTime;
        return $this;
    }

    public function getActualStartTime(): ?\DateTime
    {
        return $this->actualStartTime;
    }

    /**
     * @throws \Centreon\Domain\Common\Assertion\AssertionException
     * @throws \Exception
     */
    public function setActualStartTime(?\DateTime $actualStartTime): self
    {
        if ($actualStartTime !== null) {
            Assertion::maxDate($actualStartTime, $this->maxDate, 'Downtime::actualStartTime');
        }
        $this->actualStartTime = $actualStartTime;
        return $this;
    }

    public function getActualEndTime(): ?\DateTime
    {
        return $this->actualEndTime;
    }

    /**
     * @throws \Centreon\Domain\Common\Assertion\AssertionException
     * @throws \Exception
     */
    public function setActualEndTime(?\DateTime $actualEndTime): self
    {
        if ($actualEndTime !== null) {
            Assertion::maxDate($actualEndTime, $this->maxDate, 'Downtime::actualEndTime');
        }
        $this->actualEndTime = $actualEndTime;
        return $this;
    }

    public function isStarted(): bool
    {
        return $this->isStarted;
    }

    public function setStarted(bool $isStarted): self
    {
        $this->isStarted = $isStarted;
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
