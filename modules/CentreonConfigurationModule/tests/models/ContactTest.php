<?php
/*
 * Copyright 2005-2014 MERETHIS
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
 * For more information : contact@centreon.com
 *
 */

namespace Test\CentreonConfiguration\Models;

require_once CENTREON_PATH . "/tests/DbTestCase.php";

use \Test\Centreon\DbTestCase,
    \CentreonConfiguration\Models\Contact;

class ContactTest extends DbTestCase
{
    protected $dataPath = '/modules/CentreonConfigurationModule/tests/data/json/';

    public function testInsert()
    {
        $newContact = array(
            'timeperiod_tp_id' => 1,
            'timeperiod_tp_id2' => 1,
            'contact_name' => 'New contact',
            'contact_alias' => 'New contact',
            'contact_passwd' => 'wxcvb',
            'contact_lang' => 'us_US',
            'contact_host_notification_options' => 'n',
            'contact_service_notification_options' => 'n',
            'contact_email' => 'contact@domain.tld',
            'contact_enable_notifications' => '1',
            'contact_activate' => '1',
            'contact_auth_type' => 'local',
            'contact_register' => '1'
        );
        Contact::insert($newContact);
        /* Assert for test insert in DB */
        $dataset = $this->createXmlDataSet(
            dirname(__DIR__) . '/data/contact.insert.xml'
        )->getTable('cfg_contacts');
        $tableResult = $this->getConnection()->createQueryTable(
            'cfg_contacts',
            'SELECT * FROM cfg_contacts'
        );
        $this->assertTablesEqual($dataset, $tableResult);
    }

    public function testInsertDuplicate()
    {
        $newContact = array(
            'timeperiod_tp_id' => 1,
            'timeperiod_tp_id2' => 1,
            'contact_name' => 'Contact 1',
            'contact_alias' => 'Contact 1',
            'contact_passwd' => 'wxcvb',
            'contact_lang' => 'us_US',
            'contact_host_notification_options' => 'n',
            'contact_service_notification_options' => 'n',
            'contact_email' => 'contact@domain.tld',
            'contact_enable_notifications' => '1',
            'contact_activate' => '1',
            'contact_auth_type' => 'local',
            'contact_register' => '1'
        );

        /* Test exception unique */
        $this->setExpectedException(
            'PDOException',
            '',
            23000
        );
        Contact::insert($newContact);
    }

    public function testInsertTemplateNotExists()
    {
        $newContact = array(
            'timeperiod_tp_id' => 1,
            'timeperiod_tp_id2' => 1,
            'contact_name' => 'New contact',
            'contact_alias' => 'New contact',
            'contact_passwd' => 'wxcvb',
            'contact_lang' => 'us_US',
            'contact_host_notification_options' => 'n',
            'contact_service_notification_options' => 'n',
            'contact_email' => 'contact@domain.tld',
            'contact_enable_notifications' => '1',
            'contact_activate' => '1',
            'contact_auth_type' => 'local',
            'contact_register' => '1',
            'contact_template_id' => 42
        );
        $this->setExpectedException(
            'PDOException',
            '',
            23000
        );
        Contact::insert($newContact);
    }

    public function testDelete()
    {
        Contact::delete(3);
        /* Assert for test delete in DB */
        $dataset = $this->createXmlDataSet(
            dirname(__DIR__) . '/data/contact.delete.xml'
        )->getTable('cfg_contacts');
        $tableResult = $this->getConnection()->createQueryTable(
            'cfg_contacts',
            'SELECT * FROM cfg_contacts'
        );
        $this->assertTablesEqual($dataset, $tableResult);
    }

