<?php

namespace Core\Security\ProviderConfiguration\Infrastructure\Api\FindProviderConfigurations\ProviderPresenter;

interface ProviderPresenterInterface
{
    /**
     * Validate that the presenter will handle the correct responst
     *
     * @param mixed $response
     * @return boolean
     */
    public function isValidFor(mixed $response): bool;

    /**
     * Format response.
     *
     * @param mixed $response
     * @return array<string,mixed>
     */
    public function present(mixed $response): array;
}
