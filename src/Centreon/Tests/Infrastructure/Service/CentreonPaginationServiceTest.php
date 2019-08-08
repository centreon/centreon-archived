<?php

namespace Centreon\Tests\Infrastructure\Service;

use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Pimple\Psr11\Container as ContainerWrap;
use Centreon\Infrastructure\Service\CentreonPaginationService;
use Centreon\Application\DataRepresenter;
use Centreon\ServiceProvider;
use Centreon\Tests\Resource\Mock;
use Centreon\Tests\Resource\Dependency;

class CentreonPaginationServiceTest extends TestCase
{
    use Dependency\CentreonDbManagerDependencyTrait;

    /**
     * @var \Centreon\Infrastructure\Service\CentreonPaginationService
     */
    protected $service;

    public function setUp()
    {
        $this->container = new Container;
        $this->setUpCentreonDbManager($this->container);

        $this->service = new CentreonPaginationService(new ContainerWrap($this->container));
    }

    public function testDependencies()
    {
        $this->assertEquals([
            ServiceProvider::CENTREON_DB_MANAGER,
        ], $this->service::dependencies());
    }

    public function testGetResponse()
    {
        $this->container[ServiceProvider::CENTREON_DB_MANAGER]
            ->addResultSet(
                'SELECT SQL_CALC_FOUND_ROWS t.* FROM `mock_table` AS `t` '
                . 'WHERE `name_column` LIKE :search '
                . 'ORDER BY `name` ASC LIMIT :limit OFFSET :offset',
                [
                    [
                        'id_column' => '1',
                        'name_column' => 'first',
                    ],
                ]
            )
            ->addResultSet(
                'SELECT FOUND_ROWS() AS number',
                [
                    [
                        'number' => '1',
                    ],
                ]
            );

        $filters = [
            'search' => 'f',
        ];
        $limit = 1;
        $offset = 10;
        $sortField = 'name';
        $sortOrder = 'asc';
        $extras = [
            'sub' => 'buf',
        ];
        
        $response = $this->service
            ->setRepository(Mock\RepositoryPaginationMock::class)
            ->setDataRepresenter(DataRepresenter\Entity::class)
            ->setFilters($filters)
            ->setLimit($limit)
            ->setOffset($offset)
            ->setOrder($sortField, $sortOrder)
            ->setExtras($extras)
            ->getResponse();

        // check response
        $this->assertEquals(
            '{"status":true,"result":{"pagination":{'
            . '"total":1,"offset":10,"limit":1},'
            . '"entities":[{"id":"1","name":"first"}]}}',
            json_encode($response)
        );

        // check properties
        $this->assertAttributeEquals($filters, 'filters', $this->service);
        $this->assertAttributeEquals($limit, 'limit', $this->service);
        $this->assertAttributeEquals($offset, 'offset', $this->service);
        $this->assertAttributeEquals([
            'field' => $sortField,
            'order' => strtoupper($sortOrder),
        ], 'ordering', $this->service);
        $this->assertAttributeEquals($extras, 'extras', $this->service);
    }

    /**
     * @expectedException \Exception
     */
    public function testGetResponseWithIncorectRepository()
    {
        $this->service
            ->setRepository(Mock\RepositoryMock::class);
    }

    /**
     * @expectedException \Exception
     */
    public function testGetResponseWithIncorectDataRepresenter()
    {
        $this->service
            ->setDataRepresenter(\stdClass::class);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetResponseWithIncorectLimit()
    {
        $this->service
            ->setLimit($this->service::LIMIT_MAX + 1);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetResponseWithIncorectLimit2()
    {
        $this->service
            ->setLimit(0);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetResponseWithIncorectLimit3()
    {
        $this->service
            ->setLimit(-1);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetResponseWithIncorectOffset()
    {
        $this->service
            ->setOffset(0);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetResponseWithIncorectOffset2()
    {
        $this->service
            ->setOffset(-1);
    }
}
