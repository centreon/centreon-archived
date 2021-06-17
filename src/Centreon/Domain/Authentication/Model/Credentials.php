<?php

namespace Centreon\Domain\Authentication\Model;

class Credentials
{
    /**
     * @var string
     */
    private $login;

    /**
     * @var string
     */
    private $password;

    public function getLogin(): string
    {
        return $this->login;
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }
}
