<?php
namespace CentreonRemote\Tests;

use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Pimple\Psr11\ServiceLocator;
use CentreonRemote\ServiceProvider;
use Centreon\Test\Mock;
use CentreonRemote\Domain;
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
            'centreon_remote.remote_connection_service' => Domain\Service\ConfigurationWizard\RemoteConnectionConfigurationService::class,
            'centreon_remote.poller_connection_service' => Domain\Service\ConfigurationWizard\PollerConnectionConfigurationService::class,
            'centreon_remote.poller_config_service' => Domain\Service\ConfigurationWizard\LinkedPollerConfigurationService::class,
            'centreon_remote.poller_config_bridge' => Domain\Service\ConfigurationWizard\PollerConfigurationRequestBridge::class,

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
            Exporter\ConfigurationExporter::class,
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
