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
use CentreonConfiguration\Models\Timeperiod;

class TimeperiodTest extends DbTestCase
{
    protected $errMsg = 'Object not in database.';
    protected $dataPath = '/modules/CentreonConfigurationModule/tests/data/json/';

    public function testInsert()
    {
        $tpInsert = array(
            'tp_name' => 'test_name',
            'tp_alias' => 'test_alias',
            'tp_monday' => '09:00-18:00',
            'tp_tuesday' => '09:00-18:00',
            'tp_wednesday' => '09:00-18:00',
            'tp_thursday' => '09:00-18:00',
            'tp_friday' => '09:00-17:00',
            'organization_id' => 1
        );
        Timeperiod::insert($tpInsert);
        $this->tableEqualsXml(
            'cfg_timeperiods',
            dirname(__DIR__) . '/data/timeperiod.insert.xml'
        );
    }

    public function testInsertDuplicateKey()
    {
        $tpInsert = array(
            'tp_name' => 'test_name',
            'tp_alias' => 'test_alias',
            'tp_monday' => '09:00-18:00',
            'tp_tuesday' => '09:00-18:00',
            'tp_wednesday' => '09:00-18:00',
            'tp_thursday' => '09:00-18:00',
            'tp_friday' => '09:00-17:00',
            'organization_id' => 1
        );
        Timeperiod::insert($tpInsert);
        $this->setExpectedException(
            'PDOException'
        );
        Timeperiod::insert($tpInsert);
    }

    public function testDelete()
    {
        Timeperiod::delete(1);
        $this->tableEqualsXml(
            'cfg_timeperiods',
            dirname(__DIR__) . '/data/timeperiod.delete.xml'
        );
    }

