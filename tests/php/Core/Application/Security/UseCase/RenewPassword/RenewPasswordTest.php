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

namespace Tests\Application\Security\UseCase\RenewPassword;

use PHPUnit\Framework\TestCase;
use Core\Domain\Security\User\Model\User;
use Core\Domain\Security\User\Model\UserPassword;
use Core\Application\Common\UseCase\NotFoundResponse;
use Core\Application\Common\UseCase\NoContentResponse;
use Core\Application\Common\UseCase\UnauthorizedResponse;
use Core\Application\Security\UseCase\RenewPassword\RenewPassword;
use Core\Application\Security\UseCase\RenewPassword\RenewPasswordRequest;
use Core\Domain\Security\ProviderConfiguration\Local\Model\Configuration;
use Core\Application\Security\User\Repository\ReadUserRepositoryInterface;
use Core\Domain\Security\ProviderConfiguration\Local\Model\SecurityPolicy;
use Core\Application\Security\User\Repository\WriteUserRepositoryInterface;
use Core\Application\Security\UseCase\RenewPassword\RenewPasswordPresenterInterface;
use Core\Application\Security\ProviderConfiguration\Local\Repository\ReadConfigurationRepositoryInterface;

class RenewPasswordTest extends TestCase
{
    /**
     * @var ReadUserRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $readRepository;

    /**
     * @var WriteUserRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $writeRepository;

    /**
     * @var RenewPasswordPresenterInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $presenter;

    /**
     * @var ReadConfigurationRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $readConfigurationRepository;

    public function setUp(): void
    {
        $this->readRepository = $this->createMock(ReadUserRepositoryInterface::class);
        $this->writeRepository = $this->createMock(WriteUserRepositoryInterface::class);
        $this->presenter = $this->createMock(RenewPasswordPresenterInterface::class);
        $this->readConfigurationRepository = $this->createMock(ReadConfigurationRepositoryInterface::class);
    }

    /**
     * Test that a NotFoundResponse is set when the user is not found.
     */
    public function testUseCaseWithNotFoundUser(): void
    {
        $request = new RenewPasswordRequest();
        $request->userAlias = 'invalidUser';
        $request->oldPassword = 'toto';
        $request->newPassword = 'tata';

        $this->readRepository
            ->expects($this->once())
            ->method('findUserByAlias')
            ->willReturn(null);

        $this->presenter
            ->expects($this->once())
            ->method('setResponseStatus')
            ->with(new NotFoundResponse('User'));

        $useCase = new RenewPassword($this->readRepository, $this->writeRepository, $this->readConfigurationRepository);

        $useCase($this->presenter, $request);
    }

    /**
     * Test that an ErrorResponse is set when the password is invalid.
     */
    public function testUseCaseWithInvalidPassword(): void
    {
        $request = new RenewPasswordRequest();
        $request->userAlias = 'admin';
        $request->oldPassword = 'toto';
        $request->newPassword = 'tata';

        $oldPasswords = [];
        $passwordValue = password_hash('titi', \CentreonAuth::PASSWORD_HASH_ALGORITHM);
        $password = new UserPassword(1, $passwordValue, new \DateTimeImmutable());
        $user = new User(1, 'admin', $oldPasswords, $password, null, null);

        $this->readRepository
            ->expects($this->once())
            ->method('findUserByAlias')
            ->willReturn($user);

        $this->presenter
            ->expects($this->once())
            ->method('setResponseStatus')
            ->with(new UnauthorizedResponse('Invalid credentials'));

        $useCase = new RenewPassword($this->readRepository, $this->writeRepository, $this->readConfigurationRepository);

        $useCase($this->presenter, $request);
    }

    /**
     * Test that a no content response is set if everything goes well.
     */
    public function testUseCaseWithValidParameters(): void
    {
        $request = new RenewPasswordRequest();
        $request->userAlias = 'admin';
        $request->oldPassword = 'toto';
        $request->newPassword = 'Centreon!2022';

        $oldPasswords = [];
        $passwordValue = password_hash('toto', \CentreonAuth::PASSWORD_HASH_ALGORITHM);
        $password = new UserPassword(1, $passwordValue, new \DateTimeImmutable());
        $user = new User(1, 'admin', $oldPasswords, $password, null, null);
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

        $this->readRepository
            ->expects($this->once())
            ->method('findUserByAlias')
            ->willReturn($user);

        $this->writeRepository
            ->expects($this->once())
            ->method('renewPassword');

        $this->readConfigurationRepository
            ->expects($this->once())
            ->method('findConfiguration')
            ->willReturn($configuration);

        $this->presenter
            ->expects($this->once())
            ->method('setResponseStatus')
            ->with(new NoContentResponse());

        $useCase = new RenewPassword($this->readRepository, $this->writeRepository, $this->readConfigurationRepository);

        $useCase($this->presenter, $request);
    }
}
