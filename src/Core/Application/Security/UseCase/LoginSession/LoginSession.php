<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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

namespace Core\Application\Security\UseCase\LoginSession;

use Core\Application\Common\UseCase\UnauthorizedResponse;
use Centreon\Domain\Authentication\Exception\AuthenticationException as LegacyAuthenticationException;
use Core\Domain\Security\Authentication\AuthenticationException;
use Core\Domain\Security\Authentication\PasswordExpiredException;
use Centreon\Domain\Log\LoggerTrait;
use Centreon;
use Centreon\Domain\Menu\Model\Page;
use Security\Domain\Authentication\Model\Session;
use Symfony\Component\HttpFoundation\RequestStack;
use Security\Domain\Authentication\Model\ProviderToken;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Menu\Interfaces\MenuServiceInterface;
use Centreon\Domain\Contact\Interfaces\ContactServiceInterface;
use Security\Domain\Authentication\Exceptions\ProviderException;
use Security\Domain\Authentication\Interfaces\ProviderInterface;
use Centreon\Domain\Repository\Interfaces\DataStorageEngineInterface;
use Security\Domain\Authentication\Interfaces\ProviderServiceInterface;
use Security\Domain\Authentication\Interfaces\SessionRepositoryInterface;
use Security\Domain\Authentication\Interfaces\AuthenticationServiceInterface;
use Security\Domain\Authentication\Interfaces\AuthenticationRepositoryInterface;

class LoginSession
{
    use LoggerTrait;

    /**
     * @param string $redirectDefaultPage
     * @param AuthenticationServiceInterface $authenticationService
     * @param ProviderServiceInterface $providerService
     * @param ContactServiceInterface $contactService
     * @param RequestStack $requestStack
     * @param MenuServiceInterface $menuService
     * @param AuthenticationRepositoryInterface $authenticationRepository
     * @param SessionRepositoryInterface $sessionRepository
     * @param DataStorageEngineInterface $dataStorageEngine
     */
    public function __construct(
        private string $redirectDefaultPage,
        private AuthenticationServiceInterface $authenticationService,
        private ProviderServiceInterface $providerService,
        private ContactServiceInterface $contactService,
        private RequestStack $requestStack,
        private MenuServiceInterface $menuService,
        private AuthenticationRepositoryInterface $authenticationRepository,
        private SessionRepositoryInterface $sessionRepository,
        private DataStorageEngineInterface $dataStorageEngine,
    ) {
    }

    /**
     * @param LoginSessionPresenterInterface $presenter
     * @param LoginSessionRequest $request
     */
    public function __invoke(
        LoginSessionPresenterInterface $presenter,
        LoginSessionRequest $request,
    ): void {
        $this->info('Processing session login...');

        try {
            $this->authorizeUserToAuthenticateOrFail($request->login);

            $authenticationProvider = $this->findProviderOrFail($request->providerConfigurationName);
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
            $currentRequest = $this->requestStack->getCurrentRequest();
            if ($currentRequest !== null) {
                $authenticationTokens = $this->authenticationService->findAuthenticationTokensByToken(
                    $currentRequest->getSession()->getId()
                );

                if ($authenticationTokens === null) {
                    $this->createAuthenticationTokens(
                        $currentRequest->getSession()->getId(),
                        $authenticationProvider->getConfiguration()->getName(),
                        $providerUser,
                        $authenticationProvider->getProviderToken(
                            $currentRequest->getSession()->getId()
                        ),
                        $authenticationProvider->getProviderRefreshToken(
                            $currentRequest->getSession()->getId()
                        ),
                        $request->clientIp,
                    );
                }
            }
        } catch (PasswordExpiredException $e) {
            $response = new PasswordExpiredResponse($e->getMessage());
            $response->setBody([
                'password_is_expired' => true,
            ]);
            $presenter->setResponseStatus($response);
            return;
        } catch (AuthenticationException $e) {
            $presenter->setResponseStatus(new UnauthorizedResponse($e->getMessage()));
            return;
        }

        $this->debug(
            "[AUTHENTICATE] Authentication success",
            [
                "provider_name" => $request->providerConfigurationName,
                "contact_id" => $providerUser->getId(),
                "contact_alias" => $providerUser->getAlias()
            ]
        );

        /**
         * Define the redirection uri where user will be redirect once logged.
         */
        $redirectionUri = $this->getRedirectionUri($request, $providerUser);

        $presenter->present($this->createResponse($redirectionUri));
    }

