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
        $cfg_organizations = $this->table('cfg_organizations', array('id' => false, 'primary_key' => array('organization_id')));
        $cfg_organizations
                ->addColumn('organization_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('name','string', array('limit' => 255, 'null' => false))
                ->addColumn('shortname','string', array('limit' => 100, 'null' => true))
                ->addColumn('active','integer', array('default' =>  1, 'limit' => 255))
                ->addIndex(array('name'), array('unique' => true))
                ->addIndex(array('shortname'), array('unique' => true))
                ->create();
        
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
                ->create();
        
        $cfg_timezones = $this->table('cfg_timezones', array('id' => false, 'primary_key' => array('timezone_id')));
        $cfg_timezones
                ->addColumn('timezone_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('name','string', array('limit' => 200, 'null' => false))
                ->addColumn('offset','string', array('limit' => 200, 'null' => false))
                ->addColumn('dst_offset','string', array('limit' => 200, 'null' => false))
                ->addColumn('description','string', array('limit' => 255, 'null' => true))
                ->addColumn('slug','string', array('limit' => 255, 'null' => false))
                ->addIndex(array('name'), array('unique' => true))
                ->create();
        
        $cfg_languages = $this->table('cfg_languages', array('id' => false, 'primary_key' => array('language_id')));
        $cfg_languages
                ->addColumn('language_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('name','string', array('limit' => 200, 'null' => false))
                ->addColumn('slug','string', array('limit' => 255, 'null' => false))
                ->addColumn('description','string', array('limit' => 200, 'null' => true))
                ->addIndex(array('name'), array('unique' => true))
                ->create();
        
        $cfg_contacts = $this->table('cfg_contacts', array('id' => false, 'primary_key' => array('contact_id')));
        $cfg_contacts
                ->addColumn('contact_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('description','string', array('limit' => 200, 'null' => true))
                ->addColumn('slug','string', array('limit' => 255, 'null' => true))
                ->addColumn('timezone_id','integer', array('signed' => false, 'null' => true))
                ->addForeignKey('timezone_id', 'cfg_timezones', 'timezone_id', array('delete'=> 'SET_NULL', 'update'=> 'RESTRICT'))
                ->create();
        
        $cfg_usergroups = $this->table('cfg_usergroups', array('id' => false, 'primary_key' => array('usergroup_id')));
        $cfg_usergroups
                ->addColumn('usergroup_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('name','string', array('limit' => 255, 'null' => false))
                ->addColumn('slug','string', array('limit' => 255, 'null' => false))
                ->addColumn('description','string', array('limit' => 255, 'null' => true))
                ->addColumn('status','integer', array('null' => false, 'limit' => 255, 'signed' => false, 'default' => 1))
                ->addColumn('locked','integer', array('null' => false, 'limit' => 255, 'signed' => false, 'default' => 0))                
                ->create();
        
        
        $cfg_users = $this->table('cfg_users', array('id' => false, 'primary_key' => array('user_id')));
        $cfg_users
                ->addColumn('user_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('login','string', array('limit' => 200, 'null' => false))
                ->addColumn('slug','string', array('limit' => 255, 'null' => false))
                ->addColumn('password','string', array('limit' => 255, 'null' => false))
                ->addColumn('is_admin','integer', array('null' => false, 'limit' => 255, 'signed' => false, 'default' => 0))
                ->addColumn('is_locked','integer', array('null' => false, 'limit' => 255, 'signed' => false, 'default' => 0))       
                ->addColumn('is_activated','integer', array('null' => false, 'limit' => 255, 'signed' => false, 'default' => 1))
                ->addColumn('is_password_old','boolean', array('null' => false, 'limit' => 255, 'default' => 0))          
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
                ->create();
        
        $cfg_domains = $this->table('cfg_domains', array('id' => false, 'primary_key' => array('domain_id')));
        $cfg_domains
                ->addColumn('domain_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('name','string', array('limit' => 255, 'null' => false))
                ->addColumn('slug','string', array('limit' => 255, 'null' => false))
                ->addColumn('description','string', array('limit' => 255, 'null' => true))
                ->addColumn('isroot','integer', array('signed' => false, 'null' => false))
                ->addColumn('parent_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('icon_id','integer', array('signed' => false, 'null' => true))
                ->addIndex(array('name'), array('unique' => true))
                ->addForeignKey('parent_id', 'cfg_domains', 'domain_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('icon_id', 'cfg_binaries', 'binary_id', array('delete'=> 'SET_NULL', 'update'=> 'RESTRICT'))
                ->create();
        
        $cfg_acl_resources = $this->table('cfg_acl_resources', array('id' => false, 'primary_key' => 'acl_resource_id'));
        $cfg_acl_resources
                ->addColumn('acl_resource_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('name','string',array('limit' => 255), array('null' => false))
                ->addColumn('slug','string',array('limit' => 255), array('null' => false))
                ->addColumn('description','string',array('limit' => 255, 'null' => true))
                ->addColumn('organization_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('last_update','integer', array('signed' => false, 'null' => true))
                ->addColumn('status','integer', array('signed' => false, 'limit' => 255, 'null' => false, 'default' => 1))
                ->addForeignKey('organization_id', 'cfg_organizations', 'organization_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->create();
        
        $cfg_acl_resources_cache = $this->table('cfg_acl_resources_cache', array('id' => false, 'primary_key' => array('organization_id', 'acl_resource_id', 'resource_type', 'resource_id')));
        $cfg_acl_resources_cache
                ->addColumn('organization_id','integer', array('signed' => false,'null' => false))
                ->addColumn('acl_resource_id','integer', array('signed' => false,'null' => false))
                ->addColumn('resource_type','integer', array('signed' => false, 'limit' => 255, 'null' => false))
                ->addColumn('resource_id','integer', array('signed' => false,'null' => false))
                ->addIndex(array('organization_id'), array('unique' => false))
                ->addIndex(array('acl_resource_id'), array('unique' => false))
                ->addIndex(array('resource_type'), array('unique' => false))
                ->addIndex(array('resource_id'), array('unique' => false))
                ->create();

        
        $cfg_acl_resources_domains_relations = $this->table('cfg_acl_resources_domains_relations', array('id' => false, 'primary_key' => array('ardr_id')));
        $cfg_acl_resources_domains_relations
                ->addColumn('ardr_id','integer', array('identity' => true,'signed' => false, 'null' => false))
                ->addColumn('acl_resource_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('domain_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('type','integer', array('signed' => false, 'null' => false, 'limit' => 255, 'default' => 0))
                ->addIndex(array('acl_resource_id'), array('unique' => false))
                ->addIndex(array('domain_id'), array('unique' => false))
                ->addForeignKey('acl_resource_id', 'cfg_acl_resources', 'acl_resource_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('domain_id', 'cfg_domains', 'domain_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->create();
        
        
        $cfg_acl_resources_environments_relations = $this->table('cfg_acl_resources_environments_relations', array('id' => false, 'primary_key' => array('arer_id')));
        $cfg_acl_resources_environments_relations
                ->addColumn('arer_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('acl_resource_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('environment_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('type','integer', array('null' => false, 'limit' => 255, 'signed' => false, 'default' => 0))
                ->addIndex(array('acl_resource_id'), array('unique' => false))
                ->addIndex(array('environment_id'), array('unique' => false))
                ->addForeignKey('acl_resource_id', 'cfg_acl_resources', 'acl_resource_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('environment_id', 'cfg_environments', 'environment_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->create();
         
        $cfg_acl_resources_usergroups_relations = $this->table('cfg_acl_resources_usergroups_relations', array('id' => false, 'primary_key' => array('arugr_id')));
        $cfg_acl_resources_usergroups_relations
                ->addColumn('arugr_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('acl_resource_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('usergroup_id','integer', array('signed' => false, 'null' => true))
                ->addIndex(array('acl_resource_id'), array('unique' => false))
                ->addIndex(array('usergroup_id'), array('unique' => false))
                ->addForeignKey('acl_resource_id', 'cfg_acl_resources', 'acl_resource_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('usergroup_id', 'cfg_usergroups', 'usergroup_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->create();
          
        $cfg_acl_resource_type = $this->table('cfg_acl_resource_type', array('id' => false, 'primary_key' => array('acl_resource_type_id'))); 
        $cfg_acl_resource_type
                ->addColumn('acl_resource_type_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('name','string',array('limit' => 255, 'null' => false))
                ->addIndex(array('acl_resource_type_id'), array('unique' => false))
                ->create();
        
        $cfg_api_tokens = $this->table('cfg_api_tokens', array('id' => false, 'primary_key' => array('api_token_id')));
        $cfg_api_tokens
                ->addColumn('api_token_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('value','string', array('limit' => 200, 'null' => false))
                ->addColumn('user_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('updatedat','timestamp', array('null' => false))
                ->addIndex(array('user_id'), array('unique' => false))
                ->addForeignKey('user_id', 'cfg_users', 'user_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->create();
        
        $cfg_contacts_infos = $this->table('cfg_contacts_infos', array('id' => false, 'primary_key' => array('contact_info_id')));
        $cfg_contacts_infos
                ->addColumn('contact_info_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('info_key','string', array('limit' => 200, 'null' => false))
                ->addColumn('info_value','string', array('limit' => 200, 'null' => false))
                ->addColumn('contact_id','integer', array('signed' => false, 'null' => false))
                ->addIndex(array('contact_id'), array('unique' => false))
                ->addForeignKey('contact_id', 'cfg_contacts', 'contact_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->create();

        $cfg_options = $this->table('cfg_options', array('id' => false, 'primary_key' => array('option_id')));
        $cfg_options
                ->addColumn('option_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('group','string', array('limit' => 255, 'null' => false, 'default' =>  "default"))
                ->addColumn('key','string', array('limit' => 255, 'null' => true))
                ->addColumn('value','string', array('limit' => 255, 'null' => true))
                ->create();       
        
        $cfg_organizations_modules_relations = $this->table('cfg_organizations_modules_relations', array('id' => false, 'primary_key' => array('organization_id', 'module_id')));
        $cfg_organizations_modules_relations
                ->addColumn('organization_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('module_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('is_activated','integer', array('signed' => false, 'limit' => 255, 'null' => true, 'default' =>  0))
                ->addForeignKey('organization_id', 'cfg_organizations', 'organization_id', array('delete'=> 'CASCADE'))
                ->addForeignKey('module_id', 'cfg_modules', 'id', array('delete'=> 'CASCADE'))
                ->create();
        
        
        $cfg_organizations_users_relations = $this->table('cfg_organizations_users_relations', array('id' => false, 'primary_key' => array('organization_id', 'user_id')));
        $cfg_organizations_users_relations
                ->addColumn('organization_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('user_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('is_default','integer', array('signed' => false, 'limit' => 255, 'null' => true, 'default' =>  0))
                ->addColumn('is_admin','integer', array('signed' => false, 'limit' => 255, 'null' => true, 'default' =>  0))
                ->addForeignKey('organization_id', 'cfg_organizations', 'organization_id', array('delete'=> 'CASCADE'))
                ->addForeignKey('user_id', 'cfg_users', 'user_id', array('delete'=> 'CASCADE'))
                ->create();
        
        
        $cfg_searches = $this->table('cfg_searches', array('id' => false, 'primary_key' => array('search_id')));
        $cfg_searches
                ->addColumn('search_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('user_id','integer', array('null' => false, 'signed' => false))
                ->addColumn('route','string', array('limit' => 255, 'null' => false))
                ->addColumn('label','string', array('limit' => 255, 'null' => false))
                ->addColumn('searchText','text', array('null' => false))
                ->addIndex(array('user_id', 'label', 'route'), array('unique' => true))
                ->addForeignKey('user_id', 'cfg_users', 'user_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->create();
        
        
        
        $cfg_tags = $this->table('cfg_tags', array('id' => false, 'primary_key' => array('tag_id')));
        $cfg_tags->addColumn('tag_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('user_id','integer', array('null' => true, 'signed' => false))
                ->addColumn('tagname','string', array('limit' => 100, 'null' => false))
                ->addIndex(array('user_id', 'tagname'), array('unique' => true))
                ->create();
        
        
        $cfg_tags_contacts = $this->table('cfg_tags_contacts', array('id' => false, 'primary_key' => array('tag_id', 'resource_id')));
        $cfg_tags_contacts
                ->addColumn('tag_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('resource_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('template_id','integer', array('signed' => false, 'null' => true))
                ->addForeignKey('tag_id', 'cfg_tags', 'tag_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('resource_id', 'cfg_contacts', 'contact_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->create();

        $cfg_users_timezones_relations = $this->table('cfg_users_timezones_relations', array('id' => false, 'primary_key' => array('user_id', 'timezone_id')));
        $cfg_users_timezones_relations
                ->addColumn('user_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('timezone_id','integer', array('signed' => false, 'null' => false))
                ->addForeignKey('timezone_id', 'cfg_timezones', 'timezone_id', array('delete'=> 'CASCADE'))
                ->addForeignKey('user_id', 'cfg_users', 'user_id', array('delete'=> 'CASCADE'))
                ->create();
        
        $cfg_users_usergroups_relations = $this->table('cfg_users_usergroups_relations', array('id' => false, 'primary_key' => array('uugr_id')));
        $cfg_users_usergroups_relations
                ->addColumn('uugr_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('user_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('usergroup_id','integer', array('signed' => false, 'null' => true))
                ->addIndex(array('user_id'), array('unique' => false))
                ->addIndex(array('usergroup_id'), array('unique' => false))
                ->addForeignKey('user_id', 'cfg_users', 'user_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('usergroup_id', 'cfg_usergroups', 'usergroup_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->create(); 
        
        $this->execute('INSERT INTO cfg_organizations (organization_id, name, shortname, active) values (1, "Default organization", "default_organization", 1)');
        $this->execute('INSERT INTO cfg_organizations (organization_id, name, shortname, active) values (2, "Client organization", "client", 0)');
        
        $this->execute('INSERT INTO cfg_domains (domain_id, name, slug, description, isroot) values (1, "Network", "network", "Network domain", 1)');
        $this->execute('INSERT INTO cfg_domains (domain_id, name, slug, description, isroot) values (2, "Hardware", "hardware", "Hardware domain", 1)');
        $this->execute('INSERT INTO cfg_domains (domain_id, name, slug, description, isroot) values (3, "System", "system", "System domain", 1)');
        $this->execute('INSERT INTO cfg_domains (domain_id, name, slug, description, isroot) values (4, "Application", "application", "Application domain", 1)');
        $this->execute('INSERT INTO cfg_domains (domain_id, name, slug, description, isroot, parent_id) values (5, "CPU", "cpu", "Cpu domain", 1, 3)');
        $this->execute('INSERT INTO cfg_domains (domain_id, name, slug, description, isroot, parent_id) values (6, "Memory", "memory", "Memory domain", 1, 3)');
        $this->execute('INSERT INTO cfg_domains (domain_id, name, slug, description, isroot, parent_id) values (7, "Swap", "swap", "Swap domain", 1, 3)');
        $this->execute('INSERT INTO cfg_domains (domain_id, name, slug, description, isroot, parent_id) values (8, "Filesystem", "filesystem", "Filesystem domain", 1, 3)');
        $this->execute('INSERT INTO cfg_domains (domain_id, name, slug, description, isroot, parent_id) values (9, "Traffic", "traffic", "Traffic domain", 1, 2)');

        $this->execute('INSERT INTO cfg_environments (name, slug, description, level, organization_id) values ("Production", "production", "Production environment", 5, 1)');
        $this->execute('INSERT INTO cfg_environments (name, slug, description, level, organization_id) values ("Preproduction", "preproduction", "Preproduction environment", 10, 1)');

        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("ldap_dns_use_ssl", 0)');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("ldap_dns_use_tls", 0)');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("ldap_auth_enable", 0)');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("ldap_auto_import", 0)');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("ldap_srv_dns", 0)');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("ldap_dns_use_domain", 0)');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("ldap_search_timeout", 60)');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("ldap_search_limit", 60)');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("ldap_last_acl_update", 0)');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("ldap_contact_tmpl", 0)');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("color_up", "#19EE11")');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("color_down", "#F91E05")');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("color_unreachable", "#82CFD8")');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("color_ok", "#13EB3A")');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("color_warning", "#F8C706")');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("color_critical", "#F91D05")');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("color_pending", "#2AD1D4")');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("color_unknown", "#DCDADA")');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("session_expire", 120)');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("maxViewMonitoring", 0)');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("maxViewConfiguration", 30)');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("AjaxTimeReloadMonitoring", 15)');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("AjaxTimeReloadStatistic", 15)');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("AjaxFirstTimeReloadMonitoring", 0)');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("AjaxFirstTimeReloadStatistic", 0)');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("gmt", 1)');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("mailer_path_bin", "@BIN_MAIL@")');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("snmp_community", "public")');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("snmp_version", 1)');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("snmptt_unknowntrap_log_file", "snmpttunknown.log")');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("snmpttconvertmib_path_bin", "@INSTALL_DIR_CENTREON@/bin/snmpttconvertmib")');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("perl_library_path", "/usr/local/lib")');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("rrdtool_path_bin", "@BIN_RRDTOOL@")');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("rrdtool_version", "1.2")');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("debug_path", "@CENTREON_LOG@/")');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("debug_auth", 0)');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("debug_engine_import", 0)');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("debug_rrdtool", 0)');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("debug_ldap_import", 0)');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("debug_inventory", 0)');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("color_ack", "#FAED60")');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("color_host_down", "#FCC22A")');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("color_host_unreachable", "#9CD9F1")');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("color_line_critical", "#F96461")');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("color_downtime", "#FBC5E8")');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("enable_autologin", 0)');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("display_autologin_shortcut", 1)');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("monitoring_ack_svc", 1)');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("monitoring_dwt_duration", 3600)');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("monitoring_ack_active_checks", 1)');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("monitoring_ack_persistent", 1)');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("monitoring_ack_notify", 0)');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("monitoring_ack_sticky", 1)');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("monitoring_dwt_fixed", 1)');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("index_data", 1)');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("broker_etc_directory", "/etc/centreon-broker")');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("broker_module_directory", "/usr/share/centreon/lib/centreon-broker")');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("broker_logs_directory", "/var/log/centreon-broker")');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("broker_data_directory", "/var/lib/centreon-broker")');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("broker_cbmod_directory", "/usr/lib64/nagios")');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("broker_init_script", "/etc/init.d/cbd")');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("es_url", "http://localhost:9200")');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("es_security", "none")');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("es_user", "")');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("es_pass", "")');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("rrd_metric_path", "/var/lib/centreon/metrics/")');
        $this->execute('INSERT INTO cfg_options (`key`, `value`) values ("rrd_status_path", "/var/lib/centreon/status/")');
        
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Abidjan', '-00:00', '-00:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Accra', '-00:00', '-00:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Addis_Ababa', '+03:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Algiers', '+01:00', '+01:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Asmara', '+03:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Bamako', '-00:00', '-00:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Bangui', '+01:00', '+01:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Banjul', '-00:00', '-00:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Bissau', '-00:00', '-00:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Blantyre', '+02:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Brazzaville', '+01:00', '+01:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Bujumbura', '+02:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Cairo', '+02:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Casablanca', '-00:00', '+01:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Ceuta', '+01:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Conakry', '-00:00', '-00:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Dakar', '-00:00', '-00:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Dar_es_Salaam', '+03:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Djibouti', '+03:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Douala', '+01:00', '+01:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/El_Aaiun', '-00:00', '+01:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Freetown', '-00:00', '-00:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Gaborone', '+02:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Harare', '+02:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Johannesburg', '+02:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Juba', '+03:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Kampala', '+03:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Khartoum', '+03:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Kigali', '+02:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Kinshasa', '+01:00', '+01:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Lagos', '+01:00', '+01:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Libreville', '+01:00', '+01:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Lome', '-00:00', '-00:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Luanda', '+01:00', '+01:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Lubumbashi', '+02:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Lusaka', '+02:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Malabo', '+01:00', '+01:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Maputo', '+02:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Maseru', '+02:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Mbabane', '+02:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Mogadishu', '+03:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Monrovia', '-00:00', '-00:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Nairobi', '+03:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Ndjamena', '+01:00', '+01:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Niamey', '+01:00', '+01:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Nouakchott', '-00:00', '-00:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Ouagadougou', '-00:00', '-00:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Porto-Novo', '+01:00', '+01:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Sao_Tome', '-00:00', '-00:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Tripoli', '+02:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Tunis', '+01:00', '+01:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Africa/Windhoek', '+02:00', '+01:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Adak', '-10:00', '-09:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Anchorage', '-09:00', '-08:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Anguilla', '-04:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Antigua', '-04:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Araguaina', '-03:00', '-03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Argentina/Buenos_Aires', '-03:00', '-03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Argentina/Catamarca', '-03:00', '-03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Argentina/Cordoba', '-03:00', '-03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Argentina/Jujuy', '-03:00', '-03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Argentina/La_Rioja', '-03:00', '-03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Argentina/Mendoza', '-03:00', '-03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Argentina/Rio_Gallegos', '-03:00', '-03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Argentina/Salta', '-03:00', '-03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Argentina/San_Juan', '-03:00', '-03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Argentina/San_Luis', '-03:00', '-03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Argentina/Tucuman', '-03:00', '-03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Argentina/Ushuaia', '-03:00', '-03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Aruba', '-04:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Asuncion', '-03:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Atikokan', '-05:00', '-05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Bahia', '-03:00', '-03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Bahia_Banderas', '-06:00', '-05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Barbados', '-04:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Belem', '-03:00', '-03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Belize', '-06:00', '-06:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Blanc-Sablon', '-04:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Boa_Vista', '-04:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Bogota', '-05:00', '-05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Boise', '-07:00', '-06:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Cambridge_Bay', '-07:00', '-06:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Campo_Grande', '-03:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Cancun', '-06:00', '-05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Caracas', '-04:30', '-04:30')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Cayenne', '-03:00', '-03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Cayman', '-05:00', '-05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Chicago', '-06:00', '-05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Chihuahua', '-07:00', '-06:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Costa_Rica', '-06:00', '-06:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Creston', '-07:00', '-07:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Cuiaba', '-03:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Curacao', '-04:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Danmarkshavn', '-00:00', '-00:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Dawson', '-08:00', '-07:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Dawson_Creek', '-07:00', '-07:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Denver', '-07:00', '-06:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Detroit', '-05:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Dominica', '-04:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Edmonton', '-07:00', '-06:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Eirunepe', '-05:00', '-05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/El_Salvador', '-06:00', '-06:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Fortaleza', '-03:00', '-03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Glace_Bay', '-04:00', '-03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Godthab', '-03:00', '-02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Goose_Bay', '-04:00', '-03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Grand_Turk', '-05:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Grenada', '-04:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Guadeloupe', '-04:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Guatemala', '-06:00', '-06:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Guayaquil', '-05:00', '-05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Guyana', '-04:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Halifax', '-04:00', '-03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Havana', '-05:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Hermosillo', '-07:00', '-07:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Indiana/Indianapolis', '-05:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Indiana/Knox', '-06:00', '-05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Indiana/Marengo', '-05:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Indiana/Petersburg', '-05:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Indiana/Tell_City', '-06:00', '-05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Indiana/Vevay', '-05:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Indiana/Vincennes', '-05:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Indiana/Winamac', '-05:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Inuvik', '-07:00', '-06:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Iqaluit', '-05:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Jamaica', '-05:00', '-05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Juneau', '-09:00', '-08:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Kentucky/Louisville', '-05:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Kentucky/Monticello', '-05:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Kralendijk', '-04:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/La_Paz', '-04:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Lima', '-05:00', '-05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Los_Angeles', '-08:00', '-07:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Lower_Princes', '-04:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Maceio', '-03:00', '-03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Managua', '-06:00', '-06:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Manaus', '-04:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Marigot', '-04:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Martinique', '-04:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Matamoros', '-06:00', '-05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Mazatlan', '-07:00', '-06:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Menominee', '-06:00', '-05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Merida', '-06:00', '-05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Metlakatla', '-08:00', '-08:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Mexico_City', '-06:00', '-05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Miquelon', '-03:00', '-02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Moncton', '-04:00', '-03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Monterrey', '-06:00', '-05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Montevideo', '-02:00', '-03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Montserrat', '-04:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Nassau', '-05:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/New_York', '-05:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Nipigon', '-05:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Nome', '-09:00', '-08:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Noronha', '-02:00', '-02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/North_Dakota/Beulah', '-06:00', '-05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/North_Dakota/Center', '-06:00', '-05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/North_Dakota/New_Salem', '-06:00', '-05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Ojinaga', '-07:00', '-06:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Panama', '-05:00', '-05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Pangnirtung', '-05:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Paramaribo', '-03:00', '-03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Phoenix', '-07:00', '-07:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Port-au-Prince', '-05:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Port_of_Spain', '-04:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Porto_Velho', '-04:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Puerto_Rico', '-04:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Rainy_River', '-06:00', '-05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Rankin_Inlet', '-06:00', '-05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Recife', '-03:00', '-03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Regina', '-06:00', '-06:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Resolute', '-06:00', '-05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Rio_Branco', '-05:00', '-05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Santa_Isabel', '-08:00', '-07:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Santarem', '-03:00', '-03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Santiago', '-03:00', '-03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Santo_Domingo', '-04:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Sao_Paulo', '-02:00', '-03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Scoresbysund', '-01:00', '-00:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Sitka', '-09:00', '-08:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/St_Barthelemy', '-04:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/St_Johns', '-03:30', '-02:30')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/St_Kitts', '-04:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/St_Lucia', '-04:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/St_Thomas', '-04:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/St_Vincent', '-04:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Swift_Current', '-06:00', '-06:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Tegucigalpa', '-06:00', '-06:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Thule', '-04:00', '-03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Thunder_Bay', '-05:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Tijuana', '-08:00', '-07:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Toronto', '-05:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Tortola', '-04:00', '-04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Vancouver', '-08:00', '-07:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Whitehorse', '-08:00', '-07:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Winnipeg', '-06:00', '-05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Yakutat', '-09:00', '-08:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('America/Yellowknife', '-07:00', '-06:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Antarctica/Casey', '+08:00', '+08:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Antarctica/Davis', '+07:00', '+07:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Antarctica/DumontDUrville', '+10:00', '+10:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Antarctica/Macquarie', '+11:00', '+11:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Antarctica/Mawson', '+05:00', '+05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Antarctica/McMurdo', '+13:00', '+12:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Antarctica/Palmer', '-03:00', '-03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Antarctica/Rothera', '-03:00', '-03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Antarctica/Syowa', '+03:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Antarctica/Troll', '+02:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Antarctica/Vostok', '+06:00', '+06:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Arctic/Longyearbyen', '+01:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Aden', '+03:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Almaty', '+06:00', '+06:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Amman', '+02:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Anadyr', '+12:00', '+12:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Aqtau', '+05:00', '+05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Aqtobe', '+05:00', '+05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Ashgabat', '+05:00', '+05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Baghdad', '+03:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Bahrain', '+03:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Baku', '+04:00', '+05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Bangkok', '+07:00', '+07:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Beirut', '+02:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Bishkek', '+06:00', '+06:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Brunei', '+08:00', '+08:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Chita', '+08:00', '+08:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Choibalsan', '+08:00', '+09:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Colombo', '+05:30', '+05:30')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Damascus', '+02:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Dhaka', '+06:00', '+06:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Dili', '+09:00', '+09:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Dubai', '+04:00', '+04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Dushanbe', '+05:00', '+05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Gaza', '+02:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Hebron', '+02:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Ho_Chi_Minh', '+07:00', '+07:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Hong_Kong', '+08:00', '+08:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Hovd', '+07:00', '+08:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Irkutsk', '+08:00', '+08:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Jakarta', '+07:00', '+07:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Jayapura', '+09:00', '+09:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Jerusalem', '+02:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Kabul', '+04:30', '+04:30')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Kamchatka', '+12:00', '+12:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Karachi', '+05:00', '+05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Kathmandu', '+05:45', '+05:45')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Khandyga', '+09:00', '+09:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Kolkata', '+05:30', '+05:30')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Krasnoyarsk', '+07:00', '+07:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Kuala_Lumpur', '+08:00', '+08:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Kuching', '+08:00', '+08:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Kuwait', '+03:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Macau', '+08:00', '+08:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Magadan', '+10:00', '+10:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Makassar', '+08:00', '+08:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Manila', '+08:00', '+08:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Muscat', '+04:00', '+04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Nicosia', '+02:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Novokuznetsk', '+07:00', '+07:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Novosibirsk', '+06:00', '+06:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Omsk', '+06:00', '+06:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Oral', '+05:00', '+05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Phnom_Penh', '+07:00', '+07:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Pontianak', '+07:00', '+07:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Pyongyang', '+09:00', '+09:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Qatar', '+03:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Qyzylorda', '+06:00', '+06:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Rangoon', '+06:30', '+06:30')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Riyadh', '+03:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Sakhalin', '+10:00', '+10:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Samarkand', '+05:00', '+05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Seoul', '+09:00', '+09:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Shanghai', '+08:00', '+08:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Singapore', '+08:00', '+08:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Srednekolymsk', '+11:00', '+11:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Taipei', '+08:00', '+08:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Tashkent', '+05:00', '+05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Tbilisi', '+04:00', '+04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Tehran', '+03:30', '+04:30')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Thimphu', '+06:00', '+06:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Tokyo', '+09:00', '+09:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Ulaanbaatar', '+08:00', '+09:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Urumqi', '+06:00', '+06:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Ust-Nera', '+10:00', '+10:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Vientiane', '+07:00', '+07:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Vladivostok', '+10:00', '+10:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Yakutsk', '+09:00', '+09:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Yekaterinburg', '+05:00', '+05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Asia/Yerevan', '+04:00', '+04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Atlantic/Azores', '-01:00', '-00:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Atlantic/Bermuda', '-04:00', '-03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Atlantic/Canary', '-00:00', '+01:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Atlantic/Cape_Verde', '-01:00', '-01:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Atlantic/Faroe', '-00:00', '+01:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Atlantic/Madeira', '-00:00', '+01:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Atlantic/Reykjavik', '-00:00', '-00:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Atlantic/South_Georgia', '-02:00', '-02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Atlantic/St_Helena', '-00:00', '-00:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Atlantic/Stanley', '-03:00', '-03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Australia/Adelaide', '+10:30', '+09:30')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Australia/Brisbane', '+10:00', '+10:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Australia/Broken_Hill', '+10:30', '+09:30')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Australia/Currie', '+11:00', '+10:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Australia/Darwin', '+09:30', '+09:30')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Australia/Eucla', '+08:45', '+08:45')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Australia/Hobart', '+11:00', '+10:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Australia/Lindeman', '+10:00', '+10:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Australia/Lord_Howe', '+11:00', '+10:30')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Australia/Melbourne', '+11:00', '+10:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Australia/Perth', '+08:00', '+08:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Australia/Sydney', '+11:00', '+10:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Amsterdam', '+01:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Andorra', '+01:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Athens', '+02:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Belgrade', '+01:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Berlin', '+01:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Bratislava', '+01:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Brussels', '+01:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Bucharest', '+02:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Budapest', '+01:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Busingen', '+01:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Chisinau', '+02:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Copenhagen', '+01:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Dublin', '-00:00', '+01:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Gibraltar', '+01:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Guernsey', '-00:00', '+01:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Helsinki', '+02:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Isle_of_Man', '-00:00', '+01:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Istanbul', '+02:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Jersey', '-00:00', '+01:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Kaliningrad', '+02:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Kiev', '+02:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Lisbon', '-00:00', '+01:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Ljubljana', '+01:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/London', '-00:00', '+01:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Luxembourg', '+01:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Madrid', '+01:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Malta', '+01:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Mariehamn', '+02:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Minsk', '+03:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Monaco', '+01:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Moscow', '+03:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Oslo', '+01:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Paris', '+01:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Podgorica', '+01:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Prague', '+01:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Riga', '+02:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Rome', '+01:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Samara', '+04:00', '+04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/San_Marino', '+01:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Sarajevo', '+01:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Simferopol', '+03:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Skopje', '+01:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Sofia', '+02:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Stockholm', '+01:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Tallinn', '+02:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Tirane', '+01:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Uzhgorod', '+02:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Vaduz', '+01:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Vatican', '+01:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Vienna', '+01:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Vilnius', '+02:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Volgograd', '+03:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Warsaw', '+01:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Zagreb', '+01:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Zaporozhye', '+02:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Europe/Zurich', '+01:00', '+02:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Indian/Antananarivo', '+03:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Indian/Chagos', '+06:00', '+06:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Indian/Christmas', '+07:00', '+07:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Indian/Cocos', '+06:30', '+06:30')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Indian/Comoro', '+03:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Indian/Kerguelen', '+05:00', '+05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Indian/Mahe', '+04:00', '+04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Indian/Maldives', '+05:00', '+05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Indian/Mauritius', '+04:00', '+04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Indian/Mayotte', '+03:00', '+03:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Indian/Reunion', '+04:00', '+04:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Pacific/Apia', '+14:00', '+13:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Pacific/Auckland', '+13:00', '+12:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Pacific/Bougainville', '+11:00', '+11:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Pacific/Chatham', '+13:45', '+12:45')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Pacific/Chuuk', '+10:00', '+10:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Pacific/Easter', '-05:00', '-05:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Pacific/Efate', '+11:00', '+11:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Pacific/Enderbury', '+13:00', '+13:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Pacific/Fakaofo', '+13:00', '+13:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Pacific/Fiji', '+13:00', '+12:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Pacific/Funafuti', '+12:00', '+12:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Pacific/Galapagos', '-06:00', '-06:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Pacific/Gambier', '-08:59', '-08:59')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Pacific/Guadalcanal', '+11:00', '+11:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Pacific/Guam', '+10:00', '+10:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Pacific/Honolulu', '-10:00', '-10:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Pacific/Johnston', '-10:00', '-10:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Pacific/Kiritimati', '+14:00', '+14:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Pacific/Kosrae', '+11:00', '+11:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Pacific/Kwajalein', '+12:00', '+12:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Pacific/Majuro', '+12:00', '+12:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Pacific/Marquesas', '-09:30', '-09:30')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Pacific/Midway', '-11:00', '-11:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Pacific/Nauru', '+12:00', '+12:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Pacific/Niue', '-11:00', '-11:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Pacific/Norfolk', '+11:30', '+11:30')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Pacific/Noumea', '+11:00', '+11:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Pacific/Pago_Pago', '-11:00', '-11:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Pacific/Palau', '+09:00', '+09:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Pacific/Pitcairn', '-08:00', '-08:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Pacific/Pohnpei', '+11:00', '+11:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Pacific/Port_Moresby', '+10:00', '+10:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Pacific/Rarotonga', '-10:00', '-10:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Pacific/Saipan', '+10:00', '+10:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Pacific/Tahiti', '-10:00', '-10:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Pacific/Tarawa', '+12:00', '+12:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Pacific/Tongatapu', '+13:00', '+13:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Pacific/Wake', '+12:00', '+12:00')");
        $this->execute("INSERT INTO cfg_timezones (`name`, `offset`, `dst_offset`) VALUES ('Pacific/Wallis', '+12:00', '+12:00')");
    }
}
    
