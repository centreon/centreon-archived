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

namespace Test\CentreonConfiguration\Models;

use \Test\Centreon\DbTestCase;
use CentreonConfiguration\Models\Servicegroup;

class ServiceGroupTest extends DbTestCase
{
    protected $errMsg = 'Object not in database.';
    protected $dataPath = '/modules/CentreonConfigurationModule/tests/data/json/';

    public function testInsert()
    {
        $insert = array(
            'sg_name' => 'test',
            'sg_alias' => 'test alias',
            'sg_comment' => 'test comment',
            'sg_activate' => '1',
            'organization_id' => 1
        );
        Servicegroup::insert($insert);
        $this->tableEqualsXml(
            'cfg_servicegroups',
            dirname(__DIR__) . '/data/servicegroup.insert.xml'
        );
    }

    public function testInsertDuplicateKey()
    {
        $insert = array(
            'sg_name' => 'test',
            'sg_alias' => 'test alias',
            'sg_comment' => 'test comment',
            'sg_activate' => '1',
            'organization_id' => 1
        );
        Servicegroup::insert($insert);
        $this->setExpectedException(
            'PDOException'
        );
        Servicegroup::insert($insert);
    }

    public function testDelete()
    {
        Servicegroup::delete(2);
        $this->tableEqualsXml(
            'cfg_servicegroups',
            dirname(__DIR__) . '/data/servicegroup.delete.xml'
        );
    }

