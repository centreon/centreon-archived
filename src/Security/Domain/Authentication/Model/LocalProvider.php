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

namespace Security\Domain\Authentication\Model;

use Pimple\Container;
use Centreon\Domain\Log\LoggerTrait;
use Core\Domain\Security\User\Model\User;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Option\Interfaces\OptionServiceInterface;
use Centreon\Domain\Contact\Interfaces\ContactServiceInterface;
use Security\Domain\Authentication\Model\ProviderConfiguration;
use Core\Domain\Security\Authentication\AuthenticationException;
use Core\Domain\Security\Authentication\PasswordExpiredException;
use Security\Domain\Authentication\Interfaces\LocalProviderInterface;
use Core\Application\Security\User\Repository\ReadUserRepositoryInterface;
use Core\Domain\Security\ProviderConfiguration\Local\Model\SecurityPolicy;
use Core\Application\Security\User\Repository\WriteUserRepositoryInterface;
use Core\Domain\Security\ProviderConfiguration\Local\ConfigurationException;
use Core\Application\Security\ProviderConfiguration\Local\Repository\ReadConfigurationRepositoryInterface;
use Security\Domain\Authentication\Interfaces\ProviderConfigurationInterface;

/**
 * @package Security\Authentication\Model
 */
class LocalProvider implements LocalProviderInterface
{
    use LoggerTrait;

    public const NAME = 'local';

    /**
     * @var int
     */
    private $contactId;

    /**
     * @var ProviderConfiguration
     */
    private $configuration;

    /**
     * @var \Centreon
     */
    private $legacySession;

