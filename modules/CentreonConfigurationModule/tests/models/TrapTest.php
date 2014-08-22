<?php
/*
 * Copyright 2005-2014 MERETHIS
 * Centreon is developped by => Julien Mathis and Romain Le Merlus under
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
 * this program; if not, see <http=>//www.gnu.org/licenses>.
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
 * For more information => contact@centreon.com
 * 
 */
namespace Test\CentreonConfiguration\Models;

require_once CENTREON_PATH . "/tests/DbTestCase.php";

use \Test\Centreon\DbTestCase;
use \CentreonConfiguration\Models\Trap;

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
            "traps_comments" => "Test for traps"
        );
        
        Trap::insert($newTrap);
        /* Assert for test insert in DB */
        $dataset = $this->createXmlDataSet(
            dirname(__DIR__) . '/data/traps.insert.xml'
        )->getTable('traps');
        $tableResult = $this->getConnection()->createQueryTable(
            'traps',
            'SELECT * FROM traps'
        );
        $this->assertTablesEqual($dataset, $tableResult);

        /* Test exception unique */
        $this->setExpectedException(
            'PDOException',
            '',
            23000
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
            "traps_comments" => "Test for traps"
        );
        
        /* Test exception unique */
        $this->setExpectedException(
            'PDOException',
            '',
            23000
        );
        Trap::insert($newTrap);
    }
    
    public function testDelete()
    {
        Trap::delete(3);
        /* Assert for test delete in DB */
        $dataset = $this->createXmlDataSet(
            dirname(__DIR__) . '/data/traps.delete.xml'
        )->getTable('traps');
        $tableResult = $this->getConnection()->createQueryTable(
            'traps',
            'SELECT * FROM traps'
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
            "traps_comments" => "Test for traps"
        );
        
        Trap::update(1, $updatedTrap);
        /* Assert for test update in DB */
        $dataset = $this->createXmlDataSet(
            dirname(__DIR__) . '/data/traps.update.xml'
        )->getTable('traps');
        $tableResult = $this->getConnection()->createQueryTable(
            'traps',
            'SELECT * FROM traps'
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
            '',
            23000
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
            "",
            23000
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
        )->getTable('traps');
        $tableResult = $this->getConnection()->createQueryTable(
            'traps',
            'SELECT * FROM traps'
        );
        $this->assertTablesEqual($dataset, $tableResult);
        
        Trap::duplicate(2, 2);
        /* Assert for test update in DB */
        $dataset = $this->createXmlDataSet(
            dirname(__DIR__) . '/data/traps.duplicate-2.xml'
        )->getTable('traps');
        $tableResult = $this->getConnection()->createQueryTable(
            'traps',
            'SELECT * FROM traps'
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
            '',
            '42S22'
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
        $this->assertEquals('traps', Trap::getTableName());
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
                "traps_comments"
            ),
            Trap::getColumns()
        );
    }
}
