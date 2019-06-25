<?php

namespace Centreon\Domain\Security;

class AccessGroup
{
    /**
     * @var int Id of access group
     */
    private $id;
    /**
     * @var string Name of the access group
     */
    private $name;
    /**
     * @var string Alias of the access group
     */
    private $alias;
    /**
     * @var bool Indicates whether this contact is enabled or disabled
     */
    private $isActivate;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return AccessGroup
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
     * @return AccessGroup
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
     * @return AccessGroup
     */
    public function setAlias(string $alias): self
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActivate(): bool
    {
        return $this->isActivate;
    }

    /**
     * @param bool $isActivate
     * @return AccessGroup
     */
    public function setActivate(bool $isActivate): self
    {
        $this->isActivate = $isActivate;
        return $this;
    }
}
