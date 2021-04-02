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

namespace Tests\Centreon\Infrastructure\Monitoring\Resource\Provider;

use Centreon\Infrastructure\Monitoring\Resource\Provider\HostProvider;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Domain\Monitoring\Resource;
use Centreon\Domain\Monitoring\ResourceFilter;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class HostProviderTest extends TestCase
{
    /**
     * @var DatabaseConnection&MockObject $databaseConnection
     */
    protected $databaseConnection;

    /**
     * @var SqlRequestParametersTranslator&MockObject $sqlRequestParametersTranslator
     */
    protected $sqlRequestParametersTranslator;

    /**
     * @var RequestParameters $requestParameters
     */
    protected $requestParameters;

    /**
     * @var ResourceFilter|null $resourceFilter
     */
    protected $resourceFilter;

    /**
     * @var HostProvider|null $hostProvider
     */
    protected $hostProvider;

    protected function setUp(): void
    {
        $this->databaseConnection = $this->createMock(DatabaseConnection::class);
        $this->sqlRequestParametersTranslator = $this->createMock(SqlRequestParametersTranslator::class);
        $this->requestParameters = new RequestParameters();
        $this->sqlRequestParametersTranslator->expects($this->any())
            ->method('getRequestParameters')
            ->willReturn($this->requestParameters);

        $this->resourceFilter = (new ResourceFilter());

        $this->hostProvider = new HostProvider($this->databaseConnection);
    }

    /**
     * test shouldBeSearched with resource name filter
     */
    public function testShouldBeSearchedWithResourceName()
    {
        $this->hostProvider->setSqlRequestTranslator($this->sqlRequestParametersTranslator);
        $this->requestParameters->setSearch(json_encode([
            'name' => 'test'
        ]));

        $shouldBeSearched = $this->hostProvider->shouldBeSearched($this->resourceFilter);

        $this->assertTrue($shouldBeSearched);
    }

    /**
     * test shouldBeSearched with host name filter
     */
    public function testShouldBeSearchedWithHostName()
    {
        $this->hostProvider->setSqlRequestTranslator($this->sqlRequestParametersTranslator);
        $this->requestParameters->setSearch(json_encode([
            'h.name' => 'test'
        ]));

        $shouldBeSearched = $this->hostProvider->shouldBeSearched($this->resourceFilter);

        $this->assertTrue($shouldBeSearched);
    }

    /**
     * test shouldBeSearched with multiple filter criterias
     */
    public function testShouldBeSearchedWithMultipleCriterias()
    {
        $this->hostProvider->setSqlRequestTranslator($this->sqlRequestParametersTranslator);
        $this->requestParameters->setSearch(json_encode([
            'h.name' => 'test',
            's.description' => 'test'
        ]));

        $shouldBeSearched = $this->hostProvider->shouldBeSearched($this->resourceFilter);

        $this->assertFalse($shouldBeSearched);
    }

    /**
     * test shouldBeSearched with service description filter
     */
    public function testShouldNotBeSearched()
    {
        $this->hostProvider->setSqlRequestTranslator($this->sqlRequestParametersTranslator);
        $this->requestParameters->setSearch(json_encode([
            's.description' => 'test'
        ]));

        $shouldBeSearched = $this->hostProvider->shouldBeSearched($this->resourceFilter);

        $this->assertFalse($shouldBeSearched);
    }

    /**
     * test excludeResourcesWithoutMetrics with one host
     */
    public function testExcludeResourcesWithoutMetrics()
    {
        $hostResource = (new Resource())
            ->setType('host')
            ->setId(1)
            ->setName('host1');
        $serviceResource = (new Resource())
            ->setType('service')
            ->setId(1)
            ->setName('service1')
            ->setParent($hostResource);
        $metaServiceResource = (new Resource())
            ->setType('metaservice')
            ->setId(1)
            ->setName('meta1');
        $resources = [$hostResource, $serviceResource, $metaServiceResource];

        $filteredResources = $this->hostProvider->excludeResourcesWithoutMetrics($resources);

        $this->assertCount(2, $filteredResources);

        foreach ($filteredResources as $filteredResource) {
            $this->assertNotEquals('host', $filteredResource->getType());
        }
    }
}
