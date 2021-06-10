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
use Centreon\Domain\Log\LoggerTrait;
use Centreon\Domain\Option\Interfaces\OptionServiceInterface;
use Pimple\Container;
use Security\Domain\Authentication\Interfaces\ProviderInterface;
use Security\Domain\Authentication\Model\ProviderConfiguration;

/**
 * @package Security\Authentication\Model
 */
class LocalProvider implements ProviderInterface
{
    use LoggerTrait;

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
     * @var \Centreon|null
     */
    private $legacySession;

    /**
     * LocalProvider constructor.
     *
     * @param ContactServiceInterface $contactService
     * @param Container $dependencyInjector
     * @param OptionServiceInterface $optionService
     */
    public function __construct(
        ContactServiceInterface $contactService,
        Container $dependencyInjector,
        OptionServiceInterface $optionService
    ) {
        $this->contactService = $contactService;
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
            $data['login'],
            $data['password'],
            0,
            $this->dependencyInjector['configuration_db'],
            $log,
            1,
            "",
            "WEB"
        );
        $this->debug(
            'local provider trying to authenticate using legacy Authentication',
            [
                "class" => \CentreonAuth::class,
            ],
            function () use ($auth) {
                $userInfos = $auth->userInfos;
                return [
                    'contact_id' => $userInfos['contact_id'],
                    'contact_alias' => $userInfos['contact_alias'],
                    'contact_auth_type' => $userInfos['contact_auth_type'],
                    'contact_ldap_dn' => $userInfos['contact_ldap_dn']
                ];
            }
        );
        if ($auth->passwdOk === 1) {
            if ($auth->userInfos !== null) {
                $this->contactId = (int) $auth->userInfos['contact_id'];
                $this->setLegacySession(new \Centreon($auth->userInfos));
            }
            $this->isAuthenticated = true;
            $this->debug('authentication succeed from legacy Authentication');
        } else {
            $this->isAuthenticated = false;
        }
    }

    /**
     * @inheritDoc
     */
    public function getLegacySession(): ?\Centreon
    {
        return $this->legacySession;
    }

    /**
     * @inheritDoc
     */
    public function setLegacySession(?\Centreon $legacySession): void
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
    public function getProviderToken(string $token): ProviderToken
    {
        $expirationSessionDelay = 120;
        $sessionExpireOption = $this->optionService->findSelectedOptions(['session_expire']);
        if (!empty($sessionExpireOption)) {
            $expirationSessionDelay = (int) $sessionExpireOption[0]->getValue();
        }
        return new ProviderToken(
            null,
            $token,
            new \DateTime(),
            (new \DateTime())->add(new \DateInterval('PT' . $expirationSessionDelay . 'M'))
        );
    }

    /**
     * @inheritDoc
     */
    public function getProviderRefreshToken(string $token): ?ProviderToken
    {
        return null;
    }
}
