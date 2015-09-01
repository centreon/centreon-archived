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
use CentreonConfiguration\Repository\ConnectorRepository;

class ConnectorRepositoryTest extends RepositoryTestCase
{
    protected $dataPath = '/modules/CentreonConfigurationModule/tests/data/json/';
    protected $objectName = 'connector';
    protected $objectClass = '\CentreonConfiguration\Models\Connector';
    protected $repository = '\CentreonConfiguration\Repository\ConnectorRepository';

    public function setUp()
    {
        parent::setUp();
    }

    public function testGetFormListWithEmptySearchString()
    {
        $rep = $this->repository;
        $expectedResult = array(
            array('id' => 1, 'text' => 'Perl'),
            array('id' => 2, 'text' => 'SSH')
        );
        $this->assertEquals($expectedResult, $rep::getFormList(''));
    }

    public function testGetFormListWithSearchString()
    {
        $rep = $this->repository;
        $expectedResult = array(
            array('id' => 1, 'text' => 'Perl')
        );
        $this->assertEquals($expectedResult, $rep::getFormList('er'));
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
                'name' => 'TestConnector',
                'description' => 'My connector test',
                'command_line' => '$CONNECTORS$/my_connector',
                'enabled' => 1,
                'created' => 1407836372,
                'modified' => 1407836372,
                'organization_id' => 1
            )
        );
        $this->tableEqualsXml(
            'cfg_connectors',
            dirname(__DIR__) . '/data/connector.insert.xml',
            true
        );
    }

    public function testUpdate()
    {
        $rep = $this->repository;
        $newData = array(
            'object_id' => 2,
            'description' => 'Connector for SSH',
            'enabled' => 0
        );
        $rep::update($newData);
        $this->tableEqualsXml(
            'cfg_connectors',
            dirname(__DIR__) . '/data/connector.update.xml',
            true
        );
    }

    public function testDelete()
    {
        $rep = $this->repository;
        $rep::delete(array(2));
        $this->tableEqualsXml(
            'cfg_connectors',
            dirname(__DIR__) . '/data/connector.delete.xml',
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
            'cfg_connectors',
            dirname(__DIR__) . '/data/connector.duplicate-2.xml',
            true
        );
    }

    public function testGetSimpleRelationReverse()
    {
        $rep = $this->repository;
        $expectedResult = array(
            array('id' => 3, 'text' => 'Test connector')
        );
        $this->assertEquals(
            $expectedResult,
            $rep::getSimpleRelation(
                'connector_id',
                '\CentreonConfiguration\Models\Command',
                1,
                true
            )
        );
    }
}
