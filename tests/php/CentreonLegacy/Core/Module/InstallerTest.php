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

class InstallerTest extends \PHPUnit\Framework\TestCase
{
    private $container;
    private $db;
    private $information;
    private $utils;

    public function setUp(): void
    {
        $this->container = new ServiceContainer();

        $this->db = new CentreonDB();

        $configuration = array(
            'name' => 'MyModule',
            'rname' => 'MyModule',
            'mod_release' => '1.0.0',
            'is_removeable' => 1,
            'infos' => 'my module for unit test',
            'author' => 'unit test',
            'svc_tools' => null,
            'host_tools' => null
        );
        $this->information = $this->getMockBuilder('CentreonLegacy\Core\Module\Information')
            ->disableOriginalConstructor()
            ->onlyMethods(array('getConfiguration'))
            ->getMock();

        $this->information->expects($this->any())
            ->method('getConfiguration')
            ->willReturn($configuration);

        $this->utils = $this->getMockBuilder('CentreonLegacy\Core\Utils\Utils')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function tearDown(): void
    {
        $this->container->terminate();
        $this->container = null;
    }

    public function testInstall()
    {
        $filesystem = $this->getMockBuilder('\Symfony\Component\Filesystem\Filesystem')
            ->disableOriginalConstructor()
            ->onlyMethods(array('exists'))
            ->getMock();
        $filesystem->expects($this->any())
            ->method('exists')
            ->willReturn(true);
        $this->container->registerProvider(new FilesystemProvider($filesystem));

        $query = 'INSERT INTO modules_informations ' .
            '(`name` , `rname` , `mod_release` , `is_removeable` , `infos` , `author` , ' .
            '`svc_tools`, `host_tools`)' .
            'VALUES ( :name , :rname , :mod_release , :is_removeable , :infos , :author , ' .
            ':svc_tools , :host_tools )';
        $this->db->addResultSet(
            $query,
            array()
        );
        $this->db->addResultSet(
            'SELECT MAX(id) as id FROM modules_informations',
            array(array('id' => 1))
        );

        $this->container->registerProvider(new ConfigurationDBProvider($this->db));

        $installer = new Installer(new Container($this->container), $this->information, 'MyModule', $this->utils);
        $id = $installer->install();

        $this->assertEquals($id, 1);
    }
}
