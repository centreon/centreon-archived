<?php
/*
 * Copyright 2005-2014 MERETHIS
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
 * this program; if not, see <htenv://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

namespace Test\CentreonConfiguration\Models;

use \Test\Centreon\DbTestCase;
use \CentreonConfiguration\Models\Environment;

class EnvironmentTest extends DbTestCase
{
    protected $errMsg = 'Object not in database.';
    protected $dataPath = '/modules/CentreonConfigurationModule/tests/data/json/';

    public function testInsert()
    {
        $envInsert = array(
            'name' => 'test_name',
            'description' => 'test_description',
            'level' => '1',
            'organization_id' => '1'
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
            'organization_id' => '1'
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
                'organization_id' => '1'
            ),
            array(
                'environment_id' => '2',
                'name' => 'Preproduction',
                'description' => 'Preproduction environment',
                'level' => '10',
                'organization_id' => '1'
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
                'organization_id' => '1'
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
                'organization_id' => '1'
            )
        );
        $this->assertEquals($expectedResult, Environment::getList('*', 1, 1));

    }

    public function testGetListOneParameter()
    {
        $expectedResult = array(
            array('name' => 'Production'),
            array('name' => 'Preproduction')
        );
        $this->assertEquals($expectedResult, Environment::getList('name'));
    }

    public function testGetListMultipleParameters()
    {
        $expectedResult = array(
            array('environment_id' => '1', 'name' => 'Production'),
            array('environment_id' => '2', 'name' => 'Preproduction')
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
                array('name' => 'prod')
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
            'organization_id' => '1'
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
        $expectedResult = array('2');
        $this->assertEquals($expectedResult, Environment::getIdByParameter('name', 'Production'));
    }

    public function testGetMultipleIdsByParameters()
    {
        $expectedResult = array('1', '2');
        $this->assertEquals($expectedResult, Environment::getIdByParameter('name', array('Production', 'Preproduction')));
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
                'organization_id'
            ),
            Environment::getColumns()
        );
    }
}
