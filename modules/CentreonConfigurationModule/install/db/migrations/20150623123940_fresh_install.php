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
        
        // Creation of table cfg_connectors
        $cfg_connectors = $this->table('cfg_connectors', array('id' => false, 'primary_key' => array('id')));
        $cfg_connectors->addColumn('id', 'integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('name', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('slug', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('description', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('command_line', 'string', array('limit' => 512, 'null' => false))
                ->addColumn('enabled', 'integer', array('signed' => false, 'null' => false, 'default' => 1))
                ->addColumn('created', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('modified', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('organization_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('organization_id', 'cfg_organizations', 'organization_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('name'), array('unique' => true))
                ->addIndex(array('enabled'), array('unique' => false))
                ->create();
        
        // Creation of table cfg_timeperiods
        $cfg_timeperiods = $this->table('cfg_timeperiods', array('id' => false, 'primary_key' => array('tp_id')));
        $cfg_timeperiods->addColumn('tp_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('tp_name', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('tp_slug', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('tp_alias', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('tp_sunday', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('tp_monday', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('tp_tuesday', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('tp_wednesday', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('tp_thursday', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('tp_friday', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('tp_saturday', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('organization_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('organization_id', 'cfg_organizations', 'organization_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->create();
        
        // Creation of table cfg_resources
        $cfg_resources = $this->table('cfg_resources', array('id' => false, 'primary_key' => array('resource_id')));
        $cfg_resources->addColumn('resource_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('resource_name', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('resource_slug', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('resource_line', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('resource_comment', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('resource_activate', 'string', array('limit' => 1, 'null' => true))
                ->addColumn('organization_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('organization_id', 'cfg_organizations', 'organization_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->create();
        
         // Creation of table cfg_nodes
        $cfg_nodes = $this->table('cfg_nodes', array('id' => false, 'primary_key' => array('node_id')));
        $cfg_nodes->addColumn('node_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('name', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('ip_address', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('enable', 'integer', array('limit' => 1, 'signed' => false, 'null' => false, 'default' => 1))
                ->addColumn('multiple_poller', 'integer', array('limit' => 1, 'signed' => false, 'null' => false, 'default' => 0))
                ->addIndex(array('name'))
                ->create();
        
         // Creation of table cfg_pollers
        $cfg_pollers = $this->table('cfg_pollers', array('id' => false, 'primary_key' => array('poller_id')));
        $cfg_pollers->addColumn('poller_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('node_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('name', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('slug', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('port', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('one_per_retention', 'integer', array('limit' => 1, 'signed' => false, 'null' => false, 'default' => 1))
                ->addColumn('tmpl_name', 'string', array('limit' => 50, 'null' => false))
                ->addColumn('enable', 'integer', array('limit' => 1, 'signed' => false, 'null' => false, 'default' => 1))
                ->addColumn('organization_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('node_id', 'cfg_nodes', 'node_id', array('delete'=> 'CASCADE', 'update'=> 'CASCADE'))
                ->addForeignKey('organization_id', 'cfg_organizations', 'organization_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->create();
        
        // Creation of table cfg_acl_resources_hosts_params
        $cfg_acl_resources_hosts_params = $this->table('cfg_acl_resources_hosts_params', array('id' => false, 'primary_key' => array('acl_resource_id')));
        $cfg_acl_resources_hosts_params->addColumn('acl_resource_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('all_hosts', 'integer', array('limit' => MysqlAdapter::INT_TINY, 'null' => true, 'default' => 0))
                ->addForeignKey('acl_resource_id', 'cfg_acl_resources', 'acl_resource_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('acl_resource_id'))
                ->create();

        // Creation of table cfg_commands
        $cfg_commands = $this->table('cfg_commands', array('id' => false, 'primary_key' => array('command_id')));
        $cfg_commands->addColumn('command_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('connector_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('command_name', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('command_slug', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('command_line', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('command_example', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('command_type', 'integer', array('limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'null' => false, 'default' => 2))
                ->addColumn('enable_shell', 'integer', array('signed' => false, 'null' => false, 'default' => 0))
                ->addColumn('command_comment', 'text', array('null' => true))
                ->addColumn('graph_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('cmd_cat_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('organization_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('connector_id', 'cfg_connectors', 'id', array('delete'=> 'SET_NULL', 'update'=> 'CASCADE'))
                ->addForeignKey('organization_id', 'cfg_organizations', 'organization_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->create();
        
        
        // Creation of table cfg_commands_categories_relations
        $cfg_commands_categories_relations = $this->table('cfg_commands_categories_relations', array('id' => false, 'primary_key' => array('cmd_cat_id')));
        $cfg_commands_categories_relations->addColumn('cmd_cat_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('category_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('command_command_id', 'integer', array('signed' => false, 'null' => true))
                ->create();

        // Creation of table cfg_commands_categories
        $cfg_commands_categories = $this->table('cfg_commands_categories', array('id' => false, 'primary_key' => array('cmd_category_id')));
        $cfg_commands_categories->addColumn('cmd_category_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('category_name', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('category_alias', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('category_order', 'integer', array('signed' => false, 'null' => false))
                ->create();

        // Creation of table cfg_dependencies
        $cfg_dependencies = $this->table('cfg_dependencies', array('id' => false, 'primary_key' => array('dep_id')));
        $cfg_dependencies->addColumn('dep_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('name', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('description', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('inherits_parent', 'boolean', array('null' => true))
                ->addColumn('execution_failure_criteria', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('notification_failure_criteria', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('dep_comment', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('organization_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('organization_id', 'cfg_organizations', 'organization_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->create();

        // Creation of table cfg_downtimes
        $cfg_downtimes = $this->table('cfg_downtimes', array('id' => false, 'primary_key' => array('dt_id')));
        $cfg_downtimes->addColumn('dt_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('dt_name', 'string', array('limit' => 100, 'null' => false))
                ->addColumn('dt_description', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('dt_activate', 'integer', array('signed' => false, 'null' => true, 'default' => 1))
                ->addColumn('organization_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('organization_id', 'cfg_organizations', 'organization_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('dt_id', 'dt_activate'))
                ->create();

        // Creation of table cfg_engine_macros
        $cfg_engine_macros = $this->table('cfg_engine_macros', array('id' => false, 'primary_key' => array('macro_id')));
        $cfg_engine_macros->addColumn('macro_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('macro_name', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('organization_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('organization_id', 'cfg_organizations', 'organization_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->create();

        // Creation of table cfg_hosts
        $cfg_hosts = $this->table('cfg_hosts', array('id' => false, 'primary_key' => array('host_id')));
        $cfg_hosts->addColumn('host_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('command_command_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('command_command_id_arg1', 'text', array('null' => true))
                ->addColumn('timeperiod_tp_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('command_command_id2', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('command_command_id_arg2', 'text', array('null' => true))
                ->addColumn('host_name', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('host_slug', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('host_alias', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('host_address', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('host_max_check_attempts', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('host_check_interval', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('host_retry_check_interval', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('host_active_checks_enabled', 'string', array('limit' => 1, 'null' => true))
                ->addColumn('host_checks_enabled', 'string', array('limit' => 1, 'null' => true))
                ->addColumn('initial_state', 'string', array('limit' => 1, 'null' => true))
                ->addColumn('host_obsess_over_host', 'string', array('limit' => 1, 'null' => true))
                ->addColumn('host_check_freshness', 'string', array('limit' => 1, 'null' => true))
                ->addColumn('host_freshness_threshold', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('host_event_handler_enabled', 'string', array('limit' => 1, 'null' => true))
                ->addColumn('host_low_flap_threshold', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('host_high_flap_threshold', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('host_flap_detection_enabled', 'string', array('limit' => 1, 'null' => true))
                ->addColumn('flap_detection_options', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('host_snmp_community', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('host_snmp_version', 'string', array('limit' => 5, 'null' => true))
                ->addColumn('host_location', 'integer', array('signed' => false, 'null' => true, 'default' => 0))
                ->addColumn('host_comment', 'text', array('null' => true))
                ->addColumn('host_register', 'string', array('limit' => 1, 'null' => true))
                ->addColumn('host_activate', 'string', array('limit' => 1, 'null' => true, 'default' => '1'))
                ->addColumn('organization_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('environment_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('poller_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('timezone_id', 'integer', array('signed' => false, 'null' => true))
                ->addForeignKey('command_command_id', 'cfg_commands', 'command_id', array('delete'=> 'SET_NULL', 'update'=> 'RESTRICT'))
                ->addForeignKey('command_command_id2', 'cfg_commands', 'command_id', array('delete'=> 'SET_NULL', 'update'=> 'RESTRICT'))
                ->addForeignKey('timeperiod_tp_id', 'cfg_timeperiods', 'tp_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('organization_id', 'cfg_organizations', 'organization_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('environment_id', 'cfg_environments', 'environment_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('poller_id', 'cfg_pollers', 'poller_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('timezone_id', 'cfg_timezones', 'timezone_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('command_command_id'))
                ->addIndex(array('command_command_id2'))
                ->addIndex(array('timeperiod_tp_id'))
                ->addIndex(array('host_name', 'organization_id'))
                ->addIndex(array('host_id', 'host_register'))
                ->addIndex(array('host_alias'))
                ->addIndex(array('host_register'))
                ->create();
        
        
        // Creation of table cfg_dependencies_hostparents_relations
        $cfg_dependencies_hostparents_relations = $this->table('cfg_dependencies_hostparents_relations', array('id' => false, 'primary_key' => array('dhpr_id')));
        $cfg_dependencies_hostparents_relations->addColumn('dhpr_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('dependency_dep_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('host_host_id', 'integer', array('signed' => false, 'null' => true))
                ->addForeignKey('dependency_dep_id', 'cfg_dependencies', 'dep_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('host_host_id', 'cfg_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('dependency_dep_id'))
                ->addIndex(array('host_host_id'))
                ->create();
        
        // Creation of table cfg_customvariables_hosts
        $cfg_customvariables_hosts = $this->table('cfg_customvariables_hosts', array('id' => false, 'primary_key' => array('host_macro_id')));
        $cfg_customvariables_hosts->addColumn('host_macro_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('host_macro_name', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('host_macro_value', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('is_password', 'integer', array('limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'null' => true))
                ->addColumn('host_host_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('host_host_id', 'cfg_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('host_host_id'))
                ->create();
        
        // Creation of table cfg_acl_resources_hosts_relations
        $cfg_acl_resources_hosts_relations = $this->table('cfg_acl_resources_hosts_relations', array('id' => false, 'primary_key' => array('arhr_id')));
        $cfg_acl_resources_hosts_relations->addColumn('arhr_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('acl_resource_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('host_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('type', 'integer', array('limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'null' => false, 'default' => 0))
                ->addForeignKey('acl_resource_id', 'cfg_acl_resources', 'acl_resource_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('host_id', 'cfg_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('acl_resource_id'))
                ->addIndex(array('host_id'))
                ->create();

        // Creation of table cfg_notification_methods
        $cfg_notification_methods = $this->table('cfg_notification_methods', array('id' => false, 'primary_key' => array('method_id')));
        $cfg_notification_methods->addColumn('method_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('name', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('slug', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('description', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('interval', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('interval_unit', 'string', array('limit' => 1, 'null' => false))
                ->addColumn('command_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('status', 'string', array('limit' => 32, 'null' => false))
                ->addColumn('types', 'string', array('limit' => 32, 'null' => false))
                ->addColumn('start', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('start_unit', 'string', array('limit' => 1, 'null' => false))
                ->addColumn('end', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('end_unit', 'string', array('limit' => 1, 'null' => false))
                ->addColumn('organization_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('command_id', 'cfg_commands', 'command_id', array('delete'=> 'CASCADE', 'update'=> 'CASCADE'))
                ->addForeignKey('organization_id', 'cfg_organizations', 'organization_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('name'))
                ->addIndex(array('slug'))
                ->create();

        // Creation of table cfg_notification_rules
        $cfg_notification_rules = $this->table('cfg_notification_rules', array('id' => false, 'primary_key' => array('rule_id')));
        $cfg_notification_rules->addColumn('rule_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('name', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('slug', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('description', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('method_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('owner_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('timeperiod_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('enabled', 'string', array('limit' => 1, 'null' => false, 'default' => 1))
                ->addColumn('organization_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('method_id', 'cfg_notification_methods', 'method_id', array('delete'=> 'CASCADE', 'update'=> 'CASCADE'))
                ->addForeignKey('owner_id', 'cfg_users', 'user_id', array('delete'=> 'CASCADE', 'update'=> 'CASCADE'))
                ->addForeignKey('timeperiod_id', 'cfg_timeperiods', 'tp_id', array('delete'=> 'CASCADE', 'update'=> 'CASCADE'))
                ->addForeignKey('organization_id', 'cfg_organizations', 'organization_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->create();

        // Creation of table cfg_notification_rules_contacts_relations
        $cfg_notification_rules_contacts_relations = $this->table('cfg_notification_rules_contacts_relations', array('id' => false, 'primary_key' => array('rule_id', 'contact_id')));
        $cfg_notification_rules_contacts_relations->addColumn('rule_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('contact_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('rule_id', 'cfg_notification_rules', 'rule_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('contact_id', 'cfg_contacts', 'contact_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->create();

        // Creation of table cfg_notification_rules_hosts_relations
        $cfg_notification_rules_hosts_relations = $this->table('cfg_notification_rules_hosts_relations', array('id' => false, 'primary_key' => array('rule_id', 'host_id')));
        $cfg_notification_rules_hosts_relations->addColumn('rule_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('host_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('rule_id', 'cfg_notification_rules', 'rule_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('host_id', 'cfg_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->create();

        // Creation of table cfg_notification_rules_tags_relations
        $cfg_notification_rules_tags_relations = $this->table('cfg_notification_rules_tags_relations', array('id' => false, 'primary_key' => array('rule_id', 'tag_id')));
        $cfg_notification_rules_tags_relations->addColumn('rule_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('tag_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('resource_type', 'integer', array('limit' => 1, 'signed' => false, 'null' => false))
                ->addForeignKey('rule_id', 'cfg_notification_rules', 'rule_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('tag_id', 'cfg_tags', 'tag_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->create();
        
        // Creation of table cfg_pollers_commands_relations
        $cfg_pollers_commmands_relations = $this->table('cfg_pollers_commands_relations', array('id' => false, 'primary_key' => array('poller_id', 'command_id')));
        $cfg_pollers_commmands_relations->addColumn('poller_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('command_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('command_order', 'integer', array('limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'null' => true))
                ->addForeignKey('poller_id', 'cfg_pollers', 'poller_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('command_id', 'cfg_commands', 'command_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('poller_id'))
                ->addIndex(array('command_id'))
                ->create();

        // Creation of table cfg_resources_instances_relations
        $cfg_resources_instances_relations = $this->table('cfg_resources_instances_relations', array('id' => false, 'primary_key' => array('resource_id', 'instance_id')));
        $cfg_resources_instances_relations->addColumn('resource_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('instance_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('resource_id', 'cfg_resources', 'resource_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('instance_id', 'cfg_pollers', 'poller_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('resource_id'))
                ->addIndex(array('instance_id'))
                ->create();

        // Creation of table cfg_services
        $cfg_services = $this->table('cfg_services', array('id' => false, 'primary_key' => array('service_id')));
        $cfg_services->addColumn('service_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('service_template_model_stm_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('command_command_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('timeperiod_tp_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('command_command_id2', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('service_description', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('service_slug', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('service_alias', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('service_is_volatile', 'string', array('limit' => 1, 'null' => true, 'default' => '2'))
                ->addColumn('service_max_check_attempts', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('service_normal_check_interval', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('service_retry_check_interval', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('service_active_checks_enabled', 'string', array('limit' => 1, 'null' => true))
                ->addColumn('initial_state', 'string', array('limit' => 1, 'null' => true))
                ->addColumn('service_obsess_over_service', 'string', array('limit' => 1, 'null' => true))
                ->addColumn('service_check_freshness', 'string', array('limit' => 1, 'null' => true))
                ->addColumn('service_freshness_threshold', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('service_event_handler_enabled', 'string', array('limit' => 1, 'null' => true))
                ->addColumn('service_low_flap_threshold', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('service_high_flap_threshold', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('service_flap_detection_enabled', 'string', array('limit' => 1, 'null' => true))
                ->addColumn('service_comment', 'text', array('null' => true))
                ->addColumn('command_command_id_arg', 'text', array('null' => true))
                ->addColumn('command_command_id_arg2', 'text', array('null' => true))
                ->addColumn('service_locked', 'boolean', array('limit' => 1, 'null' => true, 'default' => 0))
                ->addColumn('service_register', 'string', array('limit' => 1, 'null' => true, 'default' => 0))
                ->addColumn('service_activate', 'string', array('limit' => 1, 'null' => true, 'default' => '1'))
                ->addColumn('service_type', 'string', array('limit' => 1, 'null' => false, 'default' => '1'))
                ->addColumn('organization_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('environment_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('domain_id', 'integer', array('signed' => false, 'null' => true))
                ->addForeignKey('command_command_id', 'cfg_commands', 'command_id', array('delete'=> 'SET_NULL', 'update'=> 'RESTRICT'))
                ->addForeignKey('command_command_id2', 'cfg_commands', 'command_id', array('delete'=> 'SET_NULL', 'update'=> 'RESTRICT'))
                ->addForeignKey('timeperiod_tp_id', 'cfg_timeperiods', 'tp_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('organization_id', 'cfg_organizations', 'organization_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('environment_id', 'cfg_environments', 'environment_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('domain_id', 'cfg_domains', 'domain_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('service_template_model_stm_id', 'cfg_services', 'service_id', array('delete'=> 'SET_NULL', 'update'=> 'RESTRICT'))
                ->addIndex(array('service_template_model_stm_id'))
                ->addIndex(array('command_command_id'))
                ->addIndex(array('command_command_id2'))
                ->addIndex(array('timeperiod_tp_id'))
                ->addIndex(array('service_id', 'organization_id'))
                ->addIndex(array('service_id', 'service_register'))
                ->addIndex(array('service_description'))
                ->create();
        
        // Creation of table cfg_services_checkcmd_args_relations
        $cfg_services_checkcmd_args_relations = $this->table('cfg_services_checkcmd_args_relations', array('id' => false, 'primary_key' => array('service_id')));
        $cfg_services_checkcmd_args_relations->addColumn('service_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('arg_number', 'integer', array('limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'null' => false))
                ->addColumn('arg_value', 'string', array('limit' => 255, 'null' => true))
                ->addForeignKey('service_id', 'cfg_services', 'service_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('service_id'))
                ->create();

        // Creation of table cfg_services_hosts_templates_relations
        $cfg_services_hosts_templates_relations = $this->table('cfg_services_hosts_templates_relations', array('id' => false, 'primary_key' => array('service_id', 'host_tpl_id')));
        $cfg_services_hosts_templates_relations->addColumn('service_id', 'integer', array('signed' => false, 'null' => false, 'default' => 0))
                ->addColumn('host_tpl_id', 'integer', array('signed' => false, 'null' => false, 'default' => 0))
                ->addForeignKey('service_id', 'cfg_services', 'service_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('host_tpl_id', 'cfg_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('service_id'))
                ->addIndex(array('host_tpl_id'))
                ->create();

        // Creation of table cfg_services_images_relations
        $cfg_services_images_relations = $this->table('cfg_services_images_relations', array('id' => false, 'primary_key' => array('service_id', 'binary_id')));
        $cfg_services_images_relations->addColumn('service_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('binary_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('service_id', 'cfg_services', 'service_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('binary_id', 'cfg_binaries', 'binary_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('service_id'))
                ->addIndex(array('binary_id'))
                ->create();
        
        // Creation of table cfg_notification_rules_services_relations
        $cfg_notification_rules_services_relations = $this->table('cfg_notification_rules_services_relations', array('id' => false, 'primary_key' => array('rule_id', 'service_id')));
        $cfg_notification_rules_services_relations->addColumn('rule_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('service_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('rule_id', 'cfg_notification_rules', 'rule_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('service_id', 'cfg_services', 'service_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->create();
        
        // Creation of table cfg_dependencies_servicechildren_relations
        $cfg_dependencies_servicechildren_relations = $this->table('cfg_dependencies_servicechildren_relations', array('id' => false, 'primary_key' => array('dscr_id')));
        $cfg_dependencies_servicechildren_relations->addColumn('dscr_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('dependency_dep_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('service_service_id', 'integer', array('signed' => false, 'null' => true))
                ->addForeignKey('dependency_dep_id', 'cfg_dependencies', 'dep_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('service_service_id', 'cfg_services', 'service_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('dependency_dep_id'))
                ->addIndex(array('service_service_id'))
                ->create();

        // Creation of table cfg_dependencies_serviceparents_relations
        $cfg_dependencies_serviceparents_relations = $this->table('cfg_dependencies_serviceparents_relations', array('id' => false, 'primary_key' => array('dspr_id')));
        $cfg_dependencies_serviceparents_relations->addColumn('dspr_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('dependency_dep_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('service_service_id', 'integer', array('signed' => false, 'null' => true))
                ->addForeignKey('dependency_dep_id', 'cfg_dependencies', 'dep_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('service_service_id', 'cfg_services', 'service_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('dependency_dep_id'))
                ->addIndex(array('service_service_id'))
                ->create();

        // Creation of table cfg_downtimes_hosts_relations
        $cfg_downtimes_hosts_relations = $this->table('cfg_downtimes_hosts_relations', array('id' => false, 'primary_key' => array('dt_id', 'host_host_id')));
        $cfg_downtimes_hosts_relations->addColumn('dt_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('host_host_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('dt_id', 'cfg_downtimes', 'dt_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('host_host_id', 'cfg_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('dt_id'))
                ->addIndex(array('host_host_id'))
                ->create();

        // Creation of table cfg_downtimes_services_relations
        $cfg_downtimes_services_relations = $this->table('cfg_downtimes_services_relations', array('id' => false, 'primary_key' => array('dt_id', 'service_service_id')));
        $cfg_downtimes_services_relations->addColumn('dt_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('service_service_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('dt_id', 'cfg_downtimes', 'dt_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('service_service_id', 'cfg_services', 'service_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('dt_id'))
                ->addIndex(array('service_service_id'))
                ->create();

        // Creation of table cfg_customvariables_services
        $cfg_customvariables_services = $this->table('cfg_customvariables_services', array('id' => false, 'primary_key' => array('svc_macro_id')));
        $cfg_customvariables_services->addColumn('svc_macro_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('svc_macro_name', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('svc_macro_value', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('is_password', 'integer', array('limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'null' => true))
                ->addColumn('svc_svc_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('svc_svc_id', 'cfg_services', 'service_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('svc_svc_id'))
                ->create();
        
        // Creation of table cfg_acl_resources_services_relations
        $cfg_acl_resources_services_relations = $this->table('cfg_acl_resources_services_relations', array('id' => false, 'primary_key' => array('arsr_id')));
        $cfg_acl_resources_services_relations->addColumn('arsr_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('acl_resource_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('service_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('type', 'integer', array('limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'null' => false, 'default' => 0))
                ->addForeignKey('acl_resource_id', 'cfg_acl_resources', 'acl_resource_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('service_id', 'cfg_services', 'service_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('acl_resource_id'))
                ->addIndex(array('service_id'))
                ->create();
        
        // Creation of table cfg_tags_hosts
        $cfg_tags_hosts = $this->table('cfg_tags_hosts', array('id' => false, 'primary_key' => array('tag_id', 'resource_id')));
        $cfg_tags_hosts->addColumn('tag_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('resource_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('template_id', 'integer', array('signed' => false, 'null' => true))
                ->addForeignKey('tag_id', 'cfg_tags', 'tag_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->create();

        // Creation of table cfg_tags_services
        $cfg_tags_services = $this->table('cfg_tags_services', array('id' => false, 'primary_key' => array('tag_id', 'resource_id')));
        $cfg_tags_services->addColumn('tag_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('resource_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('template_id', 'integer', array('signed' => false, 'null' => true))
                ->addForeignKey('tag_id', 'cfg_tags', 'tag_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->create();
        
        // Creation of table cfg_timeperiods_exceptions
        $cfg_timeperiods_exceptions = $this->table('cfg_timeperiods_exceptions', array('id' => false, 'primary_key' => array('exception_id')));
        $cfg_timeperiods_exceptions->addColumn('exception_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('timeperiod_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('days', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('timerange', 'string', array('limit' => 255, 'null' => false))
                ->addForeignKey('timeperiod_id', 'cfg_timeperiods', 'tp_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('timeperiod_id'))
                ->create();

        // Creation of table cfg_timeperiods_exclude_relations
        $cfg_timeperiods_exclude_relations = $this->table('cfg_timeperiods_exclude_relations', array('id' => false, 'primary_key' => array('exclude_id')));
        $cfg_timeperiods_exclude_relations->addColumn('exclude_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('timeperiod_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('timeperiod_exclude_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('timeperiod_id', 'cfg_timeperiods', 'tp_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('timeperiod_exclude_id', 'cfg_timeperiods', 'tp_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->create();

        // Creation of table cfg_timeperiods_include_relations
        $cfg_timeperiods_include_relations = $this->table('cfg_timeperiods_include_relations', array('id' => false, 'primary_key' => array('include_id')));
        $cfg_timeperiods_include_relations->addColumn('include_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('timeperiod_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('timeperiod_include_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('timeperiod_id', 'cfg_timeperiods', 'tp_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('timeperiod_include_id', 'cfg_timeperiods', 'tp_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->create();
        
          // Creation of table cfg_escalations
        $cfg_escalations = $this->table('cfg_escalations', array('id' => false, 'primary_key' => array('esc_id')));
        $cfg_escalations->addColumn('esc_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('esc_name', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('esc_alias', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('first_notification', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('last_notification', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('notification_interval', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('escalation_period', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('escalation_options1', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('escalation_options2', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('esc_comment', 'text', array('null' => true))
                ->addColumn('organization_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('escalation_period', 'cfg_timeperiods', 'tp_id', array('delete'=> 'SET_NULL', 'update'=> 'RESTRICT'))
                ->addForeignKey('organization_id', 'cfg_organizations', 'organization_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('escalation_period'))
                ->create();
               
        // Creation of table cfg_traps_vendors
        $cfg_traps_vendors = $this->table('cfg_traps_vendors', array('id' => false, 'primary_key' => array('id')));
        $cfg_traps_vendors->addColumn('id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('name', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('slug', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('alias', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('description', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('organization_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('organization_id', 'cfg_organizations', 'organization_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->create();
        
        // Creation of table cfg_traps
        $cfg_traps = $this->table('cfg_traps', array('id' => false, 'primary_key' => array('traps_id')));
        $cfg_traps->addColumn('traps_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('traps_name', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('traps_slug', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('traps_oid', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('traps_args', 'text', array('limit' => 255, 'null' => true))
                ->addColumn('traps_status', 'string', array('limit' => 2, 'null' => true))
                ->addColumn('manufacturer_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('traps_reschedule_svc_enable', 'string', array('limit' => 1, 'null' => true, 'default' => 0))
                ->addColumn('traps_execution_command', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('traps_execution_command_enable', 'string', array('limit' => 1, 'null' => true, 'default' => 0))
                ->addColumn('traps_submit_result_enable', 'string', array('limit' => 1, 'null' => true, 'default' => 0))
                ->addColumn('traps_advanced_treatment', 'string', array('limit' => 1, 'null' => true, 'default' => 0))
                ->addColumn('traps_advanced_treatment_default', 'string', array('limit' => 1, 'null' => true, 'default' => 0))
                ->addColumn('traps_timeout', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('traps_exec_interval', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('traps_exec_interval_type', 'string', array('limit' => 1, 'null' => false))
                ->addColumn('traps_log', 'string', array('limit' => 1, 'null' => true, 'default' => 0))
                ->addColumn('traps_routing_mode', 'string', array('limit' => 1, 'null' => true, 'default' => 0))
                ->addColumn('traps_routing_value', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('traps_exec_method', 'string', array('limit' => 1, 'null' => true, 'default' => 0))
                ->addColumn('traps_comments', 'text', array('limit' => 255, 'null' => true))
                ->addColumn('organization_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('manufacturer_id', 'cfg_traps_vendors', 'id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('organization_id', 'cfg_organizations', 'organization_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('traps_id'))
                ->addIndex(array('manufacturer_id'))
                ->create();
        
        // Creation of table cfg_traps_matching_properties
        $cfg_traps_matching_properties = $this->table('cfg_traps_matching_properties', array('id' => false, 'primary_key' => array('tmo_id')));
        $cfg_traps_matching_properties->addColumn('tmo_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('trap_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('tmo_order', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('tmo_regexp', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('tmo_string', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('tmo_status', 'integer', array('signed' => false, 'null' => true))
                ->addForeignKey('trap_id', 'cfg_traps', 'traps_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('trap_id'))
                ->create();

        // Creation of table cfg_traps_preexec
        $cfg_traps_preexec = $this->table('cfg_traps_preexec', array('id' => false, 'primary_key' => array('trap_id')));
        $cfg_traps_preexec->addColumn('trap_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('tpe_order', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('tpe_string', 'string', array('limit' => 512, 'null' => true))
                ->addForeignKey('trap_id', 'cfg_traps', 'traps_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('trap_id'))
                ->create();

        // Creation of table cfg_traps_services_relations
        $cfg_traps_services_relations = $this->table('cfg_traps_services_relations', array('id' => false, 'primary_key' => array('tsr_id')));
        $cfg_traps_services_relations->addColumn('tsr_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('traps_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('service_id', 'integer', array('signed' => false, 'null' => true))
                ->addForeignKey('traps_id', 'cfg_traps', 'traps_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('service_id', 'cfg_services', 'service_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('traps_id'))
                ->addIndex(array('service_id'))
                ->create();

        // Creation of table cfg_virtual_metrics
        $cfg_virtual_metrics = $this->table('cfg_virtual_metrics', array('id' => false, 'primary_key' => array('vmetric_id')));
        $cfg_virtual_metrics->addColumn('vmetric_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('index_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('vmetric_name', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('def_type', 'string', array('limit' => 1, 'null' => true, 'default' => 0))
                ->addColumn('rpn_function', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('warn', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('crit', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('unit_name', 'string', array('limit' => 32, 'null' => true))
                ->addColumn('hidden', 'string', array('limit' => 1, 'null' => true, 'default' => 0))
                ->addColumn('comment', 'text', array('limit' => 255, 'null' => true))
                ->addColumn('vmetric_activate', 'string', array('limit' => 1, 'null' => true))
                ->addColumn('ck_state', 'string', array('limit' => 1, 'null' => true))
                ->addColumn('organization_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('organization_id', 'cfg_organizations', 'organization_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->create();
        
        
        // Creation of table cfg_acl_resources_tags_hosts_relations
        $cfg_acl_resources_tags_hosts_relations = $this->table('cfg_acl_resources_tags_hosts_relations', array('id' => false, 'primary_key' => array('arthr_id')));
        $cfg_acl_resources_tags_hosts_relations->addColumn('arthr_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('acl_resource_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('tag_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('type', 'integer', array('limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'null' => false, 'default' => 0))
                ->addForeignKey('acl_resource_id', 'cfg_acl_resources', 'acl_resource_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('tag_id', 'cfg_tags_hosts', 'tag_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('acl_resource_id'))
                ->addIndex(array('tag_id'))
                ->create();

        // Creation of table cfg_acl_resources_tags_services_relations
        $cfg_acl_resources_tags_services_relations = $this->table('cfg_acl_resources_tags_services_relations', array('id' => false, 'primary_key' => array('artsr_id')));
        $cfg_acl_resources_tags_services_relations->addColumn('artsr_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('acl_resource_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('tag_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('type', 'integer', array('limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'null' => false, 'default' => 0))
                ->addForeignKey('acl_resource_id', 'cfg_acl_resources', 'acl_resource_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('tag_id', 'cfg_tags_services', 'tag_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('acl_resource_id'))
                ->addIndex(array('tag_id'))
                ->create();
        
        // Creation of table cfg_commands_args_description
        $cfg_commands_args_description = $this->table('cfg_commands_args_description', array('id' => false, 'primary_key' => array('cmd_id')));
        $cfg_commands_args_description->addColumn('cmd_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('macro_name', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('macro_description', 'string', array('limit' => 255, 'null' => false))
                ->addForeignKey('cmd_id', 'cfg_commands', 'command_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('cmd_id'))
                ->create();
        
         // Creation of table cfg_dependencies_hostchildren_relations
        $cfg_dependencies_hostchildren_relations = $this->table('cfg_dependencies_hostchildren_relations', array('id' => false, 'primary_key' => array('dhcr_id')));
        $cfg_dependencies_hostchildren_relations->addColumn('dhcr_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('dependency_dep_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('host_host_id', 'integer', array('signed' => false, 'null' => true))
                ->addForeignKey('dependency_dep_id', 'cfg_dependencies', 'dep_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('host_host_id', 'cfg_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('dependency_dep_id'))
                ->addIndex(array('host_host_id'))
                ->create();
        
        // Creation of table cfg_downtimes_hosttags_relations
        $cfg_downtimes_hosttags_relations = $this->table('cfg_downtimes_hosttags_relations', array('id' => false, 'primary_key' => array('dt_id', 'host_tag_id')));
        $cfg_downtimes_hosttags_relations->addColumn('dt_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('host_tag_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('dt_id', 'cfg_downtimes', 'dt_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('host_tag_id', 'cfg_tags', 'tag_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('dt_id'))
                ->addIndex(array('host_tag_id'))
                ->create();
        
        // Creation of table cfg_downtimes_servicetags_relations
        $cfg_downtimes_servicetags_relations = $this->table('cfg_downtimes_servicetags_relations', array('id' => false, 'primary_key' => array('dt_id', 'service_tag_id')));
        $cfg_downtimes_servicetags_relations->addColumn('dt_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('service_tag_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('dt_id', 'cfg_downtimes', 'dt_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('service_tag_id', 'cfg_tags', 'tag_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('dt_id'))
                ->addIndex(array('service_tag_id'))
                ->create();

        // Creation of table cfg_downtimes_periods
        $cfg_downtimes_periods = $this->table('cfg_downtimes_periods', array('id' => false, 'primary_key' => array('dt_id')));
        $cfg_downtimes_periods->addColumn('dt_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('dtp_start_time', 'time', array('null' => false))
                ->addColumn('dtp_end_time', 'time', array('null' => false))
                ->addColumn('dtp_day_of_week', 'string', array('limit' => 15, 'null' => true))
                ->addColumn('dtp_month_cycle', 'string', array('limit' => 15, 'null' => true, 'default' => 'all'))
                ->addColumn('dtp_day_of_month', 'string', array('limit' => 100, 'null' => true))
                ->addColumn('dtp_fixed', 'boolean', array('null' => true, 'default' => 1))
                ->addColumn('dtp_duration', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('dtp_next_date', 'date', array('null' => false))
                ->addColumn('dtp_activate', 'integer', array('signed' => false, 'null' => true, 'default' => 1))
                ->addForeignKey('dt_id', 'cfg_downtimes', 'dt_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('dt_id', 'dtp_activate'))
                ->create();

         // Creation of table cfg_hosts_checkcmd_args_relations
        $cfg_hosts_checkcmd_args_relations = $this->table('cfg_hosts_checkcmd_args_relations', array('id' => false, 'primary_key' => array('host_id')));
        $cfg_hosts_checkcmd_args_relations->addColumn('host_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('arg_number', 'integer', array('limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'null' => true))
                ->addColumn('arg_value', 'string', array('limit' => 255, 'null' => false))
                ->addForeignKey('host_id', 'cfg_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('host_id'))
                ->create();
        
        // Creation of table cfg_escalations_hosts_relations
        $cfg_escalations_hosts_relations = $this->table('cfg_escalations_hosts_relations', array('id' => false, 'primary_key' => array('ehr_id')));
        $cfg_escalations_hosts_relations->addColumn('ehr_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('escalation_esc_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('host_host_id', 'integer', array('signed' => false, 'null' => true))
                ->addForeignKey('escalation_esc_id', 'cfg_escalations', 'esc_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('host_host_id', 'cfg_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('escalation_esc_id'))
                ->addIndex(array('host_host_id'))
                ->create();

        // Creation of table cfg_escalations_servicess_relations
        $cfg_escalations_services_relations = $this->table('cfg_escalations_services_relations', array('id' => false, 'primary_key' => array('esr_id')));
        $cfg_escalations_services_relations->addColumn('esr_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('escalation_esc_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('service_service_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('host_host_id', 'integer', array('signed' => false, 'null' => true))
                ->addForeignKey('escalation_esc_id', 'cfg_escalations', 'esc_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('service_service_id', 'cfg_services', 'service_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('host_host_id', 'cfg_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('escalation_esc_id'))
                ->addIndex(array('service_service_id'))
                ->addIndex(array('host_host_id'))
                ->create();

        // Creation of table cfg_hosts_hostparents_relations
        $cfg_hosts_hostparents_relations = $this->table('cfg_hosts_hostparents_relations', array('id' => false, 'primary_key' => array('hhr_id')));
        $cfg_hosts_hostparents_relations->addColumn('hhr_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('host_parent_hp_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('host_host_id', 'integer', array('signed' => false, 'null' => true))
                ->addForeignKey('host_parent_hp_id', 'cfg_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('host_host_id', 'cfg_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('host_parent_hp_id'))
                ->addIndex(array('host_host_id'))
                ->create();

        // Creation of table cfg_hosts_images_relations
        $cfg_hosts_images_relations = $this->table('cfg_hosts_images_relations', array('id' => false, 'primary_key' => array('host_id', 'binary_id')));
        $cfg_hosts_images_relations->addColumn('host_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('binary_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('host_id', 'cfg_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('binary_id', 'cfg_binaries', 'binary_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('host_id'))
                ->addIndex(array('binary_id'))
                ->create();

        // Creation of table cfg_hosts_services_relations
        $cfg_hosts_services_relations = $this->table('cfg_hosts_services_relations', array('id' => false, 'primary_key' => array('hsr_id')));
        $cfg_hosts_services_relations->addColumn('hsr_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('host_host_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('service_service_id', 'integer', array('signed' => false, 'null' => true))
                ->addForeignKey('host_host_id', 'cfg_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('service_service_id', 'cfg_services', 'service_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('host_host_id'))
                ->addIndex(array('service_service_id'))
                ->addIndex(array('host_host_id', 'service_service_id'))
                ->create();

        // Creation of table cfg_hosts_templates_relations
        $cfg_hosts_templates_relations = $this->table('cfg_hosts_templates_relations', array('id' => false, 'primary_key' => array('host_host_id', 'host_tpl_id')));
        $cfg_hosts_templates_relations->addColumn('host_host_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('host_tpl_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('order', 'integer', array('signed' => false, 'null' => true))
                ->addForeignKey('host_host_id', 'cfg_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('host_tpl_id', 'cfg_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('host_tpl_id'))
                ->create();
        
    }
}
