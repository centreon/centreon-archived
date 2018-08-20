<?php
namespace Centreon\Domain\Entity;

class ViewImg
{
    const TABLE = 'view_img';

    /**
     * @var int
     */
    private $imgId;

    /**
     * @var string
     */
    private $imgName;

    /**
     * @var string
     */
    private $imgPath;

    /**
     * @var string
     */
    private $imgComment;

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
