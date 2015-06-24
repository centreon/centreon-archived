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
        $graph_template_id = $this->table('graph_template_id', array('id' => false, 'primary_key' => 'graph_template_id'));
        $graph_template_id
                ->addColumn('graph_template_id','integer', array('identity' => true, 'null' => false))
                ->addColumn('svc_tmpl_id','integer', array('null' => false))
                ->addColumn('draw_status','integer', array('null' => false, "default" => 0))
                ->addColumn('split','integer', array('null' => false, "default" => 0))
                ->addColumn('stackable','integer', array('null' => false, "default" => 0))
                ->addColumn('scale','integer', array('null' => false, "default" => 0))
                ->addForeignKey('svc_tmpl_id', 'cfg_services', 'service_id', array('delete'=> 'CASCADE'))
                ->addIndex(array('svc_tmpl_id'), array('unique' => false))
                ->save();
        
        
        $cfg_curve_config = $this->table('cfg_curve_config', array('id' => false, 'primary_key' => array('graph_template_id', 'metric_name')));
        $cfg_curve_config
                ->addColumn('graph_template_id','integer', array('null' => false))
                ->addColumn('metric_name','string',array('limit' => 255), array('null' => false))
                ->addColumn('color','string',array('limit' => 7), array('null' => true))
                ->addColumn('is_negative','integer',array('null' => false, "default" => 0))
                ->addColumn('fill','integer',array('null' => false, "default" => 0))
                ->addForeignKey('graph_template_id', 'cfg_graph_template', 'graph_template_id', array('delete'=> 'CASCADE'))
                ->save();
        
        
        $cfg_graph_views = $this->table('cfg_graph_views', array('id' => false, 'primary_key' => 'graph_view_id'));
        $cfg_curve_config
                ->addColumn('graph_view_id','integer', array('identity' => true, 'null' => false))
                ->addColumn('name','string',array('limit' => 255), array('null' => false))
                ->addColumn('privacy','string',array('limit' => 7), array('null' => true, "default" => 0))
                ->addColumn('owner_id','integer',array('null' => false, 'signed' => false, "default" => 0))
                ->addForeignKey('owner_id', 'cfg_users', 'user_id', array('delete'=> 'CASCADE'))
                ->save();
        
        
        $cfg_graph_views_services = $this->table('cfg_graph_views_services', array('id' => false, 'primary_key' => array('graph_view_id', 'service_id')));
        $cfg_graph_views_services
                ->addColumn('graph_view_id','integer', array('null' => false))
                ->addColumn('service_id','integer',array('null' => false))
                ->addColumn('order','integer',array('null' => false, "default" => 0))
                ->addForeignKey('graph_view_id', 'cfg_graph_views', 'graph_view_id', array('delete'=> 'CASCADE'))
                ->addForeignKey('service_id', 'cfg_services', 'service_id', array('delete'=> 'CASCADE'))
                ->save();
    }
}
    