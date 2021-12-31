<?php

namespace Core\Application\Security\Exception;

use Core\Application\Common\Exception\NotFoundException;

class SecurityPolicyException extends \Exception
{
    public static function securityPolicyNotFound(): NotFoundException
    {
        return new NotFoundException(_('Security policy not found. Please verify that your installation is valid'));
    }
}
