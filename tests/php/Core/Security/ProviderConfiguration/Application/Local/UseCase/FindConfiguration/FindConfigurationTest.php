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

use Core\Security\Authentication\Application\Provider\ProviderAuthenticationFactoryInterface;
use Core\Security\Authentication\Application\Provider\ProviderAuthenticationInterface;
use Core\Security\ProviderConfiguration\Domain\Local\Model\CustomConfiguration;
use Core\Security\ProviderConfiguration\Domain\Local\Model\Configuration;
use Core\Security\ProviderConfiguration\Domain\Model\Provider;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Core\Security\ProviderConfiguration\Domain\Local\Model\SecurityPolicy;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;
use Core\Security\ProviderConfiguration\Application\Local\UseCase\FindConfiguration\FindConfiguration;
use Core\Security\ProviderConfiguration\Application\Local\UseCase\FindConfiguration\FindConfigurationErrorResponse;
use Core\Security\ProviderConfiguration\Infrastructure\Local\Api\FindConfiguration\FindConfigurationPresenter;
use Tests\Core\Security\ProviderConfiguration\Application\Local\UseCase\FindConfiguration\{
    FindConfigurationPresenterFake
};

class FindConfigurationTest extends TestCase
{
    /**
     * @var PresenterFormatterInterface&MockObject
     */
    private $presenterFormatter;

    /**
     * @var ProviderAuthenticationFactoryInterface
     */
    private ProviderAuthenticationFactoryInterface $providerAuthenticationFactory;

    /**
     * @var ProviderAuthenticationInterface
     */
    private ProviderAuthenticationInterface $providerAuthentication;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->presenterFormatter = $this->createMock(PresenterFormatterInterface::class);
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

        $customConfiguration = CustomConfiguration::createFromSecurityPolicy($securityPolicy);
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
        $this->assertEquals($presenter->response->hasUppercase, $configuration->getCustomConfiguration()->getSecurityPolicy()->hasUppercase());
        $this->assertEquals($presenter->response->hasLowercase, $configuration->getCustomConfiguration()->getSecurityPolicy()->hasLowercase());
        $this->assertEquals($presenter->response->hasNumber, $configuration->getCustomConfiguration()->getSecurityPolicy()->hasNumber());
        $this->assertEquals(
            $presenter->response->hasSpecialCharacter,
            $configuration->getCustomConfiguration()->getSecurityPolicy()->hasSpecialCharacter()
        );
        $this->assertEquals(
            $presenter->response->canReusePasswords,
            $configuration->getCustomConfiguration()->getSecurityPolicy()->canReusePasswords()
        );
        $this->assertEquals($presenter->response->attempts, $configuration->getCustomConfiguration()->getSecurityPolicy()->getAttempts());
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

    /**
     * Test that an error message is returned by the API when no security policy was found.
     * todo
     */
//    public function testFindConfigurationError(): void
//    {
//        $this->providerAuthenticationFactory
//            ->expects($this->once())
//            ->method('create')
//            ->with(Provider::LOCAL)
//            ->willReturn($this->providerAuthentication);
//
//        $this->providerAuthentication
//            ->expects($this->once())
//            ->method('getConfiguration')
//            ->willReturn($configuration)
//            ->willThrowException(new Exception("unknown configuration name, can't load custom config"));
//
//        $useCase = new FindConfiguration($this->readConfigurationFactory);
//        $presenter = new FindConfigurationPresenter($this->presenterFormatter);
//
//        $useCase($presenter);
//
//        $this->assertEquals(
//            $presenter->getResponseStatus(),
//            new FindConfigurationErrorResponse(
//                "unknown configuration name, can't load custom config"
//            )
//        );
//    }
}
