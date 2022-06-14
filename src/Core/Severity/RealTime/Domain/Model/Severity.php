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

namespace Core\Severity\RealTime\Domain\Model;

use Core\Domain\RealTime\Model\Icon;
use Centreon\Domain\Common\Assertion\Assertion;

class Severity
{
    public const MAX_NAME_LENGTH = 255;

    public const SERVICE_SEVERITY_TYPE_ID = 0,
                 HOST_SEVERITY_TYPE_ID = 1;

    public const TYPES_AS_STRING = [
        self::HOST_SEVERITY_TYPE_ID => 'host',
        self::SERVICE_SEVERITY_TYPE_ID => 'service'
    ];

    /**
     * @param integer $id
     * @param string $name
     * @param integer $level
     * @param integer $type
     * @param Icon $icon
     * @throws \Assert\AssertionFailedException
     */
    public function __construct(
        private int $id,
        private string $name,
        private int $level,
        private int $type,
        private Icon $icon
    ) {
        Assertion::maxLength($name, self::MAX_NAME_LENGTH, 'Severity::name');
        Assertion::notEmpty($name, 'Severity::name');
        Assertion::min($level, 0, 'Severity::level');
        Assertion::max($level, 100, 'Severity::level');
        Assertion::inArray(
            $type,
            [self::HOST_SEVERITY_TYPE_ID, self::SERVICE_SEVERITY_TYPE_ID],
            'Severity::type'
        );
    }

    /**
     * @return integer
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return integer
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @return Icon
     */
    public function getIcon(): Icon
    {
        return $this->icon;
    }

    /**
     * @return integer
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getTypeAsString(): string
    {
        return self::TYPES_AS_STRING[$this->type];
    }
}
