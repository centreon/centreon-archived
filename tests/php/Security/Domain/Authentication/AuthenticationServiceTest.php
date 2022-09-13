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

use Core\Security\Authentication\Application\Provider\ProviderAuthenticationFactoryInterface;
use Core\Security\Authentication\Application\Provider\ProviderAuthenticationInterface;
use Core\Security\Authentication\Application\Repository\ReadTokenRepositoryInterface;
use Core\Security\Authentication\Domain\Model\AuthenticationTokens;
use Core\Security\Authentication\Domain\Model\ProviderToken;
use Core\Security\ProviderConfiguration\Application\Repository\ReadConfigurationRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Security\Domain\Authentication\AuthenticationService;
use Security\Domain\Authentication\Exceptions\ProviderException;
use Security\Domain\Authentication\Interfaces\AuthenticationRepositoryInterface;
use Security\Domain\Authentication\Interfaces\SessionRepositoryInterface;
use Core\Security\Authentication\Application\Repository\WriteTokenRepositoryInterface;
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
     * @var SessionRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $sessionRepository;

    /**
     * @var WriteTokenRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $writeTokenRepository;

    /**
     * @var ProviderAuthenticationInterface|\PHPUnit\Framework\MockObject\MockObject
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

    /**
     * @var ProviderToken|\PHPUnit\Framework\MockObject\MockObject
     */
    private $refreshToken;

    /**
     * @var ReadConfigurationRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $readConfigurationFactory;

    /**
     * @var ProviderAuthenticationFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $providerFactory;

    /**
     * @var ReadTokenRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $readTokenRepository;

    protected function setUp(): void
    {
        $this->authenticationRepository = $this->createMock(AuthenticationRepositoryInterface::class);
        $this->sessionRepository = $this->createMock(SessionRepositoryInterface::class);
        $this->writeTokenRepository = $this->createMock(WriteTokenRepositoryInterface::class);
        $this->provider = $this->createMock(ProviderAuthenticationInterface::class);
        $this->authenticationTokens = $this->createMock(AuthenticationTokens::class);
        $this->providerToken = $this->createMock(ProviderToken::class);
        $this->refreshToken = $this->createMock(ProviderToken::class);
        $this->readConfigurationFactory = $this->createMock(ReadConfigurationRepositoryInterface::class);
        $this->providerFactory = $this->createMock(ProviderAuthenticationFactoryInterface::class);
        $this->readTokenRepository = $this->createMock(ReadTokenRepositoryInterface::class);
    }

    /**
     * test isValidToken when authentication tokens are not found
     */
    public function testIsValidTokenTokensNotFound(): void
    {
        $authenticationService = $this->createAuthenticationService();
        $this->readTokenRepository
            ->expects($this->once())
            ->method('findAuthenticationTokensByToken')
            ->with('abc123')
            ->willReturn(null);

        $isValid = $authenticationService->isValidToken('abc123');

        $this->assertEquals(false, $isValid);
    }

    /**
     * test isValidToken when provider is not found
     */
    public function testIsValidTokenProviderNotFound(): void
    {
        $authenticationService = $this->createAuthenticationService();

        $this->readTokenRepository
            ->expects($this->once())
            ->method('findAuthenticationTokensByToken')
            ->with('abc123')
            ->willReturn($this->authenticationTokens);

        $this->providerFactory
            ->expects($this->once())
            ->method('create')
            ->willThrowException(ProviderException::providerNotFound());

        $isValid = $authenticationService->isValidToken('abc123');

        $this->assertEquals(false, $isValid);
    }

    /**
     * test isValidToken when session has expired
     */
    public function testIsValidTokenSessionExpired(): void
    {
        $authenticationService = $this->createAuthenticationService();

        $this->readTokenRepository
            ->expects($this->once())
            ->method('findAuthenticationTokensByToken')
            ->with('abc123')
            ->willReturn($this->authenticationTokens);

        $this->providerFactory
            ->expects($this->once())
            ->method('create')
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

        $isValid = $authenticationService->isValidToken('abc123');

        $this->assertEquals(false, $isValid);
    }

    /**
     * test isValidToken when error happened on refresh token
     */
    public function testIsValidTokenErrorWhileRefreshToken(): void
    {
        $authenticationService = $this->createAuthenticationService();

        $this->readTokenRepository
            ->expects($this->once())
            ->method('findAuthenticationTokensByToken')
            ->with('abc123')
            ->willReturn($this->authenticationTokens);

        $this->providerFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->provider);

        $this->authenticationTokens
            ->expects($this->once())
            ->method('getProviderToken')
            ->willReturn($this->providerToken);

        $this->authenticationTokens
            ->expects($this->any())
            ->method('getProviderRefreshToken')
            ->willReturn($this->refreshToken);

        $this->providerToken
            ->expects($this->once())
            ->method('isExpired')
            ->willReturn(true);

        $this->refreshToken
            ->expects($this->once())
            ->method('isExpired')
            ->willReturn(false);

        $this->provider
            ->expects($this->once())
            ->method('canRefreshToken')
            ->willReturn(true);

        $this->provider
            ->expects($this->once())
            ->method('refreshToken')
            ->willReturn(null);

        $isValid = $authenticationService->isValidToken('abc123');

        $this->assertEquals(false, $isValid);
    }

    /**
     * test isValidToken when token is valid
     */
    public function testIsValidTokenValid(): void
    {
        $authenticationService = $this->createAuthenticationService();

        $this->readTokenRepository
            ->expects($this->once())
            ->method('findAuthenticationTokensByToken')
            ->with('abc123')
            ->willReturn($this->authenticationTokens);

        $this->providerFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->provider);

        $this->providerToken
            ->expects($this->once())
            ->method('isExpired')
            ->willReturn(false);

        $this->authenticationTokens
            ->expects($this->once())
            ->method('getProviderToken')
            ->willReturn($this->providerToken);

        $isValid = $authenticationService->isValidToken('abc123');

        $this->assertEquals(true, $isValid);
    }

    /**
     * test deleteSession on failure
     */
    public function testDeleteSessionFailed(): void
    {
        $authenticationService = $this->createAuthenticationService();

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

        $authenticationService->deleteSession('abc123');
    }

    /**
     * test deleteSession on success
     */
    public function testDeleteSessionSucceed(): void
    {
        $authenticationService = $this->createAuthenticationService();

        $this->authenticationRepository
            ->expects($this->once())
            ->method('deleteSecurityToken')
            ->with('abc123');

        $this->sessionRepository
            ->expects($this->once())
            ->method('deleteSession')
            ->with('abc123');

        $authenticationService->deleteSession('abc123');
    }

    /**
     * test deleteExpiredSecurityTokens on failure
     */
    public function testDeleteExpiredSecurityTokensFailed(): void
    {
        $authenticationService = $this->createAuthenticationService();

        $this->writeTokenRepository
            ->expects($this->once())
            ->method('deleteExpiredSecurityTokens')
            ->willThrowException(new \Exception());

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Error while deleting expired token');

        $authenticationService->deleteExpiredSecurityTokens();
    }

    /**
     * test deleteExpiredSecurityTokens on success
     */
    public function testDeleteExpiredSecurityTokensSucceed(): void
    {
        $authenticationService = $this->createAuthenticationService();

        $this->writeTokenRepository
            ->expects($this->once())
            ->method('deleteExpiredSecurityTokens');

        $authenticationService->deleteExpiredSecurityTokens();
    }

    /**
     * test findAuthenticationTokensByToken on failure
     */
    public function testFindAuthenticationTokensByTokenFailed(): void
    {
        $authenticationService = $this->createAuthenticationService();

        $this->readTokenRepository
            ->expects($this->once())
            ->method('findAuthenticationTokensByToken')
            ->with('abc123')
            ->willThrowException(new \Exception());

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Error while searching authentication tokens');

        $authenticationService->findAuthenticationTokensByToken('abc123');
    }

    /**
     * test findAuthenticationTokensByToken on success
     */
    public function testFindAuthenticationTokensByTokenSucceed(): void
    {
        $authenticationService = $this->createAuthenticationService();

        $this->readTokenRepository
            ->expects($this->once())
            ->method('findAuthenticationTokensByToken')
            ->with('abc123');

        $authenticationService->findAuthenticationTokensByToken('abc123');
    }

    /**
     * test updateAuthenticationTokens on failure
     */
    public function testUpdateAuthenticationTokensFailed(): void
    {
        $authenticationService = $this->createAuthenticationService();

        $this->authenticationRepository
            ->expects($this->once())
            ->method('updateAuthenticationTokens')
            ->with($this->authenticationTokens)
            ->willThrowException(new \Exception());

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Error while updating authentication tokens');

        $authenticationService->updateAuthenticationTokens($this->authenticationTokens);
    }

    /**
     * test updateAuthenticationTokens on success
     */
    public function testUpdateAuthenticationTokensSucceed(): void
    {
        $authenticationService = $this->createAuthenticationService();

        $this->authenticationRepository
            ->expects($this->once())
            ->method('updateAuthenticationTokens')
            ->with($this->authenticationTokens);

        $authenticationService->updateAuthenticationTokens($this->authenticationTokens);
    }

    /**
     * @return AuthenticationService
     */
    private function createAuthenticationService(): AuthenticationService
    {
        return new AuthenticationService(
            $this->authenticationRepository,
            $this->sessionRepository,
            $this->writeTokenRepository,
            $this->readConfigurationFactory,
            $this->providerFactory,
            $this->readTokenRepository
        );
    }
}
