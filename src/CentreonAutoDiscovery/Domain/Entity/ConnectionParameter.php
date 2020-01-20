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
 * Class representing one of the credentials or arguments used to connect to
 * the provider
 *
 * @package CentreonAutoDiscovery\Domain\Entity
 */
class ConnectionParameter
{
    /**
     * @var int Credential entity id
     */
    private $id;

    /**
     * @var string Credential entity name
     */
    private $name;

    /**
     * @var string Credential entity value
     */
    private $value;

    /**
     * @var string Credential entity description
     */
    private $description;

    /**
     * @var bool Credential entity mandatory indicator
     */
    private $isMandatory;

    /**
     * @var bool Credential entity locked indicator
     */
    private $isLocked;

    /**
     * @var bool Credential entity hidden indicator
     */
    private $isHidden;

    /**
     * @var string Credential entity type
     */
    private $type;

    /**
     * @return int
     * @see ConnectionParameter::$id
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return ConnectionParameter
     * @see ConnectionParameter::$id
     */
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     * @see ConnectionParameter::$name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return ConnectionParameter
     * @see ConnectionParameter::$name
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     * @see ConnectionParameter::$value
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return ConnectionParameter
     * @see ConnectionParameter::$value
     */
    public function setValue(?string $value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return string
     * @see ConnectionParameter::$description
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return ConnectionParameter
     * @see ConnectionParameter::$description
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return bool
     * @see ConnectionParameter::$isMandatory
     */
    public function isMandatory(): bool
    {
        return $this->isMandatory;
    }

    /**
     * @param bool $isMandatory
     * @return ConnectionParameter
     * @see ConnectionParameter::$isMandatory
     */
    public function setMandatory(bool $isMandatory): self
    {
        $this->isMandatory = $isMandatory;
        return $this;
    }

    /**
     * @return bool
     * @see ConnectionParameter::$isLocked
     */
    public function isLocked(): bool
    {
        return $this->isLocked;
    }

    /**
     * @param bool $isLocked
     * @return ConnectionParameter
     * @see ConnectionParameter::$isLocked
     */
    public function setLocked(bool $isLocked): self
    {
        $this->isLocked = $isLocked;
        return $this;
    }

    /**
     * @return bool
     * @see ConnectionParameter::$isHidden
     */
    public function isHidden(): bool
    {
        return $this->isHidden;
    }

    /**
     * @param bool $isHidden
     * @return ConnectionParameter
     * @see ConnectionParameter::$isHidden
     */
    public function setHidden(bool $isHidden): self
    {
        $this->isHidden = $isHidden;
        return $this;
    }

    /**
     * @return string
     * @see ConnectionParameter::$type
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return ConnectionParameter
     * @see ConnectionParameter::$type
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'name'         => $this->name,
            'value'        => $this->value,
            'description'  => $this->description,
            'mandatory'    => $this->isMandatory,
            'locked'       => $this->isLocked,
            'hidden'       => $this->isHidden,
            'type'         => $this->type
        ];
    }
}
