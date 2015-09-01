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
        $graph_template_id = $this->table('cfg_graph_template', array('id' => false, 'primary_key' => 'graph_template_id'));
        $graph_template_id
                ->addColumn('graph_template_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('svc_tmpl_id','integer', array('signed' => false,'null' => false))
                ->addColumn('draw_status','integer', array('signed' => false,'null' => false, "default" => 0))
                ->addColumn('split','integer', array('signed' => false, 'null' => false, "default" => 0))
                ->addColumn('stackable','integer', array('null' => false, "default" => 0))
                ->addColumn('scale','integer', array('signed' => false, 'null' => false, "default" => 0))
                ->addForeignKey('svc_tmpl_id', 'cfg_services', 'service_id', array('delete'=> 'CASCADE'))
                ->addIndex(array('svc_tmpl_id'), array('unique' => false))
                ->create();
        
        
        $cfg_curve_config = $this->table('cfg_curve_config', array('id' => false, 'primary_key' => array('graph_template_id', 'metric_name')));
        $cfg_curve_config
                ->addColumn('graph_template_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('metric_name','string',array('limit' => 255), array('null' => false))
                ->addColumn('color','string',array('limit' => 7), array('null' => true))
                ->addColumn('is_negative','integer',array('signed' => false, 'null' => false, "default" => 0))
                ->addColumn('fill','integer',array('signed' => false, 'null' => false, "default" => 0))
                ->addForeignKey('graph_template_id', 'cfg_graph_template', 'graph_template_id', array('delete'=> 'CASCADE'))
                ->create();
        
        
        $cfg_graph_views = $this->table('cfg_graph_views', array('id' => false, 'primary_key' => 'graph_view_id'));
        $cfg_graph_views
                ->addColumn('graph_view_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
                ->addColumn('name','string',array('limit' => 255), array('null' => false))
                ->addColumn('privacy','string',array('limit' => 7), array('null' => true, "default" => 0))
                ->addColumn('owner_id','integer',array('null' => false, 'signed' => false, "default" => 0))
                ->addForeignKey('owner_id', 'cfg_users', 'user_id', array('delete'=> 'CASCADE'))
                ->create();
        
        
        $cfg_graph_views_services = $this->table('cfg_graph_views_services', array('id' => false, 'primary_key' => array('graph_view_id', 'service_id')));
        $cfg_graph_views_services
                ->addColumn('graph_view_id','integer', array('signed' => false, 'null' => false))
                ->addColumn('service_id','integer',array('signed' => false, 'null' => false))
                ->addColumn('order','integer',array('signed' => false, 'null' => false, "default" => 0))
                ->addForeignKey('graph_view_id', 'cfg_graph_views', 'graph_view_id', array('delete'=> 'CASCADE'))
                ->addForeignKey('service_id', 'cfg_services', 'service_id', array('delete'=> 'CASCADE'))
                ->create();
    }
}
    
