<?php
/*
 * Copyright 2005-2019 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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

namespace CentreonRemote\Tests;

use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Pimple\Psr11\ServiceLocator;
use CentreonRemote\ServiceProvider;
use Centreon\Test\Mock;
use CentreonRemote\Domain;
use CentreonRemote\Domain\Service\ConfigurationWizard;
use CentreonRemote\Infrastructure\Service;
use CentreonRemote\Domain\Exporter;
use CentreonACL;
use CentreonRestHttp;

/**
 * @group CentreonRemote
 * @group ServiceProvider
 */
class ServiceProviderTest extends TestCase
{

    protected $container;
    protected $provider;

    protected function setUp()
    {
        $this->provider = new ServiceProvider();
        $this->container = new Container;
        $this->container['centreon.acl'] = $this->getMockBuilder(CentreonACL::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->container['realtime_db'] = $this->container['configuration_db'] = new Mock\CentreonDB;
        $this->container['configuration_db']->addResultSet("SELECT * FROM informations WHERE `key` = :key LIMIT 1", []);

        $this->container['rest_http'] = $this->getMockBuilder(CentreonRestHttp::class)
            ->disableOriginalConstructor()
            ->getMock();

        $locator = new ServiceLocator($this->container, ['realtime_db', 'configuration_db']);
        $this->container[\Centreon\ServiceProvider::CENTREON_DB_MANAGER] =
            new \Centreon\Infrastructure\Service\CentreonDBManagerService($locator);
        $this->container[\Centreon\ServiceProvider::CENTREON_WEBSERVICE] =
            $this->container[\Centreon\ServiceProvider::CENTREON_CLAPI] = new class {
                public function add($class)
                {
                }
            };

        $this->container['yml.config'] = function () {
            return [];
        };

        $this->container[\Centreon\ServiceProvider::CENTREON_BROKER_REPOSITORY] =
            new \Centreon\Domain\Repository\CfgCentreonBrokerRepository($this->container['configuration_db']);
        $this->container['centreon.broker_configuration_service'] =
            new \Centreon\Domain\Service\BrokerConfigurationService();

        $this->provider->register($this->container);
    }

    /**
     * @covers \CentreonRemote\ServiceProvider::register
     */
    public function testCheckServicesByList()
    {
        $services = $this->container->keys();

        $checkList = [
            'centreon.notifymaster' => Domain\Service\NotifyMasterService::class,
            'centreon.taskservice' => Domain\Service\TaskService::class,
            'centreon_remote.informations_service' => Domain\Service\InformationsService::class,
            'centreon_remote.remote_connection_service' =>
                ConfigurationWizard\RemoteConnectionConfigurationService::class,
            'centreon_remote.poller_connection_service' =>
                ConfigurationWizard\PollerConnectionConfigurationService::class,
            'centreon_remote.poller_config_service' =>
                ConfigurationWizard\LinkedPollerConfigurationService::class,
            'centreon_remote.poller_config_bridge' =>
                ConfigurationWizard\PollerConfigurationRequestBridge::class,

            'centreon_remote.export' => Service\ExportService::class,
            'centreon_remote.exporter' => Service\ExporterService::class,
            'centreon_remote.exporter.cache' => Service\ExporterCacheService::class,
        ];

        // check list of services
        foreach ($checkList as $serviceName => $className) {
            $this->assertTrue($this->container->offsetExists($serviceName));

            $service = $this->container->offsetGet($serviceName);

            $this->assertInstanceOf($className, $service);
        }
    }

    /**
     * @covers \CentreonRemote\ServiceProvider::register
     */
    public function testCheckExportersByList()
    {
        $checkList = [
            Exporter\CommandExporter::class,
            Exporter\DowntimeExporter::class,
            Exporter\GraphExporter::class,
            Exporter\HostExporter::class,
            Exporter\MediaExporter::class,
            Exporter\MetaServiceExporter::class,
            Exporter\PollerExporter::class,
            Exporter\ServiceExporter::class,
            Exporter\TimePeriodExporter::class,
            Exporter\TrapExporter::class,
        ];

        $exporter = $this->container['centreon_remote.exporter'];

        // check list of exporters
        foreach ($checkList as $className) {
            $name = $className::getName();

            $this->assertTrue($exporter->has($name));
            $data = $exporter->get($className::getName());

            $this->assertEquals($name, $data['name']);
            $this->assertEquals($className, $data['classname']);

            $object = $data['factory']($this->container);

            $this->assertInstanceOf($className, $object);
        }
    }

    /**
     * @covers \CentreonRemote\ServiceProvider::order
     */
    public function testOrder()
    {
        $this->assertGreaterThanOrEqual(1, $this->provider::order());
        $this->assertLessThanOrEqual(20, $this->provider::order());
    }
}
