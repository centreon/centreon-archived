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

namespace Centreon\Tests\Infrastructure\Service;

use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Pimple\Psr11\Container as ContainerWrap;
use Centreon\Infrastructure\Service\CentreonPaginationService;
use Centreon\Application\DataRepresenter;
use Centreon\ServiceProvider;
use Centreon\Tests\Resources\Mock;
use Centreon\Tests\Resources\Dependency;
use Symfony\Component\Serializer\Serializer;

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
        $this->container[ServiceProvider::SERIALIZER] = $this->createMock(Serializer::class);

        $this->service = new CentreonPaginationService(new ContainerWrap($this->container));
    }

    public function testDependencies()
    {
        $this->assertEquals([
            ServiceProvider::CENTREON_DB_MANAGER,
            ServiceProvider::SERIALIZER,
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
