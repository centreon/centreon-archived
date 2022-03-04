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

namespace Tests\Core\Application\Security\ProviderConfiguration\Local\UseCase\FindConfiguration;

use PHPUnit\Framework\TestCase;
use Core\Domain\Security\ProviderConfiguration\Local\Model\Configuration;
use Core\Domain\Security\ProviderConfiguration\Local\Model\SecurityPolicy;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;
use Core\Application\Security\ProviderConfiguration\Local\UseCase\FindConfiguration\FindConfiguration;
use Core\Application\Security\ProviderConfiguration\Local\Repository\ReadConfigurationRepositoryInterface;
use Core\Application\Security\ProviderConfiguration\Local\UseCase\FindConfiguration\FindConfigurationErrorResponse;
use Core\Infrastructure\Security\ProviderConfiguration\Local\Api\FindConfiguration\FindConfigurationPresenter;
use Tests\Core\Application\Security\ProviderConfiguration\Local\UseCase\FindConfiguration\{
    FindConfigurationPresenterFake
};

class FindConfigurationTest extends TestCase
{
    /**
     * @var ReadConfigurationRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $repository;

    /**
     * @var PresenterFormatterInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $presenterFormatter;

    public function setUp(): void
    {
        $this->repository = $this->createMock(ReadConfigurationRepositoryInterface::class);
        $this->presenterFormatter = $this->createMock(PresenterFormatterInterface::class);
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
        $configuration = new Configuration($securityPolicy);

        $useCase = new FindConfiguration($this->repository);

        $presenter = new FindConfigurationPresenterFake();

        $this->repository
            ->expects($this->once())
            ->method('findConfiguration')
            ->willReturn($configuration);

        $useCase($presenter);

        $this->assertEquals(
            $presenter->response->passwordMinimumLength,
            $configuration->getSecurityPolicy()->getPasswordMinimumLength()
        );
        $this->assertEquals($presenter->response->hasUppercase, $configuration->getSecurityPolicy()->hasUppercase());
        $this->assertEquals($presenter->response->hasLowercase, $configuration->getSecurityPolicy()->hasLowercase());
        $this->assertEquals($presenter->response->hasNumber, $configuration->getSecurityPolicy()->hasNumber());
        $this->assertEquals(
            $presenter->response->hasSpecialCharacter,
            $configuration->getSecurityPolicy()->hasSpecialCharacter()
        );
        $this->assertEquals(
            $presenter->response->canReusePasswords,
            $configuration->getSecurityPolicy()->canReusePasswords()
        );
        $this->assertEquals($presenter->response->attempts, $configuration->getSecurityPolicy()->getAttempts());
        $this->assertEquals(
            $presenter->response->blockingDuration,
            $configuration->getSecurityPolicy()->getBlockingDuration()
        );
        $this->assertEquals(
            $presenter->response->passwordExpirationDelay,
            $configuration->getSecurityPolicy()->getPasswordExpirationDelay()
        );
        $this->assertEquals(
            $presenter->response->delayBeforeNewPassword,
            $configuration->getSecurityPolicy()->getDelayBeforeNewPassword()
        );
    }

    /**
     * Test that an error message is returned by the API when no security policy was found.
     */
    public function testFindConfigurationError(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('findConfiguration')
            ->willReturn(null);
        $useCase = new FindConfiguration($this->repository);
        $presenter = new FindConfigurationPresenter($this->presenterFormatter);

        $useCase($presenter);

        $this->assertEquals(
            $presenter->getResponseStatus(),
            new FindConfigurationErrorResponse(
                'Local provider configuration not found. Please verify that your installation is valid'
            )
        );
    }
}
