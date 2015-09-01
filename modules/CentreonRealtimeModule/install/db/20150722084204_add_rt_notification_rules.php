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
 * Description of 20150722084204_add_rt_notification_rules
 *
 * @author bsauveton
 */
class AddRtNotificationRules extends AbstractMigration
{
    public function change()
    {
        
        $this->execute("SET FOREIGN_KEY_CHECKS=0");
        $rt_notification_rules = $this->table('rt_notification_rules')
            ->addColumn('rule_id','integer', array('signed' => false, 'null' => true))
            ->addColumn('method_id','integer', array('signed' => false, 'null' => true))
            ->addColumn('timeperiod_id','integer', array('signed' => false, 'null' => true))
            ->addColumn('contact_id','integer', array('signed' => false, 'null' => true))
            ->addColumn('host_id','integer', array('signed' => false, 'null' => true))
            ->addColumn('service_id','integer', array('signed' => false, 'null' => true))
            ->addForeignKey('rule_id', 'cfg_notification_rules', 'rule_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
            ->addForeignKey('method_id', 'cfg_notification_methods', 'method_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
            ->addForeignKey('timeperiod_id', 'cfg_timeperiods', 'tp_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
            ->addForeignKey('contact_id', 'cfg_contacts', 'contact_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
            ->addForeignKey('host_id', 'cfg_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
            ->addForeignKey('service_id', 'cfg_services', 'service_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
            ->create();
        $this->execute("SET FOREIGN_KEY_CHECKS=1");
    }
    //put your code here
}
