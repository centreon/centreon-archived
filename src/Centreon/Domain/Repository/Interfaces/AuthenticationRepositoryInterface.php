<?php

namespace Centreon\Domain\Repository\Interfaces;

use Centreon\Domain\Entity\AuthenticationToken;

interface AuthenticationRepositoryInterface
{
    public function isGoodCredentials(string $username, string $password): bool;

    public function addToken(int $contactId, string $token);

    public function deleteExpiredTokens():int;

    /**
     * @param int $contactId
     * @return AuthenticationToken[]
     */
    public function findTokenByContact(int $contactId): array;

    public function findToken(string $token): ?AuthenticationToken;

    public function deleteTokensByContact(int $contactId): int;
}
