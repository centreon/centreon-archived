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

namespace Tests\Core\Resources\Infrastructure\Repository;

use Centreon\Domain\Monitoring\ResourceFilter;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use Centreon\Domain\RequestParameters\RequestParameters;
use PHPUnit\Framework\TestCase;
use Core\Resources\Infrastructure\Repository\DbReadResourceRepository;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;
use Core\Domain\RealTime\Model\ResourceTypes\ServiceResourceType;
use Centreon\Domain\Contact\Interfaces\ContactInterface;

class DbReadResourceRepositoryTest extends TestCase
{
    private DbReadResourceRepository $repository;
    private DatabaseConnection $dbConnection;
    private SqlRequestParametersTranslator $paramsTranslator;
    private ServiceResourceType $serviceResourceType;

    /**
     * @test
     */
    public function resourceListShouldBeEmptyWhenContactIsNotSet(): void
    {
        $this->createDbConnectionMock();
        $this->createRequestParamTranslatorMock();
        $this->createServiceResourceTypeMock();
        $this->repository = new DbReadResourceRepository(
            $this->dbConnection,
            $this->paramsTranslator,
            new \ArrayObject([$this->serviceResourceType])
        );

        $resources = $this->repository->findResources(new ResourceFilter());

        $this->assertEquals([], $resources);
    }

    /**
     * @test
     */
    public function resourceListShouldBeEmptyWhenContactIsNotAdmin(): void
    {
        $this->createDbConnectionMock();
        $this->createRequestParamTranslatorMock();
        $this->createServiceResourceTypeMock();
        $this->repository = new DbReadResourceRepository(
            $this->dbConnection,
            $this->paramsTranslator,
            new \ArrayObject([$this->serviceResourceType])
        );
        $this->repository->setContact($this->mockContact(false));

        $resources = $this->repository->findResources(new ResourceFilter());

        $this->assertEquals([], $resources);
    }

    private function createDbConnectionMock(): void
    {
        $this->dbConnection = $this->createMock(DatabaseConnection::class);
    }

    private function createRequestParamTranslatorMock(): void
    {
        $requestParams = $this->createMock(RequestParametersInterface::class);

        $requestParams
            ->expects($this->once())
            ->method('setConcordanceStrictMode')
            ->with(RequestParameters::CONCORDANCE_MODE_STRICT)
            ->willReturn($requestParams);

        $requestParams
            ->expects($this->once())
            ->method('setConcordanceErrorMode')
            ->with(RequestParameters::CONCORDANCE_ERRMODE_SILENT)
            ->willReturn($requestParams);

        $this->paramsTranslator = $this->createMock(SqlRequestParametersTranslator::class);

        $this->paramsTranslator
            ->expects($this->once())
            ->method('getRequestParameters')
            ->willReturn($requestParams);
    }

    private function createServiceResourceTypeMock(): void
    {
        $this->serviceResourceType = $this->createMock(ServiceResourceType::class);
    }

    private function mockContact(bool $isAdmin): ContactInterface
    {
        $contact = $this->createMock(ContactInterface::class);
        $contact
            ->method('isAdmin')
            ->willReturn($isAdmin);

        return $contact;
    }
}
