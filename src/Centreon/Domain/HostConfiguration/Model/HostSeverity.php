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
use Centreon\Domain\Media\Model\Image;

/**
 * This class is designed to represent a host severity.
 *
 * @package Centreon\Domain\HostConfiguration\Model
 */
class HostSeverity
{
    public const MAX_NAME_LENGTH = 200;
    public const MIN_NAME_LENGTH = 1;
    public const MAX_ALIAS_LENGTH = 200;
    public const MIN_ALIAS_LENGTH = 1;
    public const MAX_COMMENTS_LENGTH = 65535;
    public const MAX_LEVEL_NUMBER = 127;
    public const MIN_LEVEL_NUMBER = -128;

    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string Define a short name for this severity. It will be displayed with this name in the ACL configuration.
     */
    private $name;

    /**
     * @var string Longer description of this severity.
     */
    private $alias;

    /**
     * @var int Priority.
     */
    private $level;

    /**
     * @var Image Define the image that should be associated with this severity.
     */
    private $icon;

    /**
     * @var string|null Comments regarding this severity.
     */
    private $comments;

    /**
     * @var bool Indicates whether this host severity is enabled or not (TRUE by default)
     */
    private $isActivated = true;

    /**
     * @param string $name
     * @param string $alias
     * @param int $level
     * @param Image $icon
     * @throws \Assert\AssertionFailedException
     */
    public function __construct(string $name, string $alias, int $level, Image $icon)
    {
        $this->setName($name);
        $this->setAlias($alias);
        $this->setLevel($level);
        $this->setIcon($icon);
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
     * @return HostSeverity
     */
    public function setId(int $id): HostSeverity
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
     * @return HostSeverity
     * @throws \Assert\AssertionFailedException
     */
    public function setName(string $name): HostSeverity
    {
        Assertion::maxLength($name, self::MAX_NAME_LENGTH, 'HostSeverity::name');
        Assertion::minLength($name, self::MIN_NAME_LENGTH, 'HostSeverity::name');
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
     * @return HostSeverity
     * @throws \Assert\AssertionFailedException
     */
    public function setAlias(string $alias): HostSeverity
    {
        Assertion::maxLength($alias, self::MAX_ALIAS_LENGTH, 'HostSeverity::alias');
        Assertion::minLength($alias, self::MIN_ALIAS_LENGTH, 'HostSeverity::alias');
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
     * @return HostSeverity
     */
    public function setActivated(bool $isActivated): HostSeverity
    {
        $this->isActivated = $isActivated;
        return $this;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @param int $level
     * @return HostSeverity
     * @throws \Assert\AssertionFailedException
     */
    public function setLevel(int $level): HostSeverity
    {
        Assertion::min($level, self::MIN_LEVEL_NUMBER, 'HostSeverity::level');
        Assertion::max($level, self::MAX_LEVEL_NUMBER, 'HostSeverity::level');
        $this->level = $level;
        return $this;
    }

    /**
     * @return Image
     */
    public function getIcon(): Image
    {
        return $this->icon;
    }

    /**
     * @param Image $icon
     * @return $this
     */
    public function setIcon(Image $icon): HostSeverity
    {
        $this->icon = $icon;
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
     * @return HostSeverity
     * @throws \Assert\AssertionFailedException
     */
    public function setComments(?string $comments): HostSeverity
    {
        if ($comments !== null) {
            Assertion::maxLength($comments, self::MAX_COMMENTS_LENGTH, 'HostSeverity::comments');
        }
        $this->comments = $comments;
        return $this;
    }
}
