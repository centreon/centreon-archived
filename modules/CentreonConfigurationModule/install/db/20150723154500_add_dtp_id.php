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


/**
 * Description of AddDtpId
 *
 * @author kevin duret <kduret@centreon.com>
 */
class AddDtpId extends AbstractMigration
{
    public function change()
    {
        $cfg_downtimes_periods = $this->table('cfg_downtimes_periods');
        $cfg_downtimes_periods->addColumn('dtp_id','integer', array('signed' => false, 'null' => false))
            ->save();

        $rt_downtimes = $this->table('rt_downtimes');
        $rt_downtimes->changeColumn('start_time','integer', array('null' => true))
            ->changeColumn('end_time','integer', array('null' => true))
            ->save();

        $this->execute('ALTER TABLE cfg_downtimes_periods DROP PRIMARY KEY, ADD PRIMARY KEY (dtp_id)');

        $cfg_downtimes_periods->changeColumn('dtp_id','integer', array('identity' => true, 'signed' => false, 'null' => false))
            ->save();

        $this->execute('SET FOREIGN_KEY_CHECKS = 0');
        $this->execute('ALTER TABLE cfg_downtimes_periods DROP INDEX dt_id');
        $this->execute('SET FOREIGN_KEY_CHECKS = 1');
    }
}
