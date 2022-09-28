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

namespace Core\Security\Authentication\Application\UseCase\Login;

use Centreon\Domain\Log\LoggerTrait;
use Centreon\Domain\Menu\Model\Page;
use Security\Domain\Authentication\Model\Session;
use Core\Application\Common\UseCase\PresenterInterface;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Menu\Interfaces\MenuServiceInterface;
use Core\Application\Common\UseCase\UnauthorizedResponse;
use Core\Security\Authentication\Application\Provider\ProviderAuthenticationFactoryInterface;
use Core\Security\Authentication\Application\Provider\ProviderAuthenticationInterface;
use Core\Security\Authentication\Application\Repository\ReadTokenRepositoryInterface;
use Core\Security\Authentication\Application\Repository\WriteSessionRepositoryInterface;
use Core\Security\Authentication\Application\Repository\WriteSessionTokenRepositoryInterface;
use Core\Security\Authentication\Application\Repository\WriteTokenRepositoryInterface;
use Core\Security\Authentication\Domain\Exception\AclConditionsException;
use Core\Security\ProviderConfiguration\Domain\Model\Provider;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Core\Security\Authentication\Domain\Model\NewProviderToken;
use Centreon\Domain\Repository\Interfaces\DataStorageEngineInterface;
use Core\Application\Common\UseCase\ErrorAuthenticationConditionsResponse;
use Core\Security\Authentication\Domain\Exception\AuthenticationException;
use Core\Security\Authentication\Domain\Exception\PasswordExpiredException;
use Core\Security\Authentication\Infrastructure\Provider\AclUpdaterInterface;
use Core\Security\Authentication\Domain\Exception\AuthenticationConditionsException;
use Centreon\Domain\Authentication\Exception\AuthenticationException as LegacyAuthenticationException;
use Core\Application\Common\UseCase\ErrorResponse;

final class Login
{
    use LoggerTrait;

    /** @var ProviderAuthenticationInterface */
    private ProviderAuthenticationInterface $provider;

    /**
     * @param ProviderAuthenticationFactoryInterface $providerFactory
     * @param SessionInterface $session
     * @param DataStorageEngineInterface $dataStorageEngine
     * @param WriteSessionRepositoryInterface $sessionRepository
     * @param ReadTokenRepositoryInterface $readTokenRepository
     * @param WriteTokenRepositoryInterface $writeTokenRepository
     * @param WriteSessionTokenRepositoryInterface $writeSessionTokenRepository
     * @param AclUpdaterInterface $aclUpdater
     * @param MenuServiceInterface $menuService
     * @param string $defaultRedirectUri
     */
    public function __construct(
        private ProviderAuthenticationFactoryInterface $providerFactory,
        private SessionInterface $session,
        private DataStorageEngineInterface $dataStorageEngine,
        private WriteSessionRepositoryInterface $sessionRepository,
        private ReadTokenRepositoryInterface $readTokenRepository,
        private WriteTokenRepositoryInterface $writeTokenRepository,
        private WriteSessionTokenRepositoryInterface $writeSessionTokenRepository,
        private AclUpdaterInterface $aclUpdater,
        private MenuServiceInterface $menuService,
        private string $defaultRedirectUri
    ) {
    }

    /**
     * @param LoginRequest $loginRequest
     * @param PresenterInterface $presenter
     * @throws AuthenticationException
     */
    public function __invoke(LoginRequest $loginRequest, PresenterInterface $presenter): void
    {
        try {
            $this->provider = $this->providerFactory->create($loginRequest->providerName);

            $this->provider->authenticateOrFail($loginRequest);
            $user = $this->provider->findUserOrFail();

            if ($loginRequest->providerName === Provider::LOCAL && !$user->isAllowedToReachWeb()) {
                throw LegacyAuthenticationException::notAllowedToReachWebApplication();
            }

            if ($this->provider->isAutoImportEnabled()) {
                $this->provider->importUser();
            }

            $this->updateACL($user);

            // Start a new session
            if ($this->sessionRepository->start($this->provider->getLegacySession())) {
                if ($this->readTokenRepository->hasAuthenticationTokensByToken($this->session->getId()) === false) {
                    $this->createAuthenticationTokens(
                        $this->session->getId(),
                        $user,
                        $this->provider->getProviderToken($this->session->getId()),
                        $this->provider->getProviderRefreshToken(),
                        $loginRequest->clientIp
                    );
                }
            }

            $presenter->setResponseStatus(
                new LoginResponse($this->getRedirectionUri($user, $loginRequest->refererQueryParameters))
            );

            $presenter->present(
                new LoginResponse($this->getRedirectionUri($user, $loginRequest->refererQueryParameters))
            );
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
        } catch (AclConditionsException $e) {
            $presenter->setResponseStatus(new ErrorAclConditionsResponse($e->getMessage()));
        } catch (AuthenticationConditionsException $ex) {
            $presenter->setResponseStatus(new ErrorAuthenticationConditionsResponse($ex->getMessage()));
            return;
        } catch (\Throwable $ex) {
            $presenter->setResponseStatus(new ErrorResponse($ex->getMessage()));
            return;
        }
    }

