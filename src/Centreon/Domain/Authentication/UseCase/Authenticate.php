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

use Centreon\Domain\Log\LoggerTrait;
use Centreon\Domain\Menu\Model\Page;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Centreon\Domain\Contact\Interfaces\ContactServiceInterface;
use Centreon\Domain\Authentication\UseCase\AuthenticateResponse;
use Centreon\Domain\Authentication\Exception\AuthenticationException;
use Security\Domain\Authentication\Exceptions\ProviderServiceException;
use Security\Domain\Authentication\Interfaces\ProviderServiceInterface;
use Security\Domain\Authentication\Exceptions\AuthenticationServiceException;
use Security\Domain\Authentication\Interfaces\AuthenticationServiceInterface;

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
            '[AUTHENTICATE] Beginning authentication on provider',
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
        $this->debug(
            '[AUTHENTICATE] Authentication using provider',
            ['provider_name' => $request->getProviderConfigurationName()]
        );

        /**
         * Authenticate using the provider chosen in the request.
         */
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

        $this->info('Retrieving user informations from provider');

        /**
         * Check if the user exists in the idP
         */
        $providerUser = $authenticationProvider->getUser();
        if ($providerUser === null) {
            $this->critical(
                '[AUTHENTICATE] No contact could be found from provider',
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
                    '[AUTHENTICATE] Provider is allow to create user. Creating user...',
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
         * Get the default page informations.
         */
        $this->contactService->updateUserDefaultPage($providerUser);

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
            $this->debug(
                '[AUTHENTICATE] Creating authentication tokens for user',
                ['user' => $providerUser->getAlias()]
            );
            $this->authenticationService->createAuthenticationTokens(
                $this->session->getId(),
                $request->getProviderConfigurationName(),
                $providerUser,
                $authenticationProvider->getProviderToken($this->session->getId()),
                $authenticationProvider->getProviderRefreshToken($this->session->getId())
            );
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
