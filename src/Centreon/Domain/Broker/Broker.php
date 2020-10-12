<?php

namespace Centreon\Domain\Broker;

class Broker
{
    /**
     * @var int
     */
    private $id;

    /**
     * Array of different Broker Configuration available for a monitoring server
     *
     * @var BrokerConfiguration[]
     */
    private $brokerConfigurations;

    private $isPeerRetentionMode;

    public function __consruct(array $brokerConfigurations)
    {
        $this->setBrokerConfiguration($brokerConfigurations);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getBrokerConfiguration(): ?array
    {
        return $this->brokerConfigurations;
    }

    public function setBrokerConfiguration(array $brokerConfigurations): self
    {
        $this->brokerConfigurations = $brokerConfigurations;
        return $this;
    }

    public function getIsPeerRetentionMode(): bool
    {
        return $this->isPeerRetentionMode;
    }

    public function setIsPeerRetentionMode(bool $isPeerRetentionMode): self
    {
        $this->isPeerRetentionMode = $isPeerRetentionMode;
        return $this;
    }
}