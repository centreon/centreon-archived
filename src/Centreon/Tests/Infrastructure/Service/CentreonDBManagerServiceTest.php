<?php

namespace Centreon\Tests\Infrastructure\Service;

use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Pimple\Psr11\Container as ContainerWrap;
use Centreon\Infrastructure\Service\CentreonDBManagerService;
use Centreon\Infrastructure\CentreonLegacyDB\CentreonDBAdapter;
use Centreon\Test\Mock\CentreonDB;
use Centreon\Tests\Resource\Mock\RepositoryMock;

class CentreonDBManagerServiceTest extends TestCase
{

    /**
     * @var \Centreon\Infrastructure\Service\CentreonDBManagerService
     */
    protected $service;

    public function setUp()
    {
        $this->db1 = new CentreonDB('database_1');
        $this->db2 = new CentreonDB('database_2');

        $container = new Container;
        $container['configuration_db'] = $this->db1;
        $container['realtime_db'] = $this->db2;

        $this->service = new CentreonDBManagerService(new ContainerWrap($container));
    }

    public function testGetAdapter()
    {
        (function () {
            $adapter = $this->service->getAdapter('configuration_db');

            $this->assertInstanceOf(CentreonDBAdapter::class, $adapter);
            $this->assertEquals($this->db1, $adapter->getCentreonDBInstance());
        })();

        (function () {
            $adapter = $this->service->getAdapter('realtime_db');

            $this->assertInstanceOf(CentreonDBAdapter::class, $adapter);
            $this->assertEquals($this->db2, $adapter->getCentreonDBInstance());
        })();
    }

    public function testGetDefaultAdapter()
    {
        $adapter = $this->service->getDefaultAdapter();

        $this->assertInstanceOf(CentreonDBAdapter::class, $adapter);
        $this->assertEquals($this->db1, $adapter->getCentreonDBInstance());
    }

    public function testGetRepository()
    {
        $repository = $this->service->getRepository(RepositoryMock::class);

        $this->assertInstanceOf(RepositoryMock::class, $repository);
    }
}