    /**
     * Create Authentication tokens.
     *
     * @param string $sessionToken
     * @param ContactInterface $contact
     * @param NewProviderToken $providerToken
     * @param NewProviderToken|null $providerRefreshToken
     * @param string|null $clientIp
     * @throws AuthenticationException
     */
    private function createAuthenticationTokens(
        string $sessionToken,
        ContactInterface $contact,
        NewProviderToken $providerToken,
        ?NewProviderToken $providerRefreshToken,
        ?string $clientIp,
    ): void {

        $isAlreadyInTransaction = $this->dataStorageEngine->isAlreadyinTransaction();

        if (!$isAlreadyInTransaction) {
            $this->dataStorageEngine->startTransaction();
        }

        try {
            $session = new Session($sessionToken, $contact->getId(), $clientIp);
            $this->writeSessionTokenRepository->createSession($session);
            $this->writeTokenRepository->createAuthenticationTokens(
                $sessionToken,
                $this->provider->getConfiguration()->getId(),
                $contact->getId(),
                $providerToken,
                $providerRefreshToken
            );
            if (!$isAlreadyInTransaction) {
                $this->dataStorageEngine->commitTransaction();
            }
        } catch (\Exception) {
            if (!$isAlreadyInTransaction) {
                $this->dataStorageEngine->rollbackTransaction();
            }

            throw AuthenticationException::notAuthenticated();
        }
    }

    /**
     * Get the redirection uri where user will be redirect once logged.
     *
     * @param ContactInterface $authenticatedUser
     * @param string|null $refererQueryParameters
     * @return string
     */
    private function getRedirectionUri(ContactInterface $authenticatedUser, ?string $refererQueryParameters): string
    {
        $redirectionUri = $this->defaultRedirectUri;

        $refererRedirectionPage = $this->getRedirectionPageFromRefererQueryParameters($refererQueryParameters);
        if ($refererRedirectionPage !== null) {
            $redirectionUri = $this->buildDefaultRedirectionUri($refererRedirectionPage);
        } elseif ($authenticatedUser->getDefaultPage()?->getUrl() !== null) {
            $redirectionUri = $this->buildDefaultRedirectionUri($authenticatedUser->getDefaultPage());
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
            return $defaultPage->getUrl();
        }
        $redirectUri = "/main.php?p=" . $defaultPage->getPageNumber();
        if ($defaultPage->getUrlOptions() !== null) {
            $redirectUri .= $defaultPage->getUrlOptions();
        }

        return $redirectUri;
    }

    /**
     * Get a Page from referer page number.
     *
     * @param string|null $refererQueryParameters
     * @return Page|null
     */
    private function getRedirectionPageFromRefererQueryParameters(?string $refererQueryParameters): ?Page
    {
        if ($refererQueryParameters === null) {
            return null;
        }

        $refererRedirectionPage = null;
        $queryParameters = [];
        parse_str($refererQueryParameters, $queryParameters);
        if (array_key_exists('redirect', $queryParameters)) {
            $redirectionPageParameters = [];
            parse_str($queryParameters['redirect'], $redirectionPageParameters);
            if (array_key_exists('p', $redirectionPageParameters)) {
                $refererRedirectionPage = $this->menuService->findPageByTopologyPageNumber(
                    (int)$redirectionPageParameters['p']
                );
                unset($redirectionPageParameters['p']);
                if ($refererRedirectionPage !== null) {
                    $refererRedirectionPage->setUrlOptions('&' . http_build_query($redirectionPageParameters));
                }
            }
        }

        return $refererRedirectionPage;
    }

    /**
     * @param ContactInterface $user
     */
    private function updateACL(ContactInterface $user): void
    {
        $this->aclUpdater->updateForProviderAndUser($this->provider, $user);
    }
}
