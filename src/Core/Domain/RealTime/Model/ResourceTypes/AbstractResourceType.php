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

namespace Core\Domain\RealTime\Model\ResourceTypes;

use Core\Domain\RealTime\ResourceTypeInterface;

abstract class AbstractResourceType implements ResourceTypeInterface
{
    protected string $name = '';

    protected int $id = -1;

    /**
     * @inheritDoc
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function isValidForTypeName(string $typeName): bool
    {
        return $typeName === $this->name;
    }

    /**
     * @inheritDoc
     */
    public function isValidForTypeId(int $typeId): bool
    {
        return $typeId === $this->id;
    }

    /**
     * @inheritDoc
     */
    public function hasInternalId(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function hasParent(): bool
    {
        return false;
    }
}
