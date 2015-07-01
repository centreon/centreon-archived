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
        $cfg_auth_resources = $this->table('cfg_auth_resources', array('id' => false, 'primary_key' => array('ar_id')));
        $cfg_auth_resources
                ->addColumn('ar_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('ar_name','string', array('limit' => 255, 'null' => false, "default" => "Default"))
                ->addColumn('ar_slug','string', array('limit' => 255, 'null' => false))
                ->addColumn('ar_description','string', array('limit' => 255, 'null' => false, "default" => "Default description"))
                ->addColumn('ar_type','string', array('limit' => 50, 'null' => false))
                ->addColumn('ar_enable', 'string', array('limit' => 1, 'default' => '0', 'null' => true))
                ->save();       
        
        $cfg_auth_resources_info = $this->table('cfg_auth_resources_info', array('id' => false, 'primary_key' => array('ar_id', 'ari_name')));
        $cfg_auth_resources_info
                ->addColumn('ar_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('ari_name','string', array('limit' => 100,'null' => true))
                ->addColumn('ari_value','string', array('limit' => 255,'null' => true))
                ->addForeignKey('ar_id', 'cfg_auth_resources', 'ar_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->save();
              
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
                ->save();
 
    }
}