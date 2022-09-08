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

namespace Tests\Centreon\Domain\Authentication\UseCase;

use Centreon\Domain\Authentication\Exception\AuthenticationException;
use Centreon\Domain\Authentication\UseCase\AuthenticateApi;
use Centreon\Domain\Authentication\UseCase\AuthenticateApiRequest;
use Centreon\Domain\Authentication\UseCase\AuthenticateApiResponse;
use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Core\Security\Authentication\Application\Provider\ProviderAuthenticationInterface;
use Core\Security\Authentication\Application\Provider\ProviderAuthenticationFactoryInterface;
use Core\Security\Authentication\Application\Repository\WriteTokenRepositoryInterface;
use Core\Security\Authentication\Application\UseCase\Login\LoginRequest;
use Core\Security\Authentication\Domain\Model\NewProviderToken;
use Core\Security\ProviderConfiguration\Domain\Model\Configuration;
use Core\Security\ProviderConfiguration\Domain\Model\Provider;
use PHPUnit\Framework\TestCase;
use Security\Domain\Authentication\Exceptions\ProviderException;
use Security\Domain\Authentication\Interfaces\AuthenticationRepositoryInterface;
use Security\Domain\Authentication\Interfaces\AuthenticationServiceInterface;

/**
 * @package Tests\Centreon\Domain\Authentication\UseCase
 */
class AuthenticateApiTest extends TestCase
{
    /**
     * @var AuthenticationServiceInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $authenticationService;

    /**
     * @var ProviderAuthenticationFactoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private ProviderAuthenticationFactoryInterface $providerFactory;

    /**
     * @var WriteTokenRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private WriteTokenRepositoryInterface $writeTokenRepository;

    /**
     * @var NewProviderToken
     */
    private NewProviderToken $providerToken;

    /**
     * @var Contact
     */
    private Contact $contact;

    /**
     * @var Configuration&\PHPUnit\Framework\MockObject\MockObject
     */
    private Configuration $configuration;

    /**
     * @var ProviderAuthenticationInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private ProviderAuthenticationInterface $providerAuthentication;

    protected function setUp(): void
    {
        $this->authenticationService = $this->createMock(AuthenticationServiceInterface::class);
        $this->providerFactory = $this->createMock(ProviderAuthenticationFactoryInterface::class);
        $this->providerAuthentication = $this->createMock(ProviderAuthenticationInterface::class);
        $this->configuration = $this->createMock(Configuration::class);
        $this->providerToken = $this->createMock(NewProviderToken::class);
        $this->writeTokenRepository = $this->createMock(WriteTokenRepositoryInterface::class);
        $this->contact = (new Contact())
            ->setId(1)
            ->setName('contact_name1')
            ->setAlias('contact_alias1')
            ->setEmail('root@localhost')
            ->setAdmin(true);
    }

    /**
     * test execute when local provider is not found
     */
    public function testExecuteLocalProviderNotFound(): void
    {
        $authenticateApi = $this->createAuthenticationAPI();
        $authenticateApiRequest = new AuthenticateApiRequest('admin', 'centreon');
        $authenticateApiResponse = new AuthenticateApiResponse();

        $this->authenticationService
            ->expects($this->once())
            ->method('deleteExpiredSecurityTokens');

        $this->providerFactory
            ->expects($this->once())
            ->method('create')
            ->with(Provider::LOCAL)
            ->willThrowException(ProviderException::providerConfigurationNotFound(Provider::LOCAL));

        $this->expectException(ProviderException::class);
        $this->expectExceptionMessage('Provider configuration (local) not found');

        $authenticateApi->execute($authenticateApiRequest, $authenticateApiResponse);
    }

    /**
     * test execute when user is not authenticated by provider
     */
    public function testExecuteUserNotAuthenticated(): void
    {
        $authenticateApi = $this->createAuthenticationAPI();
        $authenticateApiRequest = new AuthenticateApiRequest('admin', 'centreon');
        $authenticateApiResponse = new AuthenticateApiResponse();

        $this->authenticationService
            ->expects($this->once())
            ->method('deleteExpiredSecurityTokens');

        $this->providerFactory
            ->expects($this->once())
            ->method('create')
            ->with(Provider::LOCAL)
            ->willReturn($this->providerAuthentication);

        $this->providerAuthentication
            ->expects($this->once())
            ->method('authenticateOrFail')
            ->with(LoginRequest::createForLocal('admin', 'centreon'))
            ->willThrowException(AuthenticationException::invalidCredentials());

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid Credentials');

        $authenticateApi->execute($authenticateApiRequest, $authenticateApiResponse);
    }

