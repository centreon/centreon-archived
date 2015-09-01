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

namespace Test\CentreonConfiguration\Repository;

require_once 'modules/CentreonConfigurationModule/tests/repositories/RepositoryTestCase.php';

use \Test\CentreonConfiguration\Repository\RepositoryTestCase;
use CentreonConfiguration\Repository\TimePeriodRepository;

class TimePeriodRepositoryTest extends RepositoryTestCase
{
    protected $dataPath = '/modules/CentreonConfigurationModule/tests/data/json/';
    protected $objectName = 'timeperiod';
    protected $objectClass = '\CentreonConfiguration\Models\Timeperiod';
    protected $relationMap = array(
        'tp_include' => '\CentreonConfiguration\Models\Relation\Timeperiod\Timeperiodincluded',
        'tp_exclude' => '\CentreonConfiguration\Models\Relation\Timeperiod\Timeperiodexcluded'
    ); 
    protected $repository = '\CentreonConfiguration\Repository\TimePeriodRepository';

    public function setUp()
    {
        parent::setUp();
    }

    public function testGetFormListWithEmptySearchString()
    {
        $rep = $this->repository;
        $expectedResult = array(
            array('id' => 1, 'text' => '24x7'),
            array('id' => 2, 'text' => 'workhours')
        );
        $this->assertEquals($expectedResult, $rep::getFormList(''));
    }

    public function testGetFormListWithSearchString()
    {
        $rep = $this->repository;
        $expectedResult = array(
            array('id' => 2, 'text' => 'workhours')
        );
        $this->assertEquals($expectedResult, $rep::getFormList('work'));
    }

    public function testGetFormListWithSearchStringWithNoResult()
    {
        $rep = $this->repository;
        $expectedResult = array();
        $this->assertEquals($expectedResult, $rep::getFormList('idontexist'));
    }

    public function testCreate()
    {
        $rep = $this->repository;
        $expectedResult = array();
        $rep::create(
            array(
                'tp_name' => 'test_name',
                'tp_alias' => 'test_alias',
                'tp_monday' => '09:00-18:00',
                'tp_tuesday' => '09:00-18:00',
                'tp_wednesday' => '09:00-18:00',
                'tp_thursday' => '09:00-18:00',
                'tp_friday' => '09:00-17:00',
                'organization_id' => 1
            )
        );
        $this->tableEqualsXml(
            'cfg_timeperiods',
            dirname(__DIR__) . '/data/timeperiod.insert.xml'
        );
    }

    public function testUpdate()
    {
        $rep = $this->repository;
        $newData = array(
            'object_id' => 1,
            'tp_alias' => 'new_alias'
        );
        $rep::update($newData);
        $this->tableEqualsXml(
            'cfg_timeperiods',
            dirname(__DIR__) . '/data/timeperiod.update.xml'
        );
    }

    public function testDelete()
    {
        $rep = $this->repository;
        $rep::delete(array(1));
        $this->tableEqualsXml(
            'cfg_timeperiods',
            dirname(__DIR__) . '/data/timeperiod.delete.xml'
        );
    }

    public function testDuplicate()
    {
        $rep = $this->repository;
        $rep::duplicate(
            array(1 => 2)
        );
        $this->tableEqualsXml(
            'cfg_timeperiods',
            dirname(__DIR__) . '/data/timeperiod.duplicate-2.xml'
        );
    }

    public function testGetRelations()
    {
        $rep = $this->repository;
        $expectedResult = array();
        $this->assertEquals(
            $expectedResult,
            $rep::getRelations('\CentreonConfiguration\Models\Relation\Timeperiod\Timeperiodincluded', 1)
        );
    }

    public function testGetSimpleRelation()
    {
        $rep = $this->repository;
        $expectedResult = array(
            array('id' => 1, 'text' => 'Template host')
        );
        $this->assertEquals(
            $expectedResult,
            $rep::getSimpleRelation(
                'host_id',
                '\CentreonConfiguration\Models\Hosttemplate',
                1,
                true
            )
        );
    }
}
