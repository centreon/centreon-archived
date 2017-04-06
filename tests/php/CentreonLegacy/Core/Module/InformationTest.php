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
use CentreonLegacy\Core\Module\Information;
use Centreon\Test\Mock\DependencyInjector\ServiceContainer;
use Centreon\Test\Mock\DependencyInjector\ConfigurationDBProvider;

class InformationTest extends \PHPUnit_Framework_TestCase
{
    private $container;
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
    }

    public function tearDown()
    {
        $this->container->terminate();
        $this->container = null;
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

        $information = new Information($this->container, $this->license, $this->utils);
        $name = $information->getNameById(1);

        $this->assertEquals($name, 'MyModule');
    }

    public function testGetList()
    {
        $expectedResult = array(
            'MyModule1' => array(
                'id' => 1,
                'name' => 'MyModule1',
                'is_installed' => true,
                'source_available' => false
            ),
            'MyModule2' => array(
                'id' => 2,
                'name' => 'MyModule2',
                'is_installed' => true,
                'source_available' => false
            )
        );

        $this->db->addResultSet(
            "SELECT * FROM modules_informations ",
            array(
                array(
                    'id' => 1,
                    'name' => 'MyModule1'
                ),
                array(
                    'id' => 2,
                    'name' => 'MyModule2'
                )
            )
        );

        $this->container->registerProvider(new ConfigurationDBProvider($this->db));

        $information = new Information($this->container, $this->license, $this->utils);
        $list = $information->getList();

        //$this->assertEquals($list, $expectedResult);
    }
}
