<?php

namespace Security\Domain\Authentication\Interfaces;

use Security\Domain\Authentication\Model\ProviderToken;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Core\Domain\Security\ProviderConfiguration\OpenId\Model\OpenIdConfiguration;

interface OpenIdProviderInterface extends ProviderInterface
{
    /**
     * @return OpenIdConfiguration
     */
    public function getConfiguration(): OpenIdConfiguration;

    /**
     * @return ProviderToken
     */
    public function getProviderToken(): ProviderToken;

    /**
     * @return ProviderToken
     */
    public function getProviderRefreshToken(): ProviderToken;

    /**
     * @return ContactInterface|null
     */
    public function createUser(): ?ContactInterface;

     /**
     * Authenticate the user using OpenId Provider.
     *
     * @param string|null $authorizationCode
     */
    public function authenticateOrFail(?string $authorizationCode, string $clientIp): void;
}
