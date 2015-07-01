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
        // Creation of table cfg_acl_resources_business_activities_params
        $cfg_acl_resources_business_activities_params = $this->table('cfg_acl_resources_business_activities_params', array('id' => false, 'primary_key' => array('acl_resource_id')));
        $cfg_acl_resources_business_activities_params->addColumn('acl_resource_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('all_business_activities', 'boolean', array('null' => true, 'default' => 0))
                ->addForeignKey('acl_resource_id', 'cfg_acl_resources', 'acl_resource_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('acl_resource_id'))
                ->save();
        
        // Creation of table cfg_tags_bas
        $cfg_tags_bas = $this->table('cfg_tags_bas', array('id' => false, 'primary_key' => array('tag_id', 'resource_id')));
        $cfg_tags_bas->addColumn('tag_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('resource_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('template_id', 'integer', array('signed' => false, 'null' => true))
                ->addForeignKey('tag_id', 'cfg_tags', 'tag_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('tag_id', 'resource_id'), array('unique' => true))
                ->save();

        // Creation of table cfg_acl_resources_tags_bas_relations
        $cfg_acl_resources_tags_bas_relations = $this->table('cfg_acl_resources_tags_bas_relations', array('id' => false, 'primary_key' => array('artbar_id')));
        $cfg_acl_resources_tags_bas_relations->addColumn('artbar_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('acl_resource_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('tag_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('type', 'integer', array('signed' => false, 'null' => false, 'default' => 0))
                ->addForeignKey('acl_resource_id', 'cfg_acl_resources', 'acl_resource_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('tag_id', 'cfg_tags_bas', 'tag_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('acl_resource_id'), array('unique' => false))
                ->addIndex(array('tag_id'), array('unique' => false))
                ->save();
        
        // Creation of table cfg_bam_ba_type
        $cfg_bam_ba_type = $this->table('cfg_bam_ba_type', array('id' => false, 'primary_key' => array('ba_type_id')));
        $cfg_bam_ba_type->addColumn('ba_type_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('name', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('description', 'string', array('limit' => 255, 'null' => true))
                ->addIndex(array('name'), array('unique' => false))
                ->save();
        
         // Creation of table cfg_bam
        $cfg_bam = $this->table('cfg_bam', array('id' => false, 'primary_key' => array('ba_id')));
        $cfg_bam->addColumn('ba_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('name', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('slug', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('description', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('level_w', 'float', array('signed' => false, 'null' => true))
                ->addColumn('level_c', 'float', array('signed' => false, 'null' => true))
                ->addColumn('sla_month_percent_warn', 'float', array('signed' => false, 'null' => true))
                ->addColumn('sla_month_percent_crit', 'float', array('signed' => false, 'null' => true))
                ->addColumn('sla_month_duration_warn', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('sla_month_duration_crit', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('id_reporting_period', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('max_check_attempts', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('normal_check_interval', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('retry_check_interval', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('current_level', 'float', array('signed' => false, 'null' => true))
                ->addColumn('calculate', 'string', array('limit' => 1, 'null' => false, 'default' => '0'))
                ->addColumn('downtime', 'float', array('signed' => false, 'null' => false, 'default' => 0))
                ->addColumn('acknowledged', 'float', array('signed' => false, 'null' => false, 'default' => 0))
                ->addColumn('must_be_rebuild', 'string', array('limit' => 1, 'null' => true, 'default' => '0'))
                ->addColumn('last_state_change', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('current_status', 'integer', array('limit' => 1, 'signed' => false, 'null' => true))
                ->addColumn('in_downtime', 'boolean', array('null' => true))
                ->addColumn('dependency_dep_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('graph_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('icon_id', 'integer', array('limit' => 10, 'signed' => false, 'null' => true))
                ->addColumn('graph_style', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('activate', 'integer', array('limit' => 1, 'signed' => false, 'null' => true, 'default' => 1))
                ->addColumn('comment', 'text', array('null' => true))
                ->addColumn('organization_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('ba_type_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('id_reporting_period', 'cfg_timeperiods', 'tp_id', array('delete'=> 'SET_NULL', 'update'=> 'RESTRICT'))
                ->addForeignKey('icon_id', 'cfg_binaries', 'binary_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('ba_type_id', 'cfg_bam_ba_type', 'ba_type_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('organization_id', 'cfg_organizations', 'organization_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('name', 'organization_id'), array('unique' => true))
                ->addIndex(array('name'), array('unique' => false))
                ->addIndex(array('description'), array('unique' => false))
                ->addIndex(array('calculate'), array('unique' => false))
                ->addIndex(array('current_level'), array('unique' => false))
                ->addIndex(array('level_w'), array('unique' => false))
                ->addIndex(array('level_c'), array('unique' => false))
                ->addIndex(array('id_reporting_period'), array('unique' => false))
                ->addIndex(array('dependency_dep_id'), array('unique' => false))
                ->addIndex(array('icon_id'), array('unique' => false))
                ->addIndex(array('graph_id'), array('unique' => false))
                ->save();

        // Creation of table cfg_bam_ba_groups
        $cfg_bam_ba_groups = $this->table('cfg_bam_ba_groups', array('id' => false, 'primary_key' => array('id_ba_group')));
        $cfg_bam_ba_groups->addColumn('id_ba_group', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('ba_group_name', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('ba_group_description', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('visible', 'string', array('limit' => 1, 'null' => true, 'default' => '1'))
                ->addIndex(array('ba_group_name'), array('unique' => true))
                ->save();
        
        // Creation of table cfg_bam_bagroup_ba_relation
        $cfg_bam_bagroup_ba_relation = $this->table('cfg_bam_bagroup_ba_relation', array('id' => false, 'primary_key' => array('id_ba', 'id_ba_group')));
        $cfg_bam_bagroup_ba_relation->addColumn('id_ba', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('id_ba_group', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('id_ba', 'cfg_bam', 'ba_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('id_ba_group', 'cfg_bam_ba_groups', 'id_ba_group', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('id_ba'), array('unique' => false))
                ->addIndex(array('id_ba_group'), array('unique' => false))
                ->addIndex(array('id_ba', 'id_ba_group'), array('unique' => true))
                ->save();

        // Creation of table cfg_bam_boolean
        $cfg_bam_boolean = $this->table('cfg_bam_boolean', array('id' => false, 'primary_key' => array('boolean_id')));
        $cfg_bam_boolean->addColumn('boolean_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('name', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('slug', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('expression', 'text', array('null' => false))
                ->addColumn('bool_state', 'boolean', array('null' => false, 'default' => true))
                ->addColumn('comments', 'text', array('null' => true))
                ->addColumn('activate', 'integer', array('limit' => 1, 'signed' => false, 'null' => false))
                ->addIndex(array('name'), array('unique' => true))
                ->save();

        // Creation of table cfg_meta_services
        $cfg_meta_services = $this->table('cfg_meta_services', array('id' => false, 'primary_key' => array('meta_id')));
        $cfg_meta_services->addColumn('meta_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('meta_name', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('meta_display', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('check_period', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('max_check_attempts', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('normal_check_interval', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('retry_check_interval', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('calcul_type', 'string', array('limit' => 10, 'null' => false))
                ->addColumn('data_source_type', 'integer', array('signed' => false, 'null' => false, 'default' => 0))
                ->addColumn('meta_select_mode', 'string', array('limit' => 1, 'null' => true, 'default' => 1))
                ->addColumn('regexp_str', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('metric', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('warning', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('critical', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('meta_comment', 'text', array('null' => true))
                ->addColumn('meta_activate', 'integer', array('limit' => 1, 'signed' => false, 'null' => true))
                ->addColumn('value', 'float', array('signed' => false, 'null' => true))
                ->addColumn('organization_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('organization_id', 'cfg_organizations', 'organization_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('meta_name'), array('unique' => true))
                ->save();
        
        // Creation of table cfg_bam_impacts
        $cfg_bam_impacts = $this->table('cfg_bam_impacts', array('id' => false, 'primary_key' => array('id_impact')));
        $cfg_bam_impacts->addColumn('id_impact', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('code', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('impact', 'float', array('signed' => false, 'null' => false))
                ->addColumn('color', 'string', array('limit' => 7, 'null' => true))
                ->addColumn('organization_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('organization_id', 'cfg_organizations', 'organization_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->save();

        // Creation of table cfg_bam_kpi
        $cfg_bam_kpi = $this->table('cfg_bam_kpi', array('id' => false, 'primary_key' => array('kpi_id')));
        $cfg_bam_kpi->addColumn('kpi_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('state_type', 'integer', array('signed' => false, 'null' => false, 'default' => 1))
                ->addColumn('kpi_type', 'integer', array('signed' => false, 'null' => false, 'default' => 0))
                ->addColumn('host_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('service_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('id_indicator_ba', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('id_ba', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('meta_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('boolean_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('current_status', 'integer', array('limit' => 1, 'signed' => false, 'null' => true))
                ->addColumn('last_level', 'float', array('signed' => false, 'null' => true))
                ->addColumn('last_impact', 'float', array('signed' => false, 'null' => true))
                ->addColumn('downtime', 'float', array('signed' => false, 'null' => true))
                ->addColumn('acknowledged', 'float', array('signed' => false, 'null' => true))
                ->addColumn('comments', 'text', array('null' => true))
                ->addColumn('config_type', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('drop_warning', 'float', array('signed' => false, 'null' => true))
                ->addColumn('drop_warning_impact_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('drop_critical', 'float', array('signed' => false, 'null' => true))
                ->addColumn('drop_critical_impact_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('drop_unknown', 'float', array('signed' => false, 'null' => true))
                ->addColumn('drop_unknown_impact_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('activate', 'integer', array('limit' => 1, 'signed' => false, 'null' => true, 'default' => 1))
                ->addColumn('ignore_downtime', 'integer', array('limit' => 1, 'signed' => false, 'null' => true, 'default' => 0))
                ->addColumn('ignore_acknowledged', 'integer', array('limit' => 1, 'signed' => false, 'null' => true, 'default' => 0))
                ->addColumn('last_state_change', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('in_downtime', 'boolean', array('null' => true))
                ->addColumn('organization_id', 'integer', array('signed' => false, 'null' => false))
                ->addForeignKey('host_id', 'cfg_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('service_id', 'cfg_services', 'service_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('id_indicator_ba', 'cfg_bam', 'ba_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('id_ba', 'cfg_bam', 'ba_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('meta_id', 'cfg_meta_services', 'meta_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('boolean_id', 'cfg_bam_boolean', 'boolean_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('organization_id', 'cfg_organizations', 'organization_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('id_ba'), array('unique' => false))
                ->addIndex(array('id_indicator_ba'), array('unique' => false))
                ->addIndex(array('host_id'), array('unique' => false))
                ->addIndex(array('service_id'), array('unique' => false))
                ->addIndex(array('meta_id'), array('unique' => false))
                ->addIndex(array('boolean_id'), array('unique' => false))
                ->save();
        
        
        // Creation of table mod_bam_reporting_ba
        $mod_bam_reporting_ba = $this->table('mod_bam_reporting_ba', array('id' => false, 'primary_key' => array('ba_id')));
        $mod_bam_reporting_ba->addColumn('ba_id', 'integer', array('signed' => false, 'identity' => false, 'null' => false))
                ->addColumn('ba_name', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('ba_description', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('sla_month_percent_crit', 'float', array('signed' => false, 'null' => true))
                ->addColumn('sla_month_percent_warn', 'float', array('signed' => false, 'null' => true))
                ->addColumn('sla_month_duration_crit', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('sla_month_duration_warn', 'integer', array('signed' => false, 'null' => true))
                ->addIndex(array('ba_name'), array('unique' => true))
                ->save();
        
        // Creation of table cfg_bam_relations_ba_timeperiods
        $cfg_bam_relations_ba_timeperiods = $this->table('cfg_bam_relations_ba_timeperiods', array('id' => false, 'primary_key' => array('ba_id', 'tp_id')));
        $cfg_bam_relations_ba_timeperiods->addColumn('ba_id', 'integer', array('signed' => false, 'identity' => false, 'null' => false))
                ->addColumn('tp_id', 'integer', array('signed' => false, 'identity' => false, 'null' => false))
                ->addForeignKey('ba_id', 'cfg_bam', 'ba_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('tp_id', 'cfg_timeperiods', 'tp_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('ba_id'), array('unique' => false))
                ->addIndex(array('tp_id'), array('unique' => false))
                ->addIndex(array('ba_id', 'tp_id'), array('unique' => true))
                ->save();

        // Creation of table cfg_meta_services_relations
        $cfg_meta_services_relations = $this->table('cfg_meta_services_relations', array('id' => false, 'primary_key' => array('meta_id', 'host_id', 'metric_id')));
        $cfg_meta_services_relations->addColumn('meta_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('host_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('metric_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('msr_comment', 'text', array('null' => true))
                ->addColumn('activate', 'integer', array('limit' => 1, 'signed' => false, 'null' => true))
                ->addForeignKey('meta_id', 'cfg_meta_services', 'meta_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('meta_id', 'host_id', 'metric_id'), array('unique' => true))
                ->save();

        // Creation of table mod_bam_reporting_ba_availabilities
        $mod_bam_reporting_ba_availabilities = $this->table('mod_bam_reporting_ba_availabilities', array('id' => false, 'primary_key' => array('ba_id')));
        $mod_bam_reporting_ba_availabilities->addColumn('ba_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('time_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('timeperiod_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('available', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('unavailable', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('degraded', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('unknown', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('downtime', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('alert_unavailable_opened', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('alert_degraded_opened', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('alert_unknown_opened', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('nb_downtime', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('timeperiod_is_default', 'boolean', array('null' => true))
                ->addIndex(array('ba_id', 'time_id', 'timeperiod_id'), array('unique' => true))
                ->save();

        // Creation of table mod_bam_reporting_ba_events
        $mod_bam_reporting_ba_events = $this->table('mod_bam_reporting_ba_events', array('id' => false, 'primary_key' => array('ba_event_id')));
        $mod_bam_reporting_ba_events->addColumn('ba_event_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('ba_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('start_time', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('end_time', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('first_level', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('status', 'integer', array('limit' => 1, 'signed' => false, 'null' => true))
                ->addColumn('in_downtime', 'boolean', array('null' => true))
                ->addIndex(array('start_time'), array('unique' => false))
                ->addIndex(array('end_time'), array('unique' => false))
                ->addIndex(array('ba_id', 'start_time'), array('unique' => true))
                ->save();
        
        // Creation of table mod_bam_reporting_ba_events_durations
        $mod_bam_reporting_ba_events_durations = $this->table('mod_bam_reporting_ba_events_durations', array('id' => false, 'primary_key' => array('ba_event_id')));
        $mod_bam_reporting_ba_events_durations->addColumn('ba_event_id', 'integer', array('signed' => false, 'identity' => false, 'null' => false))
                ->addColumn('timeperiod_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('start_time', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('end_time', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('duration', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('sla_duration', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('timeperiod_is_default', 'boolean', array('null' => true))
                ->addForeignKey('ba_event_id', 'mod_bam_reporting_ba_events', 'ba_event_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('ba_event_id', 'timeperiod_id'), array('unique' => true))
                ->addIndex(array('start_time', 'end_time'), array('unique' => false))
                ->save();

        
        // Creation of table mod_bam_reporting_bv
        $mod_bam_reporting_bv = $this->table('mod_bam_reporting_bv', array('id' => false, 'primary_key' => array('bv_id')));
        $mod_bam_reporting_bv->addColumn('bv_id', 'integer', array('signed' => false, 'identity' => false, 'null' => false))
                ->addColumn('bv_name', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('bv_description', 'string', array('limit' => 255, 'null' => true))
                ->addIndex(array('bv_name'), array('unique' => true))
                ->save();

        // Creation of table mod_bam_reporting_kpi_events
        $mod_bam_reporting_kpi_events = $this->table('mod_bam_reporting_kpi_events', array('id' => false, 'primary_key' => array('kpi_event_id')));
        $mod_bam_reporting_kpi_events->addColumn('kpi_event_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('kpi_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('start_time', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('end_time', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('status', 'integer', array('limit' => 1, 'signed' => false, 'null' => true))
                ->addColumn('in_downtime', 'boolean', array('null' => true))
                ->addColumn('impact_level', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('first_output', 'text', array('null' => true))
                ->addColumn('first_perfdata', 'string', array('limit' => 255, 'null' => true))
                ->addIndex(array('kpi_id', 'start_time'), array('unique' => true))
                ->save();

        // Creation of table mod_bam_reporting_kpi
        $mod_bam_reporting_kpi = $this->table('mod_bam_reporting_kpi', array('id' => false, 'primary_key' => array('kpi_id')));
        $mod_bam_reporting_kpi->addColumn('kpi_id', 'integer', array('signed' => false, 'identity' => false, 'null' => false))
                ->addColumn('kpi_name', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('ba_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('ba_name', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('host_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('host_name', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('service_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('service_description', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('kpi_ba_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('kpi_ba_name', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('meta_service_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('meta_service_name', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('boolean_id', 'integer', array('signed' => false, 'null' => true))
                ->addColumn('boolean_name', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('impact_warning', 'float', array('signed' => false, 'null' => true))
                ->addColumn('impact_critical', 'float', array('signed' => false, 'null' => true))
                ->addColumn('impact_unknown', 'float', array('signed' => false, 'null' => true))
                ->addForeignKey('ba_id', 'mod_bam_reporting_ba', 'ba_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('kpi_ba_id', 'mod_bam_reporting_ba', 'ba_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('ba_id'), array('unique' => false))
                ->addIndex(array('kpi_ba_id'), array('unique' => false))
                ->save();

        // Creation of table mod_bam_reporting_relations_ba_bv
        $mod_bam_reporting_relations_ba_bv = $this->table('mod_bam_reporting_relations_ba_bv', array('id' => false, 'primary_key' => array('bv_id', 'ba_id')));
        $mod_bam_reporting_relations_ba_bv->addColumn('bv_id', 'integer', array('signed' => false, 'identity' => false, 'null' => false))
                ->addColumn('ba_id', 'integer', array('signed' => false, 'identity' => false, 'null' => false))
                ->addForeignKey('bv_id', 'mod_bam_reporting_bv', 'bv_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('ba_id', 'mod_bam_reporting_ba', 'ba_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('bv_id'), array('unique' => false))
                ->addIndex(array('ba_id'), array('unique' => false))
                ->addIndex(array('bv_id', 'ba_id'), array('unique' => true))
                ->save();

        // Creation of table mod_bam_reporting_relations_ba_kpi_events
        $mod_bam_reporting_relations_ba_kpi_events = $this->table('mod_bam_reporting_relations_ba_kpi_events', array('id' => false, 'primary_key' => array('ba_event_id', 'kpi_event_id')));
        $mod_bam_reporting_relations_ba_kpi_events->addColumn('ba_event_id', 'integer', array('signed' => false, 'identity' => false, 'null' => false))
                ->addColumn('kpi_event_id', 'integer', array('signed' => false, 'identity' => false, 'null' => false))
                ->addForeignKey('ba_event_id', 'mod_bam_reporting_ba_events', 'ba_event_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('kpi_event_id', 'mod_bam_reporting_kpi_events', 'kpi_event_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('ba_event_id'), array('unique' => false))
                ->addIndex(array('kpi_event_id'), array('unique' => false))
                ->addIndex(array('ba_event_id', 'kpi_event_id'), array('unique' => true))
                ->addColumn('sunday', 'string', array('limit' => 255, 'null' => true))
                ->save();

        
        // Creation of table mod_bam_reporting_timeperiods
        $mod_bam_reporting_timeperiods = $this->table('mod_bam_reporting_timeperiods', array('id' => false, 'primary_key' => array('timeperiod_id')));
        $mod_bam_reporting_timeperiods->addColumn('timeperiod_id', 'integer', array('signed' => false, 'identity' => false, 'null' => false))
                ->addColumn('name', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('sunday', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('monday', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('tuesday', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('wedneday', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('thursday', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('friday', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('saturday', 'string', array('limit' => 255, 'null' => true))
                ->addForeignKey('timeperiod_id', 'mod_bam_reporting_timeperiods', 'timeperiod_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('timeperiod_id'), array('unique' => false))
                ->addIndex(array('name'), array('unique' => true))
                ->save();
        
        
        // Creation of table mod_bam_reporting_relations_ba_timeperiods
        $mod_bam_reporting_relations_ba_timeperiods = $this->table('mod_bam_reporting_relations_ba_timeperiods', array('id' => false, 'primary_key' => array('ba_id', 'timeperiod_id')));
        $mod_bam_reporting_relations_ba_timeperiods->addColumn('ba_id', 'integer', array('signed' => false, 'identity' => false, 'null' => false))
                ->addColumn('timeperiod_id', 'integer', array('signed' => false, 'identity' => false, 'null' => false))
                ->addColumn('is_default', 'boolean', array('null' => true))
                ->addForeignKey('ba_id', 'mod_bam_reporting_ba', 'ba_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('timeperiod_id', 'mod_bam_reporting_timeperiods', 'timeperiod_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('ba_id'), array('unique' => false))
                ->addIndex(array('timeperiod_id'), array('unique' => false))
                ->save();

        // Creation of table mod_bam_reporting_timeperiods_exceptions
        $mod_bam_reporting_timeperiods_exceptions = $this->table('mod_bam_reporting_timeperiods_exceptions', array('id' => false, 'primary_key' => array('timeperiod_id')));
        $mod_bam_reporting_timeperiods_exceptions->addColumn('timeperiod_id', 'integer', array('signed' => false, 'identity' => false, 'null' => false))
                ->addColumn('daterange', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('timerange', 'string', array('limit' => 255, 'null' => false))
                ->addForeignKey('timeperiod_id', 'mod_bam_reporting_timeperiods', 'timeperiod_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('timeperiod_id'), array('unique' => false))
                ->save();

        // Creation of table mod_bam_reporting_timeperiods_exclusions
        $mod_bam_reporting_timeperiods_exclusions = $this->table('mod_bam_reporting_timeperiods_exclusions', array('id' => false, 'primary_key' => array('timeperiod_id', 'excluded_timeperiod_id')));
        $mod_bam_reporting_timeperiods_exclusions->addColumn('timeperiod_id', 'integer', array('signed' => false, 'identity' => false, 'null' => false))
                ->addColumn('excluded_timeperiod_id', 'integer', array('signed' => false, 'identity' => false, 'null' => false))
                ->addForeignKey('timeperiod_id', 'mod_bam_reporting_timeperiods', 'timeperiod_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('excluded_timeperiod_id', 'mod_bam_reporting_timeperiods', 'timeperiod_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('timeperiod_id'), array('unique' => false))
                ->addIndex(array('excluded_timeperiod_id'), array('unique' => false))
                ->addIndex(array('timeperiod_id', 'excluded_timeperiod_id'), array('unique' => true))
                ->save();

        // Creation of table cfg_acl_resources_bas_relations
        $cfg_acl_resources_bas_relations = $this->table('cfg_acl_resources_bas_relations', array('id' => false, 'primary_key' => array('arbar_id')));
        $cfg_acl_resources_bas_relations->addColumn('arbar_id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('acl_resource_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('ba_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('type', 'integer', array('signed' => false, 'null' => false, 'default' => 0))
                ->addForeignKey('acl_resource_id', 'cfg_acl_resources', 'acl_resource_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addForeignKey('ba_id', 'cfg_bam', 'ba_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
                ->addIndex(array('acl_resource_id'), array('unique' => false))
                ->addIndex(array('ba_id'), array('unique' => false))
                ->save();
    }

    /**
    * Migrate Up.
    */
    public function up()
    {
        $this->execute('INSERT INTO cfg_bam_ba_type ("ba_type_id", "name", "description") values (1, "Business Unit", "Business Unit")');
        $this->execute('INSERT INTO cfg_bam_ba_type ("ba_type_id", "name", "description") values (2, "Application", "Application")');
        $this->execute('INSERT INTO cfg_bam_ba_type ("ba_type_id", "name", "description") values (3, "Middleware", "Middleware")');
    }
}
