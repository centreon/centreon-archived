<?php


namespace Centreon\Domain\Entity\Configuration;

class Hostgroup
{
    /**
     * @var int
     */
    private $id;
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $alias;
    /**
     * @var bool
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
     * @return Hostgroup
     */
    public function setId(int $id): Hostgroup
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
     * @return Hostgroup
     */
    public function setName(string $name): Hostgroup
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
     * @return Hostgroup
     */
    public function setAlias(string $alias): Hostgroup
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
     * @return Hostgroup
     */
    public function setActivate(bool $isActivate): Hostgroup
    {
        $this->isActivate = $isActivate;
        return $this;
    }
}
