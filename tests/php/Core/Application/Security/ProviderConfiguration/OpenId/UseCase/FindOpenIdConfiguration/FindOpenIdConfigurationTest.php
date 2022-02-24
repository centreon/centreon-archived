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

namespace Tests\Core\Application\Security\ProviderConfiguration\OpenId\UseCase\FindOpenIdConfiguration;

use Core\Application\Common\UseCase\ErrorResponse;
use Core\Application\Common\UseCase\NotFoundResponse;
use Core\Application\Security\ProviderConfiguration\OpenId\Repository\ReadOpenIdConfigurationRepositoryInterface;
use Core\Application\Security\ProviderConfiguration\OpenId\UseCase\FindOpenIdConfiguration\{
    FindOpenIdConfiguration,
    FindOpenIdConfigurationResponse
};
use Core\Domain\Security\ProviderConfiguration\OpenId\Model\OpenIdConfiguration;
use PHPUnit\Framework\TestCase;

class FindOpenIdConfigurationTest extends TestCase
{
    /**
     * @var ReadOpenIdConfigurationRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $repository;

    public function setUp(): void
    {
        $this->repository = $this->createMock(ReadOpenIdConfigurationRepositoryInterface::class);
    }

    /**
     * Test that the find configuration use case is correctly executed.
     */
    public function testFindConfiguration(): void
    {
        $configuration = new OpenIdConfiguration(
            true,
            true,
            [],
            [],
            'http://127.0.0.1/auth/openid-connect',
            '/authorization',
            '/token',
            '/introspect',
            '/userinfo',
            '/logout',
            [],
            'preferred_username',
            'MyCl1ientId',
            'MyCl1ientSuperSecr3tKey',
            'client_secret_post',
            false
        );

        $useCase = new FindOpenIdConfiguration($this->repository);
        $presenter = new FindOpenIdConfigurationPresenterStub();

        $this->repository
            ->expects($this->once())
            ->method('findConfiguration')
            ->willReturn($configuration);

        $useCase($presenter);

        $this->assertInstanceOf(FindOpenIdConfigurationResponse::class, $presenter->response);
        //@todo: Adapt tests with implementation of FindOpenIdConfigurationResponse
    }

    /**
     * Test that a NotFoundResponse is return when no configuration are found.
     */
    public function testFindConfigurationNotFound(): void
    {
        $useCase = new FindOpenIdConfiguration($this->repository);
        $presenter = new FindOpenIdConfigurationPresenterStub();

        $this->repository
            ->expects($this->once())
            ->method('findConfiguration')
            ->willReturn(null);

        $useCase($presenter);

        $this->assertInstanceOf(NotFoundResponse::class, $presenter->getResponseStatus());
        $this->assertEquals('OpenIdConfiguration not found', $presenter->getResponseStatus()?->getMessage());
    }

    /**
     * Test that an ErrorResponse is return when an error occured.
     */
    public function testFindConfigurationError(): void
    {
        $useCase = new FindOpenIdConfiguration($this->repository);
        $presenter = new FindOpenIdConfigurationPresenterStub();

        $this->repository
            ->expects($this->once())
            ->method('findConfiguration')
            ->willThrowException(new \Exception('An error occured'));

        $useCase($presenter);

        $this->assertInstanceOf(ErrorResponse::class, $presenter->getResponseStatus());
        $this->assertEquals('An error occured', $presenter->getResponseStatus()?->getMessage());
    }
}
