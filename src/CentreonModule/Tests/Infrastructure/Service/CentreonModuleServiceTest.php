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

class CentreonModuleServiceTest extends TestCase
{

    use TestCaseExtensionTrait;

    protected function setUp()
    {
        $this->service = $this->getMockBuilder(CentreonModuleService::class)
            ->setMethods([
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
                ->setMethods([
                    'getList',
                    'getDetail',
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

    /**
     * @covers \CentreonModule\Infrastructure\Service\CentreonModuleService::initSources
     */
    public function testInitSources()
    {
        $container = new Container;
        $container['finder'] = null;
        $container['centreon.legacy.license'] = null;
        $container['configuration'] = $this->createMock(Configuration::class);
        $container['centreon.db-manager'] = new Mock\CentreonDBManagerService;

        // Data sets
        $queries = array_merge(ModuleSourceTest::$sqlQueryVsData, WidgetSourceTest::$sqlQueryVsData);
        foreach ($queries as $key => $result) {
            $container['centreon.db-manager']->addResultSet($key, $result);
        }

        $service = new CentreonModuleService(new ContainerWrap($container));

        $sources = $this->getProtectedProperty($service, 'sources');

        $this->assertArrayHasKey(Source\ModuleSource::TYPE, $sources);
        $this->assertArrayHasKey(Source\WidgetSource::TYPE, $sources);

        $this->assertInstanceOf(Source\ModuleSource::class, $sources[Source\ModuleSource::TYPE]);
        $this->assertInstanceOf(Source\WidgetSource::class, $sources[Source\WidgetSource::TYPE]);
    }
}
