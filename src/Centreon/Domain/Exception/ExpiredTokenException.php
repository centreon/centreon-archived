<?php


namespace Centreon\Domain\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class ExpiredTokenException extends AuthenticationException
{
    public function getMessageKey()
    {
        return 'Token expired.';
    }
}