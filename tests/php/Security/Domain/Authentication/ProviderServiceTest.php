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
use Security\Domain\Authentication\ProviderService;
use Security\Domain\Authentication\Interfaces\AuthenticationRepositoryInterface;
use Security\Domain\Authentication\Interfaces\ProviderRepositoryInterface;
use Security\Domain\Authentication\Model\ProviderFactory;
use Security\Domain\Authentication\Interfaces\ProviderInterface;;
use Security\Domain\Authentication\Model\ProviderConfiguration;
use Security\Domain\Authentication\Model\AuthenticationTokens;
use Security\Domain\Authentication\Exceptions\ProviderException;
use Centreon\Domain\Authentication\Exception\AuthenticationException;

/**
 * @package Tests\Security\Domain\Authentication
 */
class ProviderServiceTest extends TestCase
{
    /**
     * @var AuthenticationRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $authenticationRepository;

    /**
     * @var ProviderRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $providerRepository;

    /**
     * @var ProviderFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $providerFactory;

    /**
     * @var ProviderConfiguration|\PHPUnit\Framework\MockObject\MockObject
     */
    private $providerConfiguration;

    /**
     * @var ProviderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $provider;

    /**
     * @var AuthenticationTokens|\PHPUnit\Framework\MockObject\MockObject
     */
    private $authenticationTokens;

    protected function setUp(): void
    {
        $this->authenticationRepository = $this->createMock(AuthenticationRepositoryInterface::class);
        $this->providerRepository = $this->createMock(ProviderRepositoryInterface::class);
        $this->providerFactory = $this->createMock(ProviderFactory::class);
        $this->providerConfiguration = new ProviderConfiguration(
            1,
            'local',
            'local',
            true,
            true,
            '/centreon/'
        );
        $this->provider = $this->createMock(ProviderInterface::class);
        $this->authenticationTokens = $this->createMock(AuthenticationTokens::class);
    }

    /**
     * test findProvidersConfigurations on failure
     */
    public function testFindProvidersConfigurationsFailed(): void
    {
        $providerService = new ProviderService(
            $this->authenticationRepository,
            $this->providerRepository,
            $this->providerFactory
        );

        $this->providerRepository
            ->expects($this->once())
            ->method('findProvidersConfigurations')
            ->willThrowException(new \Exception());

        $this->expectException(ProviderException::class);
        $this->expectExceptionMessage('Error while searching providers configurations');

        $providerService->findProvidersConfigurations();
    }

    /**
     * test findProvidersConfigurations on success
     */
    public function testFindProvidersConfigurationsSucceed(): void
    {
        $providerService = new ProviderService(
            $this->authenticationRepository,
            $this->providerRepository,
            $this->providerFactory
        );

        $this->providerRepository
            ->expects($this->once())
            ->method('findProvidersConfigurations')
            ->willReturn([$this->providerConfiguration]);

        $providersConfigurations = $providerService->findProvidersConfigurations();

        $this->assertEquals($this->providerConfiguration, $providersConfigurations[0]);
    }

    /**
     * test findProviderByConfigurationId on failure
     */
    public function testFindProviderByConfigurationIdFailed(): void
    {
        $providerService = new ProviderService(
            $this->authenticationRepository,
            $this->providerRepository,
            $this->providerFactory
        );

        $this->providerRepository
            ->expects($this->once())
            ->method('findProviderConfiguration')
            ->with(1)
            ->willThrowException(new \Exception());

        $this->expectException(ProviderException::class);
        $this->expectExceptionMessage('Error while searching providers configurations');

        $providerService->findProviderByConfigurationId(1);
    }

    /**
     * test findProviderByConfigurationId on success
     */
    public function testFindProviderByConfigurationIdSucceed(): void
    {
        $providerService = new ProviderService(
            $this->authenticationRepository,
            $this->providerRepository,
            $this->providerFactory
        );

        $this->providerRepository
            ->expects($this->once())
            ->method('findProviderConfiguration')
            ->with(1)
            ->willReturn($this->providerConfiguration);

        $this->providerFactory
            ->expects($this->once())
            ->method('create')
            ->with($this->providerConfiguration)
            ->willReturn($this->provider);

        $provider = $providerService->findProviderByConfigurationId(1);

        $this->assertEquals($this->provider, $provider);
    }

