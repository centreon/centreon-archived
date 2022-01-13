<?php
/**
 * Copyright 2016 Centreon
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

class InformationTest extends \PHPUnit\Framework\TestCase
{
    private $container;
    private $db;
    private $license;
    private $utils;

    public function setUp(): void
    {
        $this->container = new ServiceContainer();

        $this->db = new CentreonDB();

        $this->license = $this->getMockBuilder('CentreonLegacy\Core\Module\License')
            ->disableOriginalConstructor()
            ->getMock();

        $this->utils = $this->getMockBuilder('CentreonLegacy\Core\Utils\Utils')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function tearDown(): void
    {
        $this->container->terminate();
        $this->container = null;
    }

    public function testGetConfiguration()
    {
        $expectedResult = array(
            'name' => 'MyModule',
            'rname' => 'MyModule',
            'mod_release' => '1.0.0'
        );

        $moduleConfiguration = array(
            'MyModule' => array(
                'name' => 'MyModule',
                'rname' => 'MyModule',
                'mod_release' => '1.0.0'
            )
        );
        $this->utils->expects($this->any())
            ->method('requireConfiguration')
            ->willReturn($moduleConfiguration);

        $information = new Information(new Container($this->container), $this->license, $this->utils);
        $configuration = $information->getConfiguration('MyModule');

        $this->assertEquals($configuration, $expectedResult);
    }

    public function testGetNameById()
    {
        $this->db->addResultSet(
            "SELECT name FROM modules_informations WHERE id = :id",
            array(
                array('name' => 'MyModule')
            )
        );

        $this->container->registerProvider(new ConfigurationDBProvider($this->db));

        $information = new Information(new Container($this->container), $this->license, $this->utils);
        $name = $information->getNameById(1);

        $this->assertEquals($name, 'MyModule');
    }

    public function testGetList()
    {
        $expectedResult = array(
            'MyModule1' => array(
                'id' => 1,
                'name' => 'MyModule1',
                'rname' => 'MyModule1',
                'mod_release' => '1.0.0',
                'license_expiration' => '2020-10-10 12:00:00',
                'source_available' => true,
                'is_installed' => true,
                'upgradeable' => false,
                'installed_version' => '1.0.0',
                'available_version' => '1.0.0'
            ),
            'MyModule2' => array(
                'id' => 2,
                'name' => 'MyModule2',
                'rname' => 'MyModule2',
                'mod_release' => '2.0.0',
                'license_expiration' => '2020-10-10 12:00:00',
                'source_available' => true,
                'is_installed' => true,
                'upgradeable' => true,
                'installed_version' => '1.0.0',
                'available_version' => '2.0.0'
            )
        );

        $this->db->addResultSet(
            "SELECT * FROM modules_informations ",
            array(
                array(
                    'id' => 1,
                    'name' => 'MyModule1',
                    'mod_release' => '1.0.0'
                ),
                array(
                    'id' => 2,
                    'name' => 'MyModule2',
                    'mod_release' => '1.0.0'
                )
            )
        );

        $this->container->registerProvider(new ConfigurationDBProvider($this->db));

        $filesystem = $this->getMockBuilder('\Symfony\Component\Filesystem\Filesystem')
            ->disableOriginalConstructor()
            ->onlyMethods(array('exists'))
            ->getMock();
        $filesystem->expects($this->any())
            ->method('exists')
            ->willReturn(true);
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
                    new \SplFileInfo('MyModule1'),
                    new \SplFileInfo('MyModule2')
                )
            );
        $this->container->registerProvider(new FinderProvider($finder));

        $moduleConfiguration1 = array(
            'MyModule1' => array(
                'name' => 'MyModule1',
                'rname' => 'MyModule1',
                'mod_release' => '1.0.0'
            )
        );
        $moduleConfiguration2 = array(
            'MyModule2' => array(
                'name' => 'MyModule2',
                'rname' => 'MyModule2',
                'mod_release' => '2.0.0'
            )
        );
        $this->utils->expects($this->exactly(2))
            ->method('requireConfiguration')
            ->will(
                $this->onConsecutiveCalls(
                    $moduleConfiguration1,
                    $moduleConfiguration2
                )
            );

        $this->license->expects($this->any())
            ->method('getLicenseExpiration')
            ->willReturn('2020-10-10 12:00:00');

        $information = new Information(new Container($this->container), $this->license, $this->utils);
        $list = $information->getList();

        $this->assertEquals($list, $expectedResult);
    }
}
