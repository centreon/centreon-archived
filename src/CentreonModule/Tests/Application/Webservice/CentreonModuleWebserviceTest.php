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
use Centreon\Tests\Resources\Traits;

class CentreonModuleWebserviceTest extends TestCase
{
    use Traits\WebServiceAuthorizeRestApiTrait,
        Traits\WebServiceExecuteTestTrait;

    const METHOD_GET_LIST = 'getList';
    const METHOD_GET_DETAILS = 'getDetails';
    const METHOD_POST_INSTALL = 'postInstall';
    const METHOD_POST_UPDATE = 'postUpdate';
    const METHOD_DELETE_REMOVE = 'deleteRemove';

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
        $this->fixturePath = __DIR__ . '/../../Resources/Fixture/';
    }

    public function testGetList()
    {
        // without applied filters
        $this->mockQuery();
        $this->executeTest(static::METHOD_GET_LIST, 'response-list-1.json');
    }

    public function testGetList2()
    {
        // with search, installed, updated, and selected type filter
        $this->mockQuery([
            'search' => 'test',
            'installed' => 'true',
            'updated' => 'true',
            'types' => ModuleSource::TYPE,
        ]);
        $this->executeTest(static::METHOD_GET_LIST, 'response-list-2.json');
    }

    public function testGetList3()
    {
        // with not installed, not updated and not selected type filter
        $this->mockQuery([
            'installed' => 'false',
            'updated' => 'false',
            'types' => [],
        ]);
        $this->executeTest(static::METHOD_GET_LIST, 'response-list-3.json');
    }

    public function testGetList4()
    {
        // with wrong values of installed and updated filters
        $this->mockQuery([
            'installed' => 'ture',
            'updated' => 'folse',
        ]);
        $this->executeTest(static::METHOD_GET_LIST, 'response-list-4.json');
    }

    public function testGetDetails()
    {
        // find module by id and type
        $this->mockQuery([
            'id' => ModuleSourceTest::$moduleName,
            'type' => ModuleSource::TYPE,
        ]);
        $this->executeTest(static::METHOD_GET_DETAILS, 'response-details-1.json');
    }

    public function testGetDetails2()
    {
        // try to find missing module applied filters
        $this->mockQuery([
            'id' => ModuleSourceTest::$moduleNameMissing,
            'type' => ModuleSource::TYPE,
        ]);
        $this->executeTest(static::METHOD_GET_DETAILS, 'response-details-2.json');
    }

    public function testPostInstall()
    {
        $this->mockQuery();
        $this->executeTest(static::METHOD_POST_INSTALL, 'response-install-1.json');
    }

    public function testPostInstall2()
    {
        // find module by id and type
        $this->mockQuery([
            'id' => ModuleSourceTest::$moduleName,
            'type' => ModuleSource::TYPE,
        ]);
        $this->executeTest(static::METHOD_POST_INSTALL, 'response-install-2.json');
    }

    public function testPostUpdate()
    {
        $this->mockQuery();
        $this->executeTest(static::METHOD_POST_UPDATE, 'response-update-1.json');
    }

    public function testPostUpdate2()
    {
        // find module by id and type
        $this->mockQuery([
            'id' => ModuleSourceTest::$moduleName,
            'type' => ModuleSource::TYPE,
        ]);
        $this->executeTest(static::METHOD_POST_UPDATE, 'response-update-2.json');
    }

    public function testPostRemove()
    {
        $this->mockQuery();
        $this->executeTest(static::METHOD_DELETE_REMOVE, 'response-remove-1.json');
    }

    public function testPostRemove2()
    {
        // find module by id and type
        $this->mockQuery([
            'id' => ModuleSourceTest::$moduleName,
            'type' => ModuleSource::TYPE,
        ]);
        $this->executeTest(static::METHOD_DELETE_REMOVE, 'response-remove-2.json');
    }

    public function testGetName()
    {
        $this->assertEquals('centreon_module', CentreonModuleWebservice::getName());
    }
}
