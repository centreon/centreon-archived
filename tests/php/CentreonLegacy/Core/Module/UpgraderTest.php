<?php
/**
 * Copyright 2016-2019 Centreon
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace CentreonLegacy\Core\Module;

use Pimple\Psr11\Container;
use Centreon\Test\Mock\CentreonDB;
use Centreon\Test\Mock\DependencyInjector\ServiceContainer;
use Centreon\Test\Mock\DependencyInjector\ConfigurationDBProvider;
use Centreon\Test\Mock\DependencyInjector\FilesystemProvider;
use Centreon\Test\Mock\DependencyInjector\FinderProvider;

class UpgraderTest extends \PHPUnit\Framework\TestCase
{
    private $container;
    private $db;
    private $information;
    private $utils;

    public function setUp(): void
    {
        $this->container = new ServiceContainer();

        $this->db = new CentreonDB();

        $this->information = $this->getMockBuilder('CentreonLegacy\Core\Module\Information')
            ->disableOriginalConstructor()
            ->onlyMethods(array('getInstalledInformation', 'getModulePath', 'getConfiguration'))
            ->getMock();

        $installedInformation = array(
            'name' => 'MyModule',
            'rname' => 'MyModule',
            'mod_release' => '1.0.0',
            'is_removeable' => 1,
            'infos' => 'my module for unit test',
            'author' => 'unit test',
            'svc_tools' => null,
            'host_tools' => null
        );
        $this->information->expects($this->any())
            ->method('getInstalledInformation')
            ->willReturn($installedInformation);

        $configuration = array(
            'name' => 'MyModule',
            'rname' => 'MyModule',
            'mod_release' => '1.0.1',
            'is_removeable' => 1,
            'infos' => 'my module for unit test',
            'author' => 'unit test',
            'svc_tools' => null,
            'host_tools' => null
        );
        $this->information->expects($this->any())
            ->method('getConfiguration')
            ->willReturn($configuration);

        $this->information->expects($this->any())
            ->method('getModulePath')
            ->willReturn('/MyModule/');

        $this->utils = $this->getMockBuilder('CentreonLegacy\Core\Utils\Utils')
            ->disableOriginalConstructor()
            ->onlyMethods(array('requireConfiguration', 'executeSqlFile', 'executePhpFile'))
            ->getMock();
        $upgradeConfiguration = array(
            'MyModule' => array(
                'name' => 'MyModule',
                'rname' => 'MyModule',
                'release_from' => '1.0.0',
                'release_to' => '1.0.1',
                'infos' => 'my module for unit test',
                'author' => 'unit test'
            )
        );
        $this->utils->expects($this->any())
            ->method('requireConfiguration')
            ->willReturn($upgradeConfiguration);
        $this->utils->expects($this->any())
            ->method('executeSqlFile')
            ->willReturn(true);
        $this->utils->expects($this->any())
            ->method('executePhpFile')
            ->willReturn(true);
    }

    public function tearDown(): void
    {
        $this->container->terminate();
        $this->container = null;
    }

    public function testUpgrader()
    {
        $filesystem = $this->getMockBuilder('\Symfony\Component\Filesystem\Filesystem')
            ->disableOriginalConstructor()
            ->onlyMethods(array('exists'))
            ->getMock();
        $filesystem->expects($this->any())
            ->method('exists')
            ->will($this->returnCallback(array($this, 'mockExists')));
            //->willReturn(true);
        $this->container->registerProvider(new FilesystemProvider($filesystem));

        $finder = $this->getMockBuilder('\Symfony\Component\Finder\Finder')
            ->disableOriginalConstructor()
            ->onlyMethods(array('directories', 'depth', 'in'))
            ->getMock();
        $finder->expects($this->any())
            ->method('directories')
            ->willReturn($finder);
        $finder->expects($this->any())
            ->method('depth')
            ->willReturn($finder);
        $finder->expects($this->any())
            ->method('in')
            ->willReturn(
                array(
                    new \SplFileInfo('MyModule-1.0.1')
                )
            );
        $this->container->registerProvider(new FinderProvider($finder));

        $query = 'UPDATE modules_informations ' .
            'SET `name` = :name , `rname` = :rname , `is_removeable` = :is_removeable , ' .
            '`infos` = :infos , `author` = :author , ' .
            '`svc_tools` = :svc_tools , `host_tools` = :host_tools WHERE id = :id';
        $this->db->addResultSet(
            $query,
            array()
        );
        $this->db->addResultSet(
            'SELECT MAX(id) as id FROM modules_informations',
            array(array('id' => 1))
        );
        $this->db->addResultSet(
            'UPDATE modules_informations SET `mod_release` = :mod_release WHERE id = :id',
            array()
        );

        $this->container->registerProvider(new ConfigurationDBProvider($this->db));

        $upgrader = new Upgrader(new Container($this->container), $this->information, 'MyModule', $this->utils, 1);
        $id = $upgrader->upgrade();

        $this->assertEquals($id, 1);
    }

    public function mockExists($file)
    {
        if (preg_match('/install\.pre\.php/', $file)) {
            return false;
        }
        return true;
    }
}
