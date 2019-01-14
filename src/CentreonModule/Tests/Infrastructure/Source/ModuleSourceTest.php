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
use CentreonLegacy\Core\Module\License;
use CentreonModule\Infrastructure\Source\ModuleSource;
use CentreonModule\Infrastructure\Entity\Module;

class ModuleSourceTest extends TestCase
{

    use TestCaseExtensionTrait;

    public static $moduleName = 'test-module';
    public static $moduleInfo = [
        'rname' => 'Curabitur congue porta neque',
        'name' => 'test-module',
        'mod_release' => 'x.y.q',
        'infos' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent id ante neque.',
        'is_removeable' => '1',
        'author' => 'Centreon',
        'lang_files' => '0',
        'sql_files' => '1',
        'php_files' => '0',
    ];
    public static $sqlQueryVsData = [
        "SELECT `name` AS `id`, `mod_release` AS `version` FROM `modules_informations`" => [
            [
                'id' => 'test-module',
                'version' => 'x.y.z',
            ],
        ],
    ];

    protected function setUp()
    {
        // mount VFS
        $this->fs = FileSystem::factory('vfs://');
        $this->fs->mount();
        $this->fs->get('/')->add('modules', new Directory([]));
        $this->fs->get('/modules')->add(static::$moduleName, new Directory([]));
        $this->fs->get('/modules/' . static::$moduleName)
            ->add(ModuleSource::CONFIG_FILE, new File(static::buildConfContent()))
        ;
        $this->fs->get('/modules/' . static::$moduleName)
            ->add(ModuleSource::LICENSE_FILE, new File(''))
        ;

        // provide services
        $container = new Container;
        $container['finder'] = new Finder;
        $container['centreon.legacy.license'] = $this->getMockBuilder(License::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        // DB service
        $container['centreon.db-manager'] = new Mock\CentreonDBManagerService;
        foreach (static::$sqlQueryVsData as $query => $data) {
            $container['centreon.db-manager']->addResultSet($query, $data);
        }

        $this->containerWrap = new ContainerWrap($container);

        $this->source = $this->getMockBuilder(ModuleSource::class)
            ->setMethods([
                'getPath',
                'getModuleConf',
                'getLicenseFile',
            ])
            ->setConstructorArgs([
                $this->containerWrap,
            ])
            ->getMock()
        ;
        $this->source
            ->method('getPath')
            ->will($this->returnCallback(function () {
                    $result = 'vfs://modules';

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
        $this->source
            ->method('getLicenseFile')
            ->will($this->returnCallback(function () {
                    $result = 'vfs://modules/' .
                        ModuleSourceTest::$moduleName . '/' .
                        ModuleSource::LICENSE_FILE
                    ;

                    return $result;
            }))
        ;
    }

    public function tearDown()
    {
        // unmount VFS
        $this->fs->unmount();
    }

    public function testGetList()
    {
        $result = $this->source->getList();

        $this->assertTrue(is_array($result));

        $result2 = $this->source->getList('missing-module');
        $this->assertEquals([], $result2);
    }

    public function testCreateEntityFromConfig()
    {
        $configFile = static::getConfFilePath();
        $result = $this->source->createEntityFromConfig($configFile);

        $this->assertInstanceOf(Module::class, $result);
        $this->assertEquals(static::$moduleName, $result->getId());
        $this->assertEquals(ModuleSource::TYPE, $result->getType());
        $this->assertEquals(static::$moduleInfo['rname'], $result->getName());
        $this->assertEquals(static::$moduleInfo['author'], $result->getAuthor());
        $this->assertEquals(static::$moduleInfo['mod_release'], $result->getVersion());
        $this->assertTrue($result->isInstalled());
        $this->assertFalse($result->isUpdated());
    }

//    public function testGetModuleConf()
//    {
//        $moduleSource = new ModuleSource($this->containerWrap);
//        $result = $this->invokeMethod($moduleSource, 'getModuleConf', [static::getConfFilePath()]);
//        //'php://filter/read=string.rot13/resource=' . 
//    }

    public function testGetLicenseFile()
    {
        $moduleSource = new ModuleSource($this->containerWrap);
        $result = $this->invokeMethod($moduleSource, 'getLicenseFile', [static::getLicenseFilePath()]);

        $this->assertTrue(strpos($result, ModuleSource::LICENSE_FILE) > -1);
    }

    public static function getConfFilePath(): string
    {
        return 'vfs://modules/' . static::$moduleName . '/' . ModuleSource::CONFIG_FILE;
    }

    public static function getLicenseFilePath(): string
    {
        return 'vfs://modules/' . static::$moduleName . '/' . ModuleSource::LICENSE_FILE;
    }

    public static function buildConfContent(): string
    {
        $result = '<?php';
        $moduleName = static::$moduleName;

        foreach (static::$moduleInfo as $key => $data) {
            $result .= "\n\$module_conf['{$moduleName}']['{$key}'] = '{$data}'";
        }

        return $result;
    }
}
