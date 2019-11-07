<?php
/*
 * Copyright 2005-2019 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 *
 */

namespace Centreon\Domain\Entity;

/**
 * subclass of Image
 */
class ImageDir
{
    const TABLE = 'view_img_dir';
    const JOIN_TABLE = 'view_img_dir_relation';

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
     * @return string
     */
    public function getDirName(): ?string
    {
        return $this->dir_name;
    }

    /**
     * @param string $dirName
     */
    public function setDirName(string $dirName = null): void
    {
        $this->dir_name = $dirName;
    }

    /**
     * @return string
     */
    public function getDirAlias(): ?string
    {
        return $this->dir_alias;
    }

    /**
     * @param string $dirAlias
     */
    public function setDirAlias(string $dirAlias = null): void
    {
        $this->dir_alias = $dirAlias;
    }

    /**
     * @return string
     */
    public function getDirComment(): ?string
    {
        return $this->dir_comment;
    }

    /**
     * @param string $dirComment
     */
    public function setDirComment(string $dirComment = null): void
    {
        $this->dir_comment = $dirComment;
    }
}
