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
    private $utils;
    private $configuration;

    public function setUp(): void
    {
        $this->container = new ServiceContainer();

        $this->db = new CentreonDB();

        $this->utils = $this->getMockBuilder('CentreonLegacy\Core\Utils\Utils')
            ->disableOriginalConstructor()
            ->getMock();

        $this->configuration = array(
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
            'directory' => 'my-widget',
            'preferences' => array(
                'preference' => array(
                    array(
                        '@attributes' => array(
                            'label' => 'Host Name',
                            'name' => 'host_name_search',
                            'defaultValue' => '',
                            'type' => 'compare',
                            'header' => 'Filters'
                        )

                    )
                )
            )
        );
    }

    public function tearDown(): void
    {
        $this->container->terminate();
        $this->container = null;
    }

    public function testGetConfiguration()
    {
        $expectedResult = $this->configuration;
        $expectedResult['autoRefresh'] = 0;

        $filesystem = $this->getMockBuilder('\Symfony\Component\Filesystem\Filesystem')
            ->disableOriginalConstructor()
            ->onlyMethods(array('exists'))
            ->getMock();
        $filesystem->expects($this->any())
            ->method('exists')
            ->willReturn(true);
        $this->container->registerProvider(new FilesystemProvider($filesystem));

        $this->utils->expects($this->any())
            ->method('buildPath')
            ->willReturn('MyWidget');
        $this->utils->expects($this->any())
            ->method('xmlIntoArray')
            ->willReturn($this->configuration);

        $information = new Information(new Container($this->container), $this->utils);
        $configuration = $information->getConfiguration('my-widget');

        $this->assertEquals($configuration, $expectedResult);
    }

    public function testGetTypes()
    {
        $expectedResult = array(
            'type1' => array(
                'id' => 1,
                'name' => 'type1'
            )
        );

        $query = 'SELECT ft_typename, field_type_id ' .
            'FROM widget_parameters_field_type ';
        $this->db->addResultSet(
            $query,
            array(
                array(
                    'ft_typename' => 'type1',
                    'field_type_id' => 1
                )
            )
        );

        $this->container->registerProvider(new ConfigurationDBProvider($this->db));

        $information = new Information(new Container($this->container), $this->utils);
        $types = $information->getTypes();

        $this->assertEquals($types, $expectedResult);
    }

    public function testGetParameterIdByName()
    {
        $query = 'SELECT parameter_id ' .
            'FROM widget_parameters ' .
            'WHERE parameter_code_name = :name ';
        $this->db->addResultSet(
            $query,
            array(
                array('parameter_id' => 1)
            )
        );

        $this->container->registerProvider(new ConfigurationDBProvider($this->db));

        $information = new Information(new Container($this->container), $this->utils);
        $id = $information->getParameterIdByName('MyWidget');

        $this->assertEquals($id, 1);
    }

    public function testGetParameters()
    {
        $expectedResult = array(
            'parameter1' => array(
                'parameter_id' => 1,
                'parameter_name' => 'parameter 1',
                'parameter_code_name' => 'parameter1',
                'default_value' => '',
                'parameter_order' => 1,
                'header_title' => 'title',
                'require_permission' => null,
                'widget_model_id' => 1,
                'field_type_id' => 1
            )
        );

        $query = 'SELECT * ' .
            'FROM widget_parameters ' .
            'WHERE widget_model_id = :id ';
        $this->db->addResultSet(
            $query,
            array(
                array(
                    'parameter_id' => 1,
                    'parameter_name' => 'parameter 1',
                    'parameter_code_name' => 'parameter1',
                    'default_value' => '',
                    'parameter_order' => 1,
                    'header_title' => 'title',
                    'require_permission' => null,
                    'widget_model_id' => 1,
                    'field_type_id' => 1
                )
            )
        );

        $this->container->registerProvider(new ConfigurationDBProvider($this->db));

        $information = new Information(new Container($this->container), $this->utils);
        $parameters = $information->getParameters(1);

        $this->assertEquals($parameters, $expectedResult);
    }

    public function testGetIdByName()
    {
        $query = 'SELECT widget_model_id ' .
            'FROM widget_models ' .
            'WHERE directory = :directory';
        $this->db->addResultSet(
            $query,
            array(
                array('widget_model_id' => 1)
            )
        );

        $this->container->registerProvider(new ConfigurationDBProvider($this->db));

        $information = new Information(new Container($this->container), $this->utils);
        $id = $information->getIdByName('MyWidget');

        $this->assertEquals($id, 1);
    }

    public function testGetAvailableList()
    {
        $configuration = $this->configuration;
        $configuration['autoRefresh'] = 0;
        $expectedResult = array(
            'my-widget' => $configuration
        );

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
                    new \SplFileInfo('my-widget')
                )
            );
        $this->container->registerProvider(new FinderProvider($finder));

        $filesystem = $this->getMockBuilder('\Symfony\Component\Filesystem\Filesystem')
            ->disableOriginalConstructor()
            ->onlyMethods(array('exists'))
            ->getMock();
        $filesystem->expects($this->any())
            ->method('exists')
            ->willReturn(true);
        $this->container->registerProvider(new FilesystemProvider($filesystem));

        $this->utils->expects($this->any())
            ->method('buildPath')
            ->willReturn('MyWidget');
        $this->utils->expects($this->any())
            ->method('xmlIntoArray')
            ->willReturn($this->configuration);

        $information = new Information(new Container($this->container), $this->utils);
        $list = $information->getAvailableList();

        $this->assertEquals($list, $expectedResult);
    }

    public function testGetList()
    {
        $configuration = $this->configuration;
        $configuration['autoRefresh'] = 0;
        $configuration['source_available'] = true;
        $configuration['is_installed'] = true;
        $configuration['upgradeable'] = false;
        $configuration['installed_version'] = '1.0.0';
        $configuration['available_version'] = '1.0.0';
        $configuration['id'] = 1;
        unset($configuration['version']);

        $expectedResult = array(
            'my-widget' => $configuration
        );

        $this->db->addResultSet(
            'SELECT * FROM widget_models ',
            array(
                array(
                    'widget_model_id' => 1,
                    'title' => 'my title',
                    'description' => 'my description',
                    'url' => '',
                    'version' => '1.0.0',
                    'directory' => 'my-widget',
                    'author' => 'phpunit',
                    'email' => 'root@localhost',
                    'website' => 'centreon.com',
                    'keywords' => 'centreon',
                    'screenshot' => null,
                    'thumbnail' => '',
                    'autoRefresh' => 0
                )
            )
        );
        $this->container->registerProvider(new ConfigurationDBProvider($this->db));

        $this->utils->expects($this->any())
            ->method('buildPath')
            ->willReturn('MyWidget');

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
                    new \SplFileInfo('my-widget')
                )
            );
        $this->container->registerProvider(new FinderProvider($finder));

        $filesystem = $this->getMockBuilder('\Symfony\Component\Filesystem\Filesystem')
            ->disableOriginalConstructor()
            ->onlyMethods(array('exists'))
            ->getMock();
        $filesystem->expects($this->any())
            ->method('exists')
            ->willReturn(true);
        $this->container->registerProvider(new FilesystemProvider($filesystem));

        $this->utils->expects($this->any())
            ->method('xmlIntoArray')
            ->willReturn($this->configuration);

        $information = new Information(new Container($this->container), $this->utils);
        $list = $information->getList();

        $this->assertEquals($list, $expectedResult);
    }
}
