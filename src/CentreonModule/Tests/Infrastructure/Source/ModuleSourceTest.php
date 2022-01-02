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
use Pimple\Container;
use Pimple\Psr11\Container as ContainerWrap;
use Symfony\Component\Finder\Finder;
use Vfs\FileSystem;
use Vfs\Node\Directory;
use Vfs\Node\File;
use Centreon\Test\Mock;
use Centreon\Test\Traits\TestCaseExtensionTrait;
use CentreonModule\Infrastructure\Source\ModuleSource;
use CentreonModule\Infrastructure\Entity\Module;
use CentreonLegacy\Core\Configuration\Configuration;
use CentreonModule\Tests\Resources\Traits\SourceDependencyTrait;

class ModuleSourceTest extends TestCase
{
    use TestCaseExtensionTrait;
    use SourceDependencyTrait;

    /**
     * @var ModuleSource|\PHPUnit\Framework\MockObject\MockObject
     */
    private $source;

    /**
     * @var ContainerWrap
     */
    private $containerWrap;

    /**
     * @var FileSystem
     */
    private $fs;

    /**
     * @var string
     */
    public static $moduleName = 'test-module';

    /**
     * @var string
     */
    public static $moduleNameMissing = 'missing-module';

    /**
     * @var string[]
     */
    public static $moduleInfo = [
        'rname' => 'Curabitur congue porta neque',
        'name' => 'test-module',
        'mod_release' => 'x.y.q',
        'infos' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent id ante neque.',
        'is_removeable' => '1',
        'author' => 'Centreon',
        'stability' => 'alpha',
        'last_update' => '2001-01-01',
        'release_note' => 'http://localhost',
        'images' => 'images/image1.png',
    ];

    /**
     * @var string[][][]
     */
    public static $sqlQueryVsData = [
        "SELECT `name` AS `id`, `mod_release` AS `version` FROM `modules_informations`" => [
            [
                'id' => 'test-module',
                'version' => 'x.y.z',
            ],
        ],
        "SELECT `id` FROM `modules_informations` WHERE `name` = :name LIMIT 0, 1" => [
            [
                'id' => '1',
            ],
        ],
    ];

    protected function setUp(): void
    {
        // mount VFS
        $this->fs = FileSystem::factory('vfs://');
        $this->fs->mount();
        $this->fs->get('/')->add('modules', new Directory([]));
        $this->fs->get('/modules')->add(self::$moduleName, new Directory([]));
        $this->fs->get('/modules/' . self::$moduleName)
            ->add(ModuleSource::CONFIG_FILE, new File(self::buildConfContent()))
        ;
        $this->fs->get('/modules/' . self::$moduleName)
            ->add(ModuleSource::LICENSE_FILE, new File(''))
        ;

        // provide services
        $container = new Container();
        $container['finder'] = new Finder();
        $container['configuration'] = $this->createMock(Configuration::class);

        // DB service
        $container[\Centreon\ServiceProvider::CENTREON_DB_MANAGER] = new Mock\CentreonDBManagerService;
        foreach (self::$sqlQueryVsData as $query => $data) {
            $container[\Centreon\ServiceProvider::CENTREON_DB_MANAGER]->addResultSet($query, $data);
        }

        $this->setUpSourceDependency($container);

        $this->containerWrap = new ContainerWrap($container);

        $this->source = $this->getMockBuilder(ModuleSource::class)
            ->onlyMethods([
                'getPath',
                'getModuleConf',
            ])
            ->setConstructorArgs([
                $this->containerWrap,
            ])
            ->getMock();
        $this->source
            ->method('getPath')
            ->will($this->returnCallback(function () {
                $result = 'vfs://modules/';

                return $result;
            }))
        ;
        $this->source
            ->method('getModuleConf')
            ->will($this->returnCallback(function () {
                $result = [
                        ModuleSourceTest::$moduleName => ModuleSourceTest::$moduleInfo,
                    ];

                return $result;
            }))
        ;
    }

    public function tearDown(): void
    {
        // unmount VFS
        $this->fs->unmount();
    }

    public function testGetList(): void
    {
        $result = $this->source->getList();

        $this->assertTrue(is_array($result));

        $result2 = $this->source->getList(self::$moduleNameMissing);
        $this->assertEquals([], $result2);
    }

    public function testGetDetail(): void
    {
        (function () {
            $result = $this->source->getDetail(self::$moduleNameMissing);

            $this->assertNull($result);
        })();

        (function () {
            $result = $this->source->getDetail(self::$moduleName);

            $this->assertInstanceOf(Module::class, $result);
        })();
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function testRemove(): void
    {
        try {
            $this->source->remove(self::$moduleNameMissing);
        } catch (\Exception $ex) {
            $this->assertEquals(self::$moduleNameMissing, $ex->getMessage());
            $this->assertEquals(1, $ex->getCode()); // check moduleId
        }

        $this->source->remove(self::$moduleName);
    }

    /**
    * @throws \Exception
    * @return void
    */
    public function testUpdate(): void
    {
        try {
            $this->assertNull($this->source->update(self::$moduleNameMissing));
        } catch (\Exception $ex) {
            $this->assertEquals(self::$moduleNameMissing, $ex->getMessage());
            $this->assertEquals(1, $ex->getCode()); // check moduleId
        }

        $this->source->update(self::$moduleName);
    }

    /**
    * @return void
    */
    public function testCreateEntityFromConfig(): void
    {
        $configFile = self::getConfFilePath();
        $result = $this->source->createEntityFromConfig($configFile);
        $images = [
            ModuleSource::PATH_WEB . $result->getId() . '/' . self::$moduleInfo['images'],
        ];

        $this->assertInstanceOf(Module::class, $result);
        $this->assertEquals(self::$moduleName, $result->getId());
        $this->assertEquals(ModuleSource::TYPE, $result->getType());
        $this->assertEquals(self::$moduleInfo['rname'], $result->getName());
        $this->assertEquals(self::$moduleInfo['author'], $result->getAuthor());
        $this->assertEquals(self::$moduleInfo['mod_release'], $result->getVersion());
        $this->assertEquals($images, $result->getImages());
        $this->assertEquals(self::$moduleInfo['stability'], $result->getStability());
        $this->assertEquals(self::$moduleInfo['last_update'], $result->getLastUpdate());
        $this->assertEquals(self::$moduleInfo['release_note'], $result->getReleaseNote());
        $this->assertTrue($result->isInstalled());
        $this->assertFalse($result->isUpdated());
    }

//    public function testGetModuleConf()
//    {
//        $moduleSource = new ModuleSource($this->containerWrap);
//        $result = $this->invokeMethod($moduleSource, 'getModuleConf', [self::getConfFilePath()]);
//        //'php://filter/read=string.rot13/resource=' .
//    }

    public static function getConfFilePath(): string
    {
        return 'vfs://modules/' . self::$moduleName . '/' . ModuleSource::CONFIG_FILE;
    }

    public static function buildConfContent(): string
    {
        $result = '<?php';
        $moduleName = self::$moduleName;

        foreach (self::$moduleInfo as $key => $data) {
            $result .= "\n\$module_conf['{$moduleName}']['{$key}'] = '{$data}'";
        }

        return $result;
    }
}