    /**
     * @param string $redirectionUri
     * @return LoginSessionResponse
     */
    private function createResponse(string $redirectionUri): LoginSessionResponse
    {
        $response = new LoginSessionResponse();
        $response->redirectionUri = $redirectionUri;

        return $response;
    }

    /**
     * Check if user is allowed to authenticate or throw an Exception.
     *
     * @param string $userName
     * @throws LegacyAuthenticationException
     */
    private function authorizeUserToAuthenticateOrFail(string $userName): void
    {
        $this->debug(
            '[AUTHENTICATE] Check user authorization to log in web application',
            ['user' => $userName]
        );

        $contact = $this->contactService->findByName($userName);

        if ($contact === null) {
            $this->debug(
                '[AUTHENTICATE] User is not found locally so authorization is delegated to the provider',
                ['user' => $userName]
            );
        }

        if ($contact !== null && !$contact->isAllowedToReachWeb()) {
            $this->critical(
                '[AUTHENTICATE] User is not allowed to reach web application',
                [
                    'user' => $userName
                ]
            );
            throw LegacyAuthenticationException::notAllowedToReachWebApplication();
        }
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
     * @param LoginSessionRequest $request
     * @throws LegacyAuthenticationException
     */
    private function authenticateOrFail(ProviderInterface $authenticationProvider, LoginSessionRequest $request): void
    {
        /**
         * Authenticate using the provider chosen in the request.
         */
        $this->debug(
            '[AUTHENTICATE] Authentication using provider',
            ['provider_name' => $request->providerConfigurationName]
        );

        $authenticationProvider->authenticateOrFail([
            'login' => $request->login,
            'password' => $request->password
        ]);
    }

    /**
     * Retrieve user from provider or throw an Exception.
     *
     * @param ProviderInterface $authenticationProvider
     * @return ContactInterface
     * @throws LegacyAuthenticationException
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
            throw LegacyAuthenticationException::userNotFound();
        }

        return $providerUser;
    }

    /**
     * Create the user in Centreon or throw an Exception.
     *
     * @param ProviderInterface $authenticationProvider
     * @param ContactInterface $providerUser
     * @throws LegacyAuthenticationException
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
            throw LegacyAuthenticationException::userNotFoundAndCannotBeCreated();
        }
    }

    /**
     * Start the Centreon session.
     *
     * @param Centreon $legacySession
     * @throws LegacyAuthenticationException
     */
    private function startLegacySession(Centreon $legacySession): void
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        if ($currentRequest !== null) {
            $this->info('[AUTHENTICATE] Starting Centreon Session');
            $currentRequest->getSession()->start();
            $currentRequest->getSession()->set('centreon', $legacySession);
            $_SESSION['centreon'] = $legacySession;
        }
    }

    /**
     * Get the redirection uri where user will be redirect once logged.
     *
     * @param LoginSessionRequest $request
     * @param ContactInterface $providerUser
     * @return string
     */
    private function getRedirectionUri(
        LoginSessionRequest $request,
        ContactInterface $providerUser,
    ): string {
        /**
         * Check if a legacy page could be get from the request referer.
         */
        $refererRedirectionPage = $this->getRedirectionPageFromReferer($request);
        if ($refererRedirectionPage !== null) {
            $redirectionUri = $this->buildDefaultRedirectionUri($refererRedirectionPage);
        } elseif ($providerUser->getDefaultPage() !== null && $providerUser->getDefaultPage()->getUrl() !== null) {
            $redirectionUri = $this->buildDefaultRedirectionUri($providerUser->getDefaultPage());
        } else {
            $redirectionUri = $this->redirectDefaultPage;
        }

        return $redirectionUri;
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
            $redirectUri = $defaultPage->getUrl();
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
     * @param LoginSessionRequest $request
     * @return Page|null
     */
    private function getRedirectionPageFromReferer(LoginSessionRequest $request): ?Page
    {
        $refererRedirectionPage = null;
        if ($request->refererQueryParameters !== null) {
            $queryParameters = [];
            parse_str($request->refererQueryParameters, $queryParameters);
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
     * @param string|null $clientIp
     */
    private function createAuthenticationTokens(
        string $sessionToken,
        string $providerConfigurationName,
        ContactInterface $contact,
        ProviderToken $providerToken,
        ?ProviderToken $providerRefreshToken,
        ?string $clientIp,
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
            throw LegacyAuthenticationException::addAuthenticationToken($ex);
        }
    }
}
