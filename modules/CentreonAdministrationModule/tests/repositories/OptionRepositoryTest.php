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

namespace Test\CentreonAdministration\Repository;

use CentreonAdministration\Repository\OptionRepository;

use Test\Centreon\DbTestCase;

class OptionRepositoryTest extends DbTestCase
{
    protected $errMsg = 'Object not in database.';
    protected $dataPath = '/modules/CentreonAdministrationModule/tests/data/json/';
    
    public function setUp()
    {
        parent::setUp();
    }
    
    public function testAddOption()
    {
        $newOption = array(
            'test_new_option' => 'new option test'
        );
        
        OptionRepository::update($newOption, 'test');
        
        $dataset = $this->createFlatXmlDataSet(
            dirname(__DIR__) . '/data/option.insert.xml'
        )->getTable('cfg_options');
        $tableResult = $this->getConnection()->createQueryTable(
            'cfg_options',
            'SELECT * FROM cfg_options'
        );
        $this->assertTablesEqual($dataset, $tableResult);
    }
    
    public function testUpdateOption()
    {
        $updateOption = array(
            'ldap_contact_tmpl' => '1'
        );
        
        OptionRepository::update($updateOption, 'default');
        
        $dataset = $this->createFlatXmlDataSet(
            dirname(__DIR__) . '/data/option.update.xml'
        )->getTable('cfg_options');
        $tableResult = $this->getConnection()->createQueryTable(
            'cfg_options',
            'SELECT * FROM cfg_options'
        );
        $this->assertTablesEqual($dataset, $tableResult);
    }
}
