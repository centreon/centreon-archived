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

namespace CentreonModule\Tests\Infrastructure\Service;

use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Pimple\Psr11\Container as ContainerWrap;
use Centreon\Test\Mock;
use Centreon\Test\Traits\TestCaseExtensionTrait;
use CentreonModule\Infrastructure\Service\CentreonModuleService;
use CentreonModule\Infrastructure\Source;
use CentreonModule\Tests\Infrastructure\Source\ModuleSourceTest;
use CentreonModule\Tests\Infrastructure\Source\WidgetSourceTest;
use CentreonModule\Infrastructure\Entity\Module;
use CentreonLegacy\Core\Configuration\Configuration;
use CentreonModule\Tests\Resources\Traits\SourceDependencyTrait;

class CentreonModuleServiceTest extends TestCase
{
    use TestCaseExtensionTrait;
    use SourceDependencyTrait;

    protected function setUp(): void
    {
        $this->service = $this->getMockBuilder(CentreonModuleService::class)
            ->onlyMethods([
                'initSources',
            ])
            ->setConstructorArgs([new ContainerWrap(new Container)])
            ->getMock()
        ;

        $sources = [];
        $sourcesTypes = [
            Source\ModuleSource::TYPE => Source\ModuleSource::class,
            Source\WidgetSource::TYPE => Source\WidgetSource::class,
        ];

        foreach ($sourcesTypes as $type => $class) {
            $sources[$type] = $this
                ->getMockBuilder($class)
                ->disableOriginalConstructor()
                ->onlyMethods([
                    'getList',
                    'getDetail',
                    'install',
                    'update',
                    'remove',
                ])
                ->getMock()
            ;

            $sources[$type]
                ->method('getList')
                ->will($this->returnCallback(function () use ($type) {
                        return [$type];
                }))
            ;
            $sources[$type]
                ->method('getDetail')
                ->will($this->returnCallback(function () use ($type) {
                        $entity = new Module();
                        $entity->setType($type);
                        $entity->setName($type);
                        $entity->setKeywords('test,module,lorem');
                        $entity->setInstalled(true);
                        $entity->setUpdated(false);

                        return $entity;
                }))
            ;
            $sources[$type]
                ->method('install')
                ->will($this->returnCallback(function ($id) use ($type) {
                        $entity = new Module();
                        $entity->setId($id);
                        $entity->setType($type);
                        $entity->setName($type);
                        $entity->setKeywords('test,module,lorem');
                        $entity->setInstalled(true);
                        $entity->setUpdated(false);

                        return $entity;
                }))
            ;
            $sources[$type]
                ->method('update')
                ->will($this->returnCallback(function ($id) use ($type) {
                        $entity = new Module();
                        $entity->setId($id);
                        $entity->setType($type);
                        $entity->setName($type);
                        $entity->setKeywords('test,module,lorem');
                        $entity->setInstalled(true);
                        $entity->setUpdated(false);

                        return $entity;
                }))
            ;
            $sources[$type]
                ->method('remove')
                ->will($this->returnCallback(function ($id) use ($type) {
                    if ($id === ModuleSourceTest::$moduleName) {
                        throw new \Exception('Removed');
                    }
                }))
            ;
        }

        // load sources
        $this->setProtectedProperty($this->service, 'sources', $sources);
    }

    public function testGetList()
    {
        (function () {
            $result = $this->service->getList();

            $this->assertArrayHasKey(Source\ModuleSource::TYPE, $result);
            $this->assertArrayHasKey(Source\WidgetSource::TYPE, $result);
        })();

        (function () {
            $result = $this->service->getList(null, null, null, [Source\ModuleSource::TYPE]);

            $this->assertArrayHasKey(Source\ModuleSource::TYPE, $result);
            $this->assertArrayNotHasKey(Source\WidgetSource::TYPE, $result);
        })();

        (function () {
            $result = $this->service->getList(null, null, null, ['missing-type']);

            $this->assertArrayNotHasKey(Source\ModuleSource::TYPE, $result);
            $this->assertArrayNotHasKey(Source\WidgetSource::TYPE, $result);
        })();
    }

    public function testGetDetails()
    {
        (function () {
            $result = $this->service->getDetail('test-module', Source\ModuleSource::TYPE);

            $this->assertInstanceOf(Module::class, $result);
            $this->assertEquals(Source\ModuleSource::TYPE, $result->getType());
        })();

        (function () {
            $result = $this->service->getDetail('test-module', 'missing-type');

            $this->assertEquals(null, $result);
        })();
    }

