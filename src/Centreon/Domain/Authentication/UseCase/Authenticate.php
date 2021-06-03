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

use Security\Domain\Authentication\Interfaces\AuthenticationServiceInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Centreon\Domain\Contact\Interfaces\ContactServiceInterface;
use Centreon\Domain\Authentication\Exception\AuthenticationException;
use Security\Domain\Authentication\Exceptions\AuthenticationServiceException;

class Authenticate
{
    /**
     * @var AuthenticationServiceInterface
     */
    private $authenticationService;

    /**
     * @var ContactServiceInterface
     */
    private $contactService;

    /**
     * @var string
     */
    private $redirectDefaultPage;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @param string $redirectDefaultPage
     * @param AuthenticationServiceInterface $authenticationService
     * @param ContactServiceInterface $contactService
     * @param SessionInterface $session
     */
    public function __construct(
        string $redirectDefaultPage,
        AuthenticationServiceInterface $authenticationService,
        ContactServiceInterface $contactService,
        SessionInterface $session
    ) {
        $this->redirectDefaultPage = $redirectDefaultPage;
        $this->authenticationService = $authenticationService;
        $this->contactService = $contactService;
        $this->session = $session;
    }

    /**
     * Execute authentication scenario and return the redirection URI.
     *
     * @param AuthenticateRequest $request
     * @return string
     */
    public function execute(AuthenticateRequest $request): string
    {
        $authenticationProvider = $this->authenticationService->findProviderByConfigurationName(
            $request->getProviderConfigurationName()
        );

        if ($authenticationProvider === null) {
            throw AuthenticationServiceException::providerConfigurationNotFound(
                $request->getProviderConfigurationName()
            );
        }

        $authenticationProvider->authenticate($request->getCredentials());

        if (!$authenticationProvider->isAuthenticated()) {
            throw AuthenticationException::notAuthenticated();
        }

        $providerUser = $authenticationProvider->getUser();
        if ($providerUser === null) {
            throw AuthenticationException::userNotFound();
        }

        if (!$this->contactService->exists($providerUser)) {
            if ($authenticationProvider->canCreateUser()) {
                $this->contactService->addUser($providerUser);
            } else {
                throw AuthenticationException::cannotCreateUser();
            }
        } else {
            $this->contactService->updateUser($providerUser);
        }

        $this->session->start();
        $_SESSION['centreon'] = $authenticationProvider->getLegacySession();

        $authenticationTokens = $this->authenticationService->findAuthenticationTokensByToken($this->session->getId());
        if ($authenticationTokens === null) {
            $this->authenticationService->createAuthenticationTokens(
                $this->session->getId(),
                $request->getProviderConfigurationName(),
                $providerUser,
                $authenticationProvider->getProviderToken($this->session->getId()),
                $authenticationProvider->getProviderRefreshToken($this->session->getId())
            );
        }

        if ($providerUser->getDefaultPage() !== null) {
            return $providerUser->getDefaultPage();
        } else {
            return $this->redirectDefaultPage;
        }
    }
}
