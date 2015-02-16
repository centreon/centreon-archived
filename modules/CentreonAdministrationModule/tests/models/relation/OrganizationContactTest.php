<?php
/*
 * Copyright 2005-2014 CENTREON
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
use CentreonAdministration\Models\Relation\Organization\Contact;

class OrganizationContactTest extends DbTestCase
{
    protected $errMsg = 'Object not in database.';
    protected $dataPath = '/modules/CentreonAdministrationModule/tests/data/json/';

    public function testInsert()
    {
        Contact::insert(
            2,
            1,
            array('is_default' => 1)
        );
        $this->tableEqualsXml(
            'cfg_organizations_contacts_relations',
            dirname(dirname(__DIR__)) . '/data/organization_contact.insert.xml'
        );
    }

    public function testInsertDuplicate()
    {
        $this->setExpectedException(
            'PDOException',
            '',
            23000
        );
        Contact::insert(1, 1);
    }

    public function testDelete()
    {
        Contact::delete(1, 1);
        $this->tableEqualsXml(
            'cfg_organizations_contacts_relations',
            dirname(dirname(__DIR__)) . '/data/organization_contact.delete-1.xml'
        );
        Contact::delete(2);
        $this->tableEqualsXml(
            'cfg_organizations_contacts_relations',
            dirname(dirname(__DIR__)) . '/data/organization_contact.delete-2.xml'
        );
    }

    public function testDeleteNotFound()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg,
            0
        );
        Contact::delete(42);
    }

    public function testGetMergedParameters()
    {
        $testResult = array(
            array(
                'organization_id' => '1',
                'name' => 'Default organization',
                'shortname' => 'default_organization',
                'active' => '1',
                'contact_id' => '1',
                'timeperiod_tp_id' => null,
                'timeperiod_tp_id2' => null,
                'contact_name' => 'John Doe',
                'contact_alias' => 'jdoe',
                'contact_passwd' => '2995cb0650c5f107230ed569a8c4d6e5',
                'contact_lang' => 'en_US',
                'contact_host_notification_options' => 'n',
                'contact_service_notification_options' => 'n',
                'contact_email' => 'jdoe@localhost',
                'contact_pager' => null,
                'contact_address1' => null,
                'contact_address2' => null,
                'contact_address3' => null,
                'contact_address4' => null,
                'contact_address5' => null,
                'contact_address6' => null,
                'contact_comment' => null,
                'contact_js_effects' => '0',
                'contact_location' => '0',
                'contact_oreon' => '1',
                'contact_enable_notifications' => '2',
                'contact_template_id' => null,
                'contact_admin' => '0',
                'contact_type_msg' => 'txt',
                'contact_activate' => '0',
                'contact_auth_type' => 'local',
                'contact_ldap_dn' => null,
                'ar_id' => null,
                'contact_acl_group_list' => null,
                'contact_autologin_key' => null,
                'contact_register' => '1',
                'contact_charset' => null,
                'is_default' => '0',
                'is_admin' => '0'
            ),
            array(
                'organization_id' => '1',
                'name' => 'Default organization',
                'shortname' => 'default_organization',
                'active' => '1',
                'contact_id' => '2',
                'timeperiod_tp_id' => null,
                'timeperiod_tp_id2' => null,
                'contact_name' => 'John Smith',
                'contact_alias' => 'jsmith',
                'contact_passwd' => '2995cb0650c5f107230ed569a8c4d6e5',
                'contact_lang' => 'fr_FR',
                'contact_host_notification_options' => 'n',
                'contact_service_notification_options' => 'n',
                'contact_email' => 'jsmith@localhost',
                'contact_pager' => null,
                'contact_address1' => null,
                'contact_address2' => null,
                'contact_address3' => null,
                'contact_address4' => null,
                'contact_address5' => null,
                'contact_address6' => null,
                'contact_comment' => null,
                'contact_js_effects' => '0',
                'contact_location' => '0',
                'contact_oreon' => '1',
                'contact_enable_notifications' => '2',
                'contact_template_id' => null,
                'contact_admin' => '0',
                'contact_type_msg' => 'txt',
                'contact_activate' => '0',
                'contact_auth_type' => 'local',
                'contact_ldap_dn' => null,
                'ar_id' => null,
                'contact_acl_group_list' => null,
                'contact_autologin_key' => null,
                'contact_register' => '1',
                'contact_charset' => null,
                'is_default' => '0',
                'is_admin' => '0'
            ),
            array(
                'organization_id' => '2',
                'name' => 'Client organization',
                'shortname' => 'client',
                'active' => '0',
                'contact_id' => '2',
                'timeperiod_tp_id' => null,
                'timeperiod_tp_id2' => null,
                'contact_name' => 'John Smith',
                'contact_alias' => 'jsmith',
                'contact_passwd' => '2995cb0650c5f107230ed569a8c4d6e5',
                'contact_lang' => 'fr_FR',
                'contact_host_notification_options' => 'n',
                'contact_service_notification_options' => 'n',
                'contact_email' => 'jsmith@localhost',
                'contact_pager' => null,
                'contact_address1' => null,
                'contact_address2' => null,
                'contact_address3' => null,
                'contact_address4' => null,
                'contact_address5' => null,
                'contact_address6' => null,
                'contact_comment' => null,
                'contact_js_effects' => '0',
                'contact_location' => '0',
                'contact_oreon' => '1',
                'contact_enable_notifications' => '2',
                'contact_template_id' => null,
                'contact_admin' => '0',
                'contact_type_msg' => 'txt',
                'contact_activate' => '0',
                'contact_auth_type' => 'local',
                'contact_ldap_dn' => null,
                'ar_id' => null,
                'contact_acl_group_list' => null,
                'contact_autologin_key' => null,
                'contact_register' => '1',
                'contact_charset' => null,
                'is_default' => '0',
                'is_admin' => '0'
            )
        );
        $result = Contact::getMergedParameters();
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array(
                'organization_id' => '1',
                'name' => 'Default organization',
                'shortname' => 'default_organization',
                'active' => '1',
                'contact_id' => '1',
                'timeperiod_tp_id' => null,
                'timeperiod_tp_id2' => null,
                'contact_name' => 'John Doe',
                'contact_alias' => 'jdoe',
                'contact_passwd' => '2995cb0650c5f107230ed569a8c4d6e5',
                'contact_lang' => 'en_US',
                'contact_host_notification_options' => 'n',
                'contact_service_notification_options' => 'n',
                'contact_email' => 'jdoe@localhost',
                'contact_pager' => null,
                'contact_address1' => null,
                'contact_address2' => null,
                'contact_address3' => null,
                'contact_address4' => null,
                'contact_address5' => null,
                'contact_address6' => null,
                'contact_comment' => null,
                'contact_js_effects' => '0',
                'contact_location' => '0',
                'contact_oreon' => '1',
                'contact_enable_notifications' => '2',
                'contact_template_id' => null,
                'contact_admin' => '0',
                'contact_type_msg' => 'txt',
                'contact_activate' => '0',
                'contact_auth_type' => 'local',
                'contact_ldap_dn' => null,
                'ar_id' => null,
                'contact_acl_group_list' => null,
                'contact_autologin_key' => null,
                'contact_register' => '1',
                'contact_charset' => null,
                'is_default' => '0',
                'is_admin' => '0'
            )
        );
        $result = Contact::getMergedParameters(array(), array(), 1);
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array(
                'organization_id' => '1',
                'name' => 'Default organization',
                'shortname' => 'default_organization',
                'active' => '1',
                'contact_id' => '2',
                'timeperiod_tp_id' => null,
                'timeperiod_tp_id2' => null,
                'contact_name' => 'John Smith',
                'contact_alias' => 'jsmith',
                'contact_passwd' => '2995cb0650c5f107230ed569a8c4d6e5',
                'contact_lang' => 'fr_FR',
                'contact_host_notification_options' => 'n',
                'contact_service_notification_options' => 'n',
                'contact_email' => 'jsmith@localhost',
                'contact_pager' => null,
                'contact_address1' => null,
                'contact_address2' => null,
                'contact_address3' => null,
                'contact_address4' => null,
                'contact_address5' => null,
                'contact_address6' => null,
                'contact_comment' => null,
                'contact_js_effects' => '0',
                'contact_location' => '0',
                'contact_oreon' => '1',
                'contact_enable_notifications' => '2',
                'contact_template_id' => null,
                'contact_admin' => '0',
                'contact_type_msg' => 'txt',
                'contact_activate' => '0',
                'contact_auth_type' => 'local',
                'contact_ldap_dn' => null,
                'ar_id' => null,
                'contact_acl_group_list' => null,
                'contact_autologin_key' => null,
                'contact_register' => '1',
                'contact_charset' => null,
                'is_default' => '0',
                'is_admin' => '0'
            )
        );
        $result = Contact::getMergedParameters(array(), array(), 1, 1);
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array(
                'name' => 'Default organization',
                'contact_name' => 'John Doe',
                'is_default' => '0'
            ),
            array(
                'name' => 'Default organization',
                'contact_name' => 'John Smith',
                'is_default' => '0'
            ),
            array(
                'name' => 'Client organization',
                'contact_name' => 'John Smith',
                'is_default' => '0'
            )
        );

        $result = Contact::getMergedParameters(
            array('name'),
            array('contact_name'),
            -1,
            0,
            null,
            'ASC',
            array(),
            'OR',
            array('is_default')
        );
        $this->assertEquals($testResult, $result);
    }

    public function testgetMergedParametersBySearch()
    {
        $result = Contact::getMergedParametersBySearch(
            array('name'),
            array('contact_name'),
            -1,
            0,
            null,
            'ASC',
            array('cfg_contacts.contact_name' => 'Doe'),
            'OR',
            array('is_default')
        );
        $this->assertEquals(
            array(
                array(
                    'name' => 'Default organization',
                    'contact_name' => 'John Doe',
                    'is_default' => '0'
                )
            ),
            $result
        );
    }

    public function testGetFirstKey()
    {
        $this->assertEquals('organization_id', Contact::getFirstKey());
    }

    public function testGetSecondKey()
    {
        $this->assertEquals('contact_id', Contact::getSecondKey());
    }
}
