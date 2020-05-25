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

namespace Centreon\Domain\Monitoring;

/**
 * Class representing a record of a resource severity in the repository.
 *
 * @package Centreon\Domain\Monitoring
 */
class ResourceSeverity
{
    // Groups for serilizing
    public const SERIALIZER_GROUP_MAIN = 'resource_severity_main';

    /**
     * @var int|null
     */
    private $level;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @return int|null
     */
    public function getLevel(): ?int
    {
        return $this->level;
    }

    /**
     * @param int|null $level
     * @return \Centreon\Domain\Monitoring\ResourceSeverity
     */
    public function setLevel(?int $level): self
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return \Centreon\Domain\Monitoring\ResourceSeverity
     */
    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
