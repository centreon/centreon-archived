<?php
/*
 * CENTREON
 *
 * Source Copyright 2005-2019 CENTREON
 *
 * Unauthorized reproduction, copy and distribution
 * are not allowed.
 *
 * For more information : contact@centreon.com
 *
*/

namespace CentreonAutoDiscovery\Domain\Entity;

/**
 * Class representing a provider
 *
 * @package CentreonAutoDiscovery\Domain\Entity
 */
class Provider
{
    /**
     * @var int provider id
     */
    private $id;

    /**
     * @var int linked plugin pack id
     */
    private $pluginPackId;

    /**
     * @var string provider name
     */
    private $name;

    /**
     * @var string provider description
     */
    private $description;

    /**
     * @var string provider type to share credentials between providers
     */
    private $type;

    /**
     * @var int linked command id
     */
    private $commandId;

    /**
     * @var string test option for login
     */
    private $testOption;

    /**
     * @var array provider parameters
     */
    private $parameters;

    /**
     * @return int
     * @see Provider::$id
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Provider
     * @see Provider::$id
     */
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     * @see Provider::$pluginpackId
     */
    public function getPluginPackId(): int
    {
        return $this->pluginPackId;
    }

    /**
     * @param int $pluginPackId
     * @return Provider
     * @see Provider::$pluginPackId
     */
    public function setPluginPackId(int $pluginPackId): self
    {
        $this->pluginPackId = $pluginPackId;
        return $this;
    }

    /**
     * @return string
     * @see Provider::$name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Provider
     * @see Provider::$name
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     * @see Provider::$description
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return Provider
     * @see Provider::$description
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string|null
     * @see Provider::$type
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     * @return Provider
     * @see Provider::$type
     */
    public function setType(?string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return int
     * @see Provider::$commandId
     */
    public function getCommandId(): int
    {
        return $this->commandId;
    }

    /**
     * @param int $commandId
     * @return Provider
     * @see Provider::$commandId
     */
    public function setCommandId(int $commandId): self
    {
        $this->commandId = $commandId;
        return $this;
    }

    /**
     * @return string|null
     * @see Provider::$testOption
     */
    public function getTestOption(): ?string
    {
        return $this->testOption;
    }

    /**
     * @param string|null $testOption
     * @return Provider
     * @see Provider::$testOption
     */
    public function setTestOption(?string $testOption): self
    {
        $this->testOption = $testOption;
        return $this;
    }

    /**
     * @return array
     * @see Provider::$parameters
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     * @return Provider
     * @see Provider::$parameters
     */
    public function setParameters(array $parameters): self
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * Convert data to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'pluginPackId' => $this->pluginPackId,
            'name' => $this->name,
            'description' => $this->description,
            'commandId' => $this->commandId,
            'testOption' => $this->testOption,
            'parameters' => $this->parameters
        ];
    }
}
