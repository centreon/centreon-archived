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
use Pimple\Container;
use Security\Domain\Authentication\Interfaces\ProviderInterface;
use Security\Domain\Authentication\Interfaces\AuthenticationServiceInterface;

/**
 * @package Security\Authentication\Model
 */
class LocalProvider implements ProviderInterface
{
    public const NAME = 'local';

    private $loginUrl;

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
     * @var AuthenticationServiceInterface
     */
    private $authenticationService;

    /**
     * @var Container
     */
    private $dependencyInjector;

    /**
     * @var array
     */
    private $configuration;

    /**
     * LocalProvider constructor.
     *
     * @param string $loginUrl
     * @param ContactServiceInterface $contactService
     * @param AuthenticationServiceInterface $authenticationService
     * @param Container $dependencyInjector
     */
    public function __construct(
        string $loginUrl,
        ContactServiceInterface $contactService,
        AuthenticationServiceInterface $authenticationService,
        Container $dependencyInjector
    ) {
        $this->loginUrl = $loginUrl;
        $this->contactService = $contactService;
        $this->authenticationService = $authenticationService;
        $this->dependencyInjector = $dependencyInjector;
    }

    /**
     * @inheritDoc
     */
    public function authenticate(array $data): void
    {
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
            }
            $this->isAuthenticated = true;
        } else {
            $this->isAuthenticated = false;
        }
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
    public function getAuthenticationUri(): string
    {
        return $this->loginUrl;
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
     * @todo : what is the purpose
     * @inheritDoc
     */
    public function getConfiguration(): array
    {
        return [];
    }

    /**
     * @todo : what is the purpose
     * @inheritDoc
     */
    public function setConfiguration(array $configuration): void
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
    public function isForced(): bool
    {
        return $this->configuration['isForced'];
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
        // $token = $this->authenticationService->getProviderToken($sessionToken);
        if ($token === null) {
            $token = new ProviderToken(null, $sessionToken, null, null, null);
            // generate token
        }
        return $token;
    }
}
