<?php

namespace Core\Domain\User\Model;

class User
{
    /**
     * @param integer $id
     * @param string $alias
     * @param string $password
     */
    public function __construct(private int $id, private string $alias, private string $password)
    {
    }

    /**
     * @return integer
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return self
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }
}
