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
    
