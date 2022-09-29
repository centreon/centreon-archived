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

use Pimple\Container;
use Centreon\Domain\Contact\Contact;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpKernel\KernelEvents;
use Security\Domain\Authentication\Model\Session;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Security\Domain\Authentication\Model\ProviderToken;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Option\Interfaces\OptionServiceInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Centreon\Infrastructure\Service\Exception\NotFoundException;
use Core\Security\Domain\Authentication\AuthenticationException;
use Centreon\Domain\Contact\Interfaces\ContactRepositoryInterface;
use Centreon\Domain\Log\LoggerTrait;
use Centreon\Domain\Repository\Interfaces\DataStorageEngineInterface;
use Security\Domain\Authentication\Interfaces\SessionRepositoryInterface;
use Security\Domain\Authentication\Interfaces\AuthenticationServiceInterface;
use Core\Security\Domain\ProviderConfiguration\WebSSO\Model\WebSSOConfiguration;
use Security\Domain\Authentication\Interfaces\AuthenticationRepositoryInterface;
use Core\Security\Application\ProviderConfiguration\WebSSO\Repository\ReadWebSSOConfigurationRepositoryInterface;
use Core\Security\Domain\Authentication\SSOAuthenticationException;
use Symfony\Component\HttpFoundation\Request;

class WebSSOEventSubscriber implements EventSubscriberInterface
{
    use LoggerTrait;

