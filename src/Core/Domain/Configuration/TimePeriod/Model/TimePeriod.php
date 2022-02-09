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

namespace Core\Domain\Configuration\TimePeriod\Model;

use Centreon\Domain\Common\Assertion\Assertion;

class TimePeriod
{
    public const MAX_NAME_LENGTH = 200,
                 MAX_ALIAS_LENGTH = 200;

    /**
     * Directives
     * The weekday directives are comma-delimited lists of time ranges that are "valid" times
     * for a particular day of the week. Each time range is in the form of HH:MM-HH:MM,
     * where hours are specified on a 24 hour clock
     */

    /**
     * @var string|null
     */
    private $sundayDirectives;

    /**
     * @var string|null
     */
    private $mondayDirectives;

    /**
     * @var string|null
     */
    private $tuesdayDirectives;

    /**
     * @var string|null
     */
    private $wednesdayDirectives;

    /**
     * @var string|null
     */
    private $thursdayDirectives;

    /**
     * @var string|null
     */
    private $fridayDirectives;

    /**
     * @var string|null
     */
    private $saturdayDirectives;

    /**
     * @param integer $id
     * @param string $name
     * @param string $alias
     */
    public function __construct(
        private int $id,
        private string $name,
        private string $alias
    )
    {
        Assertion::maxLength($name, self::MAX_NAME_LENGTH, 'TimePeriod::name');
        Assertion::notEmpty($name, 'TimePeriod::name');
        Assertion::maxLength($alias, self::MAX_ALIAS_LENGTH, 'TimePeriod::alias');
        Assertion::notEmpty($alias, 'TimePeriod::alias');
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
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @param string|null $directives
     * @return self
     */
    public function setMondayDirectives(?string $directives): self
    {
        $this->mondayDirectives = $directives;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMondayDirectives(): ?string
    {
        return $this->mondayDirectives;
    }

    /**
     * @param string|null $directives
     * @return self
     */
    public function setTuesdayDirectives(?string $directives): self
    {
        $this->tuesdayDirectives = $directives;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTuesdayDirectives(): ?string
    {
        return $this->tuesdayDirectives;
    }

    /**
     * @param string|null $directives
     * @return self
     */
    public function setWednesdayDirectives(?string $directives): self
    {
        $this->wednesdayDirectives = $directives;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getWednesdayDirectives(): ?string
    {
        return $this->wednesdayDirectives;
    }

    /**
     * @param string|null $directives
     * @return self
     */
    public function setThursdayDirectives(?string $directives): self
    {
        $this->thursdayDirectives = $directives;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getThursdayDirectives(): ?string
    {
        return $this->thursdayDirectives;
    }

    /**
     * @param string|null $directives
     * @return self
     */
    public function setFridayDirectives(?string $directives): self
    {
        $this->fridayDirectives = $directives;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFridayDirectives(): ?string
    {
        return $this->fridayDirectives;
    }

    /**
     * @param string|null $directives
     * @return self
     */
    public function setSaturdayDirectives(?string $directives): self
    {
        $this->saturdayDirectives = $directives;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSaturdayDirectives(): ?string
    {
        return $this->saturdayDirectives;
    }

    /**
     * @param string|null $directives
     * @return self
     */
    public function setSundayDirectives(?string $directives): self
    {
        $this->sundayDirectives = $directives;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSundayDirectives(): ?string
    {
        return $this->sundayDirectives;
    }
}