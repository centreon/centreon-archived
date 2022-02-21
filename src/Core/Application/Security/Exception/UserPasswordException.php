<?php

namespace Core\Application\Security\Exception;

class UserPasswordException extends \Exception
{
    /**
     * Exception thrown when a password doesn't match the security policy.
     *
     * @return self
     */
    public static function passwordDoesntMatchSecurityPolicy(): self
    {
        return new self(_("Your password doesn't match the security policy"));
    }
}
