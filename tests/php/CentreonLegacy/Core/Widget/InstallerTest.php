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
use \Centreon\Test\Mock\CentreonDB;
use Centreon\Test\Mock\DependencyInjector\ServiceContainer;
use Centreon\Test\Mock\DependencyInjector\ConfigurationDBProvider;

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
                    ),
                    array(
                        '@attributes' => array(
                            'label' => 'Results',
                            'name' => 'entries',
                            'defaultValue' => '10',
                            'type' => 'range',
                            'min' => '10',
                            'max' => '100',
                            'step' => '10'
                        )
                    ),
                    array(
                        '@attributes' => array(
                            'label' => 'Order By',
                            'name' => 'order_by',
                            'defaultValue' => '',
                            'type' => 'sort'
                        ),
                        'option' => array(
                            array(
                                '@attributes' => array(
                                    'value' => 'h.name',
                                    'label' => 'Host Name'
                                )
                            ),
                            array(
                                '@attributes' => array(
                                    'value' => 'criticality',
                                    'label' => 'Severity'
                                )
                            )
                        )
                    )
                )
            ),
            'autoRefresh' => 0
        );

        $this->information = $this->getMockBuilder('CentreonLegacy\Core\Widget\Information')
            ->disableOriginalConstructor()
            ->onlyMethods(array('getConfiguration', 'getTypes', 'isInstalled', 'getIdByName', 'getParameterIdByName'))
            ->getMock();

        $this->information->expects($this->any())
            ->method('getConfiguration')
            ->willReturn($configuration);

        $this->information->expects($this->any())
            ->method('getTypes')
            ->willReturn(
                array(
                    'compare' => array(
                        'id' => 1,
                        'name' => 'compare'
                    ),
                    'range' => array(
                        'id' => 2,
                        'name' => 'range'
                    ),
                    'sort' => array(
                        'id' => 3,
                        'name' => 'sort'
                    )
                )
            );

        $this->information->expects($this->any())
            ->method('isInstalled')
            ->willReturn(false);

        $this->information->expects($this->any())
            ->method('getIdByName')
            ->willReturn(1);

        $this->information->expects($this->any())
            ->method('getParameterIdByName')
            ->willReturn(1);

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
        $query = 'INSERT INTO widget_models ' .
            '(title, description, url, version, directory, author, ' .
            'email, website, keywords, thumbnail, autoRefresh) ' .
            'VALUES (:title, :description, :url, :version, :directory, :author, ' .
            ':email, :website, :keywords, :thumbnail, :autoRefresh) ';
        $this->db->addResultSet(
            $query,
            array()
        );
        $query = 'INSERT INTO widget_parameters ' .
            '(widget_model_id, field_type_id, parameter_name, parameter_code_name, ' .
            'default_value, parameter_order, require_permission, header_title) ' .
            'VALUES ' .
            '(:widget_model_id, :field_type_id, :parameter_name, :parameter_code_name, ' .
            ':default_value, :parameter_order, :require_permission, :header_title) ';
        $this->db->addResultSet(
            $query,
            array()
        );
        $query = 'INSERT INTO widget_parameters_multiple_options ' .
            '(parameter_id, option_name, option_value) VALUES ' .
            '(:parameter_id, :option_name, :option_value) ';
        $this->db->addResultSet(
            $query,
            array()
        );
        $query = 'INSERT INTO widget_parameters_range (parameter_id, min_range, max_range, step) ' .
            'VALUES (:parameter_id, :min_range, :max_range, :step) ';
        $this->db->addResultSet(
            $query,
            array()
        );
        $this->container->registerProvider(new ConfigurationDBProvider($this->db));

        $this->utils->expects($this->any())
            ->method('buildPath')
            ->willReturn('MyWidget');

        $installer = new Installer(new Container($this->container), $this->information, 'MyWidget', $this->utils);
        $id = $installer->install();

        $this->assertEquals($id, 1);
    }
}
