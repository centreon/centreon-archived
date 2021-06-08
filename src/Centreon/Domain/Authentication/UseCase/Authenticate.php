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

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Centreon\Domain\Contact\Interfaces\ContactServiceInterface;
use Centreon\Domain\Authentication\UseCase\AuthenticateResponse;
use Centreon\Domain\Authentication\Exception\AuthenticationException;
use Security\Domain\Authentication\Exceptions\ProviderServiceException;
use Security\Domain\Authentication\Interfaces\ProviderServiceInterface;
use Security\Domain\Authentication\Interfaces\AuthenticationServiceInterface;

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
     * @var ProviderServiceInterface
     */
    private $providerService;

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
     * @param ProviderServiceInterface $providerService
     * @param ContactServiceInterface $contactService
     * @param SessionInterface $session
     */
    public function __construct(
        string $redirectDefaultPage,
        AuthenticationServiceInterface $authenticationService,
        ProviderServiceInterface $providerService,
        ContactServiceInterface $contactService,
        SessionInterface $session
    ) {
        $this->redirectDefaultPage = $redirectDefaultPage;
        $this->authenticationService = $authenticationService;
        $this->providerService = $providerService;
        $this->contactService = $contactService;
        $this->session = $session;
    }

    /**
     * Execute authentication scenario and return the redirection URI.
     *
     * @param AuthenticateRequest $request
     * @return AuthenticateResponse
     */
    public function execute(AuthenticateRequest $request): AuthenticateResponse
    {
        $authenticationProvider = $this->providerService->findProviderByConfigurationName(
            $request->getProviderConfigurationName()
        );

        if ($authenticationProvider === null) {
            throw ProviderServiceException::providerConfigurationNotFound(
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
                throw AuthenticationException::userNotFoundAndCannotBeCreated();
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

        $response = new AuthenticateResponse();
        if ($providerUser->getDefaultPage() !== null) {
            $response->setRedirectionUri($request->getCentreonBaseUri() . $providerUser->getDefaultPage());
        } else {
            $response->setRedirectionUri($request->getCentreonBaseUri() . $this->redirectDefaultPage);
        }

        return $response;
    }
}
