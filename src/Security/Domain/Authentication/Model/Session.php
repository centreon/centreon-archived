<?php

namespace Security\Domain\Authentication\Model;

class Session
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var int
     */
    private $contactId;

    public function __construct(string $token, int $contactId)
    {
        $this->token = $token;
        $this->contactId = $contactId;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getContactId(): int
    {
        return $this->contactId;
    }
}
