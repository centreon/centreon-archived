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

namespace Centreon\Domain\HostConfiguration\Model;

use Centreon\Domain\Common\Assertion\Assertion;

/**
 * This class is designed to represent a host category.
 *
 * @package Centreon\Domain\HostConfiguration\Model
 */
class HostCategory
{
    public const MAX_NAME_LENGTH = 200,
                 MIN_NAME_LENGTH = 1,
                 MAX_ALIAS_LENGTH = 200,
                 MIN_ALIAS_LENGTH = 1,
                 MAX_COMMENTS_LENGTH = 65535;

    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string Define a short name for this category. It will be displayed with this name in the ACL configuration.
     */
    private $name;

    /**
     * @var string Longer description of this category.
     */
    private $alias;

    /**
     * @var string|null Comments regarding this category.
     */
    private $comments;

    /**
     * @var bool Indicates whether this host category is enabled or not (TRUE by default)
     */
    private $isActivated = true;

    /**
     * @param string $name Name of the host category
     * @param string $alias Alias of the host category
     * @throws \Assert\AssertionFailedException
     */
    public function __construct(string $name, string $alias)
    {
        $this->setName($name);
        $this->setAlias($alias);
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return HostCategory
     */
    public function setId(int $id): HostCategory
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return HostCategory
     * @throws \Assert\AssertionFailedException
     */
    public function setName(string $name): HostCategory
    {
        Assertion::minLength($name, self::MIN_NAME_LENGTH, 'HostCategory::name');
        Assertion::maxLength($name, self::MAX_NAME_LENGTH, 'HostCategory::name');
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @param string $alias
     * @return HostCategory
     * @throws \Assert\AssertionFailedException
     */
    public function setAlias(string $alias): HostCategory
    {
        Assertion::minLength($alias, self::MIN_ALIAS_LENGTH, 'HostCategory::alias');
        Assertion::maxLength($alias, self::MAX_ALIAS_LENGTH, 'HostCategory::alias');
        $this->alias = $alias;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActivated(): bool
    {
        return $this->isActivated;
    }

    /**
     * @param bool $isActivated
     * @return HostCategory
     */
    public function setActivated(bool $isActivated): HostCategory
    {
        $this->isActivated = $isActivated;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getComments(): ?string
    {
        return $this->comments;
    }

    /**
     * @param string|null $comments
     * @return HostCategory
     * @throws \Assert\AssertionFailedException
     */
    public function setComments(?string $comments): HostCategory
    {
        if ($comments !== null) {
            Assertion::maxLength($comments, self::MAX_COMMENTS_LENGTH, 'HostCategory::comments');
        }
        $this->comments = $comments;
        return $this;
    }
}
