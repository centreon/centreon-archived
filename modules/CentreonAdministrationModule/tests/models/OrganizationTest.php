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
namespace Test\CentreonAdministration\Models;

require_once CENTREON_PATH . "/tests/DbTestCase.php";

use Test\Centreon\DbTestCase;
use CentreonAdministration\Models\Organization;

class OrganizationTest extends DbTestCase
{
    protected $errMsg = 'Object not in database.';
    protected $dataPath = '/modules/CentreonAdministrationModule/tests/data/json/';

    public function testInsert()
    {
        $newOrganization = array(
            'name' => 'Test organization',
            'shortname' => 'test_organization',
            'active' => 1
        );
        Organization::insert($newOrganization);
        $this->tableEqualsXml(
            'cfg_organizations',
            dirname(__DIR__) . '/data/organization.insert.xml'
        );
    }

    public function testInsertDuplicateName()
    {
        $newOrganization = array(
            'name' => 'Default organization',
            'shortname' => 'test_organization',
            'active' => 1
        );
        $this->setExpectedException(
            'PDOException',
            '',
            23000
        );
        Organization::insert($newOrganization);
    }

    public function testInsertDuplicateShortname()
    {
        $newOrganization = array(
            'name' => 'Test organization',
            'shortname' => 'default_organization',
            'active' => 1
        );
        $this->setExpectedException(
            'PDOException',
            '',
            23000
        );
        Organization::insert($newOrganization);
    }

    public function testDelete()
    {
        Organization::delete(2);
        $this->tableEqualsXml(
            'cfg_organizations',
            dirname(__DIR__) . '/data/organization.delete.xml'
        );
    }

