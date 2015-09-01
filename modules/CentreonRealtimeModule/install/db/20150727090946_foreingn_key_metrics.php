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

class ForeingnKeyMetrics extends AbstractMigration
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
        
        /* Change log_data_bin */
        $log_data_bin = $this->table('log_data_bin');
        $log_data_bin->changeColumn('ctime', 'integer', array('signed' => false, 'null' => false))
            ->addForeignKey('metric_id', 'rt_metrics', 'metric_id', array('delete'=> 'CASCADE'))
            ->save();
        
        /* Change rt_index_data */
        $rt_index_data = $this->table('rt_index_data');
        $rt_index_data->changeColumn('host_id','integer', array('signed' => false, 'null' => false))
            ->save();
    }
}