    public function testDeleteNotExists()
    {
        /* Test exception object doesn't exist */
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            "Object not in database.",
            0
        );
        Contact::delete(42);
    }

    public function testUpdate()
    {
        $newInformation = array(
            'contact_comment' => 'New comment',
            'contact_admin' => '0'
        );
        Contact::update(2, $newInformation);
        /* Assert for test update in DB */
        $dataset = $this->createXmlDataSet(
            dirname(__DIR__) . '/data/contact.update.xml'
        )->getTable('cfg_contacts');
        $tableResult = $this->getConnection()->createQueryTable(
            'cfg_contacts',
            'SELECT * FROM cfg_contacts'
        );
        $this->assertTablesEqual($dataset, $tableResult);
    }

    public function testUpdateNotUnique()
    {
        /* Test exception unique */
        $newInformation = array(
            'contact_alias' => 'Contact 1'
        );
        $this->setExpectedException(
            'PDOException',
            '',
            23000
        );
        Contact::update(3, $newInformation);
    }

    public function testUpdateNotFound() {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            "Object not in database.",
            0
        );
        $newInformation = array(
            'contact_alias' => 'Contact 1'
        );
        Contact::update(42, $newInformation);
    }

    public function testDuplicate()
    {
        Contact::duplicate(2);
        /* Assert for test duplicate 1 in DB */
        $dataset = $this->createXmlDataSet(
            dirname(__DIR__) . '/data/contact.duplicate-1.xml'
        )->getTable('cfg_contacts');
        $tableResult = $this->getConnection()->createQueryTable(
            'cfg_contacts',
            'SELECT * FROM cfg_contacts'
        );
        $this->assertTablesEqual($dataset, $tableResult);

        Contact::duplicate(3, 2);
        /* Assert for test duplicate 2 in DB */
        $dataset = $this->createXmlDataSet(
            dirname(__DIR__) . '/data/contact.duplicate-2.xml'
        )->getTable('cfg_contacts');
        $tableResult = $this->getConnection()->createQueryTable(
            'cfg_contacts',
            'SELECT * FROM cfg_contacts'
        );
        $this->assertTablesEqual($dataset, $tableResult);
    }

    public function testDuplicateNotExists()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            "Object not in database.",
            0
        );
        Contact::duplicate(42);
    }

    public function testGetParameters()
    {
        $testInformation = array(
            'contact_id' => '1',
            'timeperiod_tp_id' => '1',
            'timeperiod_tp_id2' => '1',
            'contact_name' => 'Template contact',
            'contact_alias' => 'Template contact',
            'contact_passwd' => null,
            'contact_lang' => 'fr_FR',
            'contact_host_notification_options' => 'n',
            'contact_service_notification_options' => 'n',
            'contact_email' => null,
            'contact_pager' => null,
            'contact_address1' => null,
            'contact_address2' => null,
            'contact_address3' => null,
            'contact_address4' => null,
            'contact_address5' => null,
            'contact_address6' => null,
            'contact_comment' => 'Default template contact',
            'contact_js_effects' => '0',
            'contact_location' => '0',
            'contact_oreon' => '1',
            'contact_enable_notifications' => '1',
            'contact_template_id' => null,
            'contact_admin' => '0',
            'contact_type_msg' => 'txt',
            'contact_activate' => '1',
            'contact_auth_type' => 'local',
            'contact_ldap_dn' => null,
            'ar_id' => null,
            'contact_acl_group_list' => null,
            'contact_autologin_key' => null,
            'contact_charset' => null,
            'contact_register' => '0'
        );
        $contact = Contact::getParameters(1, '*');

        $this->assertEquals($contact, $testInformation);

        $contact = Contact::getParameters(2, 'contact_alias');
        $this->assertEquals($contact, array('contact_alias' => 'Contact 1'));

        $contact = Contact::getParameters(2, array('contact_alias', 'contact_admin'));
        $this->assertEquals($contact, array('contact_alias' => 'Contact 1', 'contact_admin' => '1'));
    }

    public function testGetParametersNotFound()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            "Object not in database.",
            0
        );
        $contact = Contact::getParameters(42, '*');
    }

    public function testGetParametersBadColumns()
    {
        $this->setExpectedException(
            'PDOException',
            '',
            '42S22'
        );
        Contact::getParameters(1, 'test_error');

        Contact::getParameters(1, array('name', 'test_error'));
    }

    public function testGetList()
    {
        $testResult = array(
            array(
                'contact_id' => '1',
                'timeperiod_tp_id' => '1',
                'timeperiod_tp_id2' => '1',
                'contact_name' => 'Template contact',
                'contact_alias' => 'Template contact',
                'contact_passwd' => null,
                'contact_lang' => 'fr_FR',
                'contact_host_notification_options' => 'n',
                'contact_service_notification_options' => 'n',
                'contact_email' => null,
                'contact_pager' => null,
                'contact_address1' => null,
                'contact_address2' => null,
                'contact_address3' => null,
                'contact_address4' => null,
                'contact_address5' => null,
                'contact_address6' => null,
                'contact_comment' => 'Default template contact',
                'contact_js_effects' => '0',
                'contact_location' => '0',
                'contact_oreon' => '1',
                'contact_enable_notifications' => '1',
                'contact_template_id' => null,
                'contact_admin' => '0',
                'contact_type_msg' => 'txt',
                'contact_activate' => '1',
                'contact_auth_type' => 'local',
                'contact_ldap_dn' => null,
                'ar_id' => null,
                'contact_acl_group_list' => null,
                'contact_autologin_key' => null,
                'contact_charset' => null,
                'contact_register' => '0'
            ),
            array(
                'contact_id' => '2',
                'timeperiod_tp_id' => '1',
                'timeperiod_tp_id2' => '1',
                'contact_name' => 'Contact 1',
                'contact_alias' => 'Contact 1',
                'contact_passwd' => 'azerty',
                'contact_lang' => 'us_US',
                'contact_host_notification_options' => 'n',
                'contact_service_notification_options' => 'n',
                'contact_email' => 'contact_1@domain.tld',
                'contact_pager' => '+331111',
                'contact_address1' => null,
                'contact_address2' => null,
                'contact_address3' => null,
                'contact_address4' => null,
                'contact_address5' => null,
                'contact_address6' => null,
                'contact_comment' => 'Contact user 1',
                'contact_js_effects' => '0',
                'contact_location' => '0',
                'contact_oreon' => '1',
                'contact_enable_notifications' => '1',
                'contact_template_id' => null,
                'contact_admin' => '1',
                'contact_type_msg' => 'txt',
                'contact_activate' => '1',
                'contact_auth_type' => 'local',
                'contact_ldap_dn' => null,
                'ar_id' => null,
                'contact_acl_group_list' => null,
                'contact_autologin_key' => null,
                'contact_charset' => null,
                'contact_register' => '1'
            ),
            array(
                'contact_id' => '3',
                'timeperiod_tp_id' => '1',
                'timeperiod_tp_id2' => '1',
                'contact_name' => 'Contact 2',
                'contact_alias' => 'Contact 2',
                'contact_passwd' => 'qsdfgh',
                'contact_lang' => 'us_US',
                'contact_host_notification_options' => 'n',
                'contact_service_notification_options' => 'n',
                'contact_email' => 'contact_2@domain.tld',
                'contact_pager' => '+332222',
                'contact_address1' => null,
                'contact_address2' => null,
                'contact_address3' => null,
                'contact_address4' => null,
                'contact_address5' => null,
                'contact_address6' => null,
                'contact_comment' => 'Contact with template',
                'contact_js_effects' => '0',
                'contact_location' => '0',
                'contact_oreon' => '1',
                'contact_enable_notifications' => '1',
                'contact_template_id' => '1',
                'contact_admin' => '0',
                'contact_type_msg' => 'txt',
                'contact_activate' => '1',
                'contact_auth_type' => 'local',
                'contact_ldap_dn' => null,
                'ar_id' => null,
                'contact_acl_group_list' => null,
                'contact_autologin_key' => null,
                'contact_charset' => null,
                'contact_register' => '1'
            )
        );
        $result = Contact::getList();
        $this->assertEquals($testResult, $result);   

        $testResult = array(
            array(
                'contact_id' => '1',
                'timeperiod_tp_id' => '1',
                'timeperiod_tp_id2' => '1',
                'contact_name' => 'Template contact',
                'contact_alias' => 'Template contact',
                'contact_passwd' => null,
                'contact_lang' => 'fr_FR',
                'contact_host_notification_options' => 'n',
                'contact_service_notification_options' => 'n',
                'contact_email' => null,
                'contact_pager' => null,
                'contact_address1' => null,
                'contact_address2' => null,
                'contact_address3' => null,
                'contact_address4' => null,
                'contact_address5' => null,
                'contact_address6' => null,
                'contact_comment' => 'Default template contact',
                'contact_js_effects' => '0',
                'contact_location' => '0',
                'contact_oreon' => '1',
                'contact_enable_notifications' => '1',
                'contact_template_id' => null,
                'contact_admin' => '0',
                'contact_type_msg' => 'txt',
                'contact_activate' => '1',
                'contact_auth_type' => 'local',
                'contact_ldap_dn' => null,
                'ar_id' => null,
                'contact_acl_group_list' => null,
                'contact_autologin_key' => null,
                'contact_charset' => null,
                'contact_register' => '0'
            )
        );
        $result = Contact::getList('*', 1);
        $this->assertEquals($testResult, $result);   

        $testResult = array(
            array(
                'contact_id' => '2',
                'timeperiod_tp_id' => '1',
                'timeperiod_tp_id2' => '1',
                'contact_name' => 'Contact 1',
                'contact_alias' => 'Contact 1',
                'contact_passwd' => 'azerty',
                'contact_lang' => 'us_US',
                'contact_host_notification_options' => 'n',
                'contact_service_notification_options' => 'n',
                'contact_email' => 'contact_1@domain.tld',
                'contact_pager' => '+331111',
                'contact_address1' => null,
                'contact_address2' => null,
                'contact_address3' => null,
                'contact_address4' => null,
                'contact_address5' => null,
                'contact_address6' => null,
                'contact_comment' => 'Contact user 1',
                'contact_js_effects' => '0',
                'contact_location' => '0',
                'contact_oreon' => '1',
                'contact_enable_notifications' => '1',
                'contact_template_id' => null,
                'contact_admin' => '1',
                'contact_type_msg' => 'txt',
                'contact_activate' => '1',
                'contact_auth_type' => 'local',
                'contact_ldap_dn' => null,
                'ar_id' => null,
                'contact_acl_group_list' => null,
                'contact_autologin_key' => null,
                'contact_charset' => null,
                'contact_register' => '1'
            )
        );
        $result = Contact::getList('*', 1, 1);
        $this->assertEquals($testResult, $result);   

        $testResult = array(
            array('contact_alias' => 'Contact 1'),
            array('contact_alias' => 'Contact 2'),
            array('contact_alias' => 'Template contact')
        );
        $result = Contact::getList('contact_alias');
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array('contact_alias' => 'Contact 1', 'contact_id' => 2),
            array('contact_alias' => 'Contact 2', 'contact_id' => 3),
            array('contact_alias' => 'Template contact', 'contact_id' => 1)
        );
        $result = Contact::getList(array('contact_alias', 'contact_id'));
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array('contact_alias' => 'Template contact'),
            array('contact_alias' => 'Contact 2'),
            array('contact_alias' => 'Contact 1')
        );
        $result = Contact::getList('contact_alias', -1, 0, 'contact_alias', 'DESC');
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array('contact_alias' => 'Contact 1')
        );
        $result = Contact::getList('contact_alias', -1, 0, null, 'ASC', array('contact_alias' => 'Contact 1'));
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array('contact_alias' => 'Contact 1'),
            array('contact_alias' => 'Contact 2')
        );
        $result = Contact::getList('contact_alias', -1, 0, null, 'ASC', array('contact_alias' => array('Contact 1', 'Contact 2')));
        $this->assertEquals($testResult, $result);

        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            "Unknown filter type",
            0
        );
        Contact::getList('contact_alias', -1, 0, null, 'ASC', array('contact_alias' => array('SSH', 'Perl')), 'ERR');
    }

    public function testGetListBySearch()
    {
        $testResult = array(
            array('contact_alias' => 'Contact 1'),
            array('contact_alias' => 'Contact 2'),
            array('contact_alias' => 'Template contact')
        );
        $result = Contact::getListBySearch('contact_alias');
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array('contact_alias' => 'Contact 1'),
            array('contact_alias' => 'Contact 2')
        );
        $result = Contact::getListBySearch('contact_alias', -1, 0, null, 'ASC', array('contact_lang' => 'us'));
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array('contact_alias' => 'Contact 1'),
            array('contact_alias' => 'Contact 2'),
            array('contact_alias' => 'Template contact')
        );
        $result = Contact::getListBySearch('contact_alias', -1, 0, 'contact_alias', 'ASC', array('contact_lang' => array('us', 'fr')));
        $this->assertEquals($testResult, $result);
    }

    public function testGet()
    {
        $testResult = array(
            'contact_id' => '1',
            'timeperiod_tp_id' => '1',
            'timeperiod_tp_id2' => '1',
            'contact_name' => 'Template contact',
            'contact_alias' => 'Template contact',
            'contact_passwd' => null,
            'contact_lang' => 'fr_FR',
            'contact_host_notification_options' => 'n',
            'contact_service_notification_options' => 'n',
            'contact_email' => null,
            'contact_pager' => null,
            'contact_address1' => null,
            'contact_address2' => null,
            'contact_address3' => null,
            'contact_address4' => null,
            'contact_address5' => null,
            'contact_address6' => null,
            'contact_comment' => 'Default template contact',
            'contact_js_effects' => '0',
            'contact_location' => '0',
            'contact_oreon' => '1',
            'contact_enable_notifications' => '1',
            'contact_template_id' => null,
            'contact_admin' => '0',
            'contact_type_msg' => 'txt',
            'contact_activate' => '1',
            'contact_auth_type' => 'local',
            'contact_ldap_dn' => null,
            'ar_id' => null,
            'contact_acl_group_list' => null,
            'contact_autologin_key' => null,
            'contact_charset' => null,
            'contact_register' => '0'
        );
        $result = Contact::get(1);
        $this->assertEquals($testResult, $result);

        $testResult = array(
            'contact_alias' => 'Contact 1',
        );
        $result = Contact::get(2, 'contact_alias');
        $this->assertEquals($testResult, $result);

        $testResult = array(
            'contact_alias' => 'Template contact',
            'contact_comment' => 'Default template contact'
        );
        $result = Contact::get(1, array('contact_alias', 'contact_comment'));
        $this->assertEquals($testResult, $result);

        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            "Object not in database.",
            0
        );
        Contact::get(42);
    }

    public function testGetIdByParameter()
    {
        $testResult = array(2);
        $result = Contact::getIdByParameter('contact_alias', 'Contact 1');
        $this->assertEquals($testResult, $result);

        $testResult = array(2, 3);
        $result = Contact::getIdByParameter('contact_alias', array('Contact 1', 'Contact 2'));
        $this->assertEquals($testResult, $result);

        $this->setExpectedException(
            'PDOException',
            '',
            '42S22'
        );
        Contact::getIdByParameter('errColumn', 'Bad contact');
    }

    public function testGetPrimaryKey()
    {
        $this->assertEquals('contact_id', Contact::getPrimaryKey());
    }

    public function testGetUniqueLabelField()
    {
        $this->assertEquals('contact_alias', Contact::getUniqueLabelField());
    }

    public function getRelations()
    {
        $this->assertEquals(array(), Contact::getRelations());
    }

    public function testIsUnique()
    {
        $this->assertTrue(Contact::isUnique('Contact 1', 2));
        $this->assertFalse(Contact::isUnique('Contact 1', 3));
        $this->assertFalse(Contact::isUnique('Contact 1'));
    }

    public function testGetTableName()
    {
        $this->assertEquals('cfg_contacts', Contact::getTableName());
    }

    public function testGetColumns()
    {
        $this->assertEquals(
            array(
                'contact_id',
                'timeperiod_tp_id',
                'timeperiod_tp_id2',
                'contact_name',
                'contact_alias',
                'contact_passwd',
                'contact_lang',
                'contact_host_notification_options',
                'contact_service_notification_options',
                'contact_email',
                'contact_pager',
                'contact_address1',
                'contact_address2',
                'contact_address3',
                'contact_address4',
                'contact_address5',
                'contact_address6',
                'contact_comment',
                'contact_js_effects',
                'contact_location',
                'contact_oreon',
                'contact_enable_notifications',
                'contact_template_id',
                'contact_admin',
                'contact_type_msg',
                'contact_activate',
                'contact_auth_type',
                'contact_ldap_dn',
                'ar_id',
                'contact_acl_group_list',
                'contact_autologin_key',
                'contact_charset',
                'contact_register'
            ),
            Contact::getColumns()
        );
    }
}
