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

namespace Tests\Core\Application\Security\ProviderConfiguration\Local\UseCase\UpdateConfiguration;

use PHPUnit\Framework\TestCase;
use Core\Domain\Security\ProviderConfiguration\Local\Model\Configuration;
use Core\Application\Security\ProviderConfiguration\Local\Repository\WriteConfigurationRepositoryInterface;
use Core\Application\Security\ProviderConfiguration\Local\UseCase\UpdateConfiguration\UpdateConfiguration;
use Core\Application\Security\ProviderConfiguration\Local\UseCase\UpdateConfiguration\UpdateConfigurationRequest;
use Core\Application\Security\ProviderConfiguration\Local\UseCase\UpdateConfiguration\{
    UpdateConfigurationPresenterInterface
};

class UpdateConfigurationTest extends TestCase
{
    /**
     * @var WriteConfigurationRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $repository;

    /**
     * @var UpdateConfigurationPresenterInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $presenter;

    public function setUp(): void
    {
        $this->repository = $this->createMock(WriteConfigurationRepositoryInterface::class);
        $this->presenter = $this->createMock(UpdateConfigurationPresenterInterface::class);
    }

    /**
     * Test that the use case will correctly be executed.
     */
    public function testUpdateConfiguration(): void
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
            Configuration::MIN_PASSWORD_EXPIRATION_DELAY,
            [],
            Configuration::MIN_NEW_PASSWORD_DELAY
        );

        $request = new UpdateConfigurationRequest();
        $request->passwordMinimumLength = Configuration::MIN_PASSWORD_LENGTH;
        $request->hasUppercase = true;
        $request->hasLowercase = true;
        $request->hasNumber = true;
        $request->hasSpecialCharacter = true;
        $request->canReusePasswords = true;
        $request->attempts = Configuration::MIN_ATTEMPTS;
        $request->blockingDuration = Configuration::MIN_BLOCKING_DURATION;
        $request->passwordExpirationDelay = Configuration::MIN_PASSWORD_EXPIRATION_DELAY;
        $request->delayBeforeNewPassword = Configuration::MIN_NEW_PASSWORD_DELAY;

        $this->repository
            ->expects($this->once())
            ->method('updateConfiguration')
            ->with($configuration);

        $this->presenter
            ->expects($this->once())
            ->method('setResponseStatus');

        $useCase = new UpdateConfiguration($this->repository);
        $useCase($this->presenter, $request);
    }
}
