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

namespace Centreon\Tests\Domain\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Vfs\FileSystem;
use Vfs\Node\Directory;
use Vfs\Node\File;
use Vfs\VfsStream;
use Centreon\Test\Mock;
use Centreon\Domain\Service\I18nService;
use CentreonLegacy\Core\Module\Information;

class I18nTest extends TestCase
{
    private $fs;
    public static $moduleName = 'test-module';
    public static $moduleNameMissing = 'missing-module';
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
        'stability' => 'alpha',
        'last_update' => '2001-01-01',
        'release_note' => 'http://localhost',
        'images' => 'images/image1.png',
    ];

    protected function setUp()
    {
        $directory = [
            'json' => [
                'valid.json' => '{"VALID_KEY":123}',
                'invalid.json' => '{"test":123'
            ]
        ];
        // setup and cache the virtual file system
        //$this->file_system = vfsStream::setup('root', 444, $directory);
        // mount VFS
        $this->fs = FileSystem::factory('vfs://');
        

        $this->fs->mount();
        $this->fs->get('/')->add('modules', new Directory([]));
        $this->fs->get('/modules')->add(static::$moduleName, new Directory([]));
        mkdir('vfs://usr/share/centreon/www/locale/en_US.UTF-8/LC_MESSAGES/toto', 0777, true);
        file_put_contents(
            'vfs://usr/share/centreon/www/locale/en_US.UTF-8/LC_MESSAGES/toto/messages.ser',
            'a:2:{s:2:"en";a:1:{s:16:"Discovered Items";s:16:"Discovered Items";}s:2:"fr";a:1:{s:16:"Discovered Items";s:21:"Eléments découverts";}}'
        );

            /*
        file_put_contents(
            'vfs://usr/share/centreon/www/locale/en_US.UTF-8/LC_MESSAGES/messages.ser',
            'a:2:{s:2:"en";a:1:{s:16:"Discovered Items";s:16:"Discovered Items";}s:2:"fr";a:1:{s:16:"Discovered Items";s:21:"Eléments découverts";}}'
        );
        */
        "/usr/share/centreon/www/locale/en_US.UTF-8/LC_MESSAGES/messages.ser";
        /*
        $this->fs->get('/modules/' . static::$moduleName)
            ->add(ModuleSource::CONFIG_FILE, new File(static::buildConfContent()))
        ;
        $this->fs->get('/modules/' . static::$moduleName)
            ->add(ModuleSource::LICENSE_FILE, new File(''))
        ;
        */
        $moduleInformationMock = $this->getMockBuilder(Information::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->translation = new I18nService($moduleInformationMock);

            /*
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
                        I18nTest::$moduleName => I18nTest::$moduleInfo,
                    ];

                    return $result;
                }))
        ;
        */
    }

    public function tearDown()
    {
        // unmount VFS
        $this->fs->unmount();
    }

    public function testGetTranslation()
    {
        $result = $this->translation->getTranslation();

        //var_dump(file_get_contents('vfs://usr/share/centreon/www/locale/en_US.UTF-8/LC_MESSAGES/toto/messages.ser'));
        $this->assertTrue(is_array($result));

        //$result2 = $this->source->getList(static::$moduleNameMissing);
        //$this->assertEquals([], $result2);
    }
}
