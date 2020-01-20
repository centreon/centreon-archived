<?php
/*
 * CENTREON
 *
 * Source Copyright 2005-2019 Centreon
 *
 * Unauthorized reproduction, copy and distribution
 * are not allowed.
 *
 * For more information : contact@centreon.com
 *
 */
declare(strict_types=1);

namespace CentreonAutoDiscovery\Domain\Mapper;

use Centreon\Domain\HostConfiguration\Host;

class DiscoveredHost
{
    /**
     * @JMS\Serializer\Annotation\Groups({"discovery_host"})
     * @JMS\Serializer\Annotation\Type("integer")
     * @var int
     */
    private $id;

    /**
     * @JMS\Serializer\Annotation\Type("integer")
     * @var int Job id linked to this discovered host
     */
    private $jobId;

    /**
     * @JMS\Serializer\Annotation\Groups({"discovery_host"})
     * @JMS\Serializer\Annotation\Type("string")
     * @var string
     */
    private $name;

    /**
     * @JMS\Serializer\Annotation\Groups({"discovery_host"})
     * @JMS\Serializer\Annotation\Type("array")
     * @Centreon\Domain\Annotation\EntityDescriptor(column="discovery_result", modifier="setDiscoveryResultFromJson")
     * @var array
     */
    private $discoveryResult;

    /**
     * @return int
     */
    public function getId (): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return DiscoveredHost
     */
    public function setId (int $id): DiscoveredHost
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getJobId (): int
    {
        return $this->jobId;
    }

    /**
     * @param int $jobId
     * @return DiscoveredHost
     */
    public function setJobId (int $jobId): DiscoveredHost
    {
        $this->jobId = $jobId;
        return $this;
    }

    /**
     * @return string
     */
    public function getName (): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return DiscoveredHost
     */
    public function setName (string $name): DiscoveredHost
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return array
     */
    public function getDiscoveryResult (): array
    {
        return $this->discoveryResult;
    }

    /**
     * @param array $discoveryResult
     * @return DiscoveredHost
     */
    public function setDiscoveryResult (array $discoveryResult): DiscoveredHost
    {
        $this->discoveryResult = $discoveryResult;
        return $this;
    }

    /**
     * @param string $json Discovery result in JSON format
     * @return DiscoveredHost
     */
    public function setDiscoveryResultFromJson(string $json): DiscoveredHost
    {
        $this->discoveryResult = json_decode($json, true) ?? [];
        return $this;
    }

    /**
     * @param DiscoveredHost $discoveredHost
     * @return Host
     */
    public static function createHostConfiguration(DiscoveredHost $discoveredHost): Host
    {
        return (new Host())->setName($discoveredHost->getName());
    }
}
