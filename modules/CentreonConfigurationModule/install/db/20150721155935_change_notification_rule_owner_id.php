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
 * Description of 20150721155935_change_notification_rule_owner_id
 *
 * @author bsauveton
 */
use Phinx\Migration\AbstractMigration;

class ChangeNotificationRuleOwnerId extends AbstractMigration
{
    
    public function change()
    {
        $cfg_notification_rules = $this->table('cfg_notification_rules');
        $cfg_notification_rules->dropForeignKey('owner_id')
            ->removeColumn('owner_id')
            ->addColumn('owner_id', 'integer', array('signed' => false, 'null' => true))
            ->addForeignKey('owner_id', 'cfg_users', 'user_id', array('delete'=> 'CASCADE', 'update'=> 'CASCADE'))->save();
        
    }
    
    //put your code here
}
