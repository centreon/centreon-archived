<?php
namespace Centreon\Domain\Entity;

class ViewImg
{
    final const TABLE = 'view_img';

    private ?int $imgId = null;

    private ?string $imgName = null;

    private ?string $imgPath = null;

    private ?string $imgComment = null;

    public function setImgId(int $imgId): void
    {
        $this->imgId = $imgId;
    }

    public function getImgId(): int
    {
        return $this->imgId;
    }

    public function setImgName(string $imgName): void
    {
        $this->imgName = $imgName;
    }

    public function getImgName(): ?string
    {
        return $this->imgName;
    }

    public function setImgPath(string $imgPath): void
    {
        $this->imgPath = $imgPath;
    }

    public function getImgPath(): ?string
    {
        return $this->imgPath;
    }

    public function setImgComment(string $imgComment): void
    {
        $this->imgComment = $imgComment;
    }

    public function getImgComment(): ?string
    {
        return $this->imgComment;
    }
}
