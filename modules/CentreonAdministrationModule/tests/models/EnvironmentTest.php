<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

namespace Test\CentreonAdministration\Models;

use \Test\Centreon\DbTestCase;
use CentreonAdministration\Models\Environment;

class EnvironmentTest extends DbTestCase
{
    protected $errMsg = 'Object not in database.';
    protected $dataPath = '/modules/CentreonAdministrationModule/tests/data/json/';

    public function testInsert()
    {
        $envInsert = array(
            'name' => 'test_name',
            'description' => 'test_description',
            'level' => '1',
            'organization_id' => '1',
            'icon_id' => null,
        );
        Environment::insert($envInsert);
        $this->tableEqualsXml(
            'cfg_environments',
            dirname(__DIR__) . '/data/environment.insert.xml'
        );
    }

    public function testInsertDuplicateKey()
    {
        $envInsert = array(
            'name' => 'test_name',
            'description' => 'test_description',
            'level' => '1',
            'organization_id' => '1'
        );
        Environment::insert($envInsert);
        $this->setExpectedException(
            'PDOException'
        );
        Environment::insert($envInsert);
    }

    public function testDelete()
    {
        Environment::delete(1);
        $this->tableEqualsXml(
            'cfg_environments',
            dirname(__DIR__) . '/data/environment.delete.xml'
        );
    }

