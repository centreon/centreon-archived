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

namespace Tests\Core\Security\ProviderConfiguration\Application\Local\UseCase\FindConfiguration;

use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;
use Core\Security\Authentication\Application\Provider\ProviderAuthenticationFactoryInterface;
use Core\Security\Authentication\Application\Provider\ProviderAuthenticationInterface;
use Core\Security\ProviderConfiguration\Application\Local\UseCase\FindConfiguration\FindConfiguration;
use Core\Security\ProviderConfiguration\Domain\Local\Model\Configuration;
use Core\Security\ProviderConfiguration\Domain\Local\Model\CustomConfiguration;
use Core\Security\ProviderConfiguration\Domain\Local\Model\SecurityPolicy;
use Core\Security\ProviderConfiguration\Domain\Model\Provider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FindConfigurationTest extends TestCase
{
    /**
     * @var ProviderAuthenticationFactoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private ProviderAuthenticationFactoryInterface $providerAuthenticationFactory;

    /**
     * @var ProviderAuthenticationInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private ProviderAuthenticationInterface $providerAuthentication;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->providerAuthenticationFactory = $this->createMock(ProviderAuthenticationFactoryInterface::class);
        $this->providerAuthentication = $this->createMock(ProviderAuthenticationInterface::class);
    }

    /**
     * Test that the use case will correctly pass the configuration to the presenter.
     */
    public function testFindConfiguration(): void
    {
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
            [],
            SecurityPolicy::MIN_NEW_PASSWORD_DELAY
        );

        $customConfiguration = new CustomConfiguration($securityPolicy);
        $configuration = new Configuration(1, 'local', 'local', '{}', true, true);
        $configuration->setCustomConfiguration($customConfiguration);
        $this->providerAuthenticationFactory
            ->expects($this->once())
            ->method('create')
            ->with(Provider::LOCAL)
            ->willReturn($this->providerAuthentication);

        $this->providerAuthentication
            ->expects($this->once())
            ->method('getConfiguration')
            ->willReturn($configuration);

        $useCase = new FindConfiguration($this->providerAuthenticationFactory);
        $presenter = new FindConfigurationPresenterFake();

        $useCase($presenter);

        $this->assertEquals(
            $presenter->response->passwordMinimumLength,
            $configuration->getCustomConfiguration()->getSecurityPolicy()->getPasswordMinimumLength()
        );

        $customConf = $configuration->getCustomConfiguration();
        $this->assertEquals($presenter->response->hasUppercase, $customConf->getSecurityPolicy()->hasUppercase());
        $this->assertEquals($presenter->response->hasLowercase, $customConf->getSecurityPolicy()->hasLowercase());
        $this->assertEquals($presenter->response->hasNumber, $customConf->getSecurityPolicy()->hasNumber());
        $this->assertEquals(
            $presenter->response->hasSpecialCharacter,
            $configuration->getCustomConfiguration()->getSecurityPolicy()->hasSpecialCharacter()
        );
        $this->assertEquals(
            $presenter->response->canReusePasswords,
            $configuration->getCustomConfiguration()->getSecurityPolicy()->canReusePasswords()
        );
        $this->assertEquals($presenter->response->attempts, $customConf->getSecurityPolicy()->getAttempts());
        $this->assertEquals(
            $presenter->response->blockingDuration,
            $configuration->getCustomConfiguration()->getSecurityPolicy()->getBlockingDuration()
        );
        $this->assertEquals(
            $presenter->response->passwordExpirationDelay,
            $configuration->getCustomConfiguration()->getSecurityPolicy()->getPasswordExpirationDelay()
        );
        $this->assertEquals(
            $presenter->response->delayBeforeNewPassword,
            $configuration->getCustomConfiguration()->getSecurityPolicy()->getDelayBeforeNewPassword()
        );
    }
}
