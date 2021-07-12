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

namespace Centreon\Tests\Application\Webservice;

use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Centreon\Application\Webservice\TopologyWebservice;
use Centreon\Domain\Repository\TopologyRepository;
use Centreon\Tests\Resources\Traits;
use Centreon\Tests\Resources\Dependency;
use Centreon\Test\Mock\CentreonDB;
use Centreon\Test\Traits\TestCaseExtensionTrait;
use Centreon\Tests\Resources\CheckPoint;
use Centreon\ServiceProvider;
use CentreonUser;

/**
 * @group Centreon
 * @group Webservice
 */
class TopologyWebserviceTest extends TestCase
{
    use Traits\WebServiceAuthorizePublicTrait,
        TestCaseExtensionTrait,
        Dependency\CentreonDbManagerDependencyTrait;

    protected function setUp()
    {
        // dependencies
        $this->container = new Container;
        $this->db = new CentreonDB;
        
        $this->setUpCentreonDbManager($this->container);

        $this->webservice = $this->createPartialMock(TopologyWebservice::class, [
            'loadDb',
            'loadArguments',
            'loadToken',
            'query',
        ]);

        $this->setProtectedProperty($this->webservice, 'pearDB', $this->db);

        // load dependencies
        $this->webservice->setDi($this->container);
    }

    public function testGetName()
    {
        $this->assertEquals('centreon_topology', TopologyWebservice::getName());
    }

    public function testDependencies()
    {
        $this->assertEquals([
            ServiceProvider::CENTREON_DB_MANAGER,
        ], $this->webservice::dependencies());
    }

    public function testGetGetTopologyByPage()
    {
        $marker = __METHOD__;
        $checkpoint = (new CheckPoint)
            ->add($marker);

        $_GET['topology_page'] = 1;
        $this->db->addResultSet(
            "SELECT * FROM `topology` WHERE `topology_page` = :id",
            [['k']],
            null,
            function () use ($checkpoint, $marker) {
                $checkpoint->mark($marker);
            }
        );

        $this->webservice->getGetTopologyByPage();
        $checkpoint->assert($this);
    }

    /**
     * @expectedException \RestBadRequestException
     */
    public function testGetGetTopologyByPageWithoutResult()
    {
        $_GET['topology_page'] = 1;
        $this->db->addResultSet("SELECT * FROM `topology` WHERE `topology_page` = :id", []);

        $this->webservice->getGetTopologyByPage();
    }

    /**
     * @expectedException \RestBadRequestException
     */
    public function testGetGetTopologyByPageWithoutTopologyPage()
    {
        if (isset($_GET['topology_page'])) {
            unset($_GET['topology_page']);
        }

        $this->webservice->getGetTopologyByPage();
    }

    /**
     * @expectedException \RestBadRequestException
     */
    public function testGetNavigationListWithoutAuth()
    {
        $this->container[ServiceProvider::CENTREON_USER] = null;

        $this->webservice->getNavigationList();
    }

    public function testGetNavigationList()
    {
        $calledGetTopologyList = false;
        $repository = $this->createMock(TopologyRepository::class);
        $repository->method('getTopologyList')
            ->will($this->returnCallback(function () use (&$calledGetTopologyList) {
                $calledGetTopologyList = true;

                return [];
            }));

        // register mocked repository in DB manager
        $this->container[ServiceProvider::CENTREON_DB_MANAGER]
            ->addRepositoryMock(TopologyRepository::class, $repository);

        // mock user service
        $this->container[ServiceProvider::CENTREON_USER] = $this->createMock(CentreonUser::class);
        $this->container[ServiceProvider::YML_CONFIG] = [
            'navigation' => [],
        ];

        $result = $this->webservice->getNavigationList();
        $this->assertTrue($calledGetTopologyList);
    }

    public function testGetNavigationListWithReact()
    {
        $_GET['reactOnly'] = 1;

        $calledGetTopologyList = false;
        $repository = $this->createMock(TopologyRepository::class);
        $repository->method('getTopologyList')
            ->will($this->returnCallback(function () use (&$calledGetTopologyList) {
                $calledGetTopologyList = true;

                return [];
            }));

        // register mocked repository in DB manager
        $this->container[ServiceProvider::CENTREON_DB_MANAGER]
            ->addRepositoryMock(TopologyRepository::class, $repository);

        // mock user service
        $this->container[ServiceProvider::CENTREON_USER] = $this->createMock(CentreonUser::class);
        $this->container[ServiceProvider::YML_CONFIG] = [
            'navigation' => [],
        ];

        $this->webservice->getNavigationList();
        $this->assertTrue($calledGetTopologyList);
    }
}
