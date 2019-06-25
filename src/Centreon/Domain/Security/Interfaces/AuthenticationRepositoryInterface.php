<?php

namespace Centreon\Domain\Security\Interfaces;

use Centreon\Domain\Entity\AuthenticationToken;
use Centreon\Domain\Entity\Session;

interface AuthenticationRepositoryInterface
{
    public function isGoodCredentials(string $username, string $password): bool;

    public function addToken(int $contactId, string $token);

    public function deleteExpiredTokens():int;

    public function findSession(string $sessionId): ?Session;

    /**
     * @param int $contactId
     * @return AuthenticationToken[]
     */
    public function findTokenByContact(int $contactId): array;

    public function findToken(string $token): ?AuthenticationToken;

    public function deleteTokensByContact(int $contactId): int;
}
