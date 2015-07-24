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

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class MetricsFixColumns extends AbstractMigration
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
        $this->execute("SET FOREIGN_KEY_CHECKS=0");
        
        /* Change index_data */
        $rtIndex = $this->table('rt_index_data');
        $rtIndex->renameColumn('id', 'index_id')
            ->changeColumn('special', 'boolean', array('null' => false, 'default' => 0))
            ->changeColumn('hidden', 'boolean', array('null' => false, 'default' => 0))
            ->changeColumn('to_delete', 'boolean', array('null' => false, 'default' => 0))
            ->changeColumn('trashed', 'boolean', array('null' => false, 'default' => 0))
            ->changeColumn('locked', 'boolean', array('null' => false, 'default' => 0))
            ->changeColumn('must_be_rebuild', 'integer', array('null' => false, 'limit' => MysqlAdapter::INT_TINY, 'default' => 0))
            ->changeColumn('storage_type', 'integer', array('null' => false, 'limit' => MysqlAdapter::INT_TINY, 'default' => 2))
            ->save();
            
        /* Change metrics */
        $rtMetrics = $this->table('rt_metrics');
        $rtMetrics->changeColumn('data_source_type', 'integer', array('null' => false, 'limit' => MysqlAdapter::INT_TINY, 'default' => 0))
            ->changeColumn('hidden', 'boolean', array('null' => false, 'default' => 0))
            ->changeColumn('to_delete', 'boolean', array('null' => false, 'default' => 0))
            ->changeColumn('locked', 'boolean', array('null' => false, 'default' => 0))
            ->changeColumn('crit_threshold_mode', 'boolean', array('null' => false, 'default' => 0))
            ->save();
        
        /* Change data_bin */
        $logDataBin = $this->table('log_data_bin');
        $logDataBin->renameColumn('id_metric', 'metric_id')
            ->changeColumn('status', 'integer', array('null' => false, 'limit' => MysqlAdapter::INT_TINY, 'default' => 3))
            ->save();
            
        $this->execute("SET FOREIGN_KEY_CHECKS=1");
    }
}