    /**
     * test findProviderByConfigurationName on failure
     */
    public function testFindProviderByConfigurationNameFailed(): void
    {
        $providerService = new ProviderService(
            $this->authenticationRepository,
            $this->providerRepository,
            $this->providerFactory
        );

        $this->providerRepository
            ->expects($this->once())
            ->method('findProviderConfigurationByConfigurationName')
            ->with('local')
            ->willThrowException(new \Exception());

        $this->expectException(ProviderException::class);
        $this->expectExceptionMessage("Error while searching provider configuration: 'local'");

        $providerService->findProviderByConfigurationName('local');
    }

    /**
     * test findProviderByConfigurationName on success
     */
    public function testFindProviderByConfigurationNameSucceed(): void
    {
        $providerService = new ProviderService(
            $this->authenticationRepository,
            $this->providerRepository,
            $this->providerFactory
        );

        $this->providerRepository
            ->expects($this->once())
            ->method('findProviderConfigurationByConfigurationName')
            ->with('local')
            ->willReturn($this->providerConfiguration);

        $this->providerFactory
            ->expects($this->once())
            ->method('create')
            ->with($this->providerConfiguration)
            ->willReturn($this->provider);

        $provider = $providerService->findProviderByConfigurationName('local');

        $this->assertEquals($this->provider, $provider);
    }

    /**
     * test findProviderBySession on failure
     */
    public function testFindProviderBySessionFailed(): void
    {
        $providerService = new ProviderService(
            $this->authenticationRepository,
            $this->providerRepository,
            $this->providerFactory
        );

        $this->authenticationRepository
            ->expects($this->once())
            ->method('findAuthenticationTokensByToken')
            ->with('abc123')
            ->willThrowException(new \Exception());

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Error while searching authentication tokens');

        $providerService->findProviderBySession('abc123');
    }

    /**
     * test findProviderBySession on success
     */
    public function testFindProviderBySessionSucceed(): void
    {
        $providerService = new ProviderService(
            $this->authenticationRepository,
            $this->providerRepository,
            $this->providerFactory
        );

        $this->authenticationRepository
            ->expects($this->once())
            ->method('findAuthenticationTokensByToken')
            ->with('abc123')
            ->willReturn($this->authenticationTokens);

        $this->authenticationTokens
            ->expects($this->once())
            ->method('getConfigurationProviderId')
            ->willReturn(1);

        $this->providerRepository
            ->expects($this->once())
            ->method('findProviderConfiguration')
            ->with(1)
            ->willReturn($this->providerConfiguration);

        $this->providerFactory
            ->expects($this->once())
            ->method('create')
            ->with($this->providerConfiguration)
            ->willReturn($this->provider);

        $provider = $providerService->findProviderBySession('abc123');

        $this->assertEquals($this->provider, $provider);
    }

    /**
     * test findProviderConfigurationByConfigurationName on failure
     */
    public function testFindProviderConfigurationByConfigurationNameFailed(): void
    {
        $providerService = new ProviderService(
            $this->authenticationRepository,
            $this->providerRepository,
            $this->providerFactory
        );

        $this->providerRepository
            ->expects($this->once())
            ->method('findProviderConfigurationByConfigurationName')
            ->with('local')
            ->willThrowException(new \Exception());

        $this->expectException(ProviderException::class);
        $this->expectExceptionMessage('Error while searching providers configurations');

        $providerService->findProviderConfigurationByConfigurationName('local');
    }

    /**
     * test findProviderConfigurationByConfigurationName on success
     */
    public function testFindProviderConfigurationByConfigurationNameSucceed(): void
    {
        $providerService = new ProviderService(
            $this->authenticationRepository,
            $this->providerRepository,
            $this->providerFactory
        );

        $this->providerRepository
            ->expects($this->once())
            ->method('findProviderConfigurationByConfigurationName')
            ->with('local')
            ->willReturn($this->providerConfiguration);

        $providerConfiguration = $providerService->findProviderConfigurationByConfigurationName('local');

        $this->assertEquals($this->providerConfiguration, $providerConfiguration);
    }
}
