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

namespace Centreon\Domain\Media\Model;

use Centreon\Domain\Common\Assertion\Assertion;

/**
 * This class is designed to represent an image or icon
 *
 * @package Centreon\Domain\Media\Model
 */
class Image
{
    public const MAX_NAME_LENGTH = 255,
                 MAX_PATH_LENGTH = 255,
                 MAX_COMMENTS_LENGTH = 65535;

    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string|null
     */
    private $comment;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return Image
     */
    public function setId(?int $id): Image
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
     * @return Image
     * @throws \Assert\AssertionFailedException
     */
    public function setName(string $name): Image
    {
        Assertion::maxLength($name, self::MAX_NAME_LENGTH, 'Image::name');
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return Image
     * @throws \Assert\AssertionFailedException
     */
    public function setPath(string $path): Image
    {
        Assertion::maxLength($path, self::MAX_PATH_LENGTH, 'Image::path');
        $this->path = $path;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @param string|null $comment
     * @return Image
     * @throws \Assert\AssertionFailedException
     */
    public function setComment(?string $comment): Image
    {
        if ($comment !== null) {
            Assertion::maxLength($comment, self::MAX_COMMENTS_LENGTH, 'Image::comment');
        }
        $this->comment = $comment;
        return $this;
    }
}
