<?php
namespace Centreon\Domain\Entity;

class ViewImgDir
{
    final const TABLE = 'view_img_dir';

    private ?int $dirId = null;

    private ?string $dirName = null;

    private ?string $dirAlias = null;

    private ?string $dirComment = null;

    public function setDirId(int $dirId): void
    {
        $this->dirId = $dirId;
    }

    public function getDirId(): int
    {
        return $this->dirId;
    }

    public function setDirName(string $dirName): void
    {
        $this->dirName = $dirName;
    }

    public function getDirName(): ?string
    {
        return $this->dirName;
    }

    public function setDirAlias(string $dirAlias): void
    {
        $this->dirAlias = $dirAlias;
    }

    public function getDirAlias(): ?string
    {
        return $this->dirAlias;
    }

    public function setDirComment(string $dirComment = null): void
    {
        $this->dirComment = $dirComment;
    }

    public function getDirComment(): ?string
    {
        return $this->dirComment;
    }
}
