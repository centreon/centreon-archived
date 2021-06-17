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
use Centreon\Domain\Log\LoggerTrait;
use Security\Domain\Authentication\Exceptions\ProviderServiceException;
use Security\Domain\Authentication\Interfaces\ProviderServiceInterface;
use Security\Domain\Authentication\Interfaces\AuthenticationServiceInterface;
use Security\Domain\Authentication\Exceptions\AuthenticationServiceException;

class Authenticate
{
    use LoggerTrait;

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
     * @param AuthenticateResponse $response
     * @throws ProviderServiceException
     * @throws AuthenticationException
     * @throws AuthenticationServiceException
     */
    public function execute(AuthenticateRequest $request, AuthenticateResponse $response): void
    {
        $this->debug(
            "Beginning authentication on provider",
            ['provider_name' => $request->getProviderConfigurationName()]
        );
        $authenticationProvider = $this->providerService->findProviderByConfigurationName(
            $request->getProviderConfigurationName()
        );

        if ($authenticationProvider === null) {
            throw ProviderServiceException::providerConfigurationNotFound(
                $request->getProviderConfigurationName()
            );
        }
        $this->debug('Authentication using provider', ['provider_name' => $request->getProviderConfigurationName()]);

        /**
         * Authenticate using the provider chosen in the request.
         */
        $authenticationProvider->authenticate($request->getCredentials());

        if (!$authenticationProvider->isAuthenticated()) {
            $this->critical(
                "Provider can't authenticate successfully user ",
                [
                    "provider_name" => $authenticationProvider->getName(),
                    "user" => $request->getCredentials()["login"]
                ]
            );
            throw AuthenticationException::notAuthenticated();
        }

        $this->info('Retrieving user informations from provider');

        /**
         * Check if the user exists in the idP
         */
        $providerUser = $authenticationProvider->getUser();
        if ($providerUser === null) {
            $this->critical(
                'No contact could be found from provider',
                ['provider_name' => $request->getProviderConfigurationName()]
            );
            throw AuthenticationException::userNotFound();
        }

        /**
         * Check if the user exists in Centreon and if the provider is allowed to create user.
         */
        if (!$this->contactService->exists($providerUser)) {
            if ($authenticationProvider->canCreateUser()) {
                $this->debug(
                    'Provider is allow to create user. Creating user...',
                    ['user' => $providerUser->getAlias()]
                );
                $this->contactService->addUser($providerUser);
            } else {
                throw AuthenticationException::userNotFoundAndCannotBeCreated();
            }
        } else {
            $this->contactService->updateUser($providerUser);
        }

        /**
         * Start the legacy Session.
         */
        $this->session->start();
        $_SESSION['centreon'] = $authenticationProvider->getLegacySession();

        /**
         * Search for an already existing and available authentications token.
         * Create a new one if no one are found.
         */
        $authenticationTokens = $this->authenticationService->findAuthenticationTokensByToken($this->session->getId());
        if ($authenticationTokens === null) {
            $this->debug('Creating authentication tokens for user', ['user' => $providerUser->getAlias()]);
            $this->authenticationService->createAuthenticationTokens(
                $this->session->getId(),
                $request->getProviderConfigurationName(),
                $providerUser,
                $authenticationProvider->getProviderToken($this->session->getId()),
                $authenticationProvider->getProviderRefreshToken($this->session->getId())
            );
        }

        $this->debug(
            "Authentication success",
            [
                "provider_name" => $request->getProviderConfigurationName(),
                "contact_id" => $providerUser->getId(),
                "contact_alias" => $providerUser->getAlias()
            ]
        );

        /**
         * Define the redirection uri where user will be redirect once logged.
         */
        if ($providerUser->getDefaultPage() !== null) {
            $response->setRedirectionUri($request->getCentreonBaseUri() . $providerUser->getDefaultPage());
        } else {
            $response->setRedirectionUri($request->getCentreonBaseUri() . $this->redirectDefaultPage);
        }
    }
}
