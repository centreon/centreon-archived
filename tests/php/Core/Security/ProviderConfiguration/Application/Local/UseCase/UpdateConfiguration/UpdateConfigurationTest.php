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

namespace Tests\Core\Security\ProviderConfiguration\Application\Local\UseCase\UpdateConfiguration;

use Core\Application\Configuration\User\Repository\ReadUserRepositoryInterface;
use Core\Security\Authentication\Application\Provider\ProviderAuthenticationFactoryInterface;
use Core\Security\Authentication\Application\Provider\ProviderAuthenticationInterface;
use Core\Security\ProviderConfiguration\Application\Local\Repository\WriteConfigurationRepositoryInterface;
use Core\Security\ProviderConfiguration\Application\Local\UseCase\UpdateConfiguration\{
    UpdateConfigurationPresenterInterface};
use Core\Security\ProviderConfiguration\Application\Local\UseCase\UpdateConfiguration\UpdateConfiguration;
use Core\Security\ProviderConfiguration\Application\Local\UseCase\UpdateConfiguration\UpdateConfigurationRequest;
use Core\Security\ProviderConfiguration\Domain\Local\Model\Configuration;
use Core\Security\ProviderConfiguration\Domain\Local\Model\CustomConfiguration;
use Core\Security\ProviderConfiguration\Domain\Local\Model\SecurityPolicy;
use Core\Security\ProviderConfiguration\Domain\Model\Provider;
use PHPUnit\Framework\TestCase;

class UpdateConfigurationTest extends TestCase
{
    /**
     * @var WriteConfigurationRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $writeConfigurationRepository;

    /**
     * @var ReadUserRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $readUserRepository;

    /**
     * @var UpdateConfigurationPresenterInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $presenter;

    /**
     * @var ProviderAuthenticationFactoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private ProviderAuthenticationFactoryInterface $providerAuthenticationFactory;

    /**
     * @var ProviderAuthenticationInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $provider;

    public function setUp(): void
    {
        $this->writeConfigurationRepository = $this->createMock(WriteConfigurationRepositoryInterface::class);
        $this->readUserRepository = $this->createMock(ReadUserRepositoryInterface::class);
        $this->presenter = $this->createMock(UpdateConfigurationPresenterInterface::class);
        $this->providerAuthenticationFactory = $this->createMock(ProviderAuthenticationFactoryInterface::class);
        $this->provider = $this->createMock(ProviderAuthenticationInterface::class);
    }

    /**
     * Test that the use case will correctly be executed.
     */
    public function testUpdateConfiguration(): void
    {
        $excludedUserAliases = ['admin'];
        $securityPolicy = new SecurityPolicy(
            SecurityPolicy::MIN_PASSWORD_LENGTH,
            true,
            true,
            true,
            true,
            true,
            SecurityPolicy::MIN_ATTEMPTS,
            SecurityPolicy::MIN_BLOCKING_DURATION,
            SecurityPolicy::MIN_PASSWORD_EXPIRATION_DELAY,
            $excludedUserAliases,
            SecurityPolicy::MIN_NEW_PASSWORD_DELAY
        );

        $customConfiguration = new CustomConfiguration($securityPolicy);
        $configuration = new Configuration(1, 'local', 'local', '{}', true, true);
        $configuration->setCustomConfiguration($customConfiguration);

        $this->provider
            ->expects($this->once())
            ->method('getConfiguration')
            ->willReturn($configuration);

        $this->providerAuthenticationFactory
            ->expects($this->once())
            ->method('create')
            ->with(Provider::LOCAL)
            ->willReturn($this->provider);

        $request = new UpdateConfigurationRequest();
        $request->passwordMinimumLength = SecurityPolicy::MIN_PASSWORD_LENGTH;
        $request->hasUppercase = true;
        $request->hasLowercase = false;
        $request->hasNumber = true;
        $request->hasSpecialCharacter = true;
        $request->canReusePasswords = true;
        $request->attempts = SecurityPolicy::MIN_ATTEMPTS;
        $request->blockingDuration = SecurityPolicy::MIN_BLOCKING_DURATION;
        $request->passwordExpirationDelay = SecurityPolicy::MIN_PASSWORD_EXPIRATION_DELAY;
        $request->passwordExpirationExcludedUserAliases = $excludedUserAliases;
        $request->delayBeforeNewPassword = SecurityPolicy::MIN_NEW_PASSWORD_DELAY;

        $this->readUserRepository
            ->expects($this->once())
            ->method('findUserIdsByAliases')
            ->with($excludedUserAliases)
            ->willReturn([1]);

        $this->writeConfigurationRepository
            ->expects($this->once())
            ->method('updateConfiguration')
            ->with($configuration, [1]);

        $this->presenter
            ->expects($this->once())
            ->method('setResponseStatus');

        $useCase = new UpdateConfiguration(
            $this->writeConfigurationRepository,
            $this->readUserRepository,
            $this->providerAuthenticationFactory
        );
        $useCase($this->presenter, $request);
        $this->assertNotEquals(
            $securityPolicy->hasLowercase(),
            $configuration->getCustomConfiguration()->getSecurityPolicy()->hasLowercase()
        );
    }
}
