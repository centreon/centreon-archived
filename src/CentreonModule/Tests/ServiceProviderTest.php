<?php
namespace CentreonModule\Tests;

use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Pimple\Psr11\ServiceLocator;
use CentreonModule\ServiceProvider;
use Centreon\Test\Mock;
use CentreonModule\Domain;
use CentreonModule\Infrastructure\Service;
use CentreonModule\Domain\Exporter;
use Symfony\Component\Finder\Finder;
use CentreonLegacy\Core\Module\License;
use CentreonModule\Application\Webservice;

/**
 * @group CentreonModule
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
        $this->container['finder'] = $this->getMockBuilder(Finder::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->container['centreon.legacy.license'] = $this->getMockBuilder(License::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->container['realtime_db'] = $this->container['configuration_db'] = new Mock\CentreonDB;
        $this->container['configuration_db']
            ->addResultSet("SELECT `name` AS `id`, `mod_release` AS `version` FROM `modules_informations`", [])
            ->addResultSet("SELECT `directory` AS `id`, `version` FROM `widget_models`", [])
        ;

        $locator = new ServiceLocator($this->container, [
            'realtime_db',
            'configuration_db',
        ]);
        $this->container['centreon.db-manager'] = new \Centreon\Infrastructure\Service\CentreonDBManagerService($locator);
        $this->container['centreon.webservice'] = new class {

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
            'centreon.module' => Service\CentreonModuleService::class,
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
        $webservices = $this->container['centreon.webservice']->getServices();
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
