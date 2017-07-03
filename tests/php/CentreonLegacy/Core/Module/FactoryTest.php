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

use \Centreon\Test\Mock\CentreonDB;
use Centreon\Test\Mock\DependencyInjector\ServiceContainer;
use Centreon\Test\Mock\DependencyInjector\ConfigurationDBProvider;
use Centreon\Test\Mock\DependencyInjector\FilesystemProvider;
use Centreon\Test\Mock\DependencyInjector\FinderProvider;

/**
 * Description of factoryTest
 *
 * @author lionel
 */
class FactoryTest extends \PHPUnit_Framework_TestCase
{
    private $db;
    private $license;
    private $utils;

    public function setUp()
    {
        $this->container = new ServiceContainer();

        $this->db = new CentreonDB();

        $this->license = $this->getMockBuilder('CentreonLegacy\Core\Module\License')
            ->disableOriginalConstructor()
            ->getMock();

        $this->utils = $this->getMockBuilder('CentreonLegacy\Core\Utils\Utils')
            ->disableOriginalConstructor()
            ->getMock();
        
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
    }

    public function tearDown()
    {
        $this->container->terminate();
        $this->container = null;
    }
    
    public function testNewInformation()
    {
        $factory = new Factory($this->container, $this->utils);
        $this->assertInstanceOf('\CentreonLegacy\Core\Module\Information', $factory->newInformation());
    }
    
    public function testNewInstaller()
    {
        $factory = new Factory($this->container, $this->utils);
        $this->assertInstanceOf('\CentreonLegacy\Core\Module\Installer', $factory->newInstaller('MyModule'));
    }
    
    public function testNewUpgrader()
    {
        $factory = new Factory($this->container, $this->utils);
        $this->assertInstanceOf('\CentreonLegacy\Core\Module\Upgrader', $factory->newUpgrader('MyModule', 1));
    }
    
    public function testNewRemover()
    {
        $factory = new Factory($this->container, $this->utils);
        $this->assertInstanceOf('\CentreonLegacy\Core\Module\Remover', $factory->newRemover('MyModule', 1));
    }
    
    public function testNewLicense()
    {
        $factory = new Factory($this->container, $this->utils);
        $this->assertInstanceOf('\CentreonLegacy\Core\Module\License', $factory->newLicense());
    }
}
