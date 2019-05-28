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
     * @Serializer\Groups({"Default", "host_main", "host_full"})
     * @var int Id of host
     */
    private $id;
    /**
     * @Serializer\Groups({"Default", "host_main", "host_full"})
     * @var string Name of host
     */
    private $name;
    /**
     * @Serializer\Groups({"Default", "host_main", "host_full"})
     * @var string Alias of host
     */
    private $alias;

    /**
     * @Serializer\Groups({"host_main", "host_full"})
     * @var string Ip address or domain name
     */
    private $address;

    /**
     * @var bool Indicates whether this host is enabled or disabled
     * @Serializer\Groups({"host_main", "host_full"})
     */
    private $isActive;
    /**
     * @var int Status of host
     * @Serializer\Groups({"host_main", "host_full"})
     */
    private $status;
    /**
     * @var Service[]
     * @Serializer\Groups({"host_main", "host_full"})
     */
    private $services = [];

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
    public function setId(int $id): Host
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
    public function setName(string $name): Host
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
    public function setAlias(string $alias): Host
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
    public function setActive(bool $isActive): Host
    {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @param string $address
     * @return Host
     */
    public function setAddress(string $address): Host
    {
        $this->address = $address;
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
    public function setStatus(int $status): Host
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
    public function setServices(array $services): Host
    {
        $this->services = $services;
        return $this;
    }

    /**
     * @param Service $service
     * @return Host
     */
    public function addService(Service $service): Host
    {
        $this->services[] = $service;
        return $this;
    }
}
