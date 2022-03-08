<?php

namespace Core\Infrastructure\Security\Api\FindProviderConfigurations\ProviderPresenter;

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
     * @return array
     */
    public function present(mixed $response): array;
}
