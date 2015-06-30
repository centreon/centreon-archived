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
 * combined work based on this program. Thus, the terms and conditions               of the GNU 
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
                ->addColumn('acl_action_id','integer', array('identity' => true, 'null' => false))
                ->addColumn('acl_action_name','string',array('limit' => 255, 'null' => true))
                ->addColumn('acl_action_description','string', array('limit' => 255,'null' => true))
                ->addColumn('acl_action_activate','string', array('null' => true, "default" => 1, 'values' => array('0','1','2')))
                ->addColumn('organization_id','integer', array('null' => true))
                ->addForeignKey('organization_id', 'cfg_organizations', 'organization_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->save();    
        
        $cfg_acl_actions_rules = $this->table('cfg_acl_actions_rules', array('id' => false, 'primary_key' => 'aar_id'));
        $cfg_acl_actions_rules
                ->addColumn('aar_id','integer', array('identity' => true,'null' => false))
                ->addColumn('acl_action_rule_id','integer', array('null' => true))
                ->addColumn('acl_action_name','string', array('limit' => 255,'null' => true))
                ->addColumn('organization_id','integer', array('null' => true))
                ->addIndex(array('acl_action_rule_id'), array('unique' => false))
                ->addForeignKey('acl_action_rule_id', 'cfg_acl_actions', 'acl_action_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('organization_id', 'cfg_organizations', 'organization_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->save();
      
        $cfg_acl_group_actions_relations = $this->table('cfg_acl_group_actions_relations', array('id' => false, 'primary_key' => 'agar_id'));
        $cfg_acl_group_actions_relations
                ->addColumn('agar_id','integer', array('identity' => true,'null' => false))
                ->addColumn('acl_action_id','integer', array('null' => true))
                ->addColumn('acl_group_id','integer', array('null' => true))
                ->addIndex(array('acl_action_id'), array('unique' => false))
                ->addIndex(array('acl_group_id'), array('unique' => false))
                ->save();

        $log_action = $this->table('log_action', array('id' => false, 'primary_key' => array('action_log_id')));
        $log_action
                ->addColumn('widget_id','integer', array('identity' => true, 'null' => false))
                ->addColumn('action_log_date','integer', array('null' => false))
                ->addColumn('object_type','string',array('limit' => 255, 'null' => false))
                ->addColumn('object_id','integer', array('null' => false))
                ->addColumn('object_name','string',array('limit' => 255, 'null' => false))
                ->addColumn('action_type','string',array('limit' => 255, 'null' => false))
                ->addColumn('action_type','string',array('limit' => 255, 'null' => false))
                ->addColumn('log_contact_id','integer', array('null' => true))
                ->addIndex(array('log_contact_id'), array('unique' => false))
                ->save();

        $log_action_modification = $this->table('log_action_modification', array('id' => false, 'primary_key' => 'modification_id')); 
        $log_action_modification
                ->addColumn('modification_id','integer', array('identity' => true, 'null' => false))
                ->addColumn('field_name','string',array('limit' => 255, 'null' => false))
                ->addColumn('field_value','string',array('limit' => 255, 'null' => false))
                ->addColumn('action_log_id','integer',array('null' => false))
                ->addIndex(array('action_log_id'), array('unique' => false))
                ->save();
                
        $log_archive_host = $this->table('log_archive_host', array('id' => false, 'primary_key' => array('log_id')));
        $log_archive_host
                ->addColumn('log_id','integer', array('identity' => true, 'null' => false))
                ->addColumn('host_id','integer', array('null' => true))
                ->addColumn('UPTimeScheduled','integer', array('null' => true))
                ->addColumn('UPnbEvent','integer', array('null' => true))
                ->addColumn('UPTimeAverageAck','integer', array('null' => false))
                ->addColumn('UPTimeAverageRecovery','integer', array('null' => false))
                ->addColumn('DOWNTimeScheduled','integer', array('null' => true))
                ->addColumn('DOWNnbEvent','integer', array('null' => true))
                ->addColumn('DOWNTimeAverageAck','integer', array('null' => false))
                ->addColumn('DOWNTimeAverageRecovery','integer', array('null' => false))
                ->addColumn('UNREACHABLETimeScheduled','integer', array('null' => true))
                ->addColumn('UNREACHABLEnbEvent','integer', array('null' => true))
                ->addColumn('UNREACHABLETimeAverageAck','integer', array('null' => false))
                ->addColumn('UNREACHABLETimeAverageRecovery','integer', array('null' => false))
                ->addColumn('UNDETERMINEDTimeScheduled','integer', array('null' => true))
                ->addColumn('MaintenanceTime','integer', array('null' => true, "default" => 0))
                ->addColumn('date_end','integer', array('null' => true))
                ->addColumn('date_start','integer', array('null' => true))               
                ->addIndex(array('log_id'), array('unique' => true))
                ->addIndex(array('host_id'), array('unique' => false))
                ->addIndex(array('date_start'), array('unique' => false))
                ->addIndex(array('date_end'), array('unique' => false))
                ->save();

        $log_archive_last_status = $this->table('log_archive_last_status', array('id' => false, 'primary_key' => array('log_archive_last_status_id')));
        $log_archive_last_status
                ->addColumn('log_archive_last_status_id','integer', array('identity' => true, 'null' => false))
                ->addColumn('host_id','integer', array('null' => false))
                ->addColumn('service_id','integer', array('null' => false))
                ->addColumn('host_name','string', array('limit' => 255, 'null' => true))
                ->addColumn('service_description','string', array('limit' => 255, 'null' => true))
                ->addColumn('status','string', array('limit' => 255, 'null' => true))
                ->addColumn('ctime','integer', array('null' => false))
                ->save();
        
        $log_archive_service = $this->table('log_archive_service', array('id' => false, 'primary_key' => array('log_id')));
        $log_archive_service
                ->addColumn('log_id','integer', array('identity' => true, 'null' => false))
                ->addColumn('host_id','integer', array('null' => false, "default" => 0))
                ->addColumn('service_id','integer', array('null' => false, "default" => 0))
                ->addColumn('OKTimeScheduled','integer', array('null' => false, "default" => 0))
                ->addColumn('OKnbEvent','integer', array('null' => false, "default" => 0))
                ->addColumn('OKTimeAverageAck','integer', array('null' => false))
                ->addColumn('OKTimeAverageRecovery','integer', array('null' => false))
                ->addColumn('WARNINGTimeScheduled','integer', array('null' => false, "default" => 0))
                ->addColumn('WARNINGnbEvent','integer', array('null' => false, "default" => 0))
                ->addColumn('WARNINGTimeAverageAck','integer', array('null' => false))
                ->addColumn('WARNINGTimeAverageRecovery','integer', array('null' => false))
                ->addColumn('UNKNOWNTimeScheduled','integer', array('null' => false, "default" => 0))
                ->addColumn('UNKNOWNnbEvent','integer', array('null' => false, "default" => 0))
                ->addColumn('UNKNOWNTimeAverageAck','integer', array('null' => false))
                ->addColumn('UNKNOWNTimeAverageRecovery','integer', array('null' => false))
                ->addColumn('CRITICALTimeScheduled','integer', array('null' => false, "default" => 0))
                ->addColumn('CRITICALnbEvent','integer', array('null' => false, "default" => 0))
                ->addColumn('CRITICALTimeAverageAck','integer', array('null' => false))
                ->addColumn('CRITICALTimeAverageRecovery','integer', array('null' => false))
                ->addColumn('UNDETERMINEDTimeScheduled','integer', array('null' => false, "default" => 0))
                ->addColumn('MaintenanceTime','integer', array('null' => true, "default" => 0))
                ->addColumn('date_end','integer', array('null' => true))
                ->addColumn('date_start','integer', array('null' => true))               
                ->addIndex(array('host_id'), array('unique' => false))
                ->addIndex(array('service_id'), array('unique' => false))
                ->addIndex(array('date_start'), array('unique' => false))
                ->addIndex(array('date_end'), array('unique' => false))
                ->save();

        $log_logs = $this->table('log_logs', array('id' => false, 'primary_key' => array('log_id')));
        $log_logs
                ->addColumn('log_id','integer', array('identity' => true, 'null' => false))
                ->addColumn('ctime','integer', array('null' => true))
                ->addColumn('host_id','integer', array('null' => true))
                ->addColumn('host_name','string', array('limit' => 255, 'null' => true))
                ->addColumn('instance_name','string', array('limit' => 255, 'null' => false))
                ->addColumn('issue_id','integer', array('null' => false))
                ->addColumn('msg_type','integer', array('null' => false))
                ->addColumn('notification_cmd','string', array('limit' => 255, 'null' => true))
                ->addColumn('notification_contact','string', array('limit' => 255, 'null' => true))
                ->addColumn('output','text', array('null' => true))
                ->addColumn('retry','integer', array('null' => true))
                ->addColumn('service_description','string', array('limit' => 255, 'null' => true))
                ->addColumn('retry','integer', array('null' => true))
                ->addColumn('service_id','integer', array('null' => true))
                ->addColumn('status','integer', array('null' => true))
                ->addColumn('type','integer', array('null' => true))
                ->addIndex(array('host_name'), array('unique' => false))
                ->addIndex(array('service_description'), array('unique' => false))
                ->addIndex(array('status'), array('unique' => false))
                ->addIndex(array('instance_name'), array('unique' => false))
                ->addIndex(array('ctime'), array('unique' => false))
                ->addIndex(array('host_id', 'service_id', 'msg_type', 'status', 'ctime'), array('unique' => false))
                ->addIndex(array('host_id', 'msg_type', 'status', 'ctime'), array('unique' => false))
                ->addIndex(array('host_id', 'msg_type', 'status', 'ctime'), array('unique' => false))
                ->save();
        
        $log_snmptt = $this->table('log_snmptt', array('id' => false, 'primary_key' => array('trap_id')));
        $log_snmptt
                ->addColumn('trap_id','integer', array('identity' => true, 'null' => false))
                ->addColumn('trap_oid','text', array('null' => false))
                ->addColumn('trap_ip','string', array('limit' => 50, 'null' => true))
                ->addColumn('trap_community','string', array('limit' => 50, 'null' => true))
                ->addColumn('trap_infos','text', array('null' => false))
                ->save();
               
        $log_traps = $this->table('log_traps', array('id' => false, 'primary_key' => array('trap_id')));
        $log_traps
                ->addColumn('trap_id','integer', array('identity' => true, 'null' => false))
                ->addColumn('trap_time','integer', array('null' => true))
                ->addColumn('timeout','string', array('limit' => 50, 'null' => true, 'values' => array('0','1')))
                ->addColumn('host_name','string', array('limit' => 255, 'null' => true))
                ->addColumn('ip_address','string', array('limit' => 255, 'null' => true))
                ->addColumn('agent_host_name','string', array('limit' => 255, 'null' => true))
                ->addColumn('agent_ip_address','string', array('limit' => 255, 'null' => true))
                ->addColumn('trap_oid','string', array('limit' => 512, 'null' => true))
                ->addColumn('trap_name','string', array('limit' => 255, 'null' => true))
                ->addColumn('vendor','string', array('limit' => 255, 'null' => true))
                ->addColumn('status','integer', array('null' => true))
                ->addColumn('severity_id','integer', array('null' => true))
                ->addColumn('status','string', array('limit' => 255, 'null' => true))
                ->addColumn('severity_id','integer', array('null' => true))
                ->addColumn('severity_name','string', array('limit' => 255, 'null' => true))
                ->addColumn('output_message','string', array('limit' => 255, 'null' => true))
                ->addIndex(array('trap_id'), array('unique' => false))
                ->addIndex(array('trap_time'), array('unique' => false))
                ->save();
              
        $log_traps = $this->table('log_traps_args', array('id' => false, 'primary_key' => array('fk_log_traps')));
        $log_traps
                ->addColumn('fk_log_traps','integer', array('null' => false))
                ->addColumn('arg_number','integer', array('null' => true))
                ->addColumn('arg_oid','string', array('limit' => 255, 'null' => true))
                ->addColumn('arg_value','string', array('limit' => 255, 'null' => true))
                ->addColumn('trap_time','integer', array('null' => true))
                ->addIndex(array('fk_log_traps'), array('unique' => false))
                ->save();

        $rt_acknowledgements = $this->table('rt_acknowledgements', array('id' => false, 'primary_key' => array('acknowledgement_id')));
        $rt_acknowledgements
                ->addColumn('acknowledgement_id','integer', array('identity' => true, 'null' => false))
                ->addColumn('entry_time','integer', array('null' => false))
                ->addColumn('host_id','integer', array('null' => false))
                ->addColumn('service_id','integer', array('null' => true))
                ->addColumn('author','string', array('limit' => 64, 'null' => true))
                ->addColumn('comment_data','string', array('limit' => 255, 'null' => true))
                ->addColumn('deletion_time','integer', array('null' => true))
                ->addColumn('instance_id','integer', array('null' => true))
                ->addColumn('notify_contacts','integer', array('null' => true))
                ->addColumn('persistent_comment','integer', array('null' => true))
                ->addColumn('state','integer', array('null' => true))
                ->addColumn('sticky','integer', array('null' => true))
                ->addColumn('type','integer', array('null' => true))
                ->addForeignKey('host_id', 'rt_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('instance_id', 'rt_instances', 'instance_id', array('delete'=> 'SET NULL', 'update'=> 'RESTRICT'))
                ->addIndex(array('entry_time', 'host_id', 'service_id'), array('unique' => true))
                ->addIndex(array('host_id'), array('unique' => false))
                ->addIndex(array('instance_id'), array('unique' => false))
                ->addIndex(array('entry_time'), array('unique' => false))
                ->save();
        
        $rt_acl = $this->table('rt_acl', array('id' => false, 'primary_key' => array('acl_id')));
        $rt_acl
                ->addColumn('acl_id','integer', array('identity' => true, 'null' => false))
                ->addColumn('host_id','integer', array('null' => false))
                ->addColumn('host_name','string', array('limit' => 255, 'null' => true))
                ->addColumn('service_id','integer', array('null' => false))
                ->addColumn('service_description','string', array('limit' => 255, 'null' => true))
                ->addColumn('group_id','integer', array('null' => false))
                ->addIndex(array('host_name'), array('unique' => false))
                ->addIndex(array('service_description'), array('unique' => false))
                ->addIndex(array('host_name', 'service_description', 'group_id'), array('unique' => true))
                ->addIndex(array('host_id', 'service_id', 'group_id'), array('unique' => true))
                ->addIndex(array('host_name', 'group_id'), array('unique' => true))
                ->save();

        $rt_comments = $this->table('rt_comments', array('id' => false, 'primary_key' => array('comment_id')));
        $rt_comments
                ->addColumn('comment_id','integer', array('identity' => true, 'null' => false))
                ->addColumn('entry_time','integer', array('null' => false))
                ->addColumn('host_id','integer', array('null' => false))
                ->addColumn('service_id','integer', array('null' => true))
                ->addColumn('author','string', array('limit' => 64, 'null' => true))
                ->addColumn('data','text', array('null' => true))
                ->addColumn('deletion_time','integer', array('null' => false))
                ->addColumn('entry_type','integer', array('null' => false))
                ->addColumn('expire_time','integer', array('null' => false))
                ->addColumn('expires','integer', array('null' => false))
                ->addColumn('instance_id','integer', array('null' => false))
                ->addColumn('internal_id','integer', array('null' => false))
                ->addColumn('persistent','integer', array('null' => false))
                ->addColumn('source','integer', array('null' => false))
                ->addColumn('type','integer', array('null' => false))
                ->addForeignKey('host_id', 'rt_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('instance_id', 'rt_instances', 'instance_id', array('delete'=> 'SET NULL', 'update'=> 'RESTRICT'))
                ->addIndex(array('entry_time', 'host_id', 'service_id'), array('unique' => true))
                ->addIndex(array('internal_id'), array('unique' => false))
                ->addIndex(array('host_id'), array('unique' => false))
                ->addIndex(array('instance_id'), array('unique' => false))
                ->save();
        
        $rt_downtimes = $this->table('rt_downtimes', array('id' => false, 'primary_key' => array('downtime_id')));
        $rt_downtimes
                ->addColumn('downtime_id','integer', array('identity' => true, 'null' => false))
                ->addColumn('entry_time','integer', array('null' => false))
                ->addColumn('host_id','integer', array('null' => false))
                ->addColumn('service_id','integer', array('null' => false))
                ->addColumn('author','string', array('limit' => 64, 'null' => true))
                ->addColumn('cancelled','integer', array('null' => true))
                ->addColumn('comment_data','text', array('null' => true))                
                ->addColumn('deletion_time','integer', array('null' => true))
                ->addColumn('duration','integer', array('null' => false))
                ->addColumn('end_time','integer', array('null' => false))
                ->addColumn('fixed','integer', array('null' => false))
                ->addColumn('instance_id','integer', array('null' => false))
                ->addColumn('internal_id','integer', array('null' => false))
                ->addColumn('start_time','integer', array('null' => false))
                ->addColumn('actual_start_time','integer', array('null' => false))
                ->addColumn('started','integer', array('null' => false))
                ->addColumn('triggered_by','integer', array('null' => false))
                ->addColumn('type','integer', array('null' => false))
                ->addColumn('is_recurring','integer', array('null' => false))
                ->addColumn('recurring_interval','integer', array('null' => false))
                ->addColumn('recurring_timeperiod','string', array('limit' => 200, 'null' => true))
                ->addForeignKey('host_id', 'rt_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('instance_id', 'rt_instances', 'instance_id', array('delete'=> 'SET NULL', 'update'=> 'RESTRICT'))
                ->addIndex(array('entry_time', 'host_id', 'service_id'), array('unique' => true))
                ->addIndex(array('host_id'), array('unique' => false))
                ->addIndex(array('instance_id'), array('unique' => false))
                ->addIndex(array('entry_time'), array('unique' => false))
                ->addIndex(array('host_id', 'start_time'), array('unique' => true))
                ->save();
        
        $rt_eventhandlers = $this->table('rt_eventhandlers', array('id' => false, 'primary_key' => array('eventhandler_id')));
        $rt_eventhandlers
                ->addColumn('eventhandler_id','integer', array('identity' => true, 'null' => false))
                ->addColumn('host_id','integer', array('null' => true))
                ->addColumn('service_id','integer', array('null' => true))
                ->addColumn('start_time','integer', array('null' => true))
                ->addColumn('command_args','string', array('limit' => 255, 'null' => true))
                ->addColumn('command_line','string', array('limit' => 255, 'null' => true))
                ->addColumn('early_timeout','integer', array('null' => true))
                ->addColumn('end_time','integer', array('null' => true))
                ->addColumn('execution_time','integer', array('null' => true))
                ->addColumn('output','string', array('limit' => 255, 'null' => true))
                ->addColumn('return_code','integer', array('null' => true))
                ->addColumn('state','text', array('null' => true))                
                ->addColumn('state_type','integer', array('null' => true))
                ->addColumn('timeout','integer', array('null' => false))
                ->addColumn('type','integer', array('null' => false))
                ->addForeignKey('host_id', 'rt_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('host_id', 'service_id', 'start_time'), array('unique' => true))
                ->save();

        $rt_flappingstatuses = $this->table('rt_flappingstatuses', array('id' => false, 'primary_key' => array('flappingstatus_id')));
        $rt_flappingstatuses
                ->addColumn('flappingstatus_id','integer', array('identity' => true, 'null' => false))
                ->addColumn('host_id','integer', array('null' => true))
                ->addColumn('service_id','integer', array('null' => true))
                ->addColumn('event_time','integer', array('null' => true))
                ->addColumn('comment_time','integer', array('null' => true))
                ->addColumn('event_type','integer', array('null' => true))
                ->addColumn('comment_time','integer', array('null' => true))
                ->addColumn('event_type','integer', array('null' => true))
                ->addColumn('high_threshold','integer', array('null' => true))
                ->addColumn('internal_comment_id','integer', array('null' => true))
                ->addColumn('low_threshold','integer', array('null' => true))
                ->addColumn('percent_state_change','integer', array('null' => true))
                ->addColumn('reason_type','integer', array('null' => true))
                ->addColumn('type','integer', array('null' => true))
                ->addForeignKey('host_id', 'rt_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('host_id', 'service_id', 'event_time'), array('unique' => true))
                ->save();
        
        $rt_hostgroups = $this->table('rt_hostgroups', array('id' => false, 'primary_key' => array('hostgroup_id')));
        $rt_hostgroups
                ->addColumn('hostgroup_id','integer', array('identity' => true, 'null' => false))
                ->addColumn('instance_id','integer', array('null' => false))
                ->addColumn('name','string', array('limit' => 255, 'null' => false))
                ->addColumn('action_url','string', array('limit' => 160, 'null' => true))
                ->addColumn('alias','string', array('limit' => 255, 'null' => true))
                ->addColumn('notes','string', array('limit' => 160, 'null' => true))
                ->addColumn('notes_url','string', array('limit' => 160, 'null' => true))
                ->addColumn('enabled','integer', array('null' => true))
                ->addForeignKey('instance_id', 'rt_instances', 'instance_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('name', 'instance_id'), array('unique' => true))
                ->save();
       
        $rt_hosts = $this->table('rt_hosts', array('id' => false, 'primary_key' => array('host_id')));
        $rt_hosts
                ->addColumn('host_id','integer', array('null' => false))
                ->addColumn('name','string', array('limit' => 255, 'null' => false))
                ->addColumn('instance_id','integer', array('null' => false))
                ->addColumn('acknowledged','integer', array('null' => true))
                ->addColumn('acknowledgement_type','integer', array('null' => true))
                ->addColumn('action_url','string', array('limit' => 255, 'null' => true))
                ->addColumn('active_checks','integer', array('null' => true))
                ->addColumn('address','string', array('limit' => 75, 'null' => true))
                ->addColumn('alias','string', array('limit' => 100, 'null' => true))
                ->addColumn('check_attempt','integer', array('null' => true))
                ->addColumn('check_command','text', array('null' => true))
                ->addColumn('check_freshness','integer', array('null' => true))
                ->addColumn('check_interval','integer', array('null' => true))
                ->addColumn('check_period','string', array('limit' => 75, 'null' => true))
                ->addColumn('check_type','integer', array('null' => true))
                ->addColumn('checked','integer', array('null' => true))
                ->addColumn('command_line','string', array('limit' => 75, 'null' => true))
                ->addColumn('default_active_checks','integer', array('null' => true))
                ->addColumn('default_event_handler_enabled','integer', array('null' => true))
                ->addColumn('default_failure_prediction','integer', array('null' => true))
                ->addColumn('default_flap_detection','integer', array('null' => true))
                ->addColumn('default_notify','integer', array('null' => true))
                ->addColumn('default_passive_checks','integer', array('null' => true))
                ->addColumn('default_process_perfdata','integer', array('null' => true))
                ->addColumn('check_pdisplay_nameeriod','string', array('limit' => 100, 'null' => true))
                ->addColumn('enabled','integer', array('null' => true))
                ->addColumn('event_handler','string', array('limit' => 255, 'null' => true))
                ->addColumn('event_handler_enabled','integer', array('null' => true))
                ->addColumn('execution_time','integer', array('null' => true))
                ->addColumn('failure_prediction','integer', array('null' => true))
                ->addColumn('first_notification_delay','integer', array('null' => true))
                ->addColumn('flap_detection','integer', array('null' => true))
                ->addColumn('flap_detection_on_down','integer', array('null' => true))
                ->addColumn('flap_detection_on_unreachable','integer', array('null' => true))
                ->addColumn('flap_detection_on_up','integer', array('null' => true))
                ->addColumn('flapping','integer', array('null' => true))
                ->addColumn('freshness_threshold','integer', array('null' => true))
                ->addColumn('high_flap_threshold','integer', array('null' => true))
                ->addColumn('icon_image','string', array('limit' => 255, 'null' => true))
                ->addColumn('icon_image_alt','string', array('limit' => 255, 'null' => true))
                ->addColumn('last_check','integer', array('null' => true))
                ->addColumn('last_hard_state','integer', array('null' => true))
                ->addColumn('last_hard_state_change','integer', array('null' => true))
                ->addColumn('last_notification','integer', array('null' => true))
                ->addColumn('last_state_change','integer', array('null' => true))
                ->addColumn('last_time_down','integer', array('null' => true))
                ->addColumn('last_time_unreachable','integer', array('null' => true))
                ->addColumn('last_time_up','integer', array('null' => true))
                ->addColumn('last_update','integer', array('null' => true))
                ->addColumn('latency','integer', array('null' => true))
                ->addColumn('low_flap_threshold','integer', array('null' => true))
                ->addColumn('max_check_attempts','integer', array('null' => true))
                ->addColumn('modified_attributes','integer', array('null' => true))
                ->addColumn('next_check','integer', array('null' => true))
                ->addColumn('next_host_notification','integer', array('null' => true))
                ->addColumn('no_more_notifications','integer', array('null' => true))
                ->addColumn('notes','integer', array('null' => true))
                ->addColumn('notes_url','string', array('limit' => 255, 'null' => true))
                ->addColumn('notification_interval','integer', array('null' => true))
                ->addColumn('notification_number','integer', array('null' => true))
                ->addColumn('notification_period','string', array('limit' => 75, 'null' => true))
                ->addColumn('notify','integer', array('null' => true))
                ->addColumn('notify_on_down','integer', array('null' => true))
                ->addColumn('notify_on_downtime','integer', array('null' => true))
                ->addColumn('notify_on_flapping','integer', array('null' => true))
                ->addColumn('notify_on_recovery','integer', array('null' => true))
                ->addColumn('notify_on_unreachable','integer', array('null' => true))
                ->addColumn('obsess_over_host','integer', array('null' => true))
                ->addColumn('output','text', array('null' => true))
                ->addColumn('passive_checks','integer', array('null' => true))
                ->addColumn('percent_state_change','integer', array('null' => true))
                ->addColumn('perfdata','text', array('null' => true))
                ->addColumn('process_perfdata','integer', array('null' => true))
                ->addColumn('real_state','integer', array('null' => true))
                ->addColumn('retain_nonstatus_information','integer', array('null' => true))
                ->addColumn('retain_status_information','integer', array('null' => true))
                ->addColumn('retry_interval','integer', array('null' => true))
                ->addColumn('scheduled_downtime_depth','integer', array('null' => true))
                ->addColumn('should_be_scheduled','integer', array('null' => true))
                ->addColumn('stalk_on_down','integer', array('null' => true))
                ->addColumn('stalk_on_unreachable','integer', array('null' => true))
                ->addColumn('stalk_on_up','integer', array('null' => true))
                ->addColumn('state','integer', array('null' => true))
                ->addColumn('state_type','integer', array('null' => true))
                ->addColumn('statusmap_image','string', array('limit' => 255, 'null' => true))
                ->addForeignKey('instance_id', 'rt_instances', 'instance_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('host_id'), array('unique' => false))
                ->addIndex(array('instance_id'), array('unique' => false))
                ->addIndex(array('name'), array('unique' => false))
                ->save();
               
        $rt_services = $this->table('rt_services', array('id' => false, 'primary_key' => array('host_id', 'service_id')));
        $rt_services
                ->addColumn('host_id','integer', array('null' => false))
                ->addColumn('description','string', array('limit' => 255, 'null' => false))
                ->addColumn('service_id','integer', array('null' => false))
                ->addColumn('acknowledged','integer', array('null' => true))
                ->addColumn('acknowledgement_type','integer', array('null' => true))
                ->addColumn('action_url','string', array('limit' => 255, 'null' => true))
                ->addColumn('active_checks','integer', array('null' => true))
                ->addColumn('check_attempt','integer', array('null' => true))
                ->addColumn('check_command','text', array('null' => true))
                ->addColumn('check_freshness','integer', array('null' => true))
                ->addColumn('check_interval','double', array('null' => true))
                ->addColumn('check_period','string', array('limit' => 75, 'null' => true))
                ->addColumn('check_type','integer', array('null' => true))
                ->addColumn('checked','integer', array('null' => true))
                ->addColumn('command_line','text', array('null' => true))
                ->addColumn('default_active_checks','integer', array('null' => true))
                ->addColumn('default_event_handler_enabled','integer', array('null' => true))
                ->addColumn('default_failure_prediction','integer', array('null' => true))
                ->addColumn('default_flap_detection','integer', array('null' => true))
                ->addColumn('default_notify','integer', array('null' => true))
                ->addColumn('default_passive_checks','integer', array('null' => true))
                ->addColumn('default_process_perfdata','integer', array('null' => true))
                ->addColumn('display_name','string', array('limit' => 160, 'null' => true))
                ->addColumn('enabled','integer', array('null' => true, 'default' => 1))
                ->addColumn('event_handler','string', array('limit' => 255, 'null' => true))
                ->addColumn('event_handler_enabled','integer', array('null' => true))
                ->addColumn('execution_time','double', array('null' => true))
                ->addColumn('failure_prediction','integer', array('null' => true))
                ->addColumn('failure_prediction_options','string', array('limit' => 64, 'null' => true))
                ->addColumn('first_notification_delay','double', array('null' => true))
                ->addColumn('flap_detection','integer', array('null' => true))
                ->addColumn('flap_detection_on_critical','integer', array('null' => true))
                ->addColumn('flap_detection_on_ok','integer', array('null' => true))
                ->addColumn('flap_detection_on_unknown','integer', array('null' => true))
                ->addColumn('flap_detection_on_warning','integer', array('null' => true))
                ->addColumn('flapping','integer', array('null' => true))
                ->addColumn('freshness_threshold','double', array('null' => true))
                ->addColumn('high_flap_threshold','double', array('null' => true))
                ->addColumn('icon_image','string', array('limit' => 255, 'null' => true))
                ->addColumn('icon_image_alt','string', array('limit' => 255, 'null' => true))
                ->addColumn('last_check','integer', array('null' => true))
                ->addColumn('last_hard_state','integer', array('null' => true))
                ->addColumn('last_hard_state_change','integer', array('null' => true))
                ->addColumn('last_notification','integer', array('null' => true))
                ->addColumn('last_state_change','integer', array('null' => true))
                ->addColumn('last_time_critical','integer', array('null' => true))
                ->addColumn('last_time_ok','integer', array('null' => true))
                ->addColumn('last_time_unknown','integer', array('null' => true))
                ->addColumn('last_time_warning','integer', array('null' => true))
                ->addColumn('last_update','integer', array('null' => true))
                ->addColumn('latency','double', array('null' => true))
                ->addColumn('low_flap_threshold','double', array('null' => true))
                ->addColumn('max_check_attempts','integer', array('null' => true))
                ->addColumn('modified_attributes','integer', array('null' => true))
                ->addColumn('next_check','integer', array('null' => true))
                ->addColumn('next_notification','integer', array('null' => true))
                ->addColumn('no_more_notifications','integer', array('null' => true))
                ->addColumn('notes','string', array('limit' => 255, 'null' => true))
                ->addColumn('notes_url','string', array('limit' => 255, 'null' => true))
                ->addColumn('notification_interval','double', array('null' => true))
                ->addColumn('notification_number','double', array('null' => true))
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
                ->addColumn('percent_state_change','double', array('null' => true))
                ->addColumn('perfdata','text', array('null' => true))
                ->addColumn('process_perfdata','boolean', array('null' => true))
                ->addColumn('real_state','integer', array('null' => true))
                ->addColumn('retain_nonstatus_information','boolean', array('null' => true))
                ->addColumn('retain_status_information','boolean', array('null' => true))
                ->addColumn('retry_interval','double', array('null' => true))
                ->addColumn('scheduled_downtime_depth','integer', array('null' => true))
                ->addColumn('should_be_scheduled','boolean', array('null' => true))
                ->addColumn('stalk_on_critical','boolean', array('null' => true))
                ->addColumn('stalk_on_ok','boolean', array('null' => true))
                ->addColumn('stalk_on_unknown','boolean', array('null' => true))
                ->addColumn('stalk_on_warning','boolean', array('null' => true))
                ->addColumn('state','integer', array('null' => true))
                ->addColumn('state_type','integer', array('null' => true))
                ->addColumn('volatile','boolean', array('null' => true))
                ->addForeignKey('host_id', 'rt_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('host_id', 'service_id'), array('unique' => true))
                ->addIndex(array('service_id'), array('unique' => false))
                ->addIndex(array('description'), array('unique' => false))
                ->save();
        
        $rt_hosts_hostgroups = $this->table('rt_hosts_hostgroups', array('id' => false, 'primary_key' => array('host_id', 'hostgroup_id')));
        $rt_hosts_hostgroups
                ->addColumn('host_id','integer', array('null' => false))
                ->addColumn('hostgroup_id','integer', array('null' => false))
                ->addForeignKey('host_id', 'rt_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('hostgroup_id', 'rt_hostgroups', 'hostgroup_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('host_id', 'hostgroup_id'), array('unique' => true))
                ->addIndex(array('hostgroup_id'), array('unique' => false))
                ->save();
        
        
        $rt_hosts_hostgroups = $this->table('rt_hosts_hostgroups', array('id' => false, 'primary_key' => array('host_id', 'hostgroup_id')));
        $rt_hosts_hostgroups
                ->addColumn('host_id','integer', array('null' => false))
                ->addColumn('hostgroup_id','integer', array('null' => false))
                ->addForeignKey('host_id', 'rt_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('hostgroup_id', 'rt_hostgroups', 'hostgroup_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('host_id', 'hostgroup_id'), array('unique' => true))
                ->addIndex(array('hostgroup_id'), array('unique' => false))
                ->save();
        
        $rt_hosts_hosts_dependencies = $this->table('rt_hosts_hosts_dependencies', array('id' => false, 'primary_key' => array('dependent_host_id', 'host_id')));
        $rt_hosts_hosts_dependencies
                ->addColumn('dependent_host_id','integer', array('null' => false))
                ->addColumn('host_id','integer', array('null' => false))
                ->addColumn('dependency_period','string', array('limit' => 75, 'null' => true))
                ->addColumn('execution_failure_options','string', array('limit' => 15, 'null' => true))
                ->addColumn('inherits_parent','integer', array('null' => false))
                ->addColumn('notification_failure_options','string', array('limit' => 15, 'null' => true))
                ->addForeignKey('host_id', 'rt_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('dependent_host_id', 'rt_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('dependent_host_id', 'host_id'), array('unique' => true))
                ->addIndex(array('host_id'), array('unique' => false))
                ->save();
        
        $rt_hoststateevents = $this->table('rt_hoststateevents', array('id' => false, 'primary_key' => array('child_id', 'parent_id')));
        $rt_hoststateevents
                ->addColumn('child_id','integer', array('null' => false))
                ->addColumn('parent_id','integer', array('null' => false))
                ->addForeignKey('child_id', 'rt_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('parent_id', 'rt_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('child_id', 'parent_id'), array('unique' => true))
                ->addIndex(array('parent_id'), array('unique' => false))
                ->save();

        $rt_hosts_hosts_parents = $this->table('rt_hosts_hosts_parents', array('id' => false, 'primary_key' => 'hoststateevent_id'));
        $rt_hosts_hosts_parents
                ->addColumn('hoststateevent_id','integer', array('identity' => true, 'null' => false))
                ->addColumn('end_time','integer', array('null' => true))
                ->addColumn('host_id','integer', array('null' => false))
                ->addColumn('start_time','integer', array('null' => false))
                ->addColumn('state','integer', array('null' => false))
                ->addColumn('last_update','integer', array('null' => false, 'default' => 0))
                ->addColumn('in_downtime','integer', array('null' => false))
                ->addColumn('ack_time','integer', array('null' => true))
                ->addIndex(array('host_id', 'start_time'), array('unique' => true))
                ->addIndex(array('start_time'), array('unique' => false))
                ->addIndex(array('end_time'), array('unique' => false))
                ->save();
        
        $rt_index_data = $this->table('rt_index_data', array('id' => false, 'primary_key' => 'id'));
        $rt_index_data
                ->addColumn('id','integer', array('identity' => true, 'null' => false))
                ->addColumn('host_name','string', array('limit' => 255, 'null' => true))
                ->addColumn('host_id','integer', array('null' => true))
                ->addColumn('service_description','string', array('limit' => 255, 'null' => true))
                ->addColumn('service_id','integer', array('null' => true))
                ->addColumn('check_interval','integer', array('null' => true))
                ->addColumn('special','string', array('limit' => 1, 'null' => true, "default" => "0", 'values' => array('0','1')))
                ->addColumn('hidden','string', array('limit' => 1, 'null' => true, "default" => "0", 'values' => array('0','1')))
                ->addColumn('locked','string', array('limit' => 1, 'null' => true, "default" => "0", 'values' => array('0','1')))
                ->addColumn('trashed','string', array('limit' => 1, 'null' => true, "default" => "0", 'values' => array('0','1')))
                ->addColumn('must_be_rebuild','string', array('limit' => 1, 'null' => true, "default" => "0", 'values' => array('0','1', '2')))
                ->addColumn('storage_type','string', array('limit' => 1, 'null' => true, "default" => "2", 'values' => array('0','1', '2')))
                ->addColumn('to_delete','integer', array('null' => true, "default" => "0"))
                ->addColumn('rrd_retention','integer', array('null' => true))
                ->addIndex(array('host_id', 'service_id'), array('unique' => true))
                ->addIndex(array('start_thost_nameime'), array('unique' => false))
                ->addIndex(array('service_description'), array('unique' => false))
                ->addIndex(array('host_id'), array('unique' => false))
                ->addIndex(array('service_id'), array('unique' => false))
                ->addIndex(array('must_be_rebuild'), array('unique' => false))
                ->addIndex(array('trashed'), array('unique' => false))
                ->save();
                
        $rt_instances = $this->table('rt_instances', array('id' => false, 'primary_key' => 'instance_id'));
        $rt_instances
                ->addColumn('instance_id','integer', array('null' => false))
                ->addColumn('host_name','string', array('limit' => 255, 'null' => false, "default" => "localhost"))
                ->addColumn('active_host_checks','integer', array('null' => true))
                ->addColumn('active_service_checks','integer', array('null' => true))
                ->addColumn('address','string', array('limit' => 128, 'null' => true))
                ->addColumn('check_hosts_freshness','integer', array('null' => true))
                ->addColumn('check_services_freshness','integer', array('null' => true))
                ->addColumn('daemon_mode','integer', array('null' => true))
                ->addColumn('description','string', array('limit' => 128, 'null' => true))
                ->addColumn('end_time','integer', array('null' => true))
                ->addColumn('engine','string', array('limit' => 64, 'null' => true))
                ->addColumn('event_handlers','integer', array('null' => true))
                ->addColumn('failure_prediction','integer', array('null' => true))
                ->addColumn('flap_detection','integer', array('null' => true))
                ->addColumn('global_host_event_handler','text', array('null' => true))
                ->addColumn('global_service_event_handler','text', array('null' => true))
                ->addColumn('last_alive','integer', array('null' => true))
                ->addColumn('last_command_check','integer', array('null' => true))
                ->addColumn('last_log_rotation','integer', array('null' => true))
                ->addColumn('modified_host_attributes','integer', array('null' => true))
                ->addColumn('modified_service_attributes','integer', array('null' => true))
                ->addColumn('notifications','integer', array('null' => true))
                ->addColumn('obsess_over_hosts','integer', array('null' => true))
                ->addColumn('obsess_over_services','integer', array('null' => true))
                ->addColumn('passive_host_checks','integer', array('null' => true))
                ->addColumn('passive_service_checks','integer', array('null' => true))
                ->addColumn('pid','integer', array('null' => true))
                ->addColumn('process_perfdata','integer', array('null' => true))
                ->addColumn('running','integer', array('null' => true))
                ->addColumn('start_time','integer', array('null' => true))
                ->addColumn('version','integer', array('null' => true))
                ->addColumn('deleted','integer', array('null' => true))
                ->addColumn('outdated','integer', array('null' => true))
                ->save();
                
        $rt_issues = $this->table('rt_issues', array('id' => false, 'primary_key' => 'issue_id'));
        $rt_issues
                ->addColumn('issue_id','integer', array('identity' => true, 'null' => false))
                ->addColumn('host_id','integer', array('null' => true))
                ->addColumn('service_id','integer', array('null' => true))
                ->addColumn('start_time','integer', array('null' => false))
                ->addColumn('ack_time','integer', array('null' => true))
                ->addColumn('end_time','integer', array('null' => true))
                ->addForeignKey('parent_id', 'rt_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('host_id', 'service_id', 'start_time'), array('unique' => true))
                ->addIndex(array('start_time'), array('unique' => false))
                ->save();
                
        $rt_issues_issues_parents = $this->table('rt_issues_issues_parents', array('id' => false, 'primary_key' => 'child_id'));
        $rt_issues_issues_parents
                ->addColumn('child_id','integer', array('null' => false))
                ->addColumn('end_time','integer', array('null' => true))
                ->addColumn('start_time','integer', array('null' => false))
                ->addColumn('parent_id','integer', array('null' => false))
                ->addForeignKey('child_id', 'rt_issues', 'issue_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('parent_id', 'rt_issues', 'issue_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('child_id'), array('unique' => false))
                ->addIndex(array('parent_id'), array('unique' => false))
                ->save();       
        
        $rt_metrics = $this->table('rt_metrics', array('id' => false, 'primary_key' => 'metric_id'));
        $rt_metrics
                ->addColumn('metric_id','integer', array('identity' => true, 'null' => false))
                ->addColumn('index_id','integer', array('null' => true))
                ->addColumn('metric_name','string', array('limit' => 255, 'null' => true))
                ->addColumn('data_source_type','string', array('limit' => 1, 'null' => true, 'values' => array('0','1', '2', '3')))
                ->addColumn('unit_name','string', array('limit' => 32, 'null' => true))
                ->addColumn('current_value','float', array('null' => true))
                ->addColumn('warn','float', array('null' => true))
                ->addColumn('warn_low','float', array('null' => true))
                ->addColumn('warn_threshold_mode','string', array('limit' => 1, 'null' => true, 'values' => array('0','1')))
                ->addColumn('crit','float', array('null' => true))
                ->addColumn('crit_low','float', array('null' => true))
                ->addColumn('crit_threshold_mode','string', array('limit' => 1, 'null' => true, 'values' => array('0','1')))
                ->addColumn('hidden','string', array('limit' => 1, 'null' => true, "default" => 0, 'values' => array('0','1')))
                ->addColumn('min','float', array('null' => true))
                ->addColumn('max','float', array('null' => true))
                ->addColumn('locked','string', array('limit' => 1, 'null' => true, 'values' => array('0','1')))
                ->addColumn('to_delete','integer', array('null' => false))
                ->addIndex(array('index_id', 'metric_name'), array('unique' => true))
                ->addIndex(array('index_id'), array('unique' => false))
                ->save();
        
        $rt_modules = $this->table('rt_modules', array('id' => false, 'primary_key' => 'module_id'));
        $rt_modules
                ->addColumn('module_id','integer', array('identity' => true, 'null' => false))
                ->addColumn('instance_id','integer', array('null' => false))
                ->addColumn('args','string', array('limit' => 255, 'null' => true))
                ->addColumn('filename','string', array('limit' => 255, 'null' => true))
                ->addColumn('loaded','integer', array('null' => true))
                ->addColumn('should_be_loaded','integer', array('null' => true))
                ->addForeignKey('instance_id', 'rt_instances', 'instance_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('instance_id'), array('unique' => false))
                ->save();
        
        $rt_notifications = $this->table('rt_notifications', array('id' => false, 'primary_key' => 'notification_id'));
        $rt_notifications
                ->addColumn('notification_id','integer', array('identity' => true, 'null' => false))
                ->addColumn('host_id','integer', array('null' => true))
                ->addColumn('service_id','integer', array('null' => true))
                ->addColumn('start_time','integer', array('null' => true))
                ->addColumn('ack_author','string', array('limit' => 255, 'null' => true))
                ->addColumn('ack_data','text', array('null' => true))
                ->addColumn('command_name','string', array('limit' => 255, 'null' => true))
                ->addColumn('contact_name','string', array('limit' => 255, 'null' => true))
                ->addColumn('contacts_notified','integer', array('null' => true))
                ->addColumn('end_time','integer', array('null' => true))
                ->addColumn('escalated','integer', array('null' => true))
                ->addColumn('output','text', array('null' => true))
                ->addColumn('reason_type','integer', array('null' => true))
                ->addColumn('state','integer', array('null' => true))
                ->addColumn('type','integer', array('null' => true))
                ->addForeignKey('host_id', 'rt_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('host_id', 'service_id', 'start_time'), array('unique' => true))
                ->save();
        
        $rt_schemaversion = $this->table('rt_schemaversion', array('id' => false, 'primary_key' => 'schema_id'));
        $rt_schemaversion
                ->addColumn('schema_id','integer', array('identity' => true, 'null' => false))
                ->addColumn('software','string', array('limit' => 128, 'null' => false))
                ->addColumn('version','integer', array('null' => false))
                ->save();
        
        $rt_servicegroups = $this->table('rt_servicegroups', array('id' => false, 'primary_key' => 'servicegroup_id'));
        $rt_servicegroups
                ->addColumn('servicegroup_id','integer', array('identity' => true, 'null' => false))
                ->addColumn('instance_id','integer', array('null' => false))
                ->addColumn('name','string', array('limit' => 255, 'null' => false))
                ->addColumn('action_url','string', array('limit' => 160, 'null' => true))
                ->addColumn('alias','string', array('limit' => 255, 'null' => true))
                ->addColumn('notes','string', array('limit' => 160, 'null' => true))
                ->addColumn('notes_url','string', array('limit' => 160, 'null' => true))
                ->addForeignKey('instance_id', 'rt_instances', 'instance_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('instance_id'), array('unique' => false))
                ->save();

        $rt_services_servicegroups = $this->table('rt_services_servicegroups', array('id' => false, 'primary_key' => 'host_id'));
        $rt_services_servicegroups
                ->addColumn('host_id','integer', array('null' => false))
                ->addColumn('service_id','integer', array('null' => false))
                ->addColumn('servicegroup_id','integer', array('null' => false))
                ->addForeignKey('host_id', 'rt_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('servicegroup_id', 'rt_servicegroups', 'servicegroup_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('host_id', 'service_id', 'servicegroup_id'), array('unique' => true))
                ->addIndex(array('servicegroup_id'), array('unique' => false))
                ->save();       
        
        $rt_services_services_dependencies = $this->table('rt_services_services_dependencies', array('id' => false, 'primary_key' => array('dependent_host_id', 'dependent_service_id', 'host_id', 'service_id')));
        $rt_services_services_dependencies
                ->addColumn('dependent_host_id','integer', array('null' => false))
                ->addColumn('dependent_service_id','integer', array('null' => false))
                ->addColumn('host_id','integer', array('null' => false))
                ->addColumn('service_id','integer', array('null' => false))
                ->addColumn('dependency_period','string', array('limit' => 75, 'null' => true))
                ->addColumn('execution_failure_options','string', array('limit' => 15, 'null' => true))
                ->addColumn('inherits_parent','integer', array('null' => false))
                ->addColumn('notification_failure_options','string', array('limit' => 15, 'null' => true))
                
                ->addForeignKey('dependent_host_id', 'rt_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('host_id', 'rt_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))

                ->addIndex(array('dependent_host_id', 'dependent_service_id', 'host_id', 'service_id'), array('unique' => true))
                ->addIndex(array('host_id'), array('unique' => false))
                ->save();
        
        $rt_servicestateevents = $this->table('rt_servicestateevents', array('id' => false, 'primary_key' => 'servicestateevent_id'));
        $rt_servicestateevents
                ->addColumn('servicestateevent_id','integer', array('identity' => true, 'null' => false))
                ->addColumn('end_time','integer', array('null' => true))
                ->addColumn('host_id','integer', array('null' => false))
                ->addColumn('service_id','integer', array('null' => true))
                ->addColumn('start_time','integer', array('null' => false))
                ->addColumn('state','integer', array('null' => false))
                ->addColumn('last_update','integer', array('null' => false, "default" => "0"))
                ->addColumn('in_downtime','integer', array('null' => false))
                ->addColumn('ack_time','integer', array('null' => false))
                ->addIndex(array('host_id', 'service_id', 'start_time'), array('unique' => true))
                ->addIndex(array('start_time'), array('unique' => false))
                ->addIndex(array('end_time'), array('unique' => false))
                ->save();
       
        $rt_statistics = $this->table('rt_statistics', array('id' => false, 'primary_key' => 'id'));
        $rt_statistics
                ->addColumn('id','integer', array('identity' => true, 'null' => false))
                ->addColumn('ctime','integer', array('null' => true))
                ->addColumn('lineRead','integer', array('null' => true))
                ->addColumn('valueReccorded','integer', array('null' => true))
                ->addColumn('last_insert_duration','integer', array('null' => true))
                ->addColumn('average_duration','integer', array('null' => true))
                ->addColumn('last_nb_line','integer', array('null' => true))
                ->addColumn('cpt','integer', array('null' => true))
                ->addColumn('last_restart','integer', array('null' => true))
                ->addColumn('average','integer', array('null' => true))
                ->save();
 
    }
}