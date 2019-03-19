<?php

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
     * @param int $dir_id
     */
    public function setDirId(int $dir_id): void
    {
        $this->dir_id = $dir_id;
    }

    /**
     * @return string
     */
    public function getDirName(): string
    {
        return $this->dir_name;
    }

    /**
     * @param string $dir_name
     */
    public function setDirName(string $dir_name): void
    {
        $this->dir_name = $dir_name;
    }

    /**
     * @return string
     */
    public function getDirAlias(): string
    {
        return $this->dir_alias;
    }

    /**
     * @param string $dir_alias
     */
    public function setDirAlias(string $dir_alias): void
    {
        $this->dir_alias = $dir_alias;
    }

    /**
     * @return string
     */
    public function getDirComment(): string
    {
        return $this->dir_comment;
    }

    /**
     * @param string $dir_comment
     */
    public function setDirComment(string $dir_comment): void
    {
        $this->dir_comment = $dir_comment;
    }
}