<?php

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
        // Creation of table cfg_sessions
        $cfg_sessions = $this->table('cfg_sessions', array('id' => false, 'primary_key' => array('session_id')));
        $cfg_sessions->addColumn('session_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('session_start_time', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('last_reload', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('ip_address', 'string', array('limit' => 45, 'null' => false))
                ->addColumn('route', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('update_acl', 'boolean', array('null' => false, 'default' => 0))
                ->save();
                
        // Creation of table cfg_forms_massive_change
        $cfg_forms_massive_change = $this->table('cfg_forms_massive_change', array('id' => false, 'primary_key' => array('massive_change_id')));
        $cfg_forms_massive_change
                ->addColumn('massive_change_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('name', 'string', array('limit' => 45, 'null' => false))
                ->addColumn('route', 'string', array('limit' => 45, 'null' => false))
                ->save();
        
         // Creation of table cfg_forms_massive_change_fields_relations
        $cfg_forms_massive_change_fields_relations = $this->table('cfg_forms_massive_change_fields_relations', array('id' => false, 'primary_key' => array('massive_change_id', 'field_id')));
        $cfg_forms_massive_change_fields_relations
                ->addColumn('massive_change_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('field_id', 'integer', array('signed' => false, 'null' => false))
                ->save();
        
        // Creation of table cfg_informations
        $cfg_informations = $this->table('cfg_informations', array('id' => false, 'primary_key' => array('information_id')));
        $cfg_informations->addColumn('information_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('key', 'string', array('limit' => 25, 'null' => false))
                ->addColumn('value', 'string', array('limit' => 25, 'null' => false))
                ->save();
        
        // Creation of table cfg_hooks
        $cfg_hooks = $this->table('cfg_hooks', array('id' => false, 'primary_key' => array('hook_id')));
        $cfg_hooks->addColumn('hook_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('hook_name', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('hook_description', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('hook_type', 'boolean', array('null' => true, 'default' => 0))
                ->save();
        
        // Creation of table cfg_modules_hooks
        $cfg_modules_hooks = $this->table('cfg_modules_hooks', array('id' => false, 'primary_key' => array('hook_id', 'module_id')));
        $cfg_modules_hooks
                ->addColumn('hook_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('module_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('module_hook_name', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('module_hook_description', 'string', array('limit' => 255, 'null' => false))
                ->addForeignKey('hook_id', 'cfg_hooks', 'hook_id', array('delete'=> 'CASCADE', 'update'=> 'CASCADE'))
                ->addForeignKey('module_id', 'cfg_modules', 'id', array('delete'=> 'CASCADE', 'update'=> 'CASCADE'))
                ->save();
        
        // Creation of table cfg_modules_dependencies
        $cfg_modules_dependencies = $this->table('cfg_modules_dependencies', array('id' => false, 'primary_key' => 'id'));
        $cfg_modules_dependencies->addColumn('id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('parent_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('child_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('optionnal', 'boolean', array('null' => false, 'default' => 0))
                ->addForeignKey('parent_id', 'cfg_modules', 'id', array('delete'=> 'CASCADE', 'update'=> 'CASCADE'))
                ->addForeignKey('child_id', 'cfg_modules', 'id', array('delete'=> 'CASCADE', 'update'=> 'CASCADE'))
                ->save();
        
        // Creation of table cfg_menus
        $cfg_menus = $this->table('cfg_menus', array('id' => false, 'primary_key' => array('menu_id')));
        $cfg_menus->addColumn('menu_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('name', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('short_name', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('url', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('icon', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('icon_class', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('bgcolor', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('menu_order', 'integer', array('signed' => false, 'limit' => MysqlAdapter::INT_TINY, 'null' => true))
                ->addColumn('menu_block', 'string', array('limit' => 10, 'null' => false, 'default' => 'submenu'))
                ->addColumn('parent_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('module_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('parent_id', 'cfg_menus', 'menu_id', array('delete'=> 'CASCADE', 'update'=> 'CASCADE'))
                ->addForeignKey('module_id', 'cfg_modules', 'id', array('delete'=> 'CASCADE', 'update'=> 'CASCADE'))
                ->save();
        
        // Creation of table cfg_binaries
        $cfg_binaries = $this->table('cfg_binaries', array('id' => false, 'primary_key' => array('binary_id')));
        $cfg_binaries->addColumn('binary_id', 'integer', array('signed' => false, 'identity' => true, 'null' => true))
                ->addColumn('username', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('filename', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('checksum', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('mimetype', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('filetype', 'boolean', array('null' => false))
                ->addColumn('binary_content', 'binary', array('limit' => MysqlAdapter::BLOB_LONG, 'null' => false))
                ->addColumn('slug', 'string', array('limit' => 254, 'null' => false))
                ->addIndex(array('checksum', 'mimetype'), array('unique' => true))
                ->addIndex(array('filename', 'filetype'), array('unique' => true))
                ->save();
        
        // Creation of table cfg_binary_type
        $cfg_binary_type = $this->table('cfg_binary_type', array('id' => false, 'primary_key' => array('binary_type_id')));
        $cfg_binary_type->addColumn('binary_type_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('type_name', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('module_id', 'integer', array('signed' => false, 'null' => false))
                ->addIndex(array('type_name'), array('unique' => true))
                ->addIndex(array('module_id'), array('unique' => false))
                ->addForeignKey('module_id', 'cfg_modules', 'id', array('delete'=> 'CASCADE', 'update'=> 'CASCADE'))
                ->save();
        
        // Creation of table cfg_binary_type_binaries_relations
        $cfg_binary_type_binaries_relations = $this->table('cfg_binary_type_binaries_relations', array('id' => false, 'primary_key' => array('binary_type_id', 'binary_id')));
        $cfg_binary_type_binaries_relations->addColumn('binary_type_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('binary_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('binary_type_id', 'cfg_binary_type', 'binary_type_id', array('delete'=> 'CASCADE', 'update'=> 'CASCADE'))
                ->addForeignKey('binary_id', 'cfg_binaries', 'binary_id', array('delete'=> 'CASCADE', 'update'=> 'CASCADE'))
                ->save();
        
        // Creation of table cfg_bookmarks
        $cfg_bookmarks = $this->table('cfg_bookmarks', array('id' => false, 'primary_key' => array('bookmark_id')));
        $cfg_bookmarks->addColumn('bookmark_id', 'integer', array('signed' => false, 'identity' => true,'null' => false))
                ->addColumn('user_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('label', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('type', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('quick_access', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('short_url_code', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('route', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('is_always_visible', 'boolean', array('null' => true))
                ->addColumn('is_public', 'boolean', array('null' => true))
                ->save();
        
        // Creation of table cfg_forms
        $cfg_forms = $this->table('cfg_forms', array('id' => false, 'primary_key' => array('form_id')));
        $cfg_forms->addColumn('form_id', 'integer', array('signed' => false, 'identity' => true,'null' => false))
                ->addColumn('name', 'string', array('limit' => 45, 'null' => false))
                ->addColumn('route', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('redirect', 'boolean', array('null' => false, 'default' => 0))
                ->addColumn('redirect_route', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('module_id', 'integer', array('signed' => false, 'null' => false))
                ->addIndex(array('route'), array('unique' => true))
                ->addForeignKey('module_id', 'cfg_modules', 'id', array('delete'=> 'CASCADE', 'update'=> 'CASCADE'))
                ->save();
        
        // Creation of table cdg_forms_sections
        $cfg_forms_sections = $this->table('cfg_forms_sections', array('id' => false, 'primary_key' => array('section_id')));
        $cfg_forms_sections->addColumn('section_id', 'integer', array('signed' => false, 'identity' => true,'null' => false))
                ->addColumn('name', 'string', array('limit' => 45, 'null' => false))
                ->addColumn('rank', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('form_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('form_id', 'cfg_forms', 'form_id', array('delete'=> 'CASCADE', 'update'=> 'CASCADE'))
                ->save();
        
        // Creation of table cfg_forms_blocks
        $cfg_forms_blocks = $this->table('cfg_forms_blocks', array('id' => false, 'primary_key' => array('block_id')));
        $cfg_forms_blocks->addColumn('block_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('name', 'string', array('limit' => 45, 'null' => false))
                ->addColumn('rank', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('section_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('section_id', 'cfg_forms_sections', 'section_id', array('delete'=> 'CASCADE', 'update'=> 'CASCADE'))
                ->save();
        
        // Creation of table cfg_forms_fields
        $cfg_forms_fields = $this->table('cfg_forms_fields', array('id' => false, 'primary_key' => array('field_id')));
        $cfg_forms_fields->addColumn('field_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('name', 'string', array('limit' => 100, 'null' => false))
                ->addColumn('normalized_name', 'string', array('limit' => 100, 'null' => false))
                ->addColumn('label', 'string', array('limit' => 45, 'null' => false))
                ->addColumn('default_value', 'string', array('limit' => 45, 'null' => false))
                ->addColumn('attributes', 'string', array('limit' => MysqlAdapter::TEXT_REGULAR, 'null' => true))
                ->addColumn('advanced', 'boolean', array('null' => false, 'default' => 0))
                ->addColumn('mandatory', 'boolean', array('null' => false, 'default' => 0))
                ->addColumn('type', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('help', 'string', array('limit' => MysqlAdapter::TEXT_REGULAR, 'null' => true))
                ->addColumn('help_url', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('parent_field', 'string', array('limit' => 45, 'null' => true))
                ->addColumn('parent_value', 'string', array('limit' => 45, 'null' => true))
                ->addColumn('child_actions', 'string', array('limit' => 45, 'null' => true))
                ->addColumn('child_mandatory', 'boolean', array('null' => true, 'default' => 0))
                ->addColumn('show_label', 'boolean', array('null' => false, 'default' => 1))
                ->addColumn('module_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('width', 'string', array('limit' => 2, 'null' => true))
                ->addForeignKey('module_id', 'cfg_modules', 'id', array('delete'=> 'CASCADE', 'update'=> 'CASCADE'))
                ->addIndex(array('module_id'), array('unique' => false))
                ->save();
        
        // Creation of table cfg_forms_blocks_fields_relations
        $cfg_forms_blocks_fields_relations = $this->table('cfg_forms_blocks_fields_relations', array('id' => false, 'primary_key' => array('block_id', 'field_id')));
        $cfg_forms_blocks_fields_relations->addColumn('block_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('field_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('rank', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('product_version', 'string', array('limit' => 20, 'null' => false))
                ->addForeignKey('block_id', 'cfg_forms_blocks', 'block_id', array('delete'=> 'CASCADE', 'update'=> 'CASCADE'))
                ->addForeignKey('field_id', 'cfg_forms_fields', 'field_id', array('delete'=> 'CASCADE', 'update'=> 'CASCADE'))
                ->save();
        
        // Creation of table cfg_forms_validators
        $cfg_forms_validators = $this->table('cfg_forms_validators', array('id' => false, 'primary_key' => array('validator_id')));
        $cfg_forms_validators->addColumn('validator_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('name', 'string', array('limit' => 45, 'null' => false))
                ->addColumn('route', 'string', array('limit' => 255, 'null' => false))
                ->save();
        
        // Creation of table cfg_forms_fields_validators
        $cfg_forms_fields_validators = $this->table('cfg_forms_fields_validators_relations', array('id' => false, 'primary_key' => array('validator_id', 'field_id')));
        $cfg_forms_fields_validators->addColumn('field_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('validator_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('params', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('client_side_event', 'string', array('limit' => 45, 'null' => false))
                ->addColumn('server_side', 'boolean', array('null' => false, 'default' => 1))
                ->addForeignKey('validator_id', 'cfg_forms_validators', 'validator_id', array('delete'=> 'CASCADE', 'update'=> 'CASCADE'))
                ->addForeignKey('field_id', 'cfg_forms_fields', 'field_id', array('delete'=> 'CASCADE', 'update'=> 'CASCADE'))
                ->save();
        
        // Creation of table cfg_forms_wizards
        $cfg_forms_wizards = $this->table('cfg_forms_wizards', array('id' => false, 'primary_key' => array('wizard_id', 'module_id')));
        $cfg_forms_wizards->addColumn('wizard_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('name', 'string', array('limit' => 45, 'null' => false))
                ->addColumn('route', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('module_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('module_id', 'cfg_modules', 'id', array('delete'=> 'CASCADE', 'update'=> 'CASCADE'))
                ->save();
        
        // Creation of table cfg_forms_steps
        $cfg_forms_steps = $this->table('cfg_forms_steps', array('id' => false, 'primary_key' => array('step_id', 'wizard_id')));
        $cfg_forms_steps->addColumn('step_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('module_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('name', 'string', array('limit' => 45, 'null' => false))
                ->addColumn('wizard_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('rank', 'integer', array('signed' => false, 'null' => false))
                ->save();
        
        // Creation of table cfg_forms_steps_fields_relations
        $cfg_forms_steps_fields_relations = $this->table('cfg_forms_steps_fields_relations', array('id' => false, 'primary_key' => array('step_id', 'field_id')));
        $cfg_forms_steps_fields_relations->addColumn('step_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('field_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('rank', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('step_id', 'cfg_forms_steps', 'step_id', array('delete'=> 'CASCADE', 'update'=> 'CASCADE'))
                ->addForeignKey('field_id', 'cfg_forms_fields', 'field_id', array('delete'=> 'CASCADE', 'update'=> 'CASCADE'))
                ->addIndex(array('field_id'), array('unique' => false))
                ->addIndex(array('step_id'), array('unique' => false))
                ->save();
        
        $this->execute('INSERT INTO cfg_informations (`key`, `value`) values ("version", "2.99.2")');
    }
    
    /**
    * Migrate Up.
    */
    public function up()
    {

    }
}
