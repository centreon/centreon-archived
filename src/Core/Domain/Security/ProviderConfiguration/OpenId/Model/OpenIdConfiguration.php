<?php

namespace Core\Domain\Security\ProviderConfiguration\OpenId\Model;

class OpenIdConfiguration
{
    public function __construct(
        private bool $isActive,
        private bool $isForced,
        private array $trustedClientAddresses,
        private array $blacklistClientAddresses,
        private ?string $baseUrl,
        private ?string $authorizationEndpoint,
        private ?string $tokenEndpoint,
        private ?string $introspectionTokenEndpoint,
        private ?string $userInformationsEndpoint,
        private ?string $endSessionEndpoint,
        private array $connectionScope,
        private ?string $loginClaim,
        private ?string $clientId,
        private ?string $clientSecret,
        private ?string $authenticationType,
        private bool $verifyPeer
    ) {
    }
}
