<?php
namespace Centreon\Domain\Entity;

class CfgResource
{
    /**
     * @var int
     */
    private $resourceId;

    /**
     * @var string
     */
    private $resourceName;

    /**
     * @var string
     */
    private $resourceLine;

    /**
     * @var string
     */
    private $resourceComment;

    /**
     * @var bool
     */
    private $resourceActivate;

    public function setResourceId(int $resourceId): void
    {
        $this->resourceId = $resourceId;
    }

    public function getResourceId(): int
    {
        return $this->resourceId;
    }

    public function setResourceName(string $resourceName): void
    {
        $this->resourceName = $resourceName;
    }

    public function getResourceName(): string
    {
        return $this->resourceName;
    }

    public function setResourceLine(string $resourceLine): void
    {
        $this->resourceLine = $resourceLine;
    }

    public function getResourceLine(): string
    {
        return $this->resourceLine;
    }

    public function setResourceComment(string $resourceComment): void
    {
        $this->resourceComment = $resourceComment;
    }

    public function getResourceComment(): string
    {
        return $this->resourceComment;
    }

    public function setResourceActivate(bool $resourceActivate): void
    {
        $this->resourceActivate = (bool) $resourceActivate;
    }

    public function getResourceActivate(): bool
    {
        return (bool) $this->resourceActivate;
    }
}
