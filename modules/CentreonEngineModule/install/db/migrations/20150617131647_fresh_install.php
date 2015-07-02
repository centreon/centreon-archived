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
 */

/**
 * Description of 20150617131647_fresh_install
 *
 * @author tmechouet
 */
use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

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
                ->addColumn('enable_event_handlers','integer', array('limit' => MysqlAdapter::INT_TINY, 'null' => false, 'signed' => false, "default" => 1))
                ->addColumn('external_command_buffer_slots','integer', array('signed' => false, 'null' => false))
                ->addColumn('command_check_interval','string',array('limit' => 255, 'null' => true))
                ->addColumn('command_file','string',array('limit' => 255, 'null' => true))
                ->addColumn('use_syslog','integer',array('limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'null' => true, "default" => 1))
                ->addColumn('log_service_retries','integer',array('limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'null' => true, "default" => 1))
                ->addColumn('log_host_retries','integer',array('limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'null' => true, "default" => 0))
                ->addColumn('log_event_handlers','integer',array('limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'null' => true, "default" => 1))
                ->addColumn('log_initial_states','integer',array('limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'null' => true, "default" => 0))
                ->addColumn('log_external_commands','integer',array('limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'null' => true, "default" => 1))
                ->addColumn('log_passive_checks','integer',array('limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'null' => true, "default" => 1))
                ->addColumn('global_host_event_handler','integer',array('signed' => false, 'null' => true))
                ->addColumn('global_service_event_handler','integer',array('signed' => false, 'null' => true))
                ->addColumn('max_concurrent_checks','integer',array('signed' => false, 'null' => true))
                ->addColumn('check_result_reaper_frequency','integer',array('signed' => false, 'null' => true))
                ->addColumn('enable_flap_detection','integer',array('limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'null' => true, "default" => 0))
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
                ->addColumn('check_service_freshness','integer',array('limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'null' => true, "default" => 0))
                ->addColumn('freshness_check_interval','integer',array('signed' => false, 'null' => true, "default" => 30))
                ->addColumn('check_host_freshness','integer',array('limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'null' => true, "default" => 0))
                ->addColumn('enable_predictive_host_dependency_checks','integer',array('limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'null' => true, "default" => 1))
                ->addColumn('enable_predictive_service_dependency_checks','integer',array('limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'null' => true, "default" => 0))
                ->addColumn('debug_file_path','string',array('limit' => 255, 'null' => true))
                ->addColumn('debug_level','integer',array('signed' => false, 'null' => true, "default" => 0))
                ->addColumn('debug_verbosity','integer',array('limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'null' => true))
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
    