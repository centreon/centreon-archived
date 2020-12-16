<?php
/*
 * Copyright 2005-2019 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
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

namespace CentreonModule\Tests\Application\Webservice;

use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Centreon\Test\Mock\CentreonDB;
use Symfony\Component\Finder\Finder;
use CentreonModule\Application\Webservice\CentreonModulesWebservice;

class CentreonModulesWebserviceTest extends TestCase
{

    public static $sqlQueriesWitoutData = [
        'SELECT * FROM modules_informations ' => [],
    ];
    public static $sqlQueries = [
        'SELECT * FROM modules_informations ' => [
            [
                'id' => '1',
                'name' => 'centreon-bam-server',
                'rname' => 'centreon-bam-server',
                'mod_release' => '',
                'is_removable' => '1',
                'infos' => '',
                'author' => '',
                'svc_tools' => '0',
                'host_tools' => '0',
            ],
        ],
    ];

    protected function setUp(): void
    {
        $this->webservice = $this->createPartialMock(CentreonModulesWebservice::class, [
            'loadDb',
            'loadArguments',
            'loadToken',
        ]);
    }

    /**
     * @covers \CentreonModule\Application\Webservice\CentreonModulesWebservice::postGetBamModuleInfo
     */
    public function testPostGetBamModuleInfoWithoutModule()
    {
        // dependencies
        $container = new Container;
        $container[\CentreonLegacy\ServiceProvider::CENTREON_LEGACY_MODULE_INFORMATION] = $this
            ->getMockBuilder(\CentreonLegacy\Core\Module\Information::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getList',
            ])
            ->getMock();

        $container[\CentreonLegacy\ServiceProvider::CENTREON_LEGACY_MODULE_INFORMATION]
            ->method('getList')
            ->will($this->returnCallback(function () {
                return [
                    'centreon-bam-server' => [
                        'is_installed' => false,
                    ],
                ];
            }));

        $this->webservice->setDi($container);

        $result = $this->webservice->postGetBamModuleInfo();
        $this->assertArrayHasKey('enabled', $result);
        $this->assertFalse($result['enabled']);
    }

    /**
     * @covers \CentreonModule\Application\Webservice\CentreonModulesWebservice::postGetBamModuleInfo
     */
    public function testPostGetBamModuleInfoWithModule()
    {
        $container = new Container;
        $container[\CentreonLegacy\ServiceProvider::CENTREON_LEGACY_MODULE_INFORMATION] = $this
            ->getMockBuilder(\CentreonLegacy\Core\Module\Information::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getList',
            ])
            ->getMock();
        $container[\CentreonLegacy\ServiceProvider::CENTREON_LEGACY_MODULE_INFORMATION]
            ->method('getList')
            ->will($this->returnCallback(function () {
                return [
                    'centreon-bam-server' => [
                        'is_installed' => true,
                    ],
                ];
            }));

        $this->webservice->setDi($container);

        $result = $this->webservice->postGetBamModuleInfo();
        $this->assertArrayHasKey('enabled', $result);
        $this->assertTrue($result['enabled']);
    }

    public function testAuthorize()
    {
        $result = $this->webservice->authorize(null, null);
        $this->assertTrue($result);
    }

    public function testGetName()
    {
        $this->assertEquals('centreon_modules_webservice', CentreonModulesWebservice::getName());
    }
}
