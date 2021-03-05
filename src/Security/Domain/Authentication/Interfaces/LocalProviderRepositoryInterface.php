<?php

namespace Security\Domain\Authentication\Interfaces;

interface LocalProviderRepositoryInterface
{
    /**
     * Clear all information about the session token.
     *
     * @param string $sessionToken
     */
    public function deleteSession(string $sessionToken): void;

    /**
     * Delete all expired API tokens registered.
     *
     */
    public function deleteExpiredAPITokens(): void;
}