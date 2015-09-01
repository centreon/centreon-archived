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
 * Description of 20150706172200_add_object_timeout
 *
 * @author kevin duret <kduret@centreon.com>
 */
use Phinx\Migration\AbstractMigration;

class AddObjectTimeout extends AbstractMigration
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
        $cfg_hosts = $this->table('cfg_hosts');
        $cfg_hosts->addColumn('host_check_timeout','integer', array('signed' => false, 'null' => true))
                ->save();

        $cfg_services = $this->table('cfg_services');
        $cfg_services->addColumn('service_check_timeout','integer', array('signed' => false, 'null' => true))
                ->save();
    }
}
    
