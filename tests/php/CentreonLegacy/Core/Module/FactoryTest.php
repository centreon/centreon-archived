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

use Pimple\Container;
use Centreon\Test\Mock\DependencyInjector\ServiceContainer;
use CentreonLegacy\Core\Module;
use CentreonLegacy\ServiceProvider;

/**
 * Description of factoryTest
 *
 * @author lionel
 */
class FactoryTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        $this->container = new ServiceContainer();

        $this->container[ServiceProvider::CENTREON_LEGACY_MODULE_INFORMATION] = $this
            ->getMockBuilder(Module\Information::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->container[ServiceProvider::CENTREON_LEGACY_MODULE_LICENSE] = $this
            ->getMockBuilder(Module\License::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->container[ServiceProvider::CENTREON_LEGACY_MODULE_INSTALLER] = function (Container $container) {
            return function ($moduleName) {
                return $this->getMockBuilder(Module\Installer::class)
                    ->disableOriginalConstructor()
                    ->getMock();
            };
        };

        $this->container[ServiceProvider::CENTREON_LEGACY_MODULE_UPGRADER] = function (Container $container) {
            return function ($moduleName, $moduleId) {
                return $this->getMockBuilder(Module\Upgrader::class)
                    ->disableOriginalConstructor()
                    ->getMock();
            };
        };

        $this->container[ServiceProvider::CENTREON_LEGACY_MODULE_REMOVER] = function (Container $container) {
            return function ($moduleName, $moduleId) {
                return $this->getMockBuilder(Module\Remover::class)
                    ->disableOriginalConstructor()
                    ->getMock();
            };
        };
    }

    public function tearDown(): void
    {
        $this->container->terminate();
        $this->container = null;
    }
    
    public function testNewInformation()
    {
        $factory = new Module\Factory($this->container);
        $this->assertInstanceOf(Module\Information::class, $factory->newInformation());
    }
    
    public function testNewInstaller()
    {
        $factory = new Module\Factory($this->container);
        $this->assertInstanceOf(Module\Installer::class, $factory->newInstaller('MyModule'));
    }
    
    public function testNewUpgrader()
    {
        $factory = new Module\Factory($this->container);
        $this->assertInstanceOf(Module\Upgrader::class, $factory->newUpgrader('MyModule', 1));
    }
    
    public function testNewRemover()
    {
        $factory = new Module\Factory($this->container);
        $this->assertInstanceOf(Module\Remover::class, $factory->newRemover('MyModule', 1));
    }
    
    public function testNewLicense()
    {
        $factory = new Module\Factory($this->container);
        $this->assertInstanceOf(Module\License::class, $factory->newLicense());
    }
}
