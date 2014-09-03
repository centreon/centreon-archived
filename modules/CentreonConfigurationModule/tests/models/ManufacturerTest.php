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
    protected $errMsg = 'Object not in database.';
    protected $dataPath = '/modules/CentreonConfigurationModule/tests/data/json/';

    public function testInsert()
    {
        $newManufacturer = array(
            "name"  => "Test Manufacturer",
            "alias" => "Test Manufacturer",
            "description" => "Test for traps manufacturer",
            "organization_id" => 1
        );
        
        Manufacturer::insert($newManufacturer);
        /* Assert for test insert in DB */
        $dataset = $this->createFlatXmlDataSet(
            dirname(__DIR__) . '/data/traps_vendor.insert.xml'
        )->getTable('cfg_traps_vendors');
        $tableResult = $this->getConnection()->createQueryTable(
            'cfg_traps_vendors',
            'SELECT * FROM cfg_traps_vendors'
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
        )->getTable('cfg_traps_vendors');
        $tableResult = $this->getConnection()->createQueryTable(
            'cfg_traps_vendors',
            'SELECT * FROM cfg_traps_vendors'
        );
        $this->assertTablesEqual($dataset, $tableResult);
        
        /* Test exception object doesn't exist */
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg,
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
        )->getTable('cfg_traps_vendors');
        $tableResult = $this->getConnection()->createQueryTable(
            'cfg_traps_vendors',
            'SELECT * FROM cfg_traps_vendors'
        );
        $this->assertTablesEqual($dataset, $tableResult);
        
        /* Test exception object doesn't exist */
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg,
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
    
    public function testUpdateNotFound()
    {
        $updatedManufacturer = array(
            "name" => "Cisco"
        );
        
        /* Test exception object doesn't exist */
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg,
            0
        );
        Manufacturer::update(8, $updatedManufacturer);
    }
    
    public function testDuplicate()
    {
        Manufacturer::duplicate(1);
        /* Assert for test update in DB */
        $dataset = $this->createFlatXmlDataSet(
            dirname(__DIR__) . '/data/traps_vendor.duplicate-1.xml'
        )->getTable('cfg_traps_vendors');
        $tableResult = $this->getConnection()->createQueryTable(
            'cfg_traps_vendors',
            'SELECT * FROM cfg_traps_vendors'
        );
        $this->assertTablesEqual($dataset, $tableResult);
        
        Manufacturer::duplicate(2, 2);
        /* Assert for test update in DB */
        $dataset = $this->createFlatXmlDataSet(
            dirname(__DIR__) . '/data/traps_vendor.duplicate-2.xml'
        )->getTable('cfg_traps_vendors');
        $tableResult = $this->getConnection()->createQueryTable(
            'cfg_traps_vendors',
            'SELECT * FROM cfg_traps_vendors'
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
        Manufacturer::duplicate(8);
    }
    
    public function testGetParameters()
    {
        $testInformation = array(
            'id' => 1,
            'name' => 'Generic',
            'alias' => 'Generic',
            'description' => 'References Generic Traps',
            'organization_id' => 1
        );
        $manufacturer = Manufacturer::getParameters(1, '*');
        $this->assertEquals($manufacturer, $testInformation);
        
        $manufacturer = Manufacturer::getParameters(5, 'name');
        $this->assertEquals($manufacturer, array('name' => 'Linksys'));
        
        $manufacturer = Manufacturer::getParameters(3, array('name', 'alias'));
        $this->assertEquals($manufacturer, array('name' => 'HP', 'alias' => 'HP Networks'));
    }
    
    public function testGetParametersNotFound()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg,
            0
        );
        $manufacturer = Manufacturer::getParameters(21, '*');
    }
    
    public function testGetParametersBadColumns()
    {
        $this->setExpectedException(
            'PDOException',
            '',
            '42S22'
        );
        Manufacturer::getParameters(1, 'test_error');
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
        $this->assertEquals('cfg_traps_vendors', Manufacturer::getTableName());
    }

    public function testGetColumns()
    {
        $this->assertEquals(
            array(
                'id',
                'name',
                'alias',
                'description',
                'organization_id'
            ),
            Manufacturer::getColumns()
        );
    }
}
