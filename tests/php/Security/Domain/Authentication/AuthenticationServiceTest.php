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

namespace Tests\Security\Domain\Authentication;

use PHPUnit\Framework\TestCase;
use Security\Domain\Authentication\AuthenticationService;
use Security\Domain\Authentication\Interfaces\AuthenticationRepositoryInterface;
use Security\Domain\Authentication\Interfaces\ProviderServiceInterface;
use Security\Domain\Authentication\Interfaces\SessionRepositoryInterface;
use Security\Domain\Authentication\Interfaces\ProviderInterface;;
use Security\Domain\Authentication\Model\ProviderConfiguration;
use Security\Domain\Authentication\Model\AuthenticationTokens;
use Security\Domain\Authentication\Model\ProviderToken;
use Centreon\Domain\Authentication\Exception\AuthenticationException;

/**
 * @package Tests\Security\Domain\Authentication
 */
class AuthenticationServiceTest extends TestCase
{
    /**
     * @var AuthenticationRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $authenticationRepository;

    /**
     * @var ProviderServiceInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $providerService;

    /**
     * @var SessionRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $sessionRepository;

    /**
     * @var ProviderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $provider;

    /**
     * @var AuthenticationTokens|\PHPUnit\Framework\MockObject\MockObject
     */
    private $authenticationTokens;

    /**
     * @var ProviderToken|\PHPUnit\Framework\MockObject\MockObject
     */
    private $providerToken;

    protected function setUp(): void
    {
        $this->authenticationRepository = $this->createMock(AuthenticationRepositoryInterface::class);
        $this->providerService = $this->createMock(ProviderServiceInterface::class);
        $this->sessionRepository = $this->createMock(SessionRepositoryInterface::class);
        $this->provider = $this->createMock(ProviderInterface::class);
        $this->authenticationTokens = $this->createMock(AuthenticationTokens::class);
        $this->providerToken = $this->createMock(ProviderToken::class);
    }

    /**
     * test isValidToken when authentication tokens are not found
     */
    public function testIsValidTokenTokensNotFound(): void
    {
        $providerService = new AuthenticationService(
            $this->authenticationRepository,
            $this->providerService,
            $this->sessionRepository
        );

        $this->authenticationRepository
            ->expects($this->once())
            ->method('findAuthenticationTokensByToken')
            ->with('abc123')
            ->willReturn(null);

        $isValid = $providerService->isValidToken('abc123');

        $this->assertEquals(false, $isValid);
    }

    /**
     * test isValidToken when provider is not found
     */
    public function testIsValidTokenProviderNotFound(): void
    {
        $providerService = new AuthenticationService(
            $this->authenticationRepository,
            $this->providerService,
            $this->sessionRepository
        );

        $this->authenticationRepository
            ->expects($this->once())
            ->method('findAuthenticationTokensByToken')
            ->with('abc123')
            ->willReturn($this->authenticationTokens);

        $this->providerService
            ->expects($this->once())
            ->method('findProviderByConfigurationId')
            ->willReturn(null);

        $isValid = $providerService->isValidToken('abc123');

        $this->assertEquals(false, $isValid);
    }

    /**
     * test isValidToken when session has expired
     */
    public function testIsValidTokenSessionExpired(): void
    {
        $providerService = new AuthenticationService(
            $this->authenticationRepository,
            $this->providerService,
            $this->sessionRepository
        );

        $this->authenticationRepository
            ->expects($this->once())
            ->method('findAuthenticationTokensByToken')
            ->with('abc123')
            ->willReturn($this->authenticationTokens);

        $this->providerService
            ->expects($this->once())
            ->method('findProviderByConfigurationId')
            ->willReturn($this->provider);

        $this->authenticationTokens
            ->expects($this->once())
            ->method('getProviderToken')
            ->willReturn($this->providerToken);

        $this->providerToken
            ->expects($this->once())
            ->method('isExpired')
            ->willReturn(true);

        $this->provider
            ->expects($this->once())
            ->method('canRefreshToken')
            ->willReturn(false);

        $isValid = $providerService->isValidToken('abc123');

        $this->assertEquals(false, $isValid);
    }

    /**
     * test isValidToken when error happened on refresh token
     */
    public function testIsValidTokenErrorWhileRefreshToken(): void
    {
        $providerService = new AuthenticationService(
            $this->authenticationRepository,
            $this->providerService,
            $this->sessionRepository
        );

        $this->authenticationRepository
            ->expects($this->once())
            ->method('findAuthenticationTokensByToken')
            ->with('abc123')
            ->willReturn($this->authenticationTokens);

        $this->providerService
            ->expects($this->once())
            ->method('findProviderByConfigurationId')
            ->willReturn($this->provider);

        $this->authenticationTokens
            ->expects($this->once())
            ->method('getProviderToken')
            ->willReturn($this->providerToken);

        $this->providerToken
            ->expects($this->once())
            ->method('isExpired')
            ->willReturn(true);

        $this->provider
            ->expects($this->once())
            ->method('canRefreshToken')
            ->willReturn(true);

        $this->provider
            ->expects($this->once())
            ->method('refreshToken')
            ->willReturn(null);

        $isValid = $providerService->isValidToken('abc123');

        $this->assertEquals(false, $isValid);
    }

