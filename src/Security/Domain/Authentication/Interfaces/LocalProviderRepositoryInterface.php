<?php

namespace Security\Domain\Authentication\Interfaces;

use Security\Domain\Authentication\Model\ProviderToken;

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

        /**
     * @param string $token Session token
     * @param int $providerConfigurationId Provider configuration id
     * @param int $contactId Contact id
     * @param ProviderToken $providerToken Provider token
     * @param ProviderToken $providerRefreshToken Provider refresh token
     */
    public function addAPIAuthenticationTokens(
        string $token,
        int $providerConfigurationId,
        int $contactId,
        ProviderToken $providerToken,
        ?ProviderToken $providerRefreshToken
    ): void;
}