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

require_once CENTREON_PATH . "/tests/DbTestCase.php";

use \Test\Centreon\DbTestCase;
use CentreonConfiguration\Models\Trap;

class TrapTest extends DbTestCase
{
    protected $errMsg = 'Object not in database.';
    protected $dataPath = '/modules/CentreonConfigurationModule/tests/data/json/';

    public function testInsert()
    {
        $newTrap = array(
            "traps_name" => "test",
            "traps_oid" => ".0.0.0.0.0.0.0.0.0.1",
            "traps_args" => "test $1",
            "traps_status" => "1",
            "manufacturer_id" => "1",
            "traps_comments" => "Test for traps",
            "organization_id" => 1
        );
        
        Trap::insert($newTrap);
        /* Assert for test insert in DB */
        $dataset = $this->createXmlDataSet(
            dirname(__DIR__) . '/data/traps.insert.xml'
        )->getTable('cfg_traps');
        $tableResult = $this->getConnection()->createQueryTable(
            'cfg_traps',
            'SELECT * FROM cfg_traps'
        );
        $this->assertTablesEqual($dataset, $tableResult);

        /* Test exception unique */
        $this->setExpectedException(
            'PDOException',
            ''
        );
        Trap::insert($newTrap);
    }
    
    public function testInsertManufacturerNotExist()
    {
        $newTrap = array(
            "traps_name" => "test",
            "traps_oid" => ".0.0.0.0.0.0.0.0.0.1",
            "traps_args" => "test $1",
            "traps_status" => "1",
            "manufacturer_id" => "24",
            "traps_comments" => "Test for traps",
            'organization_id' => 1
        );
        
        /* Test exception unique */
        $this->setExpectedException(
            'PDOException',
            ''
        );
        Trap::insert($newTrap);
    }
    
    public function testDelete()
    {
        Trap::delete(3);
        /* Assert for test delete in DB */
        $dataset = $this->createXmlDataSet(
            dirname(__DIR__) . '/data/traps.delete.xml'
        )->getTable('cfg_traps');
        $tableResult = $this->getConnection()->createQueryTable(
            'cfg_traps',
            'SELECT * FROM cfg_traps'
        );
        $this->assertTablesEqual($dataset, $tableResult);
        
        /* Test exception object doesn't exist */
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg,
            0
        );
        Trap::delete(5);
    }
    
    public function testUpdate()
    {
        $updatedTrap = array(
            "traps_name" => "test update",
            "traps_oid" => ".1.0.0.0.0.0.0.0.0.1",
            "traps_args" => "test $3",
            "traps_status" => "0",
            "manufacturer_id" => "1",
            "traps_comments" => "Test for traps",
            'organization_id' => 1
        );
        
        Trap::update(1, $updatedTrap);
        /* Assert for test update in DB */
        $dataset = $this->createXmlDataSet(
            dirname(__DIR__) . '/data/traps.update.xml'
        )->getTable('cfg_traps');
        $tableResult = $this->getConnection()->createQueryTable(
            'cfg_traps',
            'SELECT * FROM cfg_traps'
        );
        $this->assertTablesEqual($dataset, $tableResult);
    }
    
    public function testUpdateManufaturerNotExist()
    {
        $updatedTrap = array(
            "manufacturer_id" => "25"
        );
        
        /* Test exception unique */
        $this->setExpectedException(
            'PDOException',
            ''
        );
        Trap::update(2, $updatedTrap);
    }
    
    public function testUpdateNotUnique()
    {
        $updatedTrap = array(
            "traps_name" => "warmStart"
        );
        
        /* Test exception object doesn't exist */
        $this->setExpectedException(
            'PDOException',
            ""
        );
        Trap::update(1, $updatedTrap);
    }
    
    public function testUpdateNotFound()
    {
        $updatedTrap = array(
            "traps_name" => "test_error"
        );
        
        /* Test exception object doesn't exist */
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg,
            0
        );
        Trap::update(8, $updatedTrap);
    }
    
    public function testDuplicate()
    {
        Trap::duplicate(1);
        /* Assert for test update in DB */
        $dataset = $this->createXmlDataSet(
            dirname(__DIR__) . '/data/traps.duplicate-1.xml'
        )->getTable('cfg_traps');
        $tableResult = $this->getConnection()->createQueryTable(
            'cfg_traps',
            'SELECT * FROM cfg_traps'
        );
        $this->assertTablesEqual($dataset, $tableResult);
        
        Trap::duplicate(2, 2);
        /* Assert for test update in DB */
        $dataset = $this->createXmlDataSet(
            dirname(__DIR__) . '/data/traps.duplicate-2.xml'
        )->getTable('cfg_traps');
        $tableResult = $this->getConnection()->createQueryTable(
            'cfg_traps',
            'SELECT * FROM cfg_traps'
        );
        $this->assertTablesEqual($dataset, $tableResult);
        
    }
    
    public function testDuplicateNotFound()
    {
         /* Test exception object doesn't exist */
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg,
            0
        );
        Trap::duplicate(8);
    }
    
    public function testGetParametersNotFound()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg,
            0
        );
        $trap = Trap::getParameters(21, '*');
    }
    
    public function testGetParametersBadColumns()
    {
        $this->setExpectedException(
            'PDOException',
            ''
        );
        Trap::getParameters(1, 'test_error');
    }

    public function testGetPrimaryKey()
    {
        $this->assertEquals('traps_id', Trap::getPrimaryKey());
    }

    public function testGetUniqueLabelField()
    {
        $this->assertEquals('traps_name', Trap::getUniqueLabelField());
    }
    
    public function testIsUnique()
    {
        $this->assertTrue(Trap::isUnique('linkUp', 2));
        $this->assertFalse(Trap::isUnique('linkUp', 1));
        $this->assertFalse(Trap::isUnique('linkUp'));
    }
    
    public function testGetTableName()
    {
        $this->assertEquals('cfg_traps', Trap::getTableName());
    }

    public function testGetColumns()
    {
        $this->assertEquals(
            array(
                "traps_id",
                "traps_name",
                "traps_oid",
                "traps_args",
                "traps_status",
                "severity_id",
                "manufacturer_id",
                "traps_reschedule_svc_enable",
                "traps_execution_command",
                "traps_execution_command_enable",
                "traps_submit_result_enable",
                "traps_advanced_treatment",
                "traps_advanced_treatment_default",
                "traps_timeout",
                "traps_exec_interval",
                "traps_exec_interval_type",
                "traps_log",
                "traps_routing_mode",
                "traps_routing_value",
                "traps_exec_method",
                "traps_comments",
                "organization_id"
            ),
            Trap::getColumns()
        );
    }
}
