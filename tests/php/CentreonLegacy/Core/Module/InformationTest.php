<?php
/**
 * Copyright 2016 Centreon
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
 */

namespace CentreonLegacy\Core\Module;

use CentreonLegacy\Core\Module\Information;

class InformationTest extends \PHPUnit_Framework_TestCase
{
    private $container;

    public function setUp()
    {
        $this->container = new ServiceContainer();
        $this->container->registerProvider(new ConnectionProvider($schema));
    }

    public function tearDown()
    {

    }

    public function testGetConfiguration()
    {
        $informationObj = new Information();

    }
}