    /**
     * LocalProvider constructor.
     *
     * @param int $sessionExpirationDelay
     * @param ContactServiceInterface $contactService
     * @param Container $dependencyInjector
     * @param OptionServiceInterface $optionService
     * @param ReadConfigurationRepositoryInterface $readProviderConfigurationRepository
     * @param ReadUserRepositoryInterface $readUserRepository
     * @param WriteUserRepositoryInterface $writeUserRepository
     */
    public function __construct(
        private int $sessionExpirationDelay,
        private ContactServiceInterface $contactService,
        private Container $dependencyInjector,
        private OptionServiceInterface $optionService,
        private ReadConfigurationRepositoryInterface $readProviderConfigurationRepository,
        private ReadUserRepositoryInterface $readUserRepository,
        private WriteUserRepositoryInterface $writeUserRepository,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function authenticateOrFail(array $credentials): void
    {
        global $pearDB;
        $pearDB = $this->dependencyInjector['configuration_db'];

        $log = new \CentreonUserLog(0, $this->dependencyInjector['configuration_db']);
        $auth = new \CentreonAuth(
            $this->dependencyInjector,
            $credentials['login'],
            $credentials['password'],
            \CentreonAuth::AUTOLOGIN_DISABLE,
            $this->dependencyInjector['configuration_db'],
            $log,
            \CentreonAuth::ENCRYPT_MD5,
            ""
        );

        $this->debug(
            '[LOCAL PROVIDER] local provider trying to authenticate using legacy Authentication',
            [
                "class" => \CentreonAuth::class,
            ],
            function () use ($auth) {
                $userInfos = $auth->userInfos;
                return [
                    'contact_id' => $userInfos['contact_id'] ?? null,
                    'contact_alias' => $userInfos['contact_alias'] ?? null,
                    'contact_auth_type' => $userInfos['contact_auth_type'] ?? null,
                    'contact_ldap_dn' => $userInfos['contact_ldap_dn'] ?? null
                ];
            }
        );

        $doesPasswordMatch = $auth->passwdOk === 1;

        if ($auth->userInfos["contact_auth_type"] === \CentreonAuth::AUTH_TYPE_LOCAL) {
            $user = $this->readUserRepository->findUserByAlias($auth->userInfos['contact_alias']);
            if ($user === null) {
                throw new \Exception('user not found');
            }

            $providerConfiguration = $this->readProviderConfigurationRepository->findConfiguration();
            if ($providerConfiguration === null) {
                throw ConfigurationException::notFound();
            }

            $securityPolicy = $providerConfiguration->getSecurityPolicy();

            $this->respectLocalSecurityPolicyOrFail($user, $securityPolicy, $doesPasswordMatch);
        }

        if (! $doesPasswordMatch) {
            $this->info(
                "Local provider cannot authenticate successfully user",
                [
                    "provider_name" => $this->getName(),
                    "user" => $credentials['login']
                ]
            );
            throw AuthenticationException::notAuthenticated();
        }

        if ($auth->userInfos !== null) {
            $this->contactId = (int) $auth->userInfos['contact_id'];
            $this->setLegacySession(new \Centreon($auth->userInfos));
        }
        $this->info('[LOCAL PROVIDER] authentication succeed');
    }

    /**
     * @inheritDoc
     */
    public function getLegacySession(): \Centreon
    {
        return $this->legacySession;
    }

    /**
     * @inheritDoc
     */
    public function setLegacySession(\Centreon $legacySession): void
    {
        $this->legacySession = $legacySession;
    }

    /**
     * @inheritDoc
     */
    public function canCreateUser(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function refreshToken(AuthenticationTokens $authenticationTokens): ?AuthenticationTokens
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return self::NAME;
    }

    public function getUser(): ?ContactInterface
    {
        return $this->contactService->findContact($this->contactId);
    }

    /**
     * @inheritDoc
     */
    public function getConfiguration(): ProviderConfiguration
    {
        return $this->configuration;
    }

    /**
     * @inheritDoc
     */
    public function setConfiguration(ProviderConfigurationInterface $configuration): void
    {
        $this->configuration = $configuration;
    }

    /**
     * @inheritDoc
     */
    public function canRefreshToken(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getProviderToken(string $token): ProviderToken
    {
        $sessionExpireOption = $this->optionService->findSelectedOptions(['session_expire']);
        if (!empty($sessionExpireOption)) {
            $this->sessionExpirationDelay = (int) $sessionExpireOption[0]->getValue();
        }
        return new ProviderToken(
            null,
            $token,
            new \DateTime(),
            (new \DateTime())->add(new \DateInterval('PT' . $this->sessionExpirationDelay . 'M'))
        );
    }

    /**
     * @inheritDoc
     */
    public function getProviderRefreshToken(string $token): ?ProviderToken
    {
        return null;
    }

    /**
     * Check if local security policy is respected
     *
     * @param User $user
     * @param SecurityPolicy $securityPolicy
     * @param bool $doesPasswordMatch
     */
    private function respectLocalSecurityPolicyOrFail(
        User $user,
        SecurityPolicy $securityPolicy,
        bool $doesPasswordMatch,
    ): void {
        $isUserBlocked = false;
        if ($securityPolicy->getAttempts() !== null && $securityPolicy->getBlockingDuration() !== null) {
            $isUserBlocked = $this->isUserBlocked($user, $securityPolicy, $doesPasswordMatch);
        }

        $this->writeUserRepository->updateBlockingInformation($user);

        if ($isUserBlocked) {
            $this->info(
                '[LOCAL PROVIDER] authentication failed because user is blocked',
                [
                    'contact_alias' => $user->getAlias(),
                ],
            );
            throw AuthenticationException::userBlocked();
        }

        if (
            $securityPolicy->getPasswordExpirationDelay() !== null
            && $doesPasswordMatch
            && $this->isPasswordExpired($user, $securityPolicy)
        ) {
            $this->info(
                '[LOCAL PROVIDER] authentication failed because password is expired',
                [
                    'contact_alias' => $user->getAlias(),
                ],
            );
            throw PasswordExpiredException::passwordIsExpired();
        }
    }

    /**
     * Check if the user is blocked
     *
     * @param User $user
     * @param SecurityPolicy $securityPolicy
     * @param bool $doesPasswordMatch
     * @return bool
     */
    private function isUserBlocked(User $user, SecurityPolicy $securityPolicy, bool $doesPasswordMatch): bool
    {
        if (
            $user->getBlockingTime() !== null
            && (time() - $user->getBlockingTime()->getTimestamp()) < $securityPolicy->getBlockingDuration()
        ) {
            $this->info(
                'user is blocked',
                [
                    'contact_alias' => $user->getAlias(),
                ],
            );
            return true;
        }

        if ($doesPasswordMatch) {
            $this->info(
                'reset blocking duration values',
                [
                    'contact_alias' => $user->getAlias(),
                ],
            );
            $user->setLoginAttempts(null);
            $user->setBlockingTime(null);
        } else {
            $this->info(
                'increment login attempts',
                [
                    'contact_alias' => $user->getAlias(),
                ],
            );
            $user->setLoginAttempts($user->getLoginAttempts() + 1);

            if ($user->getLoginAttempts() >= $securityPolicy->getAttempts()) {
                $user->setBlockingTime(new \DateTimeImmutable());
            }
        }

        return $user->getBlockingTime() !== null;
    }

    /**
     * Check if the password is expired
     *
     * @param User $user
     * @param SecurityPolicy $securityPolicy
     * @return bool
     */
    private function isPasswordExpired(User $user, SecurityPolicy $securityPolicy): bool
    {
        if (in_array($user->getAlias(), $securityPolicy->getPasswordExpirationExcludedUserAliases())) {
            $this->info(
                'skip password expiration policy because user is excluded',
                [
                    'contact_alias' => $user->getAlias(),
                ],
            );
            return false;
        }

        $expirationDelay = $securityPolicy->getPasswordExpirationDelay();
        $passwordCreationDate = $user->getPassword()->getCreationDate();

        if ((time() - $passwordCreationDate->getTimestamp()) > $expirationDelay) {
            $this->info(
                'password is expired',
                [
                    'contact_alias' => $user->getAlias(),
                    'creation_date' => $passwordCreationDate->format(\DateTime::ISO8601),
                    'expiration_delay' => $expirationDelay,
                ],
            );
            return true;
        }

        return false;
    }
}
