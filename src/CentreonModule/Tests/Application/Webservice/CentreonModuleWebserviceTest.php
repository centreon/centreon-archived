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
use CentreonModule\Application\Webservice\CentreonModuleWebservice;
use CentreonModule\Infrastructure\Service\CentreonModuleService;
use CentreonModule\Infrastructure\Source\ModuleSource;
use CentreonModule\Infrastructure\Entity\Module;
use CentreonModule\Tests\Infrastructure\Source\ModuleSourceTest;
use CentreonModule\ServiceProvider;

class CentreonModuleWebserviceTest extends TestCase
{

    protected function setUp()
    {
        // dependencies
        $container = new Container;
        $container[ServiceProvider::CENTREON_MODULE] = $this->createMock(CentreonModuleService::class, [
            'getList',
            'getDetail',
            'install',
            'update',
            'remove',
        ]);
        $container[ServiceProvider::CENTREON_MODULE]
            ->method('getList')
            ->will($this->returnCallback(function () {
                    $funcArgs = func_get_args();

                    // prepare filters
                    $funcArgs[0] = $funcArgs[0] === null ? '-' : $funcArgs[0];
                    $funcArgs[1] = $funcArgs[1] === true ? '1' : ($funcArgs[1] !== false ? '-' : '0');
                    $funcArgs[2] = $funcArgs[2] === true ? '1' : ($funcArgs[2] !== false ? '-' : '0');
                    $funcArgs[3] = $funcArgs[3] ? implode('|', $funcArgs[3]) : '-';
                    $name = implode(',', $funcArgs);

                    $module = new Module;
                    $module->setId(ModuleSourceTest::$moduleName);
                    $module->setName($name);
                    $module->setAuthor('');
                    $module->setVersion('');
                    $module->setType(ModuleSource::TYPE);

                    return [
                        ModuleSource::TYPE => [
                            $module,
                        ],
                    ];
            }))
        ;
        $container[ServiceProvider::CENTREON_MODULE]
            ->method('getDetail')
            ->will($this->returnCallback(function () {
                    $funcArgs = func_get_args();

                    // prepare filters
                    $funcArgs[0] = $funcArgs[0] === null ? '-' : $funcArgs[0];
                    $funcArgs[1] = $funcArgs[1] === null ? '-' : $funcArgs[1];

                if ($funcArgs[0] === ModuleSourceTest::$moduleNameMissing) {
                    return null;
                }

                    $name = implode(',', $funcArgs);

                    $module = new Module;
                    $module->setId(ModuleSourceTest::$moduleName);
                    $module->setName($name);
                    $module->setAuthor('');
                    $module->setVersion('');
                    $module->setType(ModuleSource::TYPE);

                    return $module;
            }))
        ;
        $container[ServiceProvider::CENTREON_MODULE]
            ->method('install')
            ->will($this->returnCallback(function () {
                    $funcArgs = func_get_args();

                if ($funcArgs[0] === '' && $funcArgs[1] === '') {
                    throw new \Exception('');
                }

                    // prepare filters
                    $funcArgs[0] = $funcArgs[0] === '' ? '-' : $funcArgs[0];
                    $funcArgs[1] = $funcArgs[1] === '' ? '-' : $funcArgs[1];

                if ($funcArgs[0] === ModuleSourceTest::$moduleNameMissing) {
                    return null;
                }

                    $name = implode(',', $funcArgs);

                    $module = new Module;
                    $module->setId(ModuleSourceTest::$moduleName);
                    $module->setName($name);
                    $module->setAuthor('');
                    $module->setVersion('');
                    $module->setType(ModuleSource::TYPE);

                    return $module;
            }))
        ;
        $container[ServiceProvider::CENTREON_MODULE]
            ->method('update')
            ->will($this->returnCallback(function () {
                    $funcArgs = func_get_args();

                if ($funcArgs[0] === '' && $funcArgs[1] === '') {
                    throw new \Exception('');
                }

                    // prepare filters
                    $funcArgs[0] = $funcArgs[0] === '' ? '-' : $funcArgs[0];
                    $funcArgs[1] = $funcArgs[1] === '' ? '-' : $funcArgs[1];

                if ($funcArgs[0] === ModuleSourceTest::$moduleNameMissing) {
                    return null;
                }

                    $name = implode(',', $funcArgs);

                    $module = new Module;
                    $module->setId(ModuleSourceTest::$moduleName);
                    $module->setName($name);
                    $module->setAuthor('');
                    $module->setVersion('');
                    $module->setType(ModuleSource::TYPE);

                    return $module;
            }))
        ;
        $container[ServiceProvider::CENTREON_MODULE]
            ->method('remove')
            ->will($this->returnCallback(function () {
                    $funcArgs = func_get_args();

                if ($funcArgs[0] === '' && $funcArgs[1] === '') {
                    throw new \Exception('');
                }
            }))
        ;

        $this->webservice = $this->createPartialMock(CentreonModuleWebservice::class, [
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

        // with search, installed, updated, and selected type filter
        $filters['search'] = 'test';
        $filters['installed'] = 'true';
        $filters['updated'] = 'true';
        $filters['types'] = [ModuleSource::TYPE];
        $this->executeTest($method, 'response-list-2.json');

        // with not installed, not updated and not selected type filter
        unset($filters['search']);
        $filters['installed'] = 'false';
        $filters['updated'] = 'false';
        $filters['types'] = [];
        $this->executeTest($method, 'response-list-3.json');

        // with wrong values of installed and updated filters
        unset($filters['types']);
        $filters['installed'] = 'ture';
        $filters['updated'] = 'folse';
        $this->executeTest($method, 'response-list-4.json');
    }

    public function testGetDetails()
    {
        $method = 'getDetails';
        $filters = [];
        $this->webservice
            ->method('query')
            ->will($this->returnCallback(function () use (&$filters) {
                    return $filters;
            }))
        ;

        // find module by id and type
        $filters['id'] = ModuleSourceTest::$moduleName;
        $filters['type'] = ModuleSource::TYPE;
        $this->executeTest($method, 'response-details-1.json');

        // try to find missing module applied filters
        $filters['id'] = ModuleSourceTest::$moduleNameMissing;
        $filters['type'] = ModuleSource::TYPE;
        $this->executeTest($method, 'response-details-2.json');
    }

    public function testPostInstall()
    {
        $method = 'postInstall';
        $filters = [];
        $this->webservice
            ->method('query')
            ->will($this->returnCallback(function () use (&$filters) {
                    return $filters;
            }))
        ;

        $this->executeTest($method, 'response-install-1.json');

        // find module by id and type
        $filters['id'] = ModuleSourceTest::$moduleName;
        $filters['type'] = ModuleSource::TYPE;
        $this->executeTest($method, 'response-install-2.json');
    }

    public function testPostUpdate()
    {
        $method = 'postUpdate';
        $filters = [];
        $this->webservice
            ->method('query')
            ->will($this->returnCallback(function () use (&$filters) {
                    return $filters;
            }))
        ;

        $this->executeTest($method, 'response-update-1.json');

        // find module by id and type
        $filters['id'] = ModuleSourceTest::$moduleName;
        $filters['type'] = ModuleSource::TYPE;
        $this->executeTest($method, 'response-update-2.json');
    }

    public function testPostRemove()
    {
        $method = 'deleteRemove';
        $filters = [];
        $this->webservice
            ->method('query')
            ->will($this->returnCallback(function () use (&$filters) {
                    return $filters;
            }))
        ;

        $this->executeTest($method, 'response-remove-1.json');

        // find module by id and type
        $filters['id'] = ModuleSourceTest::$moduleName;
        $filters['type'] = ModuleSource::TYPE;
        $this->executeTest($method, 'response-remove-2.json');
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
        $this->assertEquals('centreon_module', CentreonModuleWebservice::getName());
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
