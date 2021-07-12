<?php
/*
 * Copyright 2005-2019 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 *
 */

namespace CentreonModule\Tests;

use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Pimple\Psr11\ServiceLocator;
use CentreonModule\ServiceProvider;
use Centreon\Test\Mock;
use CentreonModule\Infrastructure\Service;
use Symfony\Component\Finder\Finder;
use CentreonModule\Application\Webservice;
use Centreon\Infrastructure\Service\CentreonDBManagerService;
use CentreonLegacy\Core\Configuration\Configuration;
use CentreonModule\Tests\Resources\Traits\SourceDependencyTrait;

/**
 * @group CentreonModule
 * @group ServiceProvider
 */
class ServiceProviderTest extends TestCase
{
    use SourceDependencyTrait;

    protected $container;
    protected $provider;

    protected function setUp()
    {
        $this->provider = new ServiceProvider();
        $this->container = new Container;
        $this->container['finder'] = $this->getMockBuilder(Finder::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->setUpSourceDependency($this->container);

        $this->container['configuration'] = $this->createMock(Configuration::class);

        $this->container['realtime_db'] = $this->container['configuration_db'] = new Mock\CentreonDB;
        $this->container['configuration_db']
            ->addResultSet("SELECT `name` AS `id`, `mod_release` AS `version` FROM `modules_informations`", [])
            ->addResultSet("SELECT `directory` AS `id`, `version` FROM `widget_models`", [])
        ;

        $locator = new ServiceLocator($this->container, [
            'realtime_db',
            'configuration_db',
        ]);
        $this->container[\Centreon\ServiceProvider::CENTREON_DB_MANAGER] = new CentreonDBManagerService($locator);
        $this->container[\Centreon\ServiceProvider::CENTREON_WEBSERVICE] = new class {

            protected $services = [];

            public function add($class)
            {
                $this->services[$class] = $class;
            }

            public function getServices(): array
            {
                return $this->services;
            }
        };

        $this->provider->register($this->container);
    }

    /**
     * @covers \CentreonModule\ServiceProvider::register
     */
    public function testCheckServicesByList()
    {
        $checkList = [
            ServiceProvider::CENTREON_MODULE => Service\CentreonModuleService::class,
        ];

        $checkListWebservices = [
            Webservice\CentreonModuleWebservice::class,
            Webservice\CentreonModulesWebservice::class,
        ];

        // check list of services
        foreach ($checkList as $serviceName => $className) {
            $this->assertTrue($this->container->offsetExists($serviceName));

            $service = $this->container->offsetGet($serviceName);

            $this->assertInstanceOf($className, $service);
        }

        // check webservices
        $webservices = $this->container[\Centreon\ServiceProvider::CENTREON_WEBSERVICE]->getServices();
        foreach ($checkListWebservices as $webservice) {
            $this->assertArrayHasKey($webservice, $webservices);
        }
    }

    /**
     * @covers \CentreonModule\ServiceProvider::order
     */
    public function testOrder()
    {
        $this->assertGreaterThanOrEqual(1, $this->provider::order());
        $this->assertLessThanOrEqual(20, $this->provider::order());
    }
}
