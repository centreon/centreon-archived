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
use Security\Domain\Authentication\Model\Session;
use Security\Domain\Authentication\Model\ProviderToken;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Menu\Interfaces\MenuServiceInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Centreon\Domain\Contact\Interfaces\ContactServiceInterface;
use Centreon\Domain\Authentication\UseCase\AuthenticateResponse;
use Security\Domain\Authentication\Exceptions\ProviderException;
use Security\Domain\Authentication\Interfaces\ProviderInterface;
use Centreon\Domain\Authentication\Exception\AuthenticationException;
use Centreon\Domain\Repository\Interfaces\DataStorageEngineInterface;
use Security\Domain\Authentication\Interfaces\ProviderServiceInterface;
use Security\Domain\Authentication\Interfaces\SessionRepositoryInterface;
use Security\Domain\Authentication\Interfaces\AuthenticationServiceInterface;
use Security\Domain\Authentication\Interfaces\AuthenticationRepositoryInterface;

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
     * @var MenuServiceInterface
     */
    private $menuService;

    /**
     * @var AuthenticationRepositoryInterface
     */
    private $authenticationRepository;

    /**
     * @var SessionRepositoryInterface
     */
    private $sessionRepository;

    /**
     * @var DataStorageEngineInterface
     */
    private $dataStorageEngine;

    /**
     * @param string $redirectDefaultPage
     * @param AuthenticationServiceInterface $authenticationService
     * @param ProviderServiceInterface $providerService
     * @param ContactServiceInterface $contactService
     * @param SessionInterface $session
     * @param MenuServiceInterface $menuService
     * @param AuthenticationRepositoryInterface $authenticationRepository
     * @param SessionRepositoryInterface $sessionRepository
     * @param DataStorageEngineInterface $dataStorageEngine
     */
    public function __construct(
        string $redirectDefaultPage,
        AuthenticationServiceInterface $authenticationService,
        ProviderServiceInterface $providerService,
        ContactServiceInterface $contactService,
        SessionInterface $session,
        MenuServiceInterface $menuService,
        AuthenticationRepositoryInterface $authenticationRepository,
        SessionRepositoryInterface $sessionRepository,
        DataStorageEngineInterface $dataStorageEngine
    ) {
        $this->redirectDefaultPage = $redirectDefaultPage;
        $this->authenticationService = $authenticationService;
        $this->providerService = $providerService;
        $this->contactService = $contactService;
        $this->session = $session;
        $this->menuService = $menuService;
        $this->authenticationRepository = $authenticationRepository;
        $this->sessionRepository = $sessionRepository;
        $this->dataStorageEngine = $dataStorageEngine;
    }

    /**
     * Execute authentication scenario and return the redirection URI.
     *
     * @param AuthenticateRequest $request
     * @param AuthenticateResponse $response
     * @throws ProviderException
     * @throws AuthenticationException
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
        $this->startLegacySession($authenticationProvider->getLegacySession());

        /**
         * Search for an already existing and available authentications token.
         * Create a new one if no one are found.
         */
        $authenticationTokens = $this->authenticationService->findAuthenticationTokensByToken($this->session->getId());
        if ($authenticationTokens === null) {
            $this->createAuthenticationTokens(
                $this->session->getId(),
                $authenticationProvider->getConfiguration()->getName(),
                $providerUser,
                $authenticationProvider->getProviderToken($this->session->getId()),
                $authenticationProvider->getProviderRefreshToken($this->session->getId()),
                $request->getClientIp()
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
        $this->setResponseRedirectionUri($request, $response, $providerUser);
    }

    /**
     * Find a provider or throw an Exception.
     *
     * @param string $providerConfigurationName
     * @return ProviderInterface
     * @throws ProviderException
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
            throw ProviderException::providerConfigurationNotFound(
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
        $this->authorizeUserToAuthenticateOrFail($request->getLogin());

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
                "[AUTHENTICATE] Provider can't authenticate successfully user",
                [
                    "provider_name" => $authenticationProvider->getName(),
                    "user" => $request->getLogin()
                ]
            );
            throw AuthenticationException::notAuthenticated();
        }
    }

    /**
     * Check if user is allowed to authenticate or throw an Exception.
     *
     * @param string $userName
     * @throws AuthenticationException
     */
    private function authorizeUserToAuthenticateOrFail(string $userName): void
    {
        $this->debug(
            '[AUTHENTICATE] Check user authorization to log in web application',
            ['user' => $userName]
        );
        $contact = $this->contactService->findByName($userName);
        if ($contact !== null && !$contact->isAllowedToReachWeb()) {
            $this->critical(
                "[AUTHENTICATE] User is not allowed to reach web application",
                [
                    "user" => $userName
                ]
            );
            throw AuthenticationException::notAllowedToReachWebApplication();
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
        $this->info('[AUTHENTICATE] Retrieving user informations from provider');
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
                '[AUTHENTICATE] Provider is allowed to create user. Creating user...',
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
     * @param Centreon $legacySession
     * @throws AuthenticationException
     */
    private function startLegacySession(Centreon $legacySession): void
    {
        $this->info('[AUTHENTICATE] Starting Centreon Session');
        $this->session->start();
        $_SESSION['centreon'] = $legacySession;
    }

    /**
     * Define the redirection uri where user will be redirect once logged.
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
         * Check if a legacy page could be get from the request referer.
         */
        $refererRedirectionPage = $this->getRedirectionPageFromReferer($request);
        if ($refererRedirectionPage !== null) {
            $response->setRedirectionUri(
                $request->getCentreonBaseUri() . $this->buildDefaultRedirectionUri($refererRedirectionPage)
            );
        } elseif ($providerUser->getDefaultPage() !== null && $providerUser->getDefaultPage()->getUrl() !== null) {
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

    /**
     * Get a Page from referer page number.
     *
     * @param AuthenticateRequest $request
     * @return Page|null
     */
    private function getRedirectionPageFromReferer(AuthenticateRequest $request): ?Page
    {
        $refererRedirectionPage = null;
        if ($request->getRefererQueryParameters() !== null) {
            $queryParameters = [];
            parse_str($request->getRefererQueryParameters(), $queryParameters);
            if (array_key_exists('redirect', $queryParameters)) {
                $redirectionPageParameters = [];
                parse_str($queryParameters['redirect'], $redirectionPageParameters);
                if (array_key_exists('p', $redirectionPageParameters)) {
                    $refererRedirectionPage = $this->menuService->findPageByTopologyPageNumber(
                        (int) $redirectionPageParameters['p']
                    );
                    unset($redirectionPageParameters['p']);
                    if ($refererRedirectionPage !== null) {
                        $refererRedirectionPage->setUrlOptions('&' . http_build_query($redirectionPageParameters));
                    }
                }
            }
        }

        return $refererRedirectionPage;
    }

    /**
     * create Authentication tokens.
     *
     * @param string $sessionToken
     * @param string $providerConfigurationName
     * @param ContactInterface $contact
     * @param ProviderToken $providerToken
     * @param ProviderToken|null $providerRefreshToken
     * @return void
     */
    private function createAuthenticationTokens(
        string $sessionToken,
        string $providerConfigurationName,
        ContactInterface $contact,
        ProviderToken $providerToken,
        ?ProviderToken $providerRefreshToken,
        string $clientIp
    ): void {
        $providerConfiguration = $this->providerService->findProviderConfigurationByConfigurationName(
            $providerConfigurationName
        );

        $providerConfigurationId = null;
        if ($providerConfiguration !== null && $providerConfiguration->getId() !== null) {
            $providerConfigurationId = $providerConfiguration->getId();
        } else {
            throw ProviderException::providerConfigurationNotFound($providerConfigurationName);
        }

        $isAlreadyInTransaction = $this->dataStorageEngine->isAlreadyinTransaction();

        if (!$isAlreadyInTransaction) {
            $this->dataStorageEngine->startTransaction();
        }
        try {
            $session = new Session($sessionToken, $contact->getId(), $clientIp);
            $this->sessionRepository->addSession($session);
            $this->authenticationRepository->addAuthenticationTokens(
                $sessionToken,
                $providerConfigurationId,
                $contact->getId(),
                $providerToken,
                $providerRefreshToken
            );
            if (!$isAlreadyInTransaction) {
                $this->dataStorageEngine->commitTransaction();
            }
        } catch (\Exception $ex) {
            if (!$isAlreadyInTransaction) {
                $this->dataStorageEngine->rollbackTransaction();
            }
            throw AuthenticationException::addAuthenticationToken($ex);
        }
    }
}
