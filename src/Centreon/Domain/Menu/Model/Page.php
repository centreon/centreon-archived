<?php

namespace Centreon\Domain\Menu\Model;

class Page
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $url;

    /**
     * @var string|null
     */
    private $urlOptions;

    /**
     * @var int|null
     */
    private $pageNumber;

    /**
     * @var bool
     */
    private $isReact = false;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return self
     */
    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string|null $url
     * @return self
     */
    public function setUrl(?string $url): self
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUrlOptions(): ?string
    {
        return $this->urlOptions;
    }

    /**
     * @param string|null $urlOptions
     * @return self
     */
    public function setUrlOptions(?string $urlOptions): self
    {
        $this->urlOptions = $urlOptions;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPageNumber(): ?int
    {
        return $this->pageNumber;
    }

    /**
     * @param int|null $pageNumber
     * @return self
     */
    public function setPageNumber(?int $pageNumber): self
    {
        $this->pageNumber = $pageNumber;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isReact(): bool
    {
        return $this->isReact;
    }

    /**
     * @param boolean $isReact
     * @return self
     */
    public function setIsReact(bool $isReact): self
    {
        $this->isReact = $isReact;
        return $this;
    }
}
