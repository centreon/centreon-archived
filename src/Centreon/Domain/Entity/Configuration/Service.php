<?php

namespace Centreon\Domain\Entity\Configuration;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class ServiceEntity
 * @package App\Entity
 */
class Service
{
    public const STATUS_OK       = 0;
    public const STATUS_WARNING  = 1;
    public const STATUS_CRITICAL = 2;
    public const STATUS_UNKNOWN  = 3;

    /**
     * @var int Id of the service
     * @Serializer\Groups({"realtime_services"})
     */
    private $id;
    /**
     * @var string Description of service
     * @Serializer\Groups({"realtime_services"})
     */
    private $description;
    /**
     * @var string Name of service
     * @Serializer\Groups({"realtime_services"})
     */
    private $name;
    /**
     * @var int Status of service
     * @Serializer\Groups({"realtime_services"})
     */
    private $state;
    /**
     * @var bool Indicates whether this service is enabled or disabled
     * @Serializer\Groups({"realtime_services"})
     */
    private $isActive;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Service
     */
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Service
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return int
     */
    public function getState(): int
    {
        return $this->state;
    }

    /**
     * @param int $state
     * @return Service
     */
    public function setState(int $state): self
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     * @return Service
     */
    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Service
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }
}