    public function testDeleteNotExists()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg,
            0
        );
        Organization::delete(42);
    }

    public function testUpdate()
    {
        $newInformation = array(
            'name' => 'Rename client organization',
            'active' => 1
        );
        Organization::update(2, $newInformation);
        $this->tableEqualsXml(
            'cfg_organizations',
            dirname(__DIR__) . '/data/organization.update.xml'
        );
    }

    public function testUpdateNotUniqueName()
    {
        $newInformation = array(
            'name' => 'Default organization',
            'active' => 1
        );
        $this->setExpectedException(
            'PDOException',
            '',
            23000
        );
        Organization::update(2, $newInformation);
    }

    public function testUpdateNotUniqueShortname()
    {
        $newInformation = array(
            'shortname' => 'default_organization',
            'active' => 1
        );
        $this->setExpectedException(
            'PDOException',
            '',
            23000
        );
        Organization::update(2, $newInformation);
    }

    public function testUpdateNotExists()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg,
            0
        );
        $newInformation = array(
            'active' => 1
        );
        Organization::update(42, $newInformation);
    }

    public function testDuplicate()
    {
        Organization::duplicate(1);
        $this->tableEqualsXml(
            'cfg_organizations',
            dirname(__DIR__) . '/data/organization.duplicate-1.xml'
        );
        Organization::duplicate(2, 2);
        $this->tableEqualsXml(
            'cfg_organizations',
            dirname(__DIR__) . '/data/organization.duplicate-2.xml'
        );
    }

    public function testDuplicateNotExists()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg,
            0
        );
        Organization::duplicate(42);
    }

    public function testGetParameters()
    {
        $testInformation = array(
            'organization_id' => 1,
            'name' => 'Default organization',
            'shortname' => 'default_organization',
            'active' => 1
        );
        $organization = Organization::getParameters(1, '*');
        $this->assertEquals($organization, $testInformation);

        $organization = Organization::getParameters(2, 'name');
        $this->assertEquals($organization, array('name' => 'Client organization'));

        $organization = Organization::getParameters(2, array('name', 'active'));
        $this->assertEquals($organization, array('name' => 'Client organization', 'active' => 0));
    }

    public function testGetParametersNotFound()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg,
            0
        );
        Organization::getParameters(42, '*');
    }

    public function testGetParametersBadColumns()
    {
        $this->setExpectedException(
            'PDOException',
            '',
            '42S22'
        );
        Organization::getParameters(1, 'bad_column');
    }

    public function testGetList()
    {
        $testResult = array(
            array(
                'organization_id' => 1,
                'name' => 'Default organization',
                'shortname' => 'default_organization',
                'active' => 1
            ),
            array(
                'organization_id' => 2,
                'name' => 'Client organization',
                'shortname' => 'client',
                'active' => 0
            )
        );
        $result = Organization::getList();
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array(
                'organization_id' => 1,
                'name' => 'Default organization',
                'shortname' => 'default_organization',
                'active' => 1
            )
        );
        $result = Organization::getList('*', 1);
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array(
                'organization_id' => 2,
                'name' => 'Client organization',
                'shortname' => 'client',
                'active' => 0
            )
        );
        $result = Organization::getList('*', 1, 1);
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array('name' => 'Client organization'),
            array('name' => 'Default organization')
        );
        $result = Organization::getList('name');
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array('name' => 'Client organization', 'organization_id' => 2),
            array('name' => 'Default organization', 'organization_id' => 1)
        );
        $result = Organization::getList(array('name', 'organization_id'));
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array('name' => 'Client organization'),
            array('name' => 'Default organization')
        );
        $result = Organization::getList('name', -1, 0, 'name', 'ASC');
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array('name' => 'Default organization')
        );
        $result = Organization::getList('name', -1, 0, null, 'ASC', array('shortname' => 'default_organization'));
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array('name' => 'Default organization'),
            array('name' => 'Client organization')
        );
        $result = Organization::getList(
            'name',
            -1,
            0,
            null,
            'ASC',
            array('shortname' => array('default_organization', 'client'))
        );
        $this->assertEquals($testResult, $result);
    }

    public function testGetListBadFilter()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            "Unknown filter type",
            0
        );
        Organization::getList('name', -1, 0, null, 'ASC', array('shortname' => 'default_organization'), 'ERR');
    }

    public function testGetListBySearch()
    {
        $testResult = array(
            array('name' => 'Client organization'),
            array('name' => 'Default organization')
        );
        $result = Organization::getListBySearch('name');
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array('name' => 'Client organization'),
            array('name' => 'Default organization')
        );
        $result = Organization::getListBySearch('name', -1, 0, null, 'ASC', array('name' => 'organization'));
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array('name' => 'Default organization'),
            array('name' => 'Client organization')
        );
        $result = Organization::getListBySearch('name', -1, 0, null, 'ASC', array('active' => array(0, 1)));
        $this->assertEquals($testResult, $result);
    }

    public function testGet()
    {
        $testResult = array(
            'organization_id' => 1,
            'name' => 'Default organization',
            'shortname' => 'default_organization',
            'active' => 1
        );
        $result = Organization::get(1);
        $this->assertEquals($testResult, $result);

        $testResult = array(
            'name' => 'Default organization',
        );
        $result = Organization::get(1, 'name');
        $this->assertEquals($testResult, $result);
    }

    public function testGetNotFound()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg,
            0
        );
        Organization::get(42);
    }

    public function testGetIdByParameter()
    {
        $testResult = array(1);
        $result = Organization::getIdByParameter('shortname', 'default_organization');
        $this->assertEquals($testResult, $result);
    }

    public function testGetIdByParameterBadColumn()
    {
        $this->setExpectedException(
            'PDOException',
            '',
            '42S22'
        );
        Organization::getIdByParameter('errColumn', 'default_organization');
    }

    public function testGetPrimaryKey()
    {
        $this->assertEquals('organization_id', Organization::getPrimaryKey());
    }

    public function testGetUniqueLabelField()
    {
        $this->assertEquals(array('shortname', 'name'), Organization::getUniqueLabelField());
    }

    public function getRelations()
    {
        $this->assertEquals(array(), Organization::getRelations());
    }

    public function testIsUnique()
    {
        $this->assertTrue(Organization::isUnique('default_organization', 1));
        $this->assertFalse(Organization::isUnique('default_organization', 2));
        $this->assertFalse(Organization::isUnique('default_organization'));
        $this->assertFalse(Organization::isUnique('default_organization', 2, 'shortname'));
        $this->assertTrue(Organization::isUnique('default_organization', 2, 'name'));
    }

    public function testGetTableName()
    {
        $this->assertEquals('cfg_organizations', Organization::getTableName());
    }

    public function testGetColumns()
    {
        $this->assertEquals(
            array(
                'organization_id',
                'name',
                'shortname',
                'active'
            ),
            Organization::getColumns()
        );
    }
}
