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

namespace CentreonNotification\Tests\Application\Webservice;

use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Centreon\ServiceProvider;
use CentreonNotification\Application\Webservice\CentreonEscalationWebservice;
use Centreon\Tests\Resource\Mock\CentreonPaginationServiceMock;

/**
 * @group Centreon
 * @group Webservice
 */
class CentreonEscalationWebserviceTest extends TestCase
{

    protected function setUp()
    {
        // dependencies
        $container = new Container;
        $container[ServiceProvider::CENTREON_PAGINATION] = new CentreonPaginationServiceMock;

        $this->webservice = $this->createPartialMock(CentreonEscalationWebservice::class, [
            'loadDb',
            'loadArguments',
            'loadToken',
            'query',
        ]);

        // load dependencies
        $this->webservice->setDi($container);
    }

    public function testGetList()
    {
        $method = 'getList';
        $filters = [];
        $this->webservice
            ->method('query')
            ->will($this->returnCallback(function () use (&$filters) {
                    return $filters;
            }))
        ;

        // without applied filters
        $this->executeTest($method, 'response-list-1.json');

        // with search, searchByIds, limit, and offset
        $filters['search'] = 'test';
        $filters['searchByIds'] = '3,5,7';
        $filters['limit'] = '1a';
        $filters['offset'] = '2b';
        $this->executeTest($method, 'response-list-2.json');
    }

    public function testAuthorize()
    {
        (function () {
            $result = $this->webservice->authorize(null, null, true);
            $this->assertTrue($result);
        })();

        (function () {
            $result = $this->webservice->authorize(null, null);
            $this->assertFalse($result);
        })();

        (function () {
            $user = $this->createMock(\CentreonUser::class);
            $user
                ->method('hasAccessRestApiConfiguration')
                ->will($this->returnCallback(function () {
                        return true;
                }))
            ;

            $result = $this->webservice->authorize(null, $user);
            $this->assertTrue($result);
        })();
    }

    public function testGetName()
    {
        $this->assertEquals('centreon_escalation', CentreonEscalationWebservice::getName());
    }

    /**
     * Compare response with control value
     *
     * @param string $method
     * @param string $controlJsonFile
     */
    protected function executeTest($method, $controlJsonFile)
    {
        // get controlled response from file
        $path = __DIR__ . '/../../Resource/Fixture/';
        $controlJson = file_get_contents($path . $controlJsonFile);

        $result = $this->webservice->{$method}();
        $this->assertInstanceOf(\JsonSerializable::class, $result);

        $json = json_encode($result);
        $this->assertEquals($controlJson, $json);
    }
}
