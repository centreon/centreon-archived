<?php

namespace Centreon\Domain\Monitoring;

use JMS\Serializer\Annotation as Serializer;
use Centreon\Domain\Annotation\EntityDescriptor as Desc;

/**
 * Class HostGroup
 * @package Centreon\Domain\Monitoring
 */
class HostGroup
{
    /**
     * @Serializer\Groups({"hg_main"})
     * @Desc(column="hostgroup_id", modifier="setId")
     * @var int
     */
    private $id;

    /**
     * @Serializer\Groups({"hg_main"})
     * @var Host[]
     */
    private $hosts = [];

    /**
     * @Serializer\Groups({"hg_main"})
     * @var string|null
     */
    private $name;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return HostGroup
     */
    public function setId(int $id): HostGroup
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return HostGroup
     */
    public function setName(?string $name): HostGroup
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param Host $host
     * @return HostGroup
     */
    public function addHost(Host $host):HostGroup
    {
        $this->hosts[] = $host;
        return $this;
    }

    /**
     * @return Host[]
     */
    public function getHosts(): array
    {
        return $this->hosts;
    }

    /**
     * Indicates if a host exists in this host group.
     *
     * @param int $hostId Host id to find
     * @return bool
     */
    public function isHostExists(int $hostId): bool
    {
        foreach ($this->hosts as $host) {
            if ($host->getId() === $hostId) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param Host[] $hosts
     * @return HostGroup
     */
    public function setHosts(array $hosts): HostGroup
    {
        $this->hosts = $hosts;
        return $this;
    }
}
