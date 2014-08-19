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
use \CentreonConfiguration\Models\Manufacturer;

class ManufacturerTest extends DbTestCase
{
    protected $dataPath = '/modules/CentreonConfigurationModule/tests/data/json/';

    public function testInsert()
    {
        $newManufacturer = array(
            "name"  => "Test Manufacturer",
            "alias" => "Test Manufacturer",
            "description" => "Test for traps manufacturer"
        );
        
        Manufacturer::insert($newManufacturer);
        /* Assert for test insert in DB */
        $dataset = $this->createFlatXmlDataSet(
            dirname(__DIR__) . '/data/traps_vendor.insert.xml'
        )->getTable('traps_vendor');
        $tableResult = $this->getConnection()->createQueryTable(
            'traps_vendor',
            'SELECT * FROM traps_vendor'
        );
        $this->assertTablesEqual($dataset, $tableResult);

        /* Test exception unique */
        $this->setExpectedException(
            'PDOException',
            '',
            23000
        );
        Manufacturer::insert($newManufacturer);
    }
    
    /*public function testInsertWithoutMandatoryField()
    {
        $newManufacturer = array(
            "alias" => "Test Manufacturer",
            "description" => "Test for traps manufacturer"
        );
        
        Manufacturer::insert($newManufacturer);
        /* Assert for test insert in DB 
        $dataset = $this->createFlatXmlDataSet(
            dirname(__DIR__) . '/data/traps_vendor.insert.xml'
        )->getTable('traps_vendor');
        $tableResult = $this->getConnection()->createQueryTable(
            'traps_vendor',
            'SELECT * FROM traps_vendor'
        );
        $this->assertTablesEqual($dataset, $tableResult);
    }*/
    
    public function testDelete()
    {
        Manufacturer::delete(4);
        /* Assert for test delete in DB */
        $dataset = $this->createFlatXmlDataSet(
            dirname(__DIR__) . '/data/traps_vendor.delete.xml'
        )->getTable('traps_vendor');
        $tableResult = $this->getConnection()->createQueryTable(
            'traps_vendor',
            'SELECT * FROM traps_vendor'
        );
        $this->assertTablesEqual($dataset, $tableResult);
        
        /* Test exception object doesn't exist */
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            "Object not in database.",
            0
        );
        Manufacturer::delete(8);
    }
    
    public function testUpdate()
    {
        $updatedManufacturer = array(
            "name" => "Centreon Generic Traps",
            "alias" => "Centreon Generic Traps",
            "description" => "References Generic Traps for Centreon"
        );
        
        Manufacturer::update(1, $updatedManufacturer);
        /* Assert for test update in DB */
        $dataset = $this->createFlatXmlDataSet(
            dirname(__DIR__) . '/data/traps_vendor.update.xml'
        )->getTable('traps_vendor');
        $tableResult = $this->getConnection()->createQueryTable(
            'traps_vendor',
            'SELECT * FROM traps_vendor'
        );
        $this->assertTablesEqual($dataset, $tableResult);
        
        /* Test exception object doesn't exist */
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            "Object not in database.",
            0
        );
        Manufacturer::update(8, $updatedManufacturer);
    }
    
    public function testUpdateNotUnique()
    {
        $updatedManufacturer = array(
            "name" => "Cisco"
        );
        
        /* Test exception object doesn't exist */
        $this->setExpectedException(
            'PDOException',
            "",
            23000
        );
        Manufacturer::update(1, $updatedManufacturer);
    }
    
    public function testUpdateNotExist()
    {
        $updatedManufacturer = array(
            "name" => "Cisco"
        );
        
        /* Test exception object doesn't exist */
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            "Object not in database.",
            0
        );
        Manufacturer::update(8, $updatedManufacturer);
    }
    
    public function testGetPrimaryKey()
    {
        $this->assertEquals('id', Manufacturer::getPrimaryKey());
    }

    public function testGetUniqueLabelField()
    {
        $this->assertEquals('name', Manufacturer::getUniqueLabelField());
    }
    
    public function testIsUnique()
    {
        $this->assertTrue(Manufacturer::isUnique('Cisco', 2));
        $this->assertFalse(Manufacturer::isUnique('Cisco', 1));
        $this->assertFalse(Manufacturer::isUnique('Cisco'));
    }
    
    public function testGetTableName()
    {
        $this->assertEquals('traps_vendor', Manufacturer::getTableName());
    }

    public function testGetColumns()
    {
        $this->assertEquals(
            array(
                'id',
                'name',
                'alias',
                'description'
            ),
            Manufacturer::getColumns()
        );
    }
}
