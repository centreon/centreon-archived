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

namespace Centreon\Domain\Option;

/**
 * Class Option
 *
 * @package Centreon\Domain\Option
 */
class Option
{
    /**
     * @var string Option name
     */
    private $name;

    /**
     * @var string|null Option value
     */
    private $value;

    /**
     * @return string
     * @see Option::$name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Option
     * @see Option::$name
     */
    public function setName(string $name): Option
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     * @see Option::$value
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string|null $value
     * @return Option
     * @see Option::$value
     */
    public function setValue(?string $value): Option
    {
        $this->value = $value;
        return $this;
    }
}
