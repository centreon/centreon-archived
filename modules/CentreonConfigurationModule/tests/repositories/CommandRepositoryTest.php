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
use CentreonConfiguration\Repository\CommandRepository;

class CommandRepositoryTest extends RepositoryTestCase
{
    protected $dataPath = '/modules/CentreonConfigurationModule/tests/data/json/';
    protected $objectName = 'command';
    protected $objectClass = '\CentreonConfiguration\Models\Command';
    protected $repository = '\CentreonConfiguration\Repository\CommandRepository';

    public function setUp()
    {
        parent::setUp();
    }

    public function testGetFormListWithEmptySearchString()
    {
        $rep = $this->repository;
        $expectedResult = array(
            array('id' => 1, 'text' => 'Test notif'),
            array('id' => 2, 'text' => 'Test check'),
            array('id' => 3, 'text' => 'Test connector')
        );
        $this->assertEquals($expectedResult, $rep::getFormList(''));
    }

    public function testGetFormListWithSearchString()
    {
        $rep = $this->repository;
        $expectedResult = array(
            array('id' => 3, 'text' => 'Test connector')
        );
        $this->assertEquals($expectedResult, $rep::getFormList('connector'));
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
                'command_name' => "My check",
                'command_line' => '$USER1$/bin/my_check -w $ARGV1$',
                'command_example' => '$USER1$/bin/my_check -w 90',
                'command_type' => 2,
                'enable_shell' => 0,
                'command_comment' => "My check command",
                'organization_id' => 1
            )
        );
        $this->tableEqualsXml(
            'cfg_commands',
            dirname(__DIR__) . '/data/command.insert.xml'
        );
    }

    public function testUpdate()
    {
        $rep = $this->repository;
        $newData = array(
            'object_id' => 2,
            'command_comment' => 'Check ping',
            'enable_shell' => 1 
        );
        $rep::update($newData);
        $this->tableEqualsXml(
            'cfg_commands',
            dirname(__DIR__) . '/data/command.update.xml'
        );
    }

    public function testDelete()
    {
        $rep = $this->repository;
        $rep::delete(array(2));
        $this->tableEqualsXml(
            'cfg_commands',
            dirname(__DIR__) . '/data/command.delete.xml'
        );
    }

    public function testDuplicate()
    {
        $rep = $this->repository;
        $rep::duplicate(
            array(1 => 1, 2 => 2)
        );
        $this->tableEqualsXml(
            'cfg_commands',
            dirname(__DIR__) . '/data/command.duplicate-2.xml'
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
