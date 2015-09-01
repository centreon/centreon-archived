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
        $cfg_auth_resources = $this->table('cfg_auth_resources', array('id' => false, 'primary_key' => array('ar_id')));
        $cfg_auth_resources
                ->addColumn('ar_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('ar_name','string', array('limit' => 255, 'null' => false, "default" => "Default"))
                ->addColumn('ar_slug','string', array('limit' => 255, 'null' => false))
                ->addColumn('ar_description','string', array('limit' => 255, 'null' => false, "default" => "Default description"))
                ->addColumn('ar_type','string', array('limit' => 50, 'null' => false))
                ->addColumn('ar_enable', 'string', array('limit' => 1, 'default' => '0', 'null' => true))
                ->create();       
        
        $cfg_auth_resources_info = $this->table('cfg_auth_resources_info', array('id' => false, 'primary_key' => array('ar_id', 'ari_name')));
        $cfg_auth_resources_info
                ->addColumn('ar_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('ari_name','string', array('limit' => 100,'null' => true))
                ->addColumn('ari_value','string', array('limit' => 255,'null' => true))
                ->addForeignKey('ar_id', 'cfg_auth_resources', 'ar_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->create();
              
        $cfg_auth_resources_servers = $this->table('cfg_auth_resources_servers', array('id' => false, 'primary_key' => 'ldap_server_id'));
        $cfg_auth_resources_servers
                ->addColumn('ldap_server_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('auth_resource_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('server_address','string', array('limit' => 255,'null' => false))
                ->addColumn('server_port','integer', array('signed' => false, 'null' => false))
                ->addColumn('use_ssl','integer', array('signed' => false, 'null' => true))
                ->addColumn('use_tls','integer', array('signed' => false, 'null' => true))
                ->addColumn('server_order','integer', array('signed' => false, 'null' => true))
                ->addForeignKey('auth_resource_id', 'cfg_auth_resources', 'ar_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('auth_resource_id'), array('unique' => false))
                ->create();
 
    }
}