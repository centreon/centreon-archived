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

namespace CentreonLegacy;

use PHPUnit\Framework\TestCase;
use Centreon\Test\Mock;
use Pimple\Container;
use CentreonLegacy\ServiceProvider;

/**
 * @group CentreonLegacy
 * @group ServiceProvider
 */
class ServiceProviderTest extends TestCase
{

    protected $container;
    protected $provider;

    protected function setUp(): void
    {
        global $conf_centreon, $centreon_path;
        $conf_centreon = [];
        $centreon_path = '_PATH_';

        $this->provider = new ServiceProvider();
        $this->container = new Container;
        $this->container['configuration_db'] = new Mock\CentreonDB;

        $this->provider->register($this->container);
    }

    /**
     * @covers \CentreonLegacy\ServiceProvider::register
     * @covers \CentreonLegacy\ServiceProvider::registerConfiguration
     * @covers \CentreonLegacy\ServiceProvider::registerModule
     * @covers \CentreonLegacy\ServiceProvider::registerWidget
     */
    public function testCheckServicesByList()
    {
        $checkList = [
            ServiceProvider::CONFIGURATION => Core\Configuration\Configuration::class,
            ServiceProvider::CENTREON_LEGACY_UTILS => Core\Utils\Utils::class,
            ServiceProvider::CENTREON_LEGACY_MODULE_HEALTHCHECK => Core\Module\Healthcheck::class,
            ServiceProvider::CENTREON_LEGACY_MODULE_INFORMATION => Core\Module\Information::class,
            ServiceProvider::CENTREON_LEGACY_MODULE_LICENSE => Core\Module\License::class,
            ServiceProvider::CENTREON_LEGACY_LICENSE => Core\Module\License::class, // alias
            ServiceProvider::CENTREON_LEGACY_WIDGET_INFORMATION => Core\Widget\Information::class,
        ];

        // check list of services
        foreach ($checkList as $serviceName => $className) {
            $this->assertTrue($this->container->offsetExists($serviceName));

            $service = $this->container->offsetGet($serviceName);

            $this->assertInstanceOf($className, $service);
        }
    }

    /**
     * Check CentreonRestHttp service
     */
    public function testRegisterRestHttp()
    {
        $this->assertTrue($this->container->offsetExists(ServiceProvider::CENTREON_REST_HTTP));
        $service = $this->container->offsetGet(ServiceProvider::CENTREON_REST_HTTP);

        $this->assertInstanceOf(\Closure::class, $service);
    }

    /**
     * @covers \CentreonLegacy\ServiceProvider::registerModule
     * @covers \CentreonLegacy\ServiceProvider::registerWidget
     */
    public function testCheckServicesByModuleList()
    {
        // mock some of services
        $this->container[ServiceProvider::CENTREON_LEGACY_UTILS] = $this->createMock(Core\Utils\Utils::class);
        $this->container[ServiceProvider::CENTREON_LEGACY_MODULE_INFORMATION] = $this->createMock(Core\Module\Information::class);
        $this->container[ServiceProvider::CENTREON_LEGACY_WIDGET_INFORMATION] = $this->createMock(Core\Widget\Information::class);

        $moduleName = 'mod-name';
        $moduleId = 'mod-id';

        $checkList = [
            ServiceProvider::CENTREON_LEGACY_MODULE_INSTALLER => [
                'class' => Core\Module\Installer::class,
                'attr' => [
                    $moduleName,
                ],
            ],
            ServiceProvider::CENTREON_LEGACY_MODULE_UPGRADER => [
                'class' => Core\Module\Upgrader::class,
                'attr' => [
                    $moduleName,
                    $moduleId,
                ],
            ],
            ServiceProvider::CENTREON_LEGACY_MODULE_REMOVER => [
                'class' => Core\Module\Remover::class,
                'attr' => [
                    $moduleName,
                    $moduleId,
                ],
            ],
            ServiceProvider::CENTREON_LEGACY_WIDGET_INSTALLER => [
                'class' => Core\Widget\Installer::class,
                'attr' => [
                    $moduleName,
                ],
            ],
            ServiceProvider::CENTREON_LEGACY_WIDGET_UPGRADER => [
                'class' => Core\Widget\Upgrader::class,
                'attr' => [
                    $moduleName,
                ],
            ],
            ServiceProvider::CENTREON_LEGACY_WIDGET_REMOVER => [
                'class' => Core\Widget\Remover::class,
                'attr' => [
                    $moduleName,
                ],
            ],
        ];

        // check list of services
        foreach ($checkList as $serviceName => $data) {
            $className = $data['class'];
            $this->assertTrue($this->container->offsetExists($serviceName));

            $callable = $this->container->offsetGet($serviceName);

            $service = call_user_func_array($callable, $data['attr']);

            $this->assertInstanceOf($className, $service);
        }
    }

    /**
     * @covers \CentreonLegacy\ServiceProvider::order
     */
    public function testOrder()
    {
        $this->assertEquals(0, $this->provider::order());
    }
}
