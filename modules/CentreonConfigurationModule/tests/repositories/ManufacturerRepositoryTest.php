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
use CentreonConfiguration\Repository\ManufacturerRepository;

class ManufacturerRepositoryTest extends RepositoryTestCase
{
    protected $dataPath = '/modules/CentreonConfigurationModule/tests/data/json/';
    protected $objectName = 'manufacturer';
    protected $objectClass = '\CentreonConfiguration\Models\Manufacturer';
    protected $repository = '\CentreonConfiguration\Repository\ManufacturerRepository';

    public function setUp()
    {
        parent::setUp();
    }

    public function testGetFormListWithEmptySearchString()
    {
        $rep = $this->repository;
        $expectedResult = array(
            array('id' => 1, 'text' => 'Generic'),
            array('id' => 2, 'text' => 'Cisco'),
            array('id' => 3, 'text' => 'HP'),
            array('id' => 4, 'text' => '3com'),
            array('id' => 5, 'text' => 'Linksys')
        );
        $this->assertEquals($expectedResult, $rep::getFormList(''));
    }

    public function testGetFormListWithSearchString()
    {
        $rep = $this->repository;
        $expectedResult = array(
            array('id' => 5, 'text' => 'Linksys')
        );
        $this->assertEquals($expectedResult, $rep::getFormList('Link'));
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
               "name"  => "Test Manufacturer",
               "alias" => "Test Manufacturer",
               "description" => "Test for traps manufacturer",
               "organization_id" => 1
            )
        );
        $this->tableEqualsXml(
            'cfg_traps_vendors',
            dirname(__DIR__) . '/data/traps_vendor.insert.xml',
            true
        );
    }

    public function testUpdate()
    {
        $rep = $this->repository;
        $newData = array(
            "object_id" => 1,
            "name" => "Centreon Generic Traps",
            "alias" => "Centreon Generic Traps",
            "description" => "References Generic Traps for Centreon"
        );
        $rep::update($newData);
        $this->tableEqualsXml(
            'cfg_traps_vendors',
            dirname(__DIR__) . '/data/traps_vendor.update.xml',
            true
        );
    }

    public function testDelete()
    {
        $rep = $this->repository;
        $rep::delete(array(4));
        $this->tableEqualsXml(
            'cfg_traps_vendors',
            dirname(__DIR__) . '/data/traps_vendor.delete.xml',
            true
        );
    }

    public function testDuplicate()
    {
        $rep = $this->repository;
        $rep::duplicate(
            array(1 => 1, 2 => 2)
        );
        $this->tableEqualsXml(
            'cfg_traps_vendors',
            dirname(__DIR__) . '/data/traps_vendor.duplicate-2.xml',
            true
        );
    }

    public function testGetSimpleRelationReverse()
    {
        $rep = $this->repository;
        $expectedResult = array(
            array('id' => 1, 'text' => 'linkDown'),
            array('id' => 2, 'text' => 'linkUp'),
            array('id' => 3, 'text' => 'warmStart'),
            array('id' => 4, 'text' => 'coldStart')
        );
        $this->assertEquals(
            $expectedResult,
            $rep::getSimpleRelation(
                'manufacturer_id',
                '\CentreonConfiguration\Models\Trap',
                1,
                true
            )
        );
    }
}
