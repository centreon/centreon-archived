<?php

namespace Security\Domain\Authentication\Interfaces;

interface LocalProviderRepositoryInterface
{
    /**
     * Delete all expired API tokens registered.
     *
     */
    public function deleteExpiredSecurityTokens(): void;
}