    public function testDeleteUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Timeperiod::delete(9999);
    }

    public function testUpdate()
    {
        $newInfo = array(
            'tp_alias' => 'new_alias'
        );
        Timeperiod::update(1, $newInfo);
        $this->tableEqualsXml(
            'cfg_timeperiods',
            dirname(__DIR__) . '/data/timeperiod.update.xml'
        );
    }

    public function testUpdateDuplicateKey()
    {
        $newInfo = array(
            'tp_name' => '24x7'
        );
        $this->setExpectedException(
            'PDOException',
            '',
            23000
        );
        Timeperiod::update(2, $newInfo);
    }

    public function testUpdateUnknownId()
    {
      $newInfo = array(
            'tp_name' => '24x7'
        );
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Timeperiod::update(9999, $newInfo);
    }

    public function testDuplicateItemOnce()
    {
        Timeperiod::duplicate(1);
        $this->tableEqualsXml(
            'cfg_timeperiods',
            dirname(__DIR__) . '/data/timeperiod.duplicate-1.xml'
        );
    }

    public function testDuplicateItemMultipleTimes()
    {
        Timeperiod::duplicate(1, 2);
        $this->tableEqualsXml(
            'cfg_timeperiods',
            dirname(__DIR__) . '/data/timeperiod.duplicate-2.xml'
        );
    }

    public function testDuplicateUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Timeperiod::duplicate(9999);

        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Timeperiod::duplicate(9999, 2);
    }

    public function testGetAllParameters()
    {
        $testInformation = array(
            'tp_id' => 2,
            'tp_name' => 'workhours',
            'tp_alias' => 'workhours',
            'tp_sunday' => null,
            'tp_monday' => '00:00-24:00',
            'tp_tuesday' => '00:00-24:00',
            'tp_wednesday' => '00:00-24:00',
            'tp_thursday' => '00:00-24:00',
            'tp_friday' => '00:00-24:00',
            'tp_saturday' => null,
            'organization_id' => 1
        );
        $arr = Timeperiod::getParameters(2, '*');
        $this->assertEquals($arr, $testInformation);
    }

    public function testGetSpecificParameter()
    {
        $testInformation = array(
            'tp_name' => 'workhours',
        );
        $arr = Timeperiod::getParameters(2, 'tp_name');
        $this->assertEquals($arr, $testInformation);
    }

    public function testGetSpecificParameters()
    {
        $testInformation = array(
            'tp_name' => 'workhours',
            'tp_alias' => 'workhours',
            'tp_monday' => '00:00-24:00'
        );
        $arr = Timeperiod::getParameters(2, array('tp_name', 'tp_alias', 'tp_monday'));
        $this->assertEquals($arr, $testInformation);
    }

    public function testGetParametersFromUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Timeperiod::getParameters(9999, '*');
        
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Timeperiod::getParameters(9999, 'tp_name');
        
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Timeperiod::getParameters(9999, array('tp_name', 'tp_alias'));
    }

    public function testGetUnknownParameters()
    {
       $this->setExpectedException(
           'PDOException'
       );
       Timeperiod::getParameters(2, 'idontexist');

       $this->setExpectedException(
           'PDOException'
       );
       Timeperiod::getParameters(2, array('tp_name', 'idontexist'));
    }

    public function testGetList()
    {
        $expectedResult = array(
            array(
                'tp_id' => 1,
                'tp_name' => '24x7',
                'tp_alias' => '24_Hours_A_Day,_7_Days_A_Week',
                'tp_sunday' => '00:00-24:00',
                'tp_monday' => '00:00-24:00',
                'tp_tuesday' => '00:00-24:00',
                'tp_wednesday' => '00:00-24:00',
                'tp_thursday' => '00:00-24:00',
                'tp_friday' => '00:00-24:00',
                'tp_saturday' => '00:00-24:00',
                'organization_id' => 1
            ),
            array(
                'tp_id' => 2,
                'tp_name' => 'workhours',
                'tp_alias' => 'workhours',
                'tp_sunday' => null,
                'tp_monday' => '00:00-24:00',
                'tp_tuesday' => '00:00-24:00',
                'tp_wednesday' => '00:00-24:00',
                'tp_thursday' => '00:00-24:00',
                'tp_friday' => '00:00-24:00',
                'tp_saturday' => null,
                'organization_id' => 1
            )
        );
        $this->assertEquals($expectedResult, Timeperiod::getList());
    }

    public function testGetListLimitOne()
    {
        $expectedResult = array(
            array(
                'tp_id' => 1,
                'tp_name' => '24x7',
                'tp_alias' => '24_Hours_A_Day,_7_Days_A_Week',
                'tp_sunday' => '00:00-24:00',
                'tp_monday' => '00:00-24:00',
                'tp_tuesday' => '00:00-24:00',
                'tp_wednesday' => '00:00-24:00',
                'tp_thursday' => '00:00-24:00',
                'tp_friday' => '00:00-24:00',
                'tp_saturday' => '00:00-24:00',
                'organization_id' => 1
            )
        );
        $this->assertEquals($expectedResult, Timeperiod::getList('*', 1));
    }

    public function testGetListSecondElementOnly()
    {
        $expectedResult = array(
            array(
                'tp_id' => 2,
                'tp_name' => 'workhours',
                'tp_alias' => 'workhours',
                'tp_sunday' => null,
                'tp_monday' => '00:00-24:00',
                'tp_tuesday' => '00:00-24:00',
                'tp_wednesday' => '00:00-24:00',
                'tp_thursday' => '00:00-24:00',
                'tp_friday' => '00:00-24:00',
                'tp_saturday' => null,
                'organization_id' => 1
            )
        );
        $this->assertEquals($expectedResult, Timeperiod::getList('*', 1, 1));

    }

    public function testGetListOneParameter()
    {
        $expectedResult = array(
            array('tp_name' => '24x7'),
            array('tp_name' => 'workhours')
        );
        $this->assertEquals($expectedResult, Timeperiod::getList('tp_name'));
    }

    public function testGetListMultipleParameters()
    {
        $expectedResult = array(
            array('tp_id' => 1, 'tp_name' => '24x7'),
            array('tp_id' => 2, 'tp_name' => 'workhours')
        );
        $this->assertEquals($expectedResult, Timeperiod::getList(array('tp_id', 'tp_name')));
    }

    public function testGetListWithOrder()
    {
        $expectedResult = array(
            array('tp_name' => 'workhours'),
            array('tp_name' => '24x7')
        );
        $this->assertEquals($expectedResult, Timeperiod::getList('tp_name', null, null, 'tp_id', 'DESC'));
    }

    public function testGetListWithOneFilter()
    {
        $expectedResult = array(
            array('tp_name' => 'workhours')
        );
        $this->assertEquals(
            $expectedResult, 
            Timeperiod::getList(
                'tp_name', 
                null, 
                null, 
                null, 
                null, 
                array(
                    'tp_name' => 'workhours'
                )
            )
        );
    }

    public function testGetListWithMultipleFilters()
    {
        $expectedResult = array(
            array('tp_name' => 'workhours')
        );
        $this->assertEquals(
            $expectedResult, 
            Timeperiod::getList(
                'tp_name', 
                null, 
                null, 
                null, 
                null, 
                array(
                    'tp_name' => 'workhours',
                    'tp_id' => 2
                )
            )
        );
    }

    public function testGetListWithFilterNoResult()
    {
        $expectedResult = array();
        $this->assertEquals(
            $expectedResult, 
            Timeperiod::getList(
                'tp_name', 
                null, 
                null, 
                null, 
                null, 
                array(
                    'tp_name' => 'idontexist',
                )
            )

        );
    }

    public function testGetListBySearch()
    {
        $expectedResult = array(
            array('tp_name' => 'workhours')
        );
        $this->assertEquals(
            $expectedResult, 
            Timeperiod::getListBySearch(
                'tp_name', 
                null, 
                null, 
                null, 
                null,
                array('tp_name' => 'hours')
            )
        );
    }

    public function testGet()
    {
        $expectedResult = array(
            'tp_id' => 2,
            'tp_name' => 'workhours',
            'tp_alias' => 'workhours',
            'tp_sunday' => null,
            'tp_monday' => '00:00-24:00',
            'tp_tuesday' => '00:00-24:00',
            'tp_wednesday' => '00:00-24:00',
            'tp_thursday' => '00:00-24:00',
            'tp_friday' => '00:00-24:00',
            'tp_saturday' => null,
            'organization_id' => 1
        );
        $this->assertEquals($expectedResult, Timeperiod::get(2));
    }

    public function testGetWithOneParameter()
    {
        $expectedResult = array(
            'tp_name' => '24x7'
        );
        $this->assertEquals($expectedResult, Timeperiod::get(1, 'tp_name'));
    }

    public function testGetWithMultipleParameters()
    {
        $expectedResult = array(
            'tp_name' => '24x7',
            'tp_sunday' => '00:00-24:00'
        );
        $this->assertEquals($expectedResult, Timeperiod::get(1, array('tp_name', 'tp_sunday')));
    }

    public function testGetWithUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Timeperiod::get(9999);
    }

    public function testGetIdByParameter()
    {
        $expectedResult = array(2);
        $this->assertEquals($expectedResult, Timeperiod::getIdByParameter('tp_name', 'workhours'));
    }

    public function testGetMultipleIdsByParameters()
    {
        $expectedResult = array(1, 2);
        $this->assertEquals($expectedResult, Timeperiod::getIdByParameter('tp_name', array('workhours', '24x7')));
    }

    public function testGetIdByParameterWithUnknownColumn()
    {
        $this->setExpectedException(
            'PDOException'
        );
        Timeperiod::getIdByParameter('idontexist', array('workhours'));
    }

    public function testGetPrimaryKey()
    {
        $this->assertEquals('tp_id', Timeperiod::getPrimaryKey());
    }

    public function testGetUniqueLabelField()
    {
        $this->assertEquals('tp_name', Timeperiod::getUniqueLabelField());
    }

    public function testGetTableName()
    {
        $this->assertEquals('cfg_timeperiods', Timeperiod::getTableName());
    }

    public function testGetColumns()
    {
        $this->assertEquals(
            array(
                'tp_id',
                'tp_name',
                'tp_alias',
                'tp_sunday',
                'tp_monday',
                'tp_tuesday',
                'tp_wednesday',
                'tp_thursday',
                'tp_friday',
                'tp_saturday',
                'organization_id'
            ),
            Timeperiod::getColumns()
        );
    }
}
