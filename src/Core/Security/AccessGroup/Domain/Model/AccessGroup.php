<?php

/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
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

namespace Core\Security\AccessGroup\Domain\Model;

use Centreon\Domain\Common\Assertion\Assertion;

class AccessGroup
{
    /**
     * @var bool Indicates whether this access group is enabled or disabled
     */
    private bool $isActivate = false;

    /**
     * @var bool Indicates whether this access group has changed or not
     */
    private bool $hasChanged = false;

    /**
     * @param integer $id
     * @param string $name
     */
    public function __construct(private int $id, private string $name, private string $alias)
    {
        Assertion::notEmpty($name, 'AccessGroup::name');
        Assertion::notEmpty($alias, 'AccessGroup::alias');
    }

    /**
     * @return int
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
     * @return bool
     */
    public function isActivate(): bool
    {
        return $this->isActivate;
    }

    /**
     * @param bool $isActivate
     * @return AccessGroup
     */
    public function setActivate(bool $isActivate): self
    {
        $this->isActivate = $isActivate;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasChanged(): bool
    {
        return $this->hasChanged;
    }

    /**
     * @param bool $hasChanged
     * @return AccessGroup
     */
    public function setChanged(bool $hasChanged): self
    {
        $this->hasChanged = $hasChanged;
        return $this;
    }
}
