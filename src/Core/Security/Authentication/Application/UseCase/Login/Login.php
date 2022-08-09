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

use Centreon\Domain\Authentication\Exception\AuthenticationException as LegacyAuthenticationException;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Log\LoggerTrait;
use Centreon\Domain\Menu\Model\Page;
use Centreon\Domain\Repository\Interfaces\DataStorageEngineInterface;
use Core\Application\Common\UseCase\PresenterInterface;
use Core\Application\Common\UseCase\UnauthorizedResponse;
use Core\Security\Authentication\Application\Provider\ProviderAuthenticationFactoryInterface;
use Core\Security\Authentication\Application\Provider\ProviderAuthenticationInterface;
use Core\Security\Authentication\Application\Repository\ReadTokenRepositoryInterface;
use Core\Security\Authentication\Application\Repository\WriteSessionRepositoryInterface;
use Core\Security\Authentication\Application\Repository\WriteSessionTokenRepositoryInterface;
use Core\Security\Authentication\Application\Repository\WriteTokenRepositoryInterface;
use Core\Security\Authentication\Application\UseCase\LoginSession\PasswordExpiredResponse;
use Core\Security\Authentication\Domain\Exception\AuthenticationException;
use Core\Security\Authentication\Domain\Exception\PasswordExpiredException;
use Core\Security\Authentication\Domain\Model\NewProviderToken;
use Core\Security\Authentication\Domain\Model\ProviderToken;
use Core\Security\Authentication\Infrastructure\Provider\AclUpdaterInterface;
use Core\Security\ProviderConfiguration\Domain\Model\Configuration;
use Core\Security\ProviderConfiguration\Domain\Model\Provider;
use Security\Domain\Authentication\Model\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

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
     */
    public function __construct(
        private ProviderAuthenticationFactoryInterface             $providerFactory,
        private SessionInterface                     $session,
        private DataStorageEngineInterface           $dataStorageEngine,
        private WriteSessionRepositoryInterface      $sessionRepository,
        private ReadTokenRepositoryInterface         $readTokenRepository,
        private WriteTokenRepositoryInterface        $writeTokenRepository,
        private WriteSessionTokenRepositoryInterface $writeSessionTokenRepository,
        private AclUpdaterInterface                  $aclUpdater
    )
    {
    }

    /**
     * @param LoginRequest $loginRequest
     * @param PresenterInterface $presenter
     * @return void
     * @throws AuthenticationException
     */
    public function __invoke(LoginRequest $loginRequest, PresenterInterface $presenter): void
    {
        try {
            $this->provider = $this->providerFactory->create($loginRequest->getProviderName());

            $this->provider->authenticateOrFail($loginRequest);
            $user = $this->provider->findUserOrFail();

            if ($loginRequest->getProviderName() === Provider::LOCAL && !$user->isAllowedToReachWeb()) {
                throw LegacyAuthenticationException::notAllowedToReachWebApplication();
            }

            if ($this->provider->isAutoImportEnabled()) {
                $this->provider->importUserToDatabase();
            }

            $this->updateACL($user);

            // Start a new session
            if ($this->sessionRepository->start($this->provider->getLegacySession())) {
                if ($this->readTokenRepository->hasAuthenticationTokensByToken($this->session->getId()) === false) {
                    $this->createAuthenticationTokens(
                        $this->session->getId(),
                        $user,
                        $this->provider->getProviderToken(),
                        $this->provider->getProviderRefreshToken(),
                        $loginRequest->getClientIp(),
                    );
                }
            }
        } catch (PasswordExpiredException $e) {
            $response = new PasswordExpiredResponse($e->getMessage());
            $response->setBody([
                'password_is_expired' => true,
            ]);
            $presenter->setResponseStatus($response);
            throw $e;
        } catch (AuthenticationException $e) {
            $presenter->setResponseStatus(new UnauthorizedResponse($e->getMessage()));
            throw $e;
        }

        $presenter->present(new LoginResponse($this->getRedirectionUri($user)));
    }

    /**
     * Create Authentication tokens.
     *
     * @param string $sessionToken
     * @param ContactInterface $contact
     * @param ProviderToken $providerToken
     * @param ProviderToken|null $providerRefreshToken
     * @param string|null $clientIp
     * @throws AuthenticationException
     */
    private function createAuthenticationTokens(
        string            $sessionToken,
        ContactInterface  $contact,
        NewProviderToken  $providerToken,
        ?NewProviderToken $providerRefreshToken,
        ?string           $clientIp,
    ): void
    {
        // TODO Move into startTransaction() ?
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
     * @param ContactInterface $providerUser
     * @return string
     */
    private function getRedirectionUri(ContactInterface $authenticatedUser): string
    {
        if ($authenticatedUser->getDefaultPage()?->getUrl() !== null) {
            return $this->buildDefaultRedirectionUri($authenticatedUser->getDefaultPage());
        }

        // TODO inject this in config
        return "/monitoring/resources";
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
     * @param ContactInterface $user
     * @return void
     */
    private function updateACL(ContactInterface $user): void
    {
        $this->aclUpdater->updateForProviderAndUser($this->provider, $user);
    }
}