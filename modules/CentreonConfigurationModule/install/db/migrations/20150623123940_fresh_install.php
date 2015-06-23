<?php

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
        // Creation of table cfg_acl_resources_hosts_params
        $cfg_acl_resources_hosts_params = $this->table('cfg_acl_resources_hosts_params', array('id' => false, 'primary_key' => array('acl_resource_id')));
        $cfg_acl_resources_hosts_params->addColumn('acl_resource_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('all_hosts', 'boolean', array('null' => true, 'default' => 0))
                ->addForeignKey('acl_resource_id', 'cfg_acl_resources', 'acl_resource_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('acl_resource_id'))
                ->save();

        // Creation of table cfg_acl_resources_hosts_relations
        $cfg_acl_resources_hosts_relations = $this->table('cfg_acl_resources_hosts_relations', array('id' => false, 'primary_key' => array('arhr_id')));
        $cfg_acl_resources_hosts_relations->addColumn('arhr_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('acl_resource_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('host_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('type', 'integer', array('signed' => false, 'null' => false, 'default' => 0))
                ->addForeignKey('acl_resource_id', 'cfg_acl_resources', 'acl_resource_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('host_id', 'cfg_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('acl_resource_id'))
                ->addIndex(array('host_id'))
                ->save();

        // Creation of table cfg_acl_resources_services_relations
        $cfg_acl_resources_services_relations = $this->table('cfg_acl_resources_services_relations', array('id' => false, 'primary_key' => array('arsr_id')));
        $cfg_acl_resources_services_relations->addColumn('arsr_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('acl_resource_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('service_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('type', 'integer', array('signed' => false, 'null' => false, 'default' => 0))
                ->addForeignKey('acl_resource_id', 'cfg_acl_resources', 'acl_resource_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('service_id', 'cfg_services', 'service_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('acl_resource_id'))
                ->addIndex(array('service_id'))
                ->save();

        // Creation of table cfg_acl_resources_tags_hosts_relations
        $cfg_acl_resources_tags_hosts_relations = $this->table('cfg_acl_resources_tags_hosts_relations', array('id' => false, 'primary_key' => array('arthr_id')));
        $cfg_acl_resources_tags_hosts_relations->addColumn('arthr_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('acl_resource_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('tag_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('type', 'integer', array('signed' => false, 'null' => false, 'default' => 0))
                ->addForeignKey('acl_resource_id', 'cfg_acl_resources', 'acl_resource_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('tag_id', 'cfg_tags_hosts', 'tag_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('acl_resource_id'))
                ->addIndex(array('tag_id'))
                ->save();

        // Creation of table cfg_acl_resources_tags_services_relations
        $cfg_acl_resources_tags_services_relations = $this->table('cfg_acl_resources_tags_services_relations', array('id' => false, 'primary_key' => array('artsr_id')));
        $cfg_acl_resources_tags_services_relations->addColumn('artsr_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('acl_resource_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('tag_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('type', 'integer', array('signed' => false, 'null' => false, 'default' => 0))
                ->addForeignKey('acl_resource_id', 'cfg_acl_resources', 'acl_resource_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('tag_id', 'cfg_tags_services', 'tag_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('acl_resource_id'))
                ->addIndex(array('tag_id'))
                ->save();

        // Creation of table cfg_commands_args_description
        $cfg_commands_args_description = $this->table('cfg_commands_args_description', array('id' => false, 'primary_key' => array('cmd_id')));
        $cfg_commands_args_description->addColumn('cmd_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('macro_name', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('macro_description', 'string', array('limit' => 255, 'null' => false))
                ->addForeignKey('cmd_id', 'cfg_commands', 'command_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('cmd_id'))
                ->save();

        // Creation of table cfg_commands_categories_relations
        $cfg_commands_categories_relations = $this->table('cfg_commands_categories_relations', array('id' => false, 'primary_key' => array('cmd_cat_id')));
        $cfg_commands_categories_relations->addColumn('cmd_cat_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('category_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('command_command_id', 'integer', array('signed' => false, 'null' => true))
                ->save();

        // Creation of table cfg_commands_categories
        $cfg_commands_categories = $this->table('cfg_commands_categories', array('id' => false, 'primary_key' => array('cmd_category_id')));
        $cfg_commands_categories->addColumn('cmd_category_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('category_name', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('category_alias', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('category_order', 'integer', array('signed' => false, 'null' => false))
                ->save();

        // Creation of table cfg_commands
        $cfg_commands = $this->table('cfg_commands', array('id' => false, 'primary_key' => array('command_id')));
        $cfg_commands->addColumn('command_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('connector_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('command_name', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('command_slug', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('command_line', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('command_example', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('command_type', 'integer', array('signed' => false, 'null' => false, 'default' => 2))
                ->addColumn('enable_shell', 'integer', array('signed' => false, 'null' => false, 'default' => 0))
                ->addColumn('command_comment', 'text', array('null' => true))
                ->addColumn('graph_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('cmd_cat_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('organization_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('connector_id', 'cfg_connectors', 'id', array('delete'=> 'SET_NULL', 'update'=> 'CASCADE'))
                ->addForeignKey('organization_id', 'cfg_organizations', 'organization_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->save();

        // Creation of table cfg_connectors
        $cfg_connectors = $this->table('cfg_connectors', array('id' => false, 'primary_key' => array('id')));
        $cfg_connectors->addColumn('id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('name', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('slug', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('description', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('command_line', 'string', array('limit' => 512, 'null' => false))
                ->addColumn('enabled', 'integer', array('signed' => false, 'null' => false, 'default' => 1))
                ->addColumn('created', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('modified', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('organization_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('organization_id', 'cfg_organizations', 'organization_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                >addIndex(array('enabled'))
                ->save();

        // Creation of table cfg_customvariables_hosts
        $cfg_customvariables_hosts = $this->table('cfg_customvariables_hosts', array('id' => false, 'primary_key' => array('host_macro_id')));
        $cfg_customvariables_hosts->addColumn('host_macro_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('host_macro_name', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('host_macro_value', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('is_password', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('host_host_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('host_host_id', 'cfg_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                >addIndex(array('host_host_id'))
                ->save();

        // Creation of table cfg_customvariables_services
        $cfg_customvariables_services = $this->table('cfg_customvariables_services', array('id' => false, 'primary_key' => array('svc_macro_id')));
        $cfg_customvariables_services->addColumn('svc_macro_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('svc_macro_name', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('svc_macro_value', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('is_password', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('svc_svc_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('svc_svc_id', 'cfg_services', 'service_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                >addIndex(array('svc_svc_id'))
                ->save();

        // Creation of table cfg_dependencies_hostchildren_relations
        $cfg_dependencies_hostchildren_relations = $this->table('cfg_dependencies_hostchildren_relations', array('id' => false, 'primary_key' => array('dhcr_id')));
        $cfg_dependencies_hostchildren_relations->addColumn('dhcr_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('dependency_dep_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('host_host_id', 'integer', array('signed' => false, 'null' => true))
                ->addForeignKey('dependency_dep_id', 'cfg_dependencies', 'dep_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('host_host_id', 'cfg_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('dependency_dep_id'))
                ->addIndex(array('host_host_id'))
                ->save();

        // Creation of table cfg_dependencies_hostparents_relations
        $cfg_dependencies_hostparents_relations = $this->table('cfg_dependencies_hostparents_relations', array('id' => false, 'primary_key' => array('dhpr_id')));
        $cfg_dependencies_hostparents_relations->addColumn('dhpr_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('dependency_dep_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('host_host_id', 'integer', array('signed' => false, 'null' => true))
                ->addForeignKey('dependency_dep_id', 'cfg_dependencies', 'dep_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('host_host_id', 'cfg_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('dependency_dep_id'))
                ->addIndex(array('host_host_id'))
                ->save();

        // Creation of table cfg_dependencies_servicechildren_relations
        $cfg_dependencies_servicechildren_relations = $this->table('cfg_dependencies_servicechildren_relations', array('id' => false, 'primary_key' => array('dscr_id')));
        $cfg_dependencies_servicechildren_relations->addColumn('dscr_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('dependency_dep_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('service_service_id', 'integer', array('signed' => false, 'null' => true))
                ->addForeignKey('dependency_dep_id', 'cfg_dependencies', 'dep_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('service_service_id', 'cfg_services', 'service_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('dependency_dep_id'))
                ->addIndex(array('service_service_id'))
                ->save();

        // Creation of table cfg_dependencies_serviceparents_relations
        $cfg_dependencies_serviceparents_relations = $this->table('cfg_dependencies_serviceparents_relations', array('id' => false, 'primary_key' => array('dspr_id')));
        $cfg_dependencies_serviceparents_relations->addColumn('dspr_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('dependency_dep_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('service_service_id', 'integer', array('signed' => false, 'null' => true))
                ->addForeignKey('dependency_dep_id', 'cfg_dependencies', 'dep_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('service_service_id', 'cfg_services', 'service_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('dependency_dep_id'))
                ->addIndex(array('service_service_id'))
                ->save();

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
                ->save();

        // Creation of table cfg_downtimes_hosts_relations
        $cfg_downtimes_hosts_relations = $this->table('cfg_downtimes_hosts_relations', array('id' => false, 'primary_key' => array('dt_id', 'host_host_id')));
        $cfg_downtimes_hosts_relations->addColumn('dt_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('host_host_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('dt_id', 'cfg_downtimes', 'dt_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('host_host_id', 'cfg_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('dt_id'))
                ->addIndex(array('host_host_id'))
                ->save();

        // Creation of table cfg_downtimes_services_relations
        $cfg_downtimes_services_relations = $this->table('cfg_downtimes_services_relations', array('id' => false, 'primary_key' => array('dt_id', 'service_service_id')));
        $cfg_downtimes_services_relations->addColumn('dt_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('service_service_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('dt_id', 'cfg_downtimes', 'dt_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('service_service_id', 'cfg_services', 'service_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('dt_id'))
                ->addIndex(array('service_service_id'))
                ->save();

        // Creation of table cfg_downtimes_hosttags_relations
        $cfg_downtimes_hosttags_relations = $this->table('cfg_downtimes_hosttags_relations', array('id' => false, 'primary_key' => array('dt_id', 'host_tag_id')));
        $cfg_downtimes_hosttags_relations->addColumn('dt_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('host_tag_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('dt_id', 'cfg_downtimes', 'dt_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('host_tag_id', 'cfg_tags', 'tag_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('dt_id'))
                ->addIndex(array('host_tag_id'))
                ->save();

        // Creation of table cfg_downtimes_servicetags_relations
        $cfg_downtimes_servicetags_relations = $this->table('cfg_downtimes_servicetags_relations', array('id' => false, 'primary_key' => array('dt_id', 'service_tag_id')));
        $cfg_downtimes_servicetags_relations->addColumn('dt_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('service_tag_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('dt_id', 'cfg_downtimes', 'dt_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('service_tag_id', 'cfg_tags', 'tag_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('dt_id'))
                ->addIndex(array('service_tag_id'))
                ->save();

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
                ->addColumn('drp_activate', 'integer', array('signed' => false, 'null' => true, 'default' => 1))
                ->addForeignKey('dt_id', 'cfg_downtimes', 'dt_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('dt_id', 'dtp_activate'))
                ->save();

        // Creation of table cfg_downtimes
        $cfg_downtimes = $this->table('cfg_downtimes', array('id' => false, 'primary_key' => array('dt_id')));
        $cfg_downtimes->addColumn('dt_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('name', 'string', array('limit' => 100, 'null' => false))
                ->addColumn('description', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('dt_activate', 'integer', array('signed' => false, 'null' => true, 'default' => 1))
                ->addColumn('organization_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('organization_id', 'cfg_organizations', 'organization_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('dt_id', 'dt_activate'))
                ->save();

        // Creation of table cfg_engine_macros
        $cfg_engine_macros = $this->table('cfg_engine_macros', array('id' => false, 'primary_key' => array('macro_id')));
        $cfg_engine_macros->addColumn('macro_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('macro_name', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('organization_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('organization_id', 'cfg_organizations', 'organization_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->save();
    }
}
