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
        $exists_log_data_bin = $this->hasTable('log_data_bin');
        if ($exists_log_data_bin) {
            $logDataBin = $this->table('log_data_bin');
            $logDataBin->renameColumn('id_metric', 'metric_id')
                ->changeColumn('status', 'integer', array('null' => false, 'limit' => MysqlAdapter::INT_TINY, 'default' => 3))
                ->save();
        } else {
            $logDataBin = $this->table('log_data_bin', array('id' => false));
            $logDataBin->addColumn('metric_id', 'integer', array('signed' => false, 'null' => false))
                    ->addColumn('ctime', 'integer', array('signed' => false, 'null' => true))
                    ->addColumn('value', 'float', array('signed' => false, 'null' => true))
                    ->addColumn('status', 'integer', array('signed' => false, 'null' => false, 'limit' => MysqlAdapter::INT_TINY, 'default' => 3))
                    ->addIndex(array('metric_id'), array('unique' => false))
                    ->create();
        }
           
        $this->execute("SET FOREIGN_KEY_CHECKS=1");
    }
}