    public function testDeleteUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Environment::delete(9999);
    }

    public function testUpdate()
    {
        $newInfo = array(
            'description' => 'new_description'
        );
        Environment::update(1, $newInfo);
        $this->tableEqualsXml(
            'cfg_environments',
            dirname(__DIR__) . '/data/environment.update.xml'
        );
    }

    public function testUpdateDuplicateKey()
    {
        $newInfo = array(
            'name' => 'Production'
        );
        $this->setExpectedException(
            'PDOException',
            '',
            23000
        );
        Environment::update(2, $newInfo);
    }

    public function testUpdateUnknownId()
    {
        $newInfo = array(
            'name' => 'Test'
        );
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Environment::update(9999, $newInfo);
    }

    public function testDuplicateItemOnce()
    {
        Environment::duplicate(1);
        $this->tableEqualsXml(
            'cfg_environments',
            dirname(__DIR__) . '/data/environment.duplicate-1.xml'
        );
    }

    public function testDuplicateItemMultipleTimes()
    {
        Environment::duplicate(1, 2);
        $this->tableEqualsXml(
            'cfg_environments',
            dirname(__DIR__) . '/data/environment.duplicate-2.xml'
        );
    }

    public function testDuplicateUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Environment::duplicate(9999);

        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Environment::duplicate(9999, 2);
    }

    public function testGetAllParameters()
    {
        $testInformation = array(
            'environment_id' => '2',
            'name' => 'Preproduction',
            'description' => 'Preproduction environment',
            'level' => '10',
            'organization_id' => '1',
            'icon_id' => null
        );
        $arr = Environment::getParameters(2, '*');
        $this->assertEquals($arr, $testInformation);
    }

    public function testGetSpecificParameter()
    {
        $testInformation = array(
            'name' => 'Preproduction',
        );
        $arr = Environment::getParameters(2, 'name');
        $this->assertEquals($arr, $testInformation);
    }

    public function testGetSpecificParameters()
    {
        $testInformation = array(
            'name' => 'Preproduction',
            'description' => 'Preproduction environment',
            'level' => '10'
        );
        $arr = Environment::getParameters(2, array('name', 'description', 'level'));
        $this->assertEquals($arr, $testInformation);
    }

    public function testGetParametersFromUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Environment::getParameters(9999, '*');
        
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Environment::getParameters(9999, 'name');
        
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Environment::getParameters(9999, array('name', 'description'));
    }

    public function testGetUnknownParameters()
    {
        $this->setExpectedException(
            'PDOException'
        );
        Environment::getParameters(2, 'idontexist');

        $this->setExpectedException(
            'PDOException'
        );
        Environment::getParameters(2, array('name', 'idontexist'));
    }

    public function testGetList()
    {
        $expectedResult = array(
            array(
                'environment_id' => '1',
                'name' => 'Production',
                'description' => 'Production environment',
                'level' => '5',
                'organization_id' => '1',
                'icon_id' => null
            ),
            array(
                'environment_id' => '2',
                'name' => 'Preproduction',
                'description' => 'Preproduction environment',
                'level' => '10',
                'organization_id' => '1',
                'icon_id' => null
            ),
        );
        $this->assertEquals($expectedResult, Environment::getList());
    }

    public function testGetListLimitOne()
    {
        $expectedResult = array(
            array(
                'environment_id' => '1',
                'name' => 'Production',
                'description' => 'Production environment',
                'level' => '5',
                'organization_id' => '1',
                'icon_id' => null
            )
        );
        $this->assertEquals($expectedResult, Environment::getList('*', 1));
    }

    public function testGetListSecondElementOnly()
    {
        $expectedResult = array(
            array(
                'environment_id' => '2',
                'name' => 'Preproduction',
                'description' => 'Preproduction environment',
                'level' => '10',
                'organization_id' => '1',
                'icon_id' => null
            )
        );
        $this->assertEquals($expectedResult, Environment::getList('*', 1, 1));

    }

    public function testGetListOneParameter()
    {
        $expectedResult = array(
            array('name' => 'Preproduction'),
            array('name' => 'Production')
        );
        $this->assertEquals($expectedResult, Environment::getList('name'));
    }

    public function testGetListMultipleParameters()
    {
        $expectedResult = array(
            array('environment_id' => '2', 'name' => 'Preproduction'),
            array('environment_id' => '1', 'name' => 'Production')
        );
        $this->assertEquals($expectedResult, Environment::getList(array('environment_id', 'name')));
    }

    public function testGetListWithOrder()
    {
        $expectedResult = array(
            array('name' => 'Preproduction'),
            array('name' => 'Production')
        );
        $this->assertEquals($expectedResult, Environment::getList('name', null, null, 'environment_id', 'DESC'));
    }

    public function testGetListWithOneFilter()
    {
        $expectedResult = array(
            array('name' => 'Preproduction')
        );
        $this->assertEquals(
            $expectedResult,
            Environment::getList(
                'name',
                null,
                null,
                null,
                null,
                array(
                    'name' => 'Preproduction'
                )
            )
        );
    }

    public function testGetListWithMultipleFilters()
    {
        $expectedResult = array(
            array('name' => 'Preproduction')
        );
        $this->assertEquals(
            $expectedResult,
            Environment::getList(
                'name',
                null,
                null,
                null,
                null,
                array(
                    'name' => 'Preproduction',
                    'environment_id' => 2
                )
            )
        );
    }

    public function testGetListWithFilterNoResult()
    {
        $expectedResult = array();
        $this->assertEquals(
            $expectedResult,
            Environment::getList(
                'name',
                null,
                null,
                null,
                null,
                array(
                    'name' => 'idontexist',
                )
            )
        );
    }

    public function testGetListBySearch()
    {
        $expectedResult = array(
            array('name' => 'Preproduction')
        );
        $this->assertEquals(
            $expectedResult,
            Environment::getListBySearch(
                'name',
                null,
                null,
                null,
                null,
                array('name' => 'prepro')
            )
        );
    }

    public function testGet()
    {
        $expectedResult = array(
            'environment_id' => '2',
            'name' => 'Preproduction',
            'description' => 'Preproduction environment',
            'level' => '10',
            'organization_id' => '1',
            'icon_id' => null
        );
        $this->assertEquals($expectedResult, Environment::get(2));
    }

    public function testGetWithOneParameter()
    {
        $expectedResult = array(
            'name' => 'Production'
        );
        $this->assertEquals($expectedResult, Environment::get(1, 'name'));
    }

    public function testGetWithMultipleParameters()
    {
        $expectedResult = array(
            'name' => 'Production',
            'level' => '5'
        );
        $this->assertEquals($expectedResult, Environment::get(1, array('name', 'level')));
    }

    public function testGetWithUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Environment::get(9999);
    }

    public function testGetIdByParameter()
    {
        $expectedResult = array('1');
        $this->assertEquals($expectedResult, Environment::getIdByParameter('name', 'Production'));
    }

    public function testGetMultipleIdsByParameters()
    {
        $expectedResult = array('2', '1');
        $this->assertEquals(
            $expectedResult,
            Environment::getIdByParameter('name', array('Production', 'Preproduction'))
        );
    }

    public function testGetIdByParameterWithUnknownColumn()
    {
        $this->setExpectedException(
            'PDOException'
        );
        Environment::getIdByParameter('idontexist', array('Production'));
    }

    public function testGetPrimaryKey()
    {
        $this->assertEquals('environment_id', Environment::getPrimaryKey());
    }

    public function testGetUniqueLabelField()
    {
        $this->assertEquals('name', Environment::getUniqueLabelField());
    }

    public function testGetTableName()
    {
        $this->assertEquals('cfg_environments', Environment::getTableName());
    }

    public function testGetColumns()
    {
        $this->assertEquals(
            array(
                'environment_id',
                'name',
                'description',
                'level',
                'organization_id',
                'icon_id'
            ),
            Environment::getColumns()
        );
    }
}
