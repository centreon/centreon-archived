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

        $cfg_centreonbroker = $this->table('cfg_centreonbroker', array('id' => false, 'primary_key' => 'config_id'));
        $cfg_centreonbroker
                ->addColumn('config_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('poller_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('config_name','string',array('limit' => 100, 'null' => false))
                ->addColumn('flush_logs','integer', array('signed' => false, 'null' => true))
                ->addColumn('write_timestamp','integer', array('signed' => false, 'null' => true))
                ->addColumn('write_thread_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('event_queue_max_size','integer', array('signed' => false, 'null' => true))
                ->addForeignKey('poller_id', 'cfg_pollers', 'poller_id', array('delete'=> 'CASCADE'))
                ->create();
        
        $cfg_centreonbroker_info = $this->table('cfg_centreonbroker_info', array('id' => false, 'primary_key' => array('config_id', 'config_key')));
        $cfg_centreonbroker_info
                ->addColumn('config_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('config_key','string', array('limit' => 255,'null' => false))
                ->addColumn('config_value','string',array('limit' => 255, 'null' => false))
                ->addColumn('config_group','string',array('limit' => 50, 'null' => false))
                ->addColumn('config_group_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('grp_level','integer', array('signed' => false, 'null' => false, "default" => "0"))
                ->addColumn('subgrp_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('parent_grp_id','integer', array('signed' => false, 'null' => true))
                ->addIndex(array('config_id'), array('unique' => false))
                ->addIndex(array('config_id', 'config_group'), array('unique' => false))
                ->addForeignKey('config_id', 'cfg_centreonbroker', 'config_id', array('delete'=> 'CASCADE', 'update' => "RESTRICT"))
                ->create();
        
        $cfg_centreonbroker_paths = $this->table('cfg_centreonbroker_paths', array('id' => false, 'primary_key' => 'poller_id'));
        $cfg_centreonbroker_paths
                ->addColumn('poller_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('directory_config','string', array('limit' => 255,'null' => false))
                ->addColumn('directory_modules','string',array('limit' => 255, 'null' => false))
                ->addColumn('directory_data','string',array('limit' => 255, 'null' => false))
                ->addColumn('directory_logs','string',array('limit' => 255, 'null' => false))
                ->addColumn('directory_cbmod','string',array('limit' => 255, 'null' => false))
                ->addColumn('init_script','string',array('limit' => 255, 'null' => false))
                ->addForeignKey('poller_id', 'cfg_pollers', 'poller_id', array('delete'=> 'CASCADE'))
                ->create();
        
        
        $cfg_centreonbroker_pollervalues = $this->table('cfg_centreonbroker_pollervalues', array('id' => false, 'primary_key' => array('poller_id', 'name')));
        $cfg_centreonbroker_pollervalues
                ->addColumn('poller_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('name','string', array('limit' => 255,'null' => false))
                ->addColumn('value','string',array('limit' => 255, 'null' => false))
                ->addForeignKey('poller_id', 'cfg_pollers', 'poller_id', array('delete'=> 'CASCADE'))
                ->create();   
    }
}
    
