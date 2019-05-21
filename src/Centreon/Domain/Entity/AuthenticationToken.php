<?php


namespace Centreon\Domain\Entity;


class AuthenticationToken
{
    /**
     * @var string
     */
    private $token;
    /**
     * @var \DateTime
     */
    private $generatedDate;

    /**
     * @var int
     */
    private $contactId;

    /**
     * @var bool
     */
    private $isValid;

    public function __construct(
        string $token,
        int $contactId,
        \DateTime $generatedDate,
        bool $isValid
    ) {
        $this->token = $token;
        $this->contactId = $contactId;
        $this->generatedDate = $generatedDate;
        $this->isValid = $isValid;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    public function getContactId(): int
    {
        return $this->contactId;
    }

    /**
     * @return \DateTime
     */
    public function getGeneratedDate(): \DateTime
    {
        return $this->generatedDate;
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }
}
