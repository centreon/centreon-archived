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
        $cfg_engine = $this->table('cfg_engine', array('id' => false, 'primary_key' => 'poller_id'));
        $cfg_engine
                ->addColumn('poller_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('bin_path','string',array('limit' => 255, 'null' => true))
                ->addColumn('conf_dir','string',array('limit' => 255, 'null' => true))
                ->addColumn('log_dir','string',array('limit' => 255, 'null' => true))
                ->addColumn('var_lib_dir','string',array('limit' => 255, 'null' => true))
                ->addColumn('module_dir','string',array('limit' => 255, 'null' => true))
                ->addColumn('init_script','string',array('limit' => 255, 'null' => true))
                ->addColumn('enable_event_handlers','integer', array('limit' => 255, 'null' => false, 'signed' => false, "default" => 1))
                ->addColumn('external_command_buffer_slots','integer', array('signed' => false, 'null' => false))
                ->addColumn('command_check_interval','string',array('limit' => 255, 'null' => true))
                ->addColumn('command_file','string',array('limit' => 255, 'null' => true))
                ->addColumn('use_syslog','integer',array('limit' => 255, 'signed' => false, 'null' => true, "default" => 1))
                ->addColumn('log_service_retries','integer',array('limit' => 255, 'signed' => false, 'null' => true, "default" => 1))
                ->addColumn('log_host_retries','integer',array('limit' => 255, 'signed' => false, 'null' => true, "default" => 0))
                ->addColumn('log_event_handlers','integer',array('limit' => 255, 'signed' => false, 'null' => true, "default" => 1))
                ->addColumn('log_initial_states','integer',array('limit' => 255, 'signed' => false, 'null' => true, "default" => 0))
                ->addColumn('log_external_commands','integer',array('limit' => 255, 'signed' => false, 'null' => true, "default" => 1))
                ->addColumn('log_passive_checks','integer',array('limit' => 255, 'signed' => false, 'null' => true, "default" => 1))
                ->addColumn('global_host_event_handler','integer',array('signed' => false, 'null' => true))
                ->addColumn('global_service_event_handler','integer',array('signed' => false, 'null' => true))
                ->addColumn('max_concurrent_checks','integer',array('signed' => false, 'null' => true))
                ->addColumn('check_result_reaper_frequency','integer',array('signed' => false, 'null' => true))
                ->addColumn('enable_flap_detection','integer',array('limit' => 255, 'signed' => false, 'null' => true, "default" => 0))
                ->addColumn('low_service_flap_threshold','string',array('limit' => 255, 'null' => true, "default" => 20))
                ->addColumn('high_service_flap_threshold','string',array('limit' => 255, 'null' => true, "default" => 30))
                ->addColumn('low_host_flap_threshold','string',array('limit' => 255, 'null' => true))
                ->addColumn('high_host_flap_threshold','string',array('limit' => 255, 'null' => true, "default" => 30))
                ->addColumn('service_check_timeout','integer',array('signed' => false, 'null' => true, "default" => 30))
                ->addColumn('host_check_timeout','integer',array('signed' => false, 'null' => true, "default" => 30))
                ->addColumn('event_handler_timeout','integer',array('signed' => false, 'null' => true, "default" => 30))
                ->addColumn('ocsp_timeout','integer',array('signed' => false, 'null' => true, "default" => 15))
                ->addColumn('ochp_timeout','integer',array('signed' => false, 'null' => true, "default" => 15))
                ->addColumn('ocsp_command','integer',array('signed' => false, 'null' => true))
                ->addColumn('ochp_command','integer',array('signed' => false, 'null' => true))
                ->addColumn('check_service_freshness','integer',array('limit' => 255, 'signed' => false, 'null' => true, "default" => 0))
                ->addColumn('freshness_check_interval','integer',array('signed' => false, 'null' => true, "default" => 30))
                ->addColumn('check_host_freshness','integer',array('limit' => 255, 'signed' => false, 'null' => true, "default" => 0))
                ->addColumn('enable_predictive_host_dependency_checks','integer',array('limit' => 255, 'signed' => false, 'null' => true, "default" => 1))
                ->addColumn('enable_predictive_service_dependency_checks','integer',array('limit' => 255, 'signed' => false, 'null' => true, "default" => 0))
                ->addColumn('debug_file_path','string',array('limit' => 255, 'null' => true))
                ->addColumn('debug_level','integer',array('signed' => false, 'null' => true, "default" => 0))
                ->addColumn('debug_verbosity','integer',array('limit' => 255, 'signed' => false, 'null' => true))
                ->addColumn('max_debug_file_size','integer',array('signed' => false, 'null' => true, "default" => 1000000))
                ->addForeignKey('global_host_event_handler', 'cfg_commands', 'command_id', array('delete'=> 'CASCADE'))
                ->addForeignKey('global_service_event_handler', 'cfg_commands', 'command_id', array('delete'=> 'CASCADE'))
                ->addForeignKey('ocsp_command', 'cfg_commands', 'command_id', array('delete'=> 'CASCADE'))
                ->addForeignKey('ochp_command', 'cfg_commands', 'command_id', array('delete'=> 'CASCADE'))
                ->addForeignKey('poller_id', 'cfg_pollers', 'poller_id', array('delete'=> 'CASCADE'))
                ->addIndex(array('global_host_event_handler'), array('unique' => false))
                ->addIndex(array('global_service_event_handler'), array('unique' => false))
                ->addIndex(array('ocsp_command'), array('unique' => false))
                ->addIndex(array('ochp_command'), array('unique' => false))
                ->addIndex(array('poller_id'), array('unique' => false))
                ->create();
        

        $cfg_engine_broker_module = $this->table('cfg_engine_broker_module', array('id' => false, 'primary_key' => 'bk_mod_id'));
        $cfg_engine_broker_module
                ->addColumn('bk_mod_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('poller_id','integer',array('signed' => false, 'null' => false))
                ->addColumn('broker_module','string',array('limit' => 255), array('null' => false))
                ->addForeignKey('poller_id', 'cfg_engine', 'poller_id', array('update'=> 'RESTRICT'))
                ->addIndex(array('poller_id'), array('unique' => false))
                ->create();
        
    }
}
    
