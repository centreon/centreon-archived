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

namespace Centreon\Domain\Entity;

/**
 * Subclass of Image
 */
class ImageDir
{
    public const TABLE = 'view_img_dir';
    public const JOIN_TABLE = 'view_img_dir_relation';

    /**
     * @var int
     */
    public $dir_id;

    /**
     * @var string
     */
    public $dir_name;

    /**
     * @var string
     */
    public $dir_alias;

    /**
     * @var string
     */
    public $dir_comment;

    /**
     * @return int
     */
    public function getDirId(): int
    {
        return $this->dir_id;
    }

    /**
     * @param int $dirId
     */
    public function setDirId(int $dirId): void
    {
        $this->dir_id = $dirId;
    }

    /**
     * @return string|null
     */
    public function getDirName(): ?string
    {
        return $this->dir_name;
    }

    /**
     * @param string|null $dirName
     */
    public function setDirName(string $dirName = null): void
    {
        $this->dir_name = $dirName;
    }

    /**
     * @return string|null
     */
    public function getDirAlias(): ?string
    {
        return $this->dir_alias;
    }

    /**
     * @param string|null $dirAlias
     */
    public function setDirAlias(string $dirAlias = null): void
    {
        $this->dir_alias = $dirAlias;
    }

    /**
     * @return string|null
     */
    public function getDirComment(): ?string
    {
        return $this->dir_comment;
    }

    /**
     * @param string|null $dirComment
     */
    public function setDirComment(string $dirComment = null): void
    {
        $this->dir_comment = $dirComment;
    }
}
