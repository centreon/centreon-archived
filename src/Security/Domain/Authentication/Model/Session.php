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

    /**
     * @var string
     */
    private $clientIp;

    public function __construct(string $token, int $contactId, string $clientIp)
    {
        $this->token = $token;
        $this->contactId = $contactId;
        $this->clientIp = $clientIp;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return int
     */
    public function getContactId(): int
    {
        return $this->contactId;
    }

    /**
     * @return string
     */
    public function getClientIp(): string
    {
        return $this->clientIp;
    }
}