    public function testDeleteUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Servicegroup::delete(9999);
    }

    public function testUpdate()
    {
        $newInfo = array(
            'sg_alias' => 'new_alias'
        );
        Servicegroup::update(2, $newInfo);
        $this->tableEqualsXml(
            'cfg_servicegroups',
            dirname(__DIR__) . '/data/servicegroup.update.xml'
        );
    }

    public function testUpdateDuplicateKey()
    {
        $newInfo = array(
            'sg_name' => 'france'
        );
        $this->setExpectedException(
            'PDOException'
        );
        Servicegroup::update(2, $newInfo);
    }

    public function testUpdateUnknownId()
    {
      $newInfo = array(
            'sg_name' => 'test'
        );
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Servicegroup::update(9999, $newInfo);
    }

    public function testDuplicateItemOnce()
    {
        Servicegroup::duplicate(1);
        $this->tableEqualsXml(
            'cfg_servicegroups',
            dirname(__DIR__) . '/data/servicegroup.duplicate-1.xml'
        );
    }

    public function testDuplicateItemMultipleTimes()
    {
        Servicegroup::duplicate(1, 2);
        $this->tableEqualsXml(
            'cfg_servicegroups',
            dirname(__DIR__) . '/data/servicegroup.duplicate-2.xml'
        );
    }

    public function testDuplicateUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Servicegroup::duplicate(9999);

        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Servicegroup::duplicate(9999, 2);
    }

    public function testGetAllParameters()
    {
        $testInformation = array(
            'sg_id' => 2,
            'sg_name' => 'england',
            'sg_alias' => 'england',
            'sg_comment' => 'English services',
            'sg_activate' => '1',
            'organization_id' => 1
        );
        $arr = Servicegroup::getParameters(2, '*');
        $this->assertEquals($arr, $testInformation);
    }

    public function testGetSpecificParameter()
    {
        $testInformation = array(
            'sg_name' => 'england',
        );
        $arr = Servicegroup::getParameters(2, 'sg_name');
        $this->assertEquals($arr, $testInformation);
    }

    public function testGetSpecificParameters()
    {
        $testInformation = array(
            'sg_name' => 'england',
            'sg_alias' => 'england',
            'sg_activate' => '1'
        );
        $arr = Servicegroup::getParameters(2, array('sg_name', 'sg_alias', 'sg_activate'));
        $this->assertEquals($arr, $testInformation);
    }

    public function testGetParametersFromUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Servicegroup::getParameters(9999, '*');
        
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Servicegroup::getParameters(9999, 'sg_name');
        
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Servicegroup::getParameters(9999, array('sg_name', 'sg_alias'));
    }

    public function testGetUnknownParameters()
    {
       $this->setExpectedException(
           'PDOException'
       );
       Servicegroup::getParameters(2, 'idontexist');

       $this->setExpectedException(
           'PDOException'
       );
       Servicegroup::getParameters(2, array('sg_name', 'idontexist'));
    }

    public function testGetList()
    {
        $expectedResult = array(
            array(
                'sg_id' => 1,
                'sg_name' => 'france',
                'sg_alias' => 'france',
                'sg_comment' => 'French services',
                'sg_activate' => '1',
                'organization_id' => 1
            ),
            array(
                'sg_id' => 2,
                'sg_name' => 'england',
                'sg_alias' => 'england',
                'sg_comment' => 'English services',
                'sg_activate' => '1',
                'organization_id' => 1
            )
        );
        $this->assertEquals($expectedResult, Servicegroup::getList());
    }

    public function testGetListLimitOne()
    {
        $expectedResult = array(
            array(
                'sg_id' => 1,
                'sg_name' => 'france',
                'sg_alias' => 'france',
                'sg_comment' => 'French services',
                'sg_activate' => '1',
                'organization_id' => 1
            )
        );
        $this->assertEquals($expectedResult, Servicegroup::getList('*', 1));
    }

    public function testGetListSecondElementOnly()
    {
        $expectedResult = array(
            array(
                'sg_id' => 2,
                'sg_name' => 'england',
                'sg_alias' => 'england',
                'sg_comment' => 'English services',
                'sg_activate' => '1',
                'organization_id' => 1
            )
        );
        $this->assertEquals($expectedResult, Servicegroup::getList('*', 1, 1));

    }

    public function testGetListOneParameter()
    {
        $expectedResult = array(
            array('sg_name' => 'england'),
            array('sg_name' => 'france')
        );
        $this->assertEquals($expectedResult, Servicegroup::getList('sg_name'));
    }

    public function testGetListMultipleParameters()
    {
        $expectedResult = array(
            array('sg_id' => 2, 'sg_name' => 'england'),
            array('sg_id' => 1, 'sg_name' => 'france')
        );
        $this->assertEquals($expectedResult, Servicegroup::getList(array('sg_id', 'sg_name')));
    }

    public function testGetListWithOrder()
    {
        $expectedResult = array(
            array('sg_name' => 'england'),
            array('sg_name' => 'france')
        );
        $this->assertEquals($expectedResult, Servicegroup::getList('sg_name', null, null, 'sg_id', 'DESC'));
    }

    public function testGetListWithOneFilter()
    {
        $expectedResult = array(
            array('sg_name' => 'england')
        );
        $this->assertEquals(
            $expectedResult, 
            Servicegroup::getList(
                'sg_name', 
                null, 
                null, 
                null, 
                null, 
                array(
                    'sg_name' => 'england'
                )
            )
        );
    }

    public function testGetListWithMultipleFilters()
    {
        $expectedResult = array(
            array('sg_name' => 'france')
        );
        $this->assertEquals(
            $expectedResult, 
            Servicegroup::getList(
                'sg_name', 
                null, 
                null, 
                null, 
                null, 
                array(
                    'sg_name' => 'france',
                    'sg_id' => 1
                )
            )
        );
    }

    public function testGetListWithFilterNoResult()
    {
        $expectedResult = array();
        $this->assertEquals(
            $expectedResult, 
            Servicegroup::getList(
                'sg_name', 
                null, 
                null, 
                null, 
                null, 
                array(
                    'sg_name' => 'idontexist',
                )
            )

        );
    }

    public function testGetListBySearch()
    {
        $expectedResult = array(
            array('sg_name' => 'france')
        );
        $this->assertEquals(
            $expectedResult, 
            Servicegroup::getListBySearch(
                'sg_name', 
                null, 
                null, 
                null, 
                null,
                array('sg_comment' => 'French')
            )
        );
    }

    public function testGet()
    {
        $expectedResult = array(
            'sg_id' => 1,
            'sg_name' => 'france',
            'sg_alias' => 'france',
            'sg_comment' => 'French services',
            'sg_activate' => '1',
            'organization_id' => 1
        );
        $this->assertEquals($expectedResult, Servicegroup::get(1));
    }

    public function testGetWithOneParameter()
    {
        $expectedResult = array(
            'sg_name' => 'england'
        );
        $this->assertEquals($expectedResult, Servicegroup::get(2, 'sg_name'));
    }

    public function testGetWithMultipleParameters()
    {
        $expectedResult = array(
            'sg_name' => 'france',
            'sg_comment' => 'French services'
        );
        $this->assertEquals($expectedResult, Servicegroup::get(1, array('sg_name', 'sg_comment')));
    }

    public function testGetWithUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Servicegroup::get(9999);
    }

    public function testGetIdByParameter()
    {
        $expectedResult = array(2);
        $this->assertEquals($expectedResult, Servicegroup::getIdByParameter('sg_name', 'england'));
    }

    public function testGetMultipleIdsByParameters()
    {
        $expectedResult = array(2, 1);
        $this->assertEquals($expectedResult, Servicegroup::getIdByParameter('sg_name', array('england', 'france')));
    }

    public function testGetIdByParameterWithUnknownColumn()
    {
        $this->setExpectedException(
            'PDOException'
        );
        Servicegroup::getIdByParameter('idontexist', array('france'));
    }

    public function testGetPrimaryKey()
    {
        $this->assertEquals('sg_id', Servicegroup::getPrimaryKey());
    }

    public function testGetUniqueLabelField()
    {
        $this->assertEquals('sg_name', Servicegroup::getUniqueLabelField());
    }

    public function testGetTableName()
    {
        $this->assertEquals('cfg_servicegroups', Servicegroup::getTableName());
    }

    public function testGetColumns()
    {
        $this->assertEquals(
            array(
                'sg_id',
                'sg_name',
                'sg_alias',
                'sg_comment',
                'sg_activate',
                'organization_id'
            ),
            Servicegroup::getColumns()
        );
    }
}
