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

namespace CentreonUser\Tests;

use PHPUnit\Framework\TestCase;
use Pimple\Container;
use CentreonUser\ServiceProvider;
use Centreon\Tests\Resources\Traits\WebserviceTrait;
use CentreonUser\Application\Webservice;

/**
 * @group CentreonUser
 * @group ServiceProvider
 */
class ServiceProviderTest extends TestCase
{
    use WebserviceTrait;

    /**
     * @var \Pimple\Container
     */
    protected $container;

    /**
     * @var \CentreonUser\ServiceProvider
     */
    protected $provider;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->provider = new ServiceProvider();
        $this->container = new Container();

        $this->setUpWebservice($this->container);

        $this->provider->register($this->container);
    }

    /**
     * Test the webservices registration
     * @covers \CentreonUser\ServiceProvider::register
     */
    public function testWebservices()
    {
        $checkList = [
            Webservice\TimeperiodWebservice::class,
        ];

        $this->checkWebservices($checkList);
    }

    /**
     * Test the method order
     * @covers \CentreonUser\ServiceProvider::order
     */
    public function testOrder()
    {
        $this->assertEquals(51, $this->provider::order());
    }
}
