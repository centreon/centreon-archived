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

namespace CentreonModule\Tests\Infrastructure\Source;

use PHPUnit\Framework\TestCase;
use CentreonModule\Infrastructure\Entity\Module;
use CentreonModule\Infrastructure\Source\ModuleSource;
use CentreonModule\Infrastructure\Source\SourceAbstract;
use CentreonModule\Tests\Infrastructure\Source\ModuleSourceTest;

class SourceAbstractTest extends TestCase
{

    const RESULT_SUCCESS = 'OK';

    protected function setUp(): void
    {
        $this->source = $this->getMockBuilder(SourceAbstract::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'initInfo',
                'getDetail',
            ])
            ->getMockForAbstractClass()
        ;

        $this->source
            ->method('initInfo')
            ->will($this->returnCallback(function () {
                    $this->assertTrue(true);
            }))
        ;

        $this->source
            ->method('getDetail')
            ->will($this->returnCallback(function () {
                    $entity = new Module();
                    $entity->setType(ModuleSource::TYPE);
                    $entity->setName(ModuleSource::TYPE);
                    $entity->setKeywords('test,module,lorem');
                    $entity->setInstalled(true);
                    $entity->setUpdated(false);

                    return $entity;
            }))
        ;

        $this->source->installer = function ($id) {
            $mock = new class {

                public $id;

                public function install()
                {
                    if ($this->id === ModuleSourceTest::$moduleName) {
                        throw new \Exception(SourceAbstractTest::RESULT_SUCCESS, 1);
                    }
                }
            };

            $mock->id = $id;

            return $mock;
        };

        $this->source->upgrader = function ($id) {
            $mock = new class {

                public $id;

                public function upgrade()
                {
                    if ($this->id === ModuleSourceTest::$moduleName) {
                        throw new \Exception(SourceAbstractTest::RESULT_SUCCESS, 2);
                    }
                }
            };

            $mock->id = $id;

            return $mock;
        };

        $this->source->remover = function ($id) {
            $mock = new class {

                public $id;

                public function remove()
                {
                    if ($this->id === ModuleSourceTest::$moduleName) {
                        throw new \Exception(SourceAbstractTest::RESULT_SUCCESS, 3);
                    }
                }
            };

            $mock->id = $id;

            return $mock;
        };
    }

    public function testInstall()
    {
        (function () {
            $result = null;

            try {
                $result = $this->source->install(ModuleSourceTest::$moduleName);
            } catch (\Exception $ex) {
                $result = $ex->getMessage();
            }

            $this->assertEquals(static::RESULT_SUCCESS, $result);
        })();

        (function () {
            $result = $this->source->install(ModuleSourceTest::$moduleNameMissing);
        })();
    }

    public function testUpdate()
    {
        (function () {
            $result = null;

            try {
                $this->source->update(ModuleSourceTest::$moduleName);
            } catch (\Exception $ex) {
                $result = $ex->getMessage();
            }

            $this->assertEquals(static::RESULT_SUCCESS, $result);
        })();

        (function () {
            $result = $this->source->update(ModuleSourceTest::$moduleNameMissing);
        })();
    }

    public function testRemove()
    {
        (function () {
            $result = null;

            try {
                $this->source->remove(ModuleSourceTest::$moduleName);
            } catch (\Exception $ex) {
                $result = $ex->getMessage();
            }

            $this->assertEquals(static::RESULT_SUCCESS, $result);
        })();

        (function () {
            $result = $this->source->remove(ModuleSourceTest::$moduleNameMissing);
        })();
    }

    public function testIsEligible()
    {
        $entity = new Module;
        $entity->setName('tesat');
        $entity->setKeywords('test,module,lorem');
        $entity->setInstalled(true);
        $entity->setUpdated(false);

        $this->assertTrue($this->source->isEligible($entity));

        // search
        $this->assertTrue($this->source->isEligible($entity, 'sat'));
        $this->assertTrue($this->source->isEligible($entity, 'lor'));
        $this->assertFalse($this->source->isEligible($entity, 'rom'));

        // installed filter
        $this->assertTrue($this->source->isEligible($entity, null, true));
        $this->assertFalse($this->source->isEligible($entity, null, false));

        // updated filter
        $this->assertFalse($this->source->isEligible($entity, null, null, true));
        $this->assertTrue($this->source->isEligible($entity, null, null, false));
    }

    public function testIsUpdated()
    {
        $this->assertTrue($this->source->isUpdated('1.0.0', '1.0.0'));
        $this->assertFalse($this->source->isUpdated('1.0.1', '1.0.0'));
    }
}