    /**
     * test isValidToken when token is valid
     */
    public function testIsValidTokenValid(): void
    {
        $providerService = new AuthenticationService(
            $this->authenticationRepository,
            $this->providerService,
            $this->sessionRepository
        );

        $this->authenticationRepository
            ->expects($this->once())
            ->method('findAuthenticationTokensByToken')
            ->with('abc123')
            ->willReturn($this->authenticationTokens);

        $this->providerService
            ->expects($this->once())
            ->method('findProviderByConfigurationId')
            ->willReturn($this->provider);

        $this->authenticationTokens
            ->expects($this->once())
            ->method('getProviderToken')
            ->willReturn($this->providerToken);

        $this->providerToken
            ->expects($this->once())
            ->method('isExpired')
            ->willReturn(true);

        $this->provider
            ->expects($this->once())
            ->method('canRefreshToken')
            ->willReturn(true);

        $this->provider
            ->expects($this->once())
            ->method('refreshToken')
            ->willReturn($this->authenticationTokens);

        $isValid = $providerService->isValidToken('abc123');

        $this->assertEquals(true, $isValid);
    }

    /**
     * test deleteSession on failure
     */
    public function testDeleteSessionFailed(): void
    {
        $providerService = new AuthenticationService(
            $this->authenticationRepository,
            $this->providerService,
            $this->sessionRepository
        );

        $this->authenticationRepository
            ->expects($this->once())
            ->method('deleteSecurityToken')
            ->with('abc123');

        $this->sessionRepository
            ->expects($this->once())
            ->method('deleteSession')
            ->willThrowException(new \Exception());

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Error while deleting session');

        $providerService->deleteSession('abc123');
    }

    /**
     * test deleteSession on success
     */
    public function testDeleteSessionSucceed(): void
    {
        $providerService = new AuthenticationService(
            $this->authenticationRepository,
            $this->providerService,
            $this->sessionRepository
        );

        $this->authenticationRepository
            ->expects($this->once())
            ->method('deleteSecurityToken')
            ->with('abc123');

        $this->sessionRepository
            ->expects($this->once())
            ->method('deleteSession')
            ->with('abc123');

        $providerService->deleteSession('abc123');
    }

    /**
     * test deleteExpiredSecurityTokens on failure
     */
    public function testDeleteExpiredSecurityTokensFailed(): void
    {
        $providerService = new AuthenticationService(
            $this->authenticationRepository,
            $this->providerService,
            $this->sessionRepository
        );

        $this->authenticationRepository
            ->expects($this->once())
            ->method('deleteExpiredSecurityTokens')
            ->willThrowException(new \Exception());

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Error while deleting expired token');

        $providerService->deleteExpiredSecurityTokens();
    }

    /**
     * test deleteExpiredSecurityTokens on success
     */
    public function testDeleteExpiredSecurityTokensSucceed(): void
    {
        $providerService = new AuthenticationService(
            $this->authenticationRepository,
            $this->providerService,
            $this->sessionRepository
        );

        $this->authenticationRepository
            ->expects($this->once())
            ->method('deleteExpiredSecurityTokens');

        $providerService->deleteExpiredSecurityTokens();
    }

    /**
     * test findAuthenticationTokensByToken on failure
     */
    public function testFindAuthenticationTokensByTokenFailed(): void
    {
        $providerService = new AuthenticationService(
            $this->authenticationRepository,
            $this->providerService,
            $this->sessionRepository
        );

        $this->authenticationRepository
            ->expects($this->once())
            ->method('findAuthenticationTokensByToken')
            ->with('abc123')
            ->willThrowException(new \Exception());

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Error while searching authentication tokens');

        $providerService->findAuthenticationTokensByToken('abc123');
    }

    /**
     * test findAuthenticationTokensByToken on success
     */
    public function testFindAuthenticationTokensByTokenSucceed(): void
    {
        $providerService = new AuthenticationService(
            $this->authenticationRepository,
            $this->providerService,
            $this->sessionRepository
        );

        $this->authenticationRepository
            ->expects($this->once())
            ->method('findAuthenticationTokensByToken')
            ->with('abc123');

        $providerService->findAuthenticationTokensByToken('abc123');
    }

    /**
     * test updateAuthenticationTokens on failure
     */
    public function testUpdateAuthenticationTokensFailed(): void
    {
        $providerService = new AuthenticationService(
            $this->authenticationRepository,
            $this->providerService,
            $this->sessionRepository
        );

        $this->authenticationRepository
            ->expects($this->once())
            ->method('updateAuthenticationTokens')
            ->with($this->authenticationTokens)
            ->willThrowException(new \Exception());

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Error while updating authentication tokens');

        $providerService->updateAuthenticationTokens($this->authenticationTokens);
    }

    /**
     * test updateAuthenticationTokens on success
     */
    public function testUpdateAuthenticationTokensSucceed(): void
    {
        $providerService = new AuthenticationService(
            $this->authenticationRepository,
            $this->providerService,
            $this->sessionRepository
        );

        $this->authenticationRepository
            ->expects($this->once())
            ->method('updateAuthenticationTokens')
            ->with($this->authenticationTokens);

        $providerService->updateAuthenticationTokens($this->authenticationTokens);
    }
}
