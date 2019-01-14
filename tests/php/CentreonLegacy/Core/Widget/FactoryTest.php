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
namespace CentreonLegacy\Core\Widget;

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
    private $information;
    private $utils;

    public function setUp()
    {
        $this->container = new ServiceContainer();

        $this->db = new CentreonDB();

        $this->utils = $this->getMockBuilder('CentreonLegacy\Core\Utils\Utils')
            ->disableOriginalConstructor()
            ->getMock();

        $configuration = array(
            'title' => 'My Widget',
            'author' => 'Centreon',
            'email' => 'contact@centreon.com',
            'website' => 'http://www.centreon.com',
            'description' => 'Widget for displaying host monitoring information',
            'version' => '1.0.0',
            'keywords' => 'centreon, widget, host, monitoring',
            'screenshot' => '',
            'thumbnail' => './widgets/host-monitoring/resources/centreon-logo.png',
            'url' => './widgets/host-monitoring/index.php',
            'preferences' => array(),
            'autoRefresh' => 0
        );

        $this->utils->expects($this->any())
            ->method('buildPath')
            ->willReturn('/');

        $filesystem = $this->getMockBuilder('\Symfony\Component\Filesystem\Filesystem')
            ->disableOriginalConstructor()
            ->setMethods(array('exists'))
            ->getMock();
        $filesystem->expects($this->any())
            ->method('exists')
            ->willReturn(true);
        $this->container->registerProvider(new FilesystemProvider($filesystem));
    }

    public function tearDown()
    {
        $this->container->terminate();
        $this->container = null;
    }

    public function testNewInformation()
    {
        $factory = new Factory($this->container, $this->utils);
        $this->assertInstanceOf('\CentreonLegacy\Core\Widget\Information', $factory->newInformation());
    }

    public function testNewInstaller()
    {
        $factory = new Factory($this->container, $this->utils);
        $this->assertInstanceOf('\CentreonLegacy\Core\Widget\Installer', $factory->newInstaller('MyWidget'));
    }
    
    public function testNewUpgrader()
    {
        $factory = new Factory($this->container, $this->utils);
        $this->assertInstanceOf('\CentreonLegacy\Core\Widget\Upgrader', $factory->newUpgrader('MyWidget'));
    }
    
    public function testNewRemover()
    {
        $factory = new Factory($this->container, $this->utils);
        $this->assertInstanceOf('\CentreonLegacy\Core\Widget\Remover', $factory->newRemover('MyWidget'));
    }
}
