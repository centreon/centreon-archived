<?php

namespace Centreon\Domain\Entity;

use ReflectionClass;

class Image
{

    const TABLE = 'view_img';

    /**
     * @var int
     */
    private $img_id;

    /**
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
        foreach ($props as $pro){
            $propArray[] = $pro->getName();
        }
        if (in_array($prop, $propArray)){
            $this->getImageDir()->{$prop} = $val;
        }
    }

    /**
     * @return int
     */
    public function getImgId(): int
    {
        return $this->img_id;
    }

    /**
     * @param int $img_id
     */
    public function setImgId(int $img_id): void
    {
        $this->img_id = $img_id;
    }

    /**
     * @return string
     */
    public function getImgName(): string
    {
        return $this->img_name;
    }

    /**
     * @param string $img_name
     */
    public function setImgName(string $img_name): void
    {
        $this->img_name = $img_name;
    }

    /**
     * @return string
     */
    public function getImgPath(): string
    {
        return $this->img_path;
    }

    /**
     * @param string $img_path
     */
    public function setImgPath(string $img_path): void
    {
        $this->img_path = $img_path;
    }

    /**
     * @return string
     */
    public function getImgComment(): string
    {
        return $this->img_comment;
    }

    /**
     * @param string $img_comment
     */
    public function setImgComment(string $img_comment): void
    {
        $this->img_comment = $img_comment;
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
    public function setImageDir(ImageDir $imageDir): void
    {
        $this->imageDir = $imageDir;
    }

    /**
     * begin setters for subclass
     */

    /**
     * @param int $dir_id
     */
    public function setDirId(int $dir_id): void
    {
        $this->getImageDir()->setDirId($dir_id);
    }

       /**
     * @param string $dir_name
     */
    public function setDirName(string $dir_name): void
    {
        $this->getImageDir()->setDirName($dir_name);
    }

    /**
     * @param string $dir_alias
     */
    public function setDirAlias(string $dir_alias): void
    {
        $this->getImageDir()->setDirAlias($dir_alias);
    }

    /**
     * @param string $dir_comment
     */
    public function setDirComment(string $dir_comment): void
    {
        $this->getImageDir()->setDirComment($dir_comment);
    }
}