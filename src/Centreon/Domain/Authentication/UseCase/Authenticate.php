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

use Security\Domain\Authentication\AuthenticationService;
use Centreon\Domain\Contact\Interfaces\ContactServiceInterface;
use Security\Domain\Authentication\Exceptions\AuthenticationServiceException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Authenticate
{
    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var ContactServiceInterface
     */
    private $contactService;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @param AuthenticationService $authenticationService
     * @param ContactServiceInterface $contactService
     * @param SessionInterface $session
     */
    public function __construct(
        AuthenticationService $authenticationService,
        ContactServiceInterface $contactService,
        SessionInterface $session
    ) {
        $this->authenticationService = $authenticationService;
        $this->contactService = $contactService;
        $this->session = $session;
    }

    /**
     * Execute authentication scenario
     *
     * @param AuthenticateRequest $request
     * @param string $providerConfigurationName
     * @return AuthenticateResponse
     */
    public function execute(AuthenticateRequest $request, string $providerConfigurationName): AuthenticateResponse
    {
        $response = new AuthenticateResponse();

        $authenticationProvider = $this->authenticationService->findProviderByConfigurationName(
            $providerConfigurationName
        );

        if ($authenticationProvider === null) {
            throw AuthenticationServiceException::providerConfigurationNotFound($providerConfigurationName);
        }

        $authenticationProvider->authenticate($request->getParameters());

        $response->setAuthenticated($authenticationProvider->isAuthenticated());
        if (!$response->isAuthenticated()) {
            return $response;
        }

        $providerUser = $authenticationProvider->getUser();
        $response->setUserFound($providerUser !== null);
        if (!$response->isUserFound()) {
            return $response;
        }

        if (!$this->contactService->exists($providerUser)) {
            if ($authenticationProvider->canCreateUser()) {
                $this->contactService->addUser($providerUser);
            } else {
                $response->shouldAndCannotCreateUser(true);
                return $response;
            }
        } else {
            $this->contactService->updateUser($providerUser);
        }

        $this->session->start();
        $_SESSION['centreon'] = $authenticationProvider->getLegacySession();

        $this->authenticationService->createAuthenticationTokens(
            $this->session->getId(),
            $providerConfigurationName,
            $providerUser,
            $authenticationProvider->getProviderToken($this->session->getId()),
            $authenticationProvider->getProviderRefreshToken($this->session->getId())
        );

        return $response;
    }
}
