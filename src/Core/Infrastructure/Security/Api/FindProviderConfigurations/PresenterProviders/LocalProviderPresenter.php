<?php

use Core\Application\Security\UseCase\FindProviderConfigurations\FindLocalProviderConfigurationResponse;

class LocalProviderPresenter
{
    public function isValidFor(mixed $response)
    {
        return is_a($response, FindLocalProviderConfigurationResponse::class);
    }

    public function format(FindLocalProviderConfigurationResponse $response) {
        return [
            'id' => $response->id,
            'type' => $response->type,
            'name' => $response->name,
            'authentication_uri' => $response->authenticationUri,
            'is_active' => $response->isActive,
            'is_forced' => $response->isForced,
        ];
    }
}