    public function testInstall()
    {
        $result = $this->service->install(ModuleSourceTest::$moduleName, Source\ModuleSource::TYPE);

        $this->assertInstanceOf(Module::class, $result);
    }

    /**
     * @covers \CentreonModule\Infrastructure\Service\CentreonModuleService::install
     */
    public function testInstallMissingType()
    {
        $result = $this->service->install(ModuleSourceTest::$moduleName, 'missing-type');

        $this->assertNull($result);
    }

    public function testUpdate()
    {
        $result = $this->service->update(ModuleSourceTest::$moduleName, Source\ModuleSource::TYPE);

        $this->assertInstanceOf(Module::class, $result);
    }

    /**
     * @covers \CentreonModule\Infrastructure\Service\CentreonModuleService::update
     */
    public function testUpdateMissingType()
    {
        $result = $this->service->update(ModuleSourceTest::$moduleName, 'missing-type');

        $this->assertNull($result);
    }

    public function testRemove()
    {
        (function () {
            $result = null;

            try {
                $result = $this->service->remove(ModuleSourceTest::$moduleName, Source\ModuleSource::TYPE);
            } catch (\Exception $ex) {
                $result = $ex->getMessage();
            }

            $this->assertEquals('Removed', $result);
        })();

        $result = $this->service->remove(ModuleSourceTest::$moduleNameMissing, Source\ModuleSource::TYPE);
        $this->assertTrue($result);
    }

    /**
     * @covers \CentreonModule\Infrastructure\Service\CentreonModuleService::remove
     */
    public function testRemoveMissingType()
    {
        $result = $this->service->remove(ModuleSourceTest::$moduleName, 'missing-type');

        $this->assertNull($result);
    }

    /**
     * @covers \CentreonModule\Infrastructure\Service\CentreonModuleService::initSources
     */
    public function testInitSources()
    {
        $container = new Container;
        $container['finder'] = null;
        $container['configuration'] = $this->createMock(Configuration::class);
        $container[\Centreon\ServiceProvider::CENTREON_DB_MANAGER] = new Mock\CentreonDBManagerService;

        // Data sets
        $queries = array_merge(ModuleSourceTest::$sqlQueryVsData, WidgetSourceTest::$sqlQueryVsData);
        foreach ($queries as $key => $result) {
            $container[\Centreon\ServiceProvider::CENTREON_DB_MANAGER]->addResultSet($key, $result);
        }

        $this->setUpSourceDependency($container);

        $service = new CentreonModuleService(new ContainerWrap($container));

        $sources = $this->getProtectedProperty($service, 'sources');

        $this->assertArrayHasKey(Source\ModuleSource::TYPE, $sources);
        $this->assertArrayHasKey(Source\WidgetSource::TYPE, $sources);

        $this->assertInstanceOf(Source\ModuleSource::class, $sources[Source\ModuleSource::TYPE]);
        $this->assertInstanceOf(Source\WidgetSource::class, $sources[Source\WidgetSource::TYPE]);
    }

    public function testSortList()
    {
        $service = $this->createMock(CentreonModuleService::class);

        $value = [
            'B-1-0',
            'C-1-0',
            'D-0-0',
            'F-0-0',
            'A-1-1',
            'B-1-1',
        ];
        $list = [
            (function () {
                    $entity = new Module;
                    $entity->setName('B');
                    $entity->setInstalled(true);
                    $entity->setUpdated(true);

                    return $entity;
            })(),
            (function () {
                    $entity = new Module;
                    $entity->setName('A');
                    $entity->setInstalled(true);
                    $entity->setUpdated(true);

                    return $entity;
            })(),
            (function () {
                    $entity = new Module;
                    $entity->setName('B');
                    $entity->setInstalled(true);
                    $entity->setUpdated(false);

                    return $entity;
            })(),
            (function () {
                    $entity = new Module;
                    $entity->setName('C');
                    $entity->setInstalled(true);
                    $entity->setUpdated(false);

                    return $entity;
            })(),
            (function () {
                    $entity = new Module;
                    $entity->setName('D');
                    $entity->setInstalled(false);
                    $entity->setUpdated(false);

                    return $entity;
            })(),
            (function () {
                    $entity = new Module;
                    $entity->setName('F');
                    $entity->setInstalled(false);
                    $entity->setUpdated(false);

                    return $entity;
            })(),
        ];
        $list = $this->invokeMethod($service, 'sortList', [$list]);

        $result = [];
        foreach ($list as $entity) {
            $result[] = $entity->getName() . '-' . (int) $entity->isInstalled() . '-' . (int) $entity->isUpdated();
        }

        $this->assertEquals($value, $result);
    }
}
