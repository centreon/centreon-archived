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
use CentreonConfiguration\Repository\TrapRepository;

class TrapRepositoryTest extends RepositoryTestCase
{
    protected $dataPath = '/modules/CentreonConfigurationModule/tests/data/json/';
    protected $objectName = 'trap';
    protected $objectClass = '\CentreonConfiguration\Models\Trap';
    protected $repository = '\CentreonConfiguration\Repository\TrapRepository';

    public function setUp()
    {
        parent::setUp();
    }

    public function testGetFormListWithEmptySearchString()
    {
        $rep = $this->repository;
        $expectedResult = array(
            array('id' => 1, 'text' => 'linkDown'),
            array('id' => 2, 'text' => 'linkUp'),
            array('id' => 3, 'text' => 'warmStart'),
            array('id' => 4, 'text' => 'coldStart')
        );
        $this->assertEquals($expectedResult, $rep::getFormList(''));
    }

    public function testGetFormListWithSearchString()
    {
        $rep = $this->repository;
        $expectedResult = array(
            array('id' => 3, 'text' => 'warmStart')
        );
        $this->assertEquals($expectedResult, $rep::getFormList('warm'));
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
                "traps_name" => "test",
                "traps_oid" => ".0.0.0.0.0.0.0.0.0.1",
                "traps_args" => "test $1",
                "traps_status" => "1",
                "manufacturer_id" => "1",
                "traps_comments" => "Test for traps",
                'organization_id' => 1
            )
        );
        $this->tableEqualsXml(
            'cfg_traps',
            dirname(__DIR__) . '/data/traps.insert.xml'
        );
    }

    public function testUpdate()
    {
        $rep = $this->repository;
        $newData = array(
            "object_id" => 1,
            "traps_name" => "test update",
            "traps_oid" => ".1.0.0.0.0.0.0.0.0.1",
            "traps_args" => "test $3",
            "traps_status" => "0",
            "manufacturer_id" => "1",
            "traps_comments" => "Test for traps"
        );
        $rep::update($newData);
        $this->tableEqualsXml(
            'cfg_traps',
            dirname(__DIR__) . '/data/traps.update.xml'
        );
    }

    public function testDelete()
    {
        $rep = $this->repository;
        $rep::delete(array(3));
        $this->tableEqualsXml(
            'cfg_traps',
            dirname(__DIR__) . '/data/traps.delete.xml'
        );
    }

    public function testDuplicate()
    {
        $rep = $this->repository;
        $rep::duplicate(
            array(1 => 1, 2 => 2)
        );
        $this->tableEqualsXml(
            'cfg_traps',
            dirname(__DIR__) . '/data/traps.duplicate-2.xml'
        );
    }
}
