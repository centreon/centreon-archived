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
