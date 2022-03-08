<?php

namespace Core\Infrastructure\Security\Api\FindProviderConfigurations\PresenterProviders;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Core\Domain\Security\ProviderConfiguration\OpenId\Model\OpenIdConfiguration;
use Core\Application\Security\UseCase\FindProviderConfigurations\FindOpenIdProviderConfigurationResponse;

class OpenIdProviderPresenter
{
    public function __construct(private UrlGeneratorInterface $router)
    {
    }

    public function isValidFor(mixed $response): bool
    {
        return is_a($response, FindOpenIdProviderConfigurationResponse::class);
    }

    public function format(FindOpenIdProviderConfigurationResponse $response): array
    {
        $redirectUri = $this->router->generate(
            'centreon_security_authentication_openid_login',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        return [
            'id' => $response->id,
            'type' => OpenIdConfiguration::NAME,
            'name' => OpenIdConfiguration::NAME,
            'authentication_uri' => $response->baseUrl . '/' . ltrim($response->authorizationEndpoint, '/')
                . '?client_id=' . $response->clientId . '&response_type=code' . '&redirect_uri=' . $redirectUri
                . '&state=' . uniqid(),
            'is_active' => $response->isActive,
            'is_forced' => $response->isForced,
        ];
    }
}
