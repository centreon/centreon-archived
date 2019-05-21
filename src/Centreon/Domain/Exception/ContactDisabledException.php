<?php


namespace Centreon\Domain\Exception;


use Symfony\Component\Security\Core\Exception\AuthenticationException;

class ContactDisabledException extends AuthenticationException
{
    public function getMessageKey()
    {
        return 'Contact disabled.';
    }
}
