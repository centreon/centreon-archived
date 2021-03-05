<?php

namespace Centreon\Domain\Authentication\UseCase;

class AuthenticateAPIRequest
{
    /**
     * @var array
     */
    private $credentials;

    public function __construct(array $credentials)
    {
        $this->credentials = $credentials;
    }

    public function getCredentials(): array
    {
        return $this->credentials;
    }
}