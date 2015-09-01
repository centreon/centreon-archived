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
 * Description of 20150720080800_modify_downtime_default_columns_values
 *
 * @author kevin duret <kduret@centreon.com>
 */
use Phinx\Migration\AbstractMigration;

class ModifyDowntimeDefaultColumnValues extends AbstractMigration
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
        $rt_downtimes = $this->table('rt_downtimes');
        $rt_downtimes->changeColumn('actual_start_time','integer', array('null' => true))
                ->changeColumn('actual_end_time','integer', array('null' => true))
                ->changeColumn('triggered_by','integer', array('null' => true))
                ->changeColumn('service_id','integer', array('null' => true))
                ->removeIndex(array('host_id', 'start_time'))
                ->save();
    }
}
    
