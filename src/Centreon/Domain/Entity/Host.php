<?php

namespace Centreon\Domain\Entity;

use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Since;
use JMS\Serializer\Annotation\Until;

/**
 * Class HostEntity
 * @package Centreon\Domain\Entity
 */
class Host
{
    public const STATUS_UP          = 0;
    public const STATUS_DOWN        = 1;
    public const STATUS_UNREACHABLE = 2;

    /**
     * @var int Id of host
     * @Until("1.2.x")
     */
    private $id;
    /**
     * @var string Name of host
     */
    private $name;
    /**
     * @var string Alias of host
     */
    private $alias;
    /**
     * @var bool Indicates whether this host is enabled or disabled
     */
    private $isActive;
    /**
     * @var int Status of host
     */
    private $status;
    /**
     * @var Service[]
     * @Serializer\Groups({"realtime_services"})
     */
    private $services;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Host
     */
    public function setId(int $id): self
    {
        $this->id = $id;
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
     * @return Host
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @param string $alias
     * @return Host
     */
    public function setAlias(string $alias): self
    {
        $this->alias = $alias;
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
     * @return Host
     */
    public function setActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     * @return Host
     */
    public function setStatus(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return Service[]
     */
    public function getServices(): array
    {
        return $this->services;
    }

    /**
     * @param Service[] $services
     * @return Host
     */
    public function setServices(array $services): self
    {
        $this->services = $services;
        return $this;
    }

    /**
     * @param Service $service
     * @return Host
     */
    public function addService(Service $service): self
    {
        $this->services[] = $service;
        return $this;
    }
}
