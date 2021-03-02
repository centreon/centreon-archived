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

namespace Security\Domain\Authentication\Model;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Contact\Interfaces\ContactServiceInterface;
use Centreon\Domain\Option\Interfaces\OptionServiceInterface;
use Pimple\Container;
use Security\Domain\Authentication\Interfaces\ProviderInterface;
use Security\Domain\Authentication\Interfaces\AuthenticationRepositoryInterface;
use Security\Domain\Authentication\Model\ProviderConfiguration;

/**
 * @package Security\Authentication\Model
 */
class LocalProvider implements ProviderInterface
{
    public const NAME = 'local';

    /**
     * @var boolean
     */
    private $isAuthenticated;

    /**
     * @var int
     */
    private $contactId;

    /**
     * @var ContactServiceInterface
     */
    private $contactService;

    /**
     * @var AuthenticationRepositoryInterface
     */
    private $authenticationRepository;

    /**
     * @var Container
     */
    private $dependencyInjector;

    /**
     * @var ProviderConfiguration
     */
    private $configuration;

    /**
     * @var OptionServiceInterface
     */
    private $optionService;

    /**
     * LocalProvider constructor.
     *
     * @param ContactServiceInterface $contactService
     * @param AuthenticationRepositoryInterface $authenticationRepository
     * @param Container $dependencyInjector
     * @param OptionServiceInterface $optionService
     */
    public function __construct(
        ContactServiceInterface $contactService,
        AuthenticationRepositoryInterface $authenticationRepository,
        Container $dependencyInjector,
        OptionServiceInterface $optionService
    ) {
        $this->contactService = $contactService;
        $this->authenticationRepository = $authenticationRepository;
        $this->dependencyInjector = $dependencyInjector;
        $this->optionService = $optionService;
    }

    /**
     * @inheritDoc
     */
    public function authenticate(array $data): void
    {
        global $pearDB;
        $pearDB = $this->dependencyInjector['configuration_db'];

        $log = new \CentreonUserLog(0, $this->dependencyInjector['configuration_db']);
        $auth = new \CentreonAuth(
            $this->dependencyInjector,
            $data['useralias'],
            $data['password'],
            0,
            $this->dependencyInjector['configuration_db'],
            $log,
            1,
            "",
            "WEB"
        );

        if ($auth->passwdOk === 1) {
            if ($auth->userInfos !== null) {
                $this->contactId = (int) $auth->userInfos['contact_id'];
                $this->setLegacySession(new \Centreon($auth->userInfos));
            }
            $this->isAuthenticated = true;
        } else {
            $this->isAuthenticated = false;
        }
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
    public function setConfiguration(ProviderConfiguration $configuration): void
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
    public function isAuthenticated(): bool
    {
        return $this->isAuthenticated;
    }

    /**
     * @inheritDoc
     */
    public function getProviderRefreshToken(string $sessionToken): ?ProviderToken
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getProviderToken(string $sessionToken): ProviderToken
    {
        $token = null;
        $tokens = $this->authenticationRepository->findAuthenticationTokensBySessionToken($sessionToken);
        if ($tokens === null) {
            $expirationSessionDelay = "120";
            $sessionExpireOption = $this->optionService->findSelectedOptions(['session_expire']);
            if ($sessionExpireOption !== null) {
                $expirationSessionDelay = $sessionExpireOption[0]->getValue();
            }
            $token = new ProviderToken(
                null,
                $sessionToken,
                new \DateTime(),
                (new \DateTime())->add(new \DateInterval('PT' . $expirationSessionDelay . 'M')),
                null
            );
            // generate token
        } else {
            //$token = $tokens->getProviderToken();
        }
        return $token;
    }
}
