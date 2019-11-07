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

use Symfony\Component\Serializer\Annotation as Serializer;
use ReflectionClass;

class Image
{

    const TABLE = 'view_img';
    const MEDIA_DIR = 'img/media/';
    const SERIALIZER_GROUP_LIST = 'image-list';

    /**
     * @Serializer\SerializedName("id")
     * @Serializer\Groups({Image::SERIALIZER_GROUP_LIST})
     * @var int
     */
    private $img_id;

    /**
     * @Serializer\SerializedName("name")
     * @Serializer\Groups({Image::SERIALIZER_GROUP_LIST})
     * @var string
     */
    private $img_name;

    /**
     * @var string
     */
    private $img_path;

    /**
     * @var string
     */
    private $img_comment;

    /**
     * @var ImageDir
     */
    private $imageDir;

    /**
     * Image constructor.
     */
    public function __construct()
    {
        $this->imageDir = new ImageDir();
    }

    public function __set($prop, $val)
    {
        try {
            $ref = new ReflectionClass(ImageDir::class);
        } catch (\ReflectionException $e) {
        }

        $props = $ref->getProperties();
        $propArray = [];

        foreach ($props as $pro) {
            $propArray[] = $pro->getName();
        }

        if (in_array($prop, $propArray)) {
            $this->getImageDir()->{$prop} = $val;
        }
    }

    /**
     * @Serializer\Groups({Image::SERIALIZER_GROUP_LIST})
     * @Serializer\SerializedName("preview")
     * @return string
     */
    public function getPreview(): string
    {
        return static::MEDIA_DIR
            . $this->getImageDir()->getDirName()
            . '/' . $this->getImgPath();
    }

    /**
     * @return int
     */
    public function getImgId(): ?int
    {
        return $this->img_id;
    }

    /**
     * @param int $img_id
     */
    public function setImgId(int $id): void
    {
        $this->img_id = $id;
    }

    /**
     * @return string
     */
    public function getImgName(): ?string
    {
        return $this->img_name;
    }

    /**
     * @param string $img_name
     */
    public function setImgName(string $name = null): void
    {
        $this->img_name = $name;
    }

    /**
     * @return string
     */
    public function getImgPath(): ?string
    {
        return $this->img_path;
    }

    /**
     * @param string $img_path
     */
    public function setImgPath(string $path = null): void
    {
        $this->img_path = $path;
    }

    /**
     * @return string
     */
    public function getImgComment(): ?string
    {
        return $this->img_comment;
    }

    /**
     * @param string $img_comment
     */
    public function setImgComment(string $comment = null): void
    {
        $this->img_comment = $comment;
    }

    /**
     * @return ImageDir
     */
    public function getImageDir(): ImageDir
    {
        return $this->imageDir;
    }

    /**
     * @param ImageDir $imageDir
     */
    public function setImageDir(ImageDir $imageDir = null): void
    {
        $this->imageDir = $imageDir;
    }

    /**
     * begin setters for subclass
     */

    /**
     * @param int $dir_id
     */
    public function setDirId(int $dirId = null): void
    {
        $this->getImageDir()->setDirId($dirId);
    }

       /**
     * @param string $dir_name
     */
    public function setDirName(string $dirName = null): void
    {
        $this->getImageDir()->setDirName($dirName);
    }

    /**
     * @param string $dir_alias
     */
    public function setDirAlias(string $dirAlias = null): void
    {
        $this->getImageDir()->setDirAlias($dirAlias);
    }

    /**
     * @param string $dir_comment
     */
    public function setDirComment(string $dirComment = null): void
    {
        $this->getImageDir()->setDirComment($dirComment);
    }
}
