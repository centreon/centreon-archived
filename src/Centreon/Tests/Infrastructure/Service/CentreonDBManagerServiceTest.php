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
use Centreon\Infrastructure\Service\CentreonDBManagerService;
use Centreon\Infrastructure\CentreonLegacyDB\CentreonDBAdapter;
use Centreon\Test\Mock\CentreonDB;
use Centreon\Tests\Resources\Mock\RepositoryMock;

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
