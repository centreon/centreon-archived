<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */
declare(strict_types=1);

namespace Security\Domain\Authentication\Model;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Security\Domain\Authentication\Exceptions\AuthenticationTokensFactoryException;
use Security\Domain\Authentication\Interfaces\AuthenticationServiceInterface;

/**
 * @package Security\Domain\Authentication\Model
 */
class AuthenticationTokensFactory
{
    /**
     * @var AuthenticationServiceInterface
     */
    private $authenticationService;

    /**
     * @param AuthenticationServiceInterface $authenticationService
     */
    public function __construct(AuthenticationServiceInterface $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }

    /**
     * @param string $sessionToken
     * @param string $providerConfigurationName
     * @param ContactInterface $contact
     * @param ProviderToken $providerToken
     * @param ProviderToken $providerRefreshToken
     * @return AuthenticationTokens
     * @throws \Assert\AssertionFailedException
     */
    public function createOrFail(
        string $sessionToken,
        string $providerConfigurationName,
        ContactInterface $contact,
        ProviderToken $providerToken,
        ProviderToken $providerRefreshToken
    ) {
        $providerConfiguration = $this->authenticationService->findProviderConfigurationByConfigurationName(
            $providerConfigurationName
        );
        if ($providerConfiguration == null) {
            throw AuthenticationTokensFactoryException::ProviderConfigurationNotFound();
        }

        return new AuthenticationTokens(
            $contact->getId(),
            $providerConfiguration->getId(),
            $sessionToken,
            $providerToken,
            $providerRefreshToken
        );
    }
}
