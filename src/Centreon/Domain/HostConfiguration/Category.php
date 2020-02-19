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

namespace Centreon\Domain\HostConfiguration;

/**
 * This class is designed to represent a category of host.
 *
 * @package Centreon\Domain\HostConfiguration
 */
class Category
{
    /**
     * @var int Category id
     */
    private $id;

    /**
     * @var string Name of the category
     */
    private $name;
    /**
     * @var string Alias of the category
     */
    private $alias;

    /**
     * @var int Level of the category
     */
    private $level;

    /**
     * @var int Id of the icon linked to this category
     */
    private $iconId;

    /**
     * @var string
     */
    private $comments;

    /**
     * @var bool
     */
    private $isActivate = true;

    /**
     * @return int
     */
    public function getId (): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Category
     */
    public function setId (int $id): Category
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName (): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Category
     */
    public function setName (string $name): Category
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getAlias (): string
    {
        return $this->alias;
    }

    /**
     * @param string $alias
     * @return Category
     */
    public function setAlias (string $alias): Category
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * @return int
     */
    public function getLevel (): int
    {
        return $this->level;
    }

    /**
     * @param int $level
     * @return Category
     */
    public function setLevel (int $level): Category
    {
        $this->level = $level;
        return $this;
    }

    /**
     * @return int
     */
    public function getIconId (): int
    {
        return $this->iconId;
    }

    /**
     * @param int $iconId
     * @return Category
     */
    public function setIconId (int $iconId): Category
    {
        $this->iconId = $iconId;
        return $this;
    }

    /**
     * @return string
     */
    public function getComments (): string
    {
        return $this->comments;
    }

    /**
     * @param string $comments
     * @return Category
     */
    public function setComments (string $comments): Category
    {
        $this->comments = $comments;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActivate (): bool
    {
        return $this->isActivate;
    }

    /**
     * @param bool $isActivate
     * @return Category
     */
    public function setIsActivate (bool $isActivate): Category
    {
        $this->isActivate = $isActivate;
        return $this;
    }
}
