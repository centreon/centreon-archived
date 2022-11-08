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

namespace Core\HostGroup\Domain\Model;

class NewHostGroup
{
    /**
     * @param string $name
     * @param string|null $alias
     * @param int|null $icon
     * @param string|null $geo_coords
     */
    public function __construct(
        protected string $name,
        protected ?string $alias,
        protected ?int $icon,
        protected ?string $geo_coords,
    ) {
    }

    /**
     * @return ?string
     */
    public function getAlias(): ?string
    {
        return $this->alias;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int|null
     */
    public function getIcon(): ?int
    {
        return $this->icon;
    }

    /**
     * @return string|null
     */
    public function getGeoCoords(): ?string
    {
        return $this->geo_coords;
    }
}
