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

use Centreon;
use Centreon\Domain\Log\LoggerTrait;
use Centreon\Domain\Menu\Model\Page;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Centreon\Domain\Contact\Interfaces\ContactServiceInterface;
use Centreon\Domain\Authentication\UseCase\AuthenticateResponse;
use Centreon\Domain\Authentication\Exception\AuthenticationException;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Security\Domain\Authentication\Exceptions\ProviderServiceException;
use Security\Domain\Authentication\Interfaces\ProviderServiceInterface;
use Security\Domain\Authentication\Exceptions\AuthenticationServiceException;
use Security\Domain\Authentication\Interfaces\AuthenticationServiceInterface;
use Security\Domain\Authentication\Interfaces\ProviderInterface;

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
        $authenticationProvider = $this->findProviderOrFail($request->getProviderConfigurationName());
        $this->authenticateOrFail($authenticationProvider, $request);
        $providerUser = $this->getUserFromProviderOrFail($authenticationProvider);

        if (!$this->contactService->exists($providerUser)) {
            $this->createUserOrFail($authenticationProvider, $providerUser);
        } else {
            $this->contactService->updateUser($providerUser);
        }

        $this->contactService->updateUserDefaultPage($providerUser);
        $this->startLegacySessionOrFail($authenticationProvider->getLegacySession());

        /**
         * Search for an already existing and available authentications token.
         * Create a new one if no one are found.
         */
        $authenticationTokens = $this->authenticationService->findAuthenticationTokensByToken($this->session->getId());
        if ($authenticationTokens === null) {
            $this->createAuthenticationTokenOrFail($authenticationProvider, $providerUser);
        }

        $this->debug(
            "[AUTHENTICATE] Authentication success",
            [
                "provider_name" => $request->getProviderConfigurationName(),
                "contact_id" => $providerUser->getId(),
                "contact_alias" => $providerUser->getAlias()
            ]
        );

        /**
         * Define the redirection uri where user will be redirect once logged.
         */
        $this->setResponseRedirectionUri($request, $response, $providerUser);
    }

    /**
     * Find a provider or throw an Exception.
     *
     * @param string $providerConfigurationName
     * @return ProviderInterface
     * @throws ProviderServiceException
     */
    private function findProviderOrFail(string $providerConfigurationName): ProviderInterface
    {
        $this->debug(
            '[AUTHENTICATE] Beginning authentication on provider',
            ['provider_name' => $providerConfigurationName]
        );
        $authenticationProvider = $this->providerService->findProviderByConfigurationName(
            $providerConfigurationName
        );

        if ($authenticationProvider === null) {
            throw ProviderServiceException::providerConfigurationNotFound(
                $providerConfigurationName
            );
        }

        return $authenticationProvider;
    }

    /**
     * Authenticate the user or throw an Exception.
     *
     * @param ProviderInterface $authenticationProvider
     * @param AuthenticateRequest $request
     * @throws AuthenticationException
     */
    private function authenticateOrFail(ProviderInterface $authenticationProvider, AuthenticateRequest $request): void
    {
        /**
         * Authenticate using the provider chosen in the request.
         */
        $this->debug(
            '[AUTHENTICATE] Authentication using provider',
            ['provider_name' => $request->getProviderConfigurationName()]
        );
        $authenticationProvider->authenticate([
            'login' => $request->getLogin(),
            'password' => $request->getPassword()
        ]);

        if (!$authenticationProvider->isAuthenticated()) {
            $this->critical(
                "[AUTHENTICATE] Provider can't authenticate successfully user ",
                [
                    "provider_name" => $authenticationProvider->getName(),
                    "user" => $request->getLogin()
                ]
            );
            throw AuthenticationException::notAuthenticated();
        }
    }

    /**
     * Retrieve user from provider or throw an Exception.
     *
     * @param ProviderInterface $authenticationProvider
     * @return ContactInterface
     * @throws AuthenticationException
     */
    private function getUserFromProviderOrFail(ProviderInterface $authenticationProvider): ContactInterface
    {
        $this->info('Retrieving user informations from provider');
        $providerUser = $authenticationProvider->getUser();
        if ($providerUser === null) {
            $this->critical(
                '[AUTHENTICATE] No contact could be found from provider',
                ['provider_name' => $authenticationProvider->getConfiguration()->getName()]
            );
            throw AuthenticationException::userNotFound();
        }

        return $providerUser;
    }

    /**
     * Create the user in Centreon or throw an Exception.
     *
     * @param ProviderInterface $authenticationProvider
     * @param ContactInterface $providerUser
     * @throws AuthenticationException
     */
    private function createUserOrFail(ProviderInterface $authenticationProvider, ContactInterface $providerUser): void
    {
        if ($authenticationProvider->canCreateUser()) {
            $this->debug(
                '[AUTHENTICATE] Provider is allow to create user. Creating user...',
                ['user' => $providerUser->getAlias()]
            );
            $this->contactService->addUser($providerUser);
        } else {
            throw AuthenticationException::userNotFoundAndCannotBeCreated();
        }
    }

    /**
     * Start the Centreon session.
     *
     * @param Centreon|null $legacySession
     * @throws AuthenticationException
     */
    private function startLegacySessionOrFail(?Centreon $legacySession): void
    {
        $this->info('Starting Centreon Session');
        if ($legacySession !== null) {
            $this->session->start();
            $_SESSION['centreon'] = $legacySession;
            $this->info('Session Started');
        } else {
            $this->critical('[AUTHENTICATE] No Legacy has been found');
            throw AuthenticationException::cannotStartLegacySession();
        }
    }

    /**
     * Create Authentication tokens or throw an Exception.
     *
     * @param ProviderInterface $authenticationProvider
     * @param ContactInterface $providerUser
     * @throws AuthenticationServiceException
     */
    private function createAuthenticationTokenOrFail(
        ProviderInterface $authenticationProvider,
        ContactInterface $providerUser
    ): void {
        $this->debug(
            '[AUTHENTICATE] Creating authentication tokens for user',
            ['user' => $providerUser->getAlias()]
        );
        $this->authenticationService->createAuthenticationTokens(
            $this->session->getId(),
            $authenticationProvider->getConfiguration()->getName(),
            $providerUser,
            $authenticationProvider->getProviderToken($this->session->getId()),
            $authenticationProvider->getProviderRefreshToken($this->session->getId())
        );
    }

    /**
     * Set the redirection Uri to the response.
     *
     * @param AuthenticateRequest $request
     * @param AuthenticateResponse $response
     * @param ContactInterface $providerUser
     */
    private function setResponseRedirectionUri(
        AuthenticateRequest $request,
        AuthenticateResponse $response,
        ContactInterface $providerUser
    ): void {
        /**
         * Define the redirection uri where user will be redirect once logged.
         */
        if ($providerUser->getDefaultPage() !== null  && $providerUser->getDefaultPage()->getUrl() !== null) {
            $response->setRedirectionUri(
                $request->getCentreonBaseUri() . $this->buildDefaultRedirectionUri($providerUser->getDefaultPage())
            );
        } else {
            $response->setRedirectionUri($request->getCentreonBaseUri() . $this->redirectDefaultPage);
        }
    }

    /**
     * build the redirection uri based on isReact page property.
     *
     * @param Page $defaultPage
     * @return string
     */
    private function buildDefaultRedirectionUri(Page $defaultPage): string
    {
        if ($defaultPage->isReact() === true) {
            // redirect to the react path
            $redirectUri = $defaultPage->getUrl() ?? '';
        } else {
            $redirectUri = "/main.php?p=" . $defaultPage->getPageNumber();
            if ($defaultPage->getUrlOptions() !== null) {
                $redirectUri .= $defaultPage->getUrlOptions();
            }
        }
        return $redirectUri;
    }
}
