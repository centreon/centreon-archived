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

    /**
     * @var int Resource id
     */
    private $id;

    /**
     * @var int|null Resource parent id
     */
    private $parentId;

    /**
     * @var \DateTime
     */
    private $checkTime;

    /**
     * @var bool
     */
    private $isForced = true;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Check
     */
    public function setId(int $id): Check
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    /**
     * @param int|null $parentId
     * @return Check
     */
    public function setParentId(?int $parentId): Check
    {
        $this->parentId = $parentId;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCheckTime(): \DateTime
    {
        return $this->checkTime;
    }

    /**
     * @param \DateTime $checkTime
     * @return Check
     */
    public function setCheckTime(\DateTime $checkTime): Check
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
}
