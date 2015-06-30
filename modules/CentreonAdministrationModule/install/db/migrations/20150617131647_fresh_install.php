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
use Phinx\Db\Adapter\MysqlAdapter;
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
        $cfg_organizations = $this->table('cfg_organizations', array('id' => false, 'primary_key' => array('organization_id')));
        $cfg_organizations
                ->addColumn('organization_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('name','string', array('limit' => 255, 'null' => false))
                ->addColumn('shortname','string', array('limit' => 100, 'null' => true))
                ->addColumn('active','integer', array('default' =>  1))
                ->addIndex(array('name'), array('unique' => true))
                ->addIndex(array('shortname'), array('unique' => true))
                ->save();
        
        $cfg_environments = $this->table('cfg_environments', array('id' => false, 'primary_key' => array('environment_id')));
        $cfg_environments
                ->addColumn('environment_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('name','string', array('limit' => 255, 'null' => false))
                ->addColumn('slug','string', array('limit' => 255, 'null' => false))
                ->addColumn('description','string', array('limit' => 255, 'null' => true))
                ->addColumn('level','integer', array('signed' => false, 'null' => false))
                ->addColumn('organization_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('icon_id','integer', array('signed' => false, 'null' => true))
                ->addIndex(array('name'), array('unique' => true))
                ->addForeignKey('organization_id', 'cfg_organizations', 'organization_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('icon_id', 'cfg_binaries', 'binary_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->save();
        
        $cfg_timezones = $this->table('cfg_timezones', array('id' => false, 'primary_key' => array('timezone_id')));
        $cfg_timezones
                ->addColumn('timezone_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('name','string', array('limit' => 200, 'null' => false))
                ->addColumn('offset','string', array('limit' => 200, 'null' => false))
                ->addColumn('dst_offset','string', array('limit' => 200, 'null' => false))
                ->addColumn('description','string', array('limit' => 255, 'null' => true))
                ->addColumn('slug','string', array('limit' => 255, 'null' => false))
                ->addIndex(array('name'), array('unique' => true))
                ->save();
        
        $cfg_languages = $this->table('cfg_languages', array('id' => false, 'primary_key' => array('language_id')));
        $cfg_languages
                ->addColumn('language_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('name','string', array('limit' => 200, 'null' => false))
                ->addColumn('slug','string', array('limit' => 255, 'null' => false))
                ->addColumn('description','string', array('limit' => 200, 'null' => true))
                ->addIndex(array('name'), array('unique' => true))
                ->save();
        
        $cfg_contacts = $this->table('cfg_contacts', array('id' => false, 'primary_key' => array('contact_id')));
        $cfg_contacts
                ->addColumn('contact_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('description','string', array('limit' => 200, 'null' => true))
                ->addColumn('slug','string', array('limit' => 255, 'null' => true))
                ->addColumn('timezone_id','integer', array('signed' => false,'null' => false))
                //->addForeignKey('timezone_id', 'cfg_timezones', 'timezone_id', array('delete'=> 'RESTRICT', 'update'=> 'RESTRICT'))
                ->save();
        
        $cfg_usergroups = $this->table('cfg_usergroups', array('id' => false, 'primary_key' => array('usergroup_id')));
        $cfg_usergroups
                ->addColumn('usergroup_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('name','string', array('limit' => 255, 'null' => false))
                ->addColumn('slug','string', array('limit' => 255, 'null' => false))
                ->addColumn('description','string', array('limit' => 255, 'null' => true))
                ->addColumn('status','integer', array('null' => false, 'signed' => false, 'default' => 1))
                ->addColumn('locked','integer', array('null' => false, 'signed' => false, 'default' => 0))                
                ->save();
        
        
        $cfg_users = $this->table('cfg_users', array('id' => false, 'primary_key' => array('user_id')));
        $cfg_users
                ->addColumn('user_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('login','string', array('limit' => 200, 'null' => false))
                ->addColumn('slug','string', array('limit' => 255, 'null' => false))
                ->addColumn('password','string', array('limit' => 255, 'null' => false))
                ->addColumn('is_admin','integer', array('null' => false, 'signed' => false, 'default' => 0))
                ->addColumn('is_locked','integer', array('null' => false, 'signed' => false, 'default' => 0))       
                ->addColumn('is_activated','integer', array('null' => false, 'signed' => false, 'default' => 1))
                ->addColumn('is_password_old','boolean', array('null' => false, 'default' => 0))          
                ->addColumn('language_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('timezone_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('contact_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('createdat','timestamp', array('null' => false))
                ->addColumn('updatedat','timestamp', array('null' => false))
                ->addColumn('auth_type','string', array('limit' => 200, 'null' => false))
                ->addColumn('firstname','string', array('limit' => 200, 'null' => true))
                ->addColumn('lastname','string', array('limit' => 200, 'null' => true))
                ->addColumn('autologin_key','string', array('limit' => 200, 'null' => true))
                ->addIndex(array('login'), array('unique' => true))
                ->addIndex(array('language_id'), array('unique' => false))
                ->addIndex(array('timezone_id'), array('unique' => false))
                ->addIndex(array('contact_id'), array('unique' => false))
                ->addForeignKey('language_id', 'cfg_languages', 'language_id', array('delete'=> 'SET_NULL', 'update'=> 'RESTRICT'))
                ->addForeignKey('timezone_id', 'cfg_timezones', 'timezone_id', array('delete'=> 'SET_NULL', 'update'=> 'RESTRICT'))
                ->addForeignKey('contact_id', 'cfg_contacts', 'contact_id', array('delete'=> 'SET_NULL', 'update'=> 'RESTRICT'))
                ->save();
        
        $cfg_domains = $this->table('cfg_domains', array('id' => false, 'primary_key' => array('domain_id')));
        $cfg_domains
                ->addColumn('domain_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('name','string', array('limit' => 255, 'null' => false))
                ->addColumn('slug','string', array('limit' => 255, 'null' => false))
                ->addColumn('description','string', array('limit' => 255, 'null' => true))
                ->addColumn('isroot','integer', array('signed' => false, 'null' => false))
                ->addColumn('parent_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('icon_id','integer', array('signed' => false, 'null' => true))
                ->addIndex(array('name'), array('unique' => true))
                ->addForeignKey('parent_id', 'cfg_domains', 'domain_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('icon_id', 'cfg_binaries', 'binary_id', array('delete'=> 'SET_NULL', 'update'=> 'RESTRICT'))
                ->save();
        
        $cfg_acl_resources = $this->table('cfg_acl_resources', array('id' => false, 'primary_key' => 'acl_resource_id'));
        $cfg_acl_resources
                ->addColumn('acl_resource_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('name','string',array('limit' => 255), array('null' => false))
                ->addColumn('slug','string',array('limit' => 255), array('null' => false))
                ->addColumn('description','string',array('limit' => 255, 'null' => true))
                ->addColumn('organization_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('last_update','integer', array('signed' => false, 'null' => true))
                ->addColumn('status','integer', array('signed' => false, 'null' => false, 'default' => 1))
                ->addForeignKey('organization_id', 'cfg_organizations', 'organization_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->save();
        
        $cfg_acl_resources_cache = $this->table('cfg_acl_resources_cache', array('id' => false, 'primary_key' => array('organization_id', 'acl_resource_id', 'resource_type', 'resource_id')));
        $cfg_acl_resources_cache
                ->addColumn('organization_id','integer', array('signed' => false,'null' => false))
                ->addColumn('acl_resource_id','integer', array('signed' => false,'null' => false))
                ->addColumn('resource_type','integer', array('signed' => false,'null' => false))
                ->addColumn('resource_id','integer', array('signed' => false,'null' => false))
                ->addIndex(array('organization_id'), array('unique' => false))
                ->addIndex(array('acl_resource_id'), array('unique' => false))
                ->addIndex(array('resource_type'), array('unique' => false))
                ->addIndex(array('resource_id'), array('unique' => false))
                ->save();

        
        $cfg_acl_resources_domains_relations = $this->table('cfg_acl_resources_domains_relations', array('id' => false, 'primary_key' => array('ardr_id')));
        $cfg_acl_resources_domains_relations
                ->addColumn('ardr_id','integer', array('identity' => true,'signed' => false, 'null' => false))
                ->addColumn('acl_resource_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('domain_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('type','integer', array('signed' => false, 'null' => false, 'default' => 0))
                ->addIndex(array('acl_resource_id'), array('unique' => false))
                ->addIndex(array('domain_id'), array('unique' => false))
                ->addForeignKey('acl_resource_id', 'cfg_acl_resources', 'acl_resource_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('domain_id', 'cfg_domains', 'domain_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->save();
        
        
        $cfg_acl_resources_environments_relations = $this->table('cfg_acl_resources_environments_relations', array('id' => false, 'primary_key' => array('arer_id')));
        $cfg_acl_resources_environments_relations
                ->addColumn('arer_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('acl_resource_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('environment_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('type','integer', array('null' => false, 'signed' => false, 'default' => 0))
                ->addIndex(array('acl_resource_id'), array('unique' => false))
                ->addIndex(array('environment_id'), array('unique' => false))
                ->addForeignKey('acl_resource_id', 'cfg_acl_resources', 'acl_resource_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('environment_id', 'cfg_environments', 'environment_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->save();
         
        $cfg_acl_resources_usergroups_relations = $this->table('cfg_acl_resources_usergroups_relations', array('id' => false, 'primary_key' => array('arugr_id')));
        $cfg_acl_resources_usergroups_relations
                ->addColumn('arugr_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('acl_resource_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('usergroup_id','integer', array('signed' => false, 'null' => true))
                ->addIndex(array('acl_resource_id'), array('unique' => false))
                ->addIndex(array('usergroup_id'), array('unique' => false))
                ->addForeignKey('acl_resource_id', 'cfg_acl_resources', 'acl_resource_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('usergroup_id', 'cfg_usergroups', 'usergroup_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->save();
          
        $cfg_acl_resource_type = $this->table('cfg_acl_resource_type', array('id' => false, 'primary_key' => array('acl_resource_type_id'))); 
        $cfg_acl_resource_type
                ->addColumn('acl_resource_type_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('name','string',array('limit' => 255, 'null' => false))
                ->addIndex(array('acl_resource_type_id'), array('unique' => false))
                ->save();
        
        $cfg_api_tokens = $this->table('cfg_api_tokens', array('id' => false, 'primary_key' => array('api_token_id')));
        $cfg_api_tokens
                ->addColumn('api_token_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('value','string', array('limit' => 200, 'null' => false))
                ->addColumn('user_id','integer', array('signed' => false, 'signed' => false, 'null' => false))
                ->addColumn('updatedat','timestamp', array('null' => false))
                ->addIndex(array('user_id'), array('unique' => false))
                ->addForeignKey('user_id', 'cfg_users', 'user_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->save();
        
  
        
        
        
        
        $cfg_contacts_infos = $this->table('cfg_contacts_infos', array('id' => false, 'primary_key' => array('contact_info_id')));
        $cfg_contacts_infos
                ->addColumn('contact_info_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('info_key','string', array('limit' => 200, 'null' => false))
                ->addColumn('info_value','string', array('limit' => 200, 'null' => false))
                ->addColumn('contact_id','integer', array('signed' => false, 'null' => false))
                ->addIndex(array('contact_id'), array('unique' => false))
                ->addForeignKey('contact_id', 'cfg_contacts', 'contact_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->save();
        
        
        
        
        $cfg_options = $this->table('cfg_options', array('id' => false, 'primary_key' => array('option_id')));
        $cfg_options
                ->addColumn('option_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('group','string', array('limit' => 255, 'null' => false, 'default' =>  "default"))
                ->addColumn('key','string', array('limit' => 255, 'null' => true))
                ->addColumn('value','string', array('limit' => 255, 'null' => true))
                ->save();       
        
        $cfg_organizations_modules_relations = $this->table('cfg_organizations_modules_relations', array('id' => false, 'primary_key' => array('organization_id', 'module_id')));
        $cfg_organizations_modules_relations
                ->addColumn('organization_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('module_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('is_activated','integer', array('signed' => false, 'null' => true, 'default' =>  0))
                ->addForeignKey('organization_id', 'cfg_organizations', 'organization_id', array('delete'=> 'CASCADE'))
                ->addForeignKey('module_id', 'cfg_modules', 'id', array('delete'=> 'CASCADE'))
                ->save();
        
        
        $cfg_organizations_users_relations = $this->table('cfg_organizations_users_relations', array('id' => false, 'primary_key' => array('organization_id', 'user_id')));
        $cfg_organizations_users_relations
                ->addColumn('organization_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('user_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('is_default','integer', array('signed' => false, 'null' => true, 'default' =>  0))
                ->addColumn('is_admin','integer', array('signed' => false, 'null' => true, 'default' =>  0))
                ->addForeignKey('organization_id', 'cfg_organizations', 'organization_id', array('delete'=> 'CASCADE'))
                ->addForeignKey('user_id', 'cfg_users', 'user_id', array('delete'=> 'CASCADE'))
                ->save();
        
        
        $cfg_searches = $this->table('cfg_searches', array('id' => false, 'primary_key' => array('search_id')));
        $cfg_searches
                ->addColumn('search_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('user_id','integer', array('null' => false, 'signed' => false))
                ->addColumn('route','string', array('limit' => 255, 'null' => false))
                ->addColumn('label','string', array('limit' => 255, 'null' => false))
                ->addColumn('searchText','string', array('limit' => MysqlAdapter::TEXT_REGULAR, 'null' => false))
                ->addIndex(array('user_id', 'label', 'route'), array('unique' => true))
                ->addForeignKey('user_id', 'cfg_users', 'user_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->save();
        
        
        
        $cfg_tags = $this->table('cfg_tags', array('id' => false, 'primary_key' => array('tag_id')));
        $cfg_tags
                ->addColumn('tag_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('user_id','integer', array('null' => true, 'signed' => false))
                ->addColumn('tagname','string', array('limit' => 100, 'null' => false))
                ->addIndex(array('user_id', 'tagname'), array('unique' => true))
                ->save();
        
        
        $cfg_tags_contacts = $this->table('cfg_tags_contacts', array('id' => false, 'primary_key' => array('tag_id', 'resource_id')));
        $cfg_tags_contacts
                ->addColumn('tag_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('resource_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('template_id','integer', array('signed' => false, 'null' => true))
                ->addForeignKey('tag_id', 'cfg_tags', 'tag_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('resource_id', 'cfg_contacts', 'contact_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->save();
        
        
        

        $cfg_users_timezones_relations = $this->table('cfg_users_timezones_relations', array('id' => false, 'primary_key' => array('user_id', 'timezone_id')));
        $cfg_users_timezones_relations
                ->addColumn('user_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('timezone_id','integer', array('signed' => false, 'null' => false))
                ->addForeignKey('timezone_id', 'cfg_timezones', 'timezone_id', array('delete'=> 'CASCADE'))
                ->addForeignKey('user_id', 'cfg_users', 'user_id', array('delete'=> 'CASCADE'))
                ->save();
        
        $cfg_users_usergroups_relations = $this->table('cfg_users_usergroups_relations', array('id' => false, 'primary_key' => array('uugr_id')));
        $cfg_users_usergroups_relations
                ->addColumn('uugr_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('user_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('usergroup_id','integer', array('signed' => false, 'null' => true))
                ->addIndex(array('user_id'), array('unique' => false))
                ->addIndex(array('usergroup_id'), array('unique' => false))
                ->addForeignKey('user_id', 'cfg_users', 'user_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('usergroup_id', 'cfg_usergroups', 'usergroup_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->save();
   
    }
}
    