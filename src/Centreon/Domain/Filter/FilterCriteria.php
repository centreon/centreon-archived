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

namespace Centreon\Domain\Filter;

/**
 * This class is designed to represent a filter criteria entity.
 *
 * @package Centreon\Domain\Filter
 */
class FilterCriteria
{
    /**
     * @var string|null Name of the criteria
     */
    private $name;

    /**
     * @var string|null Type of the criteria
     */
    private $type;

    /**
     * @var string|array<mixed>|boolean|null Value of the criteria
     */
    private $value;

    /**
     * @var string|null Object type used in the criteria
     */
    private $objectType;

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return FilterCriteria
     */
    public function setName(?string $name): FilterCriteria
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     * @return FilterCriteria
     */
    public function setType(?string $type): FilterCriteria
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string|array<mixed>|boolean|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string|array<mixed>|boolean|null $value
     * @return FilterCriteria
     */
    public function setValue($value): FilterCriteria
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getObjectType(): ?string
    {
        return $this->objectType;
    }

    /**
     * @param string|null $objectType
     * @return FilterCriteria
     */
    public function setObjectType(?string $objectType): FilterCriteria
    {
        $this->objectType = $objectType;
        return $this;
    }
}
