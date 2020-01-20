<?php
/*
 * Centreon
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

namespace CentreonAutoDiscovery\Domain\Parameter;

/**
 * This class is designed to represent a parameter.
 *
 * @package CentreonAutoDiscovery\Domain\Parameter
 *
 */
class Parameter
{
    const TYPE_PASSWORD = 'password';
    const TYPE_TEXT = 'text';

    /**
     * @JMS\Serializer\Annotation\Groups({"param_main"})
     * @JMS\Serializer\Annotation\Type("integer")
     * @var int|null Parameter id
     */
    private $id;

    /**
     * @JMS\Serializer\Annotation\Groups({"param_main"})
     * @JMS\Serializer\Annotation\Type("string")
     * @var string Parameter description
     */
    private $description;

    /**
     * @JMS\Serializer\Annotation\Groups({"param_main"})
     * @JMS\Serializer\Annotation\Type("string")
     * @var string|null Group name of parameter
     */
    private $group;

    /**
     * @JMS\Serializer\Annotation\Groups({"param_main"})
     * @JMS\Serializer\Annotation\Type("boolean")
     * @Centreon\Domain\Annotation\EntityDescriptor(column="hidden", modifier="setHidden")
     * @var bool Indicates if this parameter is hidden
     */
    private $isHidden;

    /**
     * @JMS\Serializer\Annotation\Groups({"param_main"})
     * @JMS\Serializer\Annotation\Type("boolean")
     * @Centreon\Domain\Annotation\EntityDescriptor(column="locked", modifier="setLocked")
     * @var bool Indicates if this parameter is locked
     */
    private $isLocked;

    /**
     * @JMS\Serializer\Annotation\Groups({"param_main"})
     * @JMS\Serializer\Annotation\Type("boolean")
     * @Centreon\Domain\Annotation\EntityDescriptor(column="mandatory", modifier="setMandatory")
     * @var bool Indicates if this parameter is mandatory
     */
    private $isMandatory;

    /**
     * @JMS\Serializer\Annotation\AccessType("public_method")
     * @JMS\Serializer\Annotation\Accessor(getter="isPassword",setter="setPassword")
     * @JMS\Serializer\Annotation\Groups({"param_main"})
     * @JMS\Serializer\Annotation\Type("boolean")
     * @var bool Indicates whether this parameter is a password or not
     */
    private $isPassword;

    /**
     * @JMS\Serializer\Annotation\Groups({"param_main"})
     * @JMS\Serializer\Annotation\Type("string")
     * @var string Parameter name
     */
    private $name;

    /**
     * @var string Parameter type ('text' or 'password')
     */
    private $type;

    /**
     * @JMS\Serializer\Annotation\Groups({"param_main"})
     * @JMS\Serializer\Annotation\Type("string")
     * @var string|null
     */
    private $value;

    public function __construct ()
    {
        $this->type = self::TYPE_TEXT;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return Parameter
     */
    public function setId(?int $id): Parameter
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
     * @return Parameter
     */
    public function setDescription(string $description): Parameter
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getGroup(): ?string
    {
        return $this->group;
    }

    /**
     * @param string|null $group
     * @return Parameter
     */
    public function setGroup(?string $group): Parameter
    {
        $this->group = $group;
        return $this;
    }

    /**
     * @return bool
     */
    public function isHidden(): bool
    {
        return $this->isHidden;
    }

    /**
     * @param bool $isHidden
     * @return Parameter
     */
    public function setHidden(bool $isHidden): Parameter
    {
        $this->isHidden = $isHidden;
        return $this;
    }

    /**
     * @return bool
     */
    public function isLocked(): bool
    {
        return $this->isLocked;
    }

    /**
     * @param bool $isLocked
     * @return Parameter
     */
    public function setLocked(bool $isLocked): Parameter
    {
        $this->isLocked = $isLocked;
        return $this;
    }

    /**
     * @return bool
     */
    public function isMandatory(): bool
    {
        return $this->isMandatory;
    }

    /**
     * @param bool $isMandatory
     * @return Parameter
     */
    public function setMandatory(bool $isMandatory): Parameter
    {
        $this->isMandatory = $isMandatory;
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
     * @return Parameter
     */
    public function setName(string $name): Parameter
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Parameter
     */
    public function setType(string $type): Parameter
    {
        $this->type = $type;
        $this->isPassword = ($type === self::TYPE_PASSWORD);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string|null $value
     * @return Parameter
     */
    public function setValue(?string $value): Parameter
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPassword (): bool
    {
        return $this->isPassword;
    }

    /**
     * @param bool $isPassword
     * @return Parameter
     */
    public function setPassword (bool $isPassword): Parameter
    {
        $this->isPassword = $isPassword;
        $this->type = $isPassword ? self::TYPE_PASSWORD : self::TYPE_TEXT;
        return $this;
    }
}
