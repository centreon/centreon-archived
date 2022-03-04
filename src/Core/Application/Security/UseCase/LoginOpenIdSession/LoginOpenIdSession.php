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

namespace Core\Application\Security\UseCase\LoginOpenIdSession;

use Pimple\Container;
use Centreon\Domain\Log\LoggerTrait;
use Centreon\Domain\Menu\Model\Page;
use Core\Domain\Security\Provider\OpenIdProvider;
use Security\Domain\Authentication\Model\Session;
use Symfony\Component\HttpFoundation\RequestStack;
use Security\Domain\Authentication\Model\ProviderToken;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Menu\Interfaces\MenuServiceInterface;
use Core\Domain\Security\Authentication\AuthenticationException;
use Centreon\Domain\Repository\Interfaces\DataStorageEngineInterface;
use Centreon\Infrastructure\Service\Exception\NotFoundException;
use Security\Domain\Authentication\Interfaces\SessionRepositoryInterface;
use Security\Domain\Authentication\Interfaces\AuthenticationServiceInterface;
use Security\Domain\Authentication\Interfaces\AuthenticationRepositoryInterface;
use Core\Application\Security\ProviderConfiguration\OpenId\Repository\ReadOpenIdConfigurationRepositoryInterface;
use Core\Domain\Security\ProviderConfiguration\OpenId\Model\OpenIdConfiguration;

class LoginOpenIdSession
{
    use LoggerTrait;

    /**
     * @param ReadOpenIdConfigurationRepositoryInterface $repository
     * @param OpenIdProvider $provider
     * @param RequestStack $requestStack
     */
    public function __construct(
        private string $redirectDefaultPage,
        private ReadOpenIdConfigurationRepositoryInterface $repository,
        private OpenIdProvider $provider,
        private RequestStack $requestStack,
        private Container $dependencyInjector,
        private AuthenticationServiceInterface $authenticationService,
        private MenuServiceInterface $menuService,
        private AuthenticationRepositoryInterface $authenticationRepository,
        private SessionRepositoryInterface $sessionRepository,
        private DataStorageEngineInterface $dataStorageEngine,
    ) {
    }

    /**
     * @param LoginOpenIdSessionRequest $request
     * @param LoginOpenIdSessionPresenterInterface $presenter
     */
    public function __invoke(LoginOpenIdSessionRequest $request, LoginOpenIdSessionPresenterInterface $presenter)
    {
        global $pearDB;
        $pearDB = $this->dependencyInjector['configuration_db'];

        $openIdProviderConfiguration = $this->repository->findConfiguration();
        if ($openIdProviderConfiguration === null) {
            //throw exception
        }

        $this->provider->setConfiguration($openIdProviderConfiguration);

        try {
            $this->provider->authenticateOrFail($request->authorizationCode);
            $user = $this->provider->getUser();
            if ($user === null) {
                if (!$this->provider->canCreateUser()) {
                    throw new NotFoundException('User not found');
                }
                $this->provider->createUser();
                $user = $this->provider->getUser();
                if ($user === null) {
                    throw new NotFoundException('User not found');
                }
            }
            $sessionUserInfos = [
                'contact_id' => $user->getId(),
                'contact_name' => $user->getName(),
                'contact_alias' => $user->getAlias(),
                'contact_email' => $user->getEmail(),
                'contact_lang' => $user->getLang(),
                'contact_passwd' => $user->getEncodedPassword(),
                'contact_autologin_key' => '',
                'contact_admin' => $user->isAdmin() ? '1' : '0',
                'default_page' => $user->getDefaultPage(),
                'contact_location' => $user->getLocale(),
                'show_deprecated_pages' => $user->isUsingDeprecatedPages(),
                'reach_api' => $user->hasAccessToApiConfiguration() ? 1 : 0,
                'reach_api_rt' => $user->hasAccessToApiRealTime() ? 1 : 0
            ];
            $this->provider->setLegacySession(new \Centreon($sessionUserInfos));
            $this->startLegacySession($this->provider->getLegacySession());

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
                        $user,
                        $this->provider->getProviderToken(),
                        $this->provider->getProviderRefreshToken(),
                        $request->clientIp,
                    );
                }
            }
        } catch (AuthenticationException | NotFoundException $e) {
            $presenter->present($this->createResponse(null, $e->getMessage()));
            return;
        }

        $this->debug(
            "[AUTHENTICATE] Authentication success",
            [
                "provider_name" => OpenIdConfiguration::NAME,
                "contact_id" => $user->getId(),
                "contact_alias" => $user->getAlias()
            ]
        );

        /**
         * Define the redirection uri where user will be redirect once logged.
         */
        $redirectionUri = $this->getRedirectionUri($user);
        $presenter->present($this->createResponse($redirectionUri));

        //$presenter->present($this->createResponse($redirectionUri));
    }

    /**
     * Start the Centreon session.
     *
     * @param Centreon $legacySession
     * @throws LegacyAuthenticationException
     */
    private function startLegacySession(\Centreon $legacySession): void
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
        ContactInterface $providerUser,
    ): string {
        /**
         * Check if a legacy page could be get from the request referer.
         */
        if ($providerUser->getDefaultPage() !== null && $providerUser->getDefaultPage()->getUrl() !== null) {
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
     * create Authentication tokens.
     *
     * @param string $sessionToken
     * @param ContactInterface $contact
     * @param ProviderToken $providerToken
     * @param ProviderToken|null $providerRefreshToken
     * @param string|null $clientIp
     */
    private function createAuthenticationTokens(
        string $sessionToken,
        ContactInterface $contact,
        ProviderToken $providerToken,
        ?ProviderToken $providerRefreshToken,
        ?string $clientIp,
    ): void {
        $isAlreadyInTransaction = $this->dataStorageEngine->isAlreadyinTransaction();

        if (!$isAlreadyInTransaction) {
            $this->dataStorageEngine->startTransaction();
        }
        try {
            $session = new Session($sessionToken, $contact->getId(), $clientIp);
            $this->sessionRepository->addSession($session);
            $this->authenticationRepository->addAuthenticationTokens(
                $sessionToken,
                $this->provider->getConfiguration()->getId(),
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
            throw AuthenticationException::notAuthenticated();
        }
    }

    /**
     * @param string $redirectionUri
     * @return LoginOpenIdSessionResponse
     */
    private function createResponse(?string $redirectionUri, ?string $error = null): LoginOpenIdSessionResponse
    {
        $response = new LoginOpenIdSessionResponse();
        $response->redirectionUri = $redirectionUri;
        $response->error = $error;

        return $response;
    }
}
