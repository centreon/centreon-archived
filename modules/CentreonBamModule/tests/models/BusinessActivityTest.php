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
 * this program; if not, see <http://www.gnu.org/licenses>.
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

namespace Test\CentreonBusinessActivity\Models;

use \Test\Centreon\DbTestCase;
use \CentreonConfiguration\Models\BusinessActivity;

class BusinessActivityTest extends DbTestCase
{
    protected $errMsg = 'Object not in database.';
    protected $dataPath = '/modules/CentreonBamModule/tests/data/json/';

    public function testInsert()
    {
        $tpInsert = array(

        );
        BusinessActivity::insert($tpInsert);
        $this->tableEqualsXml(
            'cfg_bam',
            dirname(__DIR__) . '/data/businessactivity.insert.xml'
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
        );
        BusinessActivity::insert($tpInsert);
        $this->setExpectedException(
            'PDOException'
        );
        BusinessActivity::insert($tpInsert);
    }

    public function testDelete()
    {
        BusinessActivity::delete(1);
        $this->tableEqualsXml(
            'cfg_bam',
            dirname(__DIR__) . '/data/businessactivity.delete.xml'
        );
    }

    public function testDeleteUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        BusinessActivity::delete(9999);
    }

    public function testUpdate()
    {
        $newInfo = array(
            'tp_alias' => 'new_alias'
        );
        BusinessActivity::update(1, $newInfo);
        $this->tableEqualsXml(
            'cfg_bam',
            dirname(__DIR__) . '/data/businessactivity.update.xml'
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
        BusinessActivity::update(2, $newInfo);
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
        BusinessActivity::update(9999, $newInfo);
    }

    public function testDuplicateItemOnce()
    {
        BusinessActivity::duplicate(1);
        $this->tableEqualsXml(
            'cfg_bam',
            dirname(__DIR__) . '/data/businessactivity.duplicate-1.xml'
        );
    }

    public function testDuplicateItemMultipleTimes()
    {
        BusinessActivity::duplicate(1, 2);
        $this->tableEqualsXml(
            'cfg_bam',
            dirname(__DIR__) . '/data/businessactivity.duplicate-2.xml'
        );
    }

    public function testDuplicateUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        BusinessActivity::duplicate(9999);

        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        BusinessActivity::duplicate(9999, 2);
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
            'tp_saturday' => null
        );
        $arr = BusinessActivity::getParameters(2, '*');
        $this->assertEquals($arr, $testInformation);
    }

    public function testGetSpecificParameter()
    {
        $testInformation = array(
            'tp_name' => 'workhours',
        );
        $arr = BusinessActivity::getParameters(2, 'tp_name');
        $this->assertEquals($arr, $testInformation);
    }

    public function testGetSpecificParameters()
    {
        $testInformation = array(
            'tp_name' => 'workhours',
            'tp_alias' => 'workhours',
            'tp_monday' => '00:00-24:00'
        );
        $arr = BusinessActivity::getParameters(2, array('tp_name', 'tp_alias', 'tp_monday'));
        $this->assertEquals($arr, $testInformation);
    }

    public function testGetParametersFromUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        BusinessActivity::getParameters(9999, '*');
        
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        BusinessActivity::getParameters(9999, 'tp_name');
        
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        BusinessActivity::getParameters(9999, array('tp_name', 'tp_alias'));
    }

    public function testGetUnknownParameters()
    {
       $this->setExpectedException(
           'PDOException'
       );
       BusinessActivity::getParameters(2, 'idontexist');

       $this->setExpectedException(
           'PDOException'
       );
       BusinessActivity::getParameters(2, array('tp_name', 'idontexist'));
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
                'tp_saturday' => '00:00-24:00'
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
                'tp_saturday' => null
            )
        );
        $this->assertEquals($expectedResult, BusinessActivity::getList());
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
                'tp_saturday' => '00:00-24:00'
            )
        );
        $this->assertEquals($expectedResult, BusinessActivity::getList('*', 1));
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
                'tp_saturday' => null
            )
        );
        $this->assertEquals($expectedResult, BusinessActivity::getList('*', 1, 1));

    }

    public function testGetListOneParameter()
    {
        $expectedResult = array(
            array('tp_name' => '24x7'),
            array('tp_name' => 'workhours')
        );
        $this->assertEquals($expectedResult, BusinessActivity::getList('tp_name'));
    }

    public function testGetListMultipleParameters()
    {
        $expectedResult = array(
            array('tp_id' => 1, 'tp_name' => '24x7'),
            array('tp_id' => 2, 'tp_name' => 'workhours')
        );
        $this->assertEquals($expectedResult, BusinessActivity::getList(array('tp_id', 'tp_name')));
    }

    public function testGetListWithOrder()
    {
        $expectedResult = array(
            array('tp_name' => 'workhours'),
            array('tp_name' => '24x7')
        );
        $this->assertEquals($expectedResult, BusinessActivity::getList('tp_name', null, null, 'tp_id', 'DESC'));
    }

    public function testGetListWithOneFilter()
    {
        $expectedResult = array(
            array('tp_name' => 'workhours')
        );
        $this->assertEquals(
            $expectedResult, 
            BusinessActivity::getList(
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
            BusinessActivity::getList(
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
            BusinessActivity::getList(
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
            BusinessActivity::getListBySearch(
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
            'tp_saturday' => null
        );
        $this->assertEquals($expectedResult, BusinessActivity::get(2));
    }

    public function testGetWithOneParameter()
    {
        $expectedResult = array(
            'tp_name' => '24x7'
        );
        $this->assertEquals($expectedResult, BusinessActivity::get(1, 'tp_name'));
    }

    public function testGetWithMultipleParameters()
    {
        $expectedResult = array(
            'tp_name' => '24x7',
            'tp_sunday' => '00:00-24:00'
        );
        $this->assertEquals($expectedResult, BusinessActivity::get(1, array('tp_name', 'tp_sunday')));
    }

    public function testGetWithUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        BusinessActivity::get(9999);
    }

    public function testGetIdByParameter()
    {
        $expectedResult = array(2);
        $this->assertEquals($expectedResult, BusinessActivity::getIdByParameter('tp_name', 'workhours'));
    }

    public function testGetMultipleIdsByParameters()
    {
        $expectedResult = array(1, 2);
        $this->assertEquals($expectedResult, BusinessActivity::getIdByParameter('tp_name', array('workhours', '24x7')));
    }

    public function testGetIdByParameterWithUnknownColumn()
    {
        $this->setExpectedException(
            'PDOException'
        );
        BusinessActivity::getIdByParameter('idontexist', array('workhours'));
    }

    public function testGetPrimaryKey()
    {
        $this->assertEquals('tp_id', BusinessActivity::getPrimaryKey());
    }

    public function testGetUniqueLabelField()
    {
        $this->assertEquals('tp_name', BusinessActivity::getUniqueLabelField());
    }

    public function testGetTableName()
    {
        $this->assertEquals('cfg_bam', BusinessActivity::getTableName());
    }

    public function testGetColumns()
    {
        $this->assertEquals(
            array(
            ),
            BusinessActivity::getColumns()
        );
    }
}