    /**
     * @param Container $dependencyInjector
     * @param ReadWebSSOConfigurationRepositoryInterface $webSSOReadRepository
     * @param ContactRepositoryInterface $contactRepository
     * @param SessionInterface $session
     * @param AuthenticationServiceInterface $authenticationService
     * @param SessionRepositoryInterface $sessionRepository
     * @param DataStorageEngineInterface $dataStorageEngine
     * @param OptionServiceInterface $optionService
     * @param AuthenticationRepositoryInterface $authenticationRepository
     * @param Security $security
     */
    public function __construct(
        private Container $dependencyInjector,
        private ReadWebSSOConfigurationRepositoryInterface $webSSOReadRepository,
        private ContactRepositoryInterface $contactRepository,
        private SessionInterface $session,
        private AuthenticationServiceInterface $authenticationService,
        private SessionRepositoryInterface $sessionRepository,
        private DataStorageEngineInterface $dataStorageEngine,
        private OptionServiceInterface $optionService,
        private AuthenticationRepositoryInterface $authenticationRepository,
        private Security $security,
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
            && ! is_dir(_CENTREON_PATH_ . DIRECTORY_SEPARATOR . 'www'  . DIRECTORY_SEPARATOR . 'install')
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
     * @throws SSOAuthenticationException
     * @throws NotFoundException
     * @throws \InvalidArgumentException
     * @throws AuthenticationException
     */
    public function loginWebSSOUser(RequestEvent $event): void
    {
        if ($this->security->getUser() === null) {
            $request = $event->getRequest();
            $webSSOConfiguration = $this->findWebSSOConfigurationOrFail();
            if ($webSSOConfiguration->isActive()) {
                $this->info('Starting authentication with WebSSO');
                $this->validateIpIsAllowToConnect($request->getClientIp(), $webSSOConfiguration);
                $this->validateLoginAttributeOrFail($webSSOConfiguration);

                $userAlias = $_SERVER[$webSSOConfiguration->getLoginHeaderAttribute()];
                if ($webSSOConfiguration->getPatternMatchingLogin() !== null) {
                    $userAlias = $this->extractUsernameFromLoginClaimOrFail($webSSOConfiguration);
                }
                $user = $this->findUserByAliasOrFail($userAlias);
                $this->createSession($user, $request);
                $sessionId = $this->session->getId();
                $request->headers->set('Set-Cookie', "PHPSESSID=" . $sessionId);
                $this->createTokenIfNotExist($sessionId, $webSSOConfiguration->getId(), $user, $request->getClientIp());
                $this->info('Authenticated successfully', [
                    'user' => $user->getAlias()
                ]);
            }
        }
    }

    /**
     * Extract username using configured regexp for login matching
     *
     * @param WebSSOConfiguration $webSSOConfiguration
     * @return string
     * @throws SSOAuthenticationException
     */
    private function extractUsernameFromLoginClaimOrFail(WebSSOConfiguration $webSSOConfiguration): string
    {
        $this->info('Retrieving username from login claim');
        $userAlias = preg_replace(
            '/' . trim($webSSOConfiguration->getPatternMatchingLogin(), '/') . '/',
            $webSSOConfiguration->getPatternReplaceLogin() ?? '',
            $_SERVER[$webSSOConfiguration->getLoginHeaderAttribute()]
        );
        if (empty($userAlias)) {
            $this->error('Regex does not match anything', [
                'regex' => $webSSOConfiguration->getPatternMatchingLogin(),
                'subject' => $_SERVER[$webSSOConfiguration->getLoginHeaderAttribute()]
            ]);
            throw SSOAuthenticationException::unableToRetrieveUsernameFromLoginClaim();
        }

        return $userAlias;
    }

    /**
     * @param string $ipAddress
     * @param WebSSOConfiguration $webSSOConfiguration
     * @throws SSOAuthenticationException
     */
    private function validateIpIsAllowToConnect(string $ipAddress, WebSSOConfiguration $webSSOConfiguration): void
    {
        $this->info('Check Client IP from blacklist/whitelist addresses');
        if (in_array($ipAddress, $webSSOConfiguration->getBlackListClientAddresses(), true)) {
            $this->error('IP Blacklisted', ['ip' => '...' . substr($ipAddress, -5)]);
            throw SSOAuthenticationException::blackListedClient();
        }
        if (
            !empty($webSSOConfiguration->getTrustedClientAddresses())
            && !in_array($ipAddress, $webSSOConfiguration->getTrustedClientAddresses(), true)
        ) {
            $this->error('IP not Whitelisted', ['ip' => '...' . substr($ipAddress, -5)]);
            throw SSOAuthenticationException::notWhiteListedClient();
        }
    }

    /**
     * Find Web SSO Configuration or throw an exception
     *
     * @return WebSSOConfiguration
     * @throws NotFoundException
     */
    private function findWebSSOConfigurationOrFail(): WebSSOConfiguration
    {
        $this->info('finding web-sso configuration');
        $webSSOConfiguration = $this->webSSOReadRepository->findConfiguration();
        if ($webSSOConfiguration === null) {
            throw new NotFoundException('Web SSO Configuration does not exist');
        }

        return $webSSOConfiguration;
    }

    /**
     * Validate that login attribute is defined in server environment variables
     *
     * @param WebSSOConfiguration $webSSOConfiguration
     * @throws \InvalidArgumentException
     */
    private function validateLoginAttributeOrFail(WebSSOConfiguration $webSSOConfiguration): void
    {
        $this->info('Validating login header attribute');
        if (!array_key_exists($webSSOConfiguration->getLoginHeaderAttribute(), $_SERVER)) {
            $this->error('login header attribute not found in server environment server', [
                'login_header_attribute' => $webSSOConfiguration->getLoginHeaderAttribute()
            ]);
            throw new \InvalidArgumentException('Missing Login Attribute');
        }
    }

    /**
     * Find User or throw an exception
     *
     * @param string $alias
     * @return Contact
     * @throws NotFoundException
     */
    private function findUserByAliasOrFail(string $alias): Contact
    {
        $this->info('searching user', [
            'user' => $alias
        ]);
        $user = $this->contactRepository->findByName($alias);
        if ($user === null) {
            throw new NotFoundException("Contact $alias does not exists");
        }

        return $user;
    }

    /**
     * Create the session
     *
     * @param Contact $user
     * @param Request $request
     */
    private function createSession(Contact $user, Request $request): void
    {
        $this->info('creating session');
        global $pearDB;
        $pearDB = $this->dependencyInjector['configuration_db'];
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
            'contact_location' => (string) $user->getTimezoneId(),
            'show_deprecated_pages' => $user->isUsingDeprecatedPages(),
            'reach_api' => $user->hasAccessToApiConfiguration() ? 1 : 0,
            'reach_api_rt' => $user->hasAccessToApiRealTime() ? 1 : 0,
            'contact_theme' => $user->getTheme() ?? 'light'
        ];
        $centreonSession = new \Centreon($sessionUserInfos);
        $request->getSession()->start();
        $request->getSession()->set('centreon', $centreonSession);
        $_SESSION['centreon'] = $centreonSession;
    }

    /**
     * Create token if not exist
     *
     * @param string $sessionId
     * @param integer $webSSOConfigurationId
     * @param Contact $user
     * @param string $clientIp
     */
    private function createTokenIfNotExist(
        string $sessionId,
        int $webSSOConfigurationId,
        Contact $user,
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
                new \DateTime(),
                (new \DateTime())->add(new \DateInterval('PT' . $sessionExpirationDelay . 'M'))
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
                $providerToken->getId(),
                $contact->getId(),
                $providerToken,
                $providerRefreshToken
            );
            if (!$isAlreadyInTransaction) {
                $this->dataStorageEngine->commitTransaction();
            }
        } catch (\Exception $ex) {
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
