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
 */

namespace Test\CentreonConfiguration\Models;

use \Test\Centreon\DbTestCase;
use CentreonConfiguration\Models\Servicetemplate;

class ServiceTemplateTest extends DbTestCase
{
    protected $errMsg = 'Object not in database.';
    protected $dataPath = '/modules/CentreonConfigurationModule/tests/data/json/';

    public function testInsert()
    {
        $insert = array(
            'service_description' => 'test',
            'service_alias' => 'test alias',
            'service_template_model_stm_id' => '1',
            'service_activate' => '1',
            'organization_id' => 1
        );
        Servicetemplate::insert($insert);
        $this->tableEqualsXml(
            'cfg_services',
            dirname(__DIR__) . '/data/servicetemplate.insert.xml'
        );
    }

    public function testDelete()
    {
        Servicetemplate::delete(2);
        $this->tableEqualsXml(
            'cfg_services',
            dirname(__DIR__) . '/data/servicetemplate.delete.xml'
        );
    }

    public function testDeleteUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Servicetemplate::delete(9999);
    }

    public function testUpdate()
    {
        $newInfo = array(
            'service_alias' => 'new_alias'
        );
        Servicetemplate::update(1, $newInfo);
        $this->tableEqualsXml(
            'cfg_services',
            dirname(__DIR__) . '/data/servicetemplate.update.xml'
        );
    }

    public function testUpdateUnknownId()
    {
        $newInfo = array(
            'service_alias' => 'test'
        );
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Servicetemplate::update(9999, $newInfo);
    }

    public function testDuplicateItemOnce()
    {
        Servicetemplate::duplicate(1);
        $this->tableEqualsXml(
            'cfg_services',
            dirname(__DIR__) . '/data/servicetemplate.duplicate-1.xml'
        );
    }

    public function testDuplicateItemMultipleTimes()
    {
        Servicetemplate::duplicate(1, 2);
        $this->tableEqualsXml(
            'cfg_services',
            dirname(__DIR__) . '/data/servicetemplate.duplicate-2.xml'
        );
    }

    public function testDuplicateUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Servicetemplate::duplicate(9999);

        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Servicetemplate::duplicate(9999, 2);
    }

    public function testGetAllParameters()
    {
        $testInformation = array(
            'service_id' => 2,
            'service_template_model_stm_id' => null,
            'command_command_id' => 2,
            'timeperiod_tp_id' => 1,
            'command_command_id2' => 1,
            'timeperiod_tp_id2' => 1,
            'service_description' => 'load-template',
            'service_alias' => 'load',
            'display_name' => null,
            'service_is_volatile' =>  2,
            'service_max_check_attempts' => 3,
            'service_normal_check_interval' => 5,
            'service_retry_check_interval' => 1,
            'service_active_checks_enabled' =>  1,
            'service_passive_checks_enabled' => 0,
            'initial_state' => null,
            'service_parallelize_check' => 2,
            'service_obsess_over_service' => 2,
            'service_check_freshness' => 2,
            'service_freshness_threshold' => null,
            'service_event_handler_enabled' => 2,
            'service_low_flap_threshold' => null,
            'service_high_flap_threshold' => null,
            'service_flap_detection_enabled' => 2,
            'service_process_perf_data' => 2,
            'service_retain_status_information' => 2,
            'service_retain_nonstatus_information' => 2,
            'service_notification_interval' => null,
            'service_notification_options' => null,
            'service_notifications_enabled' => 2,
            'contact_additive_inheritance' => 0,
            'cg_additive_inheritance' => 0,
            'service_inherit_contacts_from_host' => 1,
            'service_first_notification_delay' => null,
            'service_stalking_options' => null,
            'service_comment' => null,
            'command_command_id_arg' => null,
            'command_command_id_arg2' => null,
            'service_locked' => 0,
            'service_register' => 0,
            'service_activate' => 1,
            'organization_id' => 1,
            'environment_id' => null,
            'domain_id' => null
        );
        $arr = Servicetemplate::getParameters(2, '*');
        $this->assertEquals($arr, $testInformation);
    }

    public function testGetSpecificParameter()
    {
        $testInformation = array(
            'service_description' => 'load-template',
        );
        $arr = Servicetemplate::getParameters(2, 'service_description');
        $this->assertEquals($arr, $testInformation);
    }

    public function testGetSpecificParameters()
    {
        $testInformation = array(
            'service_description' => 'load-template',
            'service_alias' => 'load',
            'service_activate' => '1'
        );
        $arr = Servicetemplate::getParameters(2, array('service_description', 'service_alias', 'service_activate'));
        $this->assertEquals($arr, $testInformation);
    }

    public function testGetParametersFromUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Servicetemplate::getParameters(9999, '*');

        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Servicetemplate::getParameters(9999, 'service_alias');

        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Servicetemplate::getParameters(9999, array('service_description', 'service_alias'));
    }

    public function testGetUnknownParameters()
    {
        $this->setExpectedException(
            'PDOException'
        );
        Servicetemplate::getParameters(2, 'idontexist');

        $this->setExpectedException(
            'PDOException'
        );
        Servicetemplate::getParameters(2, array('service_alias', 'idontexist'));
    }

    public function testGetList()
    {
        $expectedResult = array(
            array(
                'service_id' => 1,
                'service_template_model_stm_id' => null,
                'command_command_id' => 2,
                'timeperiod_tp_id' => 1,
                'command_command_id2' => 1,
                'timeperiod_tp_id2' => 1,
                'service_description' => 'ping-template',
                'service_alias' => 'ping',
                'display_name' => null,
                'service_is_volatile' =>  2,
                'service_max_check_attempts' => 3,
                'service_normal_check_interval' => 5,
                'service_retry_check_interval' => 1,
                'service_active_checks_enabled' =>  1,
                'service_passive_checks_enabled' => 0,
                'initial_state' => null,
                'service_parallelize_check' => 2,
                'service_obsess_over_service' => 2,
                'service_check_freshness' => 2,
                'service_freshness_threshold' => null,
                'service_event_handler_enabled' => 2,
                'service_low_flap_threshold' => null,
                'service_high_flap_threshold' => null,
                'service_flap_detection_enabled' => 2,
                'service_process_perf_data' => 2,
                'service_retain_status_information' => 2,
                'service_retain_nonstatus_information' => 2,
                'service_notification_interval' => null,
                'service_notification_options' => null,
                'service_notifications_enabled' => 2,
                'contact_additive_inheritance' => 0,
                'cg_additive_inheritance' => 0,
                'service_inherit_contacts_from_host' => 1,
                'service_first_notification_delay' => null,
                'service_stalking_options' => null,
                'service_comment' => null,
                'command_command_id_arg' => null,
                'command_command_id_arg2' => null,
                'service_locked' => 0,
                'service_register' => 0,
                'service_activate' => 1,
                'organization_id' => 1,
                'environment_id' => null,
                'domain_id' => null
            ),
            array(
                'service_id' => 2,
                'service_template_model_stm_id' => null,
                'command_command_id' => 2,
                'timeperiod_tp_id' => 1,
                'command_command_id2' => 1,
                'timeperiod_tp_id2' => 1,
                'service_description' => 'load-template',
                'service_alias' => 'load',
                'display_name' => null,
                'service_is_volatile' =>  2,
                'service_max_check_attempts' => 3,
                'service_normal_check_interval' => 5,
                'service_retry_check_interval' => 1,
                'service_active_checks_enabled' =>  1,
                'service_passive_checks_enabled' => 0,
                'initial_state' => null,
                'service_parallelize_check' => 2,
                'service_obsess_over_service' => 2,
                'service_check_freshness' => 2,
                'service_freshness_threshold' => null,
                'service_event_handler_enabled' => 2,
                'service_low_flap_threshold' => null,
                'service_high_flap_threshold' => null,
                'service_flap_detection_enabled' => 2,
                'service_process_perf_data' => 2,
                'service_retain_status_information' => 2,
                'service_retain_nonstatus_information' => 2,
                'service_notification_interval' => null,
                'service_notification_options' => null,
                'service_notifications_enabled' => 2,
                'contact_additive_inheritance' => 0,
                'cg_additive_inheritance' => 0,
                'service_inherit_contacts_from_host' => 1,
                'service_first_notification_delay' => null,
                'service_stalking_options' => null,
                'service_comment' => null,
                'command_command_id_arg' => null,
                'command_command_id_arg2' => null,
                'service_locked' => 0,
                'service_register' => 0,
                'service_activate' => 1,
                'organization_id' => 1,
                'environment_id' => null,
                'domain_id' => null
            )
        );
        $this->assertEquals($expectedResult, Servicetemplate::getList());
    }

    public function testGetListLimitOne()
    {
        $expectedResult = array(
            array(
                'service_id' => 1,
                'service_template_model_stm_id' => null,
                'command_command_id' => 2,
                'timeperiod_tp_id' => 1,
                'command_command_id2' => 1,
                'timeperiod_tp_id2' => 1,
                'service_description' => 'ping-template',
                'service_alias' => 'ping',
                'display_name' => null,
                'service_is_volatile' =>  2,
                'service_max_check_attempts' => 3,
                'service_normal_check_interval' => 5,
                'service_retry_check_interval' => 1,
                'service_active_checks_enabled' =>  1,
                'service_passive_checks_enabled' => 0,
                'initial_state' => null,
                'service_parallelize_check' => 2,
                'service_obsess_over_service' => 2,
                'service_check_freshness' => 2,
                'service_freshness_threshold' => null,
                'service_event_handler_enabled' => 2,
                'service_low_flap_threshold' => null,
                'service_high_flap_threshold' => null,
                'service_flap_detection_enabled' => 2,
                'service_process_perf_data' => 2,
                'service_retain_status_information' => 2,
                'service_retain_nonstatus_information' => 2,
                'service_notification_interval' => null,
                'service_notification_options' => null,
                'service_notifications_enabled' => 2,
                'contact_additive_inheritance' => 0,
                'cg_additive_inheritance' => 0,
                'service_inherit_contacts_from_host' => 1,
                'service_first_notification_delay' => null,
                'service_stalking_options' => null,
                'service_comment' => null,
                'command_command_id_arg' => null,
                'command_command_id_arg2' => null,
                'service_locked' => 0,
                'service_register' => 0,
                'service_activate' => 1,
                'organization_id' => 1,
                'environment_id' => null,
                'domain_id' => null
            )
        );
        $this->assertEquals($expectedResult, Servicetemplate::getList('*', 1));
    }

    public function testGetListSecondElementOnly()
    {
        $expectedResult = array(
            array(
                'service_id' => 2,
                'service_template_model_stm_id' => null,
                'command_command_id' => 2,
                'timeperiod_tp_id' => 1,
                'command_command_id2' => 1,
                'timeperiod_tp_id2' => 1,
                'service_description' => 'load-template',
                'service_alias' => 'load',
                'display_name' => null,
                'service_is_volatile' =>  2,
                'service_max_check_attempts' => 3,
                'service_normal_check_interval' => 5,
                'service_retry_check_interval' => 1,
                'service_active_checks_enabled' =>  1,
                'service_passive_checks_enabled' => 0,
                'initial_state' => null,
                'service_parallelize_check' => 2,
                'service_obsess_over_service' => 2,
                'service_check_freshness' => 2,
                'service_freshness_threshold' => null,
                'service_event_handler_enabled' => 2,
                'service_low_flap_threshold' => null,
                'service_high_flap_threshold' => null,
                'service_flap_detection_enabled' => 2,
                'service_process_perf_data' => 2,
                'service_retain_status_information' => 2,
                'service_retain_nonstatus_information' => 2,
                'service_notification_interval' => null,
                'service_notification_options' => null,
                'service_notifications_enabled' => 2,
                'contact_additive_inheritance' => 0,
                'cg_additive_inheritance' => 0,
                'service_inherit_contacts_from_host' => 1,
                'service_first_notification_delay' => null,
                'service_stalking_options' => null,
                'service_comment' => null,
                'command_command_id_arg' => null,
                'command_command_id_arg2' => null,
                'service_locked' => 0,
                'service_register' => 0,
                'service_activate' => 1,
                'organization_id' => 1,
                'environment_id' => null,
                'domain_id' => null
            )
        );
        $this->assertEquals($expectedResult, Servicetemplate::getList('*', 1, 1));

    }

    public function testGetListOneParameter()
    {
        $expectedResult = array(
            array('service_alias' => 'ping'),
            array('service_alias' => 'load')
        );
        $this->assertEquals($expectedResult, Servicetemplate::getList('service_alias'));
    }

    public function testGetListMultipleParameters()
    {
        $expectedResult = array(
            array('service_id' => 1, 'service_alias' => 'ping'),
            array('service_id' => 2, 'service_alias' => 'load')
        );
        $this->assertEquals($expectedResult, Servicetemplate::getList(array('service_id', 'service_alias')));
    }

    public function testGetListWithOrder()
    {
        $expectedResult = array(
            array('service_alias' => 'load'),
            array('service_alias' => 'ping')
        );
        $this->assertEquals(
            $expectedResult, 
            Servicetemplate::getList(
                'service_alias', 
                null, 
                null, 
                'service_id', 
                'DESC'
            )
        );
    }

    public function testGetListWithOneFilter()
    {
        $expectedResult = array(
            array('service_alias' => 'load')
        );
        $this->assertEquals(
            $expectedResult, 
            Servicetemplate::getList(
                'service_alias', 
                null, 
                null, 
                null, 
                null, 
                array(
                    'service_description' => 'load-template'
                )
            )
        );
    }

    public function testGetListWithMultipleFilters()
    {
        $expectedResult = array(
            array('service_alias' => 'ping')
        );
        $this->assertEquals(
            $expectedResult, 
            Servicetemplate::getList(
                'service_alias', 
                null, 
                null, 
                null, 
                null, 
                array(
                    'service_description' => 'ping-template',
                    'service_id' => 1
                )
            )
        );
    }

    public function testGetListWithFilterNoResult()
    {
        $expectedResult = array();
        $this->assertEquals(
            $expectedResult, 
            Servicetemplate::getList(
                'service_alias', 
                null, 
                null, 
                null, 
                null, 
                array(
                    'service_alias' => 'idontexist',
                )
            )

        );
    }

    public function testGetListBySearch()
    {
        $expectedResult = array(
            array('service_alias' => 'ping')
        );
        $this->assertEquals(
            $expectedResult, 
            Servicetemplate::getListBySearch(
                'service_alias', 
                null, 
                null, 
                null, 
                null,
                array('service_description' => 'ping-')
            )
        );
    }

    public function testGet()
    {
        $expectedResult = array(
            'service_id' => 2,
            'service_template_model_stm_id' => null,
            'command_command_id' => 2,
            'timeperiod_tp_id' => 1,
            'command_command_id2' => 1,
            'timeperiod_tp_id2' => 1,
            'service_description' => 'load-template',
            'service_alias' => 'load',
            'display_name' => null,
            'service_is_volatile' =>  2,
            'service_max_check_attempts' => 3,
            'service_normal_check_interval' => 5,
            'service_retry_check_interval' => 1,
            'service_active_checks_enabled' =>  1,
            'service_passive_checks_enabled' => 0,
            'initial_state' => null,
            'service_parallelize_check' => 2,
            'service_obsess_over_service' => 2,
            'service_check_freshness' => 2,
            'service_freshness_threshold' => null,
            'service_event_handler_enabled' => 2,
            'service_low_flap_threshold' => null,
            'service_high_flap_threshold' => null,
            'service_flap_detection_enabled' => 2,
            'service_process_perf_data' => 2,
            'service_retain_status_information' => 2,
            'service_retain_nonstatus_information' => 2,
            'service_notification_interval' => null,
            'service_notification_options' => null,
            'service_notifications_enabled' => 2,
            'contact_additive_inheritance' => 0,
            'cg_additive_inheritance' => 0,
            'service_inherit_contacts_from_host' => 1,
            'service_first_notification_delay' => null,
            'service_stalking_options' => null,
            'service_comment' => null,
            'command_command_id_arg' => null,
            'command_command_id_arg2' => null,
            'service_locked' => 0,
            'service_register' => 0,
            'service_activate' => 1,
            'organization_id' => 1,
            'environment_id' => null,
            'domain_id' => null
        );
        $this->assertEquals($expectedResult, Servicetemplate::get(2));
    }

    public function testGetWithOneParameter()
    {
        $expectedResult = array(
            'service_alias' => 'load'
        );
        $this->assertEquals($expectedResult, Servicetemplate::get(2, 'service_alias'));
    }

    public function testGetWithMultipleParameters()
    {
        $expectedResult = array(
            'service_description' => 'ping-template',
            'service_alias' => 'ping'
        );
        $this->assertEquals($expectedResult, Servicetemplate::get(1, array('service_description', 'service_alias')));
    }

    public function testGetWithUnknownId()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg
        );
        Servicetemplate::get(9999);
    }

    public function testGetIdByParameter()
    {
        $expectedResult = array(2);
        $this->assertEquals($expectedResult, Servicetemplate::getIdByParameter('service_alias', 'load'));
    }

    public function testGetMultipleIdsByParameters()
    {
        $expectedResult = array(1, 2);
        $this->assertEquals($expectedResult, Servicetemplate::getIdByParameter('service_alias', array('ping', 'load')));
    }

    public function testGetIdByParameterWithUnknownColumn()
    {
        $this->setExpectedException(
            'PDOException'
        );
        Servicetemplate::getIdByParameter('idontexist', array('ping'));
    }

    public function testGetPrimaryKey()
    {
        $this->assertEquals('service_id', Servicetemplate::getPrimaryKey());
    }

    public function testGetUniqueLabelField()
    {
        $this->assertEquals('service_description', Servicetemplate::getUniqueLabelField());
    }

    public function testGetTableName()
    {
        $this->assertEquals('cfg_services', Servicetemplate::getTableName());
    }

    public function testGetColumns()
    {
        $this->assertEquals(
            array(
                'service_id',
                'service_template_model_stm_id',
                'command_command_id',
                'timeperiod_tp_id',
                'command_command_id2',
                'timeperiod_tp_id2',
                'service_description',
                'service_alias',
                'display_name',
                'service_is_volatile',
                'service_max_check_attempts',
                'service_normal_check_interval',
                'service_retry_check_interval',
                'service_active_checks_enabled',
                'service_passive_checks_enabled',
                'initial_state',
                'service_parallelize_check',
                'service_obsess_over_service',
                'service_check_freshness',
                'service_freshness_threshold',
                'service_event_handler_enabled',
                'service_low_flap_threshold',
                'service_high_flap_threshold',
                'service_flap_detection_enabled',
                'service_process_perf_data',
                'service_retain_status_information',
                'service_retain_nonstatus_information',
                'service_notification_interval',
                'service_notification_options',
                'service_notifications_enabled',
                'contact_additive_inheritance',
                'cg_additive_inheritance',
                'service_inherit_contacts_from_host',
                'service_first_notification_delay',
                'service_stalking_options',
                'service_comment',
                'command_command_id_arg',
                'command_command_id_arg2',
                'service_locked',
                'service_register',
                'service_activate',
                'organization_id',
                'environment_id',
                'domain_id'
            ),
            Servicetemplate::getColumns()
        );
    }
}
