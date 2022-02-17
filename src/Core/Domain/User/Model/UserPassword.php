<?php

namespace Core\Domain\User\Model;

class UserPassword
{
    /**
     * @param integer $userId
     * @param string $passwordValue
     * @param integer $creationDate
     */
    public function __construct(private int $userId, private string $passwordValue, private int $creationDate)
    {
    }

    /**
     * @return integer
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function getPasswordValue(): string
    {
        return $this->passwordValue;
    }

    /**
     * @return integer
     */
    public function getCreationDate(): int
    {
        return $this->creationDate;
    }

    /**
     * @param string $passwordValue
     * @return self
     */
    public function setPasswordValue(string $passwordValue): self
    {
        $this->passwordValue = $passwordValue;
        return $this;
    }

    /**
     * @param integer $creationDate
     * @return self
     */
    public function setCreationDate(int $creationDate): self
    {
        $this->creationDate = $creationDate;
        return $this;
    }
}
