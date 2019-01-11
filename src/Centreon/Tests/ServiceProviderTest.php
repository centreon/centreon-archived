<?php

namespace Centreon\Tests;

use PHPUnit\Framework\TestCase;
use Centreon\Test\Mock;
use Pimple\Container;
use Centreon\ServiceProvider;
use Centreon\Domain;
use Centreon\Infrastructure\Service;
use CentreonClapi\CentreonACL;

/**
 * @group Centreon
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
        $this->container['realtime_db'] = $this->container['configuration_db'] = new Mock\CentreonDB;
//        $this->container['configuration_db']->addResultSet("SELECT * FROM informations WHERE `key` = :key LIMIT 1", []);
        
        $this->provider->register($this->container);
    }

    /**
     * @covers \Centreon\ServiceProvider::register
     */
    public function testCheckServicesByList()
    {
        $services = $this->container->keys();

        $checkList = [
            'centreon.webservice' => Service\CentreonWebserviceService::class,
            'centreon.clapi' => Service\CentreonClapiService::class,
            'centreon.db-manager' => Service\CentreonDBManagerService::class,
            'centreon.keygen' => Domain\Service\AppKeyGeneratorService::class,
            'centreon.acl' => CentreonACL::class,
            'centreon.config' => Service\CentcoreConfigService::class,
        ];

        // check list of services
        foreach ($checkList as $serviceName => $className) {
            $this->assertTrue($this->container->offsetExists($serviceName));
            
            $service = $this->container->offsetGet($serviceName);
            
            $this->assertInstanceOf($className, $service);
        }
    }

    /**
     * @covers \Centreon\ServiceProvider::register
     */
    public function testUserService()
    {
        $this->assertTrue($this->container->offsetExists('centreon.user'));
        
        (new Mock\Centreon())->generateSession();

        $service = $this->container->offsetGet('centreon.user');

        $this->assertInstanceOf(\CentreonUser::class, $service);
    }

    /**
     * @covers \Centreon\ServiceProvider::order
     */
    public function testOrder()
    {
        $this->assertEquals(1, $this->provider::order());
    }
}
