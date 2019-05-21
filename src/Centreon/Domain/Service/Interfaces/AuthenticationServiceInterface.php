<?php


namespace Centreon\Domain\Service\Interfaces;


use Centreon\Domain\Entity\Contact;

interface AuthenticationServiceInterface
{
    public function findContactByCredentials(string $username, string $password): ?Contact;

    public function generateToken(string $username): string;

    public function getGeneratedToken():string;
}