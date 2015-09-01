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

/**
 * Description of 20150617131647_fresh_install
 *
 * @author tmechouet
 */
use Phinx\Migration\AbstractMigration;

class FreshInstall extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     */
    public function change()
    {
        $cfg_acl_actions = $this->table('cfg_acl_actions', array('id' => false, 'primary_key' => 'acl_action_id'));
        $cfg_acl_actions
                ->addColumn('acl_action_id', 'integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('acl_action_name','string',array('limit' => 255, 'null' => true))
                ->addColumn('acl_action_description','string', array('limit' => 255,'null' => true))
                ->addColumn('acl_action_activate', 'string', array('limit' => 1, 'null' => true, 'default' => "1"))
                ->addColumn('organization_id','integer', array('signed' => false, 'null' => true))
                ->addForeignKey('organization_id', 'cfg_organizations', 'organization_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->create();    
        
        $cfg_acl_actions_rules = $this->table('cfg_acl_actions_rules', array('id' => false, 'primary_key' => 'aar_id'));
        $cfg_acl_actions_rules
                ->addColumn('aar_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('acl_action_rule_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('acl_action_name','string', array('limit' => 255, 'null' => true))
                ->addColumn('organization_id','integer', array('signed' => false, 'null' => true))
                ->addIndex(array('acl_action_rule_id'), array('unique' => false))
                ->addForeignKey('acl_action_rule_id', 'cfg_acl_actions', 'acl_action_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('organization_id', 'cfg_organizations', 'organization_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->create();
      
        $cfg_acl_group_actions_relations = $this->table('cfg_acl_group_actions_relations', array('id' => false, 'primary_key' => 'agar_id'));
        $cfg_acl_group_actions_relations
                ->addColumn('agar_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('acl_action_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('acl_group_id','integer', array('signed' => false, 'null' => true))
                ->addIndex(array('acl_action_id'), array('unique' => false))
                ->addIndex(array('acl_group_id'), array('unique' => false))
                ->create();

        $log_action = $this->table('log_action', array('id' => false, 'primary_key' => array('action_log_id')));
        $log_action
                ->addColumn('action_log_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('action_log_date','integer', array('signed' => false, 'null' => false))
                ->addColumn('object_type','string',array('limit' => 255, 'null' => false))
                ->addColumn('object_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('object_name','string',array('limit' => 255, 'null' => false))
                ->addColumn('action_type','string',array('limit' => 255, 'null' => false))
                ->addColumn('log_contact_id','integer', array('signed' => false, 'null' => true))
                ->addIndex(array('log_contact_id'), array('unique' => false))
                ->create();

        $log_action_modification = $this->table('log_action_modification', array('id' => false, 'primary_key' => 'modification_id')); 
        $log_action_modification
                ->addColumn('modification_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('field_name','string',array('limit' => 255, 'null' => false))
                ->addColumn('field_value','string',array('limit' => 255, 'null' => false))
                ->addColumn('action_log_id','integer',array('signed' => false, 'null' => false))
                ->addIndex(array('action_log_id'), array('unique' => false))
                ->create();
                
        $log_archive_host = $this->table('log_archive_host', array('id' => false, 'primary_key' => array('log_id')));
        $log_archive_host
                ->addColumn('log_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('host_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('UPTimeScheduled','integer', array('signed' => false, 'null' => true))
                ->addColumn('UPnbEvent','integer', array('signed' => false, 'null' => true))
                ->addColumn('UPTimeAverageAck','integer', array('signed' => false, 'null' => false))
                ->addColumn('UPTimeAverageRecovery','integer', array('signed' => false, 'null' => false))
                ->addColumn('DOWNTimeScheduled','integer', array('signed' => false, 'null' => true))
                ->addColumn('DOWNnbEvent','integer', array('signed' => false, 'null' => true))
                ->addColumn('DOWNTimeAverageAck','integer', array('signed' => false, 'null' => false))
                ->addColumn('DOWNTimeAverageRecovery','integer', array('signed' => false, 'null' => false))
                ->addColumn('UNREACHABLETimeScheduled','integer', array('signed' => false, 'null' => true))
                ->addColumn('UNREACHABLEnbEvent','integer', array('signed' => false, 'null' => true))
                ->addColumn('UNREACHABLETimeAverageAck','integer', array('signed' => false, 'null' => false))
                ->addColumn('UNREACHABLETimeAverageRecovery','integer', array('signed' => false, 'null' => false))
                ->addColumn('UNDETERMINEDTimeScheduled','integer', array('signed' => false, 'null' => true))
                ->addColumn('MaintenanceTime','integer', array('null' => true, 'signed' => false, "default" => 0))
                ->addColumn('date_end','integer', array('signed' => false, 'null' => true))
                ->addColumn('date_start','integer', array('signed' => false, 'null' => true))               
                ->addIndex(array('log_id'), array('unique' => true))
                ->addIndex(array('host_id'), array('unique' => false))
                ->addIndex(array('date_start'), array('unique' => false))
                ->addIndex(array('date_end'), array('unique' => false))
                ->create();

        $log_archive_last_status = $this->table('log_archive_last_status', array('id' => false, 'primary_key' => array('log_archive_last_status_id')));
        $log_archive_last_status
                ->addColumn('log_archive_last_status_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('host_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('service_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('host_name','string', array('limit' => 255, 'null' => true))
                ->addColumn('service_description','string', array('limit' => 255, 'null' => true))
                ->addColumn('status','string', array('limit' => 255, 'null' => true))
                ->addColumn('ctime','integer', array('null' => false))
                ->create();
        
        $log_archive_service = $this->table('log_archive_service', array('id' => false, 'primary_key' => array('log_id')));
        $log_archive_service
                ->addColumn('log_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('host_id','integer', array('null' => false, 'signed' => false, "default" => 0))
                ->addColumn('service_id','integer', array('null' => false, 'signed' => false, "default" => 0))
                ->addColumn('OKTimeScheduled','integer', array('null' => false, 'signed' => false, "default" => 0))
                ->addColumn('OKnbEvent','integer', array('null' => false, 'signed' => false, "default" => 0))
                ->addColumn('OKTimeAverageAck','integer', array('signed' => false, 'null' => false))
                ->addColumn('OKTimeAverageRecovery','integer', array('signed' => false, 'null' => false))
                ->addColumn('WARNINGTimeScheduled','integer', array('signed' => false, 'null' => false, "default" => 0))
                ->addColumn('WARNINGnbEvent','integer', array('signed' => false, 'null' => false, "default" => 0))
                ->addColumn('WARNINGTimeAverageAck','integer', array('signed' => false, 'null' => false))
                ->addColumn('WARNINGTimeAverageRecovery','integer', array('signed' => false, 'null' => false))
                ->addColumn('UNKNOWNTimeScheduled','integer', array('signed' => false, 'null' => false, "default" => 0))
                ->addColumn('UNKNOWNnbEvent','integer', array('signed' => false, 'null' => false, "default" => 0))
                ->addColumn('UNKNOWNTimeAverageAck','integer', array('signed' => false, 'null' => false))
                ->addColumn('UNKNOWNTimeAverageRecovery','integer', array('signed' => false, 'null' => false))
                ->addColumn('CRITICALTimeScheduled','integer', array('signed' => false, 'null' => false, "default" => 0))
                ->addColumn('CRITICALnbEvent','integer', array('signed' => false, 'null' => false, "default" => 0))
                ->addColumn('CRITICALTimeAverageAck','integer', array('signed' => false, 'null' => false))
                ->addColumn('CRITICALTimeAverageRecovery','integer', array('signed' => false, 'null' => false))
                ->addColumn('UNDETERMINEDTimeScheduled','integer', array('signed' => false, 'null' => false, "default" => 0))
                ->addColumn('MaintenanceTime','integer', array('signed' => false, 'null' => true, "default" => 0))
                ->addColumn('date_end','integer', array('signed' => false, 'null' => true))
                ->addColumn('date_start','integer', array('signed' => false, 'null' => true))               
                ->addIndex(array('host_id'), array('unique' => false))
                ->addIndex(array('service_id'), array('unique' => false))
                ->addIndex(array('date_start'), array('unique' => false))
                ->addIndex(array('date_end'), array('unique' => false))
                ->create();
        
        $rt_instances = $this->table('rt_instances', array('id' => false, 'primary_key' => 'instance_id'));
        $rt_instances
                ->addColumn('instance_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('name','string', array('limit' => 255, 'null' => false, "default" => "localhost"))
                ->addColumn('active_host_checks','integer', array('signed' => false, 'null' => true))
                ->addColumn('active_service_checks','integer', array('signed' => false, 'null' => true))
                ->addColumn('address','string', array('limit' => 128, 'null' => true))
                ->addColumn('check_hosts_freshness','integer', array('signed' => false, 'null' => true))
                ->addColumn('check_services_freshness','integer', array('signed' => false, 'null' => true))
                ->addColumn('daemon_mode','integer', array('signed' => false, 'null' => true))
                ->addColumn('description','string', array('limit' => 128, 'null' => true))
                ->addColumn('end_time','integer', array('signed' => false, 'null' => true))
                ->addColumn('engine','string', array('limit' => 64, 'null' => true))
                ->addColumn('event_handlers','integer', array('signed' => false, 'null' => true))
                ->addColumn('failure_prediction','integer', array('signed' => false, 'null' => true))
                ->addColumn('flap_detection','integer', array('signed' => false, 'null' => true))
                ->addColumn('global_host_event_handler','text', array('null' => true))
                ->addColumn('global_service_event_handler','text', array('null' => true))
                ->addColumn('last_alive','integer', array('signed' => false, 'null' => true))
                ->addColumn('last_command_check','integer', array('signed' => false, 'null' => true))
                ->addColumn('last_log_rotation','integer', array('signed' => false, 'null' => true))
                ->addColumn('modified_host_attributes','integer', array('signed' => false, 'null' => true))
                ->addColumn('modified_service_attributes','integer', array('signed' => false, 'null' => true))
                ->addColumn('notifications','integer', array('signed' => false, 'null' => true))
                ->addColumn('obsess_over_hosts','integer', array('signed' => false, 'null' => true))
                ->addColumn('obsess_over_services','integer', array('signed' => false, 'null' => true))
                ->addColumn('passive_host_checks','integer', array('signed' => false, 'null' => true))
                ->addColumn('passive_service_checks','integer', array('signed' => false, 'null' => true))
                ->addColumn('pid','integer', array('signed' => false, 'null' => true))
                ->addColumn('process_perfdata','integer', array('signed' => false, 'null' => true))
                ->addColumn('running','integer', array('signed' => false, 'null' => true))
                ->addColumn('start_time','integer', array('signed' => false, 'null' => true))
                ->addColumn('version','integer', array('signed' => false, 'null' => true))
                ->addColumn('deleted','integer', array('signed' => false, 'null' => true))
                ->addColumn('outdated','integer', array('signed' => false, 'null' => true))
                ->create();
        
        
        $rt_hosts = $this->table('rt_hosts', array('id' => false, 'primary_key' => array('host_id')));
        $rt_hosts
                ->addColumn('host_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('name','string', array('limit' => 255, 'null' => false))
                ->addColumn('instance_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('acknowledged','integer', array('signed' => false, 'null' => true))
                ->addColumn('acknowledgement_type','integer', array('signed' => false, 'null' => true))
                ->addColumn('action_url','string', array('limit' => 255, 'null' => true))
                ->addColumn('active_checks','integer', array('signed' => false, 'null' => true))
                ->addColumn('address','string', array('limit' => 75, 'null' => true))
                ->addColumn('alias','string', array('limit' => 100, 'null' => true))
                ->addColumn('check_attempt','integer', array('signed' => false, 'null' => true))
                ->addColumn('check_command','text', array('null' => true))
                ->addColumn('check_freshness','integer', array('signed' => false, 'null' => true))
                ->addColumn('check_interval','integer', array('signed' => false, 'null' => true))
                ->addColumn('check_period','string', array('limit' => 75, 'null' => true))
                ->addColumn('check_type','integer', array('signed' => false, 'null' => true))
                ->addColumn('checked','integer', array('signed' => false, 'null' => true))
                ->addColumn('command_line','string', array('limit' => 75, 'null' => true))
                ->addColumn('default_active_checks','integer', array('signed' => false, 'null' => true))
                ->addColumn('default_event_handler_enabled','integer', array('signed' => false, 'null' => true))
                ->addColumn('default_failure_prediction','integer', array('signed' => false, 'null' => true))
                ->addColumn('default_flap_detection','integer', array('signed' => false, 'null' => true))
                ->addColumn('default_notify','integer', array('signed' => false, 'null' => true))
                ->addColumn('default_passive_checks','integer', array('signed' => false, 'null' => true))
                ->addColumn('default_process_perfdata','integer', array('signed' => false, 'null' => true))
                ->addColumn('check_pdisplay_nameeriod','string', array('limit' => 100, 'null' => true))
                ->addColumn('enabled','integer', array('signed' => false, 'null' => true))
                ->addColumn('event_handler','string', array('limit' => 255, 'null' => true))
                ->addColumn('event_handler_enabled','integer', array('signed' => false, 'null' => true))
                ->addColumn('execution_time','integer', array('signed' => false, 'null' => true))
                ->addColumn('failure_prediction','integer', array('signed' => false, 'null' => true))
                ->addColumn('first_notification_delay','integer', array('signed' => false, 'null' => true))
                ->addColumn('flap_detection','integer', array('signed' => false, 'null' => true))
                ->addColumn('flap_detection_on_down','integer', array('signed' => false, 'null' => true))
                ->addColumn('flap_detection_on_unreachable','integer', array('signed' => false, 'null' => true))
                ->addColumn('flap_detection_on_up','integer', array('signed' => false, 'null' => true))
                ->addColumn('flapping','integer', array('signed' => false, 'null' => true))
                ->addColumn('freshness_threshold','integer', array('signed' => false, 'null' => true))
                ->addColumn('high_flap_threshold','integer', array('signed' => false, 'null' => true))
                ->addColumn('icon_image','string', array('limit' => 255, 'null' => true))
                ->addColumn('icon_image_alt','string', array('limit' => 255, 'null' => true))
                ->addColumn('last_check','integer', array('signed' => false, 'null' => true))
                ->addColumn('last_hard_state','integer', array('signed' => false, 'null' => true))
                ->addColumn('last_hard_state_change','integer', array('signed' => false, 'null' => true))
                ->addColumn('last_notification','integer', array('signed' => false, 'null' => true))
                ->addColumn('last_state_change','integer', array('signed' => false, 'null' => true))
                ->addColumn('last_time_down','integer', array('signed' => false, 'null' => true))
                ->addColumn('last_time_unreachable','integer', array('signed' => false, 'null' => true))
                ->addColumn('last_time_up','integer', array('signed' => false, 'null' => true))
                ->addColumn('last_update','integer', array('signed' => false, 'null' => true))
                ->addColumn('latency','integer', array('signed' => false, 'null' => true))
                ->addColumn('low_flap_threshold','integer', array('signed' => false, 'null' => true))
                ->addColumn('max_check_attempts','integer', array('signed' => false, 'null' => true))
                ->addColumn('modified_attributes','integer', array('signed' => false, 'null' => true))
                ->addColumn('next_check','integer', array('signed' => false, 'null' => true))
                ->addColumn('next_host_notification','integer', array('signed' => false, 'null' => true))
                ->addColumn('no_more_notifications','integer', array('signed' => false, 'null' => true))
                ->addColumn('notes','integer', array('signed' => false, 'null' => true))
                ->addColumn('notes_url','string', array('limit' => 255, 'null' => true))
                ->addColumn('notification_interval','integer', array('signed' => false, 'null' => true))
                ->addColumn('notification_number','integer', array('signed' => false, 'null' => true))
                ->addColumn('notification_period','string', array('limit' => 75, 'null' => true))
                ->addColumn('notify','integer', array('signed' => false, 'null' => true))
                ->addColumn('notify_on_down','integer', array('signed' => false, 'null' => true))
                ->addColumn('notify_on_downtime','integer', array('signed' => false, 'null' => true))
                ->addColumn('notify_on_flapping','integer', array('signed' => false, 'null' => true))
                ->addColumn('notify_on_recovery','integer', array('signed' => false, 'null' => true))
                ->addColumn('notify_on_unreachable','integer', array('signed' => false, 'null' => true))
                ->addColumn('obsess_over_host','integer', array('signed' => false, 'null' => true))
                ->addColumn('output','text', array('null' => true))
                ->addColumn('passive_checks','integer', array('signed' => false, 'null' => true))
                ->addColumn('percent_state_change','integer', array('signed' => false, 'null' => true))
                ->addColumn('perfdata','text', array('null' => true))
                ->addColumn('process_perfdata','integer', array('signed' => false, 'null' => true))
                ->addColumn('real_state','integer', array('signed' => false, 'null' => true))
                ->addColumn('retain_nonstatus_information','integer', array('signed' => false, 'null' => true))
                ->addColumn('retain_status_information','integer', array('signed' => false, 'null' => true))
                ->addColumn('retry_interval','integer', array('signed' => false, 'null' => true))
                ->addColumn('scheduled_downtime_depth','integer', array('signed' => false, 'null' => true))
                ->addColumn('should_be_scheduled','integer', array('signed' => false, 'null' => true))
                ->addColumn('stalk_on_down','integer', array('signed' => false, 'null' => true))
                ->addColumn('stalk_on_unreachable','integer', array('signed' => false, 'null' => true))
                ->addColumn('stalk_on_up','integer', array('signed' => false, 'null' => true))
                ->addColumn('state','integer', array('signed' => false, 'null' => true))
                ->addColumn('state_type','integer', array('signed' => false, 'null' => true))
                ->addColumn('statusmap_image','string', array('limit' => 255, 'null' => true))
                ->addForeignKey('instance_id', 'rt_instances', 'instance_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('host_id'), array('unique' => false))
                ->addIndex(array('instance_id'), array('unique' => false))
                ->addIndex(array('name'), array('unique' => false))
                ->create();

        $log_logs = $this->table('log_logs', array('id' => false, 'primary_key' => array('log_id')));
        $log_logs
                ->addColumn('log_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('ctime','integer', array('signed' => false, 'null' => true))
                ->addColumn('host_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('host_name','string', array('limit' => 255, 'null' => true))
                ->addColumn('instance_name','string', array('limit' => 255, 'null' => false))
                ->addColumn('issue_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('msg_type','integer', array('signed' => false, 'limit' => 255, 'null' => false))
                ->addColumn('notification_cmd','string', array('limit' => 255, 'null' => true))
                ->addColumn('notification_contact','string', array('limit' => 255, 'null' => true))
                ->addColumn('output','text', array('null' => true))
                ->addColumn('retry','integer', array('signed' => false, 'null' => true))
                ->addColumn('service_description','string', array('limit' => 255, 'null' => true))
                ->addColumn('service_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('status','integer', array('signed' => false, 'limit' => 255, 'null' => true))
                ->addColumn('type','integer', array('signed' => false, 'null' => true))
                ->addIndex(array('host_name'), array('unique' => false))
                ->addIndex(array('service_description'), array('unique' => false))
                ->addIndex(array('status'), array('unique' => false))
                ->addIndex(array('instance_name'), array('unique' => false))
                ->addIndex(array('ctime'), array('unique' => false))
                ->addIndex(array('host_id', 'service_id', 'msg_type', 'status', 'ctime'), array('unique' => false))
                ->addIndex(array('host_id', 'msg_type', 'status', 'ctime'), array('unique' => false))
                ->addIndex(array('host_id', 'msg_type', 'status', 'ctime'), array('unique' => false))
                ->create();
        
        $log_snmptt = $this->table('log_snmptt', array('id' => false, 'primary_key' => array('trap_id')));
        $log_snmptt
                ->addColumn('trap_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('trap_oid','text', array('null' => false))
                ->addColumn('trap_ip','string', array('limit' => 50, 'null' => true))
                ->addColumn('trap_community','string', array('limit' => 50, 'null' => true))
                ->addColumn('trap_infos','text', array('null' => false))
                ->create();
                
        $log_traps = $this->table('log_traps', array('id' => false, 'primary_key' => array('trap_id')));
        $log_traps
                ->addColumn('trap_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('trap_time','integer', array('signed' => false, 'null' => true))
                ->addColumn('timeout','string', array('limit' => 50, 'null' => true))
                ->addColumn('host_name','string', array('limit' => 255, 'null' => true))
                ->addColumn('ip_address','string', array('limit' => 255, 'null' => true))
                ->addColumn('agent_host_name','string', array('limit' => 255, 'null' => true))
                ->addColumn('agent_ip_address','string', array('limit' => 255, 'null' => true))
                ->addColumn('trap_oid','string', array('limit' => 512, 'null' => true))
                ->addColumn('trap_name','string', array('limit' => 255, 'null' => true))
                ->addColumn('vendor','string', array('limit' => 255, 'null' => true))
                ->addColumn('status','integer', array('signed' => false, 'null' => true))
                ->addColumn('severity_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('severity_name','string', array('limit' => 255, 'null' => true))
                ->addColumn('output_message','string', array('limit' => 2048, 'null' => true))
                ->addIndex(array('trap_id'), array('unique' => false))
                ->addIndex(array('trap_time'), array('unique' => false))
                ->create();
              
        $log_traps = $this->table('log_traps_args', array('id' => false, 'primary_key' => array('fk_log_traps')));
        $log_traps
                ->addColumn('fk_log_traps','integer', array('signed' => false, 'null' => false))
                ->addColumn('arg_number','integer', array('signed' => false, 'null' => true))
                ->addColumn('arg_oid','string', array('limit' => 255, 'null' => true))
                ->addColumn('arg_value','string', array('limit' => 255, 'null' => true))
                ->addColumn('trap_time','integer', array('signed' => false, 'null' => true))
                ->addIndex(array('fk_log_traps'), array('unique' => false))
                ->create();

        $rt_acl = $this->table('rt_acl', array('id' => false, 'primary_key' => array('acl_id')));
        $rt_acl
                ->addColumn('acl_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('host_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('host_name','string', array('limit' => 255, 'null' => true))
                ->addColumn('service_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('service_description','string', array('limit' => 255, 'null' => true))
                ->addColumn('group_id','integer', array('signed' => false, 'null' => false))
                ->addIndex(array('host_name'), array('unique' => false))
                ->addIndex(array('service_description'), array('unique' => false))
                ->addIndex(array('host_name', 'service_description', 'group_id'), array('unique' => true))
                ->addIndex(array('host_id', 'service_id', 'group_id'), array('unique' => true))
                ->addIndex(array('host_name', 'group_id'), array('unique' => true))
                ->create();
        
        $rt_downtimes = $this->table('rt_downtimes', array('id' => false, 'primary_key' => array('downtime_id')));
        $rt_downtimes
                ->addColumn('downtime_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('entry_time','integer', array('signed' => false, 'null' => false))
                ->addColumn('host_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('service_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('author','string', array('limit' => 64, 'null' => true))
                ->addColumn('cancelled','integer', array('signed' => false, 'null' => true))
                ->addColumn('comment_data','text', array('signed' => false, 'null' => true))                
                ->addColumn('deletion_time','integer', array('signed' => false, 'null' => true))
                ->addColumn('duration','integer', array('signed' => false, 'null' => false))
                ->addColumn('end_time','integer', array('signed' => false, 'null' => false))
                ->addColumn('fixed','integer', array('signed' => false, 'null' => false))
                ->addColumn('instance_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('internal_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('start_time','integer', array('signed' => false, 'null' => false))
                ->addColumn('actual_start_time','integer', array('signed' => false, 'null' => false))
                ->addColumn('actual_end_time','integer', array('signed' => false, 'null' => false))
                ->addColumn('started','integer', array('signed' => false, 'null' => false))
                ->addColumn('triggered_by','integer', array('signed' => false, 'null' => false))
                ->addColumn('type','integer', array('signed' => false, 'null' => false))
                ->addColumn('is_recurring','integer', array('signed' => false, 'null' => false))
                ->addColumn('recurring_interval','integer', array('signed' => false, 'null' => false))
                ->addColumn('recurring_timeperiod','string', array('limit' => 200, 'null' => true))
                ->addForeignKey('host_id', 'rt_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('instance_id', 'rt_instances', 'instance_id', array('delete'=> 'SET_NULL', 'update'=> 'RESTRICT'))
                ->addIndex(array('entry_time', 'host_id', 'service_id'), array('unique' => true))
                ->addIndex(array('host_id'), array('unique' => false))
                ->addIndex(array('instance_id'), array('unique' => false))
                ->addIndex(array('entry_time'), array('unique' => false))
                ->addIndex(array('host_id', 'start_time'), array('unique' => true))
                ->create();
        
        $rt_eventhandlers = $this->table('rt_eventhandlers', array('id' => false, 'primary_key' => array('eventhandler_id')));
        $rt_eventhandlers
                ->addColumn('eventhandler_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('host_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('service_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('start_time','integer', array('signed' => false, 'null' => true))
                ->addColumn('command_args','string', array('limit' => 255, 'null' => true))
                ->addColumn('command_line','string', array('limit' => 255, 'null' => true))
                ->addColumn('early_timeout','integer', array('signed' => false, 'null' => true))
                ->addColumn('end_time','integer', array('signed' => false, 'null' => true))
                ->addColumn('execution_time','integer', array('signed' => false, 'null' => true))
                ->addColumn('output','string', array('limit' => 255, 'null' => true))
                ->addColumn('return_code','integer', array('signed' => false, 'null' => true))
                ->addColumn('state','text', array('signed' => false, 'null' => true))                
                ->addColumn('state_type','integer', array('signed' => false, 'null' => true))
                ->addColumn('timeout','integer', array('signed' => false, 'null' => false))
                ->addColumn('type','integer', array('signed' => false, 'null' => false))
                ->addForeignKey('host_id', 'rt_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('host_id', 'service_id', 'start_time'), array('unique' => true))
                ->create();

        $rt_flappingstatuses = $this->table('rt_flappingstatuses', array('id' => false, 'primary_key' => array('flappingstatus_id')));
        $rt_flappingstatuses
                ->addColumn('flappingstatus_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('host_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('service_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('event_time','integer', array('signed' => false, 'null' => true))
                ->addColumn('comment_time','integer', array('signed' => false, 'null' => true))
                ->addColumn('event_type','integer', array('signed' => false, 'null' => true))
                ->addColumn('high_threshold','integer', array('signed' => false, 'null' => true))
                ->addColumn('internal_comment_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('low_threshold','integer', array('signed' => false, 'null' => true))
                ->addColumn('percent_state_change','integer', array('signed' => false, 'null' => true))
                ->addColumn('reason_type','integer', array('signed' => false, 'null' => true))
                ->addColumn('type','integer', array('signed' => false, 'null' => true))
                ->addForeignKey('host_id', 'rt_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('host_id', 'service_id', 'event_time'), array('unique' => true))
                ->create();

        $rt_comments = $this->table('rt_comments', array('id' => false, 'primary_key' => array('comment_id')));
        $rt_comments
                ->addColumn('comment_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('entry_time','integer', array('signed' => false, 'null' => false))
                ->addColumn('host_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('service_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('author','string', array('limit' => 64, 'null' => true))
                ->addColumn('data','text', array('null' => true))
                ->addColumn('deletion_time','integer', array('signed' => false, 'null' => false))
                ->addColumn('entry_type','integer', array('signed' => false, 'null' => false))
                ->addColumn('expire_time','integer', array('signed' => false, 'null' => false))
                ->addColumn('expires','integer', array('signed' => false, 'null' => false))
                ->addColumn('instance_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('internal_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('persistent','integer', array('signed' => false, 'null' => false))
                ->addColumn('source','integer', array('signed' => false, 'null' => false))
                ->addColumn('type','integer', array('signed' => false, 'null' => false))
                ->addForeignKey('host_id', 'rt_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('instance_id', 'rt_instances', 'instance_id', array('delete'=> 'SET_NULL', 'update'=> 'RESTRICT'))
                ->addIndex(array('entry_time', 'host_id', 'service_id'), array('unique' => true))
                ->addIndex(array('internal_id'), array('unique' => false))
                ->addIndex(array('host_id'), array('unique' => false))
                ->addIndex(array('instance_id'), array('unique' => false))
                ->create();
               
        $rt_acknowledgements = $this->table('rt_acknowledgements', array('id' => false, 'primary_key' => array('acknowledgement_id')));
        $rt_acknowledgements
                ->addColumn('acknowledgement_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('entry_time','integer', array('signed' => false, 'null' => false))
                ->addColumn('host_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('service_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('author','string', array('limit' => 64, 'null' => true))
                ->addColumn('comment_data','string', array('limit' => 255, 'null' => true))
                ->addColumn('deletion_time','integer', array('signed' => false, 'null' => true))
                ->addColumn('instance_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('notify_contacts','integer', array('signed' => false, 'null' => true))
                ->addColumn('persistent_comment','integer', array('signed' => false, 'null' => true))
                ->addColumn('state','integer', array('signed' => false, 'null' => true))
                ->addColumn('sticky','integer', array('signed' => false, 'null' => true))
                ->addColumn('type','integer', array('signed' => false, 'null' => true))
                ->addForeignKey('host_id', 'rt_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('instance_id', 'rt_instances', 'instance_id', array('delete'=> 'SET_NULL', 'update'=> 'RESTRICT'))
                ->addIndex(array('entry_time', 'host_id', 'service_id'), array('unique' => true))
                ->addIndex(array('host_id'), array('unique' => false))
                ->addIndex(array('instance_id'), array('unique' => false))
                ->addIndex(array('entry_time'), array('unique' => false))
                ->create();
        
        $rt_services = $this->table('rt_services', array('id' => false, 'primary_key' => array('host_id', 'service_id')));
        $rt_services
                ->addColumn('host_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('description','string', array('limit' => 255, 'null' => false))
                ->addColumn('service_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('acknowledged','integer', array('signed' => false, 'null' => true))
                ->addColumn('acknowledgement_type','integer', array('signed' => false, 'null' => true))
                ->addColumn('action_url','string', array('limit' => 255, 'null' => true))
                ->addColumn('active_checks','integer', array('signed' => false, 'null' => true))
                ->addColumn('check_attempt','integer', array('signed' => false, 'null' => true))
                ->addColumn('check_command','text', array('null' => true))
                ->addColumn('check_freshness','integer', array('signed' => false, 'null' => true))
                ->addColumn('check_interval', 'float', array('null' => true))
                ->addColumn('check_period','string', array('limit' => 75, 'null' => true))
                ->addColumn('check_type','integer', array('signed' => false, 'null' => true))
                ->addColumn('checked','integer', array('signed' => false, 'null' => true))
                ->addColumn('command_line','text', array('null' => true))
                ->addColumn('default_active_checks','integer', array('signed' => false, 'null' => true))
                ->addColumn('default_event_handler_enabled','integer', array('signed' => false, 'null' => true))
                ->addColumn('default_failure_prediction','integer', array('signed' => false, 'null' => true))
                ->addColumn('default_flap_detection','integer', array('signed' => false, 'null' => true))
                ->addColumn('default_notify','integer', array('signed' => false, 'null' => true))
                ->addColumn('default_passive_checks','integer', array('signed' => false, 'null' => true))
                ->addColumn('default_process_perfdata','integer', array('signed' => false, 'null' => true))
                ->addColumn('display_name','string', array('limit' => 160, 'null' => true))
                ->addColumn('enabled','integer', array('signed' => false, 'null' => true, 'default' => 1))
                ->addColumn('event_handler','string', array('limit' => 255, 'null' => true))
                ->addColumn('event_handler_enabled','integer', array('null' => true))
                ->addColumn('execution_time', 'float', array('null' => true))
                ->addColumn('failure_prediction','integer', array('signed' => false, 'null' => true))
                ->addColumn('failure_prediction_options','string', array('limit' => 64, 'null' => true))
                ->addColumn('first_notification_delay', 'float', array('null' => true))
                ->addColumn('flap_detection','integer', array('signed' => false, 'null' => true))
                ->addColumn('flap_detection_on_critical','integer', array('signed' => false, 'null' => true))
                ->addColumn('flap_detection_on_ok','integer', array('signed' => false, 'null' => true))
                ->addColumn('flap_detection_on_unknown','integer', array('signed' => false, 'null' => true))
                ->addColumn('flap_detection_on_warning','integer', array('signed' => false, 'null' => true))
                ->addColumn('flapping','integer', array('signed' => false, 'null' => true))
                ->addColumn('freshness_threshold', 'float', array('null' => true))
                ->addColumn('high_flap_threshold', 'float', array('null' => true))
                ->addColumn('icon_image','string', array('limit' => 255, 'null' => true))
                ->addColumn('icon_image_alt','string', array('limit' => 255, 'null' => true))
                ->addColumn('last_check','integer', array('signed' => false, 'null' => true))
                ->addColumn('last_hard_state','integer', array('signed' => false, 'null' => true))
                ->addColumn('last_hard_state_change','integer', array('signed' => false, 'null' => true))
                ->addColumn('last_notification','integer', array('signed' => false, 'null' => true))
                ->addColumn('last_state_change','integer', array('signed' => false, 'null' => true))
                ->addColumn('last_time_critical','integer', array('signed' => false, 'null' => true))
                ->addColumn('last_time_ok','integer', array('signed' => false, 'null' => true))
                ->addColumn('last_time_unknown','integer', array('signed' => false, 'null' => true))
                ->addColumn('last_time_warning','integer', array('signed' => false, 'null' => true))
                ->addColumn('last_update','integer', array('signed' => false, 'null' => true))
                ->addColumn('latency', 'float', array('null' => true))
                ->addColumn('low_flap_threshold', 'float', array('null' => true))
                ->addColumn('max_check_attempts','integer', array('signed' => false, 'null' => true))
                ->addColumn('modified_attributes','integer', array('signed' => false, 'null' => true))
                ->addColumn('next_check','integer', array('signed' => false, 'null' => true))
                ->addColumn('next_notification','integer', array('signed' => false, 'null' => true))
                ->addColumn('no_more_notifications','integer', array('signed' => false, 'null' => true))
                ->addColumn('notes','string', array('limit' => 255, 'null' => true))
                ->addColumn('notes_url','string', array('limit' => 255, 'null' => true))
                ->addColumn('notification_interval', 'float', array('null' => true))
                ->addColumn('notification_number', 'float', array('null' => true))
                ->addColumn('notification_period','string', array('limit' => 75, 'null' => true))
                ->addColumn('notify','boolean', array('null' => true))
                ->addColumn('notify_on_critical','boolean', array('null' => true))
                ->addColumn('notify_on_downtime','boolean', array('null' => true))
                ->addColumn('notify_on_flapping','boolean', array('null' => true))
                ->addColumn('notify_on_recovery','boolean', array('null' => true))
                ->addColumn('notify_on_unknown','boolean', array('null' => true))
                ->addColumn('notify_on_warning','boolean', array('null' => true))
                ->addColumn('obsess_over_service','boolean', array('null' => true))
                ->addColumn('output','text', array('null' => true))
                ->addColumn('passive_checks','boolean', array('null' => true))
                ->addColumn('percent_state_change', 'float', array('null' => true))
                ->addColumn('perfdata','text', array('null' => true))
                ->addColumn('process_perfdata','boolean', array('null' => true))
                ->addColumn('real_state','integer', array('signed' => false, 'null' => true))
                ->addColumn('retain_nonstatus_information','boolean', array('null' => true))
                ->addColumn('retain_status_information','boolean', array('null' => true))
                ->addColumn('retry_interval', 'float', array('null' => true))
                ->addColumn('scheduled_downtime_depth','integer', array('signed' => false, 'null' => true))
                ->addColumn('should_be_scheduled','boolean', array('null' => true))
                ->addColumn('stalk_on_critical','boolean', array('null' => true))
                ->addColumn('stalk_on_ok','boolean', array('null' => true))
                ->addColumn('stalk_on_unknown','boolean', array('null' => true))
                ->addColumn('stalk_on_warning','boolean', array('null' => true))
                ->addColumn('state','integer', array('signed' => false, 'null' => true))
                ->addColumn('state_type','integer', array('signed' => false, 'null' => true))
                ->addColumn('volatile','boolean', array('null' => true))
                ->addForeignKey('host_id', 'rt_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('host_id', 'service_id'), array('unique' => true))
                ->addIndex(array('service_id'), array('unique' => false))
                ->addIndex(array('description'), array('unique' => false))
                ->create();
              
        $rt_hosts_hosts_dependencies = $this->table('rt_hosts_hosts_dependencies', array('id' => false, 'primary_key' => array('dependent_host_id', 'host_id')));
        $rt_hosts_hosts_dependencies
                ->addColumn('dependent_host_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('host_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('dependency_period','string', array('limit' => 75, 'null' => true))
                ->addColumn('execution_failure_options','string', array('limit' => 15, 'null' => true))
                ->addColumn('inherits_parent','integer', array('signed' => false, 'null' => false))
                ->addColumn('notification_failure_options','string', array('limit' => 15, 'null' => true))
                ->addForeignKey('host_id', 'rt_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('dependent_host_id', 'rt_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('dependent_host_id', 'host_id'), array('unique' => true))
                ->addIndex(array('host_id'), array('unique' => false))
                ->create();
            
        $rt_hoststateevents = $this->table('rt_hoststateevents', array('id' => false, 'primary_key' => array('hoststateevent_id')));
        $rt_hoststateevents
                ->addColumn('hoststateevent_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('end_time','integer', array('signed' => false, 'null' => true))
                ->addColumn('host_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('start_time','integer', array('signed' => false, 'null' => false))
                ->addColumn('state','integer', array('signed' => false, 'limit' => 255, 'null' => true))
                ->addColumn('last_update','integer', array('signed' => false, 'limit' => 255, 'null' => true))
                ->addColumn('in_downtime','integer', array('signed' => false, 'limit' => 255, 'null' => true))
                ->addColumn('ack_time','integer', array('signed' => false, 'null' => true))
                ->addIndex(array('host_id', 'start_time'), array('unique' => true))
                ->addIndex(array('start_time'), array('unique' => false))
                ->create();

        $rt_hosts_hosts_parents = $this->table('rt_hosts_hosts_parents', array('id' => false, 'primary_key' => array('child_id', 'parent_id')));
        $rt_hosts_hosts_parents->addColumn('child_id','integer', array('identity' => false, 'signed' => false, 'null' => false))
                ->addColumn('parent_id','integer', array('identity' => false, 'signed' => false, 'null' => false))
                ->addForeignKey('parent_id', 'rt_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('child_id', 'rt_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('child_id', 'parent_id'), array('unique' => true))
                ->addIndex(array('child_id'), array('unique' => false))
                ->addIndex(array('parent_id'), array('unique' => false))
                ->create();
                
        $rt_index_data = $this->table('rt_index_data', array('id' => false, 'primary_key' => 'id'));
        $rt_index_data
                ->addColumn('id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('host_name','string', array('limit' => 255, 'null' => true))
                ->addColumn('host_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('service_description','string', array('limit' => 255, 'null' => true))
                ->addColumn('service_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('check_interval','integer', array('signed' => false, 'null' => true))
                ->addColumn('special','string', array('limit' => 1, 'null' => true, "default" => "0"))
                ->addColumn('hidden','string', array('limit' => 1, 'null' => true, "default" => "0"))
                ->addColumn('locked','string', array('limit' => 1, 'null' => true, "default" => "0"))
                ->addColumn('trashed','string', array('limit' => 1, 'null' => true, "default" => "0"))
                ->addColumn('must_be_rebuild','string', array('limit' => 1, 'null' => true, "default" => "0"))
                ->addColumn('storage_type','string', array('limit' => 1, 'null' => true, "default" => "2"))
                ->addColumn('to_delete','integer', array('signed' => false, 'null' => true, "default" => "0"))
                ->addColumn('rrd_retention','integer', array('signed' => false, 'null' => true))
                ->addIndex(array('host_id', 'service_id'), array('unique' => true))
                ->addIndex(array('host_name'), array('unique' => false))
                ->addIndex(array('service_description'), array('unique' => false))
                ->addIndex(array('host_id'), array('unique' => false))
                ->addIndex(array('service_id'), array('unique' => false))
                ->addIndex(array('must_be_rebuild'), array('unique' => false))
                ->addIndex(array('trashed'), array('unique' => false))
                ->create();
        
        $rt_issues = $this->table('rt_issues', array('id' => false, 'primary_key' => 'issue_id'));
        $rt_issues
                ->addColumn('issue_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('host_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('service_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('start_time','integer', array('signed' => false, 'null' => false))
                ->addColumn('ack_time','integer', array('signed' => false, 'null' => true))
                ->addColumn('end_time','integer', array('signed' => false, 'null' => true))
                ->addForeignKey('host_id', 'rt_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('host_id', 'service_id', 'start_time'), array('unique' => true))
                ->addIndex(array('start_time'), array('unique' => false))
                ->create();
                
        $rt_issues_issues_parents = $this->table('rt_issues_issues_parents', array('id' => false, 'primary_key' => 'child_id'));
        $rt_issues_issues_parents
                ->addColumn('child_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('end_time','integer', array('signed' => false, 'null' => true))
                ->addColumn('start_time','integer', array('signed' => false, 'null' => false))
                ->addColumn('parent_id','integer', array('signed' => false, 'null' => false))
                ->addForeignKey('child_id', 'rt_issues', 'issue_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('parent_id', 'rt_issues', 'issue_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('child_id'), array('unique' => false))
                ->addIndex(array('parent_id'), array('unique' => false))
                ->create();       
        
        $rt_metrics = $this->table('rt_metrics', array('id' => false, 'primary_key' => 'metric_id'));
        $rt_metrics
                ->addColumn('metric_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('index_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('metric_name','string', array('limit' => 255, 'null' => true))
                ->addColumn('data_source_type','string', array('limit' => 1, 'null' => true))
                ->addColumn('unit_name','string', array('limit' => 32, 'null' => true))
                ->addColumn('current_value','float', array('null' => true))
                ->addColumn('warn','float', array('null' => true))
                ->addColumn('warn_low','float', array('null' => true))
                ->addColumn('warn_threshold_mode','string', array('limit' => 1, 'null' => true))
                ->addColumn('crit','float', array('null' => true))
                ->addColumn('crit_low','float', array('null' => true))
                ->addColumn('crit_threshold_mode','string', array('limit' => 1, 'null' => true))
                ->addColumn('hidden','string', array('limit' => 1, 'null' => true, "default" => 0))
                ->addColumn('min','float', array('null' => true))
                ->addColumn('max','float', array('null' => true))
                ->addColumn('locked','string', array('limit' => 1, 'null' => true))
                ->addColumn('to_delete','integer', array('signed' => false, 'null' => false))
                ->addIndex(array('index_id', 'metric_name'), array('unique' => true))
                ->addIndex(array('index_id'), array('unique' => false))
                ->create();
        
        $rt_modules = $this->table('rt_modules', array('id' => false, 'primary_key' => 'module_id'));
        $rt_modules
                ->addColumn('module_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('instance_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('args','string', array('limit' => 255, 'null' => true))
                ->addColumn('filename','string', array('limit' => 255, 'null' => true))
                ->addColumn('loaded','integer', array('signed' => false, 'null' => true))
                ->addColumn('should_be_loaded','integer', array('signed' => false, 'null' => true))
                ->addForeignKey('instance_id', 'rt_instances', 'instance_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('instance_id'), array('unique' => false))
                ->create();
        
        $rt_notifications = $this->table('rt_notifications', array('id' => false, 'primary_key' => 'notification_id'));
        $rt_notifications
                ->addColumn('notification_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('host_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('service_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('start_time','integer', array('signed' => false, 'null' => true))
                ->addColumn('ack_author','string', array('limit' => 255, 'null' => true))
                ->addColumn('ack_data','text', array('null' => true))
                ->addColumn('command_name','string', array('limit' => 255, 'null' => true))
                ->addColumn('contact_name','string', array('limit' => 255, 'null' => true))
                ->addColumn('contacts_notified','integer', array('signed' => false, 'null' => true))
                ->addColumn('end_time','integer', array('signed' => false, 'null' => true))
                ->addColumn('escalated','integer', array('signed' => false, 'null' => true))
                ->addColumn('output','text', array('null' => true))
                ->addColumn('reason_type','integer', array('signed' => false, 'null' => true))
                ->addColumn('state','integer', array('signed' => false, 'null' => true))
                ->addColumn('type','integer', array('signed' => false, 'null' => true))
                ->addForeignKey('host_id', 'rt_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('host_id', 'service_id', 'start_time'), array('unique' => true))
                ->create();
        
        $rt_schemaversion = $this->table('rt_schemaversion', array('id' => false, 'primary_key' => 'schema_id'));
        $rt_schemaversion
                ->addColumn('schema_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('software','string', array('limit' => 128, 'null' => false))
                ->addColumn('version','integer', array('signed' => false, 'null' => false))
                ->create();  
        
        $rt_services_services_dependencies = $this->table('rt_services_services_dependencies', array('id' => false, 'primary_key' => array('dependent_host_id', 'dependent_service_id', 'host_id', 'service_id')));
        $rt_services_services_dependencies
                ->addColumn('dependent_host_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('dependent_service_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('host_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('service_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('dependency_period','string', array('limit' => 75, 'null' => true))
                ->addColumn('execution_failure_options','string', array('limit' => 15, 'null' => true))
                ->addColumn('inherits_parent','integer', array('signed' => false, 'null' => false))
                ->addColumn('notification_failure_options','string', array('limit' => 15, 'null' => true))
                ->addForeignKey('dependent_host_id', 'rt_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('host_id', 'rt_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('dependent_host_id', 'dependent_service_id', 'host_id', 'service_id'), array('unique' => true))
                ->addIndex(array('host_id'), array('unique' => false))
                ->create();
        
        $rt_servicestateevents = $this->table('rt_servicestateevents', array('id' => false, 'primary_key' => 'servicestateevent_id'));
        $rt_servicestateevents
                ->addColumn('servicestateevent_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('end_time','integer', array('signed' => false, 'null' => true))
                ->addColumn('host_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('service_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('start_time','integer', array('signed' => false, 'null' => false))
                ->addColumn('state','integer', array('limit' => 255, 'null' => false))
                ->addColumn('last_update','integer', array('signed' => false, 'limit' => 255, 'null' => false, "default" => "0"))
                ->addColumn('in_downtime','integer', array('signed' => false, 'limit' => 255, 'null' => false))
                ->addColumn('ack_time','integer', array('signed' => false, 'null' => true))
                ->addIndex(array('host_id', 'service_id', 'start_time'), array('unique' => true))
                ->addIndex(array('start_time'), array('unique' => false))
                ->addIndex(array('end_time'), array('unique' => false))
                ->create();
       
        $rt_statistics = $this->table('rt_statistics', array('id' => false, 'primary_key' => 'id'));
        $rt_statistics
                ->addColumn('id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('ctime','integer', array('signed' => false, 'null' => true))
                ->addColumn('lineRead','integer', array('signed' => false, 'null' => true))
                ->addColumn('valueReccorded','integer', array('signed' => false, 'null' => true))
                ->addColumn('last_insert_duration','integer', array('signed' => false, 'null' => true))
                ->addColumn('average_duration','integer', array('signed' => false, 'null' => true))
                ->addColumn('last_nb_line','integer', array('signed' => false, 'null' => true))
                ->addColumn('cpt','integer', array('signed' => false, 'null' => true))
                ->addColumn('last_restart','integer', array('signed' => false, 'null' => true))
                ->addColumn('average','integer', array('signed' => false, 'null' => true))
                ->create();
         
        $rt_customvariables = $this->table('rt_customvariables', array('id' => false, 'primary_key' => 'customvariable_id'));
        $rt_customvariables
                ->addColumn('customvariable_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('host_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('name', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('service_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('default_value', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('modified','boolean', array('null' => true))
                ->addColumn('type','integer', array('signed' => false, 'null' => true))
                ->addColumn('update_time','integer', array('signed' => false, 'null' => true))
                ->addColumn('value', 'string', array('limit' => 255, 'null' => true))
                ->addIndex(array('host_id', 'name', 'service_id'), array('unique' => true))
                ->create();
 
    }
}
