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

namespace CentreonLegacy\Core\Module;

use PHPUnit\Framework\TestCase;
use Pimple\Psr11\Container;
use Centreon\Test\Mock\DependencyInjector\ServiceContainer;
use CentreonLegacy\Core\Module;
use CentreonLegacy\ServiceProvider;
use CentreonLegacy\Core\Module\Healthcheck;

/**
 * @group CentreonLegacy
 * @group CentreonLegacy\Module
 */
class LicenseTest extends TestCase
{

    public function setUp(): void
    {
        $this->container = new ServiceContainer();
        $this->container[ServiceProvider::CENTREON_LEGACY_MODULE_HEALTHCHECK] = $this->createMock(Healthcheck::class);

        $this->service = new Module\License(new Container($this->container));
    }

    public function tearDown(): void
    {
        $this->container->terminate();
        $this->container = null;
    }

    public function testGetLicenseExpiration()
    {
        $module = 'mod';
        $value = null;

        $result = $this->service->getLicenseExpiration($module);

        $this->assertEquals($result, $value);
    }

    public function testGetLicenseExpirationWithException()
    {
        $module = 'mod';
        $value = null;

        $this->container[ServiceProvider::CENTREON_LEGACY_MODULE_HEALTHCHECK] = $this
            ->getMockBuilder(Healthcheck::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'check',
            ])
            ->getMock();
        $this->container[ServiceProvider::CENTREON_LEGACY_MODULE_HEALTHCHECK]
            ->method('check')
            ->will($this->returnCallback(function () {
                    throw new \Exception;
                }));

        $result = $this->service->getLicenseExpiration($module);

        $this->assertEquals($result, $value);
    }

    public function testGetLicenseExpirationWithExpirationDate()
    {
        $module = 'mod';
        $value = date(\DateTime::ISO8601);

        $this->container[ServiceProvider::CENTREON_LEGACY_MODULE_HEALTHCHECK] = $this
            ->getMockBuilder(Healthcheck::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getLicenseExpiration',
            ])
            ->getMock();
        $this->container[ServiceProvider::CENTREON_LEGACY_MODULE_HEALTHCHECK]
            ->method('getLicenseExpiration')
            ->will($this->returnCallback(function () use ($value) {
                    return new \DateTime($value);
                }));

        $result = $this->service->getLicenseExpiration($module);

        $this->assertEquals($result, $value);
    }
}
