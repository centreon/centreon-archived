<?php

namespace Centreon\Domain\Broker;

class BrokerConfiguration
{
    /**
     * Configuration Id
     * @var int|null
     */
    private $id;

    /**
     * Configuration Broker Key
     *
     * @var string|null
     */
    private $configurationKey;

    /**
     * Configuration Broker Value
     *
     * @var string
     */
    private $configurationValue;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getConfigurationKey(): ?string
    {
        return $this->configurationKey;
    }

    public function setConfigurationKey(?string $configurationKey): self
    {
        $this->configurationKey = $configurationKey;
        return $this;
    }

    public function getConfigurationValue(): ?string
    {
        return $this->configurationValue;
    }

    public function setConfigurationValue(?string $configurationValue): self
    {
        $this->configurationValue = $configurationValue;
        return $this;
    }

}