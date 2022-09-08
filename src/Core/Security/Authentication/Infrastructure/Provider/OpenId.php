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
 * See the License for the spceific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */

declare(strict_types=1);

namespace Core\Security\Authentication\Infrastructure\Provider;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Log\LoggerTrait;
use Centreon\Infrastructure\Service\Exception\NotFoundException;
use Core\Security\Authentication\Application\Provider\ProviderAuthenticationInterface;
use Core\Security\Authentication\Application\UseCase\Login\LoginRequest;
use Core\Security\Authentication\Domain\Exception\SSOAuthenticationException;
use Core\Security\Authentication\Domain\Model\AuthenticationTokens;
use Core\Security\Authentication\Domain\Model\NewProviderToken;
use Core\Security\Authentication\Domain\Model\ProviderToken;
use Core\Security\Authentication\Domain\Provider\OpenIdProvider;
use Core\Security\ProviderConfiguration\Domain\Model\Configuration;
use Core\Security\ProviderConfiguration\Domain\OpenId\Exceptions\OpenIdConfigurationException;
use Exception;
use Pimple\Container;
use Security\Domain\Authentication\Interfaces\OpenIdProviderInterface;
use Throwable;

class OpenId implements ProviderAuthenticationInterface
{
    use LoggerTrait;

    /**
     * @var string
     */
    private string $username;

    /**
     * @var ContactInterface|null
     */
    private ?ContactInterface $authenticatedUser;

    /**
     * @param Container $dependencyInjector
     * @param OpenIdProvider $provider
     */
    public function __construct(
        private Container $dependencyInjector,
        private OpenIdProviderInterface $provider
    ) {
    }

    /**
     * @param LoginRequest $request
     * @throws SSOAuthenticationException
     * @throws OpenIdConfigurationException
     */
    public function authenticateOrFail(LoginRequest $request): void
    {
        $this->provider->authenticateOrFail($request->code, $request->clientIp);

        $this->username = $this->provider->getUserInformation()['email'];
    }

    /**
     * @return ContactInterface
     * @throws SSOAuthenticationException
     * @throws Throwable
     */
    public function findUserOrFail(): ContactInterface
    {
        $user = $this->getAuthenticatedUser();
        if ($user === null) {
            $this->info("User not found");
            if (!$this->isAutoImportEnabled()) {
                throw new NotFoundException('User could not be created');
            }
            $this->info("Start auto import");
            $this->provider->createUser();
            $user = $this->getAuthenticatedUser();
            if ($user === null) {
                throw new NotFoundException('User not found');
            }
            $this->info("User imported: " . $user->getName());
        }

        return $user;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return bool
     */
    public function isAutoImportEnabled(): bool
    {
        return $this->provider->canCreateUser();
    }

    /**
     * @throws SSOAuthenticationException
     * @throws Throwable
     */
    public function importUser(): void
    {
        $user = $this->provider->getUser();
        if ($this->isAutoImportEnabled() && $user === null) {
            $this->info("Start auto import");
            $this->provider->createUser();
            $this->info("User imported: " . $user->getName());
        }
    }

    /**
     * @throws SSOAuthenticationException
     * @throws Throwable
     */
    public function updateUser(): void
    {
        $user = $this->provider->getUser();
        if ($this->isAutoImportEnabled() === true && $user === null) {
            $this->info("Start auto import");
            $this->provider->createUser();
            $this->info("User imported: " . $user->getName());
        }
    }

    /**
     * @return \Centreon
     * @throws Exception
     */
    public function getLegacySession(): \Centreon
    {
        global $pearDB;
        $pearDB = $this->dependencyInjector['configuration_db'];

        $user = $this->provider->getUser();
        if ($user === null) {
            throw new \Exception("can't initialize legacy session, user does not exists");
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

        return $this->provider->getLegacySession();
    }

    /**
     * @return ProviderToken
     */
    public function getProviderToken(): NewProviderToken
    {
        return $this->provider->getProviderToken();
    }

    /**
     * @return ProviderToken|null
     */
    public function getProviderRefreshToken(): ?NewProviderToken
    {
        return $this->provider->getProviderRefreshToken();
    }

    /**
     * @return Configuration
     */
    public function getConfiguration(): Configuration
    {
        return $this->provider->getConfiguration();
    }

    public function setConfiguration(Configuration $configuration): void
    {
        $this->provider->setConfiguration($configuration);
    }

    /**
     * @return bool
     */
    public function isUpdateACLSupported(): bool
    {
        return true;
    }

    /**
     * @return array
     */
    public function getUserClaims(): array
    {
        $userClaims = [];
        $configuration = $this->provider->getConfiguration();
        $idTokenPayload = $this->provider->getIdTokenPayload();
        $userInformation = $this->provider->getUserInformation();
        $claimName = $configuration->getCustomConfiguration()->getClaimName();

        if (array_key_exists($claimName, $idTokenPayload)) {
            $userClaims = $idTokenPayload[$claimName];
        } elseif (array_key_exists($claimName, $userInformation)) {
            $userClaims = $userInformation[$claimName];
        } else {
            $this->info(
                "configured claim name not found in user information or id_token, " .
                "default contact group ACL will be apply",
                ["claim_name" => $claimName]
            );
        }

        /**
         * Claims can sometime be listed as a string e.g: "claim1,claim2,claim3" so we explode
         * them to handle only one format
         */
        if (is_string($userClaims)) {
            $userClaims = explode(",", $userClaims);
        }

        $this->info("Claims found", [
            "claims_value" => implode(", ", $userClaims),
            "claim_name" => $claimName
        ]);

        return $userClaims;
    }

    /**
     * @param array $claims
     * @return array
     */
    public function getUserAccessGroupsFromClaims(array $claims): array
    {
        $userAccessGroups = [];
        $configuration = $this->provider->getConfiguration();
        foreach ($configuration->getCustomConfiguration()->getAuthorizationRules() as $authorizationRule) {
            if (!in_array($authorizationRule->getClaimValue(), $claims)) {
                $this->info(
                    "Configured Claim Value not found in user claims",
                    ["claim_value" => $authorizationRule->getClaimValue()]
                );

                continue;
            }
            // We ensure here to not duplicate access group while using their id as index
            $userAccessGroups[$authorizationRule->getAccessGroup()->getId()] = $authorizationRule->getAccessGroup();
        }
        return $userAccessGroups;
    }

    /**
     * @return bool
     */
    public function canRefreshToken(): bool
    {
        return false;
    }

    /**
     * @param AuthenticationTokens $authenticationTokens
     * @return AuthenticationTokens|null
     */
    public function refreshToken(AuthenticationTokens $authenticationTokens): ?AuthenticationTokens
    {
        return null;
    }

    /**
     * @return ContactInterface|null
     */
    public function getAuthenticatedUser(): ?ContactInterface
    {
        return $this->provider->getUser();
    }

    /**
     * @return array
     */
    public function getUserInformation(): array
    {
        return $this->provider->getUserInformation();
    }

    /**
     * @return array
     */
    public function getIdTokenPayload(): array
    {
        return $this->provider->getIdTokenPayload();
    }
}
