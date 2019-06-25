<?php


namespace Centreon\Domain\Security\Interfaces;

use Centreon\Domain\Contact\Contact;

interface AuthenticationServiceInterface
{
    public function findContactByCredentials(string $username, string $password): ?Contact;

    public function generateToken(string $username): string;

    public function getGeneratedToken():string;
}