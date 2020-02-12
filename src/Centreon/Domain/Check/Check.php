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

use JMS\Serializer\Annotation as Serializer;

class Check
{
    public const TYPE_HOST_CHECK = 0;
    public const TYPE_SERVICE_CHECK = 1;

    public const VALIDATION_GROUPS_HOST_CHECK = ['check_host'];
    public const VALIDATION_GROUPS_SERVICE_CHECK = ['check_service'];

    /**
     * @Serializer\Groups({"check_main"})
     * @Serializer\Type("integer")
     * @var int Host id
     */
    private $hostId;

    /**
     * @Serializer\Groups({"check_service"})
     * @Serializer\Type("integer")
     * @var int|null Service id
     */
    private $serviceId;

    /**
     * @Serializer\Groups({"check_main"})
     * @Serializer\Type("DateTime<'Y-m-d\TH:i:sP'>")
     * @var \DateTime
     */
    private $checkTime;

    /**
     * @Serializer\Groups({"check_main"})
     * @Serializer\Type("boolean")
     * @var bool
     */
    private $isForced = true;

    /**
     * @return int
     */
    public function getHostId(): int
    {
        return $this->hostId;
    }

    /**
     * @param int $hostId
     * @return Check
     */
    public function setHostId(int $hostId): Check
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
     * @return Check
     */
    public function setServiceId(?int $serviceId): Check
    {
        $this->serviceId = $serviceId;
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
