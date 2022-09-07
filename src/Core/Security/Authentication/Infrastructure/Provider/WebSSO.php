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

namespace Core\Security\Authentication\Infrastructure\Provider;

use Centreon;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Infrastructure\Service\Exception\NotFoundException;
use Core\Security\Authentication\Application\Provider\ProviderAuthenticationInterface;
use Core\Security\Authentication\Application\UseCase\Login\LoginRequest;
use Core\Security\Authentication\Domain\Exception\SSOAuthenticationException;
use Core\Security\Authentication\Domain\Model\AuthenticationTokens;
use Core\Security\Authentication\Domain\Model\NewProviderToken;
use Core\Security\ProviderConfiguration\Domain\Model\Configuration;
use InvalidArgumentException;
use Pimple\Container;
use Security\Domain\Authentication\Interfaces\WebSSOProviderInterface as LegacyWebSSOProviderInterface;

class WebSSO implements ProviderAuthenticationInterface
{
    use Centreon\Domain\Log\LoggerTrait;

    private ?ContactInterface $authenticatedUser;

    /**
     * @param Container $dependencyInjector
     * @param LegacyWebSSOProviderInterface $provider
     * @param Centreon\Domain\Contact\Interfaces\ContactRepositoryInterface $contactRepository
     */
    public function __construct(
        private Container $dependencyInjector,
        private LegacyWebSSOProviderInterface $provider,
        private Centreon\Domain\Contact\Interfaces\ContactRepositoryInterface $contactRepository
    )
    {
    }

    /**
     * @return bool
     */
    public function isAutoImportEnabled(): bool
    {
        return false;
    }

    /**
     * @return Centreon
     * @throws \Exception
     */
    public function getLegacySession(): Centreon
    {
        global $pearDB;
        $pearDB = $this->dependencyInjector['configuration_db'];

        $user = $this->findUserOrFail();

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

        $this->authenticatedUser = $user;

        return $this->provider->getLegacySession();
    }

    /**
     * @return Configuration
     */
    public function getConfiguration(): Configuration
    {
        return $this->provider->getConfiguration();
    }

    /**
     * @param Configuration $configuration
     */
    public function setConfiguration(Configuration $configuration): void
    {
        $this->provider->setConfiguration($configuration);
    }

    /**
     * @param AuthenticationTokens $authenticationTokens
     * @return AuthenticationTokens|null
     */
    public function refreshToken(AuthenticationTokens $authenticationTokens): ?AuthenticationTokens
    {
        if ($this->canRefreshToken()) {
            $this->provider->refreshToken($authenticationTokens);
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isUpdateACLSupported(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function canRefreshToken(): bool
    {
        return true;
    }

    /**
     * @return ContactInterface|null
     */
    public function getAuthenticatedUser(): ?ContactInterface
    {
        return $this->authenticatedUser;
    }

    /**
     * @param LoginRequest $request
     * @throws SSOAuthenticationException
     */
    public function authenticateOrFail(LoginRequest $request): void
    {
        $this->info('Authenticate the user');
        $this->ipIsAllowToConnect($request->clientIp);
        $this->validateLoginAttributeOrFail();
    }

    /**
     * @return ContactInterface
     * @throws SSOAuthenticationException
     * @throws \Exception
     */
    public function findUserOrFail(): ContactInterface
    {
        $alias = $this->extractUsernameFromLoginClaimOrFail();
        $this->info('searching user', ['user' => $alias]);
        $user = $this->contactRepository->findByName($alias);
        if ($user === null) {
            throw new NotFoundException("Contact $alias does not exists");
        }

        return $user;
    }

    public function getUsername(): string
    {
        return $this->provider->getUser()->getEmail();
    }

    /**
     * @param string $ipAddress
     * @throws SSOAuthenticationException
     */
    public function ipIsAllowToConnect(string $ipAddress): void
    {
        $this->info('Check Client IP from blacklist/whitelist addresses');
        $customConfiguration = $this->getConfiguration()->getCustomConfiguration();
        if (in_array($ipAddress, $customConfiguration->getBlackListClientAddresses(), true)) {
            $this->error('IP Blacklisted', ['ip' => '...' . substr($ipAddress, -5)]);
            throw SSOAuthenticationException::blackListedClient();
        }
        if (
            !empty($customConfiguration->getTrustedClientAddresses())
            && !in_array($ipAddress, $customConfiguration->getTrustedClientAddresses(), true)
        ) {
            $this->error('IP not Whitelisted', ['ip' => '...' . substr($ipAddress, -5)]);
            throw SSOAuthenticationException::notWhiteListedClient();
        }
    }

    /**
     * Validate that login attribute is defined in server environment variables
     */
    public function validateLoginAttributeOrFail(): void
    {
        $customConfiguration = $this->getConfiguration()->getCustomConfiguration();
        $this->info('Validating login header attribute');
        if (!array_key_exists($customConfiguration->getLoginHeaderAttribute(), $_SERVER)) {
            $this->error('login header attribute not found in server environment server', [
                'login_header_attribute' => $customConfiguration->getLoginHeaderAttribute()
            ]);
            throw new InvalidArgumentException('Missing Login Attribute');
        }
    }

    /**
     * Extract username using configured regexp for login matching
     *
     * @return string
     * @throws SSOAuthenticationException
     */
    public function extractUsernameFromLoginClaimOrFail(): string
    {
        $this->info('Retrieving username from login claim');
        $customConfiguration = $this->getConfiguration()->getCustomConfiguration();

        $userAlias = $_SERVER[$customConfiguration->getLoginHeaderAttribute()];

        if ($customConfiguration->getPatternMatchingLogin() !== null) {
            $userAlias = preg_replace(
                '/' . trim($customConfiguration->getPatternMatchingLogin(), '/') . '/',
                $customConfiguration->getPatternReplaceLogin() ?? '',
                $_SERVER[$customConfiguration->getLoginHeaderAttribute()]
            );
            if (empty($userAlias)) {
                $this->error('Regex does not match anything', [
                    'regex' => $customConfiguration->getPatternMatchingLogin(),
                    'subject' => $_SERVER[$customConfiguration->getLoginHeaderAttribute()]
                ]);
                throw SSOAuthenticationException::unableToRetrieveUsernameFromLoginClaim();
            }
        }

        return $userAlias;
    }

    public function importUser(): void
    {
        throw new \DomainException("Feature not available for WebSSO provider");
    }

    /**
     * Update user in data storage
     */
    public function updateUser(): void
    {
        throw new \DomainException("Feature not available for WebSSO provider");
    }

    /**
     * @return NewProviderToken
     */
    public function getProviderToken(): NewProviderToken
    {
        throw new \DomainException("Feature not available for WebSSO provider");
    }

    /**
     * @return NewProviderToken|null
     */
    public function getProviderRefreshToken(): ?NewProviderToken
    {
        return null;
    }

    public function getUserInformation(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getIdTokenPayload(): array
    {
        return [];
    }
}
