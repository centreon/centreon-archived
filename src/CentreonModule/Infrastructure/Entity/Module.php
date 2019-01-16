<?php
namespace CentreonModule\Infrastructure\Entity;

use CentreonModule\Infrastructure\Source\SourceDataInterface;

class Module implements SourceDataInterface
{

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $author;

    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $versionCurrent;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $keywords;

    /**
     * @var string
     */
    private $license;

    /**
     * @var bool
     */
    private $isInstalled = false;

    /**
     * @var bool
     */
    private $isUpdated = false;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id)
    {
        $this->id = $id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type)
    {
        $this->type = $type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function setAuthor(string $author)
    {
        $this->author = $author;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version)
    {
        $this->version = $version;
    }

    public function getVersionCurrent(): ?string
    {
        return $this->versionCurrent;
    }

    public function setVersionCurrent(string $versionCurrent)
    {
        $this->versionCurrent = $versionCurrent;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path)
    {
        $this->path = $path;
    }

    public function getKeywords(): string
    {
        return $this->keywords;
    }

    public function setKeywords(string $keywords)
    {
        $this->keywords = $keywords;
    }

    public function getLicense(): ?string
    {
        return $this->license;
    }

    public function setLicense(string $license = null)
    {
        $this->license = $license;
    }

    public function isInstalled(): bool
    {
        return $this->isInstalled;
    }

    public function setInstalled(bool $value)
    {
        $this->isInstalled = $value;
    }

    public function isUpdated(): bool
    {
        return $this->isUpdated;
    }

    public function setUpdated(bool $value)
    {
        $this->isUpdated = $value;
    }
}
