<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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

namespace Centreon\Domain\Authentication\UseCase;

use Centreon\Domain\Authentication\Exception\AuthenticationException;
use Security\Domain\Authentication\Exceptions\AuthenticationServiceException;
use Security\Domain\Authentication\Exceptions\ProviderServiceException;
use Security\Domain\Authentication\Interfaces\AuthenticationServiceInterface;
use Security\Domain\Authentication\Interfaces\ProviderServiceInterface;
use Security\Domain\Authentication\Model\LocalProvider;

class AuthenticateApi
{
    /**
     * @var AuthenticationServiceInterface
     */
    private $authenticationService;

    /**
     * @var ProviderServiceInterface
     */
    private $providerService;

    /**
     * @param AuthenticationServiceInterface $authenticationService
     */
    public function __construct(
        AuthenticationServiceInterface $authenticationService,
        ProviderServiceInterface $providerService
    ) {
        $this->authenticationService = $authenticationService;
        $this->providerService = $providerService;
    }

    /**
     * @param AuthenticateApiRequest $request
     * @return AuthenticateApiResponse
     * @throws ProviderServiceException
     * @throws AuthenticationServiceException
     * @throws AuthenticationException
     */
    public function execute(AuthenticateApiRequest $request): AuthenticateApiResponse
    {
        try {
            $this->authenticationService->deleteExpiredAPITokens();
        } catch (AuthenticationServiceException $ex) {
            // We don't propagate this error.
        }
        $localProvider = $this->providerService->findProviderByConfigurationName(LocalProvider::NAME);

        if ($localProvider === null) {
            throw ProviderServiceException::providerConfigurationNotFound(LocalProvider::NAME);
        }
        $localProvider->authenticate($request->getCredentials());

        if (!$localProvider->isAuthenticated()) {
            throw AuthenticationException::notAuthenticated();
        }

        $contact = $localProvider->getUser();
        if ($contact === null) {
            throw AuthenticationException::userNotFound();
        }
        $token = password_hash(bin2hex(random_bytes(128)), PASSWORD_BCRYPT);

        $this->authenticationService->createAPIAuthenticationTokens(
            $token,
            $contact,
            $localProvider->getProviderToken($token),
            null
        );

        $response = new AuthenticateApiResponse($contact, $token);

        return $response;
    }
}
