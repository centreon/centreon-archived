<?php

/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
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

namespace CentreonUser\Tests\Application\Webservice;

use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Centreon\ServiceProvider;
use CentreonUser\Application\Webservice\TimeperiodWebservice;
use Centreon\Tests\Resources\Mock\CentreonPaginationServiceMock;
use Centreon\Tests\Resources\Traits;

/**
 * @group CentreonUser
 * @group Webservice
 */
class TimeperiodWebserviceTest extends TestCase
{
    use Traits\WebServiceAuthorizeRestApiTrait;
    use Traits\WebServiceExecuteTestTrait;

    protected const METHOD_GET_LIST = 'getList';

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        // dependencies
        $container = new Container();
        $container[ServiceProvider::CENTREON_PAGINATION] = new CentreonPaginationServiceMock();

        $this->webservice = $this->createPartialMock(TimeperiodWebservice::class, [
            'loadDb',
            'loadArguments',
            'loadToken',
            'query',
        ]);

        // load dependencies
        $this->webservice->setDi($container);
        $this->fixturePath = __DIR__ . '/../../Resources/Fixture/';
    }

    /**
     * Test the method getList
     */
    public function testGetList()
    {
        // without applied filters
        $this->mockQuery();
        $this->executeTest(static::METHOD_GET_LIST, 'response-list-1.json');
    }

    /**
     * Test the method getList with different filter
     */
    public function testGetList2()
    {
        // with search, searchByIds, limit, and offset
        $this->mockQuery([
            'search' => 'test',
            'searchByIds' => '3,5,7',
            'limit' => '1a',
            'offset' => '2b',
        ]);
        $this->executeTest(static::METHOD_GET_LIST, 'response-list-2.json');
    }

    /**
     * Test the method getName
     */
    public function testGetName()
    {
        $this->assertEquals('centreon_timeperiod', TimeperiodWebservice::getName());
    }
}
