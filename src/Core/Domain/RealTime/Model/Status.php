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

class Status
{
    public const STATUS_ORDER_HIGH = 1;
    public const STATUS_ORDER_MEDIUM = 2;
    public const STATUS_ORDER_LOW = 3;
    public const STATUS_ORDER_PENDING = 4;
    public const STATUS_ORDER_OK = 5;

    /**
     * Common statuses (unchecked resource status)
     */
    public const STATUS_NAME_PENDING = 'PENDING';
    public const STATUS_CODE_PENDING = 4;

    /**
     * Host statuses
     */
    public const HOST_STATUS_NAME_UP = 'UP';
    public const HOST_STATUS_NAME_DOWN = 'DOWN';
    public const HOST_STATUS_NAME_UNREACHABLE = 'UNREACHABLE';
    public const HOST_STATUS_CODE_UP = 0;
    public const HOST_STATUS_CODE_DOWN = 1;
    public const HOST_STATUS_CODE_UNREACHABLE = 2;

    /**
     * Service statuses
     */
    public const SERVICE_STATUS_NAME_OK = 'OK';
    public const SERVICE_STATUS_NAME_WARNING = 'WARNING';
    public const SERVICE_STATUS_NAME_CRITICAL = 'CRITICAL';
    public const SERVICE_STATUS_NAME_UNKNOWN = 'UNKNOWN';
    public const SERVICE_STATUS_CODE_OK = 0;
    public const SERVICE_STATUS_CODE_WARNING = 1;
    public const SERVICE_STATUS_CODE_CRITICAL = 2;
    public const SERVICE_STATUS_CODE_UNKNOWN = 3;

    /**
     * @var int|null
     */
    private $order;

    /**
     * @param string $name
     * @param int $code
     * @param int $type
     */
    public function __construct(
        private string $name,
        private int $code,
        private int $type
    ) {
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @param int|null $order
     * @return self
     */
    public function setOrder(?int $order): self
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getOrder(): ?int
    {
        return $this->order;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }
}
