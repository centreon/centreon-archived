<?php
/*
 * Copyright 2005-2015 CENTREON
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
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
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give CENTREON
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of CENTREON choice, provided that
 * CENTREON also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
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
