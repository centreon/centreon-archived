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

/**
 * @package Tests\Security\Domain\Authentication
 */
//class ProviderServiceTest extends TestCase
//{
//    /**
//     * @var AuthenticationRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
//     */
//    private $authenticationRepository;
//
//    /**
//     * @var ProviderRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
//     */
//    private $providerRepository;
//
//    /**
//     * @var ProviderFactory|\PHPUnit\Framework\MockObject\MockObject
//     */
//    private $providerFactory;
//
//    /**
//     * @var ProviderConfiguration|\PHPUnit\Framework\MockObject\MockObject
//     */
//    private $providerConfiguration;
//
//    /**
//     * @var ProviderAuthenticationInterface|\PHPUnit\Framework\MockObject\MockObject
//     */
//    private $provider;
//
//    /**
//     * @var AuthenticationTokens|\PHPUnit\Framework\MockObject\MockObject
//     */
//    private $authenticationTokens;
//
//    /**
//     * @var ReadConfigurationFactoryInterface
//     */
//    private ReadConfigurationFactoryInterface $readConfigurationFactory;
//
//    protected function setUp(): void
//    {
//        $this->authenticationRepository = $this->createMock(AuthenticationRepositoryInterface::class);
//        $this->providerRepository = $this->createMock(ProviderRepositoryInterface::class);
//        $this->providerFactory = $this->createMock(ProviderFactory::class);
//        $this->providerConfiguration = new ProviderConfiguration(
//            1,
//            'local',
//            'local',
//            true,
//            true,
//            '/centreon/'
//        );
//        $this->provider = $this->createMock(ProviderAuthenticationInterface::class);
//        $this->authenticationTokens = $this->createMock(AuthenticationTokens::class);
//        $this->readConfigurationFactory = $this->createMock(ReadConfigurationFactoryInterface::class);
//    }
//
//    /**
//     * test findProviderByConfigurationId on failure
//     */
//    public function testFindProviderByConfigurationIdFailed(): void
//    {
//        $providerService = $this->createProviderService();
//
//        $this->providerRepository
//            ->expects($this->once())
//            ->method('findProviderConfiguration')
//            ->with(1)
//            ->willThrowException(new \Exception());
//
//        $this->expectException(ProviderException::class);
//        $this->expectExceptionMessage('Error while searching providers configurations');
//
//        $providerService->findProviderByConfigurationId(1);
//    }
//
//    /**
//     * test findProviderByConfigurationId on success
//     */
//    public function testFindProviderByConfigurationIdSucceed(): void
//    {
//        $providerService = $this->createProviderService();
//
//        $this->providerRepository
//            ->expects($this->once())
//            ->method('findProviderConfiguration')
//            ->with(1)
//            ->willReturn($this->providerConfiguration);
//
//        $this->providerFactory
//            ->expects($this->once())
//            ->method('create')
//            ->with($this->providerConfiguration)
//            ->willReturn($this->provider);
//
//        $provider = $providerService->findProviderByConfigurationId(1);
//
//        $this->assertEquals($this->provider, $provider);
//    }
//
//    /**
//     * test findProviderByConfigurationName on failure
//     */
//    public function testFindProviderByConfigurationNameFailed(): void
//    {
//        $providerService = $this->createProviderService();
//
//        $this->providerRepository
//            ->expects($this->once())
//            ->method('findProviderConfigurationByConfigurationName')
//            ->with('local')
//            ->willThrowException(new \Exception());
//
//        $this->expectException(ProviderException::class);
//        $this->expectExceptionMessage("Error while searching provider configuration: 'local'");
//
//        $providerService->findProviderByConfigurationName('local');
//    }
//
//    /**
//     * test findProviderByConfigurationName on success
//     */
//    public function testFindProviderByConfigurationNameSucceed(): void
//    {
//        $providerService = $this->createProviderService();
//
//        $this->providerRepository
//            ->expects($this->once())
//            ->method('findProviderConfigurationByConfigurationName')
//            ->with('local')
//            ->willReturn($this->providerConfiguration);
//
//        $this->providerFactory
//            ->expects($this->once())
//            ->method('create')
//            ->with($this->providerConfiguration)
//            ->willReturn($this->provider);
//
//        $provider = $providerService->findProviderByConfigurationName('local');
//
//        $this->assertEquals($this->provider, $provider);
//    }
//
//    /**
//     * test findProviderBySession on failure
//     */
//    public function testFindProviderBySessionFailed(): void
//    {
//        $providerService = $this->createProviderService();
//
//        $this->authenticationRepository
//            ->expects($this->once())
//            ->method('findAuthenticationTokensByToken')
//            ->with('abc123')
//            ->willThrowException(new \Exception());
//
//        $this->expectException(AuthenticationException::class);
//        $this->expectExceptionMessage('Error while searching authentication tokens');
//
//        $providerService->findProviderBySession('abc123');
//    }
//
//    /**
//     * test findProviderBySession on success
//     */
//    public function testFindProviderBySessionSucceed(): void
//    {
//        $providerService = $this->createProviderService();
//
//        $this->authenticationRepository
//            ->expects($this->once())
//            ->method('findAuthenticationTokensByToken')
//            ->with('abc123')
//            ->willReturn($this->authenticationTokens);
//
//        $this->authenticationTokens
//            ->expects($this->once())
//            ->method('getConfigurationProviderId')
//            ->willReturn(1);
//
//        $this->providerRepository
//            ->expects($this->once())
//            ->method('findProviderConfiguration')
//            ->with(1)
//            ->willReturn($this->providerConfiguration);
//
//        $this->providerFactory
//            ->expects($this->once())
//            ->method('create')
//            ->with($this->providerConfiguration)
//            ->willReturn($this->provider);
//
//        $provider = $providerService->findProviderBySession('abc123');
//
//        $this->assertEquals($this->provider, $provider);
//    }
//
//    /**
//     * test findProviderConfigurationByConfigurationName on failure
//     */
//    public function testFindProviderConfigurationByConfigurationNameFailed(): void
//    {
//        $providerService = $this->createProviderService();
//
//        $throwedException = new \Exception();
//
//        $this->providerRepository
//            ->expects($this->once())
//            ->method('findProviderConfigurationByConfigurationName')
//            ->with('local')
//            ->willThrowException($throwedException);
//
//        $this->expectException(ProviderException::class);
//        $this->expectExceptionMessage(
//            ProviderException::findProviderConfiguration('local', $throwedException)->getMessage()
//        );
//
//        $providerService->findProviderConfigurationByConfigurationName('local');
//    }
//
//    /**
//     * test findProviderConfigurationByConfigurationName on success
//     */
//    public function testFindProviderConfigurationByConfigurationNameSucceed(): void
//    {
//        $providerService = $this->createProviderService();
//
//        $this->providerRepository
//            ->expects($this->once())
//            ->method('findProviderConfigurationByConfigurationName')
//            ->with('local')
//            ->willReturn($this->providerConfiguration);
//
//        $providerConfiguration = $providerService->findProviderConfigurationByConfigurationName('local');
//
//        $this->assertEquals($this->providerConfiguration, $providerConfiguration);
//    }
//
//    /**
//     * @return ProviderService
//     */
//    private function createProviderService(): ProviderService
//    {
//        return new ProviderService(
//            $this->authenticationRepository,
//            $this->providerRepository,
//            $this->providerFactory,
//            $this->readConfigurationFactory
//        );
//    }
//}
