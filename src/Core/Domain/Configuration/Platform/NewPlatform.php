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

namespace Core\Domain\Configuration\Platform;

use Core\Domain\Configuration\Platform\AbstractPlatform;

class NewPlatform extends AbstractPlatform
{
    private AbstractPlatform $parent;

    /**
     * @param string $address
     * @param string|null $hostname
     * @param string $name
     * @param string $type
     */
    public function __construct(
        private string $address,
        private ?string $hostname,
        private string $name,
        private string $type,
    ){  
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @return string|null
     */
    public function getHostname(): ?string
    {
        return $this->hostname;
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
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return Platform|null
     */
    public function setParent(?AbstractPlatform $parent): self
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @return Platform|null
     */
    public function getParent(): ?AbstractPlatform
    {
        return $this->parent;
    }
}