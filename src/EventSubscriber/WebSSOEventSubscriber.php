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

namespace EventSubscriber;

use Centreon;
use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Contact\Interfaces\ContactRepositoryInterface;
use Centreon\Domain\Log\LoggerTrait;
use Centreon\Domain\Option\Interfaces\OptionServiceInterface;
use Centreon\Domain\Repository\Interfaces\DataStorageEngineInterface;
use Core\Security\Authentication\Application\Provider\ProviderAuthenticationFactoryInterface;
use Core\Security\Authentication\Application\Provider\ProviderAuthenticationInterface;
use Core\Security\Authentication\Application\Repository\WriteSessionRepositoryInterface;
use Core\Security\Authentication\Application\Repository\WriteTokenRepositoryInterface;
use Core\Security\Authentication\Application\UseCase\Login\LoginRequest;
use Core\Security\Authentication\Domain\Exception\AuthenticationException;
use Core\Security\Authentication\Domain\Exception\SSOAuthenticationException;
use Core\Security\Authentication\Domain\Model\NewProviderToken;
use Core\Security\Authentication\Domain\Model\ProviderToken;
use Core\Security\Authentication\Infrastructure\Provider\WebSSO;
use Core\Security\ProviderConfiguration\Application\WebSSO\Repository\ReadWebSSOConfigurationRepositoryInterface;
use Core\Security\ProviderConfiguration\Domain\Model\Provider;
use DateInterval;
use DateTimeImmutable;
use Exception;
use Security\Domain\Authentication\Interfaces\AuthenticationRepositoryInterface;
use Security\Domain\Authentication\Interfaces\AuthenticationServiceInterface;
use Security\Domain\Authentication\Interfaces\SessionRepositoryInterface;
use Security\Domain\Authentication\Model\Session;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class WebSSOEventSubscriber implements EventSubscriberInterface
{
    use LoggerTrait;

    /**
     * @param AuthenticationServiceInterface $authenticationService
     * @param SessionRepositoryInterface $sessionRepository
     * @param DataStorageEngineInterface $dataStorageEngine
     * @param OptionServiceInterface $optionService
     * @param WriteTokenRepositoryInterface $writeTokenRepository
     * @param WriteSessionRepositoryInterface $writeSessionRepository
     * @param ProviderAuthenticationFactoryInterface $providerFactory
     */
    public function __construct(
        private AuthenticationServiceInterface $authenticationService,
        private SessionRepositoryInterface $sessionRepository,
        private DataStorageEngineInterface $dataStorageEngine,
        private OptionServiceInterface $optionService,
        private WriteTokenRepositoryInterface $writeTokenRepository,
        private WriteSessionRepositoryInterface $writeSessionRepository,
        private ProviderAuthenticationFactoryInterface $providerFactory
    ) {
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        $events = [];
        //Register this event only if its not an upgrade or fresh install
        if (
            file_exists(_CENTREON_ETC_ . DIRECTORY_SEPARATOR . 'centreon.conf.php')
            && !is_dir(_CENTREON_PATH_ . DIRECTORY_SEPARATOR . 'www' . DIRECTORY_SEPARATOR . 'install')
        ) {
            $events = [
                KernelEvents::REQUEST => [
                    ['loginWebSSOUser', 34]
                ],
            ];
        }
        return $events;
    }

    /**
     * login User with Web SSO
     *
     * @param RequestEvent $event
     * @throws AuthenticationException
     * @throws Centreon\Domain\Authentication\Exception\AuthenticationException
     * @throws SSOAuthenticationException
     */
    public function loginWebSSOUser(RequestEvent $event): void
    {
        /** @var WebSSO $provider */
        $provider = $this->providerFactory->create(Provider::WEB_SSO);
        $configuration = $provider->getConfiguration();
        if (!$configuration->isActive()) {
            return;
        }

        $request = $event->getRequest();
        $isValidToken = $this->authenticationService->isValidToken($request->getSession()->getId());
        if ($isValidToken) {
            return;
        }

        $this->info('Starting authentication with WebSSO');
        $provider->authenticateOrFail(
            LoginRequest::createForSSO($request->getClientIp())
        );

        $user = $provider->findUserOrFail();
        $this->createSession($request, $provider);
        $this->info('Authenticated successfully', ['user' => $user->getAlias()]);
    }

    /**
     * Create the session
     *
     * @param Request $request
     * @param ProviderAuthenticationInterface $provider
     * @throws AuthenticationException
     * @throws Centreon\Domain\Authentication\Exception\AuthenticationException
     */
    private function createSession(Request $request, ProviderAuthenticationInterface $provider): void
    {
        $this->debug('Creating session');

        if ($this->writeSessionRepository->start($provider->getLegacySession())) {
            $sessionId = $request->getSession()->getId();
            $this->createTokenIfNotExist(
                $sessionId,
                $provider->getConfiguration()->getId(),
                $provider->getAuthenticatedUser(),
                $request->getClientIp()
            );
            $request->headers->set('Set-Cookie', "PHPSESSID=" . $sessionId);
        }
    }

    /**
     * Create token if not exist
     *
     * @param string $sessionId
     * @param integer $webSSOConfigurationId
     * @param ContactInterface $user
     * @param string $clientIp
     * @throws AuthenticationException
     * @throws Centreon\Domain\Authentication\Exception\AuthenticationException
     */
    private function createTokenIfNotExist(
        string $sessionId,
        int $webSSOConfigurationId,
        ContactInterface $user,
        string $clientIp
    ): void {
        $this->info('creating token');
        $authenticationTokens = $this->authenticationService->findAuthenticationTokensByToken(
            $sessionId
        );
        if ($authenticationTokens === null) {
            $sessionExpireOption = $this->optionService->findSelectedOptions(['session_expire']);
            $sessionExpirationDelay = (int) $sessionExpireOption[0]->getValue();
            $token = new ProviderToken(
                $webSSOConfigurationId,
                $sessionId,
                new DateTimeImmutable(),
                (new DateTimeImmutable())->add(new DateInterval('PT' . $sessionExpirationDelay . 'M'))
            );
            $this->createAuthenticationTokens(
                $sessionId,
                $user,
                $token,
                null,
                $clientIp,
            );
        }
    }

    /**
     * create Authentication tokens
     *
     * @param string $sessionToken
     * @param ContactInterface $contact
     * @param ProviderToken $providerToken
     * @param ProviderToken|null $providerRefreshToken
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
            $this->sessionRepository->addSession($session);
            $this->writeTokenRepository->createAuthenticationTokens(
                $sessionToken,
                $providerToken->getId(),
                $contact->getId(),
                $providerToken,
                $providerRefreshToken
            );
            if (!$isAlreadyInTransaction) {
                $this->dataStorageEngine->commitTransaction();
            }
        } catch (Exception $ex) {
            $this->error('Unable to create authentication tokens', [
                'trace' => $ex->getTraceAsString()
            ]);
            if (!$isAlreadyInTransaction) {
                $this->dataStorageEngine->rollbackTransaction();
            }
            throw AuthenticationException::notAuthenticated();
        }
    }
}
