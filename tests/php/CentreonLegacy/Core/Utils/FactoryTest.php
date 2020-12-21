<?php
/**
 * Copyright 2019 Centreon
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

namespace CentreonLegacy\Core\Utils;

use PHPUnit\Framework\TestCase;
use Centreon\Test\Mock\DependencyInjector\ServiceContainer;
use CentreonLegacy\Core\Utils;
use CentreonLegacy\ServiceProvider;

/**
 * @group CentreonLegacy
 * @group CentreonLegacy\Utils
 */
class FactoryTest extends TestCase
{

    public function setUp(): void
    {
        $this->container = new ServiceContainer();

        $this->container[ServiceProvider::CENTREON_LEGACY_UTILS] = $this
            ->getMockBuilder(Utils\Utils::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function tearDown(): void
    {
        $this->container->terminate();
        $this->container = null;
    }

    public function testNewUtils()
    {
        $factory = new Utils\Factory($this->container);
        $this->assertInstanceOf(Utils\Utils::class, $factory->newUtils());
    }
}
