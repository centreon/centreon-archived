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
        $configuration = new Configuration(
            Configuration::MIN_PASSWORD_LENGTH,
            true,
            true,
            true,
            true,
            true,
            Configuration::MIN_ATTEMPTS,
            Configuration::MIN_BLOCKING_DURATION,
            Configuration::MIN_PASSWORD_EXPIRATION,
            Configuration::MIN_NEW_PASSWORD_DELAY
        );

        $useCase = new FindConfiguration($this->repository);

        $presenter = new FindConfigurationPresenterFake();

        $this->repository
            ->expects($this->once())
            ->method('findConfiguration')
            ->willReturn($configuration);

        $useCase($presenter);

        $this->assertEquals($presenter->response->passwordMinimumLength, $configuration->getPasswordMinimumLength());
        $this->assertEquals($presenter->response->hasUppercase, $configuration->hasUppercase());
        $this->assertEquals($presenter->response->hasLowercase, $configuration->hasLowercase());
        $this->assertEquals($presenter->response->hasNumber, $configuration->hasNumber());
        $this->assertEquals($presenter->response->hasSpecialCharacter, $configuration->hasSpecialCharacter());
        $this->assertEquals($presenter->response->canReusePasswords, $configuration->canReusePasswords());
        $this->assertEquals($presenter->response->attempts, $configuration->getAttempts());
        $this->assertEquals($presenter->response->blockingDuration, $configuration->getBlockingDuration());
        $this->assertEquals($presenter->response->passwordExpiration, $configuration->getPasswordExpiration());
        $this->assertEquals($presenter->response->delayBeforeNewPassword, $configuration->getDelayBeforeNewPassword());
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
