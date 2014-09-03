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

namespace Test\CentreonBusinessView\Models;

use \Test\Centreon\DbTestCase;
use \CentreonBam\Models\BusinessView;

class BusinessViewTest extends DbTestCase
{
    protected $errMsg = 'Object not in database.';
    protected $dataPath = '/modules/CentreonBamModule/tests/data/json/';

    public function testInsert()
    {
        $newBusinessView = array(
            "name" => "Ba test",
            "description" => "Ba For Testing",
            "level_w" => "90",
            "level_c" => "85",
            "calculate" => "0",
            "downtime" => "0",
            "acknowledged" => "0",
            "activate" => "1",
            "organization_id" => "1"
        );
        BusinessView::insert($newBusinessView);
        $this->tableEqualsXml(
            'cfg_bam',
            dirname(__DIR__) . '/data/businessview.insert.xml'
        );
    }

    public function testInsertDuplicateKey()
    {
        $newBusinessView = array(
            'name' => 'accouting',
            'organization' => '1',
        );
        $this->setExpectedException(
            'PDOException'
        );
        BusinessView::insert($newBusinessView);
    }

    public function testDelete()
    {
        BusinessView::delete(1);
        $this->tableEqualsXml(
            'cfg_bam',
            dirname(__DIR__) . '/data/businessview.delete.xml'
        );
    }

