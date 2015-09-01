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
       
        $cfg_widgets_models = $this->table('cfg_widgets_models', array('id' => false, 'primary_key' => array('widget_model_id', 'module_id'))); 
        $cfg_widgets_models
                ->addColumn('widget_model_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('module_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('name','string',array('limit' => 255, 'null' => false))
                ->addColumn('shortname','string',array('limit' => 255, 'null' => false))
                ->addColumn('description','string',array('limit' => 255, 'null' => false))
                ->addColumn('version','string',array('limit' => 255, 'null' => false))
                ->addColumn('author','string',array('limit' => 255, 'null' => false))
                ->addColumn('email','string',array('limit' => 255, 'null' => true))
                ->addColumn('website','string',array('limit' => 255, 'null' => true))
                ->addColumn('keywords','string',array('limit' => 255, 'null' => true))
                ->addColumn('screenshot','string',array('limit' => 255, 'null' => true))
                ->addColumn('thumbnail','string',array('limit' => 255, 'null' => true))
                ->addColumn('isactivated','integer',array('signed' => false, 'null' => false))
                ->addColumn('isinstalled','integer',array('signed' => false, 'null' => false))
                ->addForeignKey('module_id', 'cfg_modules', 'id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->create();
        
        $cfg_custom_views = $this->table('cfg_custom_views', array('id' => false, 'primary_key' => 'custom_view_id'));
        $cfg_custom_views
                ->addColumn('custom_view_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('name','string',array('limit' => 255, 'null' => false))
                ->addColumn('mode','integer', array('signed' => false, 'limit' => 255, 'null' => true, "default" => 0))
                ->addColumn('locked','integer', array('signed' => false, 'limit' => 255, 'null' => true, "default" => 0))
                ->addColumn('owner_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('position','text',array('null' => true))
                ->create();    
        
        $cfg_custom_views_default = $this->table('cfg_custom_views_default', array('id' => false, 'primary_key' => array('user_id', 'custom_view_id')));
        $cfg_custom_views_default
                ->addColumn('user_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('custom_view_id','integer', array('signed' => false, 'null' => false))
                ->addIndex(array('user_id'), array('unique' => false))
                ->addIndex(array('custom_view_id'), array('unique' => false))
                ->addForeignKey('user_id', 'cfg_contacts', 'contact_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('custom_view_id', 'cfg_custom_views', 'custom_view_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->create();
        
        $cfg_custom_views_users_relations = $this->table('cfg_custom_views_users_relations', array('id' => false, 'primary_key' => 'custom_view_id'));
        $cfg_custom_views_users_relations
                ->addColumn('custom_view_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('user_id','integer', array('null' => false, 'signed' => false))
                ->addColumn('is_default','integer', array('signed' => false, 'null' => false))
                ->addForeignKey('user_id', 'cfg_users', 'user_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('custom_view_id', 'cfg_custom_views', 'custom_view_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->create();
               
        $cfg_organizations_widget_models_relations = $this->table('cfg_organizations_widget_models_relations', array('id' => false, 'primary_key' => array('organization_id', 'widget_model_id')));
        $cfg_organizations_widget_models_relations
                ->addColumn('organization_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('widget_model_id','integer', array('signed' => false, 'null' => true))
                ->addForeignKey('organization_id', 'cfg_organizations', 'organization_id', array('delete'=> 'CASCADE'))
                ->addForeignKey('widget_model_id', 'cfg_widgets_models', 'widget_model_id', array('delete'=> 'CASCADE'))
                ->create();
         
        $cfg_widgets = $this->table('cfg_widgets', array('id' => false, 'primary_key' => array('widget_id')));
        $cfg_widgets
                ->addColumn('widget_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('widget_model_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('title','string',array('limit' => 255, 'null' => false))
                ->addColumn('custom_view_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('organization_id','integer', array('signed' => false, 'null' => true))
                ->addIndex(array('widget_model_id'), array('unique' => false))
                ->addIndex(array('custom_view_id'), array('unique' => false))
                ->addForeignKey('organization_id', 'cfg_organizations', 'organization_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('custom_view_id', 'cfg_custom_views', 'custom_view_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('widget_model_id', 'cfg_widgets_models', 'widget_model_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->create();
             
        $cfg_widgets_parameters_fields_types = $this->table('cfg_widgets_parameters_fields_types', array('id' => false, 'primary_key' => array('field_type_id')));
        $cfg_widgets_parameters_fields_types
                ->addColumn('field_type_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('ft_typename','string', array('limit' => 50, 'null' => false))
                ->addColumn('is_connector','integer', array('signed' => false, 'limit' => 255, 'null' => false, "default" => "0"))
                ->create();
        
        $cfg_widgets_parameters = $this->table('cfg_widgets_parameters', array('id' => false, 'primary_key' => array('parameter_id')));
        $cfg_widgets_parameters
                ->addColumn('parameter_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('parameter_name','string', array('limit' => 255, 'null' => false))
                ->addColumn('parameter_code_name','string', array('limit' => 255, 'null' => false))
                ->addColumn('default_value','string', array('limit' => 255, 'null' => true))
                ->addColumn('header_title','string', array('limit' => 255, 'null' => true))
                ->addColumn('require_permission','string', array('limit' => 255, 'null' => false))
                ->addColumn('parameter_order','integer', array('signed' => false, 'limit' => 255, 'null' => false))
                ->addColumn('widget_model_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('field_type_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('is_filter','integer', array('signed' => false, 'null' => false, "default" => "0"))
                ->addIndex(array('widget_model_id'), array('unique' => false))
                ->addIndex(array('field_type_id'), array('unique' => false))
                ->addForeignKey('widget_model_id', 'cfg_widgets_models', 'widget_model_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('field_type_id', 'cfg_widgets_parameters_fields_types', 'field_type_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->create();
       
        $cfg_widgets_parameters_multiple_options = $this->table('cfg_widgets_parameters_multiple_options', array('id' => false, 'primary_key' => array('parameter_id')));
        $cfg_widgets_parameters_multiple_options
                ->addColumn('parameter_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('option_name','string', array('limit' => 255, 'null' => false))
                ->addColumn('option_value','string', array('limit' => 255, 'null' => false))
                ->addIndex(array('parameter_id'), array('unique' => false))
                ->addForeignKey('parameter_id', 'cfg_widgets_parameters', 'parameter_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->create();
           
        $cfg_widgets_parameters_range = $this->table('cfg_widgets_parameters_range', array('id' => false, 'primary_key' => array('parameter_id')));
        $cfg_widgets_parameters_range
                ->addColumn('parameter_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('min_range','integer', array('signed' => false, 'null' => false))
                ->addColumn('max_range','integer', array('signed' => false, 'null' => false))
                ->addColumn('step','integer', array('signed' => false, 'null' => false))
                ->addIndex(array('parameter_id'), array('unique' => false))
                ->addForeignKey('parameter_id', 'cfg_widgets_parameters', 'parameter_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->create();
        
        $cfg_widgets_preferences = $this->table('cfg_widgets_preferences', array('id' => false, 'primary_key' => array('widget_id', 'parameter_id')));
        $cfg_widgets_preferences
                ->addColumn('widget_id','integer', array('signed' => false, 'null' => true))
                ->addColumn('parameter_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('preference_value','string', array('limit' => 255, 'null' => false))
                ->addColumn('comparator','integer', array('signed' => false, 'limit' => 255, 'null' => true))
                ->addIndex(array('parameter_id'), array('unique' => false))
                ->addIndex(array('widget_id'), array('unique' => false))
                ->addForeignKey('widget_id', 'cfg_widgets', 'widget_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('parameter_id', 'cfg_widgets_parameters', 'parameter_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->create();     
    }

    /**
    * Migrate Up.
    */
    public function up()
    {
        $this->execute('INSERT INTO cfg_widgets_parameters_fields_types ("field_type_id", "ft_typename", "is_connector") values (1, "text", 0)');
        $this->execute('INSERT INTO cfg_widgets_parameters_fields_types ("field_type_id", "ft_typename", "is_connector") values (2, "boolean", 0)');
        $this->execute('INSERT INTO cfg_widgets_parameters_fields_types ("field_type_id", "ft_typename", "is_connector") values (3, "hidden", 0)');
        $this->execute('INSERT INTO cfg_widgets_parameters_fields_types ("field_type_id", "ft_typename", "is_connector") values (4, "password", 0)');
        $this->execute('INSERT INTO cfg_widgets_parameters_fields_types ("field_type_id", "ft_typename", "is_connector") values (5, "list", 0)');
        $this->execute('INSERT INTO cfg_widgets_parameters_fields_types ("field_type_id", "ft_typename", "is_connector") values (6, "range", 0)');
        $this->execute('INSERT INTO cfg_widgets_parameters_fields_types ("field_type_id", "ft_typename", "is_connector") values (7, "compare", 0)');
        $this->execute('INSERT INTO cfg_widgets_parameters_fields_types ("field_type_id", "ft_typename", "is_connector") values (8, "sort", 0)');
        $this->execute('INSERT INTO cfg_widgets_parameters_fields_types ("field_type_id", "ft_typename", "is_connector") values (9, "date", 0)');
        $this->execute('INSERT INTO cfg_widgets_parameters_fields_types ("field_type_id", "ft_typename", "is_connector") values (10, "host", 1)');
        $this->execute('INSERT INTO cfg_widgets_parameters_fields_types ("field_type_id", "ft_typename", "is_connector") values (11, "hostTemplate", 1)');
        $this->execute('INSERT INTO cfg_widgets_parameters_fields_types ("field_type_id", "ft_typename", "is_connector") values (12, "serviceTemplate", 1)');
        $this->execute('INSERT INTO cfg_widgets_parameters_fields_types ("field_type_id", "ft_typename", "is_connector") values (13, "hostgroup", 1)');
        $this->execute('INSERT INTO cfg_widgets_parameters_fields_types ("field_type_id", "ft_typename", "is_connector") values (14, "servicegroup", 1)');
        $this->execute('INSERT INTO cfg_widgets_parameters_fields_types ("field_type_id", "ft_typename", "is_connector") values (15, "service", 1)');
    }
}
    
