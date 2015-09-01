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

namespace Test\CentreonAdministration\Models\Relation;

use Test\Centreon\DbTestCase;
use CentreonAdministration\Models\Relation\Organization\Module;

class OrganizationModuleTest extends DbTestCase
{
    protected $errMsg = 'Object not in database.';
    protected $dataPath = '/modules/CentreonAdministrationModule/tests/data/json/';

    public function testInsert()
    {
        Module::insert(
            2,
            1,
            array('is_activated' => 1)
        );
        $this->tableEqualsXml(
            'cfg_organizations_modules_relations',
            dirname(dirname(__DIR__)) . '/data/organization_module.insert.xml'
        );
    }

    public function testInsertDuplicate()
    {
        $this->setExpectedException(
            'PDOException',
            '',
            23000
        );
        Module::insert(1, 1);
    }

    public function testDelete()
    {
        Module::delete(1, 1);
        $this->tableEqualsXml(
            'cfg_organizations_modules_relations',
            dirname(dirname(__DIR__)) . '/data/organization_module.delete-1.xml'
        );
        Module::delete(2);
        $this->tableEqualsXml(
            'cfg_organizations_modules_relations',
            dirname(dirname(__DIR__)) . '/data/organization_module.delete-2.xml'
        );
    }

    public function testDeleteNotFound()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg,
            0
        );
        Module::delete(42);
    }

    public function testGetMergedParameters()
    {
        $testResult = array(
            array(
                'organization_id' => '1',
                'name' => 'Default organization',
                'shortname' => 'default_organization',
                'active' => '1',
                'id' => '1',
                'name' => 'centreon-main',
                'alias' => 'Centreon Main',
                'description' => 'Centreon Main Module',
                'version' => '3.0.0',
                'author' => 'Centreon',
                'isactivated' => '2',
                'isinstalled' => '2',
                'is_activated' => '0',
                'module_id' => '1'
            ),
            array(
                'organization_id' => '1',
                'name' => 'Default organization',
                'shortname' => 'default_organization',
                'active' => '1',
                'id' => '2',
                'name' => 'centreon-administration',
                'alias' => 'Centreon Administration',
                'description' => 'Centreon Administration Module',
                'version' => '3.0.0',
                'author' => 'Centreon',
                'isactivated' => '2',
                'isinstalled' => '2',
                'is_activated' => '0',
                'module_id' => '2'
            ),
            array(
                'organization_id' => '2',
                'name' => 'Client organization',
                'shortname' => 'client',
                'active' => '0',
                'id' => '2',
                'name' => 'centreon-administration',
                'alias' => 'Centreon Administration',
                'description' => 'Centreon Administration Module',
                'version' => '3.0.0',
                'author' => 'Centreon',
                'isactivated' => '2',
                'isinstalled' => '2',
                'is_activated' => '0',
                'module_id' => '2'
            )
        );
        $result = Module::getMergedParameters();
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array(
                'organization_id' => '1',
                'name' => 'Default organization',
                'shortname' => 'default_organization',
                'active' => '1',
                'id' => '1',
                'name' => 'centreon-main',
                'alias' => 'Centreon Main',
                'description' => 'Centreon Main Module',
                'version' => '3.0.0',
                'author' => 'Centreon',
                'isactivated' => '2',
                'isinstalled' => '2',
                'is_activated' => '0',
                'module_id' => '1'
            )
        );
        $result = Module::getMergedParameters(array(), array(), 1);
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array(
                'organization_id' => '1',
                'name' => 'Default organization',
                'shortname' => 'default_organization',
                'active' => '1',
                'id' => '2',
                'name' => 'centreon-administration',
                'alias' => 'Centreon Administration',
                'description' => 'Centreon Administration Module',
                'version' => '3.0.0',
                'author' => 'Centreon',
                'isactivated' => '2',
                'isinstalled' => '2',
                'is_activated' => '0',
                'module_id' => '2'
            )
        );
        $result = Module::getMergedParameters(array(), array(), 1, 1);
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array(
                'name' => 'Default organization',
                'alias' => 'Centreon Main',
                'is_activated' => '0'
            ),
            array(
                'name' => 'Default organization',
                'alias' => 'Centreon Administration',
                'is_activated' => '0'
            ),
            array(
                'name' => 'Client organization',
                'alias' => 'Centreon Administration',
                'is_activated' => '0'
            )
        );

        $result = Module::getMergedParameters(
            array('name'),
            array('alias'),
            -1,
            0,
            null,
            'ASC',
            array(),
            'OR',
            array('is_activated')
        );
        $this->assertEquals($testResult, $result);
    }

    public function testgetMergedParametersBySearch()
    {
        $result = Module::getMergedParametersBySearch(
            array('name'),
            array('alias'),
            -1,
            0,
            null,
            'ASC',
            array('cfg_modules.description' => 'Main'),
            'OR',
            array('is_activated')
        );
        $this->assertEquals(
            array(
                array(
                    'name' => 'Default organization',
                'alias' => 'Centreon Main',
                'is_activated' => '0'
                )
            ),
            $result
        );
    }

    public function testGetFirstKey()
    {
        $this->assertEquals('organization_id', Module::getFirstKey());
    }

    public function testGetSecondKey()
    {
        $this->assertEquals('module_id', Module::getSecondKey());
    }
}
