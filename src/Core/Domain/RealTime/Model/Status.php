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

abstract class Status
{
    public const STATUS_ORDER_HIGH = 1,
                 STATUS_ORDER_MEDIUM = 2,
                 STATUS_ORDER_LOW = 3,
                 STATUS_ORDER_PENDING = 4,
                 STATUS_ORDER_OK = 5;

    public const STATUS_NAME_PENDING = 'PENDING',
                 STATUS_CODE_PENDING = 4;

    /**
     * @var int|null
     */
    protected $order;

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
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int|null $order
     * @return static
     */
    public function setOrder(?int $order): static
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
}
