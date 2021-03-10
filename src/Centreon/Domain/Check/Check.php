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

namespace Centreon\Domain\Check;

class Check
{
    public const VALIDATION_GROUPS_HOST_CHECK = ['check_host'];
    public const VALIDATION_GROUPS_SERVICE_CHECK = ['check_service'];
    public const VALIDATION_GROUPS_META_SERVICE_CHECK = ['check_meta_service'];

    /**
     * @var int Resource id
     */
    private $resourceId;

    /**
     * @var int|null Parent resource id
     */
    private $parentResourceId;

    /**
     * @var \DateTime
     */
    private $checkTime;

    /**
     * @var bool
     */
    private $isForced = true;

    /**
     * @var bool Indicates if this downtime should be applied to linked services
     */
    private $withServices = false;

    /**
     * @return int
     */
    public function getResourceId(): int
    {
        return $this->resourceId;
    }

    /**
     * @param int $resourceId
     * @return Check
     */
    public function setResourceId(int $resourceId): Check
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
     * @return Check
     */
    public function setParentResourceId(?int $parentResourceId): Check
    {
        $this->parentResourceId = $parentResourceId;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getCheckTime(): ?\DateTime
    {
        return $this->checkTime;
    }

    /**
     * @param \DateTime|null $checkTime
     * @return Check
     */
    public function setCheckTime(?\DateTime $checkTime): Check
    {
        $this->checkTime = $checkTime;
        return $this;
    }

    /**
     * @return bool
     */
    public function isForced(): bool
    {
        return $this->isForced;
    }

    /**
     * @param bool $isForced
     * @return Check
     */
    public function setForced(bool $isForced): Check
    {
        $this->isForced = $isForced;
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
     */
    public function setWithServices(bool $withServices): void
    {
        $this->withServices = $withServices;
    }
}