    public function testDeleteUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        BusinessView::delete(9999);
    }

    public function testUpdate()
    {
        $newInfo = array(
            'name' => 'modified ba',
            'level_w' => 25,
            'level_c' => 10
        );
        BusinessView::update(2, $newInfo);
        $this->tableEqualsXml(
            'cfg_bam',
            dirname(__DIR__) . '/data/businessview.update.xml'
        );
    }

    public function testUpdateDuplicateKey()
    {
        $newInfo = array(
            'name' => 'accouting',
            "organization_id" => 1
        );
        $this->setExpectedException(
            'PDOException',
            '',
            23000
        );
        BusinessView::update(2, $newInfo);
    }

    public function testUpdateUnknownId()
    {
      $newInfo = array(
            'organization_id' => '5'
        );
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        BusinessView::update(9999, $newInfo);
    }

    public function testDuplicateItemOnce()
    {
        BusinessView::duplicate(1);
        $this->tableEqualsXml(
            'cfg_bam',
            dirname(__DIR__) . '/data/businessview.duplicate-1.xml'
        );
    }

    public function testDuplicateItemMultipleTimes()
    {
        BusinessView::duplicate(1, 2);
        $this->tableEqualsXml(
            'cfg_bam',
            dirname(__DIR__) . '/data/businessview.duplicate-2.xml'
        );
    }

    public function testDuplicateUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        BusinessView::duplicate(9999);

        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        BusinessView::duplicate(9999, 2);
    }

    /*public function testGetAllParameters()
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
        $arr = BusinessView::getParameters(2, '*');
        $this->assertEquals($arr, $testInformation);
    }

    public function testGetSpecificParameter()
    {
        $testInformation = array(
            'tp_name' => 'workhours',
        );
        $arr = BusinessView::getParameters(2, 'tp_name');
        $this->assertEquals($arr, $testInformation);
    }

    public function testGetSpecificParameters()
    {
        $testInformation = array(
            'tp_name' => 'workhours',
            'tp_alias' => 'workhours',
            'tp_monday' => '00:00-24:00'
        );
        $arr = BusinessView::getParameters(2, array('tp_name', 'tp_alias', 'tp_monday'));
        $this->assertEquals($arr, $testInformation);
    }

    public function testGetParametersFromUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        BusinessView::getParameters(9999, '*');
        
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        BusinessView::getParameters(9999, 'tp_name');
        
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        BusinessView::getParameters(9999, array('tp_name', 'tp_alias'));
    }

    public function testGetUnknownParameters()
    {
       $this->setExpectedException(
           'PDOException'
       );
       BusinessView::getParameters(2, 'idontexist');

       $this->setExpectedException(
           'PDOException'
       );
       BusinessView::getParameters(2, array('tp_name', 'idontexist'));
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
        $this->assertEquals($expectedResult, BusinessView::getList());
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
        $this->assertEquals($expectedResult, BusinessView::getList('*', 1));
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
        $this->assertEquals($expectedResult, BusinessView::getList('*', 1, 1));

    }

    public function testGetListOneParameter()
    {
        $expectedResult = array(
            array('tp_name' => '24x7'),
            array('tp_name' => 'workhours')
        );
        $this->assertEquals($expectedResult, BusinessView::getList('tp_name'));
    }

    public function testGetListMultipleParameters()
    {
        $expectedResult = array(
            array('tp_id' => 1, 'tp_name' => '24x7'),
            array('tp_id' => 2, 'tp_name' => 'workhours')
        );
        $this->assertEquals($expectedResult, BusinessView::getList(array('tp_id', 'tp_name')));
    }

    public function testGetListWithOrder()
    {
        $expectedResult = array(
            array('tp_name' => 'workhours'),
            array('tp_name' => '24x7')
        );
        $this->assertEquals($expectedResult, BusinessView::getList('tp_name', null, null, 'tp_id', 'DESC'));
    }

    public function testGetListWithOneFilter()
    {
        $expectedResult = array(
            array('tp_name' => 'workhours')
        );
        $this->assertEquals(
            $expectedResult, 
            BusinessView::getList(
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
            BusinessView::getList(
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
            BusinessView::getList(
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
            BusinessView::getListBySearch(
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
        $this->assertEquals($expectedResult, BusinessView::get(2));
    }*/

    public function testGetWithOneParameter()
    {
        $expectedResult = array(
            'name' => 'accounting'
        );
        $this->assertEquals($expectedResult, BusinessView::get(1, 'name'));
    }

    public function testGetWithMultipleParameters()
    {
        $expectedResult = array(
            'name' => 'accounting',
            'level_w' => 80
        );
        $this->assertEquals($expectedResult, BusinessView::get(1, array('name', 'level_w')));
    }

    public function testGetWithUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        BusinessView::get(9999);
    }

    public function testGetIdByParameter()
    {
        $expectedResult = array(1);
        $this->assertEquals($expectedResult, BusinessView::getIdByParameter('name', 'accounting'));
    }

    public function testGetMultipleIdsByParameters()
    {
        $expectedResult = array(1, 2);
        $this->assertEquals($expectedResult, BusinessView::getIdByParameter('name', array('accounting', 'hotline')));
    }

    public function testGetIdByParameterWithUnknownColumn()
    {
        $this->setExpectedException(
            'PDOException'
        );
        BusinessView::getIdByParameter('idontexist', array('hotline'));
    }

    public function testGetPrimaryKey()
    {
        $this->assertEquals('ba_id', BusinessView::getPrimaryKey());
    }

    public function testGetUniqueLabelField()
    {
        $this->assertEquals('name', BusinessView::getUniqueLabelField());
    }

    public function testGetTableName()
    {
        $this->assertEquals('cfg_bam', BusinessView::getTableName());
    }

    public function testGetColumns()
    {
        $this->assertEquals(
            array(
                "ba_id",
                "name",
                "description",
                "level_w",
                "level_c",
                "sla_type",
                "sla_warning",
                "sla_critical",
                "id_notification_period",
                "id_check_period",
                "id_reporting_period",
                "notification_interval",
                "notification_options",
                "notifications_enabled",
                "max_check_attempts",
                "normal_check_interval",
                "retry_check_interval",
                "current_level",
                "calculate",
                "downtime",
                "acknowledged",
                "dependency_dep_id",
                "icon_id",
                "graph_style",
                "activate",
                "comment",
                "organization_id"
            ),
            BusinessView::getColumns()
        );
    }
}