    /**
     * test execute when user is not found
     */
    public function testExecuteUserNotFound(): void
    {
        $authenticateApi = $this->createAuthenticationAPI();
        $authenticateApiRequest = new AuthenticateApiRequest('admin', 'centreon');
        $authenticateApiResponse = new AuthenticateApiResponse();

        $this->authenticationService
            ->expects($this->once())
            ->method('deleteExpiredSecurityTokens');

        $this->providerFactory
            ->expects($this->once())
            ->method('create')
            ->with(Provider::LOCAL)
            ->willReturn($this->providerAuthentication);

        $this->providerAuthentication
            ->expects($this->once())
            ->method('authenticateOrFail')
            ->with(LoginRequest::createForLocal('admin', 'centreon'));

        $this->providerAuthentication
            ->expects($this->once())
            ->method('getAuthenticatedUser')
            ->willReturn(null);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('User cannot be retrieved from the provider');

        $authenticateApi->execute($authenticateApiRequest, $authenticateApiResponse);
    }

    /**
     * test execute when tokens cannot be created
     */
    public function testExecuteCannotCreateTokens(): void
    {
        $authenticateApi = $this->createAuthenticationAPI();
        $authenticateApiRequest = new AuthenticateApiRequest('admin', 'centreon');
        $authenticateApiResponse = new AuthenticateApiResponse();

        $this->authenticationService
            ->expects($this->once())
            ->method('deleteExpiredSecurityTokens');

        $this->providerFactory
            ->expects($this->once())
            ->method('create')
            ->with(Provider::LOCAL)
            ->willReturn($this->providerAuthentication);

        $this->providerAuthentication
            ->expects($this->once())
            ->method('authenticateOrFail')
            ->with(LoginRequest::createForLocal('admin', 'centreon'));

        $this->providerAuthentication
            ->expects($this->once())
            ->method('getAuthenticatedUser')
            ->willReturn($this->contact);

        $this->providerAuthentication
            ->expects($this->once())
            ->method('getConfiguration')
            ->willReturn($this->configuration);

        $this->providerAuthentication
            ->expects($this->once())
            ->method('getProviderToken')
            ->willReturn($this->providerToken);

        $authenticateApi->execute($authenticateApiRequest, $authenticateApiResponse);
    }

    /**
     * test execute succeed
     */
    public function testExecuteSucceed(): void
    {
        $authenticateApi = $this->createAuthenticationAPI();
        $authenticateApiRequest = new AuthenticateApiRequest('admin', 'centreon');
        $authenticateApiResponse = new AuthenticateApiResponse();

        $this->authenticationService
            ->expects($this->once())
            ->method('deleteExpiredSecurityTokens');

        $this->providerFactory
            ->expects($this->once())
            ->method('create')
            ->with(Provider::LOCAL)
            ->willReturn($this->providerAuthentication);

        $this->providerAuthentication
            ->expects($this->once())
            ->method('authenticateOrFail')
            ->with(LoginRequest::createForLocal('admin', 'centreon'));

        $this->providerAuthentication
            ->expects($this->once())
            ->method('getAuthenticatedUser')
            ->willReturn($this->contact);

        $this->providerAuthentication
            ->expects($this->once())
            ->method('getConfiguration')
            ->willReturn($this->configuration);

        $this->providerAuthentication
            ->expects($this->once())
            ->method('getProviderToken')
            ->willReturn($this->providerToken);

        $authenticateApi->execute($authenticateApiRequest, $authenticateApiResponse);

        $this->assertEqualsCanonicalizing(
            [
                'id' => 1,
                'name' => 'contact_name1',
                'alias' => 'contact_alias1',
                'email' => 'root@localhost',
                'is_admin' => true,
            ],
            $authenticateApiResponse->getApiAuthentication()['contact']
        );

        $this->assertIsString($authenticateApiResponse->getApiAuthentication()['security']['token']);
    }

    /**
     * @return AuthenticateApi
     */
    private function createAuthenticationAPI(): AuthenticateApi
    {
        return new AuthenticateApi(
            $this->authenticationService,
            $this->writeTokenRepository,
            $this->providerFactory
        );
    }
}